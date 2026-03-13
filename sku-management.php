<?php
require 'db_connect.php';
require './lib/auth.php';

require_login();

$stmt = $connection->prepare("SELECT * FROM cms_products");
$stmt->execute();
$result = $stmt->get_result();
$result_count = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKU Management</title>
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
                <li class="nav-item nav-item--active">
                    <a style="text-decoration: none; color: inherit;" href="sku-management.php"><h5>SKU Management</h5></a>
                </li>
                <li class="nav-item">
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

    <!-- main content -->
    <div class="main-content">
        <h1 class="color-text-primary">SKU Management</h1>

        <div class="sku-action-card">
            <h3 class="color-text-primary">Total # of SKU: <?php echo $result_count; ?></h3>
            <a href="edit-form.php" class="add-sku-button" style="text-decoration: none; color: inherit;">
                <h5>Add SKU</h5>
                <img class="icon" src="./images/plus-icon.png" alt="plus icon">
            </a>
        </div>

        <div class="sku-table-container">
        <table class="sku-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Description</th>
                    <th>UOM</th>
                    <th>Pieces</th>
                    <th>Dimensions</th>
                    <th>Weight</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php
                if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $item_id = $row['id'];
                    $item_sku = $row['sku'];
                    $item_description = $row['description'];
                    $item_uom = $row['uom_primary'];
                    $item_piece = $row['piece_count'];
                    $item_length = $row['length_inches'];
                    $item_width = $row['width_inches'];

                    $item_height = (string)$row['height_inches'];
                    if (str_ends_with($item_height, '.00')) {
                        $item_height = substr($item_height, 0, -3);
                    } elseif (str_ends_with($item_height, '0') && str_contains($item_height, '.')) {
                        $item_height = substr($item_height, 0, -1);
                    }

                    $item_dimension = $item_length . "in x " . $item_width . "in x " . $item_height . 'in';
                    $item_weight = $row['weight_lbs'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item_sku)?></td>
                    <td><?php echo htmlspecialchars($item_description)?></td>
                    <td><?php echo htmlspecialchars($item_uom)?></td>
                    <td><?php echo htmlspecialchars($item_piece)?></td>
                    <td><?php echo htmlspecialchars($item_dimension)?></td>
                    <td><?php echo htmlspecialchars($item_weight)?></td>
                    <td>
                        <a href="edit-form.php?id=<?= $item_id ?>">Edit</a>
                        <a href="delete.php?id=<?= $item_id ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>No SKUs found.</td></tr>";
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