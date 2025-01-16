<?php

class TimeFormatter {
    
    public static function formatTimestamp(int $timestamp): string {
        $now = time();
        $diff = $now - $timestamp;
        
        // If older than 12 months, return date format
        if ($diff >= 31536000) { // 12 months in seconds
            return date('M d, Y', $timestamp);
        }
        
        // For timestamps within last 12 months
        if ($diff < 60) {
            return "just now";
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . " day" . ($days > 1 ? "s" : "") . " ago";
        } else {
            $months = floor($diff / 2592000);
            return $months . " month" . ($months > 1 ? "s" : "") . " ago";
        }
    }
}
