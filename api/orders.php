<?php 
ob_start();

define('API_REQUEST', true);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-api-key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db_connect.php';
require_once '../lib/auth.php';
require_once '../lib/orders.php'; 

ob_end_clean();

check_api_key($env);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    global $connection;
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    $action       = $data['action']       ?? '';
    $order_number = $data['order_number'] ?? '';
    $shipped_at   = $data['shipped_at']   ?? '';

    $order = get_order_by_number($order_number);

    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'error'   => 'Not Found',
            'details' => "Order not found: $order_number"
        ]);
        exit;
    }

    if ($action !== 'ship') {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'details' => "Unknown action: $action"]);
        exit;
    }

    if ($order['status'] === 'confirmed') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'details' => 'Order already confirmed'
        ]);
        exit;
    }

    $order_id = $order['id'];

    $unit_ids = array_column(get_order_items($order_id), 'unit_number');  // ← fixed
    if (empty($unit_ids)) {
        http_response_code(422);
        echo json_encode([
            'error'   => 'Unprocessable',
            'details' => 'No units found for this order'
        ]);
        exit;
    }

    $updated = update_order_status($order_id, 'confirmed', $shipped_at);

    foreach ($unit_ids as $unit_id) {
        $stmt = $connection->prepare("DELETE FROM inventory WHERE unit_number = ?");
        $stmt->bind_param("s", $unit_id);
        $stmt->execute();
    }
    $units_deleted = count($unit_ids);

    http_response_code(200);
    echo json_encode([
        'success'       => true,
        'order_number'  => $order_number,
        'shipped_at'    => $shipped_at,
        'confirmed_at'  => date('Y-m-d H:i:s'),
        'units_deleted' => $units_deleted
    ]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>