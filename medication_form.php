<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$action = $_GET['action'] ?? 'add';
$medicationId = $_GET['id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;
$medication = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicationData = [
        'patient_id' => $_POST['patient_id'],
        'medication_name' => $_POST['medication_name'],
        'dosage' => $_POST['dosage'],
        'frequency' => $_POST['frequency'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'] ?: null,
        'minsal_code' => $_POST['minsal_code'],
        'prescribed_by' => $_SESSION['user']['id']
    ];

    try {
        if ($action === 'add') {
            $stmt = $conn->prepare("
                INSERT INTO medications 
                (patient_id, medication_name, dosage, frequency, start_date, end_date, minsal_code, prescribed_by)
                VALUES 
                (:patient_id, :medication_name, :dosage, :frequency, :start_date, :end_date, :minsal_code, :prescribed_by)
            ");
            $stmt->execute($medicationData);
            header("Location: patient_view.php?id={$medicationData['patient_id']}");
            exit();
        } else {
            $stmt = $conn->prepare("
                UPDATE medications SET
                medication_name = :medication_name,
                dosage = :dosage,
                frequency = :frequency,
                start_date = :start_date,
                end_date = :end_date,
                minsal_code = :minsal_code
                WHERE id = :id
            ");
            $medicationData['id'] = $medicationId;
            $stmt->execute($medicationData);
            header("Location: patient_view.php?id={$medicationData['patient_id']}");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error al guardar el medicamento: " . $e->getMessage();
    }
}

// Load medication data for editing
if ($action === 'edit' && $medicationId) {
    $stmt = $conn->prepare("SELECT * FROM medications WHERE id = :id");
    $stmt->bindParam(':id', $medicationId);
    $stmt->execute();
    $medication = $stmt->fetch(PDO::FETCH_ASSOC);
    $patientId = $medication['patient_id'];
}

// Get patient info
$stmt = $conn->prepare("SELECT full_name FROM patients WHERE id = :id");
$stmt->bindParam(':id', $patientId);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - <?php echo $action === 'add' ? 'Nuevo Medicamento' : 'Editar Medicamento'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'assets/css/dashboard.css'; ?>
        
        .form-container {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
        }
        .form-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-col {
            flex: 1;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn i {
            margin-right: 5px;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .minsal-search {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .minsal-search input {
            flex: 1;
        }
        .minsal-search button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .patient-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2><?php echo $action === 'add' ? 'Nuevo Medicamento' : 'Editar Medicamento'; ?></h2>
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

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="patient-info">
                <strong>Paciente:</strong> <?php echo htmlspecialchars($patient['full_name']); ?>
            </div>

            <form method="POST">
                <input type="hidden" name="patient_id" value="<?php echo $patientId; ?>">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="medication_name">Nombre del Medicamento *</label>
                            <input type="text" id="medication_name" name="medication_name" 
                                   value="<?php echo htmlspecialchars($medication['medication_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="minsal_code">Código MINSAL</label>
                            <div class="minsal-search">
                                <input type="text" id="minsal_code" name="minsal_code" 
                                       value="<?php echo htmlspecialchars($medication['minsal_code'] ?? ''); ?>"
                                       placeholder="Código MINSAL">
                                <button type="button" id="search_minsal">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="dosage">Dosis *</label>
                            <input type="text" id="dosage" name="dosage" 
                                   value="<?php echo htmlspecialchars($medication['dosage'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="frequency">Frecuencia *</label>
                            <input type="text" id="frequency" name="frequency" 
                                   value="<?php echo htmlspecialchars($medication['frequency'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="start_date">Fecha de Inicio *</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo htmlspecialchars($medication['start_date'] ?? date('Y-m-d')); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="end_date">Fecha de Término (opcional)</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo htmlspecialchars($medication['end_date'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="patient_view.php?id=<?php echo $patientId; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Medicamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // MINSAL search functionality
        document.getElementById('search_minsal').addEventListener('click', function() {
            // Open MINSAL prescription site in new window
            window.open('https://prescripcion-receta.minsal.cl/auth/login', '_blank');
            
            // For demo purposes - in a real implementation you would have an API to search codes
            alert("Por favor busque el medicamento en el portal MINSAL que se abrió y copie el código manualmente");
        });
    </script>
</body>
</html>