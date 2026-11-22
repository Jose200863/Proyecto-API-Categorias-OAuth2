<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../modelos/Categoria.php';
require_once __DIR__ . '/../repositorios/CategoriaRepositorio.php';
require_once __DIR__ . '/../repositorios/ProgramadorRepositorio.php';

$metodo = $_SERVER["REQUEST_METHOD"];

try {

    $encabezados = getallheaders();

    $cedula = $encabezados["X-Cedula"] ?? "";
    $contrasena = $encabezados["X-Contrasena"] ?? "";

    $repositorio = new ProgramadorRepositorio();

    if (!$repositorio->validar($cedula, $contrasena)) {

        http_response_code(401);

        echo json_encode(["Error" => "Autorizacion Invalida"]);

        exit;
    }

    $repositorio = new CategoriaRepositorio();

    if ($metodo == "GET") {
        if (isset($_GET["codigo"])) {
            $categoria = $repositorio->buscar($_GET["codigo"]);

            if ($categoria) {
                echo json_encode($categoria); 
            } else {
                http_response_code(404);
                echo json_encode(["Error" => "Categoria no encontrada"]);
            }
        } else {
            $categorias = $repositorio->listar();
            echo json_encode($categorias);
        }

    } elseif ($metodo == "POST") {

        $body = json_decode(file_get_contents("php://input"), true);

        // Comprobar que el JSON tenga codigo y nombre.
        if (!isset($body["codigo"]) || !isset($body["nombre"])) {

            http_response_code(400);
            echo json_encode(["Error" => "Faltan datos"]);
            exit;
        }

        $categoria = new Categoria($body["codigo"], $body["nombre"]);
        $repositorio->guardar($categoria);

        http_response_code(201);
        echo json_encode(["Correcto" => "Insercion realizada"]);

    } elseif ($metodo == "PUT") {

        $body = json_decode(file_get_contents("php://input"), true);

        if (!isset($body["codigo"]) || !isset($body["nombre"])) {

            http_response_code(400);
            echo json_encode(["Error" => "Faltan datos"]);
            exit;
        }

        $categoria = new Categoria($body["codigo"], $body["nombre"]);
        $filas = $repositorio->actualizar($categoria);

        echo json_encode(["Correcto" => "Actualizacion ejecutada", "Filas modificadas" => $filas]);

    } elseif ($metodo == "DELETE") {

        $body = json_decode(file_get_contents("php://input"), true);

        if (!isset($body["codigo"])) {
            http_response_code(400);
            echo json_encode(["Error" => "Falta el codigo"]);
            exit;
        }

        $filas = $repositorio->eliminar($body["codigo"]);

        if ($filas > 0) {
            echo json_encode(["Correcto" => "Eliminacion realizada"]);
        } else {
            http_response_code(404);
            echo json_encode(["Error" => "Categoria no encontrada"]);
        }

    } else {
        http_response_code(405);
        header("Allow: GET, POST, PUT, DELETE");
        echo json_encode(["Error" => "Metodo no permitido"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["Error" => "Ocurrio un error al consultar MySQL"]);
}
