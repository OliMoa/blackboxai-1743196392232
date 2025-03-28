<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user = authenticate($username, $password);
    
    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMSystem V2 - Inicio de Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 40px;
            text-align: center;
        }
        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
        h1 {
            color: #3a7bd5;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background: #3a7bd5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s;
        }
        button:hover {
            background: #2a5bb5;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/images/logo.png" alt="OMSystem Logo" class="logo">
        <h1>OMSystem V2</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="footer">
            Sistema Médico Integral - Versión 2.0
        </div>
    </div>
</body>
</html>