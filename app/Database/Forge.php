<?php

namespace App\Database;

use CodeIgniter\Database\Forge as BaseForge;

/**
 * Custom Forge class that handles PostgreSQL ENUM conversion
 */
class Forge extends BaseForge
{
    /**
     * Override addField to convert ENUM to VARCHAR for PostgreSQL
     */
    public function addField($field)
    {
        // If it's an array of fields, process each one
        if (is_array($field) && isset($field[0]) && is_array($field[0])) {
            foreach ($field as &$f) {
                $f = $this->convertEnumField($f);
            }
            return parent::addField($field);
        }
        
        // Single field
        if (is_array($field)) {
            $field = $this->convertEnumField($field);
        }
        
        return parent::addField($field);
    }
    
    /**
     * Convert ENUM field to VARCHAR for PostgreSQL
     */
    protected function convertEnumField(array $field): array
    {
        if (isset($field['type']) && strtoupper($field['type']) === 'ENUM') {
            $db = $this->db;
            $platform = $db->getPlatform();
            
            // Convert ENUM to VARCHAR for PostgreSQL
            if (stripos($platform, 'Postgre') !== false) {
                $constraint = $field['constraint'] ?? [];
                $maxLength = 50; // Default
                
                if (is_array($constraint)) {
                    $maxLength = max(array_map('strlen', $constraint)) + 10;
                }
                
                $newField = [
                    'type' => 'VARCHAR',
                    'constraint' => $maxLength,
                ];
                
                // Copy other properties
                foreach (['default', 'null', 'comment', 'unsigned', 'auto_increment', 'unique'] as $prop) {
                    if (isset($field[$prop])) {
                        $newField[$prop] = $field[$prop];
                    }
                }
                
                // Store original ENUM values for CHECK constraint
                $field['_enum_values'] = $constraint;
                $field['_enum_field_name'] = array_key_first($field) ?? 'unknown';
                
                return $newField;
            }
        }
        
        return $field;
    }
    
    /**
     * Override createTable to add CHECK constraints for ENUM fields
     */
    public function createTable($table, $if_not_exists = false, array $attributes = [])
    {
        $result = parent::createTable($table, $if_not_exists, $attributes);
        
        // Add CHECK constraints for PostgreSQL ENUM fields
        $db = $this->db;
        $platform = $db->getPlatform();
        
        if (stripos($platform, 'Postgre') !== false && isset($this->fields)) {
            foreach ($this->fields as $fieldName => $fieldDef) {
                if (isset($fieldDef['type']) && strtoupper($fieldDef['type']) === 'ENUM' && 
                    isset($fieldDef['constraint']) && is_array($fieldDef['constraint'])) {
                    
                    $values = array_map(function($val) use ($db) {
                        return $db->escape($val);
                    }, $fieldDef['constraint']);
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
        
        return $result;
    }
}

