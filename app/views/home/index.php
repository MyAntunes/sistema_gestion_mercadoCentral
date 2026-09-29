<?php
/**
 * Vista: Dashboard / Home
 * Variables disponibles via extract($indicadores):
 *   $total_ventas, $monto_total_ventas, $total_clientes, $total_proveedores,
 *   $total_productos, $clientes_con_cta_cte, $cheques_en_cartera, $ventas_hoy
 */
?>

<h1 class="h4 mb-4 fw-semibold">
    <i class="fa-solid fa-gauge-high me-2 text-primary"></i>Dashboard
</h1>

<!-- ═══════════════════════════════════════════════════════════
     INDICADORES
════════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Total Ventas -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-blue p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Total Ventas</span>
                <i class="fa-solid fa-shopping-cart stat-icon text-primary"></i>
            </div>
            <div class="stat-value text-primary mt-2">
                <?= htmlspecialchars((string)(int)$total_ventas) ?>
            </div>
        </div>
    </div>

    <!-- Monto Total Vendido -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-green p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Monto Total Vendido</span>
                <i class="fa-solid fa-dollar-sign stat-icon text-success"></i>
            </div>
            <div class="stat-value text-success mt-2" style="font-size:1.5rem;">
                $<?= number_format((float)$monto_total_ventas, 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <!-- Total Clientes -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-orange p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Total Clientes</span>
                <i class="fa-solid fa-users stat-icon text-warning"></i>
            </div>
            <div class="stat-value text-warning mt-2">
                <?= htmlspecialchars((string)(int)$total_clientes) ?>
            </div>
        </div>
    </div>

    <!-- Total Proveedores -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-red p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Total Proveedores</span>
                <i class="fa-solid fa-truck stat-icon text-danger"></i>
            </div>
            <div class="stat-value text-danger mt-2">
                <?= htmlspecialchars((string)(int)$total_proveedores) ?>
            </div>
        </div>
    </div>

    <!-- Total Productos -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-purple p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Total Productos</span>
                <i class="fa-solid fa-box stat-icon" style="color:#7c3aed;"></i>
            </div>
            <div class="stat-value mt-2" style="color:#7c3aed;">
                <?= htmlspecialchars((string)(int)$total_productos) ?>
            </div>
        </div>
    </div>

    <!-- Clientes con Cta. Cte. -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-yellow p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Clientes con Cta. Cte.</span>
                <i class="fa-solid fa-file-invoice-dollar stat-icon" style="color:#b45309;"></i>
            </div>
            <div class="stat-value mt-2" style="color:#b45309;">
                <?= htmlspecialchars((string)(int)$clientes_con_cta_cte) ?>
            </div>
        </div>
    </div>

    <!-- Cheques en Cartera -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-teal p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Cheques en Cartera</span>
                <i class="fa-solid fa-money-check stat-icon" style="color:#0d9488;"></i>
            </div>
            <div class="stat-value mt-2" style="color:#0d9488;">
                <?= htmlspecialchars((string)(int)$cheques_en_cartera) ?>
            </div>
        </div>
    </div>

    <!-- Ventas Hoy -->
    <div class="col-6 col-md-3">
        <div class="stat-card color-indigo p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start">
                <span class="stat-label">Ventas Hoy</span>
                <i class="fa-solid fa-calendar-day stat-icon" style="color:#4338ca;"></i>
            </div>
            <div class="stat-value mt-2" style="color:#4338ca;">
                <?= htmlspecialchars((string)(int)$ventas_hoy) ?>
            </div>
        </div>
    </div>

</div><!-- /.row indicadores -->

<!-- ═══════════════════════════════════════════════════════════
     GRÁFICOS
════════════════════════════════════════════════════════════ -->

<!-- Selector de período (compartido por ambos gráficos) -->
<div class="d-flex align-items-center gap-3 mb-3">
    <label class="fw-semibold mb-0" for="selectPeriodo">
        <i class="fa-solid fa-calendar-alt me-1 text-secondary"></i>Ver por:
    </label>
    <select id="selectPeriodo" class="form-select form-select-sm w-auto">
        <option value="dia">Día (hoy por hora)</option>
        <option value="semana">Semana (últimos 7 días)</option>
        <option value="mes" selected>Mes (mes actual)</option>
        <option value="anio">Año (últimos 12 meses)</option>
    </select>
    <div id="chartSpinner" class="spinner-border spinner-border-sm text-secondary d-none" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<div class="row g-4">

    <!-- Gráfico 1: Monto total de ventas en el tiempo (VERDE) -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2">
                <span class="badge rounded-pill" style="background:#16a34a;">&nbsp;</span>
                <span class="fw-semibold small">Monto de Ventas</span>
                <span class="text-muted small ms-1">— eje Y: pesos ($)</span>
            </div>
            <div class="card-body">
                <canvas id="chartMontos" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico 2: Cantidad de ventas en el tiempo (AZUL) -->
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2">
                <span class="badge rounded-pill" style="background:#2563eb;">&nbsp;</span>
                <span class="fw-semibold small">Cantidad de Ventas</span>
                <span class="text-muted small ms-1">— eje Y: unidades</span>
            </div>
            <div class="card-body">
                <canvas id="chartCantidades" height="120"></canvas>
            </div>
        </div>
    </div>

</div><!-- /.row gráficos -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
(function () {
    'use strict';

    // ── URL del endpoint AJAX ────────────────────────────────────────────────
    const GRAFICOS_URL = '<?= URL_BASE ?>/home/graficos-data';

    // ── Opciones base compartidas ────────────────────────────────────────────
    const baseOptions = {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: {} }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { maxRotation: 45, minRotation: 0, font: { size: 11 } }
            },
            y: {
                beginAtZero: true,
                ticks: { font: { size: 11 } }
            }
        }
    };

    // ── Gráfico 1 — Montos (VERDE) ───────────────────────────────────────────
    const ctxMontos = document.getElementById('chartMontos').getContext('2d');
    const chartMontos = new Chart(ctxMontos, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Monto ($)',
                data: [],
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.12)',
                borderWidth: 2.5,
                pointBackgroundColor: '#16a34a',
                pointRadius: 4,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: ctx => ' $' + ctx.parsed.y.toLocaleString('es-AR', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                ...baseOptions.scales,
                y: {
                    ...baseOptions.scales.y,
                    ticks: {
                        ...baseOptions.scales.y.ticks,
                        callback: val => '$' + val.toLocaleString('es-AR')
                    }
                }
            }
        }
    });

    // ── Gráfico 2 — Cantidades (AZUL) ────────────────────────────────────────
    const ctxCantidades = document.getElementById('chartCantidades').getContext('2d');
    const chartCantidades = new Chart(ctxCantidades, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Ventas',
                data: [],
                backgroundColor: 'rgba(37,99,235,0.75)',
                borderColor: '#2563eb',
                borderWidth: 1.5,
                borderRadius: 4
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y + ' venta' + (ctx.parsed.y !== 1 ? 's' : '')
                    }
                }
            },
            scales: {
                ...baseOptions.scales,
                y: {
                    ...baseOptions.scales.y,
                    ticks: {
                        ...baseOptions.scales.y.ticks,
                        stepSize: 1,
                        callback: val => Number.isInteger(val) ? val : ''
                    }
                }
            }
        }
    });

    // ── Función de actualización ─────────────────────────────────────────────
    function cargarGraficos(periodo) {
        const spinner = document.getElementById('chartSpinner');
        spinner.classList.remove('d-none');

        fetch(GRAFICOS_URL + '?periodo=' + encodeURIComponent(periodo), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Error HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            // Actualizar gráfico de montos
            chartMontos.data.labels              = data.labels;
            chartMontos.data.datasets[0].data    = data.montos;
            chartMontos.update('active');

            // Actualizar gráfico de cantidades
            chartCantidades.data.labels           = data.labels;
            chartCantidades.data.datasets[0].data = data.cantidades;
            chartCantidades.update('active');
        })
        .catch(function (err) {
            console.error('Error al cargar gráficos:', err);
        })
        .finally(function () {
            spinner.classList.add('d-none');
        });
    }

    // ── Listener del selector ────────────────────────────────────────────────
    document.getElementById('selectPeriodo').addEventListener('change', function () {
        cargarGraficos(this.value);
    });

    // ── Carga inicial con "mes" ──────────────────────────────────────────────
    cargarGraficos('mes');

})();
</script>
