<?php
class ProductosController
{
    public function __construct(private Producto $modelo) {}

    public static function index(): void
    {
        require __DIR__ . '/../views/productos.php';
    }

    public function apiGetCategorias(): void
    {
        $this->json($this->modelo->categorias());
    }

    public function apiGetFiltros(): void
    {
        [$categoria, $texto, $filtros, $min, $max] = $this->parametrosBusqueda();
        $this->json($this->modelo->filtros($categoria, $texto, $filtros, $min, $max));
    }

    public function apiBuscar(): void
    {
        [$categoria, $texto, $filtros, $min, $max, $orden] = $this->parametrosBusqueda();
        $this->json($this->modelo->buscar($categoria, $texto, $filtros, $min, $max, $orden));
    }

    private function parametrosBusqueda(): array
    {
        $categoria = $this->categoriaId();
        $texto = $_GET['q'] ?? '';
        $filtros = $_GET['filtros'] ?? [];
        if (!is_string($texto) || !is_array($filtros) || count($filtros) > 30) {
            throw new InvalidArgumentException('Parámetros incorrectos.');
        }
        foreach ($filtros as $id => $valores) {
            if (!ctype_digit((string) $id) || (int) $id < 1) {
                throw new InvalidArgumentException('Filtro incorrecto.');
            }
            if (is_string($valores)) $valores = [$valores];
            if (!is_array($valores) || !$valores || count($valores) > 50) {
                throw new InvalidArgumentException('Filtro incorrecto.');
            }
            foreach ($valores as $valor) {
                if (!is_string($valor) || strlen($valor) > 600) throw new InvalidArgumentException('Valor incorrecto.');
            }
            $filtros[$id] = array_values(array_unique($valores));
        }
        $min = $this->precio('min');
        $max = $this->precio('max');
        if ($min !== null && $max !== null && $min > $max) throw new InvalidArgumentException('El precio mínimo supera al máximo.');
        $orden = $_GET['orden'] ?? 'relevancia';
        if (!in_array($orden, ['relevancia', 'precio_asc', 'precio_desc'], true)) throw new InvalidArgumentException('Orden incorrecto.');
        return [$categoria, trim(substr($texto, 0, 400)), $filtros, $min, $max, $orden];
    }

    private function precio(string $campo): ?float
    {
        $valor = $_GET[$campo] ?? '';
        if ($valor === '') return null;
        if (!is_string($valor) || !preg_match('/^\d{1,8}(\.\d{1,2})?$/D', $valor)) throw new InvalidArgumentException('Precio incorrecto.');
        return (float) $valor;
    }

    private function categoriaId(): int
    {
        $id = filter_var($_GET['categoria_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($id === false) {
            throw new InvalidArgumentException('Categoría incorrecta.');
        }
        return $id;
    }

    private function json(array $datos): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
