<?php

namespace App\Models\Aeropuerto;

interface MediatorInterface
{
    public function registrar(Aeronave $aeronave): void;

    /** $evento: solicitar_aterrizaje | aterrizo | solicitar_despegue | despego | liberar_pista */
    public function notificar(Aeronave $emisor, string $evento): void;
}
