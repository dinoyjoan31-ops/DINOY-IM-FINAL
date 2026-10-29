<?php
/**
 * Search Recipes Action
 * Handles searching for recipes in the database
 */

require_once '../classes/Recipe.php';
require_once '../config/database.php';

// Check if search keyword is provided
$keyword = $_GET['keyword'] ?? $_POST['keyword'] ?? '';

if (empty($keyword)) {
    header('Location: ../index.php');
    exit;
}

// Redirect to search page with keyword
header('Location: ../search.php?keyword=' . urlencode($keyword));
exit;
?>