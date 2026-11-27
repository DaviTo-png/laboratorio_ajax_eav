<?php
class Producto
{
    public function __construct(private PDO $db) {}

    public function categorias(): array
    {
        return $this->db->query('SELECT id, nombre FROM categorias ORDER BY id')->fetchAll();
    }

    public function filtros(int $categoria, string $texto = '', array $seleccion = [], ?float $min = null, ?float $max = null): array
    {
        $sql = 'SELECT DISTINCT a.id, a.nombre, v.valor
                FROM productos p
                JOIN valores_productos v ON p.id = v.producto_id
                JOIN atributos a ON a.id = v.atributo_id
                WHERE (? = 0 OR p.id_categoria = ?) ORDER BY a.id, v.valor';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoria, $categoria]);
        $filtros = [];
        foreach ($stmt->fetchAll() as $fila) {
            $id = $fila['id'];
            if (!isset($filtros[$id])) {
                $filtros[$id] = ['id' => $id, 'nombre' => $fila['nombre'], 'valores' => []];
            }
            $filtros[$id]['valores'][] = ['valor' => $fila['valor'], 'cantidad' => 0];
        }
        foreach ($filtros as $id => &$grupo) {
            $otros = $seleccion;
            unset($otros[$id]);
            [$where, $params] = $this->condiciones($categoria, $texto, $otros, $min, $max);
            $sql = 'SELECT v.valor, COUNT(DISTINCT p.id) AS cantidad
                    FROM productos p JOIN valores_productos v ON v.producto_id = p.id'
                    . $where . ' AND v.atributo_id = ? GROUP BY v.valor';
            $params[] = $id;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $cantidades = array_column($stmt->fetchAll(), 'cantidad', 'valor');
            foreach ($grupo['valores'] as &$opcion) {
                $opcion['cantidad'] = (int) ($cantidades[$opcion['valor']] ?? 0);
            }
            unset($opcion);
        }
        unset($grupo);
        return array_values($filtros);
    }

    public function buscar(int $categoria, string $texto, array $filtros, ?float $min, ?float $max, string $orden): array
    {
        [$where, $parametros] = $this->condiciones($categoria, $texto, $filtros, $min, $max);
        $sql = 'SELECT p.id, p.nombre, p.precio, p.imagen, c.nombre AS categoria, a.nombre AS atributo, v.valor
                FROM productos p JOIN categorias c ON c.id = p.id_categoria
                LEFT JOIN valores_productos v ON v.producto_id = p.id
                LEFT JOIN atributos a ON a.id = v.atributo_id' . $where;
        $sql .= match ($orden) {
            'precio_asc' => ' ORDER BY p.precio ASC, p.id, a.id',
            'precio_desc' => ' ORDER BY p.precio DESC, p.id, a.id',
            default => ' ORDER BY p.id, a.id'
        };
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $productos = [];
        foreach ($stmt->fetchAll() as $fila) {
            $id = $fila['id'];
            if (!isset($productos[$id])) {
                $productos[$id] = ['id' => $id, 'nombre' => $fila['nombre'],
                    'categoria' => $fila['categoria'], 'precio' => (float) $fila['precio'],
                    'imagen' => $fila['imagen'], 'atributos' => []];
            }
            if ($fila['atributo'] !== null) {
                $productos[$id]['atributos'][] = ['nombre' => $fila['atributo'], 'valor' => $fila['valor']];
            }
        }
        return array_values($productos);
    }
    private function condiciones(int $categoria, string $texto, array $filtros, ?float $min, ?float $max): array
    {
        $where = ' WHERE 1=1';
        $parametros = [];
        if ($categoria > 0) {
            $where .= ' AND p.id_categoria = ?';
            $parametros[] = $categoria;
        }
        if ($texto !== '') {
            $where .= ' AND (p.nombre LIKE ? OR EXISTS (SELECT 1 FROM valores_productos vb
                      WHERE vb.producto_id = p.id AND vb.valor LIKE ?))';
            $parametros[] = '%' . $texto . '%';
            $parametros[] = '%' . $texto . '%';
        }
        foreach ($filtros as $atributo => $valores) {
            $marcas = implode(',', array_fill(0, count($valores), '?'));
            $where .= " AND EXISTS (SELECT 1 FROM valores_productos vf WHERE vf.producto_id = p.id
                      AND vf.atributo_id = ? AND vf.valor IN ($marcas))";
            $parametros[] = (int) $atributo;
            array_push($parametros, ...$valores);
        }
        if ($min !== null) { $where .= ' AND p.precio >= ?'; $parametros[] = $min; }
        if ($max !== null) { $where .= ' AND p.precio <= ?'; $parametros[] = $max; }
        return [$where, $parametros];
    }
}
