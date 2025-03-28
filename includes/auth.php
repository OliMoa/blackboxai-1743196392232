<?php
session_start();

// Simple authentication without password hashing
function authenticate($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit();
    }
}

// Get current user data
function currentUser() {
    return $_SESSION['user'] ?? null;
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header("Location: /login.php");
    exit();
}

// Check user role
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user']['role'] === $role;
}
?>