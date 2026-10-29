-- Create database
CREATE DATABASE IF NOT EXISTS recipe_management;
USE recipe_management;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recipes table (Main Entity)
CREATE TABLE recipes (
    recipe_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    recipe_title VARCHAR(200) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    prep_time INT DEFAULT 0,    -- in minutes
    cooking_time INT DEFAULT 0, -- in minutes
    servings INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Ingredients table
CREATE TABLE ingredients (
    ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50),
    quantity_in_stock DECIMAL(10,2) DEFAULT 0,
    low_stock_threshold DECIMAL(10,2) DEFAULT 5
);

-- Recipe Ingredients (Junction Table)
CREATE TABLE recipe_ingredients (
    recipe_ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity VARCHAR(50) NOT NULL, -- e.g., "2 cups", "500g"
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id)
);

-- Orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    total_price DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    recipe_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price_per_unit DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id)
);

-- Pantry Stock table
CREATE TABLE pantry_stock (
    stock_id INT PRIMARY KEY AUTO_INCREMENT,
    ingredient_id INT NOT NULL,
    quantity_added DECIMAL(10,2) DEFAULT 0,
    location VARCHAR(100) DEFAULT 'Pantry',
    expiry_date DATE,
    stock_in_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id)
);

-- Insert sample user
INSERT INTO users (full_name, password_hash) VALUES 
('Admin User', '$2y$10$YourHashedPasswordHere');