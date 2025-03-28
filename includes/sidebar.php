<?php if (isLoggedIn()): ?>
<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>OMSystem V2</h2>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Inicio
        </a>
        <a href="patients.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'patients.php' ? 'active' : ''; ?>">
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
        <a href="backup_system.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'backup_system.php' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> Backup
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-cog"></i> Configuración
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</div>
<?php endif; ?>