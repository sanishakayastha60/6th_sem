<?php
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($full_name) || empty($email) || empty($password)) {
        die("<p style='font-family:sans-serif; color:red;'>All fields are required.</p>");
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO Customers (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$full_name, $email, $hashedPassword]);
        echo "<p style='font-family:sans-serif; color:green;'>Registration successful!</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<p style='font-family:sans-serif; color:red;'>Email already exists. Try another one.</p>";
        } else {
            echo "<p style='font-family:sans-serif; color:red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
try {
    $stmt = $pdo->query("SELECT customer_id, full_name, email FROM Customers ORDER BY customer_id ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($customers)) {
        echo "<p>No customers found.</p>";
    } else {
        echo "<h2>Customer List</h2>";
        echo "<table border='1' cellpadding='10' cellspacing='0' style='font-family:sans-serif;'>";
        echo "<tr><th>Customer ID</th><th>Full Name</th><th>Email</th></tr>";

        foreach ($customers as $customer) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($customer['customer_id']) . "</td>";
            echo "<td>" . htmlspecialchars($customer['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($customer['email']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p>Error fetching customers: " . $e->getMessage() . "</p>";
}
?>
