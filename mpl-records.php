<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

require 'db_connect.php';
require './lib/auth.php';
require './lib/mpl.php';

require_login();

// get all MPLs
$mpls = get_all_mpls();
$mpl_count = get_mpl_count();

if (isset($_GET['send']) && isset($_GET['id'])) {
    $mpl_id = (int)$_GET['id'];
    $response = send_mpl_to_wms($mpl_id);

    if (!empty($response['success'])) {
        $_SESSION['success'] = "MPL $mpl_id sent to WMS successfully!";
    } else {
        $_SESSION['error'] = "Failed to send MPL $mpl_id: " . ($response['error'] ?? 'Unknown error') 
                   . " — " . ($response['details'] ?? '');
    }

    header('Location: mpl-records.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPL Records - JBC Manufacturing CMS</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sku.css">
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/mpl.css">
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
        <div class="main-content">
            <h1 class="color-text-primary">MPL Records</h1>

            <div class="sku-action-card">
                <h3 class="color-text-primary">Total # of MPLs: <?php echo $mpl_count; ?></h3>
                <a href="mpl-form.php" class="add-sku-button" style="text-decoration: none; color: inherit;">
                    <h5>Create MPL</h5>
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

            <div class="sku-table-container">
                <table class="sku-table">
                    <thead>
                        <tr>
                            <th>Reference #</th>
                            <th>Trailer #</th>
                            <th>Expected Arrival</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($mpls && count($mpls) > 0) {
                            foreach ($mpls as $mpl) {
                                $mpl_id = $mpl['id'];
                                $reference = $mpl['reference_num'];
                                $trailer = $mpl['trailer_number'];
                                $arrival = date('m-d-y', strtotime($mpl['expected_arrival']));
                                $status = $mpl['status'];
                                $item_count = get_mpl_item_count($mpl_id);
                                
                                // status badge class
                                $status_class = 'status-' . $status;
                                $status_display = ucfirst($status);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reference); ?></td>
                            <td><?php echo htmlspecialchars($trailer); ?></td>
                            <td><?php echo htmlspecialchars($arrival); ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_display; ?></span></td>
                            <td><?php echo $item_count; ?> units</td>
                            <td>
                                <?php if ($status === 'draft'): ?>
                                    <a href="mpl-form.php?id=<?= $mpl_id ?>">Edit</a>
                                    <a href="mpl-view.php?id=<?= $mpl_id ?>">View</a>
                                    <a href="delete-mpl.php?id=<?= $mpl_id ?>" onclick="return confirm('Are you sure you want to delete this MPL?');">Delete</a>
                                    <a href="mpl-records.php?id=<?= $mpl_id ?>&send=1" class="btn-send">Send to WMS</a>

                                <?php elseif ($status === 'sent'): ?>
                                    <a href="mpl-view.php?id=<?= $mpl_id ?>">View</a>
                                    <a href="mpl-confirm.php?id=<?= $mpl_id ?>&action=confirm">Confirm</a>
                                <?php else: 
                                    // confirmed ?>
                                    <a href="mpl-view.php?id=<?= $mpl_id ?>">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>No MPLs found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$connection->close();
?>