<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header("Location: patients.php");
    exit();
}

// Get patient data
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->bindParam(':id', $patientId);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: patients.php");
    exit();
}

// Get medical history
$diagnoses = $conn->prepare("SELECT * FROM diagnoses WHERE patient_id = :id ORDER BY date_created DESC");
$diagnoses->bindParam(':id', $patientId);
$diagnoses->execute();

$medications = $conn->prepare("SELECT * FROM medications WHERE patient_id = :id ORDER BY start_date DESC");
$medications->bindParam(':id', $patientId);
$medications->execute();

$treatments = $conn->prepare("SELECT * FROM treatments WHERE patient_id = :id ORDER BY start_date DESC");
$treatments->bindParam(':id', $patientId);
$treatments->execute();

$documents = $conn->prepare("SELECT * FROM patient_documents WHERE patient_id = :id ORDER BY upload_date DESC");
$documents->bindParam(':id', $patientId);
$documents->execute();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - Ficha de Paciente</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'assets/css/dashboard.css'; ?>
        
        .patient-header {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .patient-name {
            color: var(--primary-color);
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .patient-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            flex: 1;
            min-width: 200px;
        }
        .info-label {
            color: #777;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info-value {
            font-weight: 500;
        }
        .section {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .section-title {
            color: var(--primary-color);
            margin: 0;
            font-weight: 500;
        }
        .add-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        .add-btn i {
            margin-right: 5px;
        }
        .history-item {
            border-left: 3px solid var(--primary-color);
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .history-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .history-date {
            color: #777;
            font-size: 14px;
        }
        .history-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }
        .document-thumbnail {
            width: 100px;
            height: 100px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            border-radius: 5px;
            overflow: hidden;
        }
        .document-thumbnail img {
            max-width: 100%;
            max-height: 100%;
        }
        .document-item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        .document-info {
            flex: 1;
        }
        .tab-container {
            margin-bottom: 20px;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 500;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Ficha de Paciente</h2>
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

        <div class="patient-header">
            <h1 class="patient-name"><?php echo htmlspecialchars($patient['full_name']); ?></h1>
            <div class="patient-info">
                <div class="info-item">
                    <div class="info-label">RUT/CI/DNI</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['identification']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Edad</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['age']); ?> años</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['email']); ?></div>
                </div>
            </div>
            <div class="patient-info">
                <div class="info-item">
                    <div class="info-label">Dirección</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['address']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ciudad/Comuna</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['city']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previsión</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['insurance']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Empresa/Cargo</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($patient['company']); ?>
                        <?php echo $patient['position'] ? ' / ' . htmlspecialchars($patient['position']) : ''; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-container">
            <div class="tabs">
                <div class="tab active" data-tab="medical">Historial Médico</div>
                <div class="tab" data-tab="documents">Documentos</div>
                <div class="tab" data-tab="allergies">Alergias</div>
                <div class="tab" data-tab="medications">Medicamentos</div>
            </div>
        </div>

        <div class="tab-content active" id="medical-tab">
            <div class="section">
                <div class="section-header">
                    <h3 class="section-title">Diagnósticos</h3>
                    <a href="diagnosis_form.php?patient_id=<?php echo $patientId; ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Nuevo Diagnóstico
                    </a>
                </div>
                <?php while ($diagnosis = $diagnoses->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="history-item">
                    <div class="history-header">
                        <div>
                            <strong><?php echo htmlspecialchars($diagnosis['cie10_description']); ?></strong>
                            <?php if ($diagnosis['cie10_code']): ?>
                                <small>(<?php echo htmlspecialchars($diagnosis['cie10_code']); ?>)</small>
                            <?php endif; ?>
                        </div>
                        <div class="history-actions">
                            <a href="diagnosis_form.php?id=<?php echo $diagnosis['id']; ?>" class="action-btn">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                    <div class="history-date">
                        <?php echo date('d/m/Y', strtotime($diagnosis['date_created'])); ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($diagnosis['diagnosis_details'])); ?></p>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="section">
                <div class="section-header">
                    <h3 class="section-title">Tratamientos</h3>
                    <a href="treatment_form.php?patient_id=<?php echo $patientId; ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Nuevo Tratamiento
                    </a>
                </div>
                <?php while ($treatment = $treatments->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="history-item">
                    <div class="history-header">
                        <strong><?php echo htmlspecialchars($treatment['treatment_name']); ?></strong>
                        <div class="history-actions">
                            <a href="treatment_form.php?id=<?php echo $treatment['id']; ?>" class="action-btn">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                    <div class="history-date">
                        <?php echo date('d/m/Y', strtotime($treatment['start_date'])); ?>
                        <?php if ($treatment['end_date']): ?>
                            - <?php echo date('d/m/Y', strtotime($treatment['end_date'])); ?>
                        <?php endif; ?>
                        (<?php echo htmlspecialchars($treatment['status']); ?>)
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($treatment['description'])); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="tab-content" id="documents-tab">
            <div class="section">
                <div class="section-header">
                    <h3 class="section-title">Documentos del Paciente</h3>
                    <a href="document_upload.php?patient_id=<?php echo $patientId; ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Subir Documento
                    </a>
                </div>
                <?php while ($document = $documents->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="document-item">
                    <div class="document-thumbnail">
                        <?php if (strpos($document['file_type'], 'image') !== false): ?>
                            <img src="uploads/<?php echo htmlspecialchars($document['file_path']); ?>" alt="Documento">
                        <?php else: ?>
                            <i class="fas fa-file-alt fa-2x"></i>
                        <?php endif; ?>
                    </div>
                    <div class="document-info">
                        <div><strong><?php echo htmlspecialchars($document['document_name']); ?></strong></div>
                        <div class="history-date">
                            Subido el <?php echo date('d/m/Y H:i', strtotime($document['upload_date'])); ?>
                        </div>
                    </div>
                    <div class="history-actions">
                        <a href="uploads/<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" class="action-btn">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="#" class="action-btn">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="tab-content" id="allergies-tab">
            <div class="section">
                <div class="section-header">
                    <h3 class="section-title">Alergias</h3>
                </div>
                <div class="form-group">
                    <label>Alergias a Medicamentos</label>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($patient['drug_allergies'] ?: 'No registra')); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alergias Alimentarias</label>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($patient['food_allergies'] ?: 'No registra')); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="medications-tab">
            <div class="section">
                <div class="section-header">
                    <h3 class="section-title">Medicamentos Actuales</h3>
                    <a href="medication_form.php?patient_id=<?php echo $patientId; ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Nuevo Medicamento
                    </a>
                </div>
                <?php while ($medication = $medications->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="history-item">
                    <div class="history-header">
                        <strong><?php echo htmlspecialchars($medication['medication_name']); ?></strong>
                        <div class="history-actions">
                            <a href="medication_form.php?id=<?php echo $medication['id']; ?>" class="action-btn">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                    <div class="history-date">
                        <?php echo date('d/m/Y', strtotime($medication['start_date'])); ?>
                        <?php if ($medication['end_date']): ?>
                            - <?php echo date('d/m/Y', strtotime($medication['end_date'])); ?>
                        <?php endif; ?>
                    </div>
                    <p>
                        <strong>Dosis:</strong> <?php echo htmlspecialchars($medication['dosage']); ?><br>
                        <strong>Frecuencia:</strong> <?php echo htmlspecialchars($medication['frequency']); ?>
                    </p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Antecedentes Familiares</h3>
            </div>
            <div class="info-value">
                <?php echo nl2br(htmlspecialchars($patient['family_history'] ?: 'No registra')); ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="patients.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="patient_form.php?action=edit&id=<?php echo $patientId; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar Paciente
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="export_patient.php?id=<?php echo $patientId; ?>" class="btn btn-primary">
                <i class="fas fa-file-word"></i> Exportar a Word
            </a>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>