<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$action = $_GET['action'] ?? 'add';
$patientId = $_GET['id'] ?? null;
$patient = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data and save to database
    $patientData = [
        'full_name' => $_POST['full_name'],
        'identification' => $_POST['identification'],
        'birth_date' => $_POST['birth_date'],
        'city' => $_POST['city'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'insurance' => $_POST['insurance'],
        'company' => $_POST['company'],
        'position' => $_POST['position'],
        'drug_allergies' => $_POST['drug_allergies'],
        'food_allergies' => $_POST['food_allergies'],
        'current_medications' => $_POST['current_medications'],
        'family_history' => $_POST['family_history']
    ];

    try {
        if ($action === 'add') {
            $stmt = $conn->prepare("
                INSERT INTO patients 
                (full_name, identification, birth_date, city, address, phone, email, 
                 insurance, company, position, drug_allergies, food_allergies, 
                 current_medications, family_history)
                VALUES 
                (:full_name, :identification, :birth_date, :city, :address, :phone, :email, 
                 :insurance, :company, :position, :drug_allergies, :food_allergies, 
                 :current_medications, :family_history)
            ");
            $stmt->execute($patientData);
            $patientId = $conn->lastInsertId();
            header("Location: patient_view.php?id=$patientId");
            exit();
        } else {
            $stmt = $conn->prepare("
                UPDATE patients SET
                full_name = :full_name,
                identification = :identification,
                birth_date = :birth_date,
                city = :city,
                address = :address,
                phone = :phone,
                email = :email,
                insurance = :insurance,
                company = :company,
                position = :position,
                drug_allergies = :drug_allergies,
                food_allergies = :food_allergies,
                current_medications = :current_medications,
                family_history = :family_history
                WHERE id = :id
            ");
            $patientData['id'] = $patientId;
            $stmt->execute($patientData);
            header("Location: patient_view.php?id=$patientId");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error al guardar el paciente: " . $e->getMessage();
    }
}

// Load patient data for editing
if ($action === 'edit' && $patientId) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
    $stmt->bindParam(':id', $patientId);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - <?php echo $action === 'add' ? 'Nuevo Paciente' : 'Editar Paciente'; ?></title>
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
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2><?php echo $action === 'add' ? 'Nuevo Paciente' : 'Editar Paciente'; ?></h2>
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

            <form method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="full_name">Nombre Completo *</label>
                            <input type="text" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($patient['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="identification">RUT/CI/DNI *</label>
                            <input type="text" id="identification" name="identification" 
                                   value="<?php echo htmlspecialchars($patient['identification'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="birth_date">Fecha de Nacimiento *</label>
                            <input type="date" id="birth_date" name="birth_date" 
                                   value="<?php echo htmlspecialchars($patient['birth_date'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Ciudad/Comuna *</label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($patient['city'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="address">Dirección *</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($patient['address'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono *</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="insurance">Previsión</label>
                            <input type="text" id="insurance" name="insurance" 
                                   value="<?php echo htmlspecialchars($patient['insurance'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="company">Empresa</label>
                            <input type="text" id="company" name="company" 
                                   value="<?php echo htmlspecialchars($patient['company'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="position">Cargo</label>
                            <input type="text" id="position" name="position" 
                                   value="<?php echo htmlspecialchars($patient['position'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="drug_allergies">Alergias a Medicamentos</label>
                            <textarea id="drug_allergies" name="drug_allergies"><?php echo htmlspecialchars($patient['drug_allergies'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="food_allergies">Alergias Alimentarias</label>
                            <textarea id="food_allergies" name="food_allergies"><?php echo htmlspecialchars($patient['food_allergies'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="current_medications">Medicamentos Actuales</label>
                            <textarea id="current_medications" name="current_medications"><?php echo htmlspecialchars($patient['current_medications'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="family_history">Antecedentes Familiares</label>
                    <textarea id="family_history" name="family_history"><?php echo htmlspecialchars($patient['family_history'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="patients.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Paciente
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>