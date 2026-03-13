<?php 
function create_product($data){
    global $connection;

    $sku = $connection->real_escape_string($data['sku']);
    $desc = $connection->real_escape_string($data['description']);
    $uom = $connection->real_escape_string($data['uom']);
    $piece = (int)$data['piece'];
    $length = (int)$data['length'];
    $width = (int)$data['width'];
    $height = floatval($data['height']);
    $weight = floatval($data['weight']);

    $stmt = $connection->prepare(
        "INSERT INTO cms_products (sku, description, uom, piece, length, width, height, weight) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('sssiiidd', $sku, $desc, $uom, $piece, $length, $width, $height, $weight);

    if($stmt->execute()) {
        return $connection->insert_id;
    } else {
        error_log("Create product error: " . $stmt->error);
        return false;
    }
}

function get_product($id) {
    global $connection;

    $stmt = $connection->prepare("SELECT sku, description, uom, piece, length, width, height, weight FROM cms_products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
	    $product = $result->fetch_assoc();
        // assoc array of all the product info
        return $product;
    } else {
        return null;
    }
}

function update_product($id, $data) {
    global $connection;

    if (!isset($data['sku']) || !isset($data['description']) || !isset($data['uom']) || !isset($data['piece']) 
        || !isset($data['length']) || !isset($data['width']) || !isset($data['height']) || !isset($data['weight']))
    {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $sku = $connection -> real_escape_string($data['sku']);
    $desc = $connection -> real_escape_string($data['description']);
    $uom = $connection -> real_escape_string($data['uom']);
    $piece = (int)$data['piece'];
    $length = (int)$data['length'];
    $width = (int)$data['width'];
    $height = floatval($data['height']);
    $weight = floatval($data['weight']);

    $stmt = $connection->prepare(
        "UPDATE cms_products SET sku = ?, description = ?, uom = ?, piece = ?, length = ?, width = ?, height = ?, weight = ? WHERE id = ? LIMIT 1"
    );

    $stmt->bind_param('sssiiiddi', $sku, $desc, $uom, $piece, $length, $width, $height, $weight, $id);

    if ($stmt->execute()) {
        return $stmt->affected_rows;
    } else {
        return false;
    }
}

function get_products() {
    global $connection;

    $stmt = $connection->prepare("SELECT sku, description, uom, piece, length, width, height, weight FROM cms_products");
    if($stmt->execute()) {
        $result = $stmt->get_result();
	    $products = $result->fetch_all(MYSQLI_ASSOC);
        // assoc array of all the products
        return [
            'total' => count($products),
            'products' => $products
        ];
    } else {
        return false;   
    }
}

function delete_product($id) {
    global $connection;
    
    $stmt = $connection->prepare("DELETE FROM cms_products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function get_product_count() {
    global $connection;
    
    $result = $connection->query("SELECT COUNT(*) as count FROM cms_products");
    $row = $result->fetch_assoc();
    
    return $row['count'];
}
?>