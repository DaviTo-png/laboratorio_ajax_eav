<?php
class Database
{
    public static function conectar(): PDO
    {
        $host = 'localhost';
        $puerto = '3500';
        $base = 'nova_market_eav_v3';
        $usuario = 'root';
        $clave = '';
        return new PDO("mysql:host=$host;port=$puerto;dbname=$base;charset=utf8mb4", $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
}
