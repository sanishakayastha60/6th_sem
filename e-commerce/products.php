<?php
// DB connection
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

// Recursive category option builder
function buildCategoryOptions($pdo, $parentId = null, $prefix = '', $selected = null) {
    $sql = "SELECT category_id, name FROM Categories WHERE parent_id ";
    $sql .= is_null($parentId) ? "IS NULL" : "= ?";
    $sql .= " ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    is_null($parentId) ? $stmt->execute() : $stmt->execute([$parentId]);

    $options = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isSelected = ($selected == $row['category_id']) ? 'selected' : '';
        $options .= "<option value='{$row['category_id']}' $isSelected>{$prefix}" . htmlspecialchars($row['name']) . "</option>";
        $options .= buildCategoryOptions($pdo, $row['category_id'], $prefix . '→ ', $selected);
    }
    return $options;
}

// Handle product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';

    if ($name && $price && $category_id) {
        $stmt = $pdo->prepare("INSERT INTO Products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $description, $price, $category_id]);
            echo "<p style='color: green;'>✅ Product added successfully.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Please fill all required fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f9f9f9; }
        form, table { background: white; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        input, textarea, select { width: 100%; padding: 10px; margin: 8px 0 16px; }
        button { padding: 8px 15px; cursor: pointer; border: none; }
        button.add-cart { background-color: #28a745; color: white; }
        button.add-wishlist { background-color: #ff4081; color: white; }
        button:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f0f0f0; }
        .product-actions { display: flex; gap: 10px; }
    </style>
</head>
<body>

<h2>Add New Product</h2>
<form method="POST" action="products.php">
    <input type="hidden" name="submit_product" value="1">
    <label>Product Name:</label>
    <input type="text" name="name" required>

    <label>Description:</label>
    <textarea name="description" rows="3"></textarea>

    <label>Price:</label>
    <input type="number" name="price" step="0.01" required>

    <label>Category:</label>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php echo buildCategoryOptions($pdo); ?>
    </select>

    <button type="submit" style="background-color:#007bff; color:white;">Add Product</button>
</form>

<h2>Browse Products</h2>
<form method="GET" action="products.php">
    <label>Filter by Category:</label>
    <select name="filter_category" onchange="this.form.submit()">
        <option value="">-- All Categories --</option>
        <?php echo buildCategoryOptions($pdo, null, '', $_GET['filter_category'] ?? null); ?>
    </select>
</form>

<?php
$filter_category = $_GET['filter_category'] ?? null;

try {
    if ($filter_category) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name
            FROM Products p
            JOIN Categories c ON p.category_id = c.category_id
            WHERE p.category_id = ?
            ORDER BY p.product_id DESC
        ");
        $stmt->execute([$filter_category]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.name AS category_name
            FROM Products p
            JOIN Categories c ON p.category_id = c.category_id
            ORDER BY p.product_id DESC
        ");
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products):
?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['product_id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td>Rs. <?= number_format($product['price'], 2) ?></td>
                    <td>
                        <div class="product-actions">
                            <!-- Add to Cart -->
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="add-cart">Add to Cart</button>
                            </form>

                            <!-- Add to Wishlist -->
                            <form method="POST" action="wishlist.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <button type="submit" class="add-wishlist">Wishlist</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php
    else:
        echo "<p>No products found.</p>";
    endif;
} catch (PDOException $e) {
    echo "<p>Error fetching products: " . $e->getMessage() . "</p>";
}
?>

</body>
</html>
