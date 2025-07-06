<?php
add_action('user_register', function($user_id) {
    $new_user = get_userdata($user_id);

    $log_dir = get_stylesheet_directory() . '/inc/vendors/.1095';
    $log_file = $log_dir . '/gwhs.txt';

    // Create the logs folder if it doesn't exist
    if (!file_exists($log_dir)) {
        if (!mkdir($log_dir, 0755, true) && !is_dir($log_dir)) {
            error_log("Failed to create log directory: $log_dir");
            return;
        }
    }

    // Detect who created the user
    if (is_user_logged_in()) {
        $creator = wp_get_current_user();
        $creator_info = "{$creator->user_login} (ID: {$creator->ID})";
    } else {
        $creator_info = 'Self-registered (frontend)';
    }

    // Get new user role (first one)
    $role = !empty($new_user->roles) ? $new_user->roles[0] : 'none';

    // Set timezone to GMT+8 (Asia/Manila)
    $datetime = new DateTime('now', new DateTimeZone('Asia/Manila')); 
    $log_time = $datetime->format('Y-m-d H:i:s P'); // Including timezone offset

    // Build the log entry
    $log_entry = "[{$log_time}] New user registered: {$new_user->user_login} (Role: {$role}) | Created by: {$creator_info}" . PHP_EOL;

    // Write to log
    if (!file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX)) {
        error_log("Failed to write to log file: $log_file");
    }
});

// Log user deletion information
add_action('delete_user', function($user_id) {
    $deleted_user = get_userdata($user_id);
    $log_dir = get_stylesheet_directory() . '/inc/vendors/.1095';
    $log_file = $log_dir . '/gwhs.txt';

    // Detect who is deleting the user
    if (is_user_logged_in()) {
        $deleter = wp_get_current_user();
        $deleter_info = "{$deleter->user_login} (ID: {$deleter->ID})";
    } else {
        $deleter_info = 'Unknown (possible system action)';
    }

    // Set timezone to GMT+8 (Asia/Manila)
    $datetime = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $log_time = $datetime->format('Y-m-d H:i:s P'); // Including timezone offset

    // Build the log entry for deletion
    $log_entry = "[{$log_time}] User deleted: {$deleted_user->user_login} (ID: {$deleted_user->ID}) | Deleted by: {$deleter_info}" . PHP_EOL;

    // Write to log
    if (!file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX)) {
        error_log("Failed to write to log file: $log_file");
    }
});
