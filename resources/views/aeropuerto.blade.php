<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Torre de Control — Mediator</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
<header class="bg-slate-900 text-white">
  <div class="max-w-6xl mx-auto px-4 py-5 flex items-center justify-between">
    <h1 class="text-xl font-bold"> Torre de Control — Patrón Mediador</h1>
    <a href="{{ route('aeropuerto.reiniciar') }}" class="text-sm underline decoration-indigo-400">Reiniciar</a>
  </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-8 grid md:grid-cols-3 gap-6">
  <section class="md:col-span-1 bg-white rounded-2xl shadow p-5">
    <h2 class="font-semibold text-slate-700 mb-4">Pista</h2>
    <div class="flex items-center gap-2 mb-4">
      <span id="pista-led" class="inline-block w-2 h-2 rounded-full bg-gray-300"></span>
      <span id="pista-texto" class="text-slate-600">—</span>
    </div>

    <h3 class="font-medium text-slate-700 mb-2">Aeronaves</h3>
    <div id="aeronaves" class="space-y-3"></div>
  </section>

  <section class="md:col-span-2 bg-white rounded-2xl shadow p-5">
    <h2 class="font-semibold text-slate-700 mb-3">Cronología de eventos</h2>
    <ul id="log" class="space-y-2 max-h-[60vh] overflow-auto"></ul>
  </section>
</main>

<footer class="text-center text-xs text-slate-500 pb-6">UCR</footer>

<script>
  const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const LABELS = {
    volando:   {txt:'VOLANDO',   cls:'bg-sky-100 text-sky-700'},
    en_espera: {txt:'EN ESPERA', cls:'bg-amber-100 text-amber-700'},
    en_pista:  {txt:'EN PISTA',  cls:'bg-purple-100 text-purple-700'},
    tierra:    {txt:'EN TIERRA', cls:'bg-emerald-100 text-emerald-700'},
  };

  const pistaLed   = document.getElementById('pista-led');
  const pistaTexto = document.getElementById('pista-texto');
  const divAeros   = document.getElementById('aeronaves');
  const log        = document.getElementById('log');

  const rutas = {
    estado:  '{{ route('aeropuerto.estado') }}',
    accion:  '{{ route('aeropuerto.accion') }}',
  };

  function setPista(libre) {
    pistaLed.className = 'inline-block w-2 h-2 rounded-full ' + (libre ? 'bg-green-500' : 'bg-red-500');
    pistaTexto.textContent = 'Pista: ' + (libre ? 'LIBRE' : 'OCUPADA');
  }

  function linea(texto) {
    const li = document.createElement('li');
    li.className = 'px-3 py-2 rounded-xl border text-slate-700 border-slate-200 bg-slate-50';
    li.textContent = texto;
    log.appendChild(li);
    log.scrollTop = log.scrollHeight;
  }

  function chip(label, action, id, disabled = false) {
    const b = document.createElement('button');
    b.className = 'px-3 py-1.5 text-xs rounded-full bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-40';
    b.textContent = label;
    b.disabled = disabled;
    b.onclick = () => enviarAccion(id, action);
    return b;
  }

  function renderAeronaves(estados, tipos) {
    divAeros.innerHTML = '';
    Object.entries(estados).forEach(([id, estado]) => {
      const card = document.createElement('div');
      card.className = 'rounded-xl border border-slate-200 p-3';

      const header = document.createElement('div');
      header.className = 'flex items-center justify-between';
      header.innerHTML = `<div class="font-medium text-slate-800">${tipos[id]} <span class="text-slate-400">#${id}</span></div>
                          <span class="px-2 py-0.5 rounded-lg text-xs ${LABELS[estado].cls}">${LABELS[estado].txt}</span>`;
      card.appendChild(header);

      const acciones = document.createElement('div');
      acciones.className = 'mt-3 flex flex-wrap gap-2';

      acciones.appendChild(chip('Solicitar aterrizaje', 'solicitar_aterrizaje', id, estado!=='volando'));
      acciones.appendChild(chip('Marcar aterrizado', 'aterrizo', id, estado!=='en_pista'));
      acciones.appendChild(chip('Liberar pista', 'liberar_pista', id, estado!=='en_pista'));
      acciones.appendChild(chip('Solicitar despegue', 'solicitar_despegue', id, estado!=='tierra'));
      acciones.appendChild(chip('Marcar despegó', 'despego', id, estado!=='en_pista'));

      card.appendChild(acciones);
      divAeros.appendChild(card);
    });
  }

  async function cargarEstado() {
    const res = await fetch(rutas.estado);
    const data = await res.json();
    setPista(data.pista_libre);
    renderAeronaves(data.estados, data.tipos);
    log.innerHTML = '';
    data.log.forEach(linea);
  }

  async function enviarAccion(id, action) {
    const res = await fetch(rutas.accion, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({id, action})
    });
    const data = await res.json();
    setPista(data.pista_libre);
    renderAeronaves(data.estados, data.tipos);
    log.innerHTML = '';
    data.log.forEach(linea);
  }

  cargarEstado();
</script>
</body>
</html>
