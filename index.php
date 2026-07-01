<?php

// traer conexion base de datos
require_once 'config/conexion.php';

// ️ Conexión a BD
$db = new Database();
$pdo = $db->conectar();
if (!$pdo) {
    die('<div class="alert alert-danger text-center mt-5"> Error de conexión a la base de datos</div>');
}




// BÚSQUEDA DE RUTAS

// Verifica si el JS envió un término de búsqueda por POST
if (isset($_POST['q'])) {

    // Le dice al navegador que la respuesta es JSON
    header('Content-Type: application/json');

    // Incluye la conexión a la BD (devuelve $pdo)
    require_once 'config/conexion.php';

    // Recibe el término buscado, elimina espacios y etiquetas HTML
    $termino = trim(strip_tags($_POST['q']));

    // Agrega % a los lados para que LIKE busque en cualquier posición
    // Ejemplo: "Centro" se convierte en "%Centro%"
    $like = '%' . $termino . '%';

    try {
        // ========================================================
        // CONSULTA SQL con signos ? como parámetros
        // Cada ? se reemplaza en orden por los valores del execute()
        // DISTINCT evita filas duplicadas
        // ========================================================
        $sql = "SELECT DISTINCT ruta.nombre_ruta, ruta.origen, ruta.destino, parada.nombre, 
        CONCAT(usuario.nombre, ' ', usuario.apellido) AS conductor
        FROM ruta
        INNER JOIN parada                ON parada.id_ruta                    = ruta.id_ruta
        INNER JOIN viaje                 ON viaje.id_ruta                     = ruta.id_ruta
        INNER JOIN asignacion_vehiculo   ON asignacion_vehiculo.id_asignacion = viaje.id_asignacion
        INNER JOIN conductor             ON conductor.id_conductor            = asignacion_vehiculo.id_conductor
        INNER JOIN usuario               ON usuario.documento                 = conductor.documento
        WHERE ruta.nombre_ruta = ?
        OR parada.nombre    = ?
        OR ruta.origen      = ?
        OR ruta.destino     = ?
        LIMIT 20
        ";

        // Prepara la consulta para evitar SQL injection
        $stmt = $pdo->prepare($sql);

        // Ejecuta enviando el $like 4 veces (uno por cada ?)
        $stmt->execute([$like, $like, $like, $like]);

        // Obtiene todos los resultados como array asociativo
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devuelve los resultados en JSON al JS
        echo json_encode($resultados);

        // Termina la ejecución — no carga el HTML
        exit;
    } catch (PDOException $e) {
        // Si hay error lo registra y devuelve array vacío
        error_log('Error búsqueda rutas: ' . $e->getMessage());
        echo json_encode([]);
        exit;
    }
}
?>




<!DOCTYPE html>
<!-- Documento HTML5 en español -->
<html lang="es">

<head>
    <meta charset="UTF-8">
    <!-- Hace que la página sea responsiva en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFV - Control de Flota Vehicular</title>

    <!-- Bootstrap 5.3.3: framework CSS para diseño responsivo y componentes UI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.5.2: librería de íconos vectoriales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Hoja de estilos propia del proyecto CFV -->
    <link rel="stylesheet" href="css/style_index.css">
</head>

<body>

    <!-- ================================================================
     NAVBAR - Barra de navegación principal (vista pública)
     Sticky: se queda fija arriba al hacer scroll
     Responsiva: en móvil se convierte en menú hamburguesa
     ================================================================ -->
    <nav class="navbar navbar-expand-lg" id="mainNavbar">
        <!--
        navbar: clase base de Bootstrap para barras de navegación
        navbar-expand-lg: en pantallas grandes (lg) muestra los links horizontales,
                          en pantallas pequeñas los colapsa en menú hamburguesa
        id="mainNavbar": para aplicar estilos CSS personalizados
    -->
        <div class="container">
            <!-- ===== LOGO (izquierda) ===== -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="img/logo.png" alt="CFV" width="38" height="38" class="logo-img">
                <span class="brand-name">CFV</span>
            </a>

            <!-- ===== BOTÓN HAMBURGUESA (solo en móvil) ===== -->
            <!--
            navbar-toggler: botón que aparece en pantallas pequeñas
            data-bs-toggle="collapse": le dice a Bootstrap que va a colapsar/expandir algo
            data-bs-target="#navLinks": indica QUÉ elemento va a colapsar (los links)
        -->
            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navLinks"
                aria-controls="navLinks"
                aria-expanded="false"
                aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ===== LINKS DE NAVEGACIÓN (centro) ===== -->

            <div class="collapse navbar-collapse justify-content-center" id="navLinks">
                <ul class="navbar-nav gap-1">
                    <!-- Ítem: Inicio -->
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="#inicio">Inicio</a>
                    </li>

                    <!-- Ítem: Vehículos -->
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="#vehiculos">Vehículos</a>
                    </li>

                    <!-- Ítem: Conductores -->
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="#conductores">Conductores</a>
                    </li>

                    <!-- Ítem: Rutas -->
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="#rutas">Rutas</a>
                    </li>

                    <!-- Ítem: Reportes -->
                    <li class="nav-item">
                        <a class="nav-link nav-custom" href="#reportes">Reportes</a>
                    </li>

                </ul>
            </div>

            <!-- ===== BOTÓN INICIAR SESIÓN (derecha) ===== -->
            <div class="d-none d-lg-block">
                <!-- Enlaza directamente al login del sistema -->
                <a href="login.php" class="btn btn-login">
                    <!-- Ícono de flecha entrando (Font Awesome) -->
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Iniciar sesión
                </a>
            </div>

        </div>
    </nav>

    <!-- seccion principal -->
    <section class="hero" id="inicio">
        <div class="container">

            <!-- Etiqueta pequeña arriba del título -->
            <span class="hero-tag">SISTEMA DE TRANSPORTE PÚBLICO</span>

            <!-- Título principal -->
            <h1 class="hero-title">
                Control de <br>
                <span class="hero-accent">Flota Vehicular</span><br>
                inteligente
            </h1>

            <!-- Descripción -->
            <p class="hero-desc">
                Monitorea, gestiona y optimiza el transporte urbano de Ibagué
                en tiempo real desde una sola plataforma. Diseñado para modernizar
                el sistema de rutas urbanas con tecnología accesible.
            </p>

            <!-- Botones -->
            <div class="hero-btns">
                <a href="#servicios" class="btn-hero-primary">Conoce el sistema</a>
                <a href="#about" class="btn-hero-secondary">Ver más →</a>
            </div>

            <!-- Estadísticas -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-num">120+</span>
                    <span class="hero-stat-label">Vehículos</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-num">18</span>
                    <span class="hero-stat-label">Rutas activas</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-num">24/7</span>
                    <span class="hero-stat-label">Monitoreo</span>
                </div>
            </div>

        </div>
    </section>


    <!-- ===== BARRA DE BÚSQUEDA ======== -->
    <section class="search-section">
        <div class="container">
            <p> Busca tu ruta </P>
            <!-- Caja de búsqueda -->
            <div class="search-box">
                <!-- Ícono de ubicación a la izquierda -->
                <i class="fa-solid fa-location-dot search-icon"></i>

                <!-- Campo de texto donde el usuario escribe -->
                <input
                    type="text" id="searchInput" class="search-input" placeholder="¿A dónde vas? Busca tus líneas, barrio o parada...">
                <!-- Botón de buscar -->
                <button class="search-btn" id="searchBtn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- ==================
             TABLA DE RESULTADOS
             Oculta por defecto, aparece cuando hay resultados
            ======================== -->
            <div class="search-results" id="searchResults" style="display:none;">

                <!-- Título con el término buscado -->
                <p class="results-title">
                    Resultados para: <span id="searchTerm"></span>
                </p>

                <!-- Tabla responsive: en móvil hace scroll horizontal -->
                <div class="table-responsive">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Ruta</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Parada</th>
                                <th>Conductor</th>
                                <th>Vehículo</th>
                            </tr>
                        </thead>
                        <!-- PHP llenará este tbody con los resultados -->
                        <tbody id="resultsBody">
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </section>


    <!-- ======== GRID: RUTAS CERCANAS + CONDUCTOR ========== -->
    <section class="nearby-section">
        <div class="container">
            <div class="nearby-grid">

                <!-- COLUMNA 1: RUTAS MÁS CERCANAS -->
                <div class="nearby-box">
                    <div class="nearby-title">RUTAS MÁS CERCANAS</div>

                    <div class="route-row">
                        <div class="route-num" style="background:#1a3a6a">01</div>
                        <div class="route-info">
                            <div class="route-name">Ruta 01 - La Colina</div>
                            <div class="route-path">Av. Libertad → Av. La Plata</div>
                        </div>
                        <div class="route-time">2 min</div>
                    </div>

                    <div class="route-row">
                        <div class="route-num" style="background:#1a4a3a">1</div>
                        <div class="route-info">
                            <div class="route-name">Ruta 1 - Delicias</div>
                            <div class="route-path">La Paz → Plaza H. y Molina</div>
                        </div>
                        <div class="route-time">5 min</div>
                    </div>

                    <div class="route-row">
                        <div class="route-num" style="background:#4a3a1a">05</div>
                        <div class="route-info">
                            <div class="route-name">Ruta 05 - Salado</div>
                            <div class="route-path">Av. San Martín → Av. Principal</div>
                        </div>
                        <div class="route-time">11 min</div>
                    </div>

                    <div class="route-row">
                        <div class="route-num" style="background:#3a1a4a">22</div>
                        <div class="route-info">
                            <div class="route-name">Ruta 22 - Medellín</div>
                            <div class="route-path">Barrio Sur → Av. La Playa</div>
                        </div>
                        <div class="route-time">15 min</div>
                    </div>

                    <div class="ver-todas">
                        <a href="#rutas" class="ver-todas-link">Ver todas las rutas →</a>
                    </div>
                </div>

                <!-- COLUMNA 2: CONDUCTOR -->
                <div class="nearby-box">
                    <div class="nearby-title">CONDUCTOR DE TU PRÓXIMA BUSETA</div>

                    <div class="conductor-list">

                        <div class="conductor-card">
                            <div class="conductor-avatar" style="background:#1a3a6a">CR</div>
                            <div class="conductor-info">
                                <div class="conductor-name">Carlos Ramírez</div>
                                <div class="conductor-meta">Ruta 21 · Placa TZB-482 · 8 años exp.</div>
                                <div class="conductor-stars">★★★★★ 4.9 · 312 viajes</div>
                            </div>
                            <div class="conductor-badge">Activo</div>
                        </div>

                        <div class="conductor-card">
                            <div class="conductor-avatar" style="background:#1a4a3a">LM</div>
                            <div class="conductor-info">
                                <div class="conductor-name">Luis Mendoza</div>
                                <div class="conductor-meta">Ruta 1 · Placa SKR-219 · 5 años exp.</div>
                                <div class="conductor-stars">★★★★☆ 4.2 · 189 viajes</div>
                            </div>
                            <div class="conductor-badge">Activo</div>
                        </div>

                    </div>

                    <div class="ver-todas">
                        <a href="#conductores" class="ver-todas-link">Ver perfil completo ↗</a>
                    </div>
                </div>

            </div>
        </div>
    </section>



    <!--=========== AVISOS DEL SERVICIO ============= -->
    <section class="avisos-section">
        <div class="container">
            <div class="avisos-box">
                <div class="nearby-title">AVISOS DEL SERVICIO</div>
                <!-- Aviso 1: Rojo - problema grave -->
                <div class="aviso-row">
                    <span class="aviso-dot dot-rojo"></span>
                    <div class="aviso-info">
                        <div class="aviso-titulo">Ruta 90 detenida en Mercacentro 10</div>
                        <div class="aviso-meta">Av. Principal km 12 · Hace 5 min</div>
                    </div>
                </div>

                <!-- Aviso 2: Rojo - demora -->
                <div class="aviso-row">
                    <span class="aviso-dot dot-rojo"></span>
                    <div class="aviso-info">
                        <div class="aviso-titulo">Retraso en Ruta 4 - 15 min de demora</div>
                        <div class="aviso-meta">Calle 42 con Carrera 5 · Hace 12 min</div>
                    </div>
                </div>

                <!-- Aviso 3: Amarillo - advertencia -->
                <div class="aviso-row">
                    <span class="aviso-dot dot-amarillo"></span>
                    <div class="aviso-info">
                        <div class="aviso-titulo">Ruta 23 cambia trayecto por Pedro Tafur</div>
                        <div class="aviso-meta">Desviación temporal hasta 6 pm · Hoy</div>
                    </div>
                </div>

                <!-- Aviso 4: Verde - todo bien -->
                <div class="aviso-row">
                    <span class="aviso-dot dot-verde"></span>
                    <div class="aviso-info">
                        <div class="aviso-titulo">Ruta 21 operando con normalidad</div>
                        <div class="aviso-meta">Sin incidencias · Frecuencia 3 min</div>
                    </div>
                </div>

                <!-- Aviso 5: Verde - todo bien -->
                <div class="aviso-row">
                    <span class="aviso-dot dot-verde"></span>
                    <div class="aviso-info">
                        <div class="aviso-titulo">Sistema general operando bien</div>
                        <div class="aviso-meta">Sin incidencias mayores reportadas</div>
                    </div>
                </div>

                <!-- Enlace ver todos -->
                <div class="ver-todas">
                    <a href="#avisos" class="ver-todas-link">Ver todos los avisos ↗</a>
                </div>

            </div>
        </div>
    </section>


    <!-- footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Bootstrap JS con Popper incluido -->
    <!-- Necesario para que funcione el menú hamburguesa en móvil -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script de búsqueda de rutas -->
    <script src="js/buscar_rutas_index.js"></script>
</body>

</html>