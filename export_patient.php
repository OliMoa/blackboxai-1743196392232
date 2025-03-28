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

// Create Word document content
$htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Historial Médico - {$patient['full_name']}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        h1 { color: #3a7bd5; }
        h2 { color: #3a7bd5; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #3a7bd5; color: white; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .history-item { margin-bottom: 15px; padding-left: 10px; border-left: 3px solid #3a7bd5; }
        .history-date { color: #777; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Historial Médico</h1>
    <h2>Datos del Paciente</h2>
    <table>
        <tr>
            <th>Nombre Completo</th>
            <td>{$patient['full_name']}</td>
            <th>RUT/CI/DNI</th>
            <td>{$patient['identification']}</td>
        </tr>
        <tr>
            <th>Edad</th>
            <td>{$patient['age']} años</td>
            <th>Fecha de Nacimiento</th>
            <td>{$patient['birth_date']}</td>
        </tr>
        <tr>
            <th>Teléfono</th>
            <td>{$patient['phone']}</td>
            <th>Email</th>
            <td>{$patient['email']}</td>
        </tr>
        <tr>
            <th>Dirección</th>
            <td>{$patient['address']}</td>
            <th>Ciudad/Comuna</th>
            <td>{$patient['city']}</td>
        </tr>
        <tr>
            <th>Previsión</th>
            <td>{$patient['insurance']}</td>
            <th>Empresa/Cargo</th>
            <td>{$patient['company']} / {$patient['position']}</td>
        </tr>
    </table>

    <h2>Alergias</h2>
    <table>
        <tr>
            <th>Alergias a Medicamentos</th>
            <td>{$patient['drug_allergies'] ?: 'No registra'}</td>
        </tr>
        <tr>
            <th>Alergias Alimentarias</th>
            <td>{$patient['food_allergies'] ?: 'No registra'}</td>
        </tr>
    </table>

    <h2>Diagnósticos</h2>
HTML;

while ($diagnosis = $diagnoses->fetch(PDO::FETCH_ASSOC)) {
    $cie10 = $diagnosis['cie10_code'] ? "({$diagnosis['cie10_code']}) {$diagnosis['cie10_description']}" : '';
    $htmlContent .= <<<HTML
    <div class="history-item">
        <div class="history-date">{$diagnosis['date_created']}</div>
        <h3>{$diagnosis['cie10_description']} <small>$cie10</small></h3>
        <p>{$diagnosis['diagnosis_details']}</p>
    </div>
HTML;
}

$htmlContent .= <<<HTML
    <h2>Medicamentos</h2>
    <table>
        <tr>
            <th>Medicamento</th>
            <th>Dosis</th>
            <th>Frecuencia</th>
            <th>Período</th>
            <th>Código MINSAL</th>
        </tr>
HTML;

while ($medication = $medications->fetch(PDO::FETCH_ASSOC)) {
    $period = $medication['start_date'];
    if ($medication['end_date']) {
        $period .= " - {$medication['end_date']}";
    }
    $htmlContent .= <<<HTML
    <tr>
        <td>{$medication['medication_name']}</td>
        <td>{$medication['dosage']}</td>
        <td>{$medication['frequency']}</td>
        <td>$period</td>
        <td>{$medication['minsal_code']}</td>
    </tr>
HTML;
}

$htmlContent .= <<<HTML
    </table>

    <h2>Tratamientos</h2>
HTML;

while ($treatment = $treatments->fetch(PDO::FETCH_ASSOC)) {
    $period = $treatment['start_date'];
    if ($treatment['end_date']) {
        $period .= " - {$treatment['end_date']}";
    }
    $htmlContent .= <<<HTML
    <div class="history-item">
        <div class="history-date">$period ({$treatment['status']})</div>
        <h3>{$treatment['treatment_name']}</h3>
        <p>{$treatment['description']}</p>
    </div>
HTML;
}

$htmlContent .= <<<HTML
    <h2>Antecedentes Familiares</h2>
    <p>{$patient['family_history'] ?: 'No registra'}</p>

    <div style="margin-top: 50px; text-align: right; font-style: italic;">
        <p>Generado el: {$date('Y-m-d H:i:s')}</p>
        <p>OMSystem V2 - Historial Médico</p>
    </div>
</body>
</html>
HTML;

// Set headers for Word download
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=historial_{$patient['full_name']}.doc");
header("Pragma: no-cache");
header("Expires: 0");

// Output the HTML content
echo $htmlContent;
exit();