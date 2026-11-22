<?php

class Categoria {

    private $codigo;
    private $nombre;

    public function __construct($codigo = null, $nombre = null) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
}
