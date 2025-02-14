<?php
header('Content-Type: application/json');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Validate POST data
if (!isset($_POST['restore_users']) || !is_array($_POST['restore_users'])) {
    die(json_encode(['success' => false, 'message' => 'No users selected']));
}

try {
    $conn->begin_transaction();
    
    // Get the selected user IDs without converting to integers
    $selected_user_ids = array_filter($_POST['restore_users']);
    
    if (empty($selected_user_ids)) {
        throw new Exception('No valid user IDs provided');
    }

    // Debug - Log the selected IDs
    error_log('Selected user IDs: ' . implode(', ', $selected_user_ids));

    // Create placeholders for the IN clause
    $placeholders = str_repeat('?,', count($selected_user_ids) - 1) . '?';
    
    // First verify these users exist and get their current status
    $check_sql = "SELECT user_id, username, is_active FROM User WHERE user_id IN ($placeholders)";
    $check_stmt = $conn->prepare($check_sql);
    
    if ($check_stmt === false) {
        throw new Exception('Failed to prepare check statement: ' . $conn->error);
    }
    
    // Bind parameters for the check using string type
    $types = str_repeat('s', count($selected_user_ids));
    $check_stmt->bind_param($types, ...$selected_user_ids);
    
    if (!$check_stmt->execute()) {
        throw new Exception('Failed to execute check query: ' . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    $found_users = [];
    
    while ($row = $result->fetch_assoc()) {
        $found_users[$row['user_id']] = $row;
        error_log("Found user {$row['user_id']} ('{$row['username']}') with is_active = {$row['is_active']}");
    }
    
    $check_stmt->close();

    if (empty($found_users)) {
        throw new Exception('No matching users found in database');
    }

    // Simple update query for selected users
    $update_sql = "UPDATE User SET is_active = 1 WHERE user_id IN ($placeholders)";
    $update_stmt = $conn->prepare($update_sql);
    
    if ($update_stmt === false) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }
    
    // Use string type for binding parameters
    $update_stmt->bind_param($types, ...$selected_user_ids);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to execute update: ' . $update_stmt->error);
    }
    
    $affected_rows = $update_stmt->affected_rows;
    $update_stmt->close();

    if ($affected_rows === 0) {
        throw new Exception('No users were updated. They may already be active.');
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $affected_rows . ' user(s) restored successfully',
        'debug' => [
            'selected_ids' => $selected_user_ids,
            'found_users' => $found_users,
            'affected_rows' => $affected_rows
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Restore users error: ' . $e->getMessage());
    
    // Get current state of selected users
    $debug_sql = "SELECT user_id, username, is_active FROM User WHERE user_id IN ('" . 
                implode("','", array_map(function($id) use ($conn) {
                    return $conn->real_escape_string($id);
                }, $selected_user_ids)) . "')";
    $debug_result = $conn->query($debug_sql);
    $debug_info = [];
    
    if ($debug_result) {
        while ($row = $debug_result->fetch_assoc()) {
            $debug_info[] = $row;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'selected_ids' => $selected_user_ids,
            'found_users' => $found_users ?? [],
            'current_state' => $debug_info,
            'error' => $e->getMessage()
        ]
    ]);
}

$conn->close();
?>