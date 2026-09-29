/**
 * Lo de Carlitos — app.js
 * Funciones JS base de la aplicación.
 */

/* ══════════════════════════════════════════════════════════════════
   Confirmaciones de borrado
   ══════════════════════════════════════════════════════════════════ */

/**
 * Muestra un diálogo de confirmación antes de seguir un enlace de borrado.
 *
 * @param {string} [mensaje] - Mensaje personalizado (opcional).
 * @returns {boolean}
 */
function confirmDelete(mensaje) {
    var msg = mensaje || '¿Estás seguro de que querés eliminar este registro? Esta acción no se puede deshacer.';
    return confirm(msg);
}

/* ══════════════════════════════════════════════════════════════════
   Módulo de Ventas (ST-08)
   ══════════════════════════════════════════════════════════════════ */

var productRowIndex = 0;
var pagoRowIndex = 0;

/**
 * Formatea un número como moneda (ej. 1.234,56).
 * @param {number} valor 
 * @returns {string}
 */
function formatCurrency(valor) {
    return '$' + (valor || 0).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Agrega una nueva fila de producto a la tabla de venta.
 */
function addProductRow() {
    var tbody = document.getElementById('tbodyProductos');
    if (!tbody) return;

    var idx = productRowIndex++;
    var tr = document.createElement('tr');
    tr.id = 'product-row-' + idx;

    var optionsHtml = '<option value="">-- Seleccionar producto --</option>';
    if (typeof ventaProductos !== 'undefined' && Array.isArray(ventaProductos)) {
        ventaProductos.forEach(function(prod) {
            optionsHtml += '<option value="' + prod.id_producto + '" data-precio="' + prod.precio_venta + '">' +
                (prod.nombre || '') + (prod.especie ? ' (' + prod.especie + ')' : '') +
                ' - $' + parseFloat(prod.precio_venta).toFixed(2) +
                '</option>';
        });
    }

    tr.innerHTML = 
        '<td>' +
            '<select class="form-select form-select-sm select-producto" name="detalle[' + idx + '][id_producto]" required onchange="onProductoSelected(this, ' + idx + ')">' +
                optionsHtml +
            '</select>' +
        '</td>' +
        '<td>' +
            '<input type="number" step="0.01" min="0" class="form-control form-control-sm text-end input-precio" name="detalle[' + idx + '][precio_unitario]" id="precio_' + idx + '" value="0.00" required oninput="calcularSubtotal(' + idx + ')">' +
        '</td>' +
        '<td>' +
            '<input type="number" step="1" min="1" class="form-control form-control-sm text-center input-cantidad" name="detalle[' + idx + '][cantidad]" id="cantidad_' + idx + '" value="1" required oninput="calcularSubtotal(' + idx + ')">' +
        '</td>' +
        '<td>' +
            '<input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center input-descuento" name="detalle[' + idx + '][descuento]" id="descuento_' + idx + '" value="0" oninput="calcularSubtotal(' + idx + ')">' +
        '</td>' +
        '<td>' +
            '<div class="text-end fw-bold fs-6 subtotal-text" id="subtotal_' + idx + '">$0,00</div>' +
        '</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this, \'producto\')" title="Quitar producto">' +
                '<i class="fa-solid fa-trash"></i>' +
            '</button>' +
        '</td>';

    tbody.appendChild(tr);
    calcularSubtotal(idx);
}

/**
 * Callback al seleccionar un producto en el select: auto-rellena el precio.
 */
function onProductoSelected(selectElem, idx) {
    var selectedOption = selectElem.options[selectElem.selectedIndex];
    var precio = selectedOption.getAttribute('data-precio') || 0;
    var precioInput = document.getElementById('precio_' + idx);
    if (precioInput) {
        precioInput.value = parseFloat(precio).toFixed(2);
    }
    calcularSubtotal(idx);
}

/**
 * Calcula el subtotal de una fila de producto y actualiza el total general.
 */
function calcularSubtotal(idx) {
    var precioInput = document.getElementById('precio_' + idx);
    var cantidadInput = document.getElementById('cantidad_' + idx);
    var descuentoInput = document.getElementById('descuento_' + idx);
    var subtotalElem = document.getElementById('subtotal_' + idx);

    if (!precioInput || !cantidadInput || !subtotalElem) return;

    var precio = parseFloat(precioInput.value) || 0;
    var cantidad = parseInt(cantidadInput.value, 10) || 0;
    var descuento = parseFloat(descuentoInput ? descuentoInput.value : 0) || 0;

    if (descuento < 0) descuento = 0;
    if (descuento > 100) descuento = 100;

    var subtotal = precio * cantidad * (1 - (descuento / 100));
    subtotalElem.textContent = formatCurrency(subtotal);
    subtotalElem.setAttribute('data-subtotal', subtotal.toFixed(2));

    calcularTotal();
}

/**
 * Calcula la suma de todos los subtotales de productos.
 */
function calcularTotal() {
    var subtotalElems = document.querySelectorAll('#tbodyProductos [data-subtotal]');
    var total = 0;
    subtotalElems.forEach(function(elem) {
        total += parseFloat(elem.getAttribute('data-subtotal')) || 0;
    });

    var lblTotalVenta = document.getElementById('lblTotalVenta');
    if (lblTotalVenta) {
        lblTotalVenta.textContent = formatCurrency(total);
        lblTotalVenta.setAttribute('data-total', total.toFixed(2));
    }

    calcularTotalPagos();
}

/**
 * Agrega una nueva fila de medio de pago a la tabla.
 */
function addPagoRow() {
    var tbody = document.getElementById('tbodyPagos');
    if (!tbody) return;

    var idx = pagoRowIndex++;
    var tr = document.createElement('tr');
    tr.id = 'pago-row-' + idx;

    var optionsHtml = '<option value="">-- Seleccionar medio --</option>';
    if (typeof ventaMediosPago !== 'undefined' && Array.isArray(ventaMediosPago)) {
        ventaMediosPago.forEach(function(mp) {
            optionsHtml += '<option value="' + mp.id_medio_pago + '" data-nombre="' + mp.medio_pago + '">' +
                (mp.medio_pago || '') +
                '</option>';
        });
    }

    // Sugerir monto restante por defecto si hay saldo pendiente
    var totalVenta = parseFloat(document.getElementById('lblTotalVenta')?.getAttribute('data-total')) || 0;
    var totalPagosActual = 0;
    document.querySelectorAll('.input-pago-monto').forEach(function(inp) {
        totalPagosActual += parseFloat(inp.value) || 0;
    });
    var montoDefault = Math.max(0, totalVenta - totalPagosActual);

    tr.innerHTML = 
        '<td class="align-top">' +
            '<select class="form-select form-select-sm select-medio-pago" name="pago[' + idx + '][id_medio_pago]" required onchange="toggleChequeForm(this, ' + idx + ')">' +
                optionsHtml +
            '</select>' +
        '</td>' +
        '<td class="align-top">' +
            '<input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end input-pago-monto" name="pago[' + idx + '][monto]" id="pago_monto_' + idx + '" value="' + (montoDefault > 0 ? montoDefault.toFixed(2) : '') + '" required oninput="calcularTotalPagos()">' +
        '</td>' +
        '<td>' +
            '<div id="cheque-container-' + idx + '" class="cheque-form-wrapper d-none border rounded p-2 bg-light">' +
                '<div class="row g-2">' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold">Banco <span class="text-danger">*</span></label>' +
                        '<input type="text" maxlength="20" class="form-control form-control-sm cheque-field" name="pago[' + idx + '][cheque][banco]" placeholder="Ej: Galicia">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold">Nº Cheque <span class="text-danger">*</span></label>' +
                        '<input type="number" step="1" class="form-control form-control-sm cheque-field" name="pago[' + idx + '][cheque][numero_cheque]" placeholder="12345678">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold">Fecha Emisión</label>' +
                        '<input type="date" class="form-control form-control-sm cheque-field" name="pago[' + idx + '][cheque][fecha_emision]" value="' + new Date().toISOString().split('T')[0] + '">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold">Fecha Pago <span class="text-danger">*</span></label>' +
                        '<input type="date" class="form-control form-control-sm cheque-field" name="pago[' + idx + '][cheque][fecha_pago]" value="' + new Date().toISOString().split('T')[0] + '">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold d-block">Tipo Cheque</label>' +
                        '<div class="form-check form-check-inline">' +
                            '<input class="form-check-input" type="radio" name="pago[' + idx + '][cheque][tipo_cheque]" id="tipo_fisico_' + idx + '" value="Físico" checked>' +
                            '<label class="form-check-label small" for="tipo_fisico_' + idx + '">Físico</label>' +
                        '</div>' +
                        '<div class="form-check form-check-inline">' +
                            '<input class="form-check-input" type="radio" name="pago[' + idx + '][cheque][tipo_cheque]" id="tipo_echeq_' + idx + '" value="E-cheque">' +
                            '<label class="form-check-label small" for="tipo_echeq_' + idx + '">E-cheque</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small mb-0 fw-semibold">Observaciones</label>' +
                        '<input type="text" maxlength="255" class="form-control form-control-sm" name="pago[' + idx + '][cheque][observaciones]" placeholder="Opcional">' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div id="cta-cte-container-' + idx + '" class="d-none text-muted small p-1">' +
                '<i class="fa-solid fa-info-circle text-info me-1"></i>Se generará un saldo de deuda en Cuenta Corriente para este cliente.' +
            '</div>' +
        '</td>' +
        '<td class="text-center align-top">' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this, \'pago\')" title="Quitar pago">' +
                '<i class="fa-solid fa-trash"></i>' +
            '</button>' +
        '</td>';

    tbody.appendChild(tr);
    calcularTotalPagos();
}

/**
 * Muestra u oculta el sub-formulario de cheque según el medio seleccionado.
 */
function toggleChequeForm(selectElem, idx) {
    var selectedOption = selectElem.options[selectElem.selectedIndex];
    var medioNombre = (selectedOption.getAttribute('data-nombre') || '').trim();
    var chequeContainer = document.getElementById('cheque-container-' + idx);
    var ctaCteContainer = document.getElementById('cta-cte-container-' + idx);

    if (medioNombre === 'Cheque' || medioNombre === 'E-cheque') {
        if (chequeContainer) {
            chequeContainer.classList.remove('d-none');
            // Requerir campos de cheque
            chequeContainer.querySelectorAll('.cheque-field').forEach(function(inp) {
                if (inp.name.includes('[banco]') || inp.name.includes('[numero_cheque]') || inp.name.includes('[fecha_pago]')) {
                    inp.required = true;
                }
            });

            // Preseleccionar radio button según medio
            var radioEcheq = document.getElementById('tipo_echeq_' + idx);
            var radioFisico = document.getElementById('tipo_fisico_' + idx);
            if (medioNombre === 'E-cheque' && radioEcheq) {
                radioEcheq.checked = true;
            } else if (radioFisico) {
                radioFisico.checked = true;
            }
        }
        if (ctaCteContainer) ctaCteContainer.classList.add('d-none');
    } else if (medioNombre === 'Cuenta Corriente') {
        if (chequeContainer) {
            chequeContainer.classList.add('d-none');
            chequeContainer.querySelectorAll('.cheque-field').forEach(function(inp) {
                inp.required = false;
            });
        }
        if (ctaCteContainer) ctaCteContainer.classList.remove('d-none');
    } else {
        if (chequeContainer) {
            chequeContainer.classList.add('d-none');
            chequeContainer.querySelectorAll('.cheque-field').forEach(function(inp) {
                inp.required = false;
            });
        }
        if (ctaCteContainer) ctaCteContainer.classList.add('d-none');
    }
}

/**
 * Elimina una fila (de producto o de pago) y recalcula totales.
 */
function removeRow(btn, tipo) {
    var tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        if (tipo === 'producto') {
            calcularTotal();
        } else {
            calcularTotalPagos();
        }
    }
}

/**
 * Calcula la suma de todos los medios de pago, compara con el total de venta y actualiza indicadores.
 */
function calcularTotalPagos() {
    var totalPagos = 0;
    var pagoInputs = document.querySelectorAll('.input-pago-monto');
    pagoInputs.forEach(function(inp) {
        totalPagos += parseFloat(inp.value) || 0;
    });

    var lblTotalPagos = document.getElementById('lblTotalPagos');
    if (lblTotalPagos) {
        lblTotalPagos.textContent = formatCurrency(totalPagos);
    }

    var totalVenta = parseFloat(document.getElementById('lblTotalVenta')?.getAttribute('data-total')) || 0;
    var diff = totalPagos - totalVenta;
    var badge = document.getElementById('lblDiferenciaBadge');

    if (badge) {
        if (Math.abs(diff) < 0.01) {
            badge.className = 'badge bg-success fs-6';
            badge.innerHTML = '<i class="fa-solid fa-check me-1"></i>¡Montos coinciden!';
        } else if (diff < 0) {
            badge.className = 'badge bg-danger fs-6';
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>Faltan ' + formatCurrency(Math.abs(diff));
        } else {
            badge.className = 'badge bg-warning text-dark fs-6';
            badge.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i>Sobran ' + formatCurrency(diff);
        }
    }
}

/**
 * Validación previa al submit del formulario de venta.
 */
function validateVenta() {
    var prodRows = document.querySelectorAll('#tbodyProductos tr');
    if (prodRows.length === 0) {
        alert('Debe agregar al menos un producto a la venta.');
        return false;
    }

    var pagoRows = document.querySelectorAll('#tbodyPagos tr');
    if (pagoRows.length === 0) {
        alert('Debe agregar al menos un medio de pago.');
        return false;
    }

    var totalVenta = parseFloat(document.getElementById('lblTotalVenta')?.getAttribute('data-total')) || 0;
    if (totalVenta <= 0) {
        alert('El total de la venta debe ser mayor a 0.');
        return false;
    }

    var totalPagos = 0;
    document.querySelectorAll('.input-pago-monto').forEach(function(inp) {
        totalPagos += parseFloat(inp.value) || 0;
    });

    if (Math.abs(totalPagos - totalVenta) >= 0.05) {
        alert('La suma de los medios de pago (' + formatCurrency(totalPagos) + ') no coincide con el total de la venta (' + formatCurrency(totalVenta) + ').');
        return false;
    }

    return true;
}

// Inicialización automática de la vista de creación de venta
document.addEventListener('DOMContentLoaded', function() {
    var formVenta = document.getElementById('formVenta');
    if (formVenta) {
        formVenta.addEventListener('submit', function(e) {
            if (!validateVenta()) {
                e.preventDefault();
            }
        });

        // Agregar fila inicial de producto y medio de pago
        if (document.querySelectorAll('#tbodyProductos tr').length === 0) {
            addProductRow();
        }
        if (document.querySelectorAll('#tbodyPagos tr').length === 0) {
            addPagoRow();
        }
    }

    // ── Auto-dismiss de alertas flash después de 5 segundos ──────────
    var flashAlerts = document.querySelectorAll('.flash-alert');
    flashAlerts.forEach(function(alertEl) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });
});
