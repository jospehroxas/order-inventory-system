<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

// Handle form submission for adding orders
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $userId = $_SESSION['user_id']; // Get the user_id from the session
    $id = $_POST['id'] ?? null; // Get order ID if updating

    try {
        if ($id) { // Update order
            $stmt = $pdo->prepare("UPDATE orders SET product_id = ?, quantity = ? WHERE id = ?");
            $stmt->execute([$productId, $quantity, $id]);
            $success = "Order updated successfully!";
        } else { // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (product_id, quantity, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$productId, $quantity, $userId]);
            $success = "Order placed successfully!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle order deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Order deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing orders
$stmt = $pdo->prepare("SELECT orders.*, products.name AS product_name FROM orders JOIN products ON orders.product_id = products.id");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products for the order form
$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if editing an order
$idToEdit = $_GET['edit'] ?? null;
$orderToEdit = null;
if ($idToEdit) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$idToEdit]);
    $orderToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_orders.php">Manage Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <h1>Manage Orders</h1>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo isset($orderToEdit) ? htmlspecialchars($orderToEdit['id']) : ''; ?>">
        <select name="product_id" required>
            <option value="">Select a product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo htmlspecialchars($product['id']); ?>" <?php echo isset($orderToEdit) && $orderToEdit['product_id'] == $product['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($product['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantity" placeholder="Quantity" value="<?php echo isset($orderToEdit) ? htmlspecialchars($orderToEdit['quantity']) : ''; ?>" required>
        <input type="submit" value="Save Order">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    </form>

    <h2>Existing Orders</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td>
                    <a href="?edit=<?php echo $order['id']; ?>">Edit</a>
                    <a href="?delete=<?php echo $order['id']; ?>" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
