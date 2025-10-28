<?php

if (!function_exists('format_date')) {
    /**
     * Format a date string or timestamp to a specified format
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $format Output format (default: 'Y-m-d H:i:s')
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Formatted date string
     */
    function format_date($date = null, $format = 'Y-m-d H:i:s', $timezone = 'Asia/Manila')
    {
        if (empty($date)) {
            $date = 'now';
        }
        
        try {
            $dt = new DateTime($date);
            $dt->setTimezone(new DateTimeZone($timezone));
            return $dt->format($format);
        } catch (Exception $e) {
            return date($format);
        }
    }
}

if (!function_exists('format_date_readable')) {
    /**
     * Format a date to a human-readable format
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Human-readable date string
     */
    function format_date_readable($date = null, $timezone = 'Asia/Manila')
    {
        if (empty($date)) {
            $date = 'now';
        }
        
        try {
            $dt = new DateTime($date);
            $dt->setTimezone(new DateTimeZone($timezone));
            
            $now = new DateTime('now', new DateTimeZone($timezone));
            $diff = $now->diff($dt);
            
            // If same day
            if ($diff->days == 0) {
                return 'Today at ' . $dt->format('g:i A');
            }
            
            // If yesterday
            if ($diff->days == 1 && $dt < $now) {
                return 'Yesterday at ' . $dt->format('g:i A');
            }
            
            // If tomorrow
            if ($diff->days == 1 && $dt > $now) {
                return 'Tomorrow at ' . $dt->format('g:i A');
            }
            
            // If within a week
            if ($diff->days < 7) {
                return $dt->format('l \a\t g:i A');
            }
            
            // If within a year
            if ($diff->y == 0) {
                return $dt->format('M j \a\t g:i A');
            }
            
            // Default format
            return $dt->format('M j, Y \a\t g:i A');
            
        } catch (Exception $e) {
            return date('M j, Y \a\t g:i A');
        }
    }
}

if (!function_exists('format_date_short')) {
    /**
     * Format a date to a short format
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Short date string
     */
    function format_date_short($date = null, $timezone = 'Asia/Manila')
    {
        return format_date($date, 'M j, Y', $timezone);
    }
}

if (!function_exists('format_date_time')) {
    /**
     * Format a date to show both date and time
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Date and time string
     */
    function format_date_time($date = null, $timezone = 'Asia/Manila')
    {
        return format_date($date, 'M j, Y g:i A', $timezone);
    }
}

if (!function_exists('format_date_for_input')) {
    /**
     * Format a date for HTML datetime-local input
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Date string in Y-m-d\TH:i format
     */
    function format_date_for_input($date = null, $timezone = 'Asia/Manila')
    {
        if (empty($date)) {
            $date = 'now';
        }
        
        try {
            $dt = new DateTime($date);
            $dt->setTimezone(new DateTimeZone($timezone));
            return $dt->format('Y-m-d\TH:i');
        } catch (Exception $e) {
            return date('Y-m-d\TH:i');
        }
    }
}

if (!function_exists('get_current_datetime')) {
    /**
     * Get current date and time in a specified format
     * 
     * @param string $format Output format (default: 'Y-m-d H:i:s')
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Current date and time
     */
    function get_current_datetime($format = 'Y-m-d H:i:s', $timezone = 'Asia/Manila')
    {
        return format_date('now', $format, $timezone);
    }
}

if (!function_exists('get_current_date')) {
    /**
     * Get current date only
     * 
     * @param string $format Output format (default: 'Y-m-d')
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Current date
     */
    function get_current_date($format = 'Y-m-d', $timezone = 'Asia/Manila')
    {
        return format_date('now', $format, $timezone);
    }
}

if (!function_exists('get_current_time')) {
    /**
     * Get current time only
     * 
     * @param string $format Output format (default: 'H:i:s')
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string Current time
     */
    function get_current_time($format = 'H:i:s', $timezone = 'Asia/Manila')
    {
        return format_date('now', $format, $timezone);
    }
}

if (!function_exists('add_days_to_date')) {
    /**
     * Add days to a date
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param int $days Number of days to add
     * @param string $format Output format (default: 'Y-m-d H:i:s')
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return string New date string
     */
    function add_days_to_date($date, $days, $format = 'Y-m-d H:i:s', $timezone = 'Asia/Manila')
    {
        if (empty($date)) {
            $date = 'now';
        }
        
        try {
            $dt = new DateTime($date);
            $dt->setTimezone(new DateTimeZone($timezone));
            $dt->add(new DateInterval('P' . abs($days) . 'D'));
            return $dt->format($format);
        } catch (Exception $e) {
            return date($format);
        }
    }
}

if (!function_exists('get_date_range')) {
    /**
     * Get date range between two dates
     * 
     * @param mixed $start_date Start date
     * @param mixed $end_date End date
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return array Array with start and end DateTime objects
     */
    function get_date_range($start_date, $end_date, $timezone = 'Asia/Manila')
    {
        try {
            $start = new DateTime($start_date);
            $start->setTimezone(new DateTimeZone($timezone));
            
            $end = new DateTime($end_date);
            $end->setTimezone(new DateTimeZone($timezone));
            
            return [
                'start' => $start,
                'end' => $end,
                'days' => $start->diff($end)->days
            ];
        } catch (Exception $e) {
            $now = new DateTime('now', new DateTimeZone($timezone));
            return [
                'start' => $now,
                'end' => $now,
                'days' => 0
            ];
        }
    }
}

if (!function_exists('is_date_valid')) {
    /**
     * Check if a date string is valid
     * 
     * @param mixed $date Date string to validate
     * @param string $format Expected format (optional)
     * @return bool True if valid, false otherwise
     */
    function is_date_valid($date, $format = null)
    {
        if (empty($date)) {
            return false;
        }
        
        try {
            if ($format) {
                $dt = DateTime::createFromFormat($format, $date);
                return $dt && $dt->format($format) === $date;
            } else {
                new DateTime($date);
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('get_timezone_offset')) {
    /**
     * Get timezone offset in hours
     * 
     * @param string $timezone Timezone (default: 'Asia/Manila')
     * @return float Offset in hours
     */
    function get_timezone_offset($timezone = 'Asia/Manila')
    {
        try {
            $dt = new DateTime('now', new DateTimeZone($timezone));
            return $dt->getOffset() / 3600;
        } catch (Exception $e) {
            return 8; // Default to Philippines timezone
        }
    }
}

if (!function_exists('format_duration')) {
    /**
     * Format duration in seconds to human readable format
     * 
     * @param int $seconds Duration in seconds
     * @return string Formatted duration
     */
    function format_duration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds != 1 ? 's' : '');
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return $minutes . ' minute' . ($minutes != 1 ? 's' : '');
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $result = $hours . ' hour' . ($hours != 1 ? 's' : '');
            if ($minutes > 0) {
                $result .= ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }
            return $result;
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $result = $days . ' day' . ($days != 1 ? 's' : '');
            if ($hours > 0) {
                $result .= ' ' . $hours . ' hour' . ($hours != 1 ? 's' : '');
            }
            return $result;
        }
    }
}
