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

        if (createSessionForm) {
            createSessionForm.addEventListener("reset", function () {
                var rows = summaryBody.querySelectorAll("tr[data-summary-item='true']");
                rows.forEach(function (row) {
                    row.remove();
                });

                window.setTimeout(updateCount, 0);
            });
        }

        updateCount();
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
