<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Base Migration class with PostgreSQL ENUM support
 * Automatically converts ENUM to VARCHAR with CHECK constraints for PostgreSQL
 */
abstract class BaseMigration extends Migration
{
    /**
     * Get field definition, converting ENUM to VARCHAR for PostgreSQL
     */
    protected function getField(array $field): array
    {
        if (isset($field['type']) && strtoupper($field['type']) === 'ENUM') {
            $db = \Config\Database::connect();
            $platform = $db->getPlatform();
            
            // Convert ENUM to VARCHAR for PostgreSQL
            if (stripos($platform, 'Postgre') !== false) {
                $constraint = $field['constraint'] ?? [];
                $maxLength = 50; // Default length
                
                if (is_array($constraint)) {
                    $maxLength = max(array_map('strlen', $constraint)) + 10;
                }
                
                $newField = [
                    'type' => 'VARCHAR',
                    'constraint' => $maxLength,
                ];
                
                if (isset($field['default'])) {
                    $newField['default'] = $field['default'];
                }
                if (isset($field['null'])) {
                    $newField['null'] = $field['null'];
                }
                
                // Store original ENUM constraint for CHECK constraint
                $field['_enum_values'] = $constraint;
                $field['_original'] = $newField;
                
                return $newField;
            }
        }
        
        return $field;
    }
    
    /**
     * Add CHECK constraint for ENUM fields in PostgreSQL
     */
    protected function addEnumConstraints(string $table, array $fields): void
    {
        $db = \Config\Database::connect();
        $platform = $db->getPlatform();
        
        // Only for PostgreSQL
        if (stripos($platform, 'Postgre') === false) {
            return;
        }
        
        foreach ($fields as $fieldName => $fieldDef) {
            if (isset($fieldDef['_enum_values']) && is_array($fieldDef['_enum_values'])) {
                $values = array_map(function($val) use ($db) {
                    return $db->escape($val);
                }, $fieldDef['_enum_values']);
                $valuesList = implode(',', $values);
                
                $constraintName = "{$table}_{$fieldName}_check";
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} 
                        CHECK ({$fieldName} IN ({$valuesList}))";
                
                try {
                    $db->query($sql);
                } catch (\Exception $e) {
                    // Constraint might already exist, ignore
                }
            }
        }
    }
}

