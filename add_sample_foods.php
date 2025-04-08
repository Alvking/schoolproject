<?php
require_once 'db_connection.php';

$sample_foods = [
    ['name' => 'Chicken Breast', 'calories_per_100g' => 165, 'protein_per_100g' => 31, 'carbs_per_100g' => 0, 'fat_per_100g' => 3.6],
    ['name' => 'Brown Rice', 'calories_per_100g' => 112, 'protein_per_100g' => 2.6, 'carbs_per_100g' => 23.5, 'fat_per_100g' => 0.9],
    ['name' => 'Salmon', 'calories_per_100g' => 208, 'protein_per_100g' => 22, 'carbs_per_100g' => 0, 'fat_per_100g' => 13],
    ['name' => 'Banana', 'calories_per_100g' => 89, 'protein_per_100g' => 1.1, 'carbs_per_100g' => 22.8, 'fat_per_100g' => 0.3],
    ['name' => 'Greek Yogurt', 'calories_per_100g' => 59, 'protein_per_100g' => 10.2, 'carbs_per_100g' => 3.6, 'fat_per_100g' => 0.4],
    ['name' => 'Sweet Potato', 'calories_per_100g' => 86, 'protein_per_100g' => 1.6, 'carbs_per_100g' => 20.1, 'fat_per_100g' => 0.1],
    ['name' => 'Eggs', 'calories_per_100g' => 155, 'protein_per_100g' => 12.6, 'carbs_per_100g' => 1.1, 'fat_per_100g' => 11.3],
    ['name' => 'Oatmeal', 'calories_per_100g' => 389, 'protein_per_100g' => 16.9, 'carbs_per_100g' => 66.3, 'fat_per_100g' => 6.9],
    ['name' => 'Broccoli', 'calories_per_100g' => 34, 'protein_per_100g' => 2.8, 'carbs_per_100g' => 6.6, 'fat_per_100g' => 0.4],
    ['name' => 'Almonds', 'calories_per_100g' => 579, 'protein_per_100g' => 21.2, 'carbs_per_100g' => 21.7, 'fat_per_100g' => 49.9]
];

foreach ($sample_foods as $food) {
    $sql = "INSERT INTO food_items (name, calories_per_100g, protein_per_100g, carbs_per_100g, fat_per_100g) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            calories_per_100g = VALUES(calories_per_100g),
            protein_per_100g = VALUES(protein_per_100g),
            carbs_per_100g = VALUES(carbs_per_100g),
            fat_per_100g = VALUES(fat_per_100g)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdddd", 
        $food['name'], 
        $food['calories_per_100g'], 
        $food['protein_per_100g'], 
        $food['carbs_per_100g'], 
        $food['fat_per_100g']
    );
    $stmt->execute();
}

echo "Sample foods added successfully!";
$conn->close();
?>
