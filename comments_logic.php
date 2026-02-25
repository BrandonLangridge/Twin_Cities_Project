<?php
/* comments_logic.php */

// Link to config.php
// This provides the $pdo connection and ensures consistent DB settings.
require_once 'config.php'; 

// SESSION & CACHING INITIALISATION
// Check if a session is active. If not, we start one to store cached database results.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* FETCH LOGIC (Read)
   Purpose: Retrieves comments for a specific city while implementing caching. */
function getCommentsForCity($cityId, $pdo) {
    // CACHE CHECK 
    if (isset($_SESSION['comment_cache'][$cityId])) {
        return $_SESSION['comment_cache'][$cityId];
    }

    // SQL QUERY
    $sql = "SELECT * FROM Comments 
            WHERE city_id = :cid 
            ORDER BY created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    
    // Using Prepared Statements to prevent SQL Injection.
    $stmt->execute([
        'cid' => $cityId
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // STORE IN CACHE
    $_SESSION['comment_cache'][$cityId] = $results;

    return $results;
}

/* POST LOGIC (Create)
   Processes new comment submissions from the form. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    
    $cityId  = $_POST['city_id'] ?? null;
    
    // SECURITY, htmlspecialchars() prevents XSS attacks.
    $user    = htmlspecialchars($_POST['user_name'] ?? 'Anonymous');
    $comment = htmlspecialchars($_POST['comment_text'] ?? '');
    
    // Empty string to keep data clean
    $query   = $_POST['search_param'] ?? '';

    if ($cityId && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Comments (user_name, comment_text, search_query, city_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user, $comment, $query, $cityId]);

            // CACHE INVALIDATION 
            unset($_SESSION['comment_cache'][$cityId]);

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } catch (PDOException $e) {
            die("Error saving comment: " . $e->getMessage());
        }
    } else {
        die("Error: Missing city ID or comment text.");
    }
}

/* DELETE LOGIC
   Permanently removes a specific comment from the database. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    
    $deleteId = $_POST['delete_id'] ?? null;
    $cityId   = $_POST['city_id'] ?? null;

    if ($deleteId && $cityId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Comments WHERE comments_id = ?");
            $stmt->execute([$deleteId]);

            // CACHE INVALIDATION 
            unset($_SESSION['comment_cache'][$cityId]);

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } catch (PDOException $e) {
            die("Error deleting comment: " . $e->getMessage());
        }
    } else {
        die("Error: Missing comment ID for deletion.");
    }
}
?>