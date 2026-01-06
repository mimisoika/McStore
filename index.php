<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
        require_once 'pages/admin/functions/f_configuracion.php';
        $config = obtenerConfiguracion();
    ?>
    <title><?= htmlspecialchars($config['nombre_sitio']) ?> | Inicio</title>

    <!-- === OPTIMIZACIÓN DE RECURSOS === -->

    <!-- Preconnect para acelerar carga de CSS/CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Bootstrap Icons -->
    <link rel="preload" 
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" 
          as="style" 
          onload="this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </noscript>

    <!-- Bootstrap CSS -->
    <link rel="preload" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          as="style" 
          onload="this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </noscript>

    <!-- Font Awesome (optimizado con display=swap) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          integrity=""
          referrerpolicy="no-referrer"
          media="all">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="css/index.css">

    <!-- CSS Dinámico -->
    <style>
        <?= generarCssDinamico(); ?>

        #heroCarousel .carousel-item img {
            min-height: 500px;
            object-fit: cover;
        }
    </style>

</head>
<body>
    <?php 
    include 'pages/header.php';
    require_once __DIR__ . '/config.php';
    require_once 'pages/functions/f_index.php';
    require_once 'pages/functions/f_catalogo.php';
    require_once 'pages/functions/f_favoritos.php'; 
    ?>
    
    <section class="inicio" id="inicio">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-7">
                    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php
                            $imagenes = obtenerImagenesCarrusel();
                            foreach ($imagenes as $key => $imagen):
                            ?>
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $key; ?>" 
                                    <?php echo $key === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                    aria-label="Slide <?php echo $key + 1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($imagenes as $key => $imagen): ?>
                                <div class="carousel-item <?php echo $key === 0 ? 'active' : ''; ?>">
                                <picture>
                                    <?php
                                        $imagenOriginal = $imagen['imagen_url'];
                                        $imagenWebP = str_replace(['.jpg','.jpeg','.png'], '.webp', $imagenOriginal);

                                        if (file_exists($imagenWebP)) {
                                            echo '<source srcset="' . $imagenWebP . '" type="image/webp">';
                                        }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imagenOriginal); ?>" 
                                        loading="lazy" decoding="async"
                                        class="d-block w-100"
                                        alt="<?php echo htmlspecialchars($imagen['titulo']); ?>">
                                </picture>

                                    
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <!-- (Controles duplicados eliminados) -->
            </div>
        </div>
    </section>

    <!-- Sección Productos Aleatorios por Categoría -->
    <section class="productos-aleatorios py-5" id="productos-aleatorios">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Productos</h2>
                    <p class="lead text-muted">Explora una selección aleatoria de productos por categoría</p>
                </div>
            </div>

            <?php
            mostrarProductosAleatorios();
            ?>
        </div>
    </section>

    <section class="productos-destacados py-5" id="productos">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Productos Destacados</h2>
                    <p class="lead text-muted">Los mejores productos para tus creaciones</p>
                </div>
            </div>
            <div class="row g-4">
                <?php mostrarProductosDestacados(); ?>
            </div>
        </div>
    </section>



    <!-- Sección acerca de  -->
    <section class="acerca-de py-5" id="acerca">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">Acerca de <?php echo htmlspecialchars($config['nombre_sitio']); ?></h2>
                    <p class="lead mb-4"><?php echo nl2br(htmlspecialchars($config['texto_nosotros'])); ?></p>
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="display-6 fw-bold" style="color: <?php echo htmlspecialchars($config['color_primario']); ?>;">5000+</h3>
                            <p class="text-muted">Clientes Satisfechos</p>
                        </div>
                        <div class="col-4">
                            <h3 class="display-6 fw-bold" style="color: <?php echo htmlspecialchars($config['color_primario']); ?>;">500+</h3>
                            <p class="text-muted">Productos Disponibles</p>
                        </div>
                        <div class="col-4">
                            <h3 class="display-6 fw-bold" style="color: <?php echo htmlspecialchars($config['color_primario']); ?>;">10+</h3>
                            <p class="text-muted">Años de Experiencia</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="pages/img/tienda-mc.jpg" class="img-fluid rounded shadow" alt="Nuestra Tienda">
                </div>
            </div>
        </div>
    </section>
    <!-- Contacto -->
    <section class="contacto py-5" id="contacto">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Contáctanos</h2>
                    <p class="lead text-muted">Estamos aquí para ayudarte</p>
                </div>
            </div>
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="row g-4">
                        <?php if (!empty($config['direccion'])): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: <?php echo htmlspecialchars($config['color_primario']); ?>; color: white;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Dirección</h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($config['direccion']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($config['telefono'])): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: <?php echo htmlspecialchars($config['color_primario']); ?>; color: white;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Teléfono</h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($config['telefono']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($config['email'])): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: <?php echo htmlspecialchars($config['color_primario']); ?>; color: white;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Email</h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($config['email']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($config['horarios'])): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: <?php echo htmlspecialchars($config['color_primario']); ?>; color: white;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Horarios</h5>
                                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($config['horarios'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                </div>
            </div>
        </div>
    </section>

    <?php include 'pages/footer.php'; ?>
    <!-- === Scripts optimizados === -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="pages/js/index.js" defer></script>
    <script src="pages/js/favoritos.js" defer></script>

</body>

</html>