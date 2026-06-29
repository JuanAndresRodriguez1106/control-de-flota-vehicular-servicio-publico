<?php
session_start();


// traer conexion base de datos
require_once 'config/conexion.php';

if (isset($_SESSION['user_id'])) {
    // Redirigir al usuario a la página de inicio si ya ha iniciado sesión
    header("Location: dash.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    //validacion campos vacios
    if (empty($correo) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {

        try {
            // Conectar a la base de datos
            $db = new Database();
            $pdo = $db->conectar();

            // Preparar y ejecutar la consulta para obtener el usuario
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            // Verificar si el usuario existe y la contraseña es correcta
            if (!$usuario) {
                // el usuario no existe en la base de datos
                $error = 'Usuario no encontrado.';
            } elseif ($usuario['estado'] !== 'Activo') {
                //usuario existe pero no esta activo
                $error = 'Su cuenta no está activa.';
            } elseif (!password_verify($password, $usuario['contrasena'])) {
                // la contraseña es incorrecta
                $error = 'Contraseña incorrecta.';
            } elseif ($usuario['id_rol'] != 1) {
                // no es administrador
                $error = 'Acceso denegado.';
            } else {
                // toodo correcto, iniciar sesión
                $_SESSION['admin_id'] = $usuario['documento'];

                // guarda los datos del admin en secion para usalos en otras paginas
                $_SESSION['usuario'] = [
                    'documento' => $usuario['documento'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'id_rol' => $usuario['id_rol']
                ];

                header("Location: dash.php");
                exit();
            }
        } catch (PDOException $e) {
            // error incesperado de BD, no se expone el detalle al usuario
            $error = 'Error al conectar con la base de datos.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVF - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <div class="login-box">

            <!-- Logo -->
            <div class="logo">
                <img src="img/logo.png" alt="CVF">
                <h2>CVF</h2>
            </div>

            <!-- Título -->
            <h1>Bienvenido 👋</h1>
            <!-- Muestra el error si hubo alguno al intentar iniciar sesión -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <p class="subtitle">
                Accede a tu cuenta o regístrate
            </p>

            <!-- Formulario -->
            <form method="POST" id="loginForm">

                <label>Correo electrónico</label>

                <div class="input-group">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="correo" id="correo" placeholder="correo@gmail.com">
                </div>
                <span id="error-correo" style="color:red; font-size:0.85rem;"></span>


                <label>Contraseña</label>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="contraseña">
                    <i class="fa-regular fa-eye"></i>
                </div>
                <span id="error-password" style="color:red; font-size:0.85rem;"></span>
                <button type="submit" id="btnSubmit">
                    Iniciar sesión
                </button>

                <!-- boton para volver al inde principal      REVISARRR
                <button type="button" class="btn btn-secondary mt-2"
                    onclick="window.location.href='index.php'">
                    Volver al inicio -->


            </form>

            <!-- Recuperar contraseña -->
            <a href="recuperar_contrasena.php" class="forgot-password">
                ¿Olvidaste tu contraseña?
            </a>

            <!-- Separador -->
            <div class="separator">
                <span>o continuar con</span>
            </div>

            <!-- Redes sociales -->
            <div class="social-buttons">

                <button type="button" class="social-btn">
                    <i class="fa-brands fa-google"></i>
                </button>

                <button type="button" class="social-btn">
                    <i class="fa-brands fa-facebook-f"></i>
                </button>

                <button type="button" class="social-btn">
                    <i class="fa-brands fa-apple"></i>
                </button>

            </div>

            <!-- Crear cuenta -->
            <div class="register">

                <p>
                    ¿No tienes cuenta?
                    <a href="registro.php">Crear cuenta</a>
                </p>

            </div>
        </div>

    </div>
    <?php require_once __DIR__ . '/footer.php'; ?>
    <script src="js/login.js"></script>
</body>

</html>