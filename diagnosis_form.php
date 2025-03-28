<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$action = $_GET['action'] ?? 'add';
$diagnosisId = $_GET['id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;
$diagnosis = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosisData = [
        'patient_id' => $_POST['patient_id'],
        'cie10_code' => $_POST['cie10_code'],
        'cie10_description' => $_POST['cie10_description'],
        'diagnosis_details' => $_POST['diagnosis_details'],
        'date_created' => $_POST['date_created'],
        'created_by' => $_SESSION['user']['id']
    ];

    try {
        if ($action === 'add') {
            $stmt = $conn->prepare("
                INSERT INTO diagnoses 
                (patient_id, cie10_code, cie10_description, diagnosis_details, date_created, created_by)
                VALUES 
                (:patient_id, :cie10_code, :cie10_description, :diagnosis_details, :date_created, :created_by)
            ");
            $stmt->execute($diagnosisData);
            header("Location: patient_view.php?id={$diagnosisData['patient_id']}");
            exit();
        } else {
            $stmt = $conn->prepare("
                UPDATE diagnoses SET
                cie10_code = :cie10_code,
                cie10_description = :cie10_description,
                diagnosis_details = :diagnosis_details,
                date_created = :date_created
                WHERE id = :id
            ");
            $diagnosisData['id'] = $diagnosisId;
            $stmt->execute($diagnosisData);
            header("Location: patient_view.php?id={$diagnosisData['patient_id']}");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error al guardar el diagnóstico: " . $e->getMessage();
    }
}

// Load diagnosis data for editing
if ($action === 'edit' && $diagnosisId) {
    $stmt = $conn->prepare("SELECT * FROM diagnoses WHERE id = :id");
    $stmt->bindParam(':id', $diagnosisId);
    $stmt->execute();
    $diagnosis = $stmt->fetch(PDO::FETCH_ASSOC);
    $patientId = $diagnosis['patient_id'];
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
    <title>OMSystem V2 - <?php echo $action === 'add' ? 'Nuevo Diagnóstico' : 'Editar Diagnóstico'; ?></title>
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
        textarea {
            min-height: 100px;
            resize: vertical;
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
        .cie10-search {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .cie10-search input {
            flex: 1;
        }
        .cie10-search button {
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
            <h2><?php echo $action === 'add' ? 'Nuevo Diagnóstico' : 'Editar Diagnóstico'; ?></h2>
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

                <div class="form-group">
                    <label for="date_created">Fecha del Diagnóstico *</label>
                    <input type="date" id="date_created" name="date_created" 
                           value="<?php echo htmlspecialchars($diagnosis['date_created'] ?? date('Y-m-d')); ?>" required>
                </div>

                <div class="form-group">
                    <label>Codificación CIE-10</label>
                    <div class="cie10-search">
                        <input type="text" id="cie10_search" placeholder="Buscar código CIE-10...">
                        <button type="button" id="search_cie10">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <input type="hidden" id="cie10_code" name="cie10_code" 
                           value="<?php echo htmlspecialchars($diagnosis['cie10_code'] ?? ''); ?>">
                    <input type="text" id="cie10_description" name="cie10_description" 
                           value="<?php echo htmlspecialchars($diagnosis['cie10_description'] ?? ''); ?>" 
                           placeholder="Descripción CIE-10" readonly>
                </div>

                <div class="form-group">
                    <label for="diagnosis_details">Detalles del Diagnóstico *</label>
                    <textarea id="diagnosis_details" name="diagnosis_details" required><?php 
                        echo htmlspecialchars($diagnosis['diagnosis_details'] ?? ''); 
                    ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="patient_view.php?id=<?php echo $patientId; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // CIE-10 search functionality
        document.getElementById('search_cie10').addEventListener('click', function() {
            const searchTerm = document.getElementById('cie10_search').value;
            if (searchTerm.trim() === '') return;
            
            // Open CIE-10 search in new window
            window.open(`https://mediately.co/es/icd?q=${encodeURIComponent(searchTerm)}`, '_blank');
            
            // For demo purposes - in a real implementation you would have an API to search codes
            // This is just to show how it would work with the external site
            alert("Por favor busque el código CIE-10 en la ventana que se abrió y copie los valores manualmente");
        });

        // Allow manual entry of CIE-10 codes
        document.getElementById('cie10_search').addEventListener('change', function() {
            document.getElementById('cie10_code').value = this.value;
        });
    </script>
</body>
</html>