<?php
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $description = $_POST['description'] ?? '';

    if (empty($name)) {
        echo "<p style='color:red;'>Category name is required.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO Categories (name, parent_id, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $parent_id ?: null, $description]);
            echo "<p style='color:green;'>Category added successfully!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Category Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f9f9f9;
        }
        form, table {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 8px;
            margin: 6px 0 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            border: none;
            padding: 10px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
        }
        table th {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<h2>Add New Category</h2>
<form method="POST" action="categories.php">
    <label for="name">Category Name:</label>
    <input type="text" name="name" required>

    <label for="parent_id">Parent Category:</label>
    <select name="parent_id">
        <option value="">None</option>
        <?php
        // Load all categories for parent selection
        $stmt = $pdo->query("SELECT category_id, name FROM Categories ORDER BY name ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='{$row['category_id']}'>" . htmlspecialchars($row['name']) . "</option>";
        }
        ?>
    </select>

    <label for="description">Description:</label>
    <textarea name="description" rows="4"></textarea>

    <button type="submit">Create Category</button>
</form>

<h2>All Categories</h2>
<?php
try {
    $query = "
        SELECT c.category_id, c.name, c.description, c.created_at, c.updated_at, p.name AS parent_name
        FROM Categories c
        LEFT JOIN Categories p ON c.parent_id = p.category_id
        ORDER BY c.created_at DESC
    ";
    $stmt = $pdo->query($query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($categories) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Parent</th><th>Description</th><th>Created At</th><th>Updated At</th></tr>";
        foreach ($categories as $cat) {
            echo "<tr>";
            echo "<td>{$cat['category_id']}</td>";
            echo "<td>" . htmlspecialchars($cat['name']) . "</td>";
            echo "<td>" . htmlspecialchars($cat['parent_name'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($cat['description']) . "</td>";
            echo "<td>{$cat['created_at']}</td>";
            echo "<td>{$cat['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No categories found.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error loading categories: " . $e->getMessage() . "</p>";
}
?>

</body>
</html>
