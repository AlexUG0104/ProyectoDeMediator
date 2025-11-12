<?php

namespace App\Models\Aeropuerto;

use SplQueue;

class TorreDeControl implements MediatorInterface
{
    /** @var Aeronave[] */
    private array $aeronaves = [];

    private bool $pistaOcupada = false;

    /** @var SplQueue<string> "ID|accion" */
    private SplQueue $cola;

    /** @var string[] */
    public array $log = [];

    /** estado por aeronave: volando | en_espera | en_pista | tierra */
    public array $estados = [];

    public function __construct()
    {
        $this->cola = new SplQueue();
    }

    public function registrar(Aeronave $aeronave): void
    {
        $this->aeronaves[$aeronave->id()] = $aeronave;
        $this->estados[$aeronave->id()] = 'volando';
        $this->log[] = "🔹 Registrada {$aeronave->tipo()} #{$aeronave->id()} en torre";
    }

    public function notificar(Aeronave $emisor, string $evento): void
    {
        $this->manejarEvento($emisor->id(), $emisor->tipo(), $evento);
    }

    /** Nuevo: actuar por ID (para AJAX) */
    public function notificarPorId(string $id, string $evento): void
    {
        if (!isset($this->aeronaves[$id])) return;
        $tipo = $this->aeronaves[$id]->tipo();
        $this->manejarEvento($id, $tipo, $evento);
    }

    private function manejarEvento(string $id, string $tipo, string $evento): void
    {
        switch ($evento) {
            case 'solicitar_aterrizaje':
            case 'solicitar_despegue':
                $this->procesarSolicitud($id, $tipo, $evento);
                break;

           case 'aterrizo':
    $this->log[] = "🛬 {$tipo} #{$id} aterrizó.";
    // El avión ya está en tierra y la pista se libera de inmediato
    $this->estados[$id] = 'tierra';
    $this->pistaOcupada = false;
    $this->log[] = "✅ Pista liberada automáticamente tras aterrizaje de {$tipo} #{$id}";
    $this->asignarSiguiente();
    break;

case 'despego':
    $this->log[] = "🛫 {$tipo} #{$id} despegó.";
    // Vuelve a estado "volando" y liberamos la pista
    $this->estados[$id] = 'volando';
    $this->pistaOcupada = false;
    $this->log[] = "✅ Pista liberada automáticamente tras despegue de {$tipo} #{$id}";
    $this->asignarSiguiente();
    break;


          case 'despego':
    $this->log[] = "🛫 {$tipo} #{$id} despegó.";
    $this->estados[$id] = 'volando';

    // 👇 liberar automáticamente la pista al despegar
    $this->pistaOcupada = false;
    $this->log[] = "✅ Pista liberada automáticamente tras despegue de {$tipo} #{$id}";
    $this->asignarSiguiente();
    break;


            case 'liberar_pista':
                $this->pistaOcupada = false;
                $this->estados[$id] = 'tierra';
                $this->log[] = "✅ Pista liberada por {$tipo} #{$id}";
                $this->asignarSiguiente();
                break;
        }
    }

    private function procesarSolicitud(string $id, string $tipo, string $evento): void
    {
        $accion = $evento === 'solicitar_aterrizaje' ? 'aterrizaje' : 'despegue';

        if (!$this->pistaOcupada) {
            $this->pistaOcupada = true;
            $this->estados[$id] = 'en_pista';
            $this->log[] = "🟢 AUTORIZADO {$accion} para {$tipo} #{$id}";
        } else {
            $this->cola->enqueue($id.'|'.$accion);
            $this->estados[$id] = 'en_espera';
            $this->log[] = "⏳ EN ESPERA {$accion} para {$tipo} #{$id} (pista ocupada)";
        }
    }

    private function asignarSiguiente(): void
    {
        if ($this->pistaOcupada) return;

        if (!$this->cola->isEmpty()) {
            [$id, $accion] = explode('|', $this->cola->dequeue());
            $tipo = $this->aeronaves[$id]->tipo();
            $this->pistaOcupada = true;
            $this->estados[$id] = 'en_pista';
            $this->log[] = "🟢 AUTORIZADO {$accion} para {$tipo} #{$id} (siguiente en cola)";
        } else {
            $this->log[] = "ℹ️ Pista libre y sin cola.";
        }
    }

    /** Snapshot para la UI */
    public function snapshot(): array
    {
        // también devolvemos “meta” con lista amigable de aeronaves
        $meta = [];
        foreach ($this->aeronaves as $id => $a) {
            $meta[$id] = $a->tipo();
        }

        return [
            'pista_libre' => !$this->pistaOcupada,
            'estados'     => $this->estados,
            'tipos'       => $meta,
            'cola'        => iterator_to_array($this->cola),
            'log'         => $this->log,
        ];
    }
}
