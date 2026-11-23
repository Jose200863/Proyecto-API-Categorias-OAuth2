<?php

require_once __DIR__ . '/../configuracion/conexion.php';

class CategoriaRepositorio
{
    public function guardar($categoria)
    {
        $conexion = obtenerConexion();
        $sql = "INSERT INTO categoria (codigo, nombre) VALUES (?, ?)";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $categoria->getCodigo(), PDO::PARAM_INT);
        $consulta->bindValue(2, $categoria->getNombre(), PDO::PARAM_STR);
        $consulta->execute();
    }

    public function listar()
    {
        $conexion = obtenerConexion();
        $sql = "SELECT codigo, nombre FROM categoria ORDER BY codigo";

        $consulta = $conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar($codigo)
    {
        $conexion = obtenerConexion();
        $sql = "SELECT codigo, nombre FROM categoria WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $codigo, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($categoria)
    {
        $conexion = obtenerConexion();
        $sql = "UPDATE categoria SET nombre = ? WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $categoria->getNombre(), PDO::PARAM_STR);
        $consulta->bindValue(2, $categoria->getCodigo(), PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->rowCount();
    }

    public function eliminar($codigo)
    {
        $conexion = obtenerConexion();
        $sql = "DELETE FROM categoria WHERE codigo = ?";

        $consulta = $conexion->prepare($sql);
        $consulta->bindValue(1, $codigo, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->rowCount();
    }
}