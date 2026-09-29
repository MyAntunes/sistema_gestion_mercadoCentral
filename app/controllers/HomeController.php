<?php
/**
 * HomeController — Dashboard / Vista principal del sistema.
 */
class HomeController
{
    public function index(): void
    {
        requireAuth();

        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Dashboard.php';

        $dashboard   = new Dashboard();
        $indicadores = $dashboard->getIndicadores();

        extract($indicadores);

        $pageTitle = 'Dashboard';

        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/home/index.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Endpoint AJAX — retorna JSON con datos para los gráficos.
     * GET /home/graficos-data?periodo=mes
     */
    public function graficosData(): void
    {
        requireAuth();

        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Dashboard.php';

        $periodo  = $_GET['periodo'] ?? 'mes';
        $allowed  = ['dia', 'semana', 'mes', 'anio'];
        if (!in_array($periodo, $allowed, true)) {
            $periodo = 'mes';
        }

        $dashboard = new Dashboard();
        $data      = $dashboard->getGraficosData($periodo);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
