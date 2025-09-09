<?php
// Load environment configuration first
require_once 'config/environment.php';

session_start();
require_once 'config/database.php';

// Initialize database
$db = new Database();

// Create database and tables if they don't exist
$db->createDatabase();
$pdo = $db->getConnection();

// Create products table if it doesn't exist
if (!$db->tableExists('products')) {
    $sqlFile = file_get_contents('sql/products.sql');
    $pdo->exec($sqlFile);
    $_SESSION['success'] = 'Database initialized successfully with sample data!';
}

// Handle form submission for adding new products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    // Validate input
    $errors = [];
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = 'Valid price is required';
    if (empty($category)) $errors[] = 'Category is required';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, category, description, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $category, $description, $image_url]);
            $_SESSION['success'] = 'Product added successfully!';
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding product. Please try again.';
            error_log("Product addition failed: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = implode(', ', $errors);
    }
}

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = 'Product deleted successfully!';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting product.';
        error_log("Product deletion failed: " . $e->getMessage());
    }
}

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build SQL query with filters
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($categoryFilter)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $_SESSION['error'] = 'Error fetching products.';
    error_log("Product fetch failed: " . $e->getMessage());
}

// Get unique categories for filter dropdown
try {
    $categoryStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
    error_log("Category fetch failed: " . $e->getMessage());
}

$pageTitle = 'Product Catalog - Manage Your Products';
include 'includes/header.php';
?>

<section class="hero">
    <h2>Product Catalog Management System</h2>
    <p>Manage your product inventory with powerful search and filtering capabilities</p>
</section>

<!-- Filter and Search Section -->
<section class="filters">
    <div class="filter-container">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="search">
                    <i class="fas fa-search"></i> Search Products
                </label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                       placeholder="Search by name or description...">
            </div>
            
            <div class="form-group">
                <label for="category">
                    <i class="fas fa-filter"></i> Filter by Category
                </label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
    </div>
</section>

<!-- Products Display Section -->
<section class="products-section">
    <div class="section-header">
        <h3>
            <i class="fas fa-box"></i> 
            Products 
            <span class="product-count">(<?php echo count($products); ?> items)</span>
        </h3>
    </div>

    <?php if (empty($products)): ?>
        <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h4>No products found</h4>
            <p>Try adjusting your search criteria or add a new product below.</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/300x300/f8f9fa/6c757d?text=No+Image'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x300/f8f9fa/6c757d?text=No+Image'">
                        <div class="product-category">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                        
                        <?php if (!empty($product['description'])): ?>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>
                                <?php echo strlen($product['description']) > 100 ? '...' : ''; ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="product-actions">
                            <a href="?delete=<?php echo $product['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Add Product Form Section -->
<section id="add-product" class="add-product-section">
    <div class="section-header">
        <h3><i class="fas fa-plus-circle"></i> Add New Product</h3>
        <p>Fill in the details below to add a new product to your catalog</p>
    </div>

    <form method="POST" class="product-form">
        <input type="hidden" name="action" value="add_product">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Product Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" required 
                       placeholder="Enter product name"
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="price">Price <span class="required">*</span></label>
                <input type="number" id="price" name="price" step="0.01" min="0" required 
                       placeholder="0.00"
                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Electronics" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Computers" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Computers') ? 'selected' : ''; ?>>Computers</option>
                    <option value="Audio" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Audio') ? 'selected' : ''; ?>>Audio</option>
                    <option value="Fashion" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Fashion') ? 'selected' : ''; ?>>Fashion</option>
                    <option value="Home" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Home') ? 'selected' : ''; ?>>Home</option>
                    <option value="Books" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Books') ? 'selected' : ''; ?>>Books</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url" 
                       placeholder="https://example.com/image.jpg"
                       value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Enter product description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset Form
            </button>
        </div>
    </form>
</section>

<?php include 'includes/footer.php'; ?>