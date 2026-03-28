<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Create Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #99CC33;
            border-radius: 5px;
        }
        
        /* Sidebar */
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.open {
            transform: translateX(0);
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Schedule card */
        .schedule-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
            position: relative;
        }
        
        .schedule-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .schedule-card.holiday-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fff9e6 100%);
            border-left: 4px solid #f59e0b;
        }
        
        .remove-schedule {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            cursor: pointer;
            color: #ef4444;
            transition: all 0.2s;
            z-index: 10;
        }
        
        .remove-schedule:hover {
            transform: scale(1.1);
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.1);
        }
        
        /* Holiday styling */
        .holiday-badge {
            display: inline-block;
            background: #f59e0b;
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            margin-left: 0.5rem;
        }
        
        .holiday-notice {
            background: #fef3c7;
            border-radius: 0.5rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #92400e;
        }
        
        .holiday-icon {
            color: #f59e0b;
            margin-right: 0.25rem;
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #99CC33;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background-color: #99CC33;
            color: white;
            border-radius: 0.5rem;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background-color: #ef4444;
        }
        
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #99CC33;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #88BB22;
        }
        
        .btn-secondary {
            background: white;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #f3f4f6;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Number input spinner */
        .number-input {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .number-input input {
            width: 80px;
            text-align: center;
        }
        
        /* Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .confirmation-modal.active {
            display: flex;
        }
        
        .confirmation-modal-content {
            background: white;
            border-radius: 1rem;
            max-width: 450px;
            width: 90%;
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .confirmation-modal-header {
            padding: 1.5rem 1.5rem 0.5rem 1.5rem;
            text-align: center;
        }
        
        .confirmation-modal-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .confirmation-modal-icon.warning {
            background: #fff3e0;
        }
        
        .confirmation-modal-icon.warning i {
            color: #f97316;
        }
        
        .confirmation-modal-icon.success {
            background: #e6f7e6;
        }
        
        .confirmation-modal-icon.success i {
            color: #99CC33;
        }
        
        .confirmation-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-modal-message {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
            padding: 0 1.5rem;
            text-align: center;
        }
        
        .confirmation-modal-actions {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            margin-top: 1rem;
        }
        
        .confirmation-modal-actions button {
            flex: 1;
            padding: 0.625rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel {
            background: white;
            border: 1px solid #d1d5db;
        }
        
        .btn-cancel:hover {
            background: #f3f4f6;
        }
        
        .btn-confirm {
            background: #99CC33;
            color: white;
            border: none;
        }
        
        .btn-confirm:hover {
            background: #88BB22;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div class="hidden lg:flex items-center gap-2 text-sm text-gray-500">
                            <a href="schedules" class="hover:text-[#003366]">Schedule</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="font-semibold text-gray-700">Create Schedule</span>
                        </div>
                        <span class="lg:hidden text-sm font-bold text-[#003366]">Create Schedule</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="schedules" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition flex items-center gap-2">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Back
                        </a>
                        <?php $navName = htmlspecialchars($_SESSION['tamec_name'] ?? 'Admin'); ?>
                        <div class="flex items-center space-x-2 pl-3 border-l border-gray-100">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($navName); ?>&background=003366&color=fff&size=32" class="w-8 h-8 rounded-full">
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold text-gray-800 leading-tight"><?php echo $navName; ?></p>
                                <p class="text-xs text-[#99CC33] leading-tight">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Create Schedule</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Create schedules for staff across multiple dates</p>
                </div>
            </div>

            <!-- Step 1: Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-[#003366] mb-4">Step 1: Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Select Client <span class="text-red-500">*</span></label>
                        <select id="clientSelect" class="form-select">
                            <option value="">-- Select Client --</option>
                            <?php foreach ($counters['all_clients']['clients'] ?? [] as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= $client['firstname'] ?> <?= $client['lastname'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Number of Schedule Forms <span class="text-red-500">*</span></label>
                        <div class="number-input">
                            <input type="number" id="scheduleCount" class="form-input" min="1" max="30" value="1">
                            <button onclick="generateScheduleForms()" class="btn btn-primary">
                                <i class="fas fa-plus-circle mr-2"></i>Generate
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Maximum 30 schedule forms at once</p>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden">
                <div class="spinner"></div>
                <p class="text-center text-gray-500">Generating schedule forms...</p>
            </div>

            <!-- Step 2: Schedule Forms Container -->
            <div id="scheduleFormsContainer" class="hidden">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[#003366]">Step 2: Schedule Details</h3>
                        <div class="text-sm text-gray-500">
                            <span id="totalSchedules">0</span> schedules to create
                        </div>
                    </div>
                    
                    <!-- Schedule Cards Container -->
                    <div id="scheduleCards" class="space-y-4"></div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button onclick="showResetConfirmation()" class="btn btn-secondary">
                            <i class="fas fa-undo mr-2"></i>Reset All
                        </button>
                        <button onclick="showSubmitConfirmation()" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Save All Schedules
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Reset Confirmation Modal -->
    <div id="resetConfirmModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <div class="confirmation-modal-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="confirmation-modal-title">Reset All Schedules?</h3>
                <p class="confirmation-modal-message">This will clear all unsaved schedule data. Are you sure you want to reset?</p>
            </div>
            <div class="confirmation-modal-actions">
                <button class="btn-cancel" onclick="closeResetModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmReset()">Reset All</button>
            </div>
        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div id="submitConfirmModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <div class="confirmation-modal-icon success">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="confirmation-modal-title">Confirm Schedule Creation</h3>
                <p class="confirmation-modal-message">Are you sure you want to save <span id="scheduleCount">0</span> schedules?</p>
            </div>
            <div class="confirmation-modal-actions">
                <button class="btn-cancel" onclick="closeSubmitModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmSubmit()">Yes, Save Schedules</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="toastMessage">Schedule created successfully!</span>
        </div>
    </div>

    <script>
        // Sample data - In production, fetch from API
        const staffList = [
            <?php 
                foreach ($counters['all_staffs']['staffs'] ?? [] as $staff):
                    if($staff['role'] === 'admin') continue;
            ?>
                {
                    id: <?= $staff['id'] ?>,
                    name: "<?= addslashes($staff['firstname']) ?> <?= addslashes($staff['lastname']) ?>",
                    role: "<?= addslashes($staff['role']) ?>",
                },
            <?php endforeach; ?>
        ];

        const clients = {
            <?php foreach ($counters['all_clients']['clients'] ?? [] as $client): ?>
                <?= $client['id'] ?>: {
                    id: <?= $client['id'] ?>,
                    name: "<?= addslashes($client['firstname']) ?> <?= addslashes($client['lastname']) ?>",
                    location: "<?= addslashes($client['residential_address']) ?>"
                },
            <?php endforeach; ?>
        };

        // Holidays array - format: { date: 'YYYY-MM-DD', name: 'Holiday Name', rate: percentage }
        const holidays = [
            <?php foreach ($counters['all_holidays'] ?? [] as $holiday): ?>
                {
                    date: "<?= $holiday['holiday_date'] ?>",
                    name: "<?= addslashes($holiday['holiday_name']) ?>",
                    rate: <?= $holiday['premium_percentage'] ?>
                },
            <?php endforeach; ?>
        ];

        // Function to check if a date is a holiday and return holiday info
        function getHolidayInfo(dateStr) {
            const holiday = holidays.find(h => h.date === dateStr);
            if (holiday) {
                return {
                    isHoliday: true,
                    name: holiday.name,
                    rate: holiday.rate
                };
            }
            return {
                isHoliday: false,
                name: null,
                rate: 0
            };
        }

        let generatedSchedules = [];

        // Generate schedule forms based on number input
        function generateScheduleForms() {
            const clientId = document.getElementById('clientSelect').value;
            const scheduleCount = parseInt(document.getElementById('scheduleCount').value);

            if (!clientId) {
                showToast('Please select a client', 'error');
                return;
            }

            if (scheduleCount < 1 || scheduleCount > 30) {
                showToast('Please enter a number between 1 and 30', 'error');
                return;
            }

            // Show loading
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('scheduleFormsContainer').classList.add('hidden');

            setTimeout(() => {
                renderScheduleForms(scheduleCount);
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('scheduleFormsContainer').classList.remove('hidden');
            }, 300);
        }

        // Render schedule forms
        function renderScheduleForms(count) {
            const clientId = document.getElementById('clientSelect').value;
            const client = clients[clientId];
            const container = document.getElementById('scheduleCards');
            
            document.getElementById('totalSchedules').textContent = count;
            
            // Generate schedule cards
            generatedSchedules = [];
            container.innerHTML = '';
            
            for (let i = 0; i < count; i++) {
                const scheduleId = `schedule_${i}`;
                const today = new Date();
                const defaultDate = today.toISOString().split('T')[0];
                const holidayInfo = getHolidayInfo(defaultDate);
                const isHoliday = holidayInfo.isHoliday;
                
                const card = document.createElement('div');
                card.className = `schedule-card ${isHoliday ? 'holiday-card' : ''}`;
                card.id = scheduleId;
                card.setAttribute('data-index', i);
                
                card.innerHTML = `
                    <div class="remove-schedule" onclick="removeScheduleCard(${i})">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">
                                Schedule Date <span class="text-red-500">*</span>
                                ${isHoliday ? '<span class="holiday-badge"><i class="fas fa-gift"></i> Holiday</span>' : ''}
                            </label>
                            <input type="date" class="form-input date-input" value="${defaultDate}" 
                                   data-field="date" data-index="${i}" onchange="updateScheduleDate(${i}, this.value)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Staff Member <span class="text-red-500">*</span></label>
                            <select class="form-select" data-field="staff" data-index="${i}" onchange="updateScheduleField(${i}, 'staff', this.value)">
                                <option value="">-- Select Staff --</option>
                                ${staffList.map(staff => `
                                    <option value="${staff.id}">${staff.name} (${staff.role})</option>
                                `).join('')}
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pay Rate ($/hr) <span class="text-red-500">*</span></label>
                            <input type="number" class="form-input" step="0.01" placeholder="Enter rate" 
                                   data-field="payRate" data-index="${i}" onchange="updateScheduleField(${i}, 'payRate', this.value)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Start Time <span class="text-red-500">*</span></label>
                            <input type="time" class="form-input" value="09:00" 
                                   data-field="startTime" data-index="${i}" onchange="updateScheduleField(${i}, 'startTime', this.value)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">End Time <span class="text-red-500">*</span></label>
                            <input type="time" class="form-input" value="17:00" 
                                   data-field="endTime" data-index="${i}" onchange="updateScheduleField(${i}, 'endTime', this.value)">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Shift Type</label>
                            <select class="form-select" data-field="shiftType" data-index="${i}" onchange="updateScheduleField(${i}, 'shiftType', this.value)">
                                <option value="day">Day</option>
                                <option value="evening">Evening</option>
                                <option value="overnight">Overnight</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Overnight Type</label>
                            <select class="form-select" data-field="overnightType" data-index="${i}" onchange="updateScheduleField(${i}, 'overnightType', this.value)">
                                <option value="none">None</option>
                                <option value="awake">Awake Overnight</option>
                                <option value="rest">Rest Overnight</option>
                            </select>
                        </div>
                    </div>
                    <div id="holidayNotice_${i}" class="holiday-notice" style="display: ${isHoliday ? 'block' : 'none'}">
                        <i class="fas fa-gift holiday-icon"></i>
                        <strong>Holiday Alert:</strong> ${holidayInfo.name} - ${holidayInfo.rate}% premium will be applied automatically
                    </div>
                `;
                
                container.appendChild(card);
                
                // Initialize schedule data
                generatedSchedules.push({
                    id: i,
                    date: defaultDate,
                    clientId: parseInt(clientId),
                    staff: '',
                    payRate: '',
                    startTime: '09:00',
                    endTime: '17:00',
                    shiftType: 'day',
                    overnightType: 'none',
                    isHoliday: isHoliday,
                    holidayName: holidayInfo.name,
                    holidayRate: holidayInfo.rate
                });
            }
        }

        // Update schedule date with dynamic holiday detection
        function updateScheduleDate(index, newDate) {
            if (generatedSchedules[index]) {
                generatedSchedules[index].date = newDate;
                
                // Check if the new date is a holiday
                const holidayInfo = getHolidayInfo(newDate);
                const isHoliday = holidayInfo.isHoliday;
                
                // Update the schedule data
                generatedSchedules[index].isHoliday = isHoliday;
                generatedSchedules[index].holidayName = holidayInfo.name;
                generatedSchedules[index].holidayRate = holidayInfo.rate;
                
                // Update the card styling
                const card = document.getElementById(`schedule_${index}`);
                if (card) {
                    if (isHoliday) {
                        card.classList.add('holiday-card');
                        // Update date label to show holiday badge
                        const dateLabel = card.querySelector('.form-group:first-child .form-label');
                        if (dateLabel && !dateLabel.querySelector('.holiday-badge')) {
                            dateLabel.innerHTML = `Schedule Date <span class="text-red-500">*</span> <span class="holiday-badge"><i class="fas fa-gift"></i> Holiday</span>`;
                        }
                    } else {
                        card.classList.remove('holiday-card');
                        // Remove holiday badge from label
                        const dateLabel = card.querySelector('.form-group:first-child .form-label');
                        if (dateLabel) {
                            dateLabel.innerHTML = `Schedule Date <span class="text-red-500">*</span>`;
                        }
                    }
                    
                    // Update or remove holiday notice
                    const holidayNotice = document.getElementById(`holidayNotice_${index}`);
                    if (isHoliday) {
                        if (holidayNotice) {
                            holidayNotice.style.display = 'block';
                            holidayNotice.innerHTML = `<i class="fas fa-gift holiday-icon"></i> <strong>Holiday Alert:</strong> ${holidayInfo.name} - ${holidayInfo.rate}% premium will be applied automatically`;
                        } else {
                            // Create notice if it doesn't exist
                            const noticeDiv = document.createElement('div');
                            noticeDiv.id = `holidayNotice_${index}`;
                            noticeDiv.className = 'holiday-notice';
                            noticeDiv.innerHTML = `<i class="fas fa-gift holiday-icon"></i> <strong>Holiday Alert:</strong> ${holidayInfo.name} - ${holidayInfo.rate}% premium will be applied automatically`;
                            card.appendChild(noticeDiv);
                        }
                    } else {
                        if (holidayNotice) {
                            holidayNotice.style.display = 'none';
                        }
                    }
                }
            }
        }

        // Update schedule field
        function updateScheduleField(index, field, value) {
            if (generatedSchedules[index]) {
                generatedSchedules[index][field] = value;
            }
        }

        // Remove schedule card
        function removeScheduleCard(index) {
            // Custom confirmation for removing a single schedule
            const confirmModal = document.createElement('div');
            confirmModal.className = 'confirmation-modal active';
            confirmModal.style.display = 'flex';
            confirmModal.innerHTML = `
                <div class="confirmation-modal-content">
                    <div class="confirmation-modal-header">
                        <div class="confirmation-modal-icon warning">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3 class="confirmation-modal-title">Remove Schedule?</h3>
                        <p class="confirmation-modal-message">Are you sure you want to remove this schedule? This action cannot be undone.</p>
                    </div>
                    <div class="confirmation-modal-actions">
                        <button class="btn-cancel" onclick="this.closest('.confirmation-modal').remove()">Cancel</button>
                        <button class="btn-confirm" onclick="confirmRemoveSchedule(${index})">Remove</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmModal);
        }

        function confirmRemoveSchedule(index) {
            document.getElementById(`schedule_${index}`).remove();
            generatedSchedules.splice(index, 1);
            
            // Re-index remaining schedules
            generatedSchedules.forEach((schedule, newIndex) => {
                schedule.id = newIndex;
                const card = document.getElementById(`schedule_${schedule.id}`);
                if (card) {
                    card.id = `schedule_${newIndex}`;
                    card.setAttribute('data-index', newIndex);
                    
                    // Update all data-index attributes in inputs
                    card.querySelectorAll('[data-index]').forEach(el => {
                        el.setAttribute('data-index', newIndex);
                    });
                    
                    // Update onclick for remove button
                    const removeBtn = card.querySelector('.remove-schedule');
                    if (removeBtn) {
                        removeBtn.setAttribute('onclick', `removeScheduleCard(${newIndex})`);
                    }
                    
                    // Update holiday notice id
                    const holidayNotice = document.getElementById(`holidayNotice_${schedule.id}`);
                    if (holidayNotice) {
                        holidayNotice.id = `holidayNotice_${newIndex}`;
                    }
                }
            });
            
            document.getElementById('totalSchedules').textContent = generatedSchedules.length;
            showToast('Schedule removed', 'success');
            document.querySelector('.confirmation-modal').remove();
        }

        // Reset confirmation modal
        function showResetConfirmation() {
            document.getElementById('resetConfirmModal').classList.add('active');
        }

        function closeResetModal() {
            document.getElementById('resetConfirmModal').classList.remove('active');
        }

        function confirmReset() {
            document.getElementById('clientSelect').value = '';
            document.getElementById('scheduleCount').value = '1';
            document.getElementById('scheduleFormsContainer').classList.add('hidden');
            generatedSchedules = [];
            closeResetModal();
            showToast('Form reset successfully', 'success');
        }

        // Submit confirmation modal
        function showSubmitConfirmation() {
            // Validate all schedules first
            const invalidSchedules = generatedSchedules.filter(s => !s.staff || !s.payRate);
            
            if (invalidSchedules.length > 0) {
                showToast(`Please complete ${invalidSchedules.length} incomplete schedules`, 'error');
                return;
            }
            
            if (generatedSchedules.length === 0) {
                showToast('No schedules to save. Please generate schedules first.', 'error');
                return;
            }
            
            document.getElementById('scheduleCount').textContent = generatedSchedules.length;
            document.getElementById('submitConfirmModal').classList.add('active');
        }

        function closeSubmitModal() {
            document.getElementById('submitConfirmModal').classList.remove('active');
        }

        function confirmSubmit() {
            closeSubmitModal();
            
            const clientId = document.getElementById('clientSelect').value;
            const client = clients[clientId];
            
            const dataToSubmit = {
                client_id: clientId,
                client_name: client.name,
                schedules: generatedSchedules.map(s => ({
                    date: s.date,
                    staff_id: s.staff,
                    pay_rate: parseFloat(s.payRate),
                    start_time: s.startTime,
                    end_time: s.endTime,
                    shift_type: s.shiftType,
                    overnight_type: s.overnightType,
                    is_holiday: s.isHoliday,
                    holiday_rate: s.holidayRate,
                    holiday_name: s.holidayName
                }))
            };
            
            // Show loading state on button
            const submitBtn = document.querySelector('button[onclick="showSubmitConfirmation()"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            submitBtn.disabled = true;

            $.ajax({
                url: 'save_schedules',
                type: 'POST',
                dataType: 'json',
                data: dataToSubmit,
                success: function(response) {
                    console.log('Server response:', response);
                    if (response.status) {
                        showToast(`${generatedSchedules.length} schedules created successfully!`);
                        // Reset after successful save
                        document.getElementById('clientSelect').value = '';
                        document.getElementById('scheduleCount').value = '1';
                        document.getElementById('scheduleFormsContainer').classList.add('hidden');
                        generatedSchedules = [];
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(error) {
                    console.error('Error saving schedules:', error);
                    showToast('Error saving schedules', 'error');
                },
                complete: function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            toast.className = 'toast ' + (type === 'error' ? 'error' : '');
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Sidebar functions
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>