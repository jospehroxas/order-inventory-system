<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

// Handle form submission for adding/updating products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $id = $_POST['id'] ?? null; // Get product ID if updating

    try {
        if ($id) { // Update product
            $stmt = $pdo->prepare("UPDATE products SET name = ?, quantity = ?, price = ? WHERE id = ?");
            $stmt->execute([$name, $quantity, $price, $id]);
            $message = "Product updated successfully!";
        } else { // Create product
            $stmt = $pdo->prepare("INSERT INTO products (name, quantity, price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $quantity, $price]);
            $message = "Product added successfully!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // First delete orders that reference the product
        $stmt = $pdo->prepare("DELETE FROM orders WHERE product_id = ?");
        $stmt->execute([$id]);

        // Now delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Product and related orders deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch products
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if editing a product
$idToEdit = $_GET['edit'] ?? null;
$productToEdit = null;
if ($idToEdit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$idToEdit]);
    $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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

    <h1>Manage Products</h1>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo isset($productToEdit) ? htmlspecialchars($productToEdit['id']) : ''; ?>">
        <input type="text" name="name" placeholder="Product Name" value="<?php echo isset($productToEdit) ? htmlspecialchars($productToEdit['name']) : ''; ?>" required>
        <input type="number" name="quantity" placeholder="Quantity" value="<?php echo isset($productToEdit) ? htmlspecialchars($productToEdit['quantity']) : ''; ?>" required>
        <input type="text" name="price" placeholder="Price" value="<?php echo isset($productToEdit) ? htmlspecialchars($productToEdit['price']) : ''; ?>" required>
        <input type="submit" value="Save Product">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
    </form>

    <h2>Existing Products</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td>
                    <a href="?edit=<?php echo $product['id']; ?>">Edit</a>
                    <a href="?delete=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
