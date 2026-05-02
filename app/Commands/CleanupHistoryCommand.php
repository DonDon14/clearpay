<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupHistoryCommand extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintenance:cleanup-history';
    protected $description = 'Delete year-based history and merge/remove duplicate payers safely.';

    public function run(array $params)
    {
        $year = isset($params[0]) ? (int) $params[0] : 2025;
        $from = sprintf('%d-01-01 00:00:00', $year);
        $to   = sprintf('%d-01-01 00:00:00', $year + 1);

        $db = db_connect();

        CLI::write("Starting cleanup for year {$year} ({$from} to {$to})", 'yellow');
        $db->transBegin();

        try {
            // 1) Delete history rows for target year
            foreach (['payments', 'payment_requests', 'refunds', 'activity_logs'] as $table) {
                $builder = $db->table($table);
                $deleted = $builder->where('created_at >=', $from)->where('created_at <', $to)->delete();
                if ($deleted === false) {
                    throw new \RuntimeException("Failed deleting {$table} for year {$year}");
                }
                CLI::write("Deleted {$table} rows for {$year}", 'green');
            }

            // 2) Merge duplicate payers by case-insensitive payer_id
            $dupByPayerId = $db->query(
                "SELECT LOWER(TRIM(payer_id)) AS k, array_agg(id ORDER BY id ASC) AS ids
                 FROM payers
                 GROUP BY LOWER(TRIM(payer_id))
                 HAVING COUNT(*) > 1"
            )->getResultArray();

            foreach ($dupByPayerId as $group) {
                $ids = $this->parsePgArrayToInts((string) ($group['ids'] ?? ''));
                if (count($ids) < 2) {
                    continue;
                }
                $keeper = array_shift($ids);
                $this->repointAndDeletePayers($db, $keeper, $ids);
                CLI::write("Merged duplicate payer_id group into payer #{$keeper}", 'cyan');
            }

            // 3) Merge duplicate payers by case-insensitive email (non-empty)
            $dupByEmail = $db->query(
                "SELECT LOWER(TRIM(email_address)) AS k, array_agg(id ORDER BY id ASC) AS ids
                 FROM payers
                 WHERE email_address IS NOT NULL AND TRIM(email_address) <> ''
                 GROUP BY LOWER(TRIM(email_address))
                 HAVING COUNT(*) > 1"
            )->getResultArray();

            foreach ($dupByEmail as $group) {
                $ids = $this->parsePgArrayToInts((string) ($group['ids'] ?? ''));
                if (count($ids) < 2) {
                    continue;
                }
                $keeper = array_shift($ids);
                $this->repointAndDeletePayers($db, $keeper, $ids);
                CLI::write("Merged duplicate email group into payer #{$keeper}", 'cyan');
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction marked failed.');
            }
            $db->transCommit();

            // Summary counts
            $summary = [];
            foreach (['payments', 'payment_requests', 'refunds', 'activity_logs', 'payers'] as $table) {
                $summary[$table] = (int) $db->table($table)->countAllResults();
            }

            CLI::write('Cleanup completed successfully.', 'green');
            foreach ($summary as $table => $count) {
                CLI::write("{$table}: {$count}", 'white');
            }
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            CLI::error('Cleanup failed: ' . $e->getMessage());
        }
    }

    private function repointAndDeletePayers($db, int $keeperId, array $duplicateIds): void
    {
        $duplicateIds = array_values(array_filter(array_map('intval', $duplicateIds), fn ($id) => $id > 0 && $id !== $keeperId));
        if (empty($duplicateIds)) {
            return;
        }

        foreach ($duplicateIds as $dupId) {
            $db->table('payments')->where('payer_id', $dupId)->set(['payer_id' => $keeperId])->update();
            $db->table('payment_requests')->where('payer_id', $dupId)->set(['payer_id' => $keeperId])->update();
            $db->table('refunds')->where('payer_id', $dupId)->set(['payer_id' => $keeperId])->update();
            $db->table('activity_logs')->where('payer_id', $dupId)->set(['payer_id' => $keeperId])->update();
            $db->table('payers')->where('id', $dupId)->delete();
        }
    }

    private function parsePgArrayToInts(string $value): array
    {
        $trimmed = trim($value, '{}');
        if ($trimmed === '') {
            return [];
        }
        return array_map('intval', explode(',', $trimmed));
    }
}

