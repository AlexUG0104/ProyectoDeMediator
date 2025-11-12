<?php

namespace App\Models\Aeropuerto;

abstract class Aeronave
{
    protected string $id;
    protected string $tipo;
    protected MediatorInterface $torre;

    public function __construct(string $id, MediatorInterface $torre)
    {
        $this->id = $id;
        $this->torre = $torre;
        $this->torre->registrar($this);
    }

    public function id(): string   { return $this->id; }
    public function tipo(): string { return $this->tipo; }

    public function solicitarAterrizaje(): void { $this->torre->notificar($this, 'solicitar_aterrizaje'); }
    public function aterrizo(): void            { $this->torre->notificar($this, 'aterrizo'); }
    public function solicitarDespegue(): void   { $this->torre->notificar($this, 'solicitar_despegue'); }
    public function despego(): void             { $this->torre->notificar($this, 'despego'); }
    public function liberarPista(): void        { $this->torre->notificar($this, 'liberar_pista'); }
}
