<?php
// CRITICAL: Start the session (required for any page that might use sessions later)
session_start();

// Ensure correct paths to your classes
require_once 'classes/Database.php'; 
require_once 'classes/operatii_db.php'; 

// --- Configuration for testing (UPDATE THESE IDs!) ---
// NOTE: Get these IDs from your phpMyAdmin after running the SQL inserts
$TEST_USER_ID = 1;      
$TEST_CATEGORY_ID = 1;  
$TEST_ARTICLE_ID = 0;  

echo "<h1>OperatiiDB CRUD Verification Test</h1>";
echo "<h2>Configuration: User ID {$TEST_USER_ID}, Category ID {$TEST_CATEGORY_ID}</h2>";
echo "<hr>";


// ===================================
// A. CREATE TEST (C)
// ===================================
echo "<h2>A. Testing CREATE (INSERT)</h2>";
$article_data = [
    // Columns must match your 'articles' table fields exactly!
    'title' => 'Test Article for Deletion - ' . time(),
    'content' => 'This is test content inserted at ' . date('Y-m-d H:i:s'),
    'image_url' => null, 
    'category_id' => $TEST_CATEGORY_ID,
    'author_id' => $TEST_USER_ID
];

try {
    // Calling the static create method
    $TEST_ARTICLE_ID = OperatiiDB::create('articles', $article_data);
    
    if ($TEST_ARTICLE_ID > 0) {
        echo "✅ <b>CREATE SUCCESS:</b> Article added with ID: <b>$TEST_ARTICLE_ID</b>.<br>";
    } else {
        echo "❌ <b>CREATE FAIL:</b> Article not added (ID not returned).<br>";
    }
} catch (Exception $e) {
    echo "❌ <b>CREATE ERROR:</b> " . $e->getMessage() . "<br>";
}
echo "<hr>";


// ===================================
// B. READ TEST (R)
// ===================================
echo "<h2>B. Testing READ (SELECT)</h2>";
try {
    // Read the article just created
    $condition = "WHERE article_id = {$TEST_ARTICLE_ID}";
    $articles = OperatiiDB::read('articles', $condition);
    
    if (!empty($articles)) {
        echo "✅ <b>READ SUCCESS:</b> Found article: " . htmlspecialchars($articles[0]['title']) . "<br>";
    } else {
        echo "❌ <b>READ FAIL:</b> Article ID {$TEST_ARTICLE_ID} not found.<br>";
    }
} catch (Exception $e) {
    echo "❌ <b>READ ERROR:</b> " . $e->getMessage() . "<br>";
}
echo "<hr>";


// ===================================
// C. UPDATE TEST (U)
// ===================================
echo "<h2>C. Testing UPDATE</h2>";
if ($TEST_ARTICLE_ID > 0) {
    $new_title = "Updated Title " . time();
    $update_data = [
        // Data to update (NOTE: OperatiiDB update uses prepared statements for these values)
        'title' => $new_title,
        'content' => 'Content confirmed as updated.'
    ];
    
    // FIX APPLIED: Ensure condition is a clean, correctly formatted SQL string
    $condition = "article_id = {$TEST_ARTICLE_ID}"; 

    try {
        // Call the update method
        OperatiiDB::update('articles', $update_data, $condition);
        
        // Read back the data to confirm update
        $check = OperatiiDB::read('articles', "WHERE article_id = {$TEST_ARTICLE_ID}");
        if ($check && $check[0]['title'] === $new_title) {
            echo "✅ <b>UPDATE SUCCESS:</b> Article ID $TEST_ARTICLE_ID updated successfully.<br>";
        } else {
            echo "❌ <b>UPDATE FAIL:</b> Article updated but title confirmation failed.<br>";
        }
    } catch (Exception $e) {
        echo "❌ <b>UPDATE ERROR:</b> " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Skipping UPDATE test because CREATE failed.<br>";
}
echo "<hr>";


// ===================================
// D. DELETE TEST (D)
// ===================================
echo "<h2>D. Testing DELETE</h2>";
if ($TEST_ARTICLE_ID > 0) {
    
    // FIX APPLIED: Ensure condition is a clean, correctly formatted SQL string
    $condition = "article_id = {$TEST_ARTICLE_ID}"; 
    
    try {
        // Call the delete method
        OperatiiDB::delete('articles', $condition);
        
        // Read back the data to confirm deletion
        $check = OperatiiDB::read('articles', "WHERE article_id = {$TEST_ARTICLE_ID}");
        if (empty($check)) {
            echo "✅ <b>DELETE SUCCESS:</b> Article ID $TEST_ARTICLE_ID deleted successfully.<br>";
        } else {
            echo "❌ <b>DELETE FAIL:</b> Article still exists after delete call.<br>";
        }
    } catch (Exception $e) {
        echo "❌ <b>DELETE ERROR:</b> " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Skipping DELETE test because CREATE failed.<br>";
}
echo "<hr>";

// Final check: If all four tests passed, the core logic is sound.

?>
