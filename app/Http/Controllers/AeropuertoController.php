<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aeropuerto\TorreDeControl;
use App\Models\Aeropuerto\Avion;
use App\Models\Aeropuerto\Helicoptero;
use App\Models\Aeropuerto\Avioneta;

class AeropuertoController extends Controller
{
    private function torre(): TorreDeControl
    {
        $torre = session('torre');
        if (!$torre instanceof TorreDeControl) {
            $torre = new TorreDeControl();
            new Avion('AV001', $torre);
            new Helicoptero('HE001', $torre);
            new Avioneta('LT001', $torre);
            session(['torre' => $torre]);
        }
        return $torre;
    }

    public function index()
    {
        $torre = $this->torre();
        return view('aeropuerto', ['estado' => $torre->snapshot()]);
    }

    public function estado()
    {
        return response()->json($this->torre()->snapshot());
    }

    public function accion(Request $request)
    {
        $request->validate([
            'id'     => 'required|string',
            'action' => 'required|in:solicitar_aterrizaje,aterrizo,liberar_pista,solicitar_despegue,despego',
        ]);

        $torre = $this->torre();
        $torre->notificarPorId($request->id, $request->action);
        session(['torre' => $torre]);

        return response()->json($torre->snapshot());
    }

    public function reiniciar()
    {
        session()->forget('torre');
        return redirect()->route('aeropuerto.index');
    }
}
