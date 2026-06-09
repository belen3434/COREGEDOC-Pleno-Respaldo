/*
 * Interaccion visual del resumen de puntos agendados en Crear sesion plenaria.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var summaryBody = document.getElementById("plenoSummaryBody");
        var summaryCount = document.getElementById("plenoSummaryCount");
        var emptyRow = document.getElementById("plenoSummaryEmptyRow");

        if (!summaryBody || !summaryCount || !emptyRow) {
            return;
        }

        var btnAgregarComision = document.getElementById("btnAgregarComisionResumen");
        var btnAgregarImprevista = document.getElementById("btnAgregarImprevistaResumen");
        var btnAgregarTabla = document.getElementById("btnAgregarTablaResumen");
        var createSessionForm = document.getElementById("plenoCrearSesionForm");
        var editSessionForm = document.getElementById("plenoEditarSesionForm");
        var summaryForm = createSessionForm || editSessionForm;
        var comisionSelect = document.getElementById("plenoComision");
        var temaComisionSelect = document.getElementById("plenoTemaComision");
        var puntosJsonInput = document.getElementById("plenoPuntosResumenJson");
        var puntosEliminadosJsonInput = document.getElementById("plenoPuntosEliminadosJson");
        var puntosInicialesData = document.getElementById("plenoPuntosInicialesData");
        var puntosEliminados = [];

        function getItemsCount() {
            return summaryBody.querySelectorAll("tr[data-summary-item='true']").length;
        }

        function updateCount() {
            var total = getItemsCount();
            summaryCount.textContent = total + " ACTIVOS";
            emptyRow.hidden = total > 0;
            syncPuntosJsonInput();
        }

        function syncPuntosJsonInput() {
            if (!puntosJsonInput) {
                return;
            }

            var rows = summaryBody.querySelectorAll("tr[data-summary-item='true']");
            var puntos = [];

            rows.forEach(function (row, index) {
                puntos.push({
                    id: row.getAttribute("data-id-punto") || "",
                    id_comision: row.getAttribute("data-id-comision") || "",
                    id_tema: row.getAttribute("data-id-tema") || "",
                    tipo_punto: row.getAttribute("data-tipo-punto") || "",
                    nombre_imprevista: row.getAttribute("data-nombre-imprevista") || "",
                    observacion_imprevista: row.getAttribute("data-observacion-imprevista") || "",
                    titulo_punto: row.getAttribute("data-titulo-punto") || "",
                    descripcion_punto: row.getAttribute("data-descripcion-punto") || "",
                    seccion_orden: row.getAttribute("data-seccion-orden") || "varios",
                    orden: index + 1
                });
            });

            puntosJsonInput.value = JSON.stringify(puntos);
        }

        function syncPuntosEliminadosInput() {
            if (!puntosEliminadosJsonInput) {
                return;
            }

            puntosEliminadosJsonInput.value = JSON.stringify(puntosEliminados);
        }

        function closeModal(modalId) {
            if (!window.bootstrap) {
                return;
            }

            var modalElement = document.getElementById(modalId);
            if (!modalElement) {
                return;
            }

            var modalInstance = window.bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        function buildDescription(defaultText, inputIds) {
            for (var i = 0; i < inputIds.length; i += 1) {
                var field = document.getElementById(inputIds[i]);
                if (field && typeof field.value === "string") {
                    var value = field.value.trim();

                    if (field.tagName === "SELECT") {
                        var selectedOption = field.options[field.selectedIndex];
                        if (selectedOption) {
                            value = selectedOption.text.trim();
                        }
                    }

                    if (value) {
                        return value;
                    }
                }
            }
            return defaultText;
        }

        function clearTemaSelect(message) {
            if (!temaComisionSelect) {
                return;
            }

            temaComisionSelect.innerHTML = "";
            var option = document.createElement("option");
            option.value = "";
            option.textContent = message;
            temaComisionSelect.appendChild(option);
        }

        function setTemaLoading(isLoading) {
            if (!temaComisionSelect) {
                return;
            }

            temaComisionSelect.disabled = isLoading;
        }

        function loadTemasByComision(comisionId) {
            if (!temaComisionSelect) {
                return;
            }

            if (!comisionId) {
                clearTemaSelect("Seleccione primero una comisión");
                temaComisionSelect.disabled = true;
                return;
            }

            clearTemaSelect("Cargando temas...");
            setTemaLoading(true);

            fetch("index.php?action=listar_temas_comision&idComision=" + encodeURIComponent(comisionId), {
                method: "GET",
                headers: {
                    "Accept": "application/json"
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error("No fue posible cargar temas.");
                    }

                    return response.json();
                })
                .then(function (payload) {
                    var temas = Array.isArray(payload.temas) ? payload.temas : [];

                    clearTemaSelect(
                        temas.length > 0
                            ? "Seleccionar tema"
                            : "No existen temas asociados"
                    );

                    if (temas.length > 0) {
                        temas.forEach(function (tema) {
                            var option = document.createElement("option");
                            option.value = String(tema.idTema || "");
                            option.textContent = String(tema.nombreTema || "");
                            temaComisionSelect.appendChild(option);
                        });
                    }

                    temaComisionSelect.disabled = false;
                })
                .catch(function () {
                    clearTemaSelect("No fue posible cargar los temas");
                    temaComisionSelect.disabled = true;
                });
        }

        function createDeleteButton() {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-sm btn-outline-danger";
            button.textContent = "Quitar";
            button.setAttribute("aria-label", "Quitar punto (solo visual)");

            button.addEventListener("click", function () {
                var row = button.closest("tr");
                if (row) {
                    var idPunto = parseInt(row.getAttribute("data-id-punto") || "0", 10);
                    if (idPunto > 0 && puntosEliminados.indexOf(idPunto) === -1) {
                        puntosEliminados.push(idPunto);
                        syncPuntosEliminadosInput();
                    }
                    row.remove();
                    updateCount();
                }
            });

            return button;
        }

        function addSummaryItem(typeText, typeClass, description, metadata) {
            var row = document.createElement("tr");
            row.setAttribute("data-summary-item", "true");

            if (metadata && typeof metadata === "object") {
                Object.keys(metadata).forEach(function (key) {
                    if (metadata[key] !== null && metadata[key] !== undefined) {
                        row.setAttribute(key, String(metadata[key]));
                    }
                });
            }

            var typeCell = document.createElement("td");
            var badge = document.createElement("span");
            badge.className = "pleno-point-badge " + typeClass;
            badge.textContent = typeText;
            typeCell.appendChild(badge);

            var descriptionCell = document.createElement("td");
            descriptionCell.textContent = description;

            var actionCell = document.createElement("td");
            actionCell.className = "text-center";
            actionCell.appendChild(createDeleteButton());

            row.appendChild(typeCell);
            row.appendChild(descriptionCell);
            row.appendChild(actionCell);
            summaryBody.appendChild(row);

            updateCount();
        }

        function normalizeTipoPunto(tipoPunto) {
            return String(tipoPunto || "").trim().toUpperCase();
        }

        function hydrateSummaryFromStoredPoints() {
            if (!puntosInicialesData) {
                return;
            }

            var raw = puntosInicialesData.textContent || "[]";
            var points;

            try {
                points = JSON.parse(raw);
            } catch (error) {
                points = [];
            }

            if (!Array.isArray(points) || points.length === 0) {
                updateCount();
                return;
            }

            points.forEach(function (point) {
                var tipoPunto = normalizeTipoPunto(point.tipo_punto || "COMISION");
                if (tipoPunto === "IMPREVISTA") {
                    var nombreImprevista = (point.nombre_imprevista || "").trim();
                    var observacionImprevista = (point.observacion_imprevista || "").trim();
                    var descripcionImprevista = nombreImprevista || "Comisión Imprevista";

                    if (observacionImprevista) {
                        descripcionImprevista += " — " + observacionImprevista;
                    }

                    addSummaryItem(
                        "Imprevista",
                        "pleno-point-badge-imprevista",
                        descripcionImprevista,
                        {
                            "data-id-punto": point.id || "",
                            "data-tipo-punto": "imprevista",
                            "data-id-comision": "",
                            "data-id-tema": "",
                            "data-nombre-imprevista": nombreImprevista,
                            "data-observacion-imprevista": observacionImprevista,
                            "data-seccion-orden": point.seccion_orden || "varios",
                            "data-vigente": "1"
                        }
                    );
                    return;
                }

                if (tipoPunto === "TABLA") {
                    var tituloPunto = (point.titulo_punto || "").trim();
                    var descripcionPunto = (point.descripcion_punto || "").trim();
                    var descripcionTabla = tituloPunto || "Punto de Tabla";

                    if (descripcionPunto) {
                        descripcionTabla += " — " + descripcionPunto;
                    }

                    addSummaryItem(
                        "Tabla",
                        "pleno-point-badge-tabla",
                        descripcionTabla,
                        {
                            "data-id-punto": point.id || "",
                            "data-tipo-punto": "tabla",
                            "data-id-comision": "",
                            "data-id-tema": "",
                            "data-nombre-imprevista": "",
                            "data-observacion-imprevista": "",
                            "data-titulo-punto": tituloPunto,
                            "data-descripcion-punto": descripcionPunto,
                            "data-seccion-orden": point.seccion_orden || "varios",
                            "data-vigente": "1"
                        }
                    );
                    return;
                }

                var nombreComision = (point.nombreComision || "").trim();
                var nombreTema = (point.nombreTema || "").trim();
                var description = nombreComision;

                if (nombreTema) {
                    description += " — Tema: " + nombreTema;
                }

                addSummaryItem(
                    "Comisión",
                    "pleno-point-badge-permanente",
                    description || "Comisión",
                    {
                        "data-id-punto": point.id || "",
                        "data-tipo-punto": "comision",
                        "data-id-comision": point.id_comision || "",
                        "data-id-tema": point.id_tema || "",
                        "data-nombre-imprevista": "",
                        "data-observacion-imprevista": "",
                        "data-titulo-punto": "",
                        "data-descripcion-punto": "",
                        "data-seccion-orden": point.seccion_orden || "varios",
                        "data-vigente": "1"
                    }
                );
            });
        }

        if (btnAgregarComision) {
            btnAgregarComision.addEventListener("click", function () {
                var selectedComision = comisionSelect && comisionSelect.selectedIndex >= 0
                    ? comisionSelect.options[comisionSelect.selectedIndex]
                    : null;
                var selectedTema = temaComisionSelect && temaComisionSelect.selectedIndex >= 0
                    ? temaComisionSelect.options[temaComisionSelect.selectedIndex]
                    : null;
                var idComision = selectedComision ? (selectedComision.value || "").trim() : "";
                var nombreComision = selectedComision ? selectedComision.text.trim() : "";
                var idTema = selectedTema ? (selectedTema.value || "").trim() : "";
                var nombreTema = selectedTema ? selectedTema.text.trim() : "";

                if (!idComision) {
                    if (window.Swal && typeof window.Swal.fire === "function") {
                        window.Swal.fire({
                            icon: "warning",
                            title: "Seleccione una comisión",
                            text: "Debe seleccionar una comisión antes de agregar."
                        });
                    }
                    return;
                }

                if (!idTema) {
                    if (window.Swal && typeof window.Swal.fire === "function") {
                        window.Swal.fire({
                            icon: "warning",
                            title: "Seleccione un tema",
                            text: "Debe seleccionar un tema asociado a la comisión."
                        });
                    }
                    return;
                }

                addSummaryItem(
                    "Comisión",
                    "pleno-point-badge-permanente",
                    nombreComision + " — Tema: " + nombreTema,
                    {
                        "data-id-punto": "",
                        "data-tipo-punto": "comision",
                        "data-id-comision": idComision,
                        "data-id-tema": idTema,
                        "data-nombre-imprevista": "",
                        "data-observacion-imprevista": "",
                        "data-titulo-punto": "",
                        "data-descripcion-punto": "",
                        "data-vigente": "1"
                    }
                );
                closeModal("modalAgregarComision");
                if (comisionSelect) {
                    comisionSelect.value = "";
                }
                loadTemasByComision("");
            });
        }

        if (btnAgregarImprevista) {
            btnAgregarImprevista.addEventListener("click", function () {
                var nombreInput = document.getElementById("plenoNombreImprevista");
                var observacionInput = document.getElementById("plenoObservacionImprevista");
                var nombreImprevista = nombreInput ? nombreInput.value.trim() : "";
                var observacionImprevista = observacionInput ? observacionInput.value.trim() : "";
                var description = nombreImprevista;

                if (!nombreImprevista) {
                    if (nombreInput) {
                        nombreInput.classList.add("is-invalid");
                        nombreInput.focus();
                    }

                    if (window.Swal && typeof window.Swal.fire === "function") {
                        window.Swal.fire({
                            icon: "warning",
                            title: "Nombre obligatorio",
                            text: "Debe ingresar el nombre de la comisión imprevista."
                        });
                    }
                    return;
                }

                if (nombreInput) {
                    nombreInput.classList.remove("is-invalid");
                }

                if (observacionImprevista) {
                    description += " — " + observacionImprevista;
                }

                addSummaryItem("Imprevista", "pleno-point-badge-imprevista", description, {
                    "data-id-punto": "",
                    "data-tipo-punto": "imprevista",
                    "data-id-comision": "",
                    "data-id-tema": "",
                    "data-nombre-imprevista": nombreImprevista,
                    "data-observacion-imprevista": observacionImprevista,
                    "data-titulo-punto": "",
                    "data-descripcion-punto": "",
                    "data-vigente": "1"
                });
                closeModal("modalComisionImprevista");
                if (nombreInput) {
                    nombreInput.value = "";
                    nombreInput.classList.remove("is-invalid");
                }
                if (observacionInput) {
                    observacionInput.value = "";
                }
            });
        }

        if (btnAgregarTabla) {
            btnAgregarTabla.addEventListener("click", function () {
                var tituloInput = document.getElementById("plenoTituloPunto");
                var descripcionInput = document.getElementById("plenoDescripcionPunto");
                var ordenInput = document.getElementById("plenoOrdenPunto");
                var tituloPunto = tituloInput ? tituloInput.value.trim() : "";
                var descripcionPunto = descripcionInput ? descripcionInput.value.trim() : "";
                var description = tituloPunto;

                if (!tituloPunto) {
                    if (tituloInput) {
                        tituloInput.classList.add("is-invalid");
                        tituloInput.focus();
                    }

                    if (window.Swal && typeof window.Swal.fire === "function") {
                        window.Swal.fire({
                            icon: "warning",
                            title: "Título obligatorio",
                            text: "Debe ingresar el título del punto de tabla."
                        });
                    }
                    return;
                }

                if (tituloInput) {
                    tituloInput.classList.remove("is-invalid");
                }

                if (descripcionPunto) {
                    description += " — " + descripcionPunto;
                }

                addSummaryItem("Tabla", "pleno-point-badge-tabla", description, {
                    "data-id-punto": "",
                    "data-tipo-punto": "tabla",
                    "data-id-comision": "",
                    "data-id-tema": "",
                    "data-nombre-imprevista": "",
                    "data-observacion-imprevista": "",
                    "data-titulo-punto": tituloPunto,
                    "data-descripcion-punto": descripcionPunto,
                    "data-vigente": "1",
                    "data-orden-tabla": ordenInput && ordenInput.value ? ordenInput.value.trim() : ""
                });
                closeModal("modalPuntoTabla");
                if (tituloInput) {
                    tituloInput.value = "";
                    tituloInput.classList.remove("is-invalid");
                }
                if (descripcionInput) {
                    descripcionInput.value = "";
                }
                if (ordenInput) {
                    ordenInput.value = "";
                }
            });
        }

        if (summaryForm) {
            summaryForm.addEventListener("reset", function () {
                var rows = summaryBody.querySelectorAll("tr[data-summary-item='true']");
                rows.forEach(function (row) {
                    row.remove();
                });

                puntosEliminados = [];
                syncPuntosEliminadosInput();
                loadTemasByComision("");
                window.setTimeout(updateCount, 0);
            });
        }

        if (comisionSelect) {
            comisionSelect.addEventListener("change", function () {
                loadTemasByComision(comisionSelect.value);
            });
            loadTemasByComision(comisionSelect.value);
        }

        hydrateSummaryFromStoredPoints();
        syncPuntosEliminadosInput();
        updateCount();
    });
})();

/*
 * Campo visual de lugar en Crear/Editar sesion plenaria.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var placeSelect = document.getElementById("plenoLugarSesionVisual");
        var otherPlaceWrapper = document.getElementById("plenoLugarOtroVisualWrapper");
        var otherPlaceInput = document.getElementById("plenoLugarOtroVisual");
        var sessionForm = document.getElementById("plenoCrearSesionForm") || document.getElementById("plenoEditarSesionForm");

        if (!placeSelect || !otherPlaceWrapper || !otherPlaceInput || !sessionForm) {
            return;
        }

        function isOtherPlaceSelected() {
            return placeSelect.value === "otra";
        }

        function clearOtherPlaceError() {
            otherPlaceInput.classList.remove("is-invalid");
        }

        function syncOtherPlace() {
            var showOtherPlace = isOtherPlaceSelected();

            otherPlaceWrapper.classList.toggle("d-none", !showOtherPlace);
            otherPlaceInput.disabled = !showOtherPlace;
            otherPlaceInput.required = showOtherPlace;

            if (!showOtherPlace) {
                otherPlaceInput.value = "";
                clearOtherPlaceError();
            }
        }

        function validateOtherPlace() {
            var isValid = !isOtherPlaceSelected() || otherPlaceInput.value.trim() !== "";

            otherPlaceInput.classList.toggle("is-invalid", !isValid);

            if (!isValid) {
                var feedback = document.getElementById("plenoLugarOtroVisualFeedback");
                if (feedback) {
                    feedback.textContent = "Debe ingresar el nombre de la dependencia.";
                }
                otherPlaceInput.focus();
            }

            return isValid;
        }

        placeSelect.addEventListener("change", syncOtherPlace);
        otherPlaceInput.addEventListener("input", clearOtherPlaceError);

        sessionForm.addEventListener("submit", function (event) {
            if (!validateOtherPlace()) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);

        sessionForm.addEventListener("reset", function () {
            window.setTimeout(syncOtherPlace, 0);
        });

        syncOtherPlace();
    });
})();

/*
 * Validaciones visuales del formulario Crear sesion plenaria.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("plenoCrearSesionForm");

        if (!form) {
            return;
        }

        var formAlert = document.getElementById("plenoFormAlert");
        var sessionNumberInput = document.getElementById("numeroSesionVisual");
        var dateInput = document.getElementById("plenoFechaSesion");
        var timeInput = document.getElementById("plenoHoraSesion");

        function getTodayValue() {
            var today = new Date();
            var year = today.getFullYear();
            var month = String(today.getMonth() + 1).padStart(2, "0");
            var day = String(today.getDate()).padStart(2, "0");

            return year + "-" + month + "-" + day;
        }

        function setFeedback(field, message) {
            var feedbackId = field.getAttribute("aria-describedby");
            var feedback = feedbackId ? document.getElementById(feedbackId) : null;

            if (feedback && message) {
                feedback.textContent = message;
            }
        }

        function markField(field, isValid, message) {
            if (!field) {
                return;
            }

            field.classList.toggle("is-invalid", !isValid);
            field.classList.toggle("is-valid", isValid);
            setFeedback(field, message);
        }

        function hasValue(field) {
            return field && field.value.trim() !== "";
        }

        function validateRequired(field, message) {
            var isValid = hasValue(field);
            markField(field, isValid, message);

            return isValid;
        }

        function validateDate() {
            var todayValue = getTodayValue();

            if (!dateInput) {
                return true;
            }

            dateInput.min = todayValue;

            if (!hasValue(dateInput)) {
                markField(dateInput, false, "Seleccione una fecha");
                return false;
            }

            if (dateInput.value < todayValue) {
                markField(dateInput, false, "No puede seleccionar una fecha anterior a hoy");
                return false;
            }

            markField(dateInput, true, "");
            return true;
        }

        function validateForm() {
            var validations = [
                validateRequired(sessionNumberInput, "Debe ingresar un número de sesión"),
                validateDate(),
                validateRequired(timeInput, "La hora es obligatoria")
            ];
            var isValid = validations.every(function (result) {
                return result;
            });

            if (formAlert) {
                formAlert.classList.toggle("d-none", isValid);
            }

            return isValid;
        }

        function clearValidation() {
            [sessionNumberInput, dateInput, timeInput].forEach(function (field) {
                if (field) {
                    field.classList.remove("is-invalid", "is-valid");
                }
            });

            if (formAlert) {
                formAlert.classList.add("d-none");
            }
        }

        if (dateInput) {
            dateInput.min = getTodayValue();
        }

        [
            { field: sessionNumberInput, eventName: "input", validate: function () { return validateRequired(sessionNumberInput, "Debe ingresar un número de sesión"); } },
            { field: dateInput, eventName: "change", validate: validateDate },
            { field: timeInput, eventName: "change", validate: function () { return validateRequired(timeInput, "La hora es obligatoria"); } }
        ].forEach(function (item) {
            if (!item.field) {
                return;
            }

            item.field.addEventListener(item.eventName, function () {
                item.validate();

                if (form.querySelectorAll(".is-invalid").length === 0 && formAlert) {
                    formAlert.classList.add("d-none");
                }
            });
        });

        form.addEventListener("submit", function (event) {
            if (!validateForm()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        form.addEventListener("reset", function () {
            window.setTimeout(clearValidation, 0);
        });
    });
})();

/*
 * Solicita confirmacion antes de salir de Crear sesion plenaria.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var backLink = document.getElementById("plenoCrearSesionBackLink");
        var modalElement = document.getElementById("plenoConfirmarSalidaModal");
        var confirmButton = document.getElementById("plenoConfirmarSalidaAceptar");
        var redirectUrl;

        if (!backLink) {
            return;
        }

        redirectUrl = backLink.getAttribute("href") || "index.php";

        function redirectToBackUrl() {
            window.location.href = redirectUrl;
        }

        function showBootstrapFallback() {
            var modalInstance;

            if (!window.bootstrap || !modalElement || !confirmButton) {
                return;
            }

            modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        }

        backLink.addEventListener("click", function (event) {
            event.preventDefault();

            if (window.Swal && typeof window.Swal.fire === "function") {
                window.Swal.fire({
                    title: "¿Está seguro de volver atrás?",
                    text: "Los cambios no guardados se perderán.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, volver",
                    cancelButtonText: "Cancelar",
                    reverseButtons: true,
                    focusCancel: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        redirectToBackUrl();
                    }
                });

                return;
            }

            showBootstrapFallback();
        });

        if (confirmButton) {
            confirmButton.addEventListener("click", function () {
                redirectToBackUrl();
            });
        }
    });
})();

/*
 * Permite reordenar visualmente los puntos del orden del dia.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var orderList = document.getElementById("ordenDiaLista");
        var syncStatus = document.getElementById("ordenDiaSyncStatus");
        var sessionId = orderList ? parseInt(orderList.getAttribute("data-sesion-id") || "0", 10) : 0;
        var updateUrl = orderList ? String(orderList.getAttribute("data-update-url") || "").trim() : "";
        var draggedItem = null;
        var isSaving = false;
        var dragStartSignature = "";
        var lastCommittedOrder = [];
        var hasPendingDrop = false;

        if (!orderList) {
            return;
        }

        function getDropZones() {
            return Array.prototype.slice.call(orderList.querySelectorAll(".orden-dia-items"));
        }

        function getOrderItems(container) {
            return Array.prototype.slice.call((container || orderList).querySelectorAll(".orden-dia-item"));
        }

        function getItemSection(item) {
            var section = item.closest(".orden-dia-seccion");

            return section ? String(section.getAttribute("data-seccion") || "varios").trim() : "varios";
        }

        function closestDropZone(target) {
            return target && typeof target.closest === "function" ? target.closest(".orden-dia-items") : null;
        }

        function updateOrderNumbers() {
            getDropZones().forEach(function (dropZone) {
                getOrderItems(dropZone).forEach(function (item, index) {
                    var number = item.querySelector(".orden-dia-numero");

                    if (number) {
                        number.textContent = (index + 1) + ".";
                    }
                });
            });
        }

        function setSyncStatus(message, isError) {
            if (!syncStatus) {
                return;
            }

            syncStatus.textContent = message || "";
            syncStatus.classList.toggle("text-danger", Boolean(isError));
            syncStatus.classList.toggle("text-muted", !isError);
        }

        function getPointId(item) {
            return parseInt(item.getAttribute("data-id") || "0", 10);
        }

        function getCurrentOrderPayload() {
            var payload = [];

            getDropZones().forEach(function (dropZone) {
                getOrderItems(dropZone).forEach(function (item, index) {
                    payload.push({
                        id: getPointId(item),
                        orden: index + 1,
                        seccion_orden: getItemSection(item)
                    });
                });
            });

            return payload;
        }

        function getCurrentOrderSignature() {
            return getCurrentOrderPayload().map(function (item) {
                return item.id + ":" + item.seccion_orden + ":" + item.orden;
            }).join(",");
        }

        function isValidOrderPayload(payload) {
            if (sessionId <= 0 || !updateUrl || !Array.isArray(payload) || payload.length === 0) {
                return false;
            }

            return payload.every(function (item) {
                return Number.isInteger(item.id) && item.id > 0 && Number.isInteger(item.orden) && item.orden > 0 && typeof item.seccion_orden === "string" && item.seccion_orden.trim() !== "";
            });
        }

        function applyOrderSnapshot(snapshot) {
            var itemsById = {};
            var zonesBySection = {};

            getOrderItems().forEach(function (item) {
                itemsById[String(getPointId(item))] = item;
            });

            getDropZones().forEach(function (zone) {
                var section = zone.closest(".orden-dia-seccion");
                var sectionKey = section ? String(section.getAttribute("data-seccion") || "varios") : "varios";

                zonesBySection[sectionKey] = zone;
            });

            snapshot.forEach(function (entry) {
                var item = itemsById[String(entry.id)];
                var zone = zonesBySection[String(entry.seccion_orden || "varios")];

                if (item && zone) {
                    zone.appendChild(item);
                }
            });

            updateOrderNumbers();
        }

        function captureCommittedOrder() {
            lastCommittedOrder = getCurrentOrderPayload().map(function (item) {
                return {
                    id: item.id,
                    seccion_orden: item.seccion_orden
                };
            });
        }

        function revertToCommittedOrder() {
            if (!Array.isArray(lastCommittedOrder) || lastCommittedOrder.length === 0) {
                return;
            }

            applyOrderSnapshot(lastCommittedOrder);
        }

        function persistOrder() {
            var payload = getCurrentOrderPayload();

            if (!isValidOrderPayload(payload) || isSaving) {
                return;
            }

            isSaving = true;
            orderList.setAttribute("aria-busy", "true");
            setSyncStatus("Guardando nuevo orden...", false);

            fetch(updateUrl, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    return response.json()
                        .catch(function () {
                            return {
                                ok: false,
                                error: "La respuesta del servidor no es válida."
                            };
                        })
                        .then(function (data) {
                            return {
                                ok: response.ok,
                                data: data
                            };
                        });
                })
                .then(function (result) {
                    if (!result.ok || !result.data || result.data.ok !== true) {
                        throw new Error(result.data && result.data.error ? result.data.error : "No fue posible guardar el nuevo orden.");
                    }

                    captureCommittedOrder();
                    setSyncStatus("Orden actualizado correctamente.", false);
                })
                .catch(function (error) {
                    revertToCommittedOrder();
                    setSyncStatus(error && error.message ? error.message : "No fue posible guardar el nuevo orden.", true);

                    if (window.Swal && typeof window.Swal.fire === "function") {
                        window.Swal.fire({
                            icon: "error",
                            title: "No fue posible guardar el orden",
                            text: error && error.message ? error.message : "Intente nuevamente."
                        });
                    }
                })
                .finally(function () {
                    isSaving = false;
                    orderList.removeAttribute("aria-busy");
                });
        }

        function clearDragOverState() {
            getOrderItems().forEach(function (item) {
                item.classList.remove("drag-over");
            });
        }

        function getDragAfterElement(container, clientY) {
            var items = getOrderItems(container).filter(function (item) {
                return item !== draggedItem;
            });
            var closest = {
                offset: Number.NEGATIVE_INFINITY,
                element: null
            };

            items.forEach(function (item) {
                var box = item.getBoundingClientRect();
                var offset = clientY - box.top - (box.height / 2);

                if (offset < 0 && offset > closest.offset) {
                    closest = {
                        offset: offset,
                        element: item
                    };
                }
            });

            return closest.element;
        }

        getOrderItems().forEach(function (item) {
            item.addEventListener("dragstart", function (event) {
                if (isSaving) {
                    event.preventDefault();
                    return;
                }

                draggedItem = item;
                hasPendingDrop = false;
                dragStartSignature = getCurrentOrderSignature();
                item.classList.add("dragging");

                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = "move";
                    event.dataTransfer.setData("text/plain", item.textContent.trim());
                }
            });

            item.addEventListener("dragend", function () {
                var orderChanged = dragStartSignature && getCurrentOrderSignature() !== dragStartSignature;

                item.classList.remove("dragging");
                clearDragOverState();
                updateOrderNumbers();
                draggedItem = null;

                if (orderChanged && !hasPendingDrop) {
                    persistOrder();
                }

                hasPendingDrop = false;
            });
        });

        getDropZones().forEach(function (dropZone) {
            dropZone.addEventListener("dragover", function (event) {
                var nextItem;

                if (!draggedItem || isSaving) {
                    return;
                }

                event.preventDefault();
                clearDragOverState();

                nextItem = getDragAfterElement(dropZone, event.clientY);

                if (nextItem) {
                    nextItem.classList.add("drag-over");
                    dropZone.insertBefore(draggedItem, nextItem);
                } else {
                    dropZone.appendChild(draggedItem);
                }

                updateOrderNumbers();
            });

            dropZone.addEventListener("drop", function (event) {
                event.preventDefault();
                hasPendingDrop = true;
                clearDragOverState();
                updateOrderNumbers();

                if (dragStartSignature && getCurrentOrderSignature() !== dragStartSignature) {
                    persistOrder();
                }
            });
        });

        orderList.addEventListener("dragover", function (event) {
            var nextItem;
            var targetDropZone = closestDropZone(event.target);

            if (!draggedItem || isSaving || targetDropZone) {
                return;
            }

            event.preventDefault();
            clearDragOverState();

            nextItem = getDragAfterElement(orderList, event.clientY);

            if (nextItem) {
                nextItem.classList.add("drag-over");
                nextItem.parentNode.insertBefore(draggedItem, nextItem);
            } else {
                getDropZones()[0].appendChild(draggedItem);
            }

            updateOrderNumbers();
        });

        orderList.addEventListener("drop", function (event) {
            if (closestDropZone(event.target)) {
                return;
            }

            event.preventDefault();
            hasPendingDrop = true;
            clearDragOverState();
            updateOrderNumbers();

            if (dragStartSignature && getCurrentOrderSignature() !== dragStartSignature) {
                persistOrder();
            }
        });

        captureCommittedOrder();
        updateOrderNumbers();
    });
})();

/*
 * Inserta el menú visual interno de Pleno bajo el botón "Volver al inicio"
 * sin modificar el sidebar general del sistema.
 */
(function () {
    "use strict";

    function mountPlenoSidebarMenu() {
        var template = document.getElementById("plenoSidebarMenuTemplate");
        var sidebarBody = document.querySelector("#sidebar-wrapper .custom-scrollbar");

        if (!template || !sidebarBody || sidebarBody.querySelector(".pleno-sidebar-menu")) {
            return;
        }

        sidebarBody.appendChild(template.content.cloneNode(true));
    }

    function isMobileSidebar() {
        return window.matchMedia("(max-width: 768px)").matches;
    }

    function isSidebarHidden() {
        var isToggled = document.body.classList.contains("sb-sidenav-toggled");
        return isMobileSidebar() ? !isToggled : isToggled;
    }

    function syncPlenoSidebarClasses(plenoSidebar, plenoContent) {
        var hidden = isSidebarHidden();

        plenoSidebar.classList.toggle("pleno-sidebar-hidden", hidden);

        if (plenoContent) {
            plenoContent.classList.toggle("pleno-content-full", hidden);
        }
    }

    function setupPlenoSidebarToggle() {
        var toggle = document.getElementById("sidebarToggle");
        var plenoSidebar = document.getElementById("sidebar-wrapper");
        var plenoContent = document.getElementById("page-content-wrapper");

        if (!toggle || !plenoSidebar || toggle.dataset.plenoSidebarReady === "true") {
            return;
        }

        toggle.dataset.plenoSidebarReady = "true";
        plenoSidebar.setAttribute("data-pleno-sidebar", "true");

        if (plenoContent) {
            plenoContent.setAttribute("data-pleno-content", "true");
        }

        syncPlenoSidebarClasses(plenoSidebar, plenoContent);

        toggle.addEventListener("click", function (e) {
            var wasToggled = document.body.classList.contains("sb-sidenav-toggled");

            e.preventDefault();

            window.setTimeout(function () {
                var isToggled = document.body.classList.contains("sb-sidenav-toggled");

                if (isToggled === wasToggled) {
                    document.body.classList.toggle("sb-sidenav-toggled");
                }

                syncPlenoSidebarClasses(plenoSidebar, plenoContent);
            }, 0);
        });

        window.addEventListener("resize", function () {
            syncPlenoSidebarClasses(plenoSidebar, plenoContent);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            mountPlenoSidebarMenu();
            setupPlenoSidebarToggle();
        });
    } else {
        mountPlenoSidebarMenu();
        setupPlenoSidebarToggle();
    }
})();
