<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MC Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="row w-100">
            <div class="col-md-8 col-lg-6 mx-auto">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="img/logo.jpg" alt="MC Store" class="img-fluid mb-3" style="max-width: 100px;">
                            <h2 class="fw-bold text-primary">MC Store</h2>
                        </div>

                        <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login" type="button" role="tab">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#register" type="button" role="tab">
                                    <i class="bi bi-person-plus me-2"></i>Registrarse
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="authTabsContent">
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form action="../php/authentification.php" method="POST">
                                    <input type="hidden" name="action" value="login">
                                    
                                    <div class="mb-3">
                                        <label for="loginEmail" class="form-label">
                                            <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                        </label>
                                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="loginPassword" class="form-label">
                                            <i class="bi bi-lock me-2"></i>Contraseña
                                        </label>
                                        <input type="password" class="form-control" id="loginPassword" name="contraseña" required>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">
                                            Recordarme
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                                    </button>
                                    
                                    <div class="text-center">
                                        <a href="#" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form action="../php/authentification.php" method="POST">
                                    <input type="hidden" name="action" value="register">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="registerNombres" class="form-label">
                                                <i class="bi bi-person me-2"></i>Nombres
                                            </label>
                                            <input type="text" class="form-control" id="registerNombres" name="nombres" required maxlength="100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="registerApellidoPaterno" class="form-label">
                                                <i class="bi bi-person-badge me-2"></i>Apellido Paterno
                                            </label>
                                            <input type="text" class="form-control" id="registerApellidoPaterno" name="apellido_paterno" required maxlength="50">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="registerApellidoMaterno" class="form-label">
                                            <i class="bi bi-person-badge-fill me-2"></i>Apellido Materno
                                        </label>
                                        <input type="text" class="form-control" id="registerApellidoMaterno" name="apellido_materno" required maxlength="50">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="registerEmail" class="form-label">
                                            <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                        </label>
                                        <input type="email" class="form-control" id="registerEmail" name="email" required maxlength="255">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="registerTelefono" class="form-label">
                                            <i class="bi bi-telephone me-2"></i>Teléfono
                                        </label>
                                        <input type="tel" class="form-control" id="registerTelefono" name="telefono" maxlength="20" placeholder="Ej: +52 55 1234 5678">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="registerPassword" class="form-label">
                                            <i class="bi bi-lock me-2"></i>Contraseña
                                        </label>
                                        <input type="password" class="form-control" id="registerPassword" name="contraseña" required>
                                        <div class="form-text">
                                            <small>Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">
                                            <i class="bi bi-lock-fill me-2"></i>Confirmar Contraseña
                                        </label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_contraseña" required>
                                    </div>
                                    
                                    <input type="hidden" name="rol" value="cliente">
                                    <!-- Corregido: usar 'Activo' en lugar de 'activo' -->
                                    <input type="hidden" name="usuario_estado" value="Activo">
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="acceptTerms" required>
                                        <label class="form-check-label" for="acceptTerms">
                                            Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-person-plus me-2"></i>Registrarse
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="../index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-2"></i>Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defear></script>
    <script src="js/login.js" defear></script>
</body>
</html>