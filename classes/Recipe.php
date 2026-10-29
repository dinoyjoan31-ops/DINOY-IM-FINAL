<?php
require_once __DIR__ . '/../config/database.php';

class Recipe {
    private $db;
    private $recipe_id;
    private $user_id;
    private $recipe_title;
    private $description;
    private $image_url;
    private $prep_time;
    private $cooking_time;
    private $servings;
    private $ingredients = [];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Getters and Setters
    public function setRecipeId($id) { $this->recipe_id = $id; }
    public function getRecipeId() { return $this->recipe_id; }
    
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setTitle($title) { $this->recipe_title = $title; }
    public function setDescription($desc) { $this->description = $desc; }
    public function setImageUrl($url) { $this->image_url = $url; }
    public function setPrepTime($time) { $this->prep_time = $time; }
    public function setCookingTime($time) { $this->cooking_time = $time; }
    public function setServings($servings) { $this->servings = $servings; }
    public function setIngredients($ingredients) { $this->ingredients = $ingredients; }
    
    // CRUD Operations
    
    /**
     * Add a new recipe
     */
    public function addRecipe() {
        try {
            $this->db->beginTransaction();
            
            // Insert recipe
            $sql = "INSERT INTO recipes (user_id, recipe_title, description, image_url, prep_time, cooking_time, servings) 
                    VALUES (:user_id, :title, :desc, :image_url, :prep_time, :cooking_time, :servings)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $this->user_id,
                ':title' => $this->recipe_title,
                ':desc' => $this->description,
                ':image_url' => $this->image_url,
                ':prep_time' => $this->prep_time,
                ':cooking_time' => $this->cooking_time,
                ':servings' => $this->servings
            ]);
            
            $recipeId = $this->db->lastInsertId();
            
            // Insert ingredients if any
            if (!empty($this->ingredients)) {
                $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES (:recipe_id, :ingredient_id, :quantity)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($this->ingredients as $ingredient) {
                    $stmt->execute([
                        ':recipe_id' => $recipeId,
                        ':ingredient_id' => $ingredient['ingredient_id'],
                        ':quantity' => $ingredient['quantity']
                    ]);
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'recipe_id' => $recipeId];
            
        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get all recipes
     */
    public function getRecipes() {
        try {
            $sql = "SELECT r.*, u.full_name as author 
                    FROM recipes r 
                    LEFT JOIN users u ON r.user_id = u.user_id 
                    ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $recipes = $stmt->fetchAll();
            
            // Fetch ingredients for each recipe
            foreach ($recipes as &$recipe) {
                $recipe['ingredients'] = $this->getRecipeIngredients($recipe['recipe_id']);
            }
            
            return ['success' => true, 'data' => $recipes];
            
        } catch(PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update a recipe
     */
    public function updateRecipe($id) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE recipes 
                    SET recipe_title = :title, 
                        description = :desc, 
                        image_url = :image_url, 
                        prep_time = :prep_time, 
                        cooking_time = :cooking_time, 
                        servings = :servings 
                    WHERE recipe_id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $id,
                ':title' => $this->recipe_title,
                ':desc' => $this->description,
                ':image_url' => $this->image_url,
                ':prep_time' => $this->prep_time,
                ':cooking_time' => $this->cooking_time,
                ':servings' => $this->servings
            ]);
            
            // Update ingredients if provided
            if (!empty($this->ingredients)) {
                // Delete existing ingredients
                $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':recipe_id' => $id]);
                
                // Insert new ingredients
                $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) VALUES (:recipe_id, :ingredient_id, :quantity)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($this->ingredients as $ingredient) {
                    $stmt->execute([
                        ':recipe_id' => $id,
                        ':ingredient_id' => $ingredient['ingredient_id'],
                        ':quantity' => $ingredient['quantity']
                    ]);
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'rows_affected' => $stmt->rowCount()];
            
        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete a recipe
     */
    public function deleteRecipe($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete recipe ingredients first (due to foreign key constraint)
            $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // Delete the recipe
            $sql = "DELETE FROM recipes WHERE recipe_id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            $this->db->commit();
            return ['success' => true, 'rows_affected' => $stmt->rowCount()];
            
        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Search for recipes by any field
     */
    public function searchRecipes($keyword) {
        try {
            $sql = "SELECT DISTINCT r.*, u.full_name as author 
                    FROM recipes r 
                    LEFT JOIN users u ON r.user_id = u.user_id 
                    LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id 
                    LEFT JOIN ingredients i ON ri.ingredient_id = i.ingredient_id 
                    WHERE r.recipe_title LIKE :keyword 
                       OR r.description LIKE :keyword 
                       OR u.full_name LIKE :keyword 
                       OR i.name LIKE :keyword 
                    ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':keyword' => '%' . $keyword . '%']);
            
            $recipes = $stmt->fetchAll();
            
            // Fetch ingredients for each recipe
            foreach ($recipes as &$recipe) {
                $recipe['ingredients'] = $this->getRecipeIngredients($recipe['recipe_id']);
            }
            
            return ['success' => true, 'data' => $recipes];
            
        } catch(PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get a single recipe by ID
     */
    public function getRecipeById($id) {
        try {
            $sql = "SELECT r.*, u.full_name as author 
                    FROM recipes r 
                    LEFT JOIN users u ON r.user_id = u.user_id 
                    WHERE r.recipe_id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $recipe = $stmt->fetch();
            
            if ($recipe) {
                $recipe['ingredients'] = $this->getRecipeIngredients($id);
                return ['success' => true, 'data' => $recipe];
            }
            
            return ['success' => false, 'error' => 'Recipe not found'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get ingredients for a specific recipe
     */
    private function getRecipeIngredients($recipeId) {
        try {
            $sql = "SELECT i.name, i.category, ri.quantity 
                    FROM recipe_ingredients ri 
                    JOIN ingredients i ON ri.ingredient_id = i.ingredient_id 
                    WHERE ri.recipe_id = :recipe_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>
