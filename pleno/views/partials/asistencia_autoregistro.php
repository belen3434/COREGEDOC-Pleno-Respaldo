<?php

$asistenciaEstado = $data['pleno_asistencia_usuario'] ?? null;
$sesionAsistencia = is_array($asistenciaEstado) ? ($asistenciaEstado['sesion'] ?? null) : null;
$asistenciaMarcada = is_array($asistenciaEstado) && !empty($asistenciaEstado['ya_marco']);
$nombreSesionAsistencia = $sesionAsistencia
    ? trim((string)($sesionAsistencia['numero_sesion'] ?? 'Pleno en curso'))
    : '';

if (!function_exists('plenoAsistenciaH')) {
    function plenoAsistenciaH($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .pleno-attendance-self-card {
        border: 1px solid #e0e7ef;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 38, 56, 0.08);
        overflow: hidden;
    }

    .pleno-attendance-self-header {
        padding: 0.9rem 1.1rem;
        color: #ffffff;
        background: #0071bc;
        font-weight: 800;
    }

    .pleno-attendance-self-body {
        padding: 1.25rem;
        text-align: center;
    }

    .pleno-attendance-self-icon {
        width: 72px;
        height: 72px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        border-radius: 50%;
        color: #0071bc;
        background: rgba(0, 113, 188, 0.1);
        animation: pleno-attendance-pulse 2s infinite;
    }

    .pleno-attendance-self-card.is-confirmed {
        border-left: 6px solid #00a650;
    }

    .pleno-attendance-self-confirm-icon {
        color: #00a650;
        font-size: 3.2rem;
    }

    .pleno-attendance-self-btn {
        min-height: 48px;
        border: 0;
        border-radius: 999px;
        background: #f7931e;
        color: #ffffff;
        font-weight: 850;
        text-transform: uppercase;
        box-shadow: 0 5px 14px rgba(247, 147, 30, 0.28);
    }

    .pleno-attendance-self-btn:hover,
    .pleno-attendance-self-btn:focus {
        color: #ffffff;
        background: #e87b00;
    }

    .pleno-attendance-live-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        margin-right: 0.35rem;
        border-radius: 50%;
        background: #dc3545;
        animation: pleno-attendance-blink 1s infinite;
    }

    @keyframes pleno-attendance-pulse {
        0% { box-shadow: 0 0 0 0 rgba(0, 113, 188, 0.35); }
        70% { box-shadow: 0 0 0 18px rgba(0, 113, 188, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 113, 188, 0); }
    }

    @keyframes pleno-attendance-blink {
        50% { opacity: 0; }
    }
</style>

<section
    class="pleno-attendance-self-card mb-4<?php echo $asistenciaMarcada ? ' is-confirmed' : ''; ?>"
    id="plenoAttendanceSelfCard"
    data-id-sesion="<?php echo (int)($sesionAsistencia['id_sesion'] ?? 0); ?>"
    data-state="<?php echo $asistenciaMarcada ? 'confirmed' : ($sesionAsistencia ? 'active' : 'waiting'); ?>"
>
    <div class="pleno-attendance-self-header" id="plenoAttendanceSelfHeader">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $asistenciaMarcada ? 'Asistencia Confirmada' : ($sesionAsistencia ? 'Sesión Habilitada' : 'Esperando Inicio de Sesión'); ?>
    </div>
    <div class="pleno-attendance-self-body">
        <div id="plenoAttendanceWaiting" <?php echo $sesionAsistencia ? 'hidden' : ''; ?>>
            <span class="pleno-attendance-self-icon"><i class="fa-solid fa-chalkboard-user fa-2x"></i></span>
            <h2 class="h4 fw-bold text-primary mb-2">Esperando Inicio de Sesión...</h2>
            <p class="text-muted mb-0">La asistencia se habilitará cuando Secretaría inicie el pleno.</p>
        </div>

        <div id="plenoAttendanceRegister" <?php echo (!$sesionAsistencia || $asistenciaMarcada) ? 'hidden' : ''; ?>>
            <p class="text-uppercase text-muted fw-bold small mb-2">Bienvenido a la sesión:</p>
            <h2 class="h4 fw-bold mb-3" id="plenoAttendanceSessionName"><?php echo plenoAsistenciaH($nombreSesionAsistencia); ?></h2>
            <div class="alert alert-info border-0 bg-info bg-opacity-10 small mb-3">
                <i class="fas fa-clock me-1"></i> El registro de asistencia al Pleno está habilitado.
            </div>
            <button type="button" class="btn pleno-attendance-self-btn px-4" id="plenoRegisterAttendanceBtn">
                <i class="fas fa-user-check me-2"></i> Registrar mi asistencia
            </button>
        </div>

        <div id="plenoAttendanceConfirmed" <?php echo $asistenciaMarcada ? '' : 'hidden'; ?>>
            <div class="mb-3 pleno-attendance-self-confirm-icon"><i class="fas fa-id-badge"></i></div>
            <h2 class="h4 fw-bold mb-2 text-dark">Te encuentras en sesión</h2>
            <h3 class="h6 text-secondary mb-3" id="plenoAttendanceConfirmedName"><?php echo plenoAsistenciaH($nombreSesionAsistencia); ?></h3>
            <div class="d-inline-block bg-light border rounded-pill px-4 py-2">
                <span class="pleno-attendance-live-dot"></span>
                <span class="fw-bold text-danger small">EN CURSO</span>
            </div>
            <p class="text-muted mt-3 mb-0 small">Su asistencia ya fue registrada y confirmada.</p>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function () {
        "use strict";

        var card = document.getElementById("plenoAttendanceSelfCard");
        var btn = document.getElementById("plenoRegisterAttendanceBtn");
        var header = document.getElementById("plenoAttendanceSelfHeader");
        var waiting = document.getElementById("plenoAttendanceWaiting");
        var register = document.getElementById("plenoAttendanceRegister");
        var confirmed = document.getElementById("plenoAttendanceConfirmed");
        var sessionName = document.getElementById("plenoAttendanceSessionName");
        var confirmedName = document.getElementById("plenoAttendanceConfirmedName");

        if (!card) {
            return;
        }

        function showPanel(name) {
            waiting.hidden = name !== "waiting";
            register.hidden = name !== "register";
            confirmed.hidden = name !== "confirmed";
            card.classList.toggle("is-confirmed", name === "confirmed");

            if (name === "confirmed" && card.getAttribute("data-state") !== "confirmed") {
                card.setAttribute("data-state", "confirmed");
                document.dispatchEvent(new CustomEvent("pleno:asistencia-confirmada"));
            } else {
                card.setAttribute("data-state", name);
            }
        }

        function setSession(sesion) {
            var nombre = sesion && sesion.numero_sesion ? sesion.numero_sesion : "Pleno en curso";
            card.setAttribute("data-id-sesion", String(sesion && sesion.id_sesion ? sesion.id_sesion : 0));
            sessionName.textContent = nombre;
            confirmedName.textContent = nombre;
        }

        function refreshState() {
            fetch("ajax/asistencia_estado.php", { headers: { "Accept": "application/json" } })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    if (!payload || payload.success !== true || payload.status === "none") {
                        header.innerHTML = '<i class="fas fa-check-circle me-2"></i> Esperando Inicio de Sesión';
                        showPanel("waiting");
                        return;
                    }

                    setSession(payload.sesion || {});

                    if (payload.ya_marco) {
                        header.innerHTML = '<i class="fas fa-check-circle me-2"></i> Asistencia Confirmada';
                        showPanel("confirmed");
                        return;
                    }

                    header.innerHTML = '<i class="fas fa-check-circle me-2"></i> Sesión Habilitada';
                    showPanel("register");
                })
                .catch(function () {});
        }

        if (btn) {
            btn.addEventListener("click", function () {
                var idSesion = parseInt(card.getAttribute("data-id-sesion") || "0", 10);

                if (!idSesion) {
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registrando...';

                fetch("ajax/registrar_asistencia.php", {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ id_sesion: idSesion })
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok || !result.payload || result.payload.success !== true) {
                            throw new Error(result.payload && result.payload.mensaje ? result.payload.mensaje : "No fue posible registrar la asistencia.");
                        }

                        if (window.Swal && typeof window.Swal.fire === "function") {
                            window.Swal.fire({
                                icon: "success",
                                title: "¡Asistencia Registrada!",
                                text: "Se ha confirmado su presencia.",
                                confirmButtonColor: "#00a650",
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }

                        header.innerHTML = '<i class="fas fa-check-circle me-2"></i> Asistencia Confirmada';
                        showPanel("confirmed");
                        document.dispatchEvent(new CustomEvent("pleno:asistencia-registrada"));
                    })
                    .catch(function (error) {
                        if (window.Swal && typeof window.Swal.fire === "function") {
                            window.Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: error && error.message ? error.message : "Problema de conexión.",
                                confirmButtonColor: "#d33"
                            });
                        }
                    })
                    .finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-user-check me-2"></i> Registrar mi asistencia';
                    });
            });
        }

        refreshState();
        window.setInterval(refreshState, 5000);
    })();
</script>
