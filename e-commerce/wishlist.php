<?php
session_start();

// DB config
$host = 'localhost';
$dbname = 'sanisha_ecomm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize wishlist
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Add to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $product_id = $_POST['product_id'];

    if (!in_array($product_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $product_id;
    }
}

// Remove from wishlist
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$product_id]);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wishlist</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #eee; }
        .remove-btn { color: red; text-decoration: none; font-weight: bold; }
        .add-cart-btn { background: #007bff; color: white; padding: 6px 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>Your Wishlist</h2>

<?php
if (!empty($_SESSION['wishlist'])):
    $placeholders = str_repeat('?,', count($_SESSION['wishlist']) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id IN ($placeholders)");
    $stmt->execute($_SESSION['wishlist']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <table>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= htmlspecialchars($product['description']) ?></td>
            <td>$<?= number_format($product['price'], 2) ?></td>
            <td>
                <form action="cart.php" method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="add-cart-btn" type="submit">Add to Cart</button>
                </form>
                <a class="remove-btn" href="wishlist.php?remove=<?= $product['product_id'] ?>" onclick="return confirm('Remove from wishlist?')">Remove ❌</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Your wishlist is empty.</p>
<?php endif; ?>

</body>
</html>
