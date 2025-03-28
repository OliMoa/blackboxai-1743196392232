<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - Panel Principal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a7bd5;
            --secondary-color: #00d2ff;
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            color: white;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-item {
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
        }
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .quick-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .quick-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            text-align: center;
            color: var(--primary-color);
            text-decoration: none;
        }
        .quick-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .quick-card i {
            font-size: 30px;
            margin-bottom: 15px;
        }
        .quick-card h3 {
            margin: 0;
            font-weight: 500;
        }
        .section-title {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>OMSystem V2</h2>
        </div>
        <div class="sidebar-menu">
            <a href="#" class="menu-item active">
                <i class="fas fa-home"></i> Inicio
            </a>
            <a href="patients.php" class="menu-item">
                <i class="fas fa-user-injured"></i> Pacientes
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-stethoscope"></i> Consultas
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-pills"></i> Medicamentos
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-medical"></i> Reportes
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i> Configuración
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="header">
            <h2>Panel Principal</h2>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <small><?php echo htmlspecialchars($user['role']); ?></small>
                </div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <h3 class="section-title">Acciones Rápidas</h3>
        <div class="quick-cards">
            <a href="patients.php?action=add" class="quick-card">
                <i class="fas fa-user-plus"></i>
                <h3>Nuevo Paciente</h3>
            </a>
            <a href="https://mediately.co/es/icd" target="_blank" class="quick-card">
                <i class="fas fa-search"></i>
                <h3>CIE-10 en Línea</h3>
            </a>
            <a href="https://chatgpt.com/g/g-OoY8N0RvB-tu-doctor-ia" target="_blank" class="quick-card">
                <i class="fas fa-robot"></i>
                <h3>Asistente IA</h3>
            </a>
            <a href="https://prescripcion-receta.minsal.cl/auth/login" target="_blank" class="quick-card">
                <i class="fas fa-prescription-bottle-alt"></i>
                <h3>MINSAL Recetas</h3>
            </a>
        </div>

        <!-- Recent Patients Section -->
        <h3 class="section-title">Pacientes Recientes</h3>
        <div class="recent-patients">
            <!-- Will be populated with patient data via AJAX -->
            <p>Cargando pacientes recientes...</p>
        </div>
    </div>

    <script>
        // Will add functionality for loading recent patients
    </script>
</body>
</html>