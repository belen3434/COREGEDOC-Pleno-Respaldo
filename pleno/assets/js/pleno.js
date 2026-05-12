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

        function getItemsCount() {
            return summaryBody.querySelectorAll("tr[data-summary-item='true']").length;
        }

        function updateCount() {
            var total = getItemsCount();
            summaryCount.textContent = total + " ACTIVOS";
            emptyRow.hidden = total > 0;
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
                    if (value) {
                        return value;
                    }
                }
            }
            return defaultText;
        }

        function createDeleteButton() {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "pleno-delete-btn";
            button.setAttribute("aria-label", "Eliminar punto (solo visual)");

            var icon = document.createElement("i");
            icon.className = "fas fa-trash-alt";
            icon.setAttribute("aria-hidden", "true");
            button.appendChild(icon);

            button.addEventListener("click", function () {
                var row = button.closest("tr");
                if (row) {
                    row.remove();
                    updateCount();
                }
            });

            return button;
        }

        function addSummaryItem(typeText, typeClass, description) {
            var row = document.createElement("tr");
            row.setAttribute("data-summary-item", "true");

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

        if (btnAgregarComision) {
            btnAgregarComision.addEventListener("click", function () {
                var description = buildDescription(
                    "Comisión de Finanzas y Presupuesto Regional",
                    ["plenoTemaComision", "plenoComision"]
                );
                addSummaryItem("PERMANENTE", "pleno-point-badge-permanente", description);
                closeModal("modalAgregarComision");
            });
        }

        if (btnAgregarImprevista) {
            btnAgregarImprevista.addEventListener("click", function () {
                var description = buildDescription(
                    "Situación de Emergencia: Incendios Forestales Quilpué",
                    ["plenoObservacionImprevista", "plenoNombreImprevista"]
                );
                addSummaryItem("IMPREVISTA", "pleno-point-badge-imprevista", description);
                closeModal("modalComisionImprevista");
            });
        }

        if (btnAgregarTabla) {
            btnAgregarTabla.addEventListener("click", function () {
                var description = buildDescription(
                    "Aprobación Proyecto Pavimentación Rodelillo Etapa 3",
                    ["plenoDescripcionPunto", "plenoTituloPunto"]
                );
                addSummaryItem("TABLA", "pleno-point-badge-tabla", description);
                closeModal("modalPuntoTabla");
            });
        }

        updateCount();
    });
})();
