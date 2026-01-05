<?php
/**
 * MULTIMEDIA ELEMENT (DATA MODELLING)
 * This script serves as the backend API for the statistics dashboard.
 * It parses and aggregates data from the 'articles', 'visits', and 'comments' tables 
 * to generate a statistical comparison of user engagement.
 */

require_once 'classes/Database.php';
header('Content-Type: application/json');

try {
    $conn = Database::getInstance()->getConnection();
    $data = [['Article', 'Clicks', 'Comments']];

    if ($conn) {
        // Query to get top 5 articles by clicks and their comment counts
        $sql = "SELECT a.title, 
                (SELECT COUNT(*) FROM visits v WHERE v.page_visited LIKE CONCAT('%id=', a.article_id)) as clicks,
                (SELECT COUNT(*) FROM comments c WHERE c.article_id = a.article_id) as comments
                FROM articles a
                ORDER BY clicks DESC LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($rows as $row) {
            $data[] = [$row['title'], (int)$row['clicks'], (int)$row['comments']];
        }
    }
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode([["Error", $e->getMessage()]]);
}