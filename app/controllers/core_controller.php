<?php
class CoreController
{
    protected $coreModel, $userModel, $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->coreModel = new CoreModel($db);
        $this->userModel = new UserModel($db);
    }

    public function loginAuth(){
        try{
            foreach (['email', 'password'] as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $email    = $_POST['email'];
            $password = $_POST['password'];

            $response = $this->userModel->processLogin($email, $password);

            if (!$response['status']) {
                throw new Exception($response['message']);
            }

            $user = $response['user'];

            if (strtolower($user['role']) !== 'admin') {
                throw new Exception('Access denied. Admin privileges required.');
            }

            $_SESSION['tamec_session'] = $user['email'];
            $_SESSION['tamec_role']    = strtolower($user['role']);
            $_SESSION['tamec_name']    = trim($user['firstname'] . ' ' . $user['lastname']);
            $_SESSION['tamec_id']      = (int)$user['staff_id'];

            $this->coreModel->logActivity('login', 'Admin Login', $_SESSION['tamec_name'] . ' signed in', 'system');

            echo json_encode(['status' => true, 'message' => 'Login successful']);

        } catch (Exception $e){
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_all_staff(){
        try {
            $staffs = $this->coreModel->get_all_staffs();
            echo json_encode($staffs);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_all_clients(){
        try {
            $clients = $this->coreModel->get_all_clients();
            echo json_encode($clients);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_all_holidays(){
        try {
            $holidays = $this->coreModel->get_all_holidays();
            echo json_encode($holidays);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create_new_staff(){
        try {
            $requiredFields = ['firstname', 'lastname', 'address', 'city', 'province', 'postalCode', 'country', 'email', 'phone'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $staffData = [];
            foreach ($_POST as $field => $value) {
                $staffData[$field] = $this->coreModel->sanitizeInput($value);
            }

            if($this->coreModel->check_email_exists($staffData['email'])){
                throw new Exception("Email already exists");
            }

            $response = $this->coreModel->add_staff($staffData);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('staff_created', 'Staff Added',
                    'Added new staff: ' . $staffData['firstname'] . ' ' . $staffData['lastname'], 'staff');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update_staff(){
        try {
            $requiredFields = ['firstname', 'lastname', 'address', 'city', 'province', 'postalCode', 'country', 'email', 'phone'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $staffData = [];
            foreach ($_POST as $field => $value) {
                $staffData[$field] = $this->coreModel->sanitizeInput($value);
            }

            $response = $this->coreModel->update_staff($staffData);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('staff_updated', 'Staff Updated',
                    'Updated staff: ' . $staffData['firstname'] . ' ' . $staffData['lastname'], 'staff');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_staff(){
        try {
            if (!isset($_POST['staff_id']) || empty(trim($_POST['staff_id']))) {
                throw new Exception("Missing required field: staff_id");
            }

            $staff_id = $this->coreModel->sanitizeInput($_POST['staff_id']);
            $response = $this->coreModel->delete_staff($staff_id);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('staff_deleted', 'Staff Removed',
                    'Removed staff ID: ' . $staff_id, 'staff');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_client(){
        try {
            if (!isset($_POST['client_id']) || empty(trim($_POST['client_id']))) {
                throw new Exception("Missing required field: client_id");
            }

            $client_id = $this->coreModel->sanitizeInput($_POST['client_id']);
            $response  = $this->coreModel->delete_client($client_id);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('client_deleted', 'Client Removed',
                    'Removed client ID: ' . $client_id, 'client');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function client_update_or_create(){
        try {
            $requiredFields = ['firstname', 'lastname', 'email', 'billingRate', 'residentialName', 'residentialAddress', 'residentialCity', 'residentialProvince', 'residentialCountry', 'billingName', 'billingAddress', 'billingProvince', 'billingEmail'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $clientData = [];
            foreach ($_POST as $field => $value) {
                $clientData[$field] = $this->coreModel->sanitizeInput($value);
            }

            $isUpdate = !empty($clientData['client_id']);
            $response = $this->coreModel->update_or_create_client($clientData);

            if (!empty($response['status'])) {
                $actType = $isUpdate ? 'client_updated' : 'client_created';
                $actTitle = $isUpdate ? 'Client Updated' : 'Client Added';
                $this->coreModel->logActivity($actType, $actTitle,
                    ($isUpdate ? 'Updated' : 'Added') . ' client: ' . $clientData['firstname'] . ' ' . $clientData['lastname'], 'client');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function insert_update_holiday(){
        try {
            $holidayData = [];
            foreach ($_POST as $field => $value) {
                $holidayData[$field] = $this->coreModel->sanitizeInput($value);
            }
            $isUpdate = !empty($holidayData['holiday_id']);
            $response = $this->coreModel->saveHoliday($holidayData);

            if (!empty($response['status'])) {
                $actType  = $isUpdate ? 'holiday_updated' : 'holiday_created';
                $actTitle = $isUpdate ? 'Holiday Updated' : 'Holiday Added';
                $name     = $holidayData['holiday_name'] ?? $holidayData['name'] ?? 'Holiday';
                $this->coreModel->logActivity($actType, $actTitle,
                    ($isUpdate ? 'Updated' : 'Added') . ' holiday: ' . $name, 'holiday');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_holiday(){
        try {
            if (!isset($_POST['holiday_id']) || empty(trim($_POST['holiday_id']))) {
                throw new Exception("Missing required field: holiday_id");
            }
            $holiday_id = $this->coreModel->sanitizeInput($_POST['holiday_id']);
            $response   = $this->coreModel->delete_holiday($holiday_id);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('holiday_deleted', 'Holiday Removed',
                    'Removed holiday ID: ' . $holiday_id, 'holiday');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_schedules(){
        try {
            $startDate = isset($_POST['startDate']) ? $this->coreModel->sanitizeInput($_POST['startDate']) : null;
            $endDate   = isset($_POST['endDate'])   ? $this->coreModel->sanitizeInput($_POST['endDate'])   : null;
            $schedules = $this->coreModel->get_all_schedules($startDate, $endDate);
            echo json_encode($schedules);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveSchedule() {
        try {
            $rawData      = file_get_contents('php://input');
            $scheduleData = json_decode($rawData, true);
            if (!$scheduleData) $scheduleData = $_POST;

            $scheduleData = $this->coreModel->sanitizeInput($scheduleData);

            $validation = $this->validateScheduleRequest($scheduleData);
            if (!$validation['valid']) {
                echo json_encode(['status' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']]);
                return;
            }

            $response = $this->coreModel->saveSchedule($scheduleData);

            if (!empty($response['status'])) {
                $count = is_array($scheduleData['schedules']) ? count($scheduleData['schedules']) : 1;
                $this->coreModel->logActivity('schedule_created', 'Schedule Created',
                    'Created ' . $count . ' schedule(s) for client ID: ' . $scheduleData['client_id'], 'schedule');
            }

            echo json_encode($response);

        } catch (Exception $e) {
            error_log("Error in saveSchedule controller: " . $e->getMessage());
            echo json_encode(['status' => false, 'message' => 'An error occurred while saving the schedule', 'error' => $e->getMessage()]);
        }
    }

    private function validateScheduleRequest($data) {
        $errors = [];
        if (empty($data['client_id'])) $errors[] = 'Client ID is required';
        if (empty($data['schedules']) || !is_array($data['schedules'])) {
            $errors[] = 'No schedules to save';
        } else {
            if (count($data['schedules']) > 30) $errors[] = 'Cannot create more than 30 schedules at once';
        }
        return ['valid' => empty($errors), 'errors' => $errors];
    }

    public function fetch_schedules_for_payroll() {
        try {
            $startDate = isset($_POST['startDate']) ? $this->coreModel->sanitizeInput($_POST['startDate']) : null;
            $endDate   = isset($_POST['endDate'])   ? $this->coreModel->sanitizeInput($_POST['endDate'])   : null;
            if (!$startDate || !$endDate) throw new Exception("Start date and end date are required");
            $result = $this->coreModel->fetch_schedules_for_payroll($startDate, $endDate);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create_payroll_from_selection() {
        try {
            $raw  = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (!$data) $data = $_POST;

            foreach (['period_start', 'period_end', 'schedule_ids'] as $field) {
                if (empty($data[$field])) throw new Exception("Missing required field: $field");
            }

            $data['period_start'] = $this->coreModel->sanitizeInput($data['period_start']);
            $data['period_end']   = $this->coreModel->sanitizeInput($data['period_end']);
            $data['notes']        = isset($data['notes']) ? $this->coreModel->sanitizeInput($data['notes']) : null;
            $data['schedule_ids'] = array_filter(array_map('intval', (array)$data['schedule_ids']));

            $result = $this->coreModel->create_payroll_from_selection($data);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('payroll_generated', 'Payroll Generated',
                    'Generated payroll for period ' . $data['period_start'] . ' to ' . $data['period_end'], 'payroll');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_all_payrolls() {
        try {
            $result = $this->coreModel->get_all_payrolls();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function get_payroll_details() {
        try {
            if (!isset($_POST['payroll_id']) || empty(trim($_POST['payroll_id']))) {
                throw new Exception("Missing required field: payroll_id");
            }
            $payroll_id = (int) $this->coreModel->sanitizeInput($_POST['payroll_id']);
            $result     = $this->coreModel->get_payroll_details($payroll_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function generate_payroll() {
        try {
            foreach (['period_start', 'period_end'] as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }
            $data = [];
            foreach ($_POST as $field => $value) {
                $data[$field] = $this->coreModel->sanitizeInput($value);
            }
            $result = $this->coreModel->generate_payroll($data);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_payroll() {
        try {
            if (!isset($_POST['payroll_id']) || empty(trim($_POST['payroll_id']))) {
                throw new Exception("Missing required field: payroll_id");
            }
            $payroll_id = (int) $this->coreModel->sanitizeInput($_POST['payroll_id']);
            $result     = $this->coreModel->delete_payroll($payroll_id);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('delete', 'Payroll Deleted',
                    'Deleted payroll ID: ' . $payroll_id, 'payroll');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_schedules_for_invoice() {
        try {
            $required = ['client_id', 'start_date', 'end_date'];
            foreach ($required as $f) {
                if (!isset($_POST[$f]) || empty(trim($_POST[$f]))) throw new Exception("Missing required field: $f");
            }
            $client_id  = (int) $this->coreModel->sanitizeInput($_POST['client_id']);
            $start_date = $this->coreModel->sanitizeInput($_POST['start_date']);
            $end_date   = $this->coreModel->sanitizeInput($_POST['end_date']);
            $result     = $this->coreModel->fetch_schedules_for_invoice($client_id, $start_date, $end_date);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create_invoice_from_selection() {
        try {
            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body) throw new Exception("Invalid request body");
            $required = ['client_id', 'period_start', 'period_end', 'schedule_ids'];
            foreach ($required as $f) {
                if (empty($body[$f])) throw new Exception("Missing required field: $f");
            }
            $result = $this->coreModel->create_invoice_from_selection($body);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('invoice_generated', 'Invoice Generated',
                    'Generated invoice for client ID: ' . $body['client_id'] . ' (' . $body['period_start'] . ' to ' . $body['period_end'] . ')', 'invoice');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function fetch_all_invoices() {
        try {
            $result = $this->coreModel->get_all_invoices();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function get_invoice_details() {
        try {
            if (!isset($_POST['invoice_id']) || empty(trim($_POST['invoice_id']))) {
                throw new Exception("Missing required field: invoice_id");
            }
            $invoice_id = (int) $this->coreModel->sanitizeInput($_POST['invoice_id']);
            $result     = $this->coreModel->get_invoice_details($invoice_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_invoice() {
        try {
            if (!isset($_POST['invoice_id']) || empty(trim($_POST['invoice_id']))) {
                throw new Exception("Missing required field: invoice_id");
            }
            $invoice_id = (int) $this->coreModel->sanitizeInput($_POST['invoice_id']);
            $result     = $this->coreModel->delete_invoice($invoice_id);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('delete', 'Invoice Deleted',
                    'Deleted invoice ID: ' . $invoice_id, 'invoice');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update_invoice_status() {
        try {
            if (!isset($_POST['invoice_id']) || empty(trim($_POST['invoice_id']))) {
                throw new Exception("Missing required field: invoice_id");
            }
            if (!isset($_POST['status']) || empty(trim($_POST['status']))) {
                throw new Exception("Missing required field: status");
            }
            $invoice_id = (int) $this->coreModel->sanitizeInput($_POST['invoice_id']);
            $status     = $this->coreModel->sanitizeInput($_POST['status']);
            $result     = $this->coreModel->update_invoice_status($invoice_id, $status);

            if (!empty($result['status'])) {
                $actType = ($status === 'paid') ? 'invoice_paid' : 'invoice_sent';
                $this->coreModel->logActivity($actType, 'Invoice Status Updated',
                    'Invoice ID ' . $invoice_id . ' marked as ' . $status, 'invoice');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function send_invoice_email() {
        try {
            if (!isset($_POST['invoice_id']) || empty(trim($_POST['invoice_id']))) {
                throw new Exception("Missing required field: invoice_id");
            }
            $invoice_id = (int) $this->coreModel->sanitizeInput($_POST['invoice_id']);
            $result     = $this->coreModel->send_invoice_email($invoice_id);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('invoice_sent', 'Invoice Emailed',
                    'Sent invoice ID ' . $invoice_id . ' via email', 'invoice');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update_payroll_status() {
        try {
            if (!isset($_POST['payroll_id']) || empty(trim($_POST['payroll_id']))) {
                throw new Exception("Missing required field: payroll_id");
            }
            if (!isset($_POST['status']) || empty(trim($_POST['status']))) {
                throw new Exception("Missing required field: status");
            }
            $payroll_id = (int) $this->coreModel->sanitizeInput($_POST['payroll_id']);
            $status     = $this->coreModel->sanitizeInput($_POST['status']);
            $result     = $this->coreModel->update_payroll_status($payroll_id, $status);

            if (!empty($result['status'])) {
                $actType = ($status === 'paid') ? 'payroll_paid' : 'payroll_processed';
                $this->coreModel->logActivity($actType, 'Payroll Status Updated',
                    'Payroll ID ' . $payroll_id . ' marked as ' . $status, 'payroll');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateSchedule() {
        try {
            $requiredFields = ['id', 'start_time', 'end_time', 'status'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $scheduleData = [];
            foreach ($_POST as $field => $value) {
                $scheduleData[$field] = $this->coreModel->sanitizeInput($value);
            }

            $response = $this->coreModel->update_schedule($scheduleData);

            if (!empty($response['status'])) {
                $this->coreModel->logActivity('schedule_updated', 'Schedule Updated',
                    'Updated schedule ID: ' . $scheduleData['id'] . ' — status: ' . $scheduleData['status'], 'schedule');
            }

            echo json_encode($response);

        } catch (Exception $e) {
            error_log("Error in updateSchedule controller: " . $e->getMessage());
            echo json_encode(['status' => false, 'message' => 'An error occurred while updating the schedule', 'error' => $e->getMessage()]);
        }
    }

    // ─── Auth Actions ─────────────────────────────────────────────────────────

    public function forgot_password() {
        try {
            if (empty(trim($_POST['email'] ?? ''))) throw new Exception("Email address is required");
            $email  = $this->coreModel->sanitizeInput($_POST['email']);
            $result = $this->userModel->forgot_password($email);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function change_password() {
        try {
            foreach (['current_password', 'new_password'] as $field) {
                if (empty(trim($_POST[$field] ?? ''))) throw new Exception("Missing required field: $field");
            }

            $staff_id = $_SESSION['tamec_id'] ?? 0;
            if (!$staff_id) throw new Exception("Session expired. Please log in again.");

            $current = $_POST['current_password'];
            $new     = $_POST['new_password'];

            if (strlen($new) < 6) throw new Exception("New password must be at least 6 characters");

            $result = $this->userModel->change_password((int)$staff_id, $current, $new);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('password_changed', 'Password Changed',
                    $_SESSION['tamec_name'] . ' changed their password', 'setting');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Documents CRUD ───────────────────────────────────────────────────────

    public function fetch_all_documents() {
        try {
            echo json_encode($this->coreModel->get_all_documents());
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function create_document() {
        try {
            if (empty(trim($_POST['doc_name'] ?? ''))) throw new Exception("Document name is required");
            if (empty(trim($_POST['doc_tag'] ?? '')))  throw new Exception("Document tag is required");

            $data = [
                'doc_name' => $this->coreModel->sanitizeInput($_POST['doc_name']),
                'doc_tag'  => $this->coreModel->sanitizeInput(strtolower(preg_replace('/\\s+/', '_', trim($_POST['doc_tag'])))),
                'optional' => isset($_POST['optional']) && $_POST['optional'] === 'true',
            ];
            $result = $this->coreModel->create_document($data);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('create', 'Document Type Created',
                    'Created document type: ' . $data['doc_name'], 'setting');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update_document() {
        try {
            if (empty(trim($_POST['doc_id'] ?? '')))   throw new Exception("Document ID is required");
            if (empty(trim($_POST['doc_name'] ?? ''))) throw new Exception("Document name is required");
            if (empty(trim($_POST['doc_tag'] ?? '')))  throw new Exception("Document tag is required");

            $data = [
                'doc_id'   => (int)$_POST['doc_id'],
                'doc_name' => $this->coreModel->sanitizeInput($_POST['doc_name']),
                'doc_tag'  => $this->coreModel->sanitizeInput(strtolower(preg_replace('/\\s+/', '_', trim($_POST['doc_tag'])))),
                'optional' => isset($_POST['optional']) && $_POST['optional'] === 'true',
            ];
            $result = $this->coreModel->update_document($data);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('update', 'Document Type Updated',
                    'Updated document type: ' . $data['doc_name'], 'setting');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_document() {
        try {
            if (empty(trim($_POST['doc_id'] ?? ''))) throw new Exception("Document ID is required");
            $doc_id = (int)$_POST['doc_id'];
            $result = $this->coreModel->delete_document($doc_id);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('delete', 'Document Type Removed',
                    'Deleted document type ID: ' . $doc_id, 'setting');
            }

            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── User Documents ───────────────────────────────────────────────────────

    public function fetch_user_documents() {
        try {
            if (empty(trim($_POST['staff_id'] ?? ''))) throw new Exception("Staff ID is required");
            $staff_id = (int)$_POST['staff_id'];
            echo json_encode($this->coreModel->get_user_documents($staff_id));
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function save_user_document() {
        try {
            $staff_id = (int)($_POST['staff_id'] ?? 0);
            $doc_id   = (int)($_POST['doc_id']   ?? 0);
            $doc_tag  = $this->coreModel->sanitizeInput($_POST['doc_tag'] ?? '');

            if (!$staff_id) throw new Exception("Staff ID is required");
            if (!$doc_id)   throw new Exception("Document ID is required");
            if (!$doc_tag)  throw new Exception("Document tag is required");

            if (empty($_FILES['document']['name'])) throw new Exception("No file uploaded");

            $file    = $_FILES['document'];
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) throw new Exception("File type not allowed. Allowed: " . implode(', ', $allowed));
            if ($file['size'] > 5 * 1024 * 1024) throw new Exception("File size must not exceed 5MB");

            $upload_dir = __DIR__ . '/../../public/uploads/staff_documents/' . $staff_id . '/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $filename = $doc_tag . '_' . time() . '.' . $ext;
            $dest     = $upload_dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) throw new Exception("Failed to save uploaded file");

            $file_path     = 'uploads/staff_documents/' . $staff_id . '/' . $filename;
            $original_name = $file['name'];

            $result = $this->coreModel->save_user_document($staff_id, $doc_id, $doc_tag, $file_path, $original_name);

            if (!empty($result['status'])) {
                $this->coreModel->logActivity('update', 'Staff Document Uploaded',
                    'Uploaded document "' . $original_name . '" for staff ID: ' . $staff_id, 'staff');
            }

            echo json_encode($result);

        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── Activities ───────────────────────────────────────────────────────────

    public function fetch_all_activities() {
        try {
            $page        = (int)($_POST['page']        ?? 1);
            $per_page    = (int)($_POST['per_page']    ?? 20);
            $type_filter = $_POST['type_filter'] ?? null;
            $date_from   = $_POST['date_from']   ?? null;
            $date_to     = $_POST['date_to']     ?? null;
            $search      = $_POST['search']      ?? null;

            if ($page < 1)     $page     = 1;
            if ($per_page < 1) $per_page = 20;
            if ($type_filter === '') $type_filter = null;
            if ($date_from   === '') $date_from   = null;
            if ($date_to     === '') $date_to     = null;
            if ($search      === '') $search      = null;

            $result = $this->coreModel->fetch_all_activities_paginated($page, $per_page, $type_filter, $date_from, $date_to, $search);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
