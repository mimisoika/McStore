<?php
include '../php/database.php';
include 'functions/f_carrito.php';

session_start();

// Solo manejar acciones AJAX
manejarAccionesCarrito();

// Si llegamos aquí, no se procesó ninguna acción válida
echo json_encode(['success' => false, 'message' => 'Acción no válida']);
?>