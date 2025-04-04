<?php

// INTERFACES SEGÚN HABILIDADES
interface PuedeVolar {
    public function volar();
}

interface PuedeNadar {
    public function nadar();
}

interface PuedePonerHuevos {
    public function ponerHuevo();
}

interface PuedeFlotar {
    public function flotar();
}

// CLASE BASE PARA AVES
abstract class Ave {
    protected string $nombre;
    
    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }
    
    public function emite_sonido() {
        echo "$this->nombre emite sonido.\n";
    }
}

// CLASES CONCRETAS
class Gorrión extends Ave implements PuedeVolar, PuedePonerHuevos {
    public function volar() {
        echo "$this->nombre está volando.\n";
    }
    
    public function ponerHuevo() {
        echo "$this->nombre puso un huevo.\n";
    }
}

class Paloma extends Ave implements PuedeVolar, PuedePonerHuevos {
    public function volar() {
        echo "$this->nombre está volando.\n";
    }
    
    public function ponerHuevo() {
        echo "$this->nombre puso un huevo.\n";
    }
}

class Pato extends Ave implements PuedeVolar, PuedeNadar, PuedePonerHuevos {
    public function volar() {
        echo "$this->nombre está volando.\n";
    }
    
    public function nadar() {
        echo "$this->nombre está nadando.\n";
    }
    
    public function ponerHuevo() {
        echo "$this->nombre puso un huevo.\n";
    }
}

class PatoDeGoma extends Ave implements PuedeFlotar {
    public function flotar() {
        echo "$this->nombre está flotando en el agua.\n";
    }
}

class Avestruz extends Ave implements PuedePonerHuevos {
    public function ponerHuevo() {
        echo "$this->nombre puso un huevo.\n";
    }
    
    public function correr() {
        echo "$this->nombre está corriendo.\n";
    }
}

// EJEMPLO DE USO
$gorrión = new Gorrión("Gorrión");
$gorrión->volar();
$gorrión->ponerHuevo();
$gorrión->comer();

$patito = new Pato("Pato");
$patito->volar();
$patito->nadar();
$patito->ponerHuevo();
$patito->comer();

$patoGoma = new PatoDeGoma("Pato de goma");
$patoGoma->flotar();

$avestruz = new Avestruz("Avestruz");
$avestruz->ponerHuevo();
$avestruz->correr();
$avestruz->comer();
