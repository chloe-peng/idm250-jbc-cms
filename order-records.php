<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connect.php';
require './lib/auth.php';
require './lib/orders.php';

require_login();

$orders = get_all_orders();
$order_count = get_order_count();

if (isset($_GET['send']) && isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $response = send_order_to_wms($order_id);

    if (!empty($response['success'])) {
        $_SESSION['success'] = "Order $order_id sent to WMS successfully!";
    } else {
        $_SESSION['error'] = "Failed to send Order $order_id: " . ($response['error'] ?? 'Unknown error') 
                   . " — " . ($response['details'] ?? '');
    }

    header('Location: order-records.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Records - JBC CMS</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sku.css">
    <link rel="stylesheet" href="./css/mpl-form.css">
    <link rel="stylesheet" href="./css/mpl.css">
</head>
<body>
<div class="header-bar">
    <h2>JBC Manufacturing CMS</h2>
    <div class="header-bar-right">
        <h5><?= htmlspecialchars($_SESSION['user_email']); ?></h5>
        <a href="logout.php"><h5>Logout</h5></a>
    </div>
</div>

<div class="page-wrapper">
    <div class="sidebar-nav">
    <ul class="nav-list">
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="sku-management.php"><h5>SKU Management</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="internal-inventory.php"><h5>Internal Inventory</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="warehouse-inventory.php"><h5>Warehouse Inventory</h5></a>
                </li>
                <li class="nav-item ">
                    <a style="text-decoration: none; color: inherit;" href="mpl-records.php"><h5>MPL Records</h5></a>
                </li>
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="order-records.php"><h5>Order Records</h5></a>
                </li>
            </ul>
    </div>

    <div class="main-content">
        <h1 class="color-text-primary">Order Records</h1>

        <div class="sku-action-card">
            <h3 class="color-text-primary">Total # of Orders: <?php echo count($orders); ?></h3>
            <a href="order-forms.php" class="add-sku-button" style="text-decoration: none; color: inherit;">
                <h5>Create Order</h5>
                <img class="icon" src="./images/plus-icon.png" alt="plus icon">
            </a>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success']; ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <table class="units-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Ship To</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && count($orders) > 0):
                    foreach ($orders as $order):
                    $full_address = "{$order['ship_to_street']}, {$order['ship_to_city']}, {$order['ship_to_state']} {$order['ship_to_zip']}";
                    $item_count = get_order_item_count($connection, $order['id']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                    <td><?= htmlspecialchars($full_address) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= $item_count ?></td>
                    <td>
                        <?php if ($order['status'] === 'draft'): ?>
                            <a href="order-forms.php?id=<?= $order['id'] ?>">Edit</a>
                            <a href="view-order.php?id=<?= $order['id'] ?>">View</a>
                            <a href="delete-order.php?id=<?= $order['id'] ?>" onclick="return confirm('Delete this order?');">Delete</a>
                            <a href="order-records.php?id=<?= $order['id'] ?>&send=1" class="btn-send">Send to WMS</a>
                        <?php elseif ($order['status'] === 'sent'): ?>
                            <a href="view-order.php?id=<?= $order['id'] ?>">View</a>
                        <?php else: ?>
                            <a href="view-order.php?id=<?= $order['id'] ?>">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach;
                else: ?>
            <tr><td colspan="5" style="text-align: center;">No orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</div>
</body>
</html>

<?php $connection->close(); ?>