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

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch product info from DB
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
    }
}

// Update cart
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = max(1, (int)$qty);
        }
    }
}

// Remove item
if (isset($_GET['remove'])) {
    $removeId = $_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <style>
        body { font-family: Arial; padding: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background-color: #f4f4f4; }
        input[type='number'] { width: 60px; }
        .actions { margin-top: 20px; }
        button { padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Shopping Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>
<form method="POST" action="cart.php">
    <input type="hidden" name="action" value="update">
    <table>
        <tr>
            <th>Product</th>
            <th>Price (each)</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Remove</th>
        </tr>
        <?php $total = 0; ?>
        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
            <?php $lineTotal = $item['price'] * $item['quantity']; ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>
                    <input type="number" name="quantities[<?= $id ?>]" value="<?= $item['quantity'] ?>" min="1">
                </td>
                <td>$<?= number_format($lineTotal, 2) ?></td>
                <td><a href="cart.php?remove=<?= $id ?>" onclick="return confirm('Remove item?')">❌</a></td>
            </tr>
            <?php $total += $lineTotal; ?>
        <?php endforeach; ?>
        <tr>
            <th colspan="3" style="text-align: right;">Total:</th>
            <th colspan="2">Rs. <?= number_format($total, 2) ?></th>
        </tr>
    </table>

    <div class="actions">
        <button type="submit">Update Cart</button>
        <a href="cart.php?clear=1" onclick="return confirm('Clear entire cart?')">
            <button type="button">Clear Cart</button>
        </a>
    </div>
</form>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>

</body>
</html>
