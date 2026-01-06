<footer class="text-white py-5 mt-5" style="background-color: <?php echo htmlspecialchars($config['color_secundario']); ?>;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($config['nombre_sitio']); ?></h5>
                <p class="text-light"><?php echo substr(htmlspecialchars($config['texto_nosotros']), 0, 150); ?>...</p>
                <div class="d-flex">
                    <?php if (!empty($config['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($config['facebook']); ?>" class="text-white me-3" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($config['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($config['instagram']); ?>" class="text-white me-3" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($config['whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo htmlspecialchars($config['whatsapp']); ?>" class="text-white me-3" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Enlaces Rápidos</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#inicio" class="text-light text-decoration-none">Inicio</a></li>
                    <li class="mb-2"><a href="#productos" class="text-light text-decoration-none">Productos</a></li>
                    <li class="mb-2"><a href="#servicios" class="text-light text-decoration-none">Servicios</a></li>
                    <li class="mb-2"><a href="#acerca" class="text-light text-decoration-none">Acerca de</a></li>
                    <li class="mb-2"><a href="#contacto" class="text-light text-decoration-none">Contacto</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Contacto</h6>
                <?php if (!empty($config['direccion'])): ?>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <small class="text-light"><?php echo htmlspecialchars($config['direccion']); ?></small>
                </div>
                <?php endif; ?>
                <?php if (!empty($config['telefono'])): ?>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-phone me-2"></i>
                    <small class="text-light"><?php echo htmlspecialchars($config['telefono']); ?></small>
                </div>
                <?php endif; ?>
                <?php if (!empty($config['email'])): ?>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-envelope me-2"></i>
                    <small class="text-light"><?php echo htmlspecialchars($config['email']); ?></small>
                </div>
                <?php endif; ?>
                <?php if (!empty($config['horarios'])): ?>
                <div class="d-flex align-items-center">
                    <i class="fas fa-clock me-2"></i>
                    <small class="text-light"><?php echo str_replace("\n", " | ", htmlspecialchars($config['horarios'])); ?></small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-light">&copy; 2024 <?php echo htmlspecialchars($config['nombre_sitio']); ?>. Todos los derechos reservados.</p>
            </div>
            
        </div>
    </div>
</footer>