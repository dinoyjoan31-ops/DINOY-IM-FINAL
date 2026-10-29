<?php
/**
 * Delete Recipe Action
 * Handles deleting recipes from the database
 */

require_once '../classes/Recipe.php';
require_once '../config/database.php';

// Check if recipe ID is provided
if (empty($_GET['id']) && empty($_POST['recipe_id'])) {
    header('Location: ../view.php?error=Recipe ID is required');
    exit;
}

$id = $_GET['id'] ?? $_POST['recipe_id'] ?? null;

try {
    // Create new Recipe instance
    $recipe = new Recipe();
    
    // Get recipe details for the success message
    $recipeData = $recipe->getRecipeById($id);
    $recipeTitle = $recipeData['success'] ? $recipeData['data']['recipe_title'] : 'Recipe';
    
    // Delete recipe from database
    $result = $recipe->deleteRecipe($id);
    
    if ($result['success']) {
        // Redirect to view page with success message
        header('Location: ../view.php?success=🗑️ Recipe "' . urlencode($recipeTitle) . '" deleted successfully!');
        exit;
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    // Redirect to view page with error message
    header('Location: ../view.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>