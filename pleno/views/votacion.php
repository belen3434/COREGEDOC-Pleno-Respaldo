<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>
<style>
    .pleno-vote-page {
        color: #1f3240;
    }

    .pleno-vote-eyebrow {
        margin-bottom: 0.35rem;
        color: #d21f2b;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .pleno-vote-title {
        margin: 0;
        color: #182b38;
        font-size: 2rem;
        font-weight: 800;
    }

    .pleno-vote-main-card,
    .pleno-vote-table-card {
        border: 1px solid #e3ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 38, 56, 0.08);
    }

    .pleno-vote-main-card {
        margin-top: 1.4rem;
        padding: 1.6rem;
    }

    .pleno-vote-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .pleno-vote-pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #eaf3ff;
        color: #0d6efd;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .pleno-vote-topic {
        margin: 0.85rem 0 0;
        color: #192f3e;
        font-size: 1.28rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .pleno-vote-meta {
        margin: 0.45rem 0 0;
        color: #6b7d88;
        font-size: 0.9rem;
        font-weight: 650;
    }

    .pleno-vote-close-btn {
        flex: 0 0 auto;
        min-height: 40px;
        padding: 0.55rem 1rem;
        border: 1px solid #e1b94e;
        border-radius: 10px;
        background: #fff4cc;
        color: #7a5a05;
        font-weight: 750;
    }

    .pleno-vote-counters {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .pleno-vote-counter {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 124px;
        padding: 1.25rem;
        border-radius: 14px;
        color: #ffffff;
    }

    .pleno-vote-counter-si {
        background: #138a36;
    }

    .pleno-vote-counter-no {
        background: #d72638;
    }

    .pleno-vote-counter-abs {
        background: #f28c18;
    }

    .pleno-vote-counter-icon {
        width: 50px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.22);
        font-size: 1.35rem;
    }

    .pleno-vote-counter-label {
        font-size: 0.9rem;
        font-weight: 850;
        letter-spacing: 0.04em;
    }

    .pleno-vote-counter-number {
        margin-top: 0.1rem;
        font-size: 2.55rem;
        font-weight: 900;
        line-height: 1;
    }

    .pleno-vote-tables {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
        margin-top: 1.25rem;
    }

    .pleno-vote-table-card {
        padding: 1.25rem;
    }

    .pleno-vote-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pleno-vote-table-title {
        margin: 0;
        color: #203949;
        font-size: 1.08rem;
        font-weight: 800;
    }

    .pleno-vote-participants {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: #eef2f5;
        color: #5e7180;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .pleno-vote-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pleno-vote-table th {
        padding: 0.75rem 0.85rem;
        background: #f1f5f8;
        color: #5f7280;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .pleno-vote-table td {
        padding: 0.85rem;
        border-bottom: 1px solid #edf2f5;
        color: #253d4d;
        font-weight: 650;
        vertical-align: middle;
    }

    .pleno-vote-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pleno-vote-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 88px;
        min-height: 28px;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #eef2f5;
        color: #667987;
        font-size: 0.78rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .pleno-vote-status-si {
        background: #e5f6eb;
        color: #147a34;
    }

    .pleno-vote-status-no {
        background: #fde8eb;
        color: #b51f2f;
    }

    .pleno-vote-status-abs {
        background: #fff0db;
        color: #9b5b08;
    }

    .pleno-vote-empty {
        margin-top: 1.25rem;
        padding: 1rem 1.25rem;
        border: 1px solid #dce8f1;
        border-radius: 12px;
        background: #f8fbfd;
        color: #5d7180;
        font-weight: 700;
    }

    .pleno-vote-exposition {
        margin-top: 1.25rem;
        padding: 1.25rem;
        border: 1px solid #dce8f1;
        border-radius: 14px;
        background: #f8fbfd;
        color: #243d4d;
    }

    .pleno-vote-exposition-message {
        margin: 0 0 1rem;
        color: #5d7180;
        font-weight: 800;
    }

    .pleno-vote-timer {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pleno-vote-timer-ring {
        width: 96px;
        height: 96px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: conic-gradient(#f28c18 var(--progress, 100%), #e8eef2 0);
    }

    .pleno-vote-timer-inner {
        width: 76px;
        height: 76px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #ffffff;
        color: #203949;
        font-size: 1.2rem;
        font-weight: 900;
    }

    .pleno-vote-timer-label {
        color: #5d7180;
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .pleno-vote-page:fullscreen {
        overflow: auto;
        padding: 1.5rem;
        background: #f6f8fa;
    }

    @media (max-width: 991.98px) {
        .pleno-vote-counters,
        .pleno-vote-tables {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-vote-main-card {
            padding: 1rem;
        }

        .pleno-vote-card-header,
        .pleno-vote-table-head {
            display: block;
        }

        .pleno-vote-close-btn {
            width: 100%;
            margin-top: 1rem;
        }

        .pleno-vote-participants {
            margin-top: 0.65rem;
        }

        .pleno-vote-table {
            min-width: 420px;
        }

        .pleno-vote-table-wrap {
            overflow-x: auto;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-vote-page" id="plenoVoteMonitor">
    <div class="pleno-vote-eyebrow">• VOTACIÓN EN VIVO</div>
    <h1 class="pleno-vote-title">Votación</h1>

    <section class="pleno-vote-main-card">
        <div class="pleno-vote-card-header">
            <div>
                <span class="pleno-vote-pill" id="plenoVoteStatus">CARGANDO VOTACIÓN</span>
                <h2 class="pleno-vote-topic" id="plenoVoteTopic">Consultando datos del pleno...</h2>
                <p class="pleno-vote-meta" id="plenoVoteMeta">Actualización automática cada 3 segundos.</p>
            </div>
            <button type="button" class="pleno-vote-close-btn" id="plenoFullscreenBtn">Pantalla completa</button>
        </div>

        <div class="pleno-vote-counters" id="plenoVoteCounters" aria-label="Conteo visual de votos">
            <div class="pleno-vote-counter pleno-vote-counter-si">
                <span class="pleno-vote-counter-icon"><i class="fas fa-check"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">SÍ</div>
                    <div class="pleno-vote-counter-number" id="plenoVoteCountSi">0</div>
                </div>
            </div>
            <div class="pleno-vote-counter pleno-vote-counter-no">
                <span class="pleno-vote-counter-icon"><i class="fas fa-times"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">NO</div>
                    <div class="pleno-vote-counter-number" id="plenoVoteCountNo">0</div>
                </div>
            </div>
            <div class="pleno-vote-counter pleno-vote-counter-abs">
                <span class="pleno-vote-counter-icon"><i class="fas fa-hand-paper"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">ABS</div>
                    <div class="pleno-vote-counter-number" id="plenoVoteCountAbs">0</div>
                </div>
            </div>
        </div>

        <div class="pleno-vote-exposition d-none" id="plenoVoteExposition">
            <p class="pleno-vote-exposition-message" id="plenoVoteExpositionMessage">Punto en exposición. No requiere votación.</p>
            <div class="pleno-vote-timer" aria-label="Cronómetro de exposición">
                <div class="pleno-vote-timer-ring" id="plenoVoteTimerRing">
                    <span class="pleno-vote-timer-inner" id="plenoVoteTimerText">02:00</span>
                </div>
                <div>
                    <div class="pleno-vote-timer-label">Tiempo de exposición</div>
                    <div class="pleno-vote-meta">Cronómetro visual de referencia.</div>
                </div>
            </div>
        </div>
    </section>

    <div class="pleno-vote-empty d-none" id="plenoVoteEmpty">No hay votación activa en este momento.</div>

    <div class="pleno-vote-tables" id="plenoVoteTables">
        <section class="pleno-vote-table-card">
            <div class="pleno-vote-table-head">
                <h3 class="pleno-vote-table-title" id="plenoVoteGroupOneTitle">Consejeros/as 1 al 6</h3>
                <span class="pleno-vote-participants" id="plenoVoteGroupOneCount">0 PARTICIPANTES</span>
            </div>
            <div class="pleno-vote-table-wrap">
                <table class="pleno-vote-table">
                    <thead>
                        <tr>
                            <th>Consejero/a</th>
                            <th>Voto</th>
                        </tr>
                    </thead>
                    <tbody id="plenoVoteGroupOneBody"></tbody>
                </table>
            </div>
        </section>

        <section class="pleno-vote-table-card">
            <div class="pleno-vote-table-head">
                <h3 class="pleno-vote-table-title" id="plenoVoteGroupTwoTitle">Consejeros/as 7 al 12</h3>
                <span class="pleno-vote-participants" id="plenoVoteGroupTwoCount">0 PARTICIPANTES</span>
            </div>
            <div class="pleno-vote-table-wrap">
                <table class="pleno-vote-table">
                    <thead>
                        <tr>
                            <th>Consejero/a</th>
                            <th>Voto</th>
                        </tr>
                    </thead>
                    <tbody id="plenoVoteGroupTwoBody"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
    (function () {
        var monitor = document.getElementById("plenoVoteMonitor");
        var fullscreenBtn = document.getElementById("plenoFullscreenBtn");
        var statusEl = document.getElementById("plenoVoteStatus");
        var topicEl = document.getElementById("plenoVoteTopic");
        var metaEl = document.getElementById("plenoVoteMeta");
        var emptyEl = document.getElementById("plenoVoteEmpty");
        var countersEl = document.getElementById("plenoVoteCounters");
        var tablesEl = document.getElementById("plenoVoteTables");
        var expositionEl = document.getElementById("plenoVoteExposition");
        var expositionMessageEl = document.getElementById("plenoVoteExpositionMessage");
        var timerRingEl = document.getElementById("plenoVoteTimerRing");
        var timerTextEl = document.getElementById("plenoVoteTimerText");
        var countSiEl = document.getElementById("plenoVoteCountSi");
        var countNoEl = document.getElementById("plenoVoteCountNo");
        var countAbsEl = document.getElementById("plenoVoteCountAbs");
        var groupOneBody = document.getElementById("plenoVoteGroupOneBody");
        var groupTwoBody = document.getElementById("plenoVoteGroupTwoBody");
        var groupOneCount = document.getElementById("plenoVoteGroupOneCount");
        var groupTwoCount = document.getElementById("plenoVoteGroupTwoCount");
        var groupOneTitle = document.getElementById("plenoVoteGroupOneTitle");
        var groupTwoTitle = document.getElementById("plenoVoteGroupTwoTitle");
        var pollingMs = 3000;
        var timerSeconds = 120;
        var timerInterval = null;
        var timerPoint = "";
        var timerFinishedPoint = "";

        function limpiar(texto) {
            return String(texto || "");
        }

        function formatoTimer(segundos) {
            var minutos = Math.floor(segundos / 60);
            var resto = segundos % 60;
            return String(minutos).padStart(2, "0") + ":" + String(resto).padStart(2, "0");
        }

        function detenerTimer() {
            if (timerInterval) {
                window.clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        function iniciarTimer(punto) {
            if (timerPoint === punto && timerInterval) {
                return;
            }

            if (timerFinishedPoint === punto) {
                timerPoint = punto;
                timerSeconds = 0;
                if (timerTextEl) {
                    timerTextEl.textContent = "00:00";
                }
                if (timerRingEl) {
                    timerRingEl.style.setProperty("--progress", "0%");
                }
                return;
            }

            detenerTimer();
            timerPoint = punto;
            timerFinishedPoint = "";
            timerSeconds = 120;

            function pintarTimer() {
                var progreso = Math.max(timerSeconds, 0) / 120 * 100;
                if (timerTextEl) {
                    timerTextEl.textContent = formatoTimer(Math.max(timerSeconds, 0));
                }
                if (timerRingEl) {
                    timerRingEl.style.setProperty("--progress", progreso + "%");
                }
            }

            pintarTimer();
            timerInterval = window.setInterval(function () {
                timerSeconds = Math.max(timerSeconds - 1, 0);
                pintarTimer();

                if (timerSeconds <= 0) {
                    timerFinishedPoint = punto;
                    detenerTimer();
                }
            }, 1000);
        }

        function etiquetaVoto(voto) {
            if (voto === "SI") {
                return "Sí";
            }
            if (voto === "NO") {
                return "No";
            }
            if (voto === "ABSTENCION") {
                return "Abstención";
            }
            return "Sin votar";
        }

        function claseVoto(voto) {
            if (voto === "SI") {
                return " pleno-vote-status-si";
            }
            if (voto === "NO") {
                return " pleno-vote-status-no";
            }
            if (voto === "ABSTENCION") {
                return " pleno-vote-status-abs";
            }
            return "";
        }

        function renderGrupo(tbody, participantes) {
            tbody.innerHTML = "";

            if (!participantes.length) {
                var filaVacia = document.createElement("tr");
                var celda = document.createElement("td");
                celda.colSpan = 2;
                celda.textContent = "Sin participantes para mostrar.";
                filaVacia.appendChild(celda);
                tbody.appendChild(filaVacia);
                return;
            }

            participantes.forEach(function (participante) {
                var fila = document.createElement("tr");
                var nombre = document.createElement("td");
                var voto = document.createElement("td");
                var estado = document.createElement("span");

                nombre.textContent = limpiar(participante.nombre);
                estado.className = "pleno-vote-status" + claseVoto(participante.voto);
                estado.textContent = etiquetaVoto(participante.voto);

                voto.appendChild(estado);
                fila.appendChild(nombre);
                fila.appendChild(voto);
                tbody.appendChild(fila);
            });
        }

        function renderEstado(data) {
            var votacion = data.votacion || {};
            var sesion = data.sesion || {};
            var conteo = data.conteo || {};
            var participantes = Array.isArray(data.participantes) ? data.participantes : [];
            var punto = limpiar(votacion.punto_numero);
            var estado = limpiar(votacion.estado_votacion);
            var tituloPunto = punto
                ? (limpiar(votacion.titulo) + (limpiar(votacion.descripcion) ? " - " + limpiar(votacion.descripcion) : ""))
                : "Sin votacion registrada";
            var mitad = Math.ceil(participantes.length / 2);
            var grupoUno = participantes.slice(0, mitad);
            var grupoDos = participantes.slice(mitad);

            countSiEl.textContent = Number(conteo.SI || 0);
            countNoEl.textContent = Number(conteo.NO || 0);
            countAbsEl.textContent = Number(conteo.ABSTENCION || 0);

            if (data.hay_votacion !== true) {
                detenerTimer();
                timerPoint = "";
                timerFinishedPoint = "";
                countersEl.classList.remove("d-none");
                tablesEl.classList.remove("d-none");
                expositionEl.classList.add("d-none");
                statusEl.textContent = "SIN VOTACION ACTIVA";
                topicEl.textContent = limpiar(data.mensaje || "No hay votacion activa en este momento.");
                metaEl.textContent = "La pantalla se actualizara automaticamente.";
                emptyEl.textContent = limpiar(data.mensaje || "No hay votacion activa en este momento.");
                emptyEl.classList.remove("d-none");
                groupOneTitle.textContent = "Consejeros/as";
                groupTwoTitle.textContent = "Consejeros/as";
                groupOneCount.textContent = "0 PARTICIPANTES";
                groupTwoCount.textContent = "0 PARTICIPANTES";
                renderGrupo(groupOneBody, []);
                renderGrupo(groupTwoBody, []);
                return;
            }

            if (data.requiere_votacion === false || data.modo === "exposicion") {
                countersEl.classList.add("d-none");
                tablesEl.classList.add("d-none");
                expositionEl.classList.remove("d-none");
                emptyEl.classList.add("d-none");
                statusEl.textContent = "PUNTO EN EXPOSICION";
                topicEl.textContent = tituloPunto || "Punto en exposicion";
                metaEl.textContent = "Pleno " + limpiar(sesion.numero_sesion || sesion.id_sesion || "") + " - No requiere votacion";
                expositionMessageEl.textContent = limpiar(data.mensaje || "Punto en exposicion. No requiere votacion.");
                iniciarTimer(punto);
                return;
            }

            detenerTimer();
            timerPoint = "";
            timerFinishedPoint = "";
            countersEl.classList.remove("d-none");
            tablesEl.classList.remove("d-none");
            expositionEl.classList.add("d-none");
            statusEl.textContent = estado === "votacion_en_curso" ? "PUNTO ACTUAL EN VOTACION" : "ULTIMA VOTACION REGISTRADA";
            topicEl.textContent = tituloPunto;
            metaEl.textContent = "Pleno " + limpiar(sesion.numero_sesion || sesion.id_sesion || "") + " - " + Number(data.total_votos || 0) + " votos de " + Number(data.total_participantes || 0) + " participantes";
            emptyEl.classList.toggle("d-none", data.hay_votacion === true);

            groupOneTitle.textContent = "Consejeros/as 1 al " + grupoUno.length;
            groupTwoTitle.textContent = "Consejeros/as " + (grupoUno.length + 1) + " al " + participantes.length;
            groupOneCount.textContent = grupoUno.length + " PARTICIPANTES";
            groupTwoCount.textContent = grupoDos.length + " PARTICIPANTES";

            renderGrupo(groupOneBody, grupoUno);
            renderGrupo(groupTwoBody, grupoDos);
        }
        function cargarEstado() {
            fetch("ajax/votacion_estado.php", {
                headers: {
                    "Accept": "application/json"
                }
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.payload || result.payload.success !== true) {
                        throw new Error(result.payload && result.payload.mensaje ? result.payload.mensaje : "No fue posible cargar la votación.");
                    }

                    renderEstado(result.payload);
                })
                .catch(function (error) {
                    statusEl.textContent = "SIN CONEXIÓN";
                    topicEl.textContent = error && error.message ? error.message : "No fue posible cargar la votación.";
                    metaEl.textContent = "Se reintentará automáticamente.";
                });
        }

        fullscreenBtn.addEventListener("click", function () {
            var target = monitor || document.documentElement;

            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }

            if (target.requestFullscreen) {
                target.requestFullscreen();
            }
        });

        cargarEstado();
        window.setInterval(cargarEstado, pollingMs);
    }());
</script>
