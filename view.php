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
    <title>📋 All Recipes · Smart Cafe</title>
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
            <h1>📋 <span>All</span> Recipes</h1>
            <p class="subtitle">
                <i class="fas fa-mug-saucer"></i> 
                Complete collection of your culinary creations
                <i class="fas fa-mug-saucer"></i>
            </p>
        </header>

        <!-- Alert Messages -->
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

        <!-- Action Buttons -->
        <div class="actions">
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Add New Recipe
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Stats -->
        <?php if ($result['success'] && !empty($result['data'])): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-icon">📚</span>
                    <div class="stat-info">
                        <div class="stat-label">Total Recipes</div>
                        <div class="stat-number"><?php echo count($result['data']); ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">⏱️</span>
                    <div class="stat-info">
                        <div class="stat-label">Total Prep Time</div>
                        <div class="stat-number">
                            <?php 
                            $totalPrep = array_sum(array_column($result['data'], 'prep_time'));
                            echo $totalPrep . ' min';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon">🍽️</span>
                    <div class="stat-info">
                        <div class="stat-label">Total Servings</div>
                        <div class="stat-number">
                            <?php 
                            $totalServings = array_sum(array_column($result['data'], 'servings'));
                            echo $totalServings;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recipe Table -->
        <?php if ($result['success'] && !empty($result['data'])): ?>
            <div class="table-wrapper">
                <table class="recipe-table" id="recipeTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Recipe</th>
                            <th>Author</th>
                            <th>Prep</th>
                            <th>Cook</th>
                            <th>Servings</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['data'] as $index => $recipe): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($recipe['recipe_title']); ?></strong>
                                    <?php if (!empty($recipe['description'])): ?>
                                        <br><small style="color: var(--gray); font-size: 0.8rem;">
                                            <?php echo htmlspecialchars(substr($recipe['description'], 0, 50)) . '...'; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($recipe['author'] ?? 'Unknown'); ?></td>
                                <td><?php echo $recipe['prep_time']; ?> min</td>
                                <td><?php echo $recipe['cooking_time']; ?> min</td>
                                <td><?php echo $recipe['servings']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <a href="edit.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn-small btn-small-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn-small btn-small-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Export Options -->
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
                <button onclick="window.print()" class="btn btn-secondary" style="padding: 8px 20px; font-size: 0.85rem;">
                    <i class="fas fa-print"></i> Print
                </button>
                <button onclick="exportToCSV('recipeTable', 'recipes.csv')" class="btn btn-secondary" style="padding: 8px 20px; font-size: 0.85rem;">
                    <i class="fas fa-file-export"></i> Export CSV
                </button>
            </div>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <span class="empty-icon">📖</span>
                <h3>No recipes yet</h3>
                <p>Start building your recipe collection today!</p>
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
