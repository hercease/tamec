<?php
class ViewController
{
    private $rootUrl;
    private $db;
    private $coreModel;

    public function __construct($rootUrl, $db)
    {
        $this->rootUrl = $rootUrl;
        $this->db = $db;
        $this->coreModel = new CoreModel($db);

    }

    public function showLoginPage($rootUrl)
    {
        include 'app/views/login.php';
    }

    public function showDashboardPage($rootUrl)
    {
        $stats = $this->coreModel->dashboardStats();
        $greetings = $this->coreModel->getTimeGreeting($_SESSION['tamec_name'] ?? 'Admin');
        $formattedDate = $this->coreModel->getFormattedDateWithWeek();
        $todaySchedule = $this->coreModel->todayschedule();
        $staffavailability = $this->coreModel->staffAvailability();
        $recentActivities = $this->coreModel->fetch_recent_activities(8);
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/dashboard.php';
    }

    public function showForgotPasswordPage($rootUrl)
    {
        include 'app/views/forgot_password.php';
    }

    public function show404Page($rootUrl)
    {
        include 'app/views/404.php';
    }

    public function showStaffsPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/staffs.php';
    }

    public function showClientsPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/clients.php';
    }

    public function showSchedulePage($rootUrl){
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
            'all_clients' => $this->coreModel->get_all_clients(),
            'all_staffs' => $this->coreModel->get_all_staffs(),
        ];
        include 'app/views/schedules.php';
    }

    public function showPayrollsPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/payrolls.php';
    }

    public function showHolidaysPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/holidays.php';
    }

    public function showDocumentsPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        $documents = $this->coreModel->get_all_documents()['documents'];
        include 'app/views/documents.php';
    }

    public function showCreatePayrollPage($rootUrl)
    {
        $counters = [
            'total_staffs'    => $this->coreModel->sidebarCounter('staffs'),
            'total_clients'   => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls'  => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices'  => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/create_payroll.php';
    }

    public function showCreateInvoicePage($rootUrl)
    {
        $counters = [
            'total_staffs'    => $this->coreModel->sidebarCounter('staffs'),
            'total_clients'   => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls'  => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices'  => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
            'all_clients'     => $this->coreModel->get_all_clients(),
        ];
        include 'app/views/create_invoice.php';
    }

    public function showInvoicesPage($rootUrl)
    {
        $counters = [
            'total_staffs'    => $this->coreModel->sidebarCounter('staffs'),
            'total_clients'   => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls'  => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices'  => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/invoices.php';
    }

    public function showChangePasswordPage($rootUrl)
    {
        $counters = [
            'total_staffs'    => $this->coreModel->sidebarCounter('staffs'),
            'total_clients'   => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls'  => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices'  => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/change_password.php';
    }

    public function showCreateSchedulePage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
            'all_clients' => $this->coreModel->get_all_clients(),
            'all_staffs' => $this->coreModel->get_all_staffs(),
            'all_holidays' => $this->coreModel->get_all_holidays(),
        ];
        include 'app/views/create_schedule.php';
    }

    public function showActivitiesPage($rootUrl)
    {
        $counters = [
            'total_staffs' => $this->coreModel->sidebarCounter('staffs'),
            'total_clients' => $this->coreModel->sidebarCounter('clients'),
            'total_schedules' => $this->coreModel->sidebarCounter('schedules'),
            'total_payrolls' => $this->coreModel->sidebarCounter('payrolls'),
            'total_invoices' => $this->coreModel->sidebarCounter('invoices'),
            'total_locations' => $this->coreModel->sidebarCounter('locations'),
        ];
        include 'app/views/activities.php';
    }

}