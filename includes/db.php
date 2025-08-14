<?php
// Database connection utility for MySQL
require_once __DIR__ . '/config.php';

$pdo_instance = null;

function getDatabase() {
    global $pdo_instance;
    
    // Use singleton pattern to avoid multiple connections
    if ($pdo_instance === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo_instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your MySQL configuration.");
        }
    }
    
    return $pdo_instance;
}

// Helper function to execute queries
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
        throw new Exception("Database query failed: " . $e->getMessage());
    }
}

// Helper function to get single row
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Helper function to get multiple rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Helper function to get count
function fetchCount($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchColumn();
}

// Helper function to get last insert ID
function getLastInsertId() {
    $pdo = getDatabase();
    return $pdo->lastInsertId();
}

// Helper function to begin transaction
function beginTransaction() {
    $pdo = getDatabase();
    return $pdo->beginTransaction();
}

// Helper function to commit transaction
function commitTransaction() {
    $pdo = getDatabase();
    return $pdo->commit();
}

// Helper function to rollback transaction
function rollbackTransaction() {
    $pdo = getDatabase();
    return $pdo->rollBack();
}
?>
