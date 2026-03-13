<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';
require './lib/auth.php';
require './lib/inventory.php';

$warehouse_inventory = get_inventory('warehouse'); 
// $warehouse_units = $warehouse_inventory->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Inventory</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sku.css">
    <link rel="stylesheet" href="./css/normalize.css">
</head>
<body>
    <!-- header -->
    <div class="header-bar">
        <h2>JBC Manufacturing CMS</h2>

        <div class="header-bar-right">
            <h5><?php echo htmlspecialchars($_SESSION['user_email']); ?></h5>
            <a href="logout.php" style="text-decoration: none; color: inherit;"><h5>Logout</h5></a>
        </div>
    </div>

    <!-- page wrapper: sidebar + main content -->
    <div class="page-wrapper">
        <!-- sidebar -->
        <div class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="sku-management.php"><h5>SKU Management</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="internal-inventory.php"><h5>Internal Inventory</h5></a>
                </li>
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="warehouse-inventory.php"><h5>Warehouse Inventory</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="mpl-records.php"><h5>MPL Records</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="order-records.php"><h5>Order Records</h5></a>
                </li>
            </ul>
        </div>

        <!-- main content -->
        <div class="main-content">
            <h1 class="color-text-primary">Warehouse Inventory</h1>

        <div class="internal-inventory-action-card">
            <h3 class="color-text-primary">
                Total # of Warehouse Inventory:
                <?php echo $warehouse_inventory['total']; ?>
            </h3>
        </div>

        <div class="sku-table-container">
            <table class="sku-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Unit Number</th>
                        <th>Ficha</th>
                        <th>Description</th>
                        <th>Quantity Shipped</th>
                        <th>Footage Quantity</th>
                        <th>Ship Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warehouse_inventory['inventory'] as $unit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($unit['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($unit['unit_number']); ?></td>
                            <td><?php echo htmlspecialchars($unit['ficha']); ?></td>
                            <td><?php echo htmlspecialchars($unit['description']); ?></td>
                            <td><?php echo htmlspecialchars($unit['quantity_shipped']); ?></td>
                            <td><?php echo htmlspecialchars($unit['footage_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($unit['ship_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
