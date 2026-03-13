<?php
require 'db_connect.php';
require './lib/auth.php';

require_login();

// Prepare query
$stmt = $connection->prepare("
    SELECT order_number, unit_number, ficha, description, quantity_shipped, footage_quantity, ship_date
    FROM inventory
");

$stmt->execute();
$result = $stmt->get_result();
$result_count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Inventory</title>
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

<!-- Page Wrapper -->
<div class="page-wrapper">

    <!-- Sidebar -->
        <div class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="sku-management.php"><h5>SKU Management</h5></a>
                </li>
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="internal-inventory.php"><h5>Internal Inventory</h5></a>
                </li>
                <li class="nav-item">
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

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="color-text-primary">Internal Inventory</h1>

        <div class="internal-inventory-action-card">
            <h3 class="color-text-primary">
                Total # of Internal Inventory: <?php echo $result_count; ?>
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
                    <?php if ($result_count > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['unit_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['ficha']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity_shipped']); ?></td>
                                <td><?php echo htmlspecialchars($row['footage_quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['ship_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">
                                No inventory records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

<?php
$stmt->close();
$connection->close();
?>
