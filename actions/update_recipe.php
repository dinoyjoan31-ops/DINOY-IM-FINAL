<?php
/**
 * Update Recipe Action
 * Handles updating existing recipes in the database
 */

require_once '../classes/Recipe.php';
require_once '../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view.php');
    exit;
}

// Check if recipe ID is provided
if (empty($_POST['recipe_id'])) {
    header('Location: ../view.php?error=Recipe ID is required');
    exit;
}

$id = $_POST['recipe_id'];

try {
    // Validate required fields
    if (empty($_POST['recipe_title'])) {
        throw new Exception('Recipe title is required.');
    }

    // Create new Recipe instance
    $recipe = new Recipe();
    
    // Set recipe properties
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
    
    // Update recipe in database
    $result = $recipe->updateRecipe($id);
    
    if ($result['success']) {
        // Redirect to view page with success message
        header('Location: ../view.php?success=✏️ Recipe "' . urlencode($_POST['recipe_title']) . '" updated successfully!');
        exit;
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    // Redirect back to edit page with error message
    header('Location: ../edit.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
    exit;
}
?>