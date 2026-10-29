<?php
/**
 * Add Recipe Action
 * Handles the addition of new recipes to the database
 */

require_once '../classes/Recipe.php';
require_once '../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../add.php');
    exit;
}

try {
    // Validate required fields
    if (empty($_POST['recipe_title'])) {
        throw new Exception('Recipe title is required.');
    }

    // Create new Recipe instance
    $recipe = new Recipe();
    
    // Set recipe properties
    $recipe->setUserId(1); // Assuming user_id = 1 for demo
    $recipe->setTitle($_POST['recipe_title']);
    $recipe->setDescription($_POST['description'] ?? '');
    $recipe->setImageUrl($_POST['image_url'] ?? '');
    $recipe->setPrepTime($_POST['prep_time'] ?? 0);
    $recipe->setCookingTime($_POST['cooking_time'] ?? 0);
    $recipe->setServings($_POST['servings'] ?? 1);
    
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
    
    // Add recipe to database
    $result = $recipe->addRecipe();
    
    if ($result['success']) {
        // Redirect to view page with success message
        header('Location: ../view.php?success=✨ Recipe "' . urlencode($_POST['recipe_title']) . '" added successfully!');
        exit;
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    // Redirect back to add page with error message
    header('Location: ../add.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>