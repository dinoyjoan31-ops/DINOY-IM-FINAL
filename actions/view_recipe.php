<?php
/**
 * View All Recipes Action
 * Fetches all recipes from the database
 */

require_once '../classes/Recipe.php';
require_once '../config/database.php';

try {
    // Create new Recipe instance
    $recipe = new Recipe();
    
    // Get all recipes
    $result = $recipe->getRecipes();
    
    // Store results in session or pass through redirect
    if ($result['success']) {
        // You can use session to pass data
        session_start();
        $_SESSION['recipes'] = $result['data'];
        $_SESSION['recipe_count'] = count($result['data']);
        header('Location: ../view.php');
        exit;
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    header('Location: ../view.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
