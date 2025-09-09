<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Product Catalog System'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">
                <i class="fas fa-shopping-bag"></i>
                Product Catalog
            </h1>
            <nav class="nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="index.php#add-product" class="nav-link">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container"><?php
// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>