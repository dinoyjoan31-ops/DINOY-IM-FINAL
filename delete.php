<?php
require_once 'classes/Recipe.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: view.php?error=Recipe ID required');
    exit;
}

$recipe = new Recipe();
$recipeData = $recipe->getRecipeById($id);

if (!$recipeData['success']) {
    header('Location: view.php?error=Recipe not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $result = $recipe->deleteRecipe($id);
    
    if ($result['success']) {
        header('Location: view.php?success=🗑️ Recipe deleted successfully');
        exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗑️ Delete Recipe · Smart Cafe</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header" style="padding-bottom: 20px;">
            <h1>🗑️ <span>Delete</span> Recipe</h1>
            <p class="subtitle">
                <i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i> 
                Please confirm before deleting this recipe
                <i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i>
            </p>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <span class="alert-icon">❌</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Confirmation Box -->
        <div class="confirm-box">
            <span class="confirm-icon">⚠️</span>
            <h2>Delete Recipe?</h2>
            <p>
                Are you sure you want to delete the recipe 
                <span class="recipe-name">"<?php echo htmlspecialchars($recipeData['data']['recipe_title']); ?>"</span>?
            </p>
            <div class="warning">
                <i class="fas fa-exclamation-circle"></i>
                This action cannot be undone. All associated data will be permanently removed.
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="confirm" value="1">
                <div class="form-actions" style="justify-content: center; border-top: none; padding-top: 0; gap: 15px;">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Yes, Delete Recipe
                    </button>
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>