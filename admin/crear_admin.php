    <?php

    // Script para crear un nuevo administrador desde la terminal.
    // No puede ejecutarse desde el navegador, solo por consola
    if (php_sapi_name() !== 'cli') {
        die("Este script solo puede ejecutarse desde la terminal.\n");
    }

    require_once __DIR__ . '/config/conexion.php';

    try {

        // Valida que se pasen entre 5 y 6 argumentos por consola
        // Uso: php crear_admin.php <documento> <nombre> <apellido> <correo> <contrasena> [telefono] [direccion] <id_rol>
        if ($argc < 6 || $argc > 9) {
            echo "Uso: php crear_admin.php <documento> <nombre> <apellido> <correo> <contrasena> [telefono] [direccion] <id_rol>\n";
            exit(1);
        }

        // Argumentos desde consola
        $documento   = trim($argv[1]);
        $nombre      = trim($argv[2]);
        $apellido    = trim($argv[3]);
        $correo      = trim($argv[4]);
        $contrasena  = trim($argv[5]);
        $telefono    = $argc >= 7 ? trim($argv[6]) : null;
        $direccion   = $argc === 8 ? trim($argv[7]) : null;
        $id_rol      = $argc === 9 ? (int) $argv[8] : 1; // Administrador siempre tiene id_rol = 1


        // Valida que los campos obligatorios no estén vacíos
        if ($documento === '' || $nombre === '' || $apellido === '' || $correo === '' || $contrasena === '' || $id_rol <= 0) {
            echo "Todos los campos obligatorios deben ser proporcionados.\n";
            exit(1);
        }

        // Valida que la contraseña tenga exactamente 10 caracteres alfanuméricos
        if (!preg_match('/^[a-zA-Z0-9]{10}$/', $contrasena)) {
            echo "La contraseña debe tener exactamente 10 caracteres alfanuméricos.\n";
            exit(1);
        }

        // Conexión a la base de datos
        $db  = new Database();
        $pdo = $db->conectar();

        // Verifica que el documento no esté ya registrado
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE documento = ?');
        $stmt->execute([$documento]);

        if ($stmt->fetchColumn() > 0) {
            echo "El documento ya está registrado.\n";
            exit(1);
        }


        // Verificar que el correo no esté registrado (campo UNIQUE)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE correo = ?');
        $stmt->execute([$correo]);

        if ($stmt->fetchColumn() > 0) {
            echo "El correo ya está registrado.\n";
            exit(1);
        }

        // Hashea la contraseña antes de guardarla en la BD
        $hashPassword = password_hash($contrasena, PASSWORD_DEFAULT);

        // Inserta el nuevo administrador en la BD
        $sql    = 'INSERT INTO usuario (documento, nombre, apellido, correo, contrasena, id_rol) VALUES (?, ?, ?, ?, ?, ?)';
        $insert = $pdo->prepare($sql);
        $insert->execute([
            $documento,
            $nombre,
            $apellido,
            $correo,
            $hashPassword,
            $id_rol
        ]);

        // Confirmación en consola con los datos del admin creado
        echo "Administrador creado exitosamente.\n";
        echo "Documento: $documento | Nombre: $nombre | Apellido: $apellido | Correo: $correo | Rol: $id_rol";
        echo "\n";

    } catch (Exception $e) {
        // Muestra el error si algo falla en la BD u otro proceso
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
