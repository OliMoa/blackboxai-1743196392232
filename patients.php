<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$action = $_GET['action'] ?? 'list';
$searchTerm = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Handle patient search
if (!empty($searchTerm)) {
    $stmt = $conn->prepare("
        SELECT * FROM patients 
        WHERE full_name LIKE :search 
        OR identification LIKE :search
        OR phone LIKE :search
        OR email LIKE :search
        ORDER BY full_name ASC
        LIMIT :perPage OFFSET :offset
    ");
    $searchParam = "%$searchTerm%";
    $stmt->bindParam(':search', $searchParam);
} else {
    $stmt = $conn->prepare("
        SELECT * FROM patients 
        ORDER BY full_name ASC
        LIMIT :perPage OFFSET :offset
    ");
}

$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM patients");
$totalStmt->execute();
$totalPatients = $totalStmt->fetchColumn();
$totalPages = ceil($totalPatients / $perPage);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - Gestión de Pacientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reuse dashboard styles */
        <?php include 'assets/css/dashboard.css'; ?>
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-box {
            display: flex;
            width: 300px;
        }
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            font-size: 14px;
        }
        .search-box button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 15px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        .patient-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 5px;
            overflow: hidden;
        }
        .patient-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        .patient-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .patient-table tr:hover {
            background: #f9f9f9;
        }
        .action-buttons a {
            color: var(--primary-color);
            margin-right: 10px;
            text-decoration: none;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            color: var(--primary-color);
            text-decoration: none;
        }
        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .add-patient-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .add-patient-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Pacientes</h2>
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

        <div class="content-header">
            <form class="search-box" method="GET" action="patients.php">
                <input type="text" name="search" placeholder="Buscar por nombre, RUT, teléfono..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="patient_form.php?action=add" class="add-patient-btn">
                <i class="fas fa-user-plus"></i> Nuevo Paciente
            </a>
        </div>

        <table class="patient-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT/CI/DNI</th>
                    <th>Teléfono</th>
                    <th>Edad</th>
                    <th>Previsión</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($patient['identification']); ?></td>
                    <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    <td><?php echo htmlspecialchars($patient['age']); ?></td>
                    <td><?php echo htmlspecialchars($patient['insurance']); ?></td>
                    <td class="action-buttons">
                        <a href="patient_view.php?id=<?php echo $patient['id']; ?>" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="patient_form.php?action=edit&id=<?php echo $patient['id']; ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="medical_history.php?patient_id=<?php echo $patient['id']; ?>" title="Historial">
                            <i class="fas fa-file-medical"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>"
                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>