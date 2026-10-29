<?php
require_once 'classes/Recipe.php';

$recipe = new Recipe();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: view.php?error=Recipe ID required');
    exit;
}

// Get recipe details
$result = $recipe->getRecipeById($id);
if (!$result['success']) {
    header('Location: view.php?error=Recipe not found');
    exit;
}
$recipeData = $result['data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe = new Recipe();
    $recipe->setTitle($_POST['recipe_title']);
    $recipe->setDescription($_POST['description']);
    $recipe->setImageUrl($_POST['image_url']);
    $recipe->setPrepTime($_POST['prep_time']);
    $recipe->setCookingTime($_POST['cooking_time']);
    $recipe->setServings($_POST['servings']);
    
    // Process ingredients
    $ingredients = [];
    if (isset($_POST['ingredient_id']) && isset($_POST['ingredient_quantity'])) {
        for ($i = 0; $i < count($_POST['ingredient_id']); $i++) {
            if (!empty($_POST['ingredient_id'][$i]) && !empty($_POST['ingredient_quantity'][$i])) {
                $ingredients[] = [
                    'ingredient_id' => $_POST['ingredient_id'][$i],
                    'quantity' => $_POST['ingredient_quantity'][$i]
                ];
            }
        }
    }
    $recipe->setIngredients($ingredients);
    
    $updateResult = $recipe->updateRecipe($id);
    
    if ($updateResult['success']) {
        header('Location: view.php?success=✏️ Recipe updated successfully');
        exit;
    } else {
        $error = $updateResult['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Edit Recipe · Smart Cafe</title>
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
            <h1>✏️ <span>Edit</span> Recipe</h1>
            <p class="subtitle">
                <i class="fas fa-mug-saucer"></i> 
                Update your recipe details and ingredients
                <i class="fas fa-mug-saucer"></i>
            </p>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <span class="alert-icon">❌</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div class="form-container">
            <div class="form-title">
                <i class="fas fa-edit" style="color: var(--secondary);"></i>
                Edit Recipe: <?php echo htmlspecialchars($recipeData['recipe_title']); ?>
            </div>
            <p class="form-subtitle">Update the details below to modify your recipe.</p>

            <form method="POST" action="" id="recipeForm">
                <!-- Recipe Title -->
                <div class="form-group">
                    <label for="recipe_title">
                        Recipe Title <span class="required">*</span>
                    </label>
                    <input type="text" id="recipe_title" name="recipe_title" 
                           value="<?php echo htmlspecialchars($recipeData['recipe_title']); ?>" 
                           placeholder="e.g., Creamy Garlic Pasta" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Describe your recipe..."><?php echo htmlspecialchars($recipeData['description']); ?></textarea>
                </div>

                <!-- Image URL -->
                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="url" id="image_url" name="image_url" 
                           value="<?php echo htmlspecialchars($recipeData['image_url']); ?>" 
                           placeholder="https://example.com/delicious-food.jpg">
                    <div class="image-preview" id="imagePreview" 
                         style="<?php echo !empty($recipeData['image_url']) ? 'display: block;' : 'display: none;'; ?>">
                        <img id="previewImage" src="<?php echo htmlspecialchars($recipeData['image_url']); ?>" 
                             alt="Recipe image preview">
                    </div>
                </div>

                <!-- Time & Servings Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="prep_time">
                            <i class="far fa-clock" style="color: var(--secondary);"></i>
                            Prep Time (minutes)
                        </label>
                        <input type="number" id="prep_time" name="prep_time" 
                               value="<?php echo $recipeData['prep_time']; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="cooking_time">
                            <i class="fas fa-fire" style="color: var(--secondary);"></i>
                            Cooking Time (minutes)
                        </label>
                        <input type="number" id="cooking_time" name="cooking_time" 
                               value="<?php echo $recipeData['cooking_time']; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="servings">
                            <i class="fas fa-utensils" style="color: var(--secondary);"></i>
                            Servings
                        </label>
                        <input type="number" id="servings" name="servings" 
                               value="<?php echo $recipeData['servings']; ?>" min="1">
                    </div>
                </div>

                <!-- Ingredients Section -->
                <div class="ingredients-section">
                    <h3>
                        <i class="fas fa-carrot" style="color: var(--secondary);"></i>
                        Ingredients
                        <span style="font-size: 0.8rem; font-weight: 400; color: var(--gray); font-family: var(--font-secondary);">
                            (Add all ingredients needed)
                        </span>
                    </h3>
                    
                    <div id="ingredients-container">
                        <?php if (!empty($recipeData['ingredients'])): ?>
                            <?php foreach ($recipeData['ingredients'] as $ing): ?>
                                <div class="ingredient-row">
                                    <div class="form-group">
                                        <label>Ingredient</label>
                                        <select name="ingredient_id[]" class="ingredient-select">
                                            <option value="">Select ingredient</option>
                                            <?php
                                            require_once 'config/database.php';
                                            $db = Database::getInstance()->getConnection();
                                            $stmt = $db->query("SELECT ingredient_id, name FROM ingredients ORDER BY name");
                                            while ($ingredient = $stmt->fetch()) {
                                                $selected = ($ingredient['ingredient_id'] == $ing['ingredient_id']) ? 'selected' : '';
                                                echo "<option value='{$ingredient['ingredient_id']}' $selected>{$ingredient['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="text" name="ingredient_quantity[]" 
                                               value="<?php echo htmlspecialchars($ing['quantity']); ?>" 
                                               placeholder="e.g., 2 cups, 500g" class="ingredient-quantity">
                                    </div>
                                    <button type="button" onclick="removeIngredient(this)" class="btn-small btn-small-danger" style="margin-top: 6px;">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="ingredient-row">
                                <div class="form-group">
                                    <label>Ingredient</label>
                                    <select name="ingredient_id[]" class="ingredient-select">
                                        <option value="">Select ingredient</option>
                                        <?php
                                        require_once 'config/database.php';
                                        $db = Database::getInstance()->getConnection();
                                        $stmt = $db->query("SELECT ingredient_id, name FROM ingredients ORDER BY name");
                                        while ($ingredient = $stmt->fetch()) {
                                            echo "<option value='{$ingredient['ingredient_id']}'>{$ingredient['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="text" name="ingredient_quantity[]" 
                                           placeholder="e.g., 2 cups, 500g" class="ingredient-quantity">
                                </div>
                                <button type="button" onclick="removeIngredient(this)" class="btn-small btn-small-danger" style="margin-top: 6px;">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" onclick="addIngredient()" class="btn btn-secondary add-ingredient-btn" style="padding: 10px 24px; font-size: 0.85rem;">
                        <i class="fas fa-plus-circle"></i>
                        Add More Ingredient
                    </button>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Recipe
                    </button>
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ============================================
        // IMAGE PREVIEW
        // ============================================
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');

        if (imageUrlInput) {
            imageUrlInput.addEventListener('input', function() {
                const url = this.value.trim();
                if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                    previewImage.src = url;
                    imagePreview.style.display = 'block';
                    previewImage.onerror = function() {
                        imagePreview.style.display = 'none';
                    };
                } else {
                    imagePreview.style.display = 'none';
                }
            });
        }

        // ============================================
        // ADD INGREDIENT ROW
        // ============================================
        function addIngredient() {
            const container = document.getElementById('ingredients-container');
            const row = document.createElement('div');
            row.className = 'ingredient-row';
            
            // Get the current select options from the first row
            const firstSelect = container.querySelector('.ingredient-select');
            let selectHTML = '<option value="">Select ingredient</option>';
            
            if (firstSelect) {
                const options = firstSelect.querySelectorAll('option');
                options.forEach(opt => {
                    selectHTML += `<option value="${opt.value}">${opt.textContent}</option>`;
                });
            }
            
            row.innerHTML = `
                <div class="form-group">
                    <label>Ingredient</label>
                    <select name="ingredient_id[]" class="ingredient-select">
                        ${selectHTML}
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="text" name="ingredient_quantity[]" placeholder="e.g., 2 cups, 500g" class="ingredient-quantity">
                </div>
                <button type="button" onclick="removeIngredient(this)" class="btn-small btn-small-danger" style="margin-top: 6px;">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `;
            
            container.appendChild(row);
            
            // Animate the new row
            row.style.opacity = '0';
            row.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 10);
            
            // Focus on the new quantity input
            const newInput = row.querySelector('.ingredient-quantity');
            if (newInput) {
                setTimeout(() => newInput.focus(), 200);
            }
        }

        // ============================================
        // REMOVE INGREDIENT ROW
        // ============================================
        function removeIngredient(button) {
            const container = document.getElementById('ingredients-container');
            if (container.children.length <= 1) {
                showAlert('You need at least one ingredient row.', 'warning');
                return;
            }
            
            const row = button.parentElement;
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                row.remove();
            }, 300);
        }

        // ============================================
        // SHOW ALERT FUNCTION
        // ============================================
        function showAlert(message, type = 'info') {
            const existingAlerts = document.querySelectorAll('.custom-alert');
            existingAlerts.forEach(alert => alert.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert alert alert-${type}`;
            alertDiv.innerHTML = `
                <span class="alert-icon">${type === 'warning' ? '⚠️' : 'ℹ️'}</span>
                ${message}
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.querySelector('.form-container'));
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.transition = 'opacity 0.5s ease';
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 500);
                }
            }, 4000);
        }

        // ============================================
        // FORM VALIDATION
        // ============================================
        document.getElementById('recipeForm').addEventListener('submit', function(e) {
            const title = document.getElementById('recipe_title');
            if (title.value.trim() === '') {
                e.preventDefault();
                showAlert('Recipe title is required!', 'error');
                title.focus();
                return false;
            }
            
            const prepTime = document.getElementById('prep_time');
            const cookTime = document.getElementById('cooking_time');
            
            if (parseInt(prepTime.value) < 0) {
                e.preventDefault();
                showAlert('Prep time cannot be negative!', 'error');
                prepTime.focus();
                return false;
            }
            
            if (parseInt(cookTime.value) < 0) {
                e.preventDefault();
                showAlert('Cooking time cannot be negative!', 'error');
                cookTime.focus();
                return false;
            }
            
            const servings = document.getElementById('servings');
            if (parseInt(servings.value) < 1) {
                e.preventDefault();
                showAlert('Servings must be at least 1!', 'error');
                servings.focus();
                return false;
            }
            
            return true;
        });

        // ============================================
        // KEYBOARD SHORTCUTS
        // ============================================
        document.addEventListener('keydown', function(e) {
            // Escape key to go back
            if (e.key === 'Escape') {
                const cancelLink = document.querySelector('a[href="view.php"]');
                if (cancelLink && !e.target.closest('input, textarea, select')) {
                    window.location.href = cancelLink.href;
                }
            }
        });
    </script>
</body>
</html>