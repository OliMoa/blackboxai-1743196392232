<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$filename = $_GET['file'] ?? null;
if (!$filename) {
    header("Location: backup_system.php");
    exit();
}

// Validate filename to prevent directory traversal
$filepath = "backups/" . basename($filename);
if (!file_exists($filepath)) {
    $_SESSION['error'] = "El archivo de backup no existe";
    header("Location: backup_system.php");
    exit();
}

// Delete the backup file
if (unlink($filepath)) {
    $_SESSION['success'] = "Backup eliminado exitosamente";
} else {
    $_SESSION['error'] = "Error al eliminar el backup";
}

header("Location: backup_system.php");
exit();