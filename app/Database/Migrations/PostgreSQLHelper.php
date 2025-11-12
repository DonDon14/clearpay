<?php

namespace App\Database\Migrations;

/**
 * Helper trait for PostgreSQL compatibility in migrations
 * Converts MySQL ENUM types to PostgreSQL-compatible VARCHAR with CHECK constraints
 */
trait PostgreSQLHelper
{
    /**
     * Convert ENUM field definition to database-agnostic format
     * For PostgreSQL: Uses VARCHAR with CHECK constraint
     * For MySQL: Uses ENUM as-is
     */
    protected function enumField(array $enumDef): array
    {
        $db = \Config\Database::connect();
        $driver = $db->getPlatform();
        
        // If using PostgreSQL, convert ENUM to VARCHAR
        if (strpos(strtolower($driver), 'postgre') !== false) {
            $constraint = $enumDef['constraint'] ?? [];
            $maxLength = max(array_map('strlen', $constraint));
            
            return [
                'type' => 'VARCHAR',
                'constraint' => $maxLength + 10, // Add some buffer
                'default' => $enumDef['default'] ?? null,
                'null' => $enumDef['null'] ?? false,
            ];
        }
        
        // For MySQL, return ENUM as-is
        return $enumDef;
    }
    
    /**
     * Add CHECK constraint for ENUM-like field in PostgreSQL
     */
    protected function addEnumCheck(string $table, string $column, array $allowedValues): void
    {
        $db = \Config\Database::connect();
        $driver = $db->getPlatform();
        
        // Only add CHECK constraint for PostgreSQL
        if (strpos(strtolower($driver), 'postgre') !== false) {
            $values = array_map(function($val) use ($db) {
                return $db->escape($val);
            }, $allowedValues);
            $valuesList = implode(',', $values);
            
            $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$table}_{$column}_check 
                    CHECK ({$column} IN ({$valuesList}))";
            
            $db->query($sql);
        }
    }
}

