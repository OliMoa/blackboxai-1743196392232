<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

// Handle backup request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $backupType = $_POST['backup_type'] ?? 'database';
    $backupName = 'backup_' . date('Y-m-d_His');
    
    try {
        if ($backupType === 'database') {
            // Database backup
            $backupFile = "backups/{$backupName}.sql";
            
            // Create backups directory if it doesn't exist
            if (!file_exists('backups')) {
                mkdir('backups', 0777, true);
            }
            
            // Get all tables
            $tables = [];
            $stmt = $conn->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            // Generate SQL dump
            $output = '';
            foreach ($tables as $table) {
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $stmt = $conn->query("SHOW CREATE TABLE `$table`");
                $row = $stmt->fetch(PDO::FETCH_NUM);
                $output .= $row[1] . ";\n\n";
                
                $stmt = $conn->query("SELECT * FROM `$table`");
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $output .= "INSERT INTO `$table` VALUES(";
                    foreach ($row as $value) {
                        $value = addslashes($value);
                        $value = str_replace("\n", "\\n", $value);
                        $output .= "'$value',";
                    }
                    $output = rtrim($output, ',') . ");\n";
                }
                $output .= "\n";
            }
            
            // Save to file
            file_put_contents($backupFile, $output);
            $success = "Backup de base de datos creado exitosamente: {$backupName}.sql";
            
        } elseif ($backupType === 'full') {
            // Full backup (database + uploads)
            $backupDir = "backups/{$backupName}";
            
            // Create backup directory
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            // Backup database
            $dbBackupFile = "{$backupDir}/database.sql";
            file_put_contents($dbBackupFile, $output); // Reuse $output from above
            
            // Backup uploads directory if exists
            if (file_exists('uploads')) {
                $uploadsBackupDir = "{$backupDir}/uploads";
                mkdir($uploadsBackupDir, 0777, true);
                
                // Copy files
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator('uploads'),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen('uploads') + 1);
                        $destPath = "{$uploadsBackupDir}/{$relativePath}";
                        
                        // Ensure directory exists
                        $destDir = dirname($destPath);
                        if (!file_exists($destDir)) {
                            mkdir($destDir, 0777, true);
                        }
                        
                        copy($filePath, $destPath);
                    }
                }
            }
            
            // Create zip archive
            $zip = new ZipArchive();
            $zipFile = "backups/{$backupName}.zip";
            
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($backupDir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($backupDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                
                $zip->close();
                
                // Remove temp directory
                array_map('unlink', glob("{$backupDir}/*.*"));
                rmdir($backupDir);
                
                $success = "Backup completo creado exitosamente: {$backupName}.zip";
            } else {
                throw new Exception("No se pudo crear el archivo ZIP");
            }
        }
    } catch (Exception $e) {
        $error = "Error al crear el backup: " . $e->getMessage();
    }
}

// Get existing backups
$backups = [];
if (file_exists('backups')) {
    $files = scandir('backups', SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = "backups/{$file}";
            $backups[] = [
                'name' => $file,
                'size' => filesize($filePath),
                'date' => date('Y-m-d H:i:s', filemtime($filePath)),
                'type' => pathinfo($file, PATHINFO_EXTENSION) === 'sql' ? 'database' : 'full'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - Sistema de Backup</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'assets/css/dashboard.css'; ?>
        
        .backup-container {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .backup-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 500;
        }
        .backup-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .backup-radio {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .backup-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        .backup-btn i {
            margin-right: 5px;
        }
        .backup-list {
            width: 100%;
            border-collapse: collapse;
        }
        .backup-list th {
            background: var(--primary-color);
            color: white;
            text-align: left;
            padding: 10px;
        }
        .backup-list td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .backup-actions {
            display: flex;
            gap: 10px;
        }
        .backup-action {
            color: var(--primary-color);
            text-decoration: none;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .success {
            color: #2ecc71;
            margin-bottom: 15px;
        }
        .file-size {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Sistema de Backup</h2>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user']['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></div>
                    <small><?php echo htmlspecialchars($_SESSION['user']['role']); ?></small>
                </div>
            </div>
        </div>

        <div class="backup-container">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <h3 class="backup-title">Crear Nuevo Backup</h3>
            <form method="POST" class="backup-form">
                <div class="backup-radio">
                    <input type="radio" id="backup_db" name="backup_type" value="database" checked>
                    <label for="backup_db">Solo Base de Datos</label>
                </div>
                <div class="backup-radio">
                    <input type="radio" id="backup_full" name="backup_type" value="full">
                    <label for="backup_full">Completo (BD + Archivos)</label>
                </div>
                <button type="submit" class="backup-btn">
                    <i class="fas fa-database"></i> Crear Backup
                </button>
            </form>
        </div>

        <div class="backup-container">
            <h3 class="backup-title">Backups Existentes</h3>
            <?php if (empty($backups)): ?>
                <p>No hay backups disponibles</p>
            <?php else: ?>
                <table class="backup-list">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td><?php echo $backup['type'] === 'database' ? 'Base de Datos' : 'Completo'; ?></td>
                            <td><?php echo htmlspecialchars($backup['date']); ?></td>
                            <td class="file-size"><?php echo formatSizeUnits($backup['size']); ?></td>
                            <td class="backup-actions">
                                <a href="backups/<?php echo htmlspecialchars($backup['name']); ?>" download class="backup-action">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                                <a href="#" onclick="confirmDelete('<?php echo htmlspecialchars($backup['name']); ?>')" class="backup-action">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(filename) {
            if (confirm(`¿Está seguro que desea eliminar el backup "${filename}"?`)) {
                window.location.href = `delete_backup.php?file=${encodeURIComponent(filename)}`;
            }
        }
        
        function formatSizeUnits(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' bytes';
            }
        }
    </script>
</body>
</html>