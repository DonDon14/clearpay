<?php

namespace App\Services;

use CodeIgniter\Config\BaseService;
use RuntimeException;

class PythonAnalyticsService extends BaseService
{
    private string $pythonExecutable;
    private string $workerScript;

    public function __construct()
    {
        $root = rtrim(ROOTPATH, DIRECTORY_SEPARATOR);

        $candidates = array_filter([
            getenv('PYTHON_ANALYTICS_EXECUTABLE') ?: null,
            $root . DIRECTORY_SEPARATOR . 'analytics' . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe',
            $root . DIRECTORY_SEPARATOR . 'analytics' . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python',
            'C:\\Program Files\\PostgreSQL\\18\\pgAdmin 4\\python\\python.exe',
            'python',
        ]);

        $this->pythonExecutable = '';
        foreach ($candidates as $candidate) {
            if (($candidate === 'python' || is_file($candidate)) && $this->isRunnablePython($candidate)) {
                $this->pythonExecutable = $candidate;
                break;
            }
        }

        $this->workerScript = $root . DIRECTORY_SEPARATOR . 'analytics' . DIRECTORY_SEPARATOR . 'clearpay_analytics.py';
    }

    public function generateAnalytics(): array
    {
        return $this->runWorker('analyze');
    }

    public function generateReport(string $format): array
    {
        $format = strtolower($format);
        if (! in_array($format, ['pdf', 'csv', 'excel'], true)) {
            throw new RuntimeException('Unsupported analytics report format: ' . $format);
        }

        return $this->runWorker('report', $format);
    }

    private function runWorker(string $mode, ?string $format = null): array
    {
        if ($this->pythonExecutable === '') {
            throw new RuntimeException('Python executable not found for analytics integration.');
        }

        if (! is_file($this->workerScript)) {
            throw new RuntimeException('Python analytics worker script is missing: ' . $this->workerScript);
        }

        $payloadPath = tempnam(WRITEPATH . 'cache', 'analytics_payload_');
        if ($payloadPath === false) {
            throw new RuntimeException('Unable to create temporary analytics payload file.');
        }

        $outputExtension = $mode === 'report'
            ? ($format === 'excel' ? '.xls' : '.' . $format)
            : '.json';
        $outputPath = tempnam(WRITEPATH . 'cache', 'analytics_output_');
        if ($outputPath === false) {
            @unlink($payloadPath);
            throw new RuntimeException('Unable to create temporary analytics output file.');
        }

        $renamedOutputPath = $outputPath . $outputExtension;
        @unlink($outputPath);
        $outputPath = $renamedOutputPath;

        file_put_contents($payloadPath, json_encode($this->buildPayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $command = '"' . $this->pythonExecutable . '" "' . $this->workerScript . '" ' . $mode .
            ' --input "' . $payloadPath . '" --output "' . $outputPath . '"';

        if ($mode === 'report' && $format !== null) {
            $command .= ' --format "' . $format . '"';
        }

        exec($command . ' 2>&1', $outputLines, $returnCode);

        @unlink($payloadPath);

        if ($returnCode !== 0 || ! is_file($outputPath)) {
            @unlink($outputPath);
            throw new RuntimeException(
                "Python analytics worker failed.\nCommand: {$command}\nOutput: " . implode("\n", $outputLines)
            );
        }

        if ($mode === 'analyze') {
            $content = file_get_contents($outputPath);
            @unlink($outputPath);

            if ($content === false) {
                throw new RuntimeException('Unable to read Python analytics JSON output.');
            }

            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                throw new RuntimeException('Python analytics output was not valid JSON.');
            }

            return $decoded;
        }

        return [
            'path' => $outputPath,
            'filename' => basename($outputPath),
            'mime' => $this->guessMimeType($format ?? 'csv'),
        ];
    }

    private function buildPayload(): array
    {
        $db = \Config\Database::connect();

        $payments = $db->table('payments p')
            ->select('
                p.id,
                p.payer_id as payer_db_id,
                py.payer_name,
                py.payer_id as payer_id_number,
                py.profile_picture,
                p.contribution_id,
                p.product_id,
                COALESCE(c.title, pr.title) as contribution_title,
                CASE WHEN p.product_id IS NOT NULL THEN \'product\' ELSE COALESCE(c.contribution_type, \'contribution\') END as contribution_type,
                COALESCE(c.amount, pr.amount, 0) as contribution_amount,
                COALESCE(c.category, pr.category) as category,
                COALESCE(pr.cost_price, c.cost_price, 0) as cost_price,
                p.amount_paid,
                COALESCE(p.quantity, 1) as quantity,
                p.payment_method,
                p.payment_status,
                p.created_at,
                p.payment_date,
                p.receipt_number,
                p.payment_sequence
            ')
            ->join('payers py', 'py.id = p.payer_id', 'left')
            ->join('contributions c', 'c.id = p.contribution_id', 'left')
            ->join('products pr', 'pr.id = p.product_id', 'left')
            ->where('p.deleted_at', null)
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $contributions = $db->table('contributions')
            ->select('id, title, contribution_type, amount, COALESCE(cost_price, 0) as cost_price, category, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $products = $db->table('products')
            ->select("id, title, 'product' as contribution_type, amount, COALESCE(cost_price, 0) as cost_price, category, status, created_at")
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'generated_at' => date('c'),
            'payments' => $payments,
            'contributions' => array_merge($contributions, $products),
        ];
    }

    private function guessMimeType(string $format): string
    {
        return match ($format) {
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.ms-excel',
            default => 'text/csv; charset=utf-8',
        };
    }

    private function isRunnablePython(string $candidate): bool
    {
        $command = '"' . $candidate . '" --version';
        exec($command . ' 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }
}
