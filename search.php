<?php
require_once 'classes/Recipe.php';

$keyword = $_GET['keyword'] ?? '';

if (empty($keyword)) {
    header('Location: index.php');
    exit;
}

$recipe = new Recipe();
$result = $recipe->searchRecipes($keyword);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Search Results · Smart Cafe</title>
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
            <h1>🔍 <span>Search</span> Results</h1>
            <p class="subtitle">
                <i class="fas fa-mug-saucer"></i> 
                Showing results for: <strong style="color: var(--primary);">"<?php echo htmlspecialchars($keyword); ?>"</strong>
                <i class="fas fa-mug-saucer"></i>
            </p>
        </header>

        <!-- Action Buttons -->
        <div class="actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add New Recipe
            </a>
        </div>

        <!-- Results Count -->
        <?php if ($result['success'] && !empty($result['data'])): ?>
            <div style="margin-bottom: 20px; color: var(--gray); font-size: 0.95rem;">
                <i class="fas fa-utensils" style="color: var(--secondary);"></i>
                Found <strong style="color: var(--dark);"><?php echo count($result['data']); ?></strong> recipe(s) matching your search
            </div>
        <?php endif; ?>

        <!-- Search Results -->
        <?php if ($result['success'] && !empty($result['data'])): ?>
            <div class="recipe-grid">
                <?php foreach ($result['data'] as $recipe): ?>
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
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <span class="empty-icon">🔍</span>
                <h3>No recipes found</h3>
                <p>We couldn't find any recipes matching "<strong><?php echo htmlspecialchars($keyword); ?></strong>".</p>
                <p style="font-size: 0.9rem; color: var(--gray);">
                    Try searching with different keywords or 
                    <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        <i class="fas fa-arrow-left"></i> return to dashboard
                    </a>
                </p>
                <a href="add.php" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fas fa-plus-circle"></i>
                    Add Your First Recipe
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
