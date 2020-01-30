<?php
/**
 * TaskValidator.php
 *
 * @project take-home
 *
 */
namespace App\Utility;

/**
 * Class TaskValidator
 *
 * @package Utility
 */
class TaskValidator
{
    /**
     * Simple check for command
     *
     * @param $command
     *
     * @return bool
     */
    public function isValidCommand($command) {
        $ret_val = true;
    
        if (empty($command)) {
            $ret_val = false;
        }
        
        return $ret_val;
    }
    
    /**
     * Simple check for timestamps
     *
     * @param $timestamp
     *
     * @return bool
     */
    public function isValidTimestamp($timestamp) {
        $ret_val = true;
    
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return false;
        }
        
        return $ret_val;
    }
    
    /**
     * Simple check for IDs
     *
     * @param $id
     *
     * @return bool
     */
    public function isValidId($id) {
        $ret_val = true;
    
        if (empty($id) || !is_numeric($id) || $id < 0) {
            $ret_val = false;
        }
    
        return $ret_val;
    
    }
}