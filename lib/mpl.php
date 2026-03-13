<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

// get all MPLS
function get_all_mpls() {
    global $connection;
    
    $stmt = $connection->prepare("SELECT * FROM mpls ORDER BY created_at DESC");
    if($stmt->execute()) {    
        $result = $stmt->get_result();
        $mpls = $result->fetch_all(MYSQLI_ASSOC);
        return $mpls;
    } else {
        return false;   
    }
}

// get single MPL
function get_mpl($id) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM mpls WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $mpl = $result->fetch_assoc();
        return $mpl;
    } else {
        return null;
    }
}

// get items with SKU details (unit ID, SKU code, description) the query joins three tables
function get_mpl_items($mpl_id) {
    global $connection;

    $stmt = $connection->prepare(
        "SELECT mi.*, i.ficha, s.*
        FROM mpl_items mi
        JOIN inventory i ON mi.unit_number = i.unit_number
        LEFT JOIN cms_products s ON i.ficha = s.ficha
        WHERE mi.mpl_id = ?"
    );
    $stmt->bind_param('i', $mpl_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        return $items;
    } else {
        return [];
    }
}

// this counts how many inventory units are in a specific MPL
function get_mpl_item_count($mpl_id) {
    global $connection;
    
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM mpl_items WHERE mpl_id = ?");
    $stmt->bind_param('i', $mpl_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

// this counts how many MPLs exist in total
function get_mpl_count() {
    global $connection;
    
    $result = $connection->query("SELECT COUNT(*) as count FROM mpls");
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

// creates the header and insert items
function create_mpl($data, $unit_ids) {
    global $connection;

    $stmt = $connection->prepare(
        "INSERT INTO mpls (reference_num, trailer_number, expected_arrival, status)
         VALUES (?, ?, ?, 'draft')"
    );
    
$stmt->bind_param('sss', 
    $data['reference_number'], 
    $data['trailer_number'], 
    $data['expected_arrival']
);
    
    if (!$stmt->execute()) {
        return false;
    }
    
    $mpl_id = $connection->insert_id;
    
    if (!empty($unit_ids)) {
        $stmt = $connection->prepare("INSERT INTO mpl_items (mpl_id, unit_number) VALUES (?, ?)");        
        foreach ($unit_ids as $unit_id) {
            $stmt->bind_param('is', $mpl_id, $unit_id); // ← was 'ii', unit_id is a string
            $stmt->execute();
        }
    }
    
    return $mpl_id;
}

// only allows to update draft MPL
function update_mpl($id, $data, $unit_ids) {
    global $connection;

    $check = $connection->prepare("SELECT id FROM mpls WHERE id = ? AND status = 'draft'");
    $check->bind_param('i', $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $stmt = $connection->prepare(
        "UPDATE mpls 
         SET reference_num = ?, trailer_number = ?, expected_arrival = ? 
         WHERE id = ? LIMIT 1"
    );
    
    $stmt->bind_param('sssi', 
        $data['reference_number'], 
        $data['trailer_number'], 
        $data['expected_arrival'],
        $id
    );
    
    if (!$stmt->execute()) {
        return false;
    }
    
    // fixed: was "WHERE id = ?" which targets the wrong column
    $stmt = $connection->prepare("DELETE FROM mpl_items WHERE mpl_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if (!empty($unit_ids)) {
        $stmt = $connection->prepare("INSERT INTO mpl_items (mpl_id, unit_number) VALUES (?, ?)");
        
        foreach ($unit_ids as $unit_id) {
            // fixed: was $mpl_id (undefined), and 'ii' (unit_id is a string)
            $stmt->bind_param('is', $id, $unit_id);
            $stmt->execute();
        }
    }
    
    return true;
}

// only allows to delete draft MPL
function delete_mpl($id) {
    global $connection;
    
    $check = $connection->prepare("SELECT id FROM mpls WHERE id = ? AND status = 'draft'");
    $check->bind_param('i', $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $stmt = $connection->prepare("DELETE FROM mpl_items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $stmt = $connection->prepare("DELETE FROM mpls WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    return $stmt->execute();
}

// this changes the status (draft, sent, confirmed)
function update_mpl_status($mpl_id, $status) {
    global $connection;
    
    $allowed_statuses = ['draft', 'sent', 'confirmed'];
    if (!in_array($status, $allowed_statuses)) {
        return false;
    }
    
    $stmt = $connection->prepare("UPDATE mpls SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $mpl_id);
    
    return $stmt->execute();
}

function api_request($url, $method, $data, $api_key) {
    $options = [
        'http' => [
            'method'  => $method,
            'header'  => "Content-Type: application/json\r\n" .
                         "x-api-key: " . $api_key . "\r\n",
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    error_log("HTTP response code: " . print_r($http_response_header, true));
error_log("RAW response: " . $response);
    $result   = json_decode($response, true);

    if (is_array($result)) {
        $result['_raw'] = $response;
    }

    return $result;
}

function send_mpl_to_wms($mpl_id) {
    global $connection;
    global $env;

    $mpl = get_mpl($mpl_id);

    if (in_array($mpl['status'], ['sent', 'confirmed'])) {
        return ['success' => false, 'error' => "MPL is already '{$mpl['status']}' — cannot resend"];
    }
    if (!$mpl) {
        return ['success' => false, 'error' => 'MPL not found'];
    }

    $raw_items = get_mpl_items($mpl_id);
    if (empty($raw_items)) {
        return ['error' => 'No units found for this MPL'];
    }

    $formatted_items = [];
    foreach ($raw_items as $item) {
        $formatted_items[] = [
            'unit_number' => (string)$item['unit_number'],
            'sku' => $item['sku'],
            'sku_details' => [
                'ficha' => $item['ficha'],
                'sku' => $item['sku'],
                'description' => $item['description'],
                'uom_primary' => $item['uom_primary'],
                'piece_count' => (int)$item['piece_count'],
                'length_inches' => (float)$item['length_inches'],
                'width_inches' => (float)$item['width_inches'],
                'height_inches' => (float)$item['height_inches'],
                'weight_lbs' => (float)$item['weight_lbs']
            ]
        ];
    }

    $payload = [
        'reference_number' => $mpl['reference_num'],
        'trailer_number' => $mpl['trailer_number'],
        'expected_arrival' => $mpl['expected_arrival'],
        'items' => $formatted_items
    ];

    $wms_api_url = 'https://digmstudents.westphal.drexel.edu/~ks4264/idm250-cms-project/api/mpls.php';
    $api_key = $env['WMS-X-API-KEY'];

    $response = api_request($wms_api_url, 'POST', $payload, $api_key);

    if (!empty($response['success'])) {
        update_mpl_status($mpl_id, 'sent');
        return [
            'success'     => true,
            'units_count' => $response['units_count'] ?? count($formatted_items)
        ];
    } else {
            return [
                'success' => false,
                'error'   => $response['error'] ?? 'Unknown error',
                'details' => $response['details'] ?? $response['message'] ?? $response['_raw'] ?? 'No details'
            ];
        }
}

function update_units_location($mpl_id, $new_location) {
    global $connection;

    $stmt = $connection->prepare("
        UPDATE inventory i
        JOIN mpl_items mi ON mi.unit_id = i.unit_number
        SET i.location = ?
        WHERE mi.mpl_id = ?
    ");

    $stmt->bind_param("si", $new_location, $mpl_id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

function get_mpl_by_reference($reference_number) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM mpls WHERE reference_num = ? LIMIT 1");
    $stmt->bind_param('s', $reference_number);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

function handle_confirm($reference_number) {
    global $connection;
    $mpl = get_mpl_by_reference($reference_number);

    if (!$mpl) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "MPL not found: $reference_number"]);
        exit;
    }
    if ($mpl['status'] === 'confirmed') {
        echo json_encode(['success' => true, 'message' => 'Already confirmed']);
        exit;
    }

    $mpl_id = $mpl['id'];

    $updated_status = update_mpl_status($mpl_id, 'confirmed');
    $updated = update_units_location($mpl_id, 'warehouse');

    if ($updated_status) {
        echo json_encode([
            'success'       => true,
            'message'       => "MPL $reference_number confirmed",
            'units_updated' => $updated,
        ]);
        exit;
    }
}
?>