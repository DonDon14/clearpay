<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * System Configuration
 * Stores system version and other system-level settings
 */
class SystemConfig extends BaseConfig
{
    /**
     * System Version
     * Update this when releasing a new version
     */
    public string $version = 'ClearPay v1.0.0';
    
    /**
     * System Name
     */
    public string $name = 'ClearPay';
    
    /**
     * System Description
     */
    public string $description = 'ClearPay Payment Management System';
}

