<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/public/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Load config and all controllers
require_once('app/controllers/db.php');
require_once('app/models/coremodel.php');
require_once('app/controllers/views.php');
require_once('app/controllers/core_controller.php');
require_once('app/models/usermodel.php');


// Initialize database and get root URL
$db = (new DBController())->connect();
$rootUrl = (new CoreModel($db))->getCurrentUrl();
$viewController = new ViewController($rootUrl,$db);
$coreController = new CoreController($db);
$coremodel = new CoreModel($db);

// Base directory configuration
$baseDir = '/tamec';  // Base directory where your app is located
$url = str_replace($baseDir, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$requestMethod = $_SERVER['REQUEST_METHOD'];


// Auth check — public routes bypass session verification
$publicRoutes = ['/login', '/forgot_password', '/404', '/loginauth', '/logout', '/forgot_password_action'];
if (!in_array($url, $publicRoutes)) {
    $isAdmin = isset($_SESSION['tamec_session'])
            && isset($_SESSION['tamec_role'])
            && strtolower($_SESSION['tamec_role']) === 'admin';

    if (!$isAdmin) {
        if ($requestMethod === 'GET') {
            header('Location: ' . $baseDir . '/login');
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Unauthorized access. Please log in.']);
            exit;
        }
    }
}

// Routing
$routes = [
    // GET Routes (Page Views)
    'GET' => [
        '/'                => fn() => $viewController->showDashboardPage($rootUrl),
        '/dashboard'       => fn() => $viewController->showDashboardPage($rootUrl),
        '/login'           => fn() => $viewController->showLoginPage($rootUrl),
        '/forgot_password' => fn() => $viewController->showForgotPasswordPage($rootUrl),
        '/404'             => fn() => $viewController->show404Page($rootUrl),
        '/staffs'          => fn() => $viewController->showStaffsPage($rootUrl),
        '/clients'         => fn() => $viewController->showClientsPage($rootUrl),
        '/schedules'       => fn() => $viewController->showSchedulePage($rootUrl),
        '/payrolls'        => fn() => $viewController->showPayrollsPage($rootUrl),
        '/holidays'        => fn() => $viewController->showHolidaysPage($rootUrl),
        '/documents'       => fn() => $viewController->showDocumentsPage($rootUrl),
        '/create_schedule' => fn() => $viewController->showCreateSchedulePage($rootUrl),
        '/create_payroll'  => fn() => $viewController->showCreatePayrollPage($rootUrl),
        '/invoices'        => fn() => $viewController->showInvoicesPage($rootUrl),
        '/create_invoice'  => fn() => $viewController->showCreateInvoicePage($rootUrl),
        '/change_password' => fn() => $viewController->showChangePasswordPage($rootUrl),
        '/activities'      => fn() => $viewController->showActivitiesPage($rootUrl),
        '/logout'          => function() use ($baseDir) {
            session_destroy();
            header('Location: ' . $baseDir . '/login');
            exit;
        },
    ],

    // POST Routes (API/Actions)
    'POST' => [
        '/loginauth'                    => fn() => $coreController->loginAuth(),
        '/forgot_password_action'       => fn() => $coreController->forgot_password(),
        '/change_password'              => fn() => $coreController->change_password(),
        '/fetch_all_staff'              => fn() => $coreController->fetch_all_staff(),
        '/create_new_staff'             => fn() => $coreController->create_new_staff(),
        '/update_staff'                 => fn() => $coreController->update_staff(),
        '/delete_staff'                 => fn() => $coreController->delete_staff(),
        '/fetch_all_clients'            => fn() => $coreController->fetch_all_clients(),
        '/create_or_update_client'      => fn() => $coreController->client_update_or_create(),
        '/delete_client'                => fn() => $coreController->delete_client(),
        '/insert_update_holiday'        => fn() => $coreController->insert_update_holiday(),
        '/fetch_holidays'               => fn() => $coreController->fetch_all_holidays(),
        '/delete_holiday'               => fn() => $coreController->delete_holiday(),
        '/fetch_schedules'              => fn() => $coreController->fetch_schedules(),
        '/save_schedules'               => fn() => $coreController->saveSchedule(),
        '/update_schedule'              => fn() => $coreController->updateSchedule(),
        '/fetch_schedules_for_payroll'  => fn() => $coreController->fetch_schedules_for_payroll(),
        '/create_payroll_from_selection'=> fn() => $coreController->create_payroll_from_selection(),
        '/fetch_all_payrolls'           => fn() => $coreController->fetch_all_payrolls(),
        '/get_payroll_details'          => fn() => $coreController->get_payroll_details(),
        '/generate_payroll'             => fn() => $coreController->generate_payroll(),
        '/delete_payroll'               => fn() => $coreController->delete_payroll(),
        '/update_payroll_status'        => fn() => $coreController->update_payroll_status(),
        '/fetch_schedules_for_invoice'  => fn() => $coreController->fetch_schedules_for_invoice(),
        '/create_invoice_from_selection'=> fn() => $coreController->create_invoice_from_selection(),
        '/fetch_all_invoices'           => fn() => $coreController->fetch_all_invoices(),
        '/get_invoice_details'          => fn() => $coreController->get_invoice_details(),
        '/delete_invoice'               => fn() => $coreController->delete_invoice(),
        '/update_invoice_status'        => fn() => $coreController->update_invoice_status(),
        '/send_invoice_email'           => fn() => $coreController->send_invoice_email(),
        '/fetch_all_documents'          => fn() => $coreController->fetch_all_documents(),
        '/create_document'              => fn() => $coreController->create_document(),
        '/update_document'              => fn() => $coreController->update_document(),
        '/delete_document'              => fn() => $coreController->delete_document(),
        '/fetch_user_documents'         => fn() => $coreController->fetch_user_documents(),
        '/save_user_document'           => fn() => $coreController->save_user_document(),
        '/fetch_all_activities'         => fn() => $coreController->fetch_all_activities(),
    ]
];

// Handle the request
try {
    // Check if route exists for current method and URL
    if (isset($routes[$requestMethod]) && isset($routes[$requestMethod][$url])) {
        $handler = $routes[$requestMethod][$url];
        $handler();
    } else {
        // Handle 404 - Page not found
        http_response_code(404);
        //echo 'Page not found';

        // You could also log this or redirect to a custom 404 page
        // error_log("404 - Route not found: $requestMethod $url");
         header("Location: 404");
    }
} catch (Exception $e) {   // Handle exceptions
    http_response_code(500);
    echo 'An error occurred: ' . $e->getMessage();

    // Log the error
    error_log("Route handler error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

?>