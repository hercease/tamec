<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP;
class CoreModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCurrentUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'];
    }

    public function sanitizeInput($data)
    {
        if (is_array($data)) {
            // Loop through each element of the array and sanitize recursively
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeInput($value);
            }
        } else {
            // If it's not an array, sanitize the string
            $data = trim($data); // Remove unnecessary spaces
            $data = stripslashes($data); // Remove backslashes
            $data = htmlspecialchars($data); // Convert special characters to HTML entities
        }
        return $data;
    }

    public function processMailQueue($batchSize = 10, $maxAttempts = 3) {
        $sent = 0;
        $failed = 0;

        $stmt = $this->db->prepare(
            "SELECT id, to_email, to_name, subject, body
             FROM mail_queue
             WHERE status = 'pending' AND attempts < ?
             ORDER BY created_at ASC
             LIMIT ?"
        );
        $stmt->bind_param('ii', $maxAttempts, $batchSize);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($rows)) {
            return ['status' => true, 'message' => 'No pending emails', 'sent' => 0, 'failed' => 0];
        }

        foreach ($rows as $row) {
            // Increment attempts first to prevent double-sends
            $upd = $this->db->prepare("UPDATE mail_queue SET attempts = attempts + 1 WHERE id = ?");
            $upd->bind_param('i', $row['id']);
            $upd->execute();
            $upd->close();

            $result = $this->sendmail($row['to_email'], $row['to_name'], $row['body'], $row['subject']);

            if ($result['status']) {
                $done = $this->db->prepare("UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = ?");
                $done->bind_param('i', $row['id']);
                $done->execute();
                $done->close();
                $sent++;
            } else {
                // Check if max attempts reached
                $check = $this->db->prepare("SELECT attempts FROM mail_queue WHERE id = ?");
                $check->bind_param('i', $row['id']);
                $check->execute();
                $cur = $check->get_result()->fetch_assoc();
                $check->close();

                $newStatus = ($cur['attempts'] >= $maxAttempts) ? 'failed' : 'pending';
                $errMsg = $result['error'] ?? 'Unknown error';

                $fail = $this->db->prepare("UPDATE mail_queue SET status = ?, error_message = ? WHERE id = ?");
                $fail->bind_param('ssi', $newStatus, $errMsg, $row['id']);
                $fail->execute();
                $fail->close();
                $failed++;
            }
        }

        return [
            'status'  => true,
            'message' => "Processed: {$sent} sent, {$failed} failed",
            'sent'    => $sent,
            'failed'  => $failed
        ];
    }

    public function sendmail($email, $name, $body, $subject)
    {
        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        $response = [
            'status' => false,
            'message' => '',
            'error' => null,
            'email' => $email,
            'subject' => $subject
        ];

        // Quick validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email address';
            $response['error'] = 'VALIDATION_ERROR';
            return $response;
        }


        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->Timeout = 15; // 15 second timeout

            // Recipients
            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'TAMEC Care Staffing Services');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body); // Plain text version

            // Send and capture result
            $sendResult = $mail->send();

            if ($sendResult) {
                $response['status'] = true;
                $response['message'] = 'Email sent successfully';
                $response['message_id'] = $mail->getLastMessageID();
            } else {
                $response['status'] = false;
                $response['error'] = $mail->ErrorInfo;
            }

            $mail->clearAddresses();

        } catch (Exception $e) {
            $response['message'] = 'Email sending failed';
            $response['error'] = $e->getMessage();
        }

        return $response;
    }

    public function dashboardStats()
    {
        date_default_timezone_set('America/Toronto');
        $stats = [];

        try {
            // Total Staffs
            $result = $this->db->query("SELECT COUNT(*) AS total_staffs FROM staffs");
            if (!$result) {
                throw new Exception("Error fetching total staffs: " . $this->db->error);
            }
            $stats['total_staffs'] = $result->fetch_assoc()['total_staffs'] ?? 0;
            $result->free();

            // Staffs from last month
            $result = $this->db->query("
                SELECT COUNT(*) AS last_month_staffs 
                FROM staffs 
                WHERE MONTH(reg_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
                AND YEAR(reg_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)
            ");
            if (!$result) {
                throw new Exception("Error fetching last month staffs: " . $this->db->error);
            }
            $last_month_staffs = $result->fetch_assoc()['last_month_staffs'] ?? 0;
            $stats['staffs_increment'] = $stats['total_staffs'] - $last_month_staffs;
            $result->free();

            // Total Clients
            $result = $this->db->query("SELECT COUNT(*) AS total_clients FROM clients");
            if (!$result) {
                throw new Exception("Error fetching total clients: " . $this->db->error);
            }
            $stats['total_clients'] = $result->fetch_assoc()['total_clients'] ?? 0;
            $result->free();

            // Clients from last month
            $result = $this->db->query("
                SELECT COUNT(*) AS last_month_clients 
                FROM clients 
                WHERE MONTH(reg_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
                AND YEAR(reg_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)
            ");
            if (!$result) {
                throw new Exception("Error fetching last month clients: " . $this->db->error);
            }
            $last_month_clients = $result->fetch_assoc()['last_month_clients'] ?? 0;
            $stats['clients_increment'] = $stats['total_clients'] - $last_month_clients;
            $result->free();

            // Weekly Payroll (using COALESCE to handle NULL)
            $result = $this->db->query("
                SELECT COALESCE(SUM(total_amount), 0) AS weekly_payroll 
                FROM payrolls 
                WHERE WEEK(created_at) = WEEK(CURDATE())
            ");
            if (!$result) {
                throw new Exception("Error fetching weekly payroll: " . $this->db->error);
            }
            $stats['weekly_payroll'] = $result->fetch_assoc()['weekly_payroll'] ?? 0;
            $result->free();

            //calculate total staffs under each pyaroll
            $result = $this->db->query("
                SELECT COUNT(*) AS total_staffs_by_payroll
                FROM schedules WHERE payroll_id IN (SELECT payroll_id FROM payrolls WHERE WEEK(created_at) = WEEK(CURDATE()))
            ");
            if (!$result) {
                throw new Exception("Error fetching total staffs: " . $this->db->error);
            }
            $stats['total_staffs_by_payroll'] = $result->fetch_assoc()['total_staffs_by_payroll'] ?? 0;
            $result->free();

            // Today's Schedules
            $result = $this->db->query("
                SELECT COUNT(*) AS today_schedules 
                FROM schedules 
                WHERE DATE(schedule_date) = CURDATE()
            ");
            if (!$result) {
                throw new Exception("Error fetching today's schedules: " . $this->db->error);
            }
            $stats['today_schedules'] = $result->fetch_assoc()['today_schedules'] ?? 0;
            $result->free();

            // Ongoing schedules
            $result = $this->db->query("
                SELECT COUNT(*) AS ongoing_schedules 
                FROM schedules 
                WHERE DATE(schedule_date) = CURDATE() 
                AND status = 'in-progress'
            ");
            if (!$result) {
                throw new Exception("Error fetching ongoing schedules: " . $this->db->error);
            }
            $stats['ongoing_schedules'] = $result->fetch_assoc()['ongoing_schedules'] ?? 0;
            $result->free();

        } catch (Exception $e) {
            // Log the error
            error_log("Dashboard stats error: " . $e->getMessage());

            // Return error information (optional)
            return [
                'error' => true,
                'message' => 'Unable to fetch dashboard statistics',
                'debug_message' => $e->getMessage() // Remove this in production!
            ];
        }

        return $stats;
    }

    public function getTimeGreeting($name = null)
    {
        date_default_timezone_set('America/Toronto');
        $hour = (int) date('H');

        if ($hour < 12) {
            $greeting = "Good morning";
        } elseif ($hour < 17) {
            $greeting = "Good afternoon";
        } elseif ($hour < 21) {
            $greeting = "Good evening";
        } else {
            $greeting = "Good night";
        }

        return $name ? "{$greeting}, {$name}!" : "{$greeting}!";
    }


    public function getFormattedDateWithWeek()
    {
        date_default_timezone_set('America/Toronto');

        $formattedDate = date('l, F j, Y'); // Tuesday, March 4, 2026
        $weekNumber = date('W'); // Week number (01-52)

        return $formattedDate . ' • Week ' . ltrim($weekNumber, '0');
    }

    public function todayschedule()
    {
        date_default_timezone_set('America/Toronto');
        $today = date('Y-m-d');

        try {
            $query = "
                SELECT 
                    s.schedule_id,
                    s.schedule_date,
                    s.start_time,
                    s.end_time,
                    s.shift_type,
                    s.status,
                    s.overnight_type,
                    s.pay_per_hour,
                    s.notes,
                    
                    -- Staff Information
                    s.user_id as staff_id,
                    st.firstname as staff_firstname,
                    st.lastname as staff_lastname,

                    -- Location Information (concatenated)
                    CONCAT_WS(', ', c.residential_address, c.residential_city, c.residential_province) as location_name,
                    
                    -- Location Information
                    c.residential_address as location_address,
                    c.residential_city as location_city,
                    c.residential_province as location_province,
                    
                    -- Additional Info
                    TIME_FORMAT(s.start_time, '%h:%i %p') as start_time_formatted,
                    TIME_FORMAT(s.end_time, '%h:%i %p') as end_time_formatted,
                    TIMESTAMPDIFF(HOUR, s.start_time, s.end_time) as shift_duration,
                    
                    -- Shift Status Badge
                    CASE 
                        WHEN s.status = 'scheduled' THEN 'badge-pending'
                        WHEN s.status = 'in-progress' THEN 'badge-in-progress'
                        WHEN s.status = 'completed' THEN 'badge-completed'
                        ELSE 'badge-default'
                    END as status_badge_class,
                    
                    -- Shift Type Display
                    CASE s.shift_type
                        WHEN 'day' THEN 'Day Shift'
                        WHEN 'evening' THEN 'Evening Shift'
                        WHEN 'overnight' THEN 'Overnight Shift'
                        ELSE s.shift_type
                    END as shift_type_display,
                    
                    -- Shift Color Class
                    CASE s.shift_type
                        WHEN 'day' THEN 'badge-day'
                        WHEN 'evening' THEN 'badge-evening'
                        WHEN 'overnight' THEN 'badge-overnight'
                        ELSE 'badge-default'
                    END as shift_type_class
                    
                FROM schedules s
                INNER JOIN staffs st ON s.user_id = st.staff_id AND st.is_active = TRUE
                LEFT JOIN clients c ON c.client_id = s.client_id
                WHERE s.schedule_date = ?
                AND s.shift_type IN ('day', 'evening', 'overnight')
                ORDER BY 
                    CASE s.shift_type
                        WHEN 'day' THEN 1
                        WHEN 'evening' THEN 2
                        WHEN 'overnight' THEN 3
                        ELSE 4
                    END,
                    s.start_time ASC
            ";

            // Prepare statement
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            // Bind parameter
            $stmt->bind_param('s', $today);

            // Execute
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            // Get result
            $result = $stmt->get_result();
            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }

            $stmt->close();

            // Group schedules by shift type
            $groupedSchedules = [
                'day' => [],
                'evening' => [],
                'overnight' => []
            ];

            foreach ($schedules as $schedule) {
                $shiftType = $schedule['shift_type'];
                if (in_array($shiftType, ['day', 'evening', 'overnight'])) {
                    $groupedSchedules[$shiftType][] = $schedule;
                }
            }

            // Get shift counts
            $shiftCounts = [
                'day' => count($groupedSchedules['day']),
                'evening' => count($groupedSchedules['evening']),
                'overnight' => count($groupedSchedules['overnight']),
                'total' => count($schedules),
                'total_staff' => count(array_unique(array_column($schedules, 'staff_id')))
            ];

            return [
                'success' => true,
                'data' => $schedules,
                'grouped' => $groupedSchedules,
                'counts' => $shiftCounts,
                'date' => $today,
                'date_formatted' => date('F j, Y', strtotime($today))
            ];

        } catch (Exception $e) {
            error_log("Error fetching today's schedule: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch today\'s schedule',
                'message' => $e->getMessage()
            ];
        }
    }

    public function staffAvailability()
    {
        date_default_timezone_set('America/Toronto');
        $today = date('Y-m-d');

        try {
            // First, get ALL active staff
            $allStaffQuery = "
                SELECT 
                    staff_id,
                    CONCAT(firstname, ' ', lastname) as staff_name,
                    phone,
                    email
                FROM staffs 
                WHERE is_active = TRUE
                ORDER BY firstname ASC
            ";

            $staffResult = $this->db->query($allStaffQuery);
            if (!$staffResult) {
                throw new Exception("Failed to fetch staff: " . $this->db->error);
            }

            // Get all staff IDs for reference
            $allStaffs = [];
            $staffMap = []; // For quick lookup
            while ($staff = $staffResult->fetch_assoc()) {
                $staffId = $staff['staff_id'];
                $staffMap[$staffId] = [
                    'staff_id' => $staffId,
                    'staff_name' => $staff['staff_name'],
                    'position' => $staff['position'] ?? 'Staff',
                    'phone' => $staff['phone'] ?? '',
                    'email' => $staff['email'] ?? '',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($staff['staff_name']) . '&background=99CC33&color=fff&size=32',
                    'duty_status' => 'off_duty', // Default to off duty
                    'schedule_status' => null,
                    'shift_type' => null,
                    'location' => null,
                    'start_time' => null,
                    'end_time' => null
                ];
                $allStaffs[] = $staffId;
            }
            $staffResult->free();

            // Now get today's schedules for staff
            $scheduleQuery = "
                SELECT 
                    s.user_id as staff_id,
                    s.schedule_id,
                    s.shift_type,
                    s.status,
                    TIME_FORMAT(s.start_time, '%h:%i %p') as start_time,
                    TIME_FORMAT(s.end_time, '%h:%i %p') as end_time,
                    
                    -- Location Information
                    c.client_id,
                    CONCAT_WS(', ', c.residential_address, c.residential_city, c.residential_province) as location_name
                    
                FROM schedules s
                LEFT JOIN clients c ON c.client_id = s.client_id
                WHERE s.schedule_date = '$today'
                AND s.user_id IN (" . implode(',', array_map('intval', $allStaffs)) . ")
                ORDER BY s.start_time ASC
            ";

            $scheduleResult = $this->db->query($scheduleQuery);
            if (!$scheduleResult) {
                throw new Exception("Failed to fetch schedules: " . $this->db->error);
            }

            // Track staff with schedules
            $staffWithSchedules = [];
            $locations = [];
            $availableStaffs = []; // Staff with 'scheduled' status
            $onDutyStaffs = [];    // Staff with 'in-progress' status

            while ($schedule = $scheduleResult->fetch_assoc()) {
                $staffId = $schedule['staff_id'];
                $status = $schedule['status'];
                $staffWithSchedules[] = $staffId;

                // Update staff map with schedule info
                if (isset($staffMap[$staffId])) {
                    $locationName = $schedule['location_name'] ?? 'No location assigned';

                    // Update duty status based on schedule status
                    if ($status == 'in-progress') {
                        $staffMap[$staffId]['duty_status'] = 'on_duty';
                        $onDutyStaffs[] = $staffMap[$staffId];
                    } elseif ($status == 'scheduled') {
                        $staffMap[$staffId]['duty_status'] = 'available';
                        $availableStaffs[] = $staffMap[$staffId];
                    }

                    // Add schedule details
                    $staffMap[$staffId]['schedule_status'] = $status;
                    $staffMap[$staffId]['shift_type'] = $schedule['shift_type'];
                    $staffMap[$staffId]['location'] = $locationName;
                    $staffMap[$staffId]['start_time'] = $schedule['start_time'];
                    $staffMap[$staffId]['end_time'] = $schedule['end_time'];

                    // Group by location
                    if (!isset($locations[$locationName])) {
                        $locations[$locationName] = [
                            'name' => $locationName,
                            'staffs' => [],
                            'counts' => [
                                'on_duty' => 0,
                                'available' => 0,
                                'total' => 0
                            ]
                        ];
                    }

                    $locations[$locationName]['staffs'][] = $staffMap[$staffId];
                    $locations[$locationName]['counts']['total']++;
                    if ($status == 'in-progress') {
                        $locations[$locationName]['counts']['on_duty']++;
                    } elseif ($status == 'scheduled') {
                        $locations[$locationName]['counts']['available']++;
                    }
                }
            }

            $scheduleResult->free();

            // Calculate counts
            $totalStaff = count($staffMap);
            $onDutyCount = count($onDutyStaffs);
            $availableCount = count($availableStaffs);
            $offDutyCount = $totalStaff - ($onDutyCount + $availableCount);

            // Sort available staffs by name for the preview
            usort($availableStaffs, function ($a, $b) {
                return strcmp($a['staff_name'], $b['staff_name']);
            });

            // Take first 5 for preview
            $previewStaffs = array_slice($availableStaffs, 0, 5);

            // Sort locations by name
            ksort($locations);

            return [
                'success' => true,
                'summary' => [
                    'on_duty' => $onDutyCount,
                    'off_duty' => $offDutyCount,
                    'available' => $availableCount,
                    'total_staffs' => $totalStaff,
                    'total_locations' => count($locations)
                ],
                'all_staffs' => array_values($staffMap),
                'on_duty_staffs' => $onDutyStaffs,
                'available_staffs' => $availableStaffs,
                'preview_staffs' => $previewStaffs,
                'locations' => array_values($locations),
                'date' => $today,
                'date_formatted' => date('F j, Y', strtotime($today))
            ];

        } catch (Exception $e) {
            error_log("Error fetching current staff assignments: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch staff assignments',
                'message' => $e->getMessage(),
                'summary' => [
                    'on_duty' => 0,
                    'off_duty' => 0,
                    'available' => 0,
                    'total_staffs' => 0,
                    'total_locations' => 0
                ],
                'preview_staffs' => [],
                'locations' => []
            ];
        }
    }

    public function staffAssignments()
    {
        date_default_timezone_set('America/Toronto');
        $today = date('Y-m-d');

        try {
            $query = "
                SELECT 
                    -- Location Information
                    l.location_id,
                    l.address,
                    l.city,
                    l.province,
                    CONCAT_WS(', ', l.address, l.city, l.province) as full_address,
                    UPPER(LEFT(l.address, 2)) as location_initials,
                    
                    -- Staff Information
                    s.user_id as staff_id,
                    CONCAT(st.firstname, ' ', st.lastname) as staff_name,
                    
                    -- Schedule Information
                    s.schedule_id,
                    s.shift_type,
                    s.status,
                    TIME_FORMAT(s.start_time, '%h:%i %p') as start_time,
                    TIME_FORMAT(s.end_time, '%h:%i %p') as end_time
                    
                FROM schedules s
                INNER JOIN staffs st ON s.user_id = st.staff_id AND st.is_active = TRUE
                INNER JOIN locations l ON l.location_id = s.location_id
                WHERE s.schedule_date = '$today'
                AND s.shift_type IN ('day', 'evening', 'overnight')
                ORDER BY 
                    l.address ASC,
                    st.firstname ASC
            ";

            $result = $this->db->query($query);

            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            // Initialize locations array
            $locations = [];
            $allStaffCount = 0;

            // Process results
            while ($row = $result->fetch_assoc()) {
                $locationId = $row['location_id'];
                $allStaffCount++;

                // Initialize location if not exists
                if (!isset($locations[$locationId])) {
                    $locations[$locationId] = [
                        'location_id' => $locationId,
                        'full_address' => $row['full_address'],
                        'initials' => $row['location_initials'],
                        'staff_count' => 0,
                        'staffs' => [],
                        'preview_staffs' => []
                    ];
                }

                // Add staff to location
                $staffData = [
                    'staff_id' => $row['staff_id'],
                    'staff_name' => $row['staff_name'],
                    'shift_type' => $row['shift_type'],
                    'status' => $row['status'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($row['staff_name']) . '&background=99CC33&color=fff&size=32'
                ];

                $locations[$locationId]['staffs'][] = $staffData;
                $locations[$locationId]['staff_count']++;
            }

            $result->free();

            // Process each location to create preview staffs (first 3 for avatar stack)
            foreach ($locations as &$location) {
                // Sort staffs by name
                usort($location['staffs'], function ($a, $b) {
                    return strcmp($a['staff_name'], $b['staff_name']);
                });

                // Get first 3 for preview avatars
                $location['preview_staffs'] = array_slice($location['staffs'], 0, 3);

                // Format staff count display
                $location['staff_count_display'] = $location['staff_count'] . ' ' .
                    ($location['staff_count'] == 1 ? 'Staff' : 'Staffs');

                // Get additional count for "+X more"
                $location['additional_count'] = max(0, $location['staff_count'] - 3);
            }

            // Convert to indexed array and sort by location name
            $locations = array_values($locations);
            usort($locations, function ($a, $b) {
                return strcmp($a['location_name'], $b['location_name']);
            });

            return [
                'success' => true,
                'locations' => $locations,
                'total_locations' => count($locations),
                'total_staffs' => $allStaffCount,
                'date' => $today,
                'date_formatted' => date('F j, Y', strtotime($today))
            ];

        } catch (Exception $e) {
            error_log("Error fetching recent assignments: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch assignments',
                'message' => $e->getMessage(),
                'locations' => [],
                'total_locations' => 0,
                'total_staffs' => 0
            ];
        }
    }

    public function fetch_recent_activities($limit = 5)
    {
        date_default_timezone_set('America/Toronto');

        try {
            $query = "
                    SELECT 
                        activity_id,
                        user_name,
                        activity_type,
                        activity_title,
                        activity_description,
                        created_at
                    FROM recent_activities
                    ORDER BY created_at DESC
                    LIMIT ?
                ";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $activities = [];
            while ($row = $result->fetch_assoc()) {
                // Get icon data based on activity type
                $iconData = $this->getActivityIcon($row['activity_type']);

                // Format the time ago
                $row['time_ago'] = $this->timeAgo($row['created_at']);

                // Add icon data to the row
                $row['icon'] = $iconData['icon'];
                $row['icon_bg'] = $iconData['icon_bg'];
                $row['icon_color'] = $iconData['icon_color'];

                // Use the title from icons array if not set in database
                if (empty($row['activity_title'])) {
                    $row['activity_title'] = $iconData['title'];
                }

                $activities[] = $row;
            }

            $stmt->close();

            return [
                'success' => true,
                'activities' => $activities,
                'total' => count($activities),
                'has_more' => count($activities) >= $limit
            ];

        } catch (Exception $e) {
            error_log("Error fetching recent activities: " . $e->getMessage());
            return [
                'success' => false,
                'activities' => [],
                'total' => 0,
                'has_more' => false
            ];
        }
    }

    public function getActivityIcon($activity_type)
    {
        $icons = [
            // Authentication Activities
            'login' => [
                'icon' => 'sign-in-alt',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'User Logged In'
            ],
            'logout' => [
                'icon' => 'sign-out-alt',
                'icon_bg' => 'bg-gray-100',
                'icon_color' => 'text-gray-600',
                'title' => 'User Logged Out'
            ],
            'failed_login' => [
                'icon' => 'exclamation-triangle',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Failed Login Attempt'
            ],

            // CRUD Operations
            'create' => [
                'icon' => 'plus-circle',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Item Created'
            ],
            'update' => [
                'icon' => 'edit',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Item Updated'
            ],
            'delete' => [
                'icon' => 'trash-alt',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Item Deleted'
            ],
            'view' => [
                'icon' => 'eye',
                'icon_bg' => 'bg-purple-100',
                'icon_color' => 'text-purple-600',
                'title' => 'Item Viewed'
            ],
            'export' => [
                'icon' => 'file-export',
                'icon_bg' => 'bg-indigo-100',
                'icon_color' => 'text-indigo-600',
                'title' => 'Data Exported'
            ],

            // Time & Attendance
            'clock_in' => [
                'icon' => 'clock',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Clock In'
            ],
            'clock_out' => [
                'icon' => 'clock',
                'icon_bg' => 'bg-orange-100',
                'icon_color' => 'text-orange-600',
                'title' => 'Clock Out'
            ],
            'start_break' => [
                'icon' => 'coffee',
                'icon_bg' => 'bg-yellow-100',
                'icon_color' => 'text-yellow-600',
                'title' => 'Break Started'
            ],
            'end_break' => [
                'icon' => 'coffee',
                'icon_bg' => 'bg-yellow-100',
                'icon_color' => 'text-yellow-600',
                'title' => 'Break Ended'
            ],

            // Schedule Management
            'schedule_created' => [
                'icon' => 'calendar-plus',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Schedule Created'
            ],
            'schedule_updated' => [
                'icon' => 'calendar-alt',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Schedule Updated'
            ],
            'schedule_cancelled' => [
                'icon' => 'calendar-times',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Schedule Cancelled'
            ],

            // Payroll
            'payroll_generated' => [
                'icon' => 'file-invoice-dollar',
                'icon_bg' => 'bg-purple-100',
                'icon_color' => 'text-purple-600',
                'title' => 'Payroll Generated'
            ],
            'payroll_processed' => [
                'icon' => 'cogs',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Payroll Processed'
            ],
            'payroll_paid' => [
                'icon' => 'money-bill-wave',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Payroll Paid'
            ],

            // Invoices
            'invoice_generated' => [
                'icon' => 'file-invoice',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Invoice Generated'
            ],
            'invoice_sent' => [
                'icon' => 'paper-plane',
                'icon_bg' => 'bg-indigo-100',
                'icon_color' => 'text-indigo-600',
                'title' => 'Invoice Sent'
            ],
            'invoice_paid' => [
                'icon' => 'check-circle',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Invoice Paid'
            ],

            // Client Management
            'client_created' => [
                'icon' => 'user-plus',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Client Created'
            ],
            'client_updated' => [
                'icon' => 'user-edit',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Client Updated'
            ],
            'client_deleted' => [
                'icon' => 'user-minus',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Client Deleted'
            ],

            // Staff Management
            'staff_created' => [
                'icon' => 'user-plus',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Staff Created'
            ],
            'staff_updated' => [
                'icon' => 'user-edit',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Staff Updated'
            ],
            'staff_deleted' => [
                'icon' => 'user-minus',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Staff Deleted'
            ],

            // Location Management
            'location_created' => [
                'icon' => 'map-marker-alt',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Location Created'
            ],
            'location_updated' => [
                'icon' => 'map-marker-alt',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Location Updated'
            ],
            'location_deleted' => [
                'icon' => 'map-marker-alt',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Location Deleted'
            ],

            // Holiday Management
            'holiday_created' => [
                'icon' => 'gift',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Holiday Created'
            ],
            'holiday_updated' => [
                'icon' => 'gift',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Holiday Updated'
            ],
            'holiday_deleted' => [
                'icon' => 'gift',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Holiday Deleted'
            ],

            // Reports & Exports
            'report_generated' => [
                'icon' => 'chart-bar',
                'icon_bg' => 'bg-purple-100',
                'icon_color' => 'text-purple-600',
                'title' => 'Report Generated'
            ],
            'export_completed' => [
                'icon' => 'file-export',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'title' => 'Export Completed'
            ],
            'import_completed' => [
                'icon' => 'file-import',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'title' => 'Import Completed'
            ],

            // System Settings
            'settings_changed' => [
                'icon' => 'cog',
                'icon_bg' => 'bg-gray-100',
                'icon_color' => 'text-gray-600',
                'title' => 'Settings Changed'
            ],
            'password_changed' => [
                'icon' => 'key',
                'icon_bg' => 'bg-yellow-100',
                'icon_color' => 'text-yellow-600',
                'title' => 'Password Changed'
            ],
            'permission_updated' => [
                'icon' => 'lock',
                'icon_bg' => 'bg-red-100',
                'icon_color' => 'text-red-600',
                'title' => 'Permissions Updated'
            ]
        ];

        // Return icon data or default if type not found
        return $icons[$activity_type] ?? [
            'icon' => 'bell',
            'icon_bg' => 'bg-gray-100',
            'icon_color' => 'text-gray-600',
            'title' => 'Activity'
        ];
    }

    /**
     * Helper function to format time ago
     */
    private function timeAgo($datetime)
    {
        if (!$datetime)
            return 'Just now';

        // Use PHP's configured timezone for both sides so DST transitions
        // and MySQL/PHP timezone mismatches don't produce negative diffs.
        $tz = new DateTimeZone(date_default_timezone_get());
        $time = new DateTime($datetime, $tz);
        $now = new DateTime('now', $tz);
        $diff = $now->getTimestamp() - $time->getTimestamp();

        // Safety: if clocks are skewed or record is from the future, show "Just now"
        if ($diff < 0)
            return 'Just now';

        if ($diff < 60) {
            return $diff < 5 ? 'Just now' : $diff . ' sec ago';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time->getTimestamp());
        }
    }

    public function get_all_staffs()
    {

        try {
            $query = "
                SELECT 
                    staff_id,
                    firstname,
                    middlename,
                    lastname,
                    is_active,
                    is_admin,
                    role,
                    email,
                    phone,
                    address,
                    city,
                    province,
                    country,
                    postal_code,
                    reg_date
                FROM staffs
                ORDER BY firstname ASC
            ";

            $result = $this->db->query($query);
            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            $staffs = [];
            while ($row = $result->fetch_assoc()) {

                $row['id'] = (int) $row['staff_id'];
                $row['role'] = $row['role'] ?? 'staff';
                $row['regDate'] = $row['reg_date'];
                $row['isActive'] = (bool) $row['is_active'];
                $row['address'] = $row['address'];
                $row['city'] = $row['city'];
                $row['province'] = $row['province'];
                $row['postalCode'] = $row['postal_code'];
                $row['country'] = $row['country'];
                $row['phone'] = $row['phone'];
                $row['email'] = $row['email'];
                $row['firstname'] = $row['firstname'];
                $row['middlename'] = $row['middlename'];
                $row['lastname'] = $row['lastname'];
                $row['isAdmin'] = (strtolower($row['role']) === 'admin');

                $staffs[] = $row;

            }

            $result->free();

            return [
                'success' => true,
                'staffs' => $staffs,
                'total' => count($staffs)
            ];
        } catch (Exception $e) {
            error_log("Error fetching staffs: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch staff data',
                'error' => $e->getMessage(),
                'staffs' => [],
                'total' => 0
            ];
        }
    }

    public function get_all_clients()
    {
        try {
            $query = "
                SELECT 
                    client_id,
                    firstname,
                    middlename,
                    lastname,
                    email,
                    mobile,
                    residential_name,
                    residential_address,
                    residential_city,
                    residential_province,
                    residential_postal_code,
                    residential_country,
                    billing_name,
                    billing_address,
                    billing_city,
                    billing_province,
                    billing_postal_code,
                    billing_country,
                    billing_email,
                    bill_rate,
                    bill_rate_rest,
                    latitude,
                    longitude,
                    reg_date
                FROM clients
                ORDER BY firstname ASC
            ";

            $result = $this->db->query($query);
            if (!$result) {
                throw new Exception("Query failed: " . $this->db->error);
            }

            $clients = [];
            while ($row = $result->fetch_assoc()) {

                $row['id'] = (int) $row['client_id'];
                $row['firstname'] = $row['firstname'];
                $row['middlename'] = $row['middlename'];
                $row['lastname'] = $row['lastname'];
                $row['email'] = $row['email'];
                $row['mobile'] = $row['mobile'];
                $row['residentialName'] = $row['residential_name'];
                $row['residentialAddress'] = $row['residential_address'];
                $row['residentialCity'] = $row['residential_city'];
                $row['residentialProvince'] = $row['residential_province'];
                $row['residentialPostalCode'] = $row['residential_postal_code'];
                $row['residentialCountry'] = $row['residential_country'];
                $row['billingName'] = $row['billing_name'];
                $row['billingAddress'] = $row['billing_address'];
                $row['billingCity'] = $row['billing_city'];
                $row['billingProvince'] = $row['billing_province'];
                $row['billingPostalCode'] = $row['billing_postal_code'];
                $row['billingCountry'] = $row['billing_country'];
                $row['billingEmail'] = $row['billing_email'];
                $row['billingRate'] = (float) $row['bill_rate'];
                $row['billingRateRest'] = (float) $row['bill_rate_rest'];
                $row['regDate'] = $row['reg_date'];
                $row['client_id'] = (int) $row['client_id'];
                $row['latitude'] = $row['latitude'] ?? null;
                $row['longitude'] = $row['longitude'] ?? null;

                $clients[] = $row;
            }

            $result->free();

            return [
                'status' => true,
                'clients' => $clients,
                'total' => count($clients)
            ];
        } catch (Exception $e) {
            error_log("Error fetching clients: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to fetch client data',
                'error' => $e->getMessage(),
                'clients' => [],
                'total' => 0
            ];
        }
    }

    public function add_staff($staffData)
    {
        date_default_timezone_set('America/Toronto');
        $date = date('Y-m-d H:i:s');
        try {
            $query = "
                INSERT INTO staffs (firstname, middlename, lastname, is_active, role, email, phone, address, city, province, country, postal_code, reg_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $isActive = filter_var($staffData['isActive'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $isAdminBool = filter_var($staffData['isAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $role = $isAdminBool ? 'admin' : 'staff';

            $stmt = $this->db->prepare($query);
            $stmt->bind_param(
                'sssisssssssss',
                $staffData['firstname'],
                $staffData['middlename'],
                $staffData['lastname'],
                $isActive,
                $role,
                $staffData['email'],
                $staffData['phone'],
                $staffData['address'],
                $staffData['city'],
                $staffData['province'],
                $staffData['country'],
                $staffData['postalCode'],
                $date
            );

            if ($stmt->execute()) {
                $newStaffId = $stmt->insert_id;

                // Queue welcome email — runs in background via worker.php
                $this->queueWelcomeEmail([
                    'firstname' => $staffData['firstname'],
                    'lastname' => $staffData['lastname'],
                    'email' => $staffData['email'],
                ]);

                return [
                    'status' => true,
                    'message' => 'Staff added successfully',
                    'staff_id' => $newStaffId
                ];
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error adding staff: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to add staff',
                'error' => $e->getMessage()
            ];
        }
    }

    public function update_staff($staffData)
    {
        try {
            $query = "
                UPDATE staffs 
                SET firstname = ?, middlename = ?, lastname = ?, email = ?, phone = ?, address = ?, city = ?, province = ?, country = ?, postal_code = ?, is_active = ?, role = ?
                WHERE staff_id = ?
            ";

            $isActive = filter_var($staffData['isActive'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $isAdminBool = filter_var($staffData['isAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $role = $isAdminBool ? 'admin' : 'staff';
            error_log("Mapped isActive: " . $isActive . ", isAdmin: " . $isAdminBool . ", role: " . $role);

            $stmt = $this->db->prepare($query);
            $stmt->bind_param(
                'ssssssssssisi',
                $staffData['firstname'],
                $staffData['middlename'],
                $staffData['lastname'],
                $staffData['email'],
                $staffData['phone'],
                $staffData['address'],
                $staffData['city'],
                $staffData['province'],
                $staffData['country'],
                $staffData['postalCode'],
                $isActive,
                $role,
                $staffData['id']
            );

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Staff updated successfully'
                ];
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error updating staff: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to update staff',
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete_staff($staff_id)
    {
        try {
            // Check if staff exists
            $staffInfo = $this->getStaffInfo($staff_id);

            if (empty($staffInfo)) {
                throw new Exception("Staff not found with ID: " . $staff_id);
            }

            if ($staffInfo['is_admin']) {
                throw new Exception("Cannot delete admin staff");
            }

            // Delete staff
            $query = "DELETE FROM staffs WHERE staff_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $staff_id);

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Staff deleted successfully'
                ];
            } else {
                throw new Exception("Delete failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error deleting staff: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to delete staff',
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete_client($client_id)
    {
        try {
            $query = "DELETE FROM clients WHERE client_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $client_id);

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Client deleted successfully'
                ];
            } else {
                throw new Exception("Delete failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error deleting client: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to delete client',
                'error' => $e->getMessage()
            ];
        }
    }

    public function check_email_exists($email)
    {
        try {
            $query = "SELECT staff_id FROM staffs WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }

    public function sidebarCounter($table)
    {
        try {
            $query = "SELECT COUNT(*) AS count FROM $table";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            return $row['count'];
        } catch (Exception $e) {
            error_log("Error fetching counter: " . $e->getMessage());
            return 0;
        }
    }

    public function update_or_create_client($clientData)
    {
        date_default_timezone_set('America/Toronto');
        $date = date('Y-m-d H:i:s');
        try {
            if ($clientData['id'] == 0) {
                $query = "
                    INSERT INTO clients (firstname, middlename, lastname, mobile, email, residential_name, residential_address, residential_city, residential_province, residential_postal_code, residential_country, billing_name, billing_address, billing_city, billing_province, billing_postal_code, billing_country, billing_email, bill_rate, bill_rate_rest, latitude, longitude, reg_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = $this->db->prepare($query);
                $stmt->bind_param(
                    'ssssssssssssssssssdddss',
                    $clientData['firstname'],
                    $clientData['middlename'],
                    $clientData['lastname'],
                    $clientData['mobile'],
                    $clientData['email'],
                    $clientData['residentialName'],
                    $clientData['residentialAddress'],
                    $clientData['residentialCity'],
                    $clientData['residentialProvince'],
                    $clientData['residentialPostalCode'],
                    $clientData['residentialCountry'],
                    $clientData['billingName'],
                    $clientData['billingAddress'],
                    $clientData['billingCity'],
                    $clientData['billingProvince'],
                    $clientData['billingPostalCode'],
                    $clientData['billingCountry'],
                    $clientData['billingEmail'],
                    $clientData['billingRate'],
                    $clientData['billingRateRest'],
                    $clientData['latitude'],
                    $clientData['longitude'],
                    $date
                );

                if ($stmt->execute()) {
                    return [
                        'status' => true,
                        'message' => 'Client added successfully',
                        'client_id' => $stmt->insert_id
                    ];
                } else {
                    throw new Exception("Insert failed: " . $stmt->error);
                }

            } else {

                //update existing client
                $query = "
                    UPDATE clients 
                    SET firstname = ?, middlename = ?, lastname = ?, mobile = ?, email = ?, residential_name = ?, residential_address = ?, residential_city = ?, residential_province = ?, residential_postal_code = ?, residential_country = ?, billing_name = ?, billing_address = ?, billing_city = ?, billing_province = ?, billing_postal_code = ?, billing_country = ?, billing_email = ?, bill_rate = ?, bill_rate_rest = ?, latitude = ?, longitude = ?
                    WHERE client_id = ?
                ";

                $stmt = $this->db->prepare($query);
                $stmt->bind_param(
                    'ssssssssssssssssssddddi',
                    $clientData['firstname'],
                    $clientData['middlename'],
                    $clientData['lastname'],
                    $clientData['mobile'],
                    $clientData['email'],
                    $clientData['residentialName'],
                    $clientData['residentialAddress'],
                    $clientData['residentialCity'],
                    $clientData['residentialProvince'],
                    $clientData['residentialPostalCode'],
                    $clientData['residentialCountry'],
                    $clientData['billingName'],
                    $clientData['billingAddress'],
                    $clientData['billingCity'],
                    $clientData['billingProvince'],
                    $clientData['billingPostalCode'],
                    $clientData['billingCountry'],
                    $clientData['billingEmail'],
                    $clientData['billingRate'],
                    $clientData['billingRateRest'],
                    $clientData['latitude'],
                    $clientData['longitude'],
                    $clientData['id']
                );

                if ($stmt->execute()) {
                    return [
                        'status' => true,
                        'message' => 'Client updated successfully'
                    ];
                } else {
                    throw new Exception("Update failed: " . $stmt->error);
                }
            }

        } catch (Exception $e) {
            error_log("Error adding client: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to add client',
                'error' => $e->getMessage()
            ];
        }

    }

    public function getCoordinates($address)
    {
        $address = urlencode($address);
        //write a get curl request to geocode the address using a free geocoding API like OpenCage or Geoapify
        $url = "https://api.geoapify.com/v1/geocode/search?text={$address}&apiKey=d60bc710cd934b1d9fd833853efb1639";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['features'][0]['geometry']['coordinates'])) {
            return [
                'latitude' => $data['features'][0]['geometry']['coordinates'][1],
                'longitude' => $data['features'][0]['geometry']['coordinates'][0]
            ];
        }

        return [
            'latitude' => 0.0,
            'longitude' => 0.0
        ];
    }

    public function saveHoliday($holidayData)
    {
        try {
            $holidayId = $holidayData['holiday_id'];
            $holidayName = $holidayData['holiday_name'];
            $fixedMonth = $holidayData['fixed_month'];
            $fixedDay = $holidayData['fixed_day'];
            $premiumPercentage = $holidayData['premium_percentage'];

            if ($holidayId > 0) {
                $query = "UPDATE holidays SET holiday_name = ?, fixed_month = ?, fixed_day = ?, premium_percentage = ? WHERE holiday_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('siidi', $holidayName, $fixedMonth, $fixedDay, $premiumPercentage, $holidayId);
            } else {
                $query = "INSERT INTO holidays (holiday_name, fixed_month, fixed_day, premium_percentage) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('siid', $holidayName, $fixedMonth, $fixedDay, $premiumPercentage);
            }

            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Holiday saved successfully'];
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error adding holiday: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to add holiday',
                'error' => $e->getMessage()
            ];
        }
    }

    public function get_all_holidays()
    {
        $query = "SELECT * FROM holidays";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $year = date('Y');
        $holidays = [];
        foreach ($result as $row) {
            $row['holiday_id'] = (int) $row['holiday_id'];
            $row['holiday_name'] = $row['holiday_name'];
            $row['holiday_date'] = date('Y-m-d', strtotime("{$year}-{$row['fixed_month']}-{$row['fixed_day']}"));
            $row['premium_percentage'] = (float) $row['premium_percentage'];
            $holidays[] = $row;
        }
        return $holidays;
    }

    public function delete_holiday($holiday_id)
    {
        try {
            $query = "DELETE FROM holidays WHERE holiday_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $holiday_id);
            if ($stmt->execute()) {
                return ['status' => true, 'message' => 'Holiday deleted successfully'];
            } else {
                throw new Exception("Delete failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error deleting holiday: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to delete holiday',
                'error' => $e->getMessage()
            ];
        }
    }

    public function get_all_schedules($start_date = null, $end_date = null)
    {
        date_default_timezone_set('America/Toronto');

        // Use provided dates or default to today
        if (!$start_date) {
            $start_date = date('Y-m-d');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        try {
            $query = "
                SELECT 
                    s.schedule_id as id,
                    s.user_id,
                    s.client_id,
                    s.schedule_date,
                    TIME(s.start_time) as start_time,
                    TIME(s.end_time) as end_time,
                    TIME(s.clockin_time) as clock_in,
                    TIME(s.clockout_time) as clock_out,
                    s.shift_type,
                    s.overnight_type,
                    s.status,
                    s.pay_per_hour,
                    s.holiday_pay,
                    
                    -- Staff Information
                    CONCAT(st.firstname, ' ', st.lastname) as staff_name,
                    st.staff_id as staff_employee_id,
                    
                    -- Client Information
                    CONCAT(c.firstname, ' ', c.lastname) as client_name,
                    CONCAT_WS(', ', c.residential_city, c.residential_province) as client_location,
                    
                    -- Calculate worked duration if status is completed
                    CASE 
                        WHEN s.status = 'completed' AND s.clockin_time IS NOT NULL AND s.clockout_time IS NOT NULL THEN
                            TIMESTAMPDIFF(HOUR, s.clockin_time, s.clockout_time)
                        ELSE NULL
                    END as worked_hours,
                    
                    -- Calculate worked minutes for more precise duration
                    CASE 
                        WHEN s.status = 'completed' AND s.clockin_time IS NOT NULL AND s.clockout_time IS NOT NULL THEN
                            TIMESTAMPDIFF(MINUTE, s.clockin_time, s.clockout_time)
                        ELSE NULL
                    END as worked_minutes,
                    
                    -- Format worked duration as HH:MM
                    CASE 
                        WHEN s.status = 'completed' AND s.clockin_time IS NOT NULL AND s.clockout_time IS NOT NULL THEN
                            TIME_FORMAT(TIMEDIFF(s.clockout_time, s.clockin_time), '%H:%i')
                        ELSE NULL
                    END as worked_duration_formatted
                    
                FROM schedules s
                INNER JOIN staffs st ON s.user_id = st.staff_id AND st.is_active = TRUE
                INNER JOIN clients c ON s.client_id = c.client_id AND c.is_active = TRUE
                WHERE DATE(s.schedule_date) BETWEEN ? AND ?
                ORDER BY s.schedule_date ASC, s.start_time ASC
            ";

            // Using prepared statement for security
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param('ss', $start_date, $end_date);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result();

            $schedules = [];

            while ($row = $result->fetch_assoc()) {
                // Format the response exactly as needed
                $schedule = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'staff_name' => $row['staff_name'],
                    'client_id' => $row['client_id'],
                    'client_name' => $row['client_name'],
                    'client_location' => $row['client_location'],
                    'schedule_date' => $row['schedule_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'shift_type' => $row['shift_type'],
                    'overnight_type' => $row['overnight_type'],
                    'status' => $row['status'],
                    'pay_per_hour' => floatval($row['pay_per_hour']),
                    'holiday_pay' => floatval($row['holiday_pay'] ?? 0)
                ];

                // Add clock in/out times if they exist
                if ($row['clock_in']) {
                    $schedule['clock_in'] = $row['clock_in'];
                }

                if ($row['clock_out']) {
                    $schedule['clock_out'] = $row['clock_out'];
                }

                // Add worked duration if status is completed
                if ($row['status'] == 'completed') {
                    if ($row['worked_duration_formatted']) {
                        $schedule['worked_duration'] = $row['worked_duration_formatted'];
                        $schedule['worked_hours'] = $row['worked_hours'];
                        $schedule['worked_minutes'] = $row['worked_minutes'];
                    } else {
                        // If no clock in/out recorded, use scheduled duration
                        $schedule['worked_duration'] = 'Not recorded';
                        $schedule['worked_hours'] = 0;
                        $schedule['worked_minutes'] = 0;
                    }
                }

                // Calculate final pay if holiday pay exists
                if ($row['holiday_pay'] > 0) {
                    $schedule['original_pay_per_hour'] = $schedule['pay_per_hour'];
                    $schedule['final_pay_per_hour'] = round($schedule['pay_per_hour'] + ($schedule['pay_per_hour'] * $row['holiday_pay'] / 100), 2);
                    $schedule['holiday_pay_percentage'] = $row['holiday_pay'];
                }

                $schedules[] = $schedule;
            }

            $stmt->close();

            return [
                'status' => true,
                'data' => $schedules,
                'total' => count($schedules),
                'date_range' => [
                    'start' => $start_date,
                    'end' => $end_date
                ]
            ];

        } catch (Exception $e) {
            error_log("Error fetching schedule: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to fetch schedule',
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function delete_schedule($schedule_id)
    {
        try {
            $query = "DELETE FROM schedules WHERE schedule_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $schedule_id);

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Schedule deleted successfully'
                ];
            } else {
                throw new Exception("Delete failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error deleting schedule: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ];
        }
    }

    public function get_schedule_by_id($schedule_id)
    {
        try {
            $query = "SELECT * FROM schedules WHERE schedule_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $schedule_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return [
                'status' => true,
                'schedule' => $result->fetch_assoc()
            ];
        } catch (Exception $e) {
            error_log("Error fetching schedule by ID: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to fetch schedule',
                'error' => $e->getMessage(),
                'schedule' => null
            ];
        }
    }

    public function update_schedule($scheduleData)
    {
        date_default_timezone_set('America/Toronto');
        $date = date('Y-m-d');
        $schedule_date = $scheduleData['schedule_date'] ?? $date;
        $scheduleData['start_time'] = $schedule_date . ' ' . $scheduleData['start_time'];
        $scheduleData['end_time'] = $schedule_date . ' ' . $scheduleData['end_time'];
        $scheduleData['clock_in'] = isset($scheduleData['clock_in']) ? $schedule_date . ' ' . $scheduleData['clock_in'] : null;
        $scheduleData['clock_out'] = isset($scheduleData['clock_out']) ? $schedule_date . ' ' . $scheduleData['clock_out'] : null;
        try {

            $query = "
                UPDATE schedules 
                SET start_time = ?, end_time = ?, clockin_time = ?, clockout_time = ?, status = ?
                WHERE schedule_id = ?
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param(
                'sssssi',
                $scheduleData['start_time'],
                $scheduleData['end_time'],
                $scheduleData['clock_in'],
                $scheduleData['clock_out'],
                $scheduleData['status'],
                $scheduleData['id']
            );

            if ($stmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Schedule updated successfully'
                ];
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error updating schedule: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to update schedule',
                'error' => $e->getMessage()
            ];
        }
    }

    public function saveSchedule($scheduleData)
    {
        try {
            date_default_timezone_set('America/Toronto');

            // Validate incoming data
            if (empty($scheduleData['client_id'])) {
                return [
                    'status' => false,
                    'message' => 'Client ID is required'
                ];
            }

            if (empty($scheduleData['schedules']) || !is_array($scheduleData['schedules'])) {
                return [
                    'status' => false,
                    'message' => 'No schedules to save'
                ];
            }

            $clientId = $scheduleData['client_id'];
            $schedules = $scheduleData['schedules'];
            $successCount = 0;
            $failedSchedules = [];
            $savedSchedules = [];

            // Fetch client name once for notification emails
            $clientStmt = $this->db->prepare("SELECT CONCAT(firstname, ' ', lastname) AS client_name FROM clients WHERE client_id = ?");
            $clientStmt->bind_param('i', $clientId);
            $clientStmt->execute();
            $clientRow = $clientStmt->get_result()->fetch_assoc();
            $clientStmt->close();
            $clientName = $clientRow ? $clientRow['client_name'] : 'the client';

            // Begin transaction
            $this->db->begin_transaction();

            foreach ($schedules as $index => $schedule) {
                // Validate required fields for each schedule
                $errors = $this->validateScheduleItem($schedule, $index);

                if (!empty($errors)) {
                    $failedSchedules[] = [
                        'index' => $index,
                        'data' => $schedule,
                        'errors' => $errors
                    ];
                    continue;
                }

                try {
                    // Prepare schedule data
                    $scheduleDate = $schedule['date'];
                    $startTime = $scheduleDate . ' ' . $schedule['start_time'] . ':00';
                    $endTime = $scheduleDate . ' ' . $schedule['end_time'] . ':00';

                    // For overnight shifts that go past midnight
                    if ($schedule['shift_type'] === 'overnight' && strtotime($schedule['end_time']) <= strtotime($schedule['start_time'])) {
                        $endTime = date('Y-m-d H:i:s', strtotime($scheduleDate . ' ' . $schedule['end_time'] . ':00') + 86400);
                    }

                    // Calculate pay rate with holiday premium if applicable
                    $basePayRate = floatval($schedule['pay_rate']);
                    $holidayRate = isset($schedule['holiday_rate']) ? floatval($schedule['holiday_rate']) : 0;
                    $isHoliday = isset($schedule['is_holiday']) ? $schedule['is_holiday'] : false;

                    // Calculate final pay rate (add holiday premium if applicable)
                    $finalPayRate = $basePayRate;
                    if ($isHoliday && $holidayRate > 0) {
                        $finalPayRate = $basePayRate + ($basePayRate * $holidayRate / 100);
                    }

                    $shiftType = $schedule['shift_type'];
                    $overnightType = isset($schedule['overnight_type']) ? $schedule['overnight_type'] : 'none';
                    $status = 'scheduled'; // Default status for new schedules
                    $staffInfo = $this->getStaffInfo($schedule['staff_id']);
                    $staff_email = $staffInfo['email'] ?? null;

                    $query = "
                        INSERT INTO schedules (
                            user_id,
                            email,
                            client_id, 
                            schedule_date, 
                            start_time, 
                            end_time, 
                            shift_type, 
                            overnight_type,
                            pay_per_hour,
                            status, 
                            created_at, 
                            updated_at,
                            holiday_pay
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)
                    ";

                    $stmt = $this->db->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $this->db->error);
                    }

                    $stmt->bind_param(
                        'isisssssdsd',
                        $schedule['staff_id'],
                        $staff_email,
                        $clientId,
                        $scheduleDate,
                        $startTime,
                        $endTime,
                        $shiftType,
                        $overnightType,
                        $basePayRate,
                        $status,
                        $holidayRate
                    );

                    if ($stmt->execute()) {
                        $scheduleId = $stmt->insert_id;
                        $successCount++;
                        $savedSchedules[] = [
                            'id' => $scheduleId,
                            'staff_id' => $schedule['staff_id'],
                            'staff_info' => $staffInfo,
                            'client_name' => $clientName,
                            'date' => $scheduleDate,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'shift_type' => $shiftType,
                            'is_holiday' => $isHoliday,
                            'holiday_name' => isset($schedule['holiday_name']) ? $schedule['holiday_name'] : null,
                            'final_pay_rate' => $finalPayRate
                        ];

                        // Log activity for each created schedule
                        //$this->logScheduleActivity($scheduleId, $schedule['staff_id'], $clientId, $scheduleDate, $isHoliday, $holidayRate);
                    } else {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }

                    $stmt->close();

                } catch (Exception $e) {
                    $failedSchedules[] = [
                        'index' => $index,
                        'data' => $schedule,
                        'errors' => [$e->getMessage()]
                    ];
                }
            }

            // Commit or rollback based on success
            if (empty($failedSchedules)) {
                $this->db->commit();

                // Group schedules by staff and queue one email per staff member
                $schedulesByStaff = [];
                foreach ($savedSchedules as $sched) {
                    $sid = $sched['staff_id'];
                    if (!isset($schedulesByStaff[$sid])) {
                        $schedulesByStaff[$sid] = [
                            'staff_info' => $sched['staff_info'],
                            'schedules' => []
                        ];
                    }
                    $schedulesByStaff[$sid]['schedules'][] = $sched;
                }
                foreach ($schedulesByStaff as $group) {
                    if (!empty($group['staff_info']['email'])) {
                        $this->queueScheduleNotificationEmail($group['staff_info'], $group['schedules']);
                    }
                }

                return [
                    'status' => true,
                    'message' => $successCount . ' schedule(s) created successfully',
                    'success_count' => $successCount,
                    'saved_schedules' => $savedSchedules
                ];
            } else {
                $this->db->rollback();

                // If all schedules failed
                if ($successCount === 0) {
                    return [
                        'status' => false,
                        'message' => 'Failed to create schedule ' . $failedSchedules[0]['errors'][0],
                        'errors' => $failedSchedules,
                        'failed_count' => count($failedSchedules)
                    ];
                }

                // Partial success (some schedules were saved)
                return [
                    'status' => true,
                    'message' => $successCount . ' schedule(s) created, ' . count($failedSchedules) . ' failed',
                    'success_count' => $successCount,
                    'failed_count' => count($failedSchedules),
                    'failed_schedules' => $failedSchedules,
                    'saved_schedules' => $savedSchedules,
                    'partial_success' => true
                ];
            }

        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->db->connect_errno === 0) {
                $this->db->rollback();
            }

            error_log("Error saving schedules: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to save schedules',
                'error' => $e->getMessage()
            ];
        }
    }

    private function validateScheduleItem($schedule, $index)
    {
        $errors = [];

        // Validate staff_id
        if (empty($schedule['staff_id'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Staff member is required";
        } elseif (!is_numeric($schedule['staff_id'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Invalid staff ID";
        }

        // Validate date
        if (empty($schedule['date'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Date is required";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $schedule['date']);
            if (!$date || $date->format('Y-m-d') !== $schedule['date']) {
                $errors[] = "Schedule " . ($index + 1) . ": Invalid date format";
            }

            // Check if date is in the past
            /* $today = date('Y-m-d');
            if ($schedule['date'] < $today) {
                $errors[] = "Schedule " . ($index + 1) . ": Date cannot be in the past";
            } 
            */
        }

        // Validate start_time
        if (empty($schedule['start_time'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Start time is required";
        } else {
            $time = DateTime::createFromFormat('H:i:s', $schedule['start_time']);
            if (!$time) {
                $errors[] = "Schedule " . ($index + 1) . ": Invalid start time format";
            }
        }

        // Validate end_time
        if (empty($schedule['end_time'])) {
            $errors[] = "Schedule " . ($index + 1) . ": End time is required";
        } else {
            $time = DateTime::createFromFormat('H:i:s', $schedule['end_time']);
            if (!$time) {
                $errors[] = "Schedule " . ($index + 1) . ": Invalid end time format";
            }
        }

        // Validate start_time vs end_time
        if (!empty($schedule['start_time']) && !empty($schedule['end_time'])) {
            $start = strtotime($schedule['start_time']);
            $end = strtotime($schedule['end_time']);

            // For overnight shifts, end time can be earlier than start time
            // This is allowed, so we don't need to validate that end > start

            $diffHours = ($end < $start) ? ($end + 86400 - $start) / 3600 : ($end - $start) / 3600;

            if ($diffHours > 24) {
                $errors[] = "Schedule " . ($index + 1) . ": Shift duration cannot exceed 24 hours";
            }
        }

        // Validate pay_rate
        if (empty($schedule['pay_rate'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Pay rate is required";
        } elseif (!is_numeric($schedule['pay_rate']) || floatval($schedule['pay_rate']) <= 0) {
            $errors[] = "Schedule " . ($index + 1) . ": Pay rate must be a positive number";
        }

        // Validate shift_type
        if (empty($schedule['shift_type'])) {
            $errors[] = "Schedule " . ($index + 1) . ": Shift type is required";
        } else {
            $validShiftTypes = ['day', 'evening', 'overnight'];
            if (!in_array($schedule['shift_type'], $validShiftTypes)) {
                $errors[] = "Schedule " . ($index + 1) . ": Invalid shift type";
            }
        }

        // Validate overnight_type (optional)
        if (!empty($schedule['overnight_type'])) {
            $validOvernightTypes = ['none', 'awake', 'rest'];
            if (!in_array($schedule['overnight_type'], $validOvernightTypes)) {
                $errors[] = "Schedule " . ($index + 1) . ": Invalid overnight type";
            }
        }

        return $errors;
    }

    public function logScheduleActivity($scheduleId, $staffId, $clientId, $scheduleDate, $isHoliday, $holidayRate)
    {
        try {
            // Get staff and client names
            //$staffName = $this->getStaffName($staffId);
            //$clientName = $this->getClientName($clientId);

            $activityDescription = "";

            if ($isHoliday && $holidayRate > 0) {
                $activityDescription .= " (Holiday: {$holidayRate}% premium)";
            }

            $query = "INSERT INTO recent_activities (activity_type, description, reference_id, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $activityType = 'schedule_created';
            $stmt->bind_param('ssi', $activityType, $activityDescription, $scheduleId);
            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            // Don't fail if logging fails, just log error
            error_log("Failed to log schedule activity: " . $e->getMessage());
        }
    }

    public function getStaffInfo($staffId)
    {
        $query = "SELECT * FROM staffs WHERE staff_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $staffId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row;
    }

    private function queueScheduleNotificationEmail($staffInfo, array $schedules)
    {
        try {
            $staffName = trim(($staffInfo['firstname'] ?? '') . ' ' . ($staffInfo['lastname'] ?? ''));
            $staffEmail = $staffInfo['email'] ?? '';
            if (!$staffEmail)
                return;

            $count = count($schedules);

            // Build one row per schedule
            $rows = '';
            foreach ($schedules as $i => $sched) {
                $bg = ($i % 2 === 0) ? '#ffffff' : '#f9fafb';
                $date = date('D, M j Y', strtotime($sched['date']));
                $startFmt = date('g:i A', strtotime($sched['start_time']));
                $endFmt = date('g:i A', strtotime($sched['end_time']));
                $shiftLabel = ucfirst($sched['shift_type']);
                $clientName = htmlspecialchars($sched['client_name']);
                $holiday = ($sched['is_holiday'] && !empty($sched['holiday_name']))
                    ? ' <span style="background:#fef3c7;color:#92400e;font-size:11px;padding:1px 6px;border-radius:10px;">'
                    . htmlspecialchars($sched['holiday_name']) . '</span>'
                    : '';

                $rows .= '<tr style="background:' . $bg . ';">'
                    . '<td style="padding:10px 14px;font-size:13px;color:#111827;border-bottom:1px solid #e5e7eb;">' . $clientName . '</td>'
                    . '<td style="padding:10px 14px;font-size:13px;color:#111827;border-bottom:1px solid #e5e7eb;">' . $date . $holiday . '</td>'
                    . '<td style="padding:10px 14px;font-size:13px;color:#111827;border-bottom:1px solid #e5e7eb;">' . $startFmt . ' &ndash; ' . $endFmt . '</td>'
                    . '<td style="padding:10px 14px;font-size:13px;color:#111827;border-bottom:1px solid #e5e7eb;">' . $shiftLabel . '</td>'
                    . '</tr>';
            }

            $plural = $count > 1 ? $count . ' new schedules have' : 'A new schedule has';
            $subject = $count > 1
                ? $count . ' New Schedules Assigned to You'
                : 'New Schedule Assigned — ' . date('D, M j', strtotime($schedules[0]['date']));

            $body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.08);">

      <tr>
        <td style="background:#003366;padding:28px 32px;">
          <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;">TAMEC Care Staffing Services</p>
          <p style="margin:4px 0 0;font-size:13px;color:#94a3b8;">Schedule Notification</p>
        </td>
      </tr>

      <tr>
        <td style="padding:32px 32px 24px;">
          <p style="margin:0 0 12px;font-size:15px;color:#111827;">Hi <strong>' . htmlspecialchars($staffName) . '</strong>,</p>
          <p style="margin:0 0 24px;font-size:14px;color:#374151;">' . $plural . ' been assigned to you. Please review the details below.</p>

          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:24px;">
            <tr style="background:#003366;">
              <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;">Client</td>
              <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;">Date</td>
              <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;">Time</td>
              <td style="padding:10px 14px;font-size:12px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.05em;">Shift</td>
            </tr>
            ' . $rows . '
          </table>

          <p style="margin:0;font-size:13px;color:#6b7280;">Questions? Contact us at <a href="mailto:info@tameccarestaffing.com" style="color:#003366;">info@tameccarestaffing.com</a>.</p>
        </td>
      </tr>

      <tr>
        <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:16px 32px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">TAMEC Care Staffing Services Ltd &bull; 3100 Steeles Ave W, Suite 403, Concord, ON L4K 3R1</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';

            $this->queueMail($staffEmail, $staffName, $subject, $body);

        } catch (Exception $e) {
            error_log("Failed to queue schedule notification email: " . $e->getMessage());
        }
    }

    private function queueMail($toEmail, $toName, $subject, $body)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mail_queue (to_email, to_name, subject, body, status, attempts, created_at)
             VALUES (?, ?, ?, ?, 'pending', 0, NOW())"
        );
        $stmt->bind_param('ssss', $toEmail, $toName, $subject, $body);
        $stmt->execute();
        $stmt->close();
    }

    private function queueWelcomeEmail($staff)
    {
        try {
            $firstName = htmlspecialchars($staff['firstname']);
            $fullName = htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']);
            $email = $staff['email'];
            $resetUrl = $this->getCurrentUrl() . '/forgot_password';
            $subject = 'Welcome to TAMEC Care Staffing Services — Set Up Your Password';

            $body = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Welcome to TAMEC</title>
  <!--[if mso]>
  <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
  <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

  <!-- Outer wrapper -->
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;min-width:320px;">
    <tr>
      <td align="center" style="padding:40px 16px;">

        <!-- Card -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- Header banner -->
          <tr>
            <td style="background-color:#003366;padding:36px 40px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td>
                    <p style="margin:0 0 4px 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">TAMEC Care Staffing</p>
                    <p style="margin:0;font-size:13px;color:#93c5fd;letter-spacing:0.3px;text-transform:uppercase;">Welcome Aboard</p>
                  </td>
                  <td align="right" valign="middle">
                    <!-- Decorative circle -->
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="width:52px;height:52px;background-color:rgba(255,255,255,0.12);border-radius:50%;text-align:center;vertical-align:middle;">
                          <span style="font-size:26px;line-height:52px;">&#128075;</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Accent bar -->
          <tr>
            <td style="background-color:#99cc33;height:4px;font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px 40px 32px;">

              <p style="margin:0 0 20px 0;font-size:16px;color:#111827;line-height:1.5;">
                Hi <strong>' . $firstName . '</strong>,
              </p>

              <p style="margin:0 0 20px 0;font-size:15px;color:#374151;line-height:1.7;">
                Welcome to <strong>TAMEC Care Staffing Services</strong>! Your staff account has been created by our admin team and you are now part of our caregiving network.
              </p>

              <!-- Info box -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f9ff;border-left:4px solid #003366;border-radius:0 8px 8px 0;margin-bottom:28px;">
                <tr>
                  <td style="padding:18px 20px;">
                    <p style="margin:0 0 6px 0;font-size:12px;font-weight:700;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">Your Login Email</p>
                    <p style="margin:0;font-size:15px;color:#111827;font-weight:600;">' . htmlspecialchars($email) . '</p>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 12px 0;font-size:15px;color:#374151;line-height:1.7;">
                For security, no password has been set on your account yet. Before you can log in, please click the button below to create your password.
              </p>

              <p style="margin:0 0 28px 0;font-size:14px;color:#6b7280;line-height:1.6;">
                You will be asked to enter the email address above and we will send you a reset link.
              </p>

              <!-- CTA Button -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
                <tr>
                  <td style="background-color:#003366;border-radius:8px;">
                    <!--[if mso]>
                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $resetUrl . '" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="10%" stroke="f" fillcolor="#003366">
                      <w:anchorlock/>
                      <center style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;">Set Up My Password</center>
                    </v:roundrect>
                    <![endif]-->
                    <!--[if !mso]><!-->
                    <a href="' . $resetUrl . '" target="_blank" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;font-family:Arial,Helvetica,sans-serif;mso-hide:all;">Set Up My Password</a>
                    <!--<![endif]-->
                  </td>
                </tr>
              </table>

              <!-- Divider -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="border-top:1px solid #e5e7eb;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
              </table>

              <!-- Steps -->
              <p style="margin:0 0 14px 0;font-size:13px;font-weight:700;color:#003366;text-transform:uppercase;letter-spacing:0.5px;">How it works</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                <tr>
                  <td valign="top" style="padding:0 12px 12px 0;width:28px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="width:24px;height:24px;background-color:#003366;border-radius:50%;text-align:center;vertical-align:middle;">
                          <span style="font-size:12px;font-weight:700;color:#ffffff;line-height:24px;">1</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <td valign="top" style="padding-bottom:12px;">
                    <p style="margin:0;font-size:14px;color:#374151;line-height:1.5;">Click <strong>Set Up My Password</strong> above or visit the forgot password page</p>
                  </td>
                </tr>
                <tr>
                  <td valign="top" style="padding:0 12px 12px 0;width:28px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="width:24px;height:24px;background-color:#003366;border-radius:50%;text-align:center;vertical-align:middle;">
                          <span style="font-size:12px;font-weight:700;color:#ffffff;line-height:24px;">2</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <td valign="top" style="padding-bottom:12px;">
                    <p style="margin:0;font-size:14px;color:#374151;line-height:1.5;">Enter your email address: <strong>' . htmlspecialchars($email) . '</strong></p>
                  </td>
                </tr>
                <tr>
                  <td valign="top" style="padding:0 12px 0 0;width:28px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="width:24px;height:24px;background-color:#003366;border-radius:50%;text-align:center;vertical-align:middle;">
                          <span style="font-size:12px;font-weight:700;color:#ffffff;line-height:24px;">3</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <td valign="top">
                    <p style="margin:0;font-size:14px;color:#374151;line-height:1.5;">Check your inbox for the reset link and create your password</p>
                  </td>
                </tr>
              </table>

              <!-- Fallback link -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f9fafb;border-radius:8px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <p style="margin:0 0 4px 0;font-size:12px;color:#9ca3af;">Button not working? Copy and paste this link into your browser:</p>
                    <p style="margin:0;font-size:12px;color:#003366;word-break:break-all;">' . $resetUrl . '</p>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td>
                    <p style="margin:0 0 4px 0;font-size:13px;font-weight:700;color:#374151;">TAMEC Care Staffing Services Ltd</p>
                    <p style="margin:0 0 8px 0;font-size:12px;color:#9ca3af;line-height:1.5;">3100 Steeles Avenue West, Suite 403<br>Concord, Ontario L4K 3R1</p>
                    <p style="margin:0;font-size:12px;color:#9ca3af;">
                      <a href="mailto:info@tameccarestaffing.com" style="color:#003366;text-decoration:none;">info@tameccarestaffing.com</a>
                    </p>
                  </td>
                  <td align="right" valign="top">
                    <p style="margin:0;font-size:11px;color:#d1d5db;line-height:1.5;">This email was sent to<br>' . htmlspecialchars($email) . '</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
        <!-- /Card -->

        <p style="margin:20px 0 0 0;font-size:12px;color:#9ca3af;text-align:center;">
          &copy; ' . date('Y') . ' TAMEC Care Staffing Services Ltd. All rights reserved.
        </p>

      </td>
    </tr>
  </table>
  <!-- /Outer wrapper -->

</body>
</html>';

            $this->queueMail($email, $fullName, $subject, $body);

        } catch (Exception $e) {
            error_log("Failed to queue welcome email: " . $e->getMessage());
        }
    }

    public function fetch_schedules_for_payroll($start_date, $end_date)
    {
        $query = "
            SELECT
                sc.schedule_id,
                sc.user_id,
                sc.client_id,
                sc.schedule_date,
                DATE_FORMAT(sc.start_time, '%h:%i %p') AS start_time_fmt,
                DATE_FORMAT(sc.end_time,   '%h:%i %p') AS end_time_fmt,
                sc.shift_type,
                sc.overnight_type,
                sc.pay_per_hour,
                sc.holiday_pay,
                sc.payroll_id,
                sc.payroll_status,
                sc.total_break_hours,
                sc.notes,
                CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
                st.role AS staff_role,
                CONCAT(cl.firstname, ' ', cl.lastname) AS client_name,
                CONCAT_WS(', ', cl.residential_city, cl.residential_province) AS client_location,
                ROUND(
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS hours_worked,
                ROUND(
                    CASE WHEN sc.holiday_pay > 0
                        THEN (sc.pay_per_hour * sc.holiday_pay / 100)
                        ELSE sc.pay_per_hour
                    END
                    *
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS estimated_amount
            FROM schedules sc
            JOIN staffs st ON sc.user_id = st.staff_id
            JOIN clients cl ON sc.client_id = cl.client_id
            WHERE sc.schedule_date BETWEEN ? AND ?
              AND sc.status = 'completed'
            ORDER BY st.firstname ASC, st.lastname ASC, sc.schedule_date ASC, sc.start_time ASC
        ";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $grouped = [];
        while ($row = $result->fetch_assoc()) {
            $sid = $row['user_id'];
            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'staff_id' => $sid,
                    'staff_name' => $row['staff_name'],
                    'staff_role' => $row['staff_role'],
                    'schedules' => [],
                    'total_count' => 0,
                    'pending_count' => 0,
                    'pending_hours' => 0,
                    'pending_amount' => 0,
                ];
            }
            $grouped[$sid]['schedules'][] = $row;
            $grouped[$sid]['total_count']++;
            if (!$row['payroll_id']) {
                $grouped[$sid]['pending_count']++;
                $grouped[$sid]['pending_hours'] = round($grouped[$sid]['pending_hours'] + (float) $row['hours_worked'], 2);
                $grouped[$sid]['pending_amount'] = round($grouped[$sid]['pending_amount'] + (float) $row['estimated_amount'], 2);
            }
        }
        $stmt->close();

        return ['status' => true, 'groups' => array_values($grouped)];
    }

    public function create_payroll_from_selection($data)
    {
        $period_start = $data['period_start'];
        $period_end = $data['period_end'];
        $schedule_ids = $data['schedule_ids'];
        $notes = !empty($data['notes']) ? $data['notes'] : null;

        if (empty($schedule_ids)) {
            return ['status' => false, 'message' => 'No schedules selected'];
        }

        $placeholders = implode(',', array_fill(0, count($schedule_ids), '?'));
        $types = str_repeat('i', count($schedule_ids));

        $stmt = $this->db->prepare("
            SELECT schedule_id, user_id, start_time, end_time, pay_per_hour, holiday_pay, payroll_status
            FROM schedules
            WHERE schedule_id IN ($placeholders)
        ");
        $stmt->bind_param($types, ...$schedule_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        $all_schedules = [];
        while ($row = $result->fetch_assoc()) {
            $all_schedules[] = $row;
        }
        $stmt->close();

        $valid_arr = [];
        foreach ($all_schedules as $s) {
            if ($s['payroll_status'] === 'pending') {
                $valid_arr[] = $s;
            }
        }

        if (empty($valid_arr)) {
            return ['status' => false, 'message' => 'All selected schedules are already assigned to a payroll'];
        }

        $unique_staff = [];
        $total_hours = 0;
        $total_amount = 0;

        foreach ($valid_arr as $s) {
            $unique_staff[$s['user_id']] = true;
            $hours = max(0, (strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600);
            $rate = $s['holiday_pay'] > 0 ? ($s['pay_per_hour'] * $s['holiday_pay'] / 100) : $s['pay_per_hour'];
            $total_hours += $hours;
            $total_amount += $hours * $rate;
        }

        $total_staff = count($unique_staff);
        $total_hours = round($total_hours, 2);
        $total_amount = round($total_amount, 2);

        $year = date('Y');
        $cnt = $this->db->query("SELECT COUNT(*) AS c FROM payrolls WHERE YEAR(created_at) = $year")->fetch_assoc()['c'] ?? 0;
        $payroll_number = 'PR-' . $year . '-' . str_pad($cnt + 1, 3, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO payrolls (payroll_number, period_start, period_end, total_staff, total_hours, total_amount, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)
        ");
        $stmt->bind_param('sssidds', $payroll_number, $period_start, $period_end, $total_staff, $total_hours, $total_amount, $notes);
        $stmt->execute();
        $payroll_id = $stmt->insert_id;
        $stmt->close();

        $valid_ids = array_column($valid_arr, 'schedule_id');
        $ph2 = implode(',', array_fill(0, count($valid_ids), '?'));
        $types2 = 'i' . str_repeat('i', count($valid_ids));
        $params2 = array_merge([$payroll_id], $valid_ids);
        $stmt = $this->db->prepare("UPDATE schedules SET payroll_id = ?, payroll_status = 'processed' WHERE schedule_id IN ($ph2)");
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $stmt->close();

        $skipped = count($schedule_ids) - count($valid_ids);

        return [
            'status' => true,
            'message' => "Payroll $payroll_number created with $total_staff staff and " . count($valid_ids) . " schedules" . ($skipped > 0 ? " ($skipped already processed skipped)" : ''),
            'payroll_id' => $payroll_id,
            'payroll_number' => $payroll_number
        ];
    }

    public function get_all_payrolls()
    {
        $query = "
            SELECT
                p.*,
                CONCAT(s.firstname, ' ', s.lastname) AS created_by_name
            FROM payrolls p
            LEFT JOIN staffs s ON p.created_by = s.staff_id
            ORDER BY p.created_at DESC
        ";
        $result = $this->db->query($query);
        if (!$result) {
            throw new Exception("Error fetching payrolls: " . $this->db->error);
        }
        $payrolls = [];
        while ($row = $result->fetch_assoc()) {
            $payrolls[] = $row;
        }
        return ['status' => true, 'payrolls' => $payrolls];
    }

    public function get_payroll_details($payroll_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, CONCAT(s.firstname, ' ', s.lastname) AS created_by_name
            FROM payrolls p
            LEFT JOIN staffs s ON p.created_by = s.staff_id
            WHERE p.payroll_id = ?
        ");
        $stmt->bind_param('i', $payroll_id);
        $stmt->execute();
        $payroll = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$payroll) {
            throw new Exception("Payroll not found");
        }

        $stmt = $this->db->prepare("
            SELECT
                sc.schedule_id,
                sc.schedule_date,
                DATE_FORMAT(sc.start_time, '%h:%i %p') AS start_time_fmt,
                DATE_FORMAT(sc.end_time,   '%h:%i %p') AS end_time_fmt,
                sc.pay_per_hour,
                sc.holiday_pay,
                sc.shift_type,
                CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
                CONCAT(cl.firstname, ' ', cl.lastname) AS client_name,
                ROUND(
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS hours_worked,
                ROUND(
                    CASE WHEN sc.holiday_pay > 0
                        THEN (sc.pay_per_hour * sc.holiday_pay / 100)
                        ELSE sc.pay_per_hour
                    END
                    *
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS amount
            FROM schedules sc
            JOIN staffs st ON sc.user_id = st.staff_id
            JOIN clients cl ON sc.client_id = cl.client_id
            WHERE sc.payroll_id = ?
            ORDER BY sc.schedule_date ASC, st.firstname ASC
        ");
        $stmt->bind_param('i', $payroll_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        $stmt->close();

        return ['status' => true, 'payroll' => $payroll, 'schedules' => $schedules];
    }

    public function generate_payroll($data)
    {
        $period_start = $data['period_start'];
        $period_end = $data['period_end'];
        $notes = !empty($data['notes']) ? $data['notes'] : null;

        $stmt = $this->db->prepare("
            SELECT schedule_id, user_id, start_time, end_time, pay_per_hour, holiday_pay
            FROM schedules
            WHERE payroll_status = 'pending'
              AND status = 'completed'
              AND schedule_date BETWEEN ? AND ?
        ");
        $stmt->bind_param('ss', $period_start, $period_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        $stmt->close();

        if (empty($schedules)) {
            return ['status' => false, 'message' => 'No pending schedules found in the selected period'];
        }

        $total_staff = count(array_unique(array_column($schedules, 'user_id')));
        $total_hours = 0;
        $total_amount = 0;

        foreach ($schedules as $s) {
            $hours = max(0, (strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600);
            $rate = $s['holiday_pay'] > 0 ? ($s['pay_per_hour'] * $s['holiday_pay'] / 100) : $s['pay_per_hour'];
            $total_hours += $hours;
            $total_amount += $hours * $rate;
        }

        $total_hours = round($total_hours, 2);
        $total_amount = round($total_amount, 2);

        $year = date('Y');
        $count_result = $this->db->query("SELECT COUNT(*) AS cnt FROM payrolls WHERE YEAR(created_at) = $year");
        $next_num = ($count_result->fetch_assoc()['cnt'] ?? 0) + 1;
        $payroll_number = 'PR-' . $year . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO payrolls (payroll_number, period_start, period_end, total_staff, total_hours, total_amount, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)
        ");
        $stmt->bind_param('sssidds', $payroll_number, $period_start, $period_end, $total_staff, $total_hours, $total_amount, $notes);
        $stmt->execute();
        $payroll_id = $stmt->insert_id;
        $stmt->close();

        $schedule_ids = array_column($schedules, 'schedule_id');
        $placeholders = implode(',', array_fill(0, count($schedule_ids), '?'));
        $types = 'i' . str_repeat('i', count($schedule_ids));
        $params = array_merge([$payroll_id], $schedule_ids);
        $stmt = $this->db->prepare("UPDATE schedules SET payroll_id = ?, payroll_status = 'processed' WHERE schedule_id IN ($placeholders)");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        return [
            'status' => true,
            'message' => "Payroll $payroll_number generated successfully ($total_staff staff, " . count($schedules) . " schedules)",
            'payroll_id' => $payroll_id,
            'payroll_number' => $payroll_number
        ];
    }

    public function delete_payroll($payroll_id)
    {
        $stmt = $this->db->prepare("UPDATE schedules SET payroll_id = NULL, payroll_status = 'pending' WHERE payroll_id = ?");
        $stmt->bind_param('i', $payroll_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->db->prepare("DELETE FROM payrolls WHERE payroll_id = ?");
        $stmt->bind_param('i', $payroll_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            return ['status' => false, 'message' => 'Payroll not found'];
        }

        return ['status' => true, 'message' => 'Payroll deleted and schedules reset to pending'];
    }

    // ─── Invoice Creation Methods ─────────────────────────────────────────────

    public function fetch_schedules_for_invoice($client_id, $start_date, $end_date)
    {
        $query = "
            SELECT
                sc.schedule_id,
                sc.user_id,
                sc.schedule_date,
                DATE_FORMAT(sc.start_time, '%h:%i %p') AS start_time_fmt,
                DATE_FORMAT(sc.end_time,   '%h:%i %p') AS end_time_fmt,
                sc.shift_type,
                sc.overnight_type,
                sc.invoice_id,
                sc.invoice_status,
                sc.holiday_pay,
                sc.total_break_hours,
                sc.notes,
                COALESCE(cl.bill_rate, 0) AS bill_rate,
                COALESCE(cl.bill_rate_rest, 0) AS bill_rate_rest,
                CONCAT(st.firstname, ' ', st.lastname) AS staff_name,
                st.role AS staff_role,
                CONCAT(cl.firstname, ' ', cl.lastname) AS client_name,
                CONCAT_WS(', ', cl.residential_city, cl.residential_province) AS client_location,
                ROUND(
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS hours_worked,
                ROUND(
                    CASE WHEN sc.holiday_pay > 0
                        THEN (
                            CASE WHEN sc.overnight_type = 'rest'
                                THEN COALESCE(cl.bill_rate_rest, cl.bill_rate, 0)
                                ELSE COALESCE(cl.bill_rate, 0)
                            END * sc.holiday_pay / 100
                        )
                        ELSE
                            CASE WHEN sc.overnight_type = 'rest'
                                THEN COALESCE(cl.bill_rate_rest, cl.bill_rate, 0)
                                ELSE COALESCE(cl.bill_rate, 0)
                            END
                    END
                    *
                    GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                ) AS estimated_amount
            FROM schedules sc
            JOIN staffs st  ON sc.user_id    = st.staff_id
            JOIN clients cl ON sc.client_id  = cl.client_id
            WHERE sc.client_id    = ?
              AND sc.schedule_date BETWEEN ? AND ?
              AND sc.status = 'completed'
            ORDER BY st.firstname ASC, st.lastname ASC, sc.schedule_date ASC, sc.start_time ASC
        ";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        $stmt->bind_param('iss', $client_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $grouped = [];
        while ($row = $result->fetch_assoc()) {
            $sid = $row['user_id'];
            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'staff_id' => $sid,
                    'staff_name' => $row['staff_name'],
                    'staff_role' => $row['staff_role'],
                    'schedules' => [],
                    'total_count' => 0,
                    'pending_count' => 0,
                    'pending_hours' => 0,
                    'pending_amount' => 0,
                ];
            }
            $grouped[$sid]['schedules'][] = $row;
            $grouped[$sid]['total_count']++;
            if (!$row['invoice_id']) {
                $grouped[$sid]['pending_count']++;
                $grouped[$sid]['pending_hours'] = round($grouped[$sid]['pending_hours'] + (float) $row['hours_worked'], 2);
                $grouped[$sid]['pending_amount'] = round($grouped[$sid]['pending_amount'] + (float) $row['estimated_amount'], 2);
            }
        }
        $stmt->close();

        return ['status' => true, 'groups' => array_values($grouped)];
    }

    public function create_invoice_from_selection($data)
    {
        $client_id = (int) $data['client_id'];
        $period_start = $data['period_start'];
        $period_end = $data['period_end'];
        $schedule_ids = $data['schedule_ids'];
        $notes = !empty($data['notes']) ? $data['notes'] : null;

        if (empty($schedule_ids)) {
            return ['status' => false, 'message' => 'No schedules selected'];
        }

        // Fetch client bill_rate
        $stmt = $this->db->prepare("SELECT bill_rate, bill_rate_rest FROM clients WHERE client_id = ?");
        $stmt->bind_param('i', $client_id);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$client) {
            return ['status' => false, 'message' => 'Client not found'];
        }
        $bill_rate = (float) ($client['bill_rate'] ?? 0);
        $bill_rate_rest = (float) ($client['bill_rate_rest'] ?? 0);

        // Fetch selected schedules
        $placeholders = implode(',', array_fill(0, count($schedule_ids), '?'));
        $types = str_repeat('i', count($schedule_ids));

        $stmt = $this->db->prepare("
            SELECT schedule_id, user_id, start_time, end_time, holiday_pay, invoice_status, overnight_type
            FROM schedules
            WHERE schedule_id IN ($placeholders) AND client_id = ?
        ");
        $bound = array_merge($schedule_ids, [$client_id]);
        $stmt->bind_param($types . 'i', ...$bound);
        $stmt->execute();
        $res = $stmt->get_result();
        $all_schedules = [];
        while ($row = $res->fetch_assoc()) {
            $all_schedules[] = $row;
        }
        $stmt->close();

        // Keep only pending ones
        $valid_arr = array_filter($all_schedules, fn($s) => $s['invoice_status'] === 'pending');
        $valid_arr = array_values($valid_arr);

        if (empty($valid_arr)) {
            return ['status' => false, 'message' => 'All selected schedules are already assigned to an invoice'];
        }

        $unique_staff = [];
        $total_hours = 0;
        $subtotal = 0;

        foreach ($valid_arr as $s) {
            $unique_staff[$s['user_id']] = true;
            $hours = max(0, (strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600);
            $rate = $s['holiday_pay'] > 0 ? ($s['overnight_type'] === 'rest' ? $bill_rate_rest : $bill_rate) * $s['holiday_pay'] / 100 : ($s['overnight_type'] === 'rest' ? $bill_rate_rest : $bill_rate);
            $total_hours += $hours;
            $subtotal += $hours * $rate;
        }

        $total_staff = count($unique_staff);
        $total_hours = round($total_hours, 2);
        $subtotal = ceil($subtotal);
        $tax_rate = 13.00;
        $tax_amount = ceil($subtotal * $tax_rate / 100);
        $expense = !empty($data['expense']) ? round(floatval($data['expense']), 2) : 0;
        $total_amount = ceil($subtotal + $tax_amount + $expense);

        $year = date('Y');
        $cnt = $this->db->query("SELECT COUNT(*) AS c FROM invoices WHERE YEAR(created_at) = $year")->fetch_assoc()['c'] ?? 0;
        $invoice_number = 'INV-' . $year . '-' . str_pad($cnt + 1, 3, '0', STR_PAD_LEFT);
        $invoice_date = date('Y-m-d');
        $due_date = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+30 days'));

        $stmt = $this->db->prepare("
            INSERT INTO invoices
                (invoice_number, client_id, period_start, period_end, invoice_date, due_date,
                 total_staff, total_hours, subtotal, tax_rate, tax_amount, discount, expense, total_amount, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 'generated', ?)
        ");
        $stmt->bind_param(
            'sissssiiddddds',
            $invoice_number,
            $client_id,
            $period_start,
            $period_end,
            $invoice_date,
            $due_date,
            $total_staff,
            $total_hours,
            $subtotal,
            $tax_rate,
            $tax_amount,
            $expense,
            $total_amount,
            $notes
        );
        $stmt->execute();
        $invoice_id = $stmt->insert_id;
        $stmt->close();

        $valid_ids = array_column($valid_arr, 'schedule_id');
        $ph2 = implode(',', array_fill(0, count($valid_ids), '?'));
        $types2 = 'i' . str_repeat('i', count($valid_ids));
        $params2 = array_merge([$invoice_id], $valid_ids);

        $stmt = $this->db->prepare("UPDATE schedules SET invoice_id = ?, invoice_status = 'processed', invoice_processed_at = NOW() WHERE schedule_id IN ($ph2)");
        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $stmt->close();

        $skipped = count($schedule_ids) - count($valid_ids);

        return [
            'status' => true,
            'message' => "Invoice $invoice_number created with $total_staff staff and " . count($valid_ids) . " schedules" . ($skipped > 0 ? " ($skipped already invoiced skipped)" : ''),
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number
        ];
    }

    // ─── Invoice Methods ──────────────────────────────────────────────────────

    private function calculateOverdueCharge($invoice) {
        if (in_array($invoice['status'], ['paid', 'cancelled'])) {
            return ['overdue_rate' => 0, 'overdue_charge' => 0];
        }

        $tz   = new DateTimeZone('America/Toronto');
        $inv  = new DateTime($invoice['invoice_date'], $tz);
        $now  = new DateTime('now', $tz);
        $days = (int) $now->diff($inv)->days;

        $rate = 0;
        if ($days >= 41) {
            $rate = 10;
        } elseif ($days >= 36) {
            $rate = 5;
        } elseif ($days >= 31) {
            $rate = 2;
        }

        $charge = $rate > 0 ? round($invoice['total_amount'] * $rate / 100, 2) : 0;

        return ['overdue_rate' => $rate, 'overdue_charge' => $charge];
    }

    public function get_all_invoices()
    {
        $sql = "SELECT i.invoice_id, i.invoice_number, i.invoice_date, i.period_start, i.period_end,
                       i.due_date, i.total_staff, i.total_hours, i.subtotal, i.tax_rate, i.tax_amount,
                       i.discount, i.total_amount, i.status, i.po_number, i.notes,
                       i.sent_at, i.paid_at, i.created_at,
                       CONCAT(c.firstname, ' ', c.lastname) AS client_name,
                       COALESCE(c.billing_email, c.email) AS client_email
                FROM invoices i
                LEFT JOIN clients c ON i.client_id = c.client_id
                ORDER BY i.created_at DESC";

        $result = $this->db->query($sql);
        if (!$result) {
            return ['status' => false, 'message' => 'Query failed: ' . $this->db->error, 'invoices' => []];
        }

        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $overdue = $this->calculateOverdueCharge($row);
            $row['overdue_rate']   = $overdue['overdue_rate'];
            $row['overdue_charge'] = $overdue['overdue_charge'];
            $invoices[] = $row;
        }
        return ['status' => true, 'invoices' => $invoices];
    }

    public function get_invoice_details($invoice_id)
    {
        $stmt = $this->db->prepare(
            "SELECT i.*,
                    CONCAT(c.firstname, ' ', c.lastname) AS client_name,
                    COALESCE(c.billing_email, c.email) AS client_email,
                    c.billing_name, c.billing_address, c.billing_city,
                    c.billing_province, c.billing_postal_code, c.billing_country,
                    c.residential_address, c.residential_city, c.residential_province
             FROM invoices i
             LEFT JOIN clients c ON i.client_id = c.client_id
             WHERE i.invoice_id = ?"
        );
        $stmt->bind_param('i', $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$invoice) {
            return ['status' => false, 'message' => 'Invoice not found'];
        }

        $stmt2 = $this->db->prepare(
            "SELECT sc.schedule_id, sc.schedule_date, sc.shift_type, sc.overnight_type, sc.holiday_pay, sc.status,
                    DATE_FORMAT(sc.start_time,  '%h:%i %p') AS start_time_fmt,
                    DATE_FORMAT(sc.end_time,    '%h:%i %p') AS end_time_fmt,
                    DATE_FORMAT(sc.start_time,  '%l:%i%p')  AS start_short,
                    DATE_FORMAT(sc.end_time,    '%l:%i%p')  AS end_short,
                    DATE_FORMAT(sc.schedule_date, '%a')     AS day_name,
                    CONCAT(st.firstname, ' ', st.lastname)  AS staff_name,
                    CONCAT(st.lastname, ', ', st.firstname) AS staff_name_last,
                    cl.bill_rate,
                    cl.bill_rate_rest,
                    ROUND(
                        GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                    ) AS hours_worked,
                    ROUND(
                        CASE WHEN sc.holiday_pay > 0
                            THEN (
                                CASE WHEN sc.overnight_type = 'rest'
                                    THEN COALESCE(cl.bill_rate_rest, cl.bill_rate)
                                    ELSE cl.bill_rate
                                END * sc.holiday_pay / 100
                            )
                            ELSE
                                CASE WHEN sc.overnight_type = 'rest'
                                    THEN COALESCE(cl.bill_rate_rest, cl.bill_rate)
                                    ELSE cl.bill_rate
                                END
                        END
                        *
                        GREATEST(TIMESTAMPDIFF(MINUTE, sc.start_time, sc.end_time) / 60, 0), 2
                    ) AS amount
             FROM schedules sc
             LEFT JOIN staffs st ON sc.user_id = st.staff_id
             LEFT JOIN invoices invi ON sc.invoice_id = invi.invoice_id
             LEFT JOIN clients cl ON invi.client_id = cl.client_id
             WHERE sc.invoice_id = ?
             ORDER BY sc.schedule_date ASC, sc.start_time ASC"
        );
        $stmt2->bind_param('i', $invoice_id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $schedules = [];
        while ($row = $res->fetch_assoc()) {
            $schedules[] = $row;
        }
        $stmt2->close();

        // Fetch outstanding unpaid invoices for same client (excluding this invoice)
        $outstanding = [];
        if (!empty($invoice['client_id'])) {
            $client_id_ref = (int) $invoice['client_id'];
            $stmt3 = $this->db->prepare(
                "SELECT invoice_id, invoice_number, invoice_date, due_date, total_amount, status
                 FROM invoices
                 WHERE client_id = ? AND status NOT IN ('paid','cancelled') AND invoice_id != ?
                 ORDER BY invoice_date ASC"
            );
            $stmt3->bind_param('ii', $client_id_ref, $invoice_id);
            $stmt3->execute();
            $res3 = $stmt3->get_result();
            while ($row = $res3->fetch_assoc()) {
                $outstanding[] = $row;
            }
            $stmt3->close();
        }

        // Calculate overdue charge
        $overdue = $this->calculateOverdueCharge($invoice);
        $invoice['overdue_rate']   = $overdue['overdue_rate'];
        $invoice['overdue_charge'] = $overdue['overdue_charge'];

        return ['status' => true, 'invoice' => $invoice, 'schedules' => $schedules, 'outstanding' => $outstanding];
    }

    public function delete_invoice($invoice_id)
    {
        $stmt = $this->db->prepare("UPDATE schedules SET invoice_id = NULL, invoice_status = 'pending' WHERE invoice_id = ?");
        $stmt->bind_param('i', $invoice_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->db->prepare("DELETE FROM invoices WHERE invoice_id = ?");
        $stmt->bind_param('i', $invoice_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            return ['status' => false, 'message' => 'Invoice not found'];
        }
        return ['status' => true, 'message' => 'Invoice deleted and schedules reset to pending'];
    }

    public function update_invoice_status($invoice_id, $new_status)
    {
        $allowed = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        if (!in_array($new_status, $allowed)) {
            return ['status' => false, 'message' => 'Invalid status value'];
        }

        $stmt = $this->db->prepare(
            "UPDATE invoices SET status = ?,
                sent_at = CASE WHEN ? = 'sent' AND sent_at IS NULL THEN NOW() ELSE sent_at END,
                paid_at = CASE WHEN ? = 'paid' AND paid_at IS NULL THEN NOW() ELSE paid_at END
             WHERE invoice_id = ?"
        );
        $stmt->bind_param('sssi', $new_status, $new_status, $new_status, $invoice_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            return ['status' => false, 'message' => 'Invoice not found or status unchanged'];
        }
        return ['status' => true, 'message' => 'Status updated to ' . ucfirst($new_status)];
    }

    public function send_invoice_email($invoice_id)
    {
        $data = $this->get_invoice_details($invoice_id);
        if (!$data['status']) {
            return $data;
        }

        $inv = $data['invoice'];
        $scheds = $data['schedules'];

        $recipient_email = $inv['client_email'];
        $recipient_name = $inv['client_name'];

        if (empty($recipient_email)) {
            return ['status' => false, 'message' => 'No email address on file for this client'];
        }

        // Build schedule rows HTML
        $rows_html = '';
        foreach ($scheds as $s) {
            $holiday = floatval($s['holiday_pay']) > 0
                ? '<span style="color:#c2410c;font-size:11px;margin-left:4px;">Holiday Pay</span>'
                : '';
            $rows_html .= '
                <tr>
                    <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;">' . htmlspecialchars($s['staff_name']) . '</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;">' . date('M j, Y', strtotime($s['schedule_date'])) . '</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;text-transform:capitalize;">' . htmlspecialchars($s['shift_type']) . $holiday . '</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;text-align:right;">' . number_format($s['hours_worked'], 2) . ' hrs</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:600;">$' . number_format($s['amount'], 2) . '</td>
                </tr>';
        }

        // Build billing address
        $billing_parts = array_filter([
            $inv['billing_address'] ?? $inv['residential_address'],
            $inv['billing_city'] ?? $inv['residential_city'],
            $inv['billing_province'] ?? $inv['residential_province'],
        ]);
        $billing_addr = implode(', ', $billing_parts);

        $body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f4f4f4;">
  <div style="max-width:700px;margin:30px auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <div style="background:#003366;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:1px;">TAMEC</h1>
        <p style="margin:4px 0 0;color:#99CC33;font-size:13px;font-weight:600;">INVOICE</p>
      </div>
      <div style="text-align:right;">
        <p style="margin:0;color:#ffffff;font-size:18px;font-weight:700;">' . htmlspecialchars($inv['invoice_number']) . '</p>
        <p style="margin:4px 0 0;color:#99CC33;font-size:12px;">Date: ' . date('M j, Y', strtotime($inv['invoice_date'])) . '</p>
        <p style="margin:2px 0 0;color:#99CC33;font-size:12px;">Due: ' . date('M j, Y', strtotime($inv['due_date'])) . '</p>
      </div>
    </div>
    <!-- Bill To -->
    <div style="padding:24px 32px;border-bottom:1px solid #e5e7eb;">
      <p style="margin:0 0 4px;font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Bill To</p>
      <p style="margin:0;font-size:15px;font-weight:700;color:#111827;">' . htmlspecialchars($recipient_name) . '</p>
      <p style="margin:4px 0 0;font-size:13px;color:#6b7280;">' . htmlspecialchars($billing_addr) . '</p>
      <p style="margin:2px 0 0;font-size:13px;color:#6b7280;">' . htmlspecialchars($recipient_email) . '</p>
      <div style="margin-top:12px;font-size:12px;color:#6b7280;">
        Period: <strong>' . date('M j, Y', strtotime($inv['period_start'])) . ' – ' . date('M j, Y', strtotime($inv['period_end'])) . '</strong>
        &nbsp;|&nbsp; Staff Visits: <strong>' . intval($inv['total_staff']) . '</strong>
        ' . (!empty($inv['po_number']) ? '&nbsp;|&nbsp; PO: <strong>' . htmlspecialchars($inv['po_number']) . '</strong>' : '') . '
      </div>
    </div>
    <!-- Services Table -->
    <div style="padding:24px 32px;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:#f9fafb;">
            <th style="padding:10px 12px;text-align:left;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;border-bottom:2px solid #e5e7eb;">Staff</th>
            <th style="padding:10px 12px;text-align:left;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;border-bottom:2px solid #e5e7eb;">Date</th>
            <th style="padding:10px 12px;text-align:left;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;border-bottom:2px solid #e5e7eb;">Shift</th>
            <th style="padding:10px 12px;text-align:right;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;border-bottom:2px solid #e5e7eb;">Hours</th>
            <th style="padding:10px 12px;text-align:right;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;border-bottom:2px solid #e5e7eb;">Amount</th>
          </tr>
        </thead>
        <tbody>' . $rows_html . '</tbody>
      </table>
    </div>
    <!-- Totals -->
    <div style="padding:0 32px 24px;display:flex;justify-content:flex-end;">
      <table style="font-size:13px;min-width:260px;">
        <tr>
          <td style="padding:5px 16px 5px 0;color:#6b7280;">Subtotal</td>
          <td style="padding:5px 0;text-align:right;font-weight:600;">$' . number_format(floatval($inv['subtotal']), 2) . '</td>
        </tr>
        ' . (floatval($inv['tax_amount']) > 0 ? '
        <tr>
          <td style="padding:5px 16px 5px 0;color:#6b7280;">Tax (' . number_format(floatval($inv['tax_rate']), 0) . '%)</td>
          <td style="padding:5px 0;text-align:right;font-weight:600;">$' . number_format(floatval($inv['tax_amount']), 2) . '</td>
        </tr>' : '') . '
        ' . (floatval($inv['discount']) > 0 ? '
        <tr>
          <td style="padding:5px 16px 5px 0;color:#6b7280;">Discount</td>
          <td style="padding:5px 0;text-align:right;font-weight:600;color:#15803d;">-$' . number_format(floatval($inv['discount']), 2) . '</td>
        </tr>' : '') . '
        <tr style="border-top:2px solid #e5e7eb;">
          <td style="padding:10px 16px 5px 0;font-size:15px;font-weight:700;color:#003366;">Total Due</td>
          <td style="padding:10px 0 5px;text-align:right;font-size:15px;font-weight:700;color:#003366;">$' . number_format(floatval($inv['total_amount']), 2) . '</td>
        </tr>
      </table>
    </div>
    ' . (!empty($inv['notes']) ? '
    <div style="padding:0 32px 24px;">
      <p style="margin:0 0 4px;font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Notes</p>
      <p style="margin:0;font-size:13px;color:#6b7280;">' . nl2br(htmlspecialchars($inv['notes'])) . '</p>
    </div>' : '') . '
    <!-- Footer -->
    <div style="background:#f9fafb;padding:16px 32px;border-top:1px solid #e5e7eb;text-align:center;">
      <p style="margin:0;font-size:12px;color:#9ca3af;">Thank you for your business. Please remit payment by ' . date('M j, Y', strtotime($inv['due_date'])) . '.</p>
    </div>
  </div>
</body>
</html>';

        $subject = 'Invoice ' . $inv['invoice_number'] . ' from TAMEC';
        $mail_result = $this->sendmail($recipient_email, $recipient_name, $body, $subject);

        if ($mail_result['status']) {
            // Mark as sent
            $this->update_invoice_status($invoice_id, 'sent');
            return ['status' => true, 'message' => 'Invoice emailed to ' . $recipient_email];
        }

        return ['status' => false, 'message' => 'Failed to send email: ' . ($mail_result['error'] ?? $mail_result['message'])];
    }

    public function update_payroll_status($payroll_id, $new_status)
    {
        $allowed = ['draft', 'processed', 'paid', 'cancelled'];
        if (!in_array($new_status, $allowed)) {
            return ['status' => false, 'message' => 'Invalid status value'];
        }

        $stmt = $this->db->prepare("UPDATE payrolls SET status = ? WHERE payroll_id = ?");
        $stmt->bind_param('si', $new_status, $payroll_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            return ['status' => false, 'message' => 'Payroll not found or status unchanged'];
        }

        return ['status' => true, 'message' => 'Status updated to ' . ucfirst($new_status)];
    }

    // ─── Documents ────────────────────────────────────────────────────────────

    public function get_all_documents()
    {
        try {
            $result = $this->db->query("SELECT * FROM documents ORDER BY doc_name ASC");
            if (!$result)
                throw new Exception($this->db->error);
            $docs = [];
            while ($row = $result->fetch_assoc()) {
                $row['doc_id'] = (int) $row['doc_id'];
                $row['optional'] = (bool) $row['optional'];
                $docs[] = $row;
            }
            $result->free();
            return ['status' => true, 'documents' => $docs];
        } catch (Exception $e) {
            error_log("get_all_documents error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage(), 'documents' => []];
        }
    }

    public function create_document($data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO documents (doc_name, doc_tag, optional) VALUES (?, ?, ?)");
            $optional = $data['optional'] ? 1 : 0;
            $stmt->bind_param('ssi', $data['doc_name'], $data['doc_tag'], $optional);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            return ['status' => true, 'message' => 'Document created successfully', 'doc_id' => $stmt->insert_id];
        } catch (Exception $e) {
            error_log("create_document error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_document($data)
    {
        try {
            $stmt = $this->db->prepare("UPDATE documents SET doc_name = ?, doc_tag = ?, optional = ? WHERE doc_id = ?");
            $optional = $data['optional'] ? 1 : 0;
            $stmt->bind_param('ssii', $data['doc_name'], $data['doc_tag'], $optional, $data['doc_id']);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            return ['status' => true, 'message' => 'Document updated successfully'];
        } catch (Exception $e) {
            error_log("update_document error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete_document($doc_id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM documents WHERE doc_id = ?");
            $stmt->bind_param('i', $doc_id);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            return ['status' => true, 'message' => 'Document deleted successfully'];
        } catch (Exception $e) {
            error_log("delete_document error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── User Documents ───────────────────────────────────────────────────────

    public function get_user_documents($staff_id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ud.*, d.doc_name, d.optional
                FROM user_documents ud
                JOIN documents d ON d.doc_id = ud.doc_id
                WHERE ud.staff_id = ?
            ");
            $stmt->bind_param('i', $staff_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $docs = [];
            while ($row = $result->fetch_assoc()) {
                $row['user_doc_id'] = (int) $row['user_doc_id'];
                $row['staff_id'] = (int) $row['staff_id'];
                $row['doc_id'] = (int) $row['doc_id'];
                $row['optional'] = (bool) $row['optional'];
                $docs[] = $row;
            }
            return ['status' => true, 'user_documents' => $docs];
        } catch (Exception $e) {
            error_log("get_user_documents error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage(), 'user_documents' => []];
        }
    }

    public function save_user_document($staff_id, $doc_id, $doc_tag, $file_path, $original_name)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_documents (staff_id, doc_id, doc_tag, file_path, original_name)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), original_name = VALUES(original_name), updated_at = NOW()
            ");
            $stmt->bind_param('iisss', $staff_id, $doc_id, $doc_tag, $file_path, $original_name);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            return ['status' => true];
        } catch (Exception $e) {
            error_log("save_user_document error: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Log an activity to the recent_activities table
     */
    public function logActivity($activity_type, $title, $description = null, $target_type = null)
    {
        try {
            $user_id = (int) ($_SESSION['tamec_id'] ?? 0);
            $user_name = $_SESSION['tamec_name'] ?? 'System';
            $user_role = $_SESSION['tamec_role'] ?? 'admin';
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $device_type = 'desktop';
            if ($ua) {
                if (preg_match('/iPad/i', $ua)) {
                    $device_type = 'tablet';
                } elseif (preg_match('/Mobile|Android|iPhone/i', $ua)) {
                    $device_type = 'mobile';
                }
            }

            if ($user_id <= 0)
                return;

            $stmt = $this->db->prepare("
                INSERT INTO recent_activities
                    (user_id, user_name, user_role, activity_type, activity_title, activity_description, target_type, ip_address, user_agent, device_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'isssssssss',
                $user_id,
                $user_name,
                $user_role,
                $activity_type,
                $title,
                $description,
                $target_type,
                $ip,
                $ua,
                $device_type
            );
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("logActivity error: " . $e->getMessage());
        }
    }

    /**
     * Fetch all activities with optional filters and pagination
     */
    public function fetch_all_activities_paginated($page = 1, $per_page = 20, $type_filter = null, $date_from = null, $date_to = null, $search = null)
    {
        try {
            $offset = ($page - 1) * $per_page;
            $conditions = [];
            $params = [];
            $types = '';

            if ($type_filter) {
                $conditions[] = 'activity_type = ?';
                $params[] = $type_filter;
                $types .= 's';
            }
            if ($date_from) {
                $conditions[] = 'DATE(created_at) >= ?';
                $params[] = $date_from;
                $types .= 's';
            }
            if ($date_to) {
                $conditions[] = 'DATE(created_at) <= ?';
                $params[] = $date_to;
                $types .= 's';
            }
            if ($search) {
                $conditions[] = '(activity_title LIKE ? OR activity_description LIKE ? OR user_name LIKE ?)';
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $types .= 'sss';
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) AS total FROM recent_activities $where");
            if ($types)
                $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $total = (int) $countStmt->get_result()->fetch_assoc()['total'];
            $countStmt->close();

            $dataStmt = $this->db->prepare("
                SELECT activity_id, user_name, user_role, activity_type, activity_title,
                       activity_description, target_type, ip_address, device_type, created_at
                FROM recent_activities
                $where
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");

            $dataParams = $params;
            $dataParams[] = $per_page;
            $dataParams[] = $offset;
            $dataStmt->bind_param($types . 'ii', ...$dataParams);
            $dataStmt->execute();
            $result = $dataStmt->get_result();

            $activities = [];
            while ($row = $result->fetch_assoc()) {
                $iconData = $this->getActivityIcon($row['activity_type']);
                $row['time_ago'] = $this->timeAgo($row['created_at']);
                $row['icon'] = $iconData['icon'];
                $row['icon_bg'] = $iconData['icon_bg'];
                $row['icon_color'] = $iconData['icon_color'];
                $row['date_formatted'] = date('M j, Y g:i A', strtotime($row['created_at']));
                $activities[] = $row;
            }
            $dataStmt->close();

            return [
                'success' => true,
                'activities' => $activities,
                'total' => $total,
                'page' => (int) $page,
                'per_page' => (int) $per_page,
                'total_pages' => (int) ceil($total / max(1, $per_page))
            ];

        } catch (Exception $e) {
            error_log("fetch_all_activities_paginated error: " . $e->getMessage());
            return ['success' => false, 'activities' => [], 'total' => 0, 'total_pages' => 0, 'page' => 1];
        }
    }
}
