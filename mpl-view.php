<?php
require 'db_connect.php';
require './lib/auth.php';
require './lib/mpl.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: mpl-records.php');
    exit;
}

$mpl = get_mpl($id);

if (!$mpl) {
    $_SESSION['error'] = 'MPL not found.';
    header('Location: mpl-records.php');
    exit;
}

$items = get_mpl_items($id);
$item_count = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View MPL - JBC Manufacturing CMS</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sku.css">
    <link rel="stylesheet" href="./css/normalize.css">
<style>
    .back-link {
        position: absolute;
        right: 48px;
        top: 48px;
        background-color: #CFEFFF;
        color: #323232;
        padding: 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
    }
    
    .back-link:hover {
        background-color: #b8e5ff;
    }

    .details-section {
        border: 1px solid #EBEBEB;
        border-radius: 8px;
        padding: 28px 32px;
        margin-bottom: 32px;
        background: #FCFCFC;
    }

    .details-section h4 {
        color: #2C77A0;
        margin-bottom: 20px;
    }

    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px 40px;
    }

    .detail-field label {
        font-weight: 700;
        display: block;
        margin-bottom: 6px;
        color: #323232;
    }

    .detail-field p {
        margin: 0;
        color: #323232;
    }

    .line-items-header {
        color: #2C77A0;
        margin-bottom: 16px;
    }
</style>
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
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="warehouse-inventory.php"><h5>Warehouse Inventory</h5></a>
                </li>
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="mpl-records.php"><h5>MPL Records</h5></a>
                </li>
                <li class="nav-item">
                    <a style="text-decoration: none; color: inherit;" href="order-records.php"><h5>Order Records</h5></a>
                </li>
            </ul>
        </div>

        <!-- main content -->
        <div class="main-content" style="position: relative;">
            <a href="mpl-records.php" class="back-link">Back to List</a>

            <h1 class="color-text-primary" style="margin-bottom: 30px;">MPL</h1>

            <!-- details card -->
            <div class="details-section">
                <h4>Details</h4>
                <div class="details-grid">
                    <div class="detail-field">
                        <label>Reference Number:</label>
                        <p><?php echo htmlspecialchars($mpl['reference_num']); ?></p>
                    </div>
                    <div class="detail-field">
                        <label>Trailer Number:</label>
                        <p><?php echo htmlspecialchars($mpl['trailer_number']); ?></p>
                    </div>
                    <div class="detail-field">
                        <label>Expected Arrival:</label>
                        <p><?php echo htmlspecialchars(date('m-d-y', strtotime($mpl['expected_arrival']))); ?></p>
                    </div>
                    <div class="detail-field">
                        <label>Status:</label>
                        <p><?php echo htmlspecialchars(ucfirst($mpl['status'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- line items table -->
            <div class="internal-inventory-action-card">
                <h3 class="color-text-primary">Line items: <?php echo $item_count; ?> units</h3>
            </div>

            <div class="sku-table-container">
                <table class="sku-table">
                    <thead>
                        <tr>
                            <th>Unit ID</th>
                            <th>SKU</th>
                            <th>Description</th>
                            <th>Date received</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999;">No items in this MPL.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['unit_number']); ?></td>
                            <td><?php echo htmlspecialchars($item['sku'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($item['description'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars(date('m-d-y', strtotime($item['ship_date'] ?? ''))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>
<?php $connection->close(); ?>