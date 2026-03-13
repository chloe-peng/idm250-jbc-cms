<?php

function get_inventory($location = null) {
    global $connection;

    $sql = "SELECT i.*, p.sku, p.description, p.uom_primary
        FROM inventory i
        JOIN cms_products p ON i.ficha = p.ficha";
     
    if ($location) {
        $sql .= " WHERE i.location = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param('s', $location);
        $stmt->execute();
        $result = $stmt->get_result();
        $inventory = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'total' => count($inventory),
            'inventory' => $inventory
        ];
    } else {
        $stmt = $connection->prepare($sql);
        $result = $stmt->get_result();
        $inventory = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'total' => count($inventory),
            'inventory' => $inventory
        ];
    }
}

?>
