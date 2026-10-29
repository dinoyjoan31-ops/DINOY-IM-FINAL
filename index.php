

<?php
require_once 'classes/Recipe.php';
$recipe = new Recipe();
$result = $recipe->getRecipes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>☕ Recipe Management · Smart Cafe</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>☕ <span>Recipe</span> Management</h1>
            <p class="subtitle">
                <i class="fas fa-mug-saucer"></i> 
                Smart recommendations based on your taste, mood and time of day
                <i class="fas fa-mug-saucer"></i>
            </p>
        </header>

        <!-- AI Feature Suggestions -->
        <div class="ai-features">
            <div class="ai-feature-card">
                <span class="icon">🌅</span>
                <h4>Morning Energy Boost</h4>
                <p>Meet your energy requirements for the morning without compromise</p>
            </div>
            <div class="ai-feature-card">
                <span class="icon">😌</span>
                <h4>Relax Mode Coffee</h4>
                <p>Relax at any moment using our own in-house blend</p>
            </div>
            <div class="ai-feature-card">
                <span class="icon">🍯</span>
                <h4>Sweet Flavor Match</h4>
                <p>Add a touch of your favorite sweet to every cup</p>
            </div>
        </div>

        <!-- Search Bar - FIXED -->
        <div class="search-wrapper">
            <form class="search-bar" action="search.php" method="GET">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Search recipes by title, ingredients, or author..." required>
                    <span class="search-shortcut">⌘K</span>
                </div>
                <button type="submit">
                    <i class="fas fa-arrow-right"></i>
                    Search
                </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="actions">
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add New Recipe
            </a>
            <a href="view.php" class="btn btn-secondary">
                <i class="fas fa-list"></i>
                View All Recipes
            </a>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <span class="alert-icon">❌</span>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Section Title -->
        <div class="section-title">
            <span class="deco-left"></span>
            <i class="fas fa-fire" style="color: var(--secondary);"></i>
            Recent Recipes
            <span class="deco"></span>
        </div>

        <?php if ($result['success'] && !empty($result['data'])): ?>
            <div class="recipe-grid">
                <?php 
                $recentRecipes = array_slice($result['data'], 0, 6);
                foreach ($recentRecipes as $recipe): 
                ?>
                    <div class="recipe-card">
                        <?php if ($recipe['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($recipe['recipe_title']); ?>" 
                                 class="recipe-card-image">
                        <?php else: ?>
                            <div class="recipe-card-image-placeholder">
                                🍽️
                            </div>
                        <?php endif; ?>
                        
                        <div class="recipe-card-body">
                            <h3 class="recipe-card-title"><?php echo htmlspecialchars($recipe['recipe_title']); ?></h3>
                            <div class="recipe-card-author">
                                👨‍🍳 By <span><?php echo htmlspecialchars($recipe['author'] ?? 'Unknown'); ?></span>
                            </div>
                            
                            <div class="recipe-card-meta">
                                <span class="recipe-card-meta-item">
                                    <i class="far fa-clock"></i>
                                    <?php echo $recipe['prep_time'] + $recipe['cooking_time']; ?> min
                                </span>
                                <span class="recipe-card-meta-item">
                                    <i class="fas fa-utensils"></i>
                                    <?php echo $recipe['servings']; ?> servings
                                </span>
                                <span class="recipe-card-meta-item">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($recipe['description'])): ?>
                                <p class="recipe-card-description">
                                    <?php echo htmlspecialchars(substr($recipe['description'], 0, 120)) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($recipe['ingredients'])): ?>
                                <div class="recipe-card-ingredients">
                                    <strong>🥘 Ingredients:</strong>
                                    <?php 
                                    $names = array_column($recipe['ingredients'], 'name');
                                    echo implode(', ', array_slice($names, 0, 4));
                                    if (count($names) > 4) echo ' +' . (count($names) - 4) . ' more';
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="recipe-card-actions">
                                <a href="edit.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn-small btn-small-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn-small btn-small-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($result['data']) > 6): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i>
                        View All <?php echo count($result['data']); ?> Recipes
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <span class="empty-icon">📖</span>
                <h3>No recipes yet</h3>
                <p>Start building your recipe collection today! Add your first recipe and share your culinary creations.</p>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Add Your First Recipe
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
