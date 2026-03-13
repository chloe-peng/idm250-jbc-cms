<?php
require 'db_connect.php';
require './lib/cms.php';
require './lib/auth.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $id ? get_product($id) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id) {
        update_product($id, $_POST);
    } else {
        create_product($_POST);
    }
    header('Location: sku-management.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> SKU - JBC Manufacturing CMS</title>
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
        <div class="page-header">
            <h1 class="color-text-primary"><?php echo $id ? 'Edit' : 'Add'; ?> SKU</h1>
            <div class="breadcrumb">
                <a href="sku-management.php">SKU Management</a> / <?php echo $id ? 'Edit SKU' : 'Add New SKU'; ?>
            </div>
        </div>

        <div class="form-container">
        <form method="POST">
            <div class="form-control">
                <label for="sku">SKU *</label>
                <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>" required>
            </div>

            <div class="form-control">
                <label for="description">Description *</label>
                <input type="text" id="description" name="description" value="<?= htmlspecialchars($product['description'] ?? '') ?>" required>
            </div>

            <div class="form-control">
                <label for="uom">UOM *</label>
                <input type="text" id="uom" name="uom" value="<?= htmlspecialchars($product['uom'] ?? '') ?>" required>
            </div>

            <div class="form-control">
                <label for="piece">Pieces *</label>
                <input type="number" id="piece" name="piece" value="<?= htmlspecialchars($product['piece'] ?? '') ?>" min="1" required>
            </div>

            <div class="form-control">
                <label>Dimensions *</label>
                <div class="form-row">
                    <div>
                        <label for="length" style="font-weight: normal; font-size: 13px;">Length</label>
                        <input type="number" id="length" name="length" value="<?= htmlspecialchars($product['length'] ?? '') ?>" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label for="width" style="font-weight: normal; font-size: 13px;">Width</label>
                        <input type="number" id="width" name="width" value="<?= htmlspecialchars($product['width'] ?? '') ?>" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label for="height" style="font-weight: normal; font-size: 13px;">Height</label>
                        <input type="number" id="height" name="height" value="<?= htmlspecialchars($product['height'] ?? '') ?>" step="0.01" min="0" required>
                    </div>
                </div>
            </div>

            <div class="form-control">
                <label for="weight">Weight (lbs) *</label>
                <input type="number" id="weight" name="weight" value="<?= htmlspecialchars($product['weight'] ?? '') ?>" step="0.01" min="0" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $id ? 'Update SKU' : 'Create SKU' ?>
                </button>
                
                <a href="sku-management.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    </div>
</div>
</body>
</html>