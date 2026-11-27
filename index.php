<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Producto.php';
require_once __DIR__ . '/controllers/ProductosController.php';

$accion = $_GET['accion'] ?? '';
if ($accion === '') {
    ProductosController::index();
    exit;
}
try {
    $controller = new ProductosController(new Producto(Database::conectar()));
    switch ($accion) {
        case 'apiGetCategorias': $controller->apiGetCategorias(); break;
        case 'apiGetFiltros': $controller->apiGetFiltros(); break;
        case 'apiBuscar': $controller->apiBuscar(); break;
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Acción no encontrada.']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No se pudo consultar la base de datos. Verifica MySQL, la importación SQL y la configuración.']);
}
