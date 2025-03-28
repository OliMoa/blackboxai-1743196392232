<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    header("Location: patients.php");
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentName = $_POST['document_name'];
    $uploadedFile = $_FILES['document_file'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($uploadedFile['type'], $allowedTypes)) {
        $error = "Tipo de archivo no permitido. Solo se aceptan imágenes, PDF y documentos Word.";
    } elseif ($uploadedFile['size'] > $maxSize) {
        $error = "El archivo es demasiado grande. El tamaño máximo permitido es 5MB.";
    } elseif ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $error = "Error al subir el archivo. Código: " . $uploadedFile['error'];
    } else {
        // Create uploads directory if it doesn't exist
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // Generate unique filename
        $fileExt = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $filePath = 'uploads/' . $fileName;
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO patient_documents 
                    (patient_id, document_name, file_path, file_type, uploaded_by)
                    VALUES 
                    (:patient_id, :document_name, :file_path, :file_type, :uploaded_by)
                ");
                $stmt->execute([
                    'patient_id' => $patientId,
                    'document_name' => $documentName,
                    'file_path' => $fileName,
                    'file_type' => $uploadedFile['type'],
                    'uploaded_by' => $_SESSION['user']['id']
                ]);
                header("Location: patient_view.php?id=$patientId");
                exit();
            } catch (PDOException $e) {
                $error = "Error al guardar el documento: " . $e->getMessage();
                // Remove uploaded file if DB insert failed
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        } else {
            $error = "Error al mover el archivo subido.";
        }
    }
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
    <title>OMSystem V2 - Subir Documento</title>
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
        .file-upload {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .file-upload:hover {
            border-color: var(--primary-color);
        }
        .file-upload input {
            display: none;
        }
        .file-upload-label {
            display: block;
            cursor: pointer;
        }
        .file-upload-label i {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: #777;
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
            <h2>Subir Documento</h2>
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

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="patient_id" value="<?php echo $patientId; ?>">

                <div class="form-group">
                    <label for="document_name">Nombre del Documento *</label>
                    <input type="text" id="document_name" name="document_name" required>
                </div>

                <div class="form-group">
                    <label>Archivo *</label>
                    <div class="file-upload">
                        <label class="file-upload-label" for="document_file">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>Haz clic para seleccionar un archivo</div>
                            <div class="file-name" id="file-name">Ningún archivo seleccionado</div>
                        </label>
                        <input type="file" id="document_file" name="document_file" required>
                    </div>
                    <small>Tipos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX (Máx. 5MB)</small>
                </div>

                <div class="form-actions">
                    <a href="patient_view.php?id=<?php echo $patientId; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Subir Documento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show selected file name
        document.getElementById('document_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Ningún archivo seleccionado';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>