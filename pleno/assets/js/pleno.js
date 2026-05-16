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
        var comisionSelect = document.getElementById("plenoComision");
        var temaComisionSelect = document.getElementById("plenoTemaComision");

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

        updateCount();
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
        var statusSelect = document.getElementById("plenoEstadoSesion");

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
                validateRequired(timeInput, "La hora es obligatoria"),
                validateRequired(statusSelect, "Seleccione un estado de sesión")
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
            [sessionNumberInput, dateInput, timeInput, statusSelect].forEach(function (field) {
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
            { field: timeInput, eventName: "change", validate: function () { return validateRequired(timeInput, "La hora es obligatoria"); } },
            { field: statusSelect, eventName: "change", validate: function () { return validateRequired(statusSelect, "Seleccione un estado de sesión"); } }
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
        var draggedItem = null;

        if (!orderList) {
            return;
        }

        function getOrderItems() {
            return Array.prototype.slice.call(orderList.querySelectorAll(".orden-dia-item"));
        }

        function updateOrderNumbers() {
            getOrderItems().forEach(function (item, index) {
                var number = item.querySelector(".orden-dia-numero");

                if (number) {
                    number.textContent = (index + 1) + ".";
                }
            });
        }

        function clearDragOverState() {
            getOrderItems().forEach(function (item) {
                item.classList.remove("drag-over");
            });
        }

        function getDragAfterElement(container, clientY) {
            var items = getOrderItems().filter(function (item) {
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
                draggedItem = item;
                item.classList.add("dragging");

                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = "move";
                    event.dataTransfer.setData("text/plain", item.textContent.trim());
                }
            });

            item.addEventListener("dragend", function () {
                item.classList.remove("dragging");
                clearDragOverState();
                updateOrderNumbers();
                draggedItem = null;
            });
        });

        orderList.addEventListener("dragover", function (event) {
            var nextItem;

            if (!draggedItem) {
                return;
            }

            event.preventDefault();
            clearDragOverState();

            nextItem = getDragAfterElement(orderList, event.clientY);

            if (nextItem) {
                nextItem.classList.add("drag-over");
                orderList.insertBefore(draggedItem, nextItem);
            } else {
                orderList.appendChild(draggedItem);
            }

            updateOrderNumbers();
        });

        orderList.addEventListener("drop", function (event) {
            event.preventDefault();
            clearDragOverState();
            updateOrderNumbers();
        });

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
