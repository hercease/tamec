<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Schedule Calendar (Horizontal View)</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #99CC33;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #669933;
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.75rem;
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .confirmation-modal.active {
            display: flex;
        }

        .confirmation-modal-content {
            background: white;
            border-radius: 1rem;
            max-width: 400px;
            width: 90%;
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
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
            font-size: 2rem;
        }

        .confirmation-modal-icon.danger {
            background: #fee2e2;
        }

        .confirmation-modal-icon.danger i {
            color: #ef4444;
            font-size: 2rem;
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

        /* Filter bar */
        .filter-bar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .filter-bar {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
            }
        }

        .date-range-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 640px) {
            .date-range-container {
                flex-direction: row;
                align-items: center;
                width: auto;
            }
        }

        .date-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-input-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
        }

        .date-input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            min-width: 140px;
        }

        .range-badge {
            background: #e6f7e6;
            color: #003366;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .filter-controls {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        @media (min-width: 640px) {
            .filter-controls {
                grid-template-columns: repeat(3, 1fr);
                width: auto;
                min-width: 400px;
            }
        }

        .filter-select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: white;
            width: 100%;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-primary {
            background: #99CC33;
            color: white;
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
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-cancel {
            background: white;
            border: 1px solid #d1d5db;
        }

        .btn-cancel:hover {
            background: #f3f4f6;
        }

        /* Schedule table */
        .schedule-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 1.5rem;
            position: relative;
        }

        .schedule-header {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .date-range-info {
            font-size: 1rem;
            font-weight: 600;
            color: #003366;
        }

        .date-range-days {
            margin-left: 1rem;
            font-size: 0.875rem;
            color: #99CC33;
            font-weight: 600;
        }

        .schedule-table-wrapper {
            overflow-x: auto;
            max-height: 70vh;
            overflow-y: auto;
            position: relative;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .schedule-table th {
            background: #f3f4f6;
            padding: 0.75rem 0.5rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            border-right: 1px solid #e5e7eb;
            border-bottom: 2px solid #d1d5db;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .schedule-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 20;
            background: #e5e7eb;
            border-right: 2px solid #d1d5db;
        }

        .schedule-table td {
            padding: 0.5rem;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            min-width: 120px;
            max-width: 150px;
        }

        .schedule-table td:first-child {
            position: sticky;
            left: 0;
            background: white;
            z-index: 5;
            font-weight: 600;
            color: #003366;
            border-right: 2px solid #d1d5db;
            min-width: 180px;
        }

        .client-info {
            display: flex;
            flex-direction: column;
        }

        .client-name {
            font-weight: 600;
            color: #003366;
            font-size: 0.875rem;
        }

        .client-location {
            font-size: 0.7rem;
            color: #6b7280;
        }

        .date-header {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .date-weekday {
            font-size: 0.7rem;
            color: #6b7280;
            text-transform: uppercase;
        }

        .date-day {
            font-size: 0.9rem;
            font-weight: 700;
            color: #111827;
        }

        .date-month {
            font-size: 0.7rem;
            color: #6b7280;
        }

        .today-indicator {
            background: #99CC33;
            color: white;
            font-size: 0.6rem;
            padding: 0.1rem 0.3rem;
            border-radius: 1rem;
            margin-top: 0.1rem;
        }

        /* Schedule items in cells */
        .schedule-items {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .schedule-card {
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            cursor: pointer;
        }

        .schedule-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-color: #99CC33;
        }

        .schedule-card-actions {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .schedule-card:hover .schedule-card-actions {
            opacity: 1;
        }

        .schedule-card-action {
            padding: 0.2rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .schedule-card-action.edit:hover {
            color: #99CC33;
        }

        .schedule-card-action.delete:hover {
            color: #ef4444;
        }

        .staff-name-mini {
            font-weight: 600;
            color: #111827;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
            padding-right: 3rem;
        }

        .shift-time-mini {
            font-size: 0.6rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.6rem;
            font-weight: 600;
            text-align: center;
        }

        .status-scheduled {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-in-progress {
            background: #ff9800;
            color: white;
            animation: pulse 2s infinite;
        }

        .status-completed {
            background: #10b981;
            color: white;
        }

        .status-confirmed {
            background: #99CC33;
            color: white;
        }

        .status-cancelled {
            background: #ef4444;
            color: white;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        /* Legend */
        .legend {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 640px) {
            .legend {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .legend {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
            }
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.7rem;
        }

        /* Empty cell */
        .empty-cell {
            color: #9ca3af;
            font-size: 0.7rem;
            text-align: center;
            padding: 0.5rem;
            font-style: italic;
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Summary stats */
        .summary-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #003366;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #99CC33;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem;
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

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        hr {
            margin: 1rem 0;
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
                    <div class="flex items-center lg:hidden">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <span class="ml-3 text-sm font-bold text-[#003366]">TAMEC</span>
                    </div>
                    <div class="hidden lg:flex items-center space-x-2">
                        <i class="fas fa-calendar-alt text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Schedule</h2>
                    </div>
                    <?php $navName = htmlspecialchars($_SESSION['tamec_name'] ?? 'Admin'); ?>
                    <div class="flex items-center space-x-2 pl-3 border-l border-gray-100">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($navName); ?>&background=003366&color=fff&size=32"
                            class="w-8 h-8 rounded-full">
                        <div class="hidden sm:block">
                            <p class="text-xs font-semibold text-gray-800 leading-tight"><?php echo $navName; ?></p>
                            <p class="text-xs text-[#99CC33] leading-tight">Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="p-4 sm:p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Schedule Calendar</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">View schedules by client across date ranges</p>
                </div>
                <button onclick="window.location.href = 'create_schedule';"
                    class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create New Shift
                </button>
            </div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item"><span class="status-badge status-scheduled">Scheduled</span></div>
                <div class="legend-item"><span class="status-badge status-confirmed">Confirmed</span></div>
                <div class="legend-item"><span class="status-badge status-in-progress">In Progress</span></div>
                <div class="legend-item"><span class="status-badge status-completed">Completed</span></div>
                <div class="legend-item"><span class="status-badge status-cancelled">Cancelled</span></div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="date-range-container">
                    <div class="date-input-group">
                        <span class="date-input-label">From</span>
                        <input type="date" id="startDate" class="date-input" onchange="updateDateRange()"
                            value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="date-input-group">
                        <span class="date-input-label">To</span>
                        <input type="date" id="endDate" class="date-input" onchange="updateDateRange()"
                            value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="range-badge" id="dateRangeBadge">
                        <span id="totalDays">1</span> day(s)
                    </div>
                </div>

                <div class="filter-controls">
                    <select id="staffFilter" class="filter-select" onchange="filterSchedule()">
                        <option value="all">All Staff</option>
                    </select>

                    <select id="clientFilter" class="filter-select" onchange="filterSchedule()">
                        <option value="all">All Clients</option>
                    </select>

                    <select id="statusFilter" class="filter-select" onchange="filterSchedule()">
                        <option value="all">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="action-buttons">
                    <button onclick="applyDatePreset('week')" class="btn btn-secondary">This Week</button>
                    <button onclick="applyDatePreset('month')" class="btn btn-secondary">This Month</button>
                    <button onclick="applyDatePreset('quarter')" class="btn btn-secondary">This Quarter</button>
                    <button onclick="applyDatePreset('year')" class="btn btn-secondary">This Year</button>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="summary-stats" id="summaryStats">
                <div class="stat-card">
                    <div>
                        <div class="stat-value" id="totalShifts">0</div>
                        <div class="stat-label">Total Shifts</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-value" id="uniqueStaff">0</div>
                        <div class="stat-label">Staff</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-value" id="uniqueClients">0</div>
                        <div class="stat-label">Clients</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-value" id="totalHours">0</div>
                        <div class="stat-label">Hours</div>
                    </div>
                </div>
            </div>

            <!-- Schedule Container -->
            <div class="schedule-container" id="scheduleContainer">
                <div class="schedule-header">
                    <span class="date-range-info" id="dateRangeInfo">Loading...</span>
                    <span class="date-range-days" id="dateRangeDays"></span>
                </div>

                <div class="schedule-table-wrapper">
                    <div class="loading-container" id="loadingContainer">
                        <div class="loading-spinner"></div>
                        <span class="ml-3 text-gray-500">Loading schedules...</span>
                    </div>
                    <table class="schedule-table" id="scheduleTable" style="display: none;">
                        <thead id="tableHeader">
                            <!-- Header will be populated by JavaScript -->
                        </thead>
                        <tbody id="tableBody">
                            <!-- Table body will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message (hidden by default) -->
            <div id="noResults" class="no-results hidden">
                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                <p>No schedules found for the selected filters and date range.</p>
                <button onclick="clearAllFilters()" class="mt-3 btn btn-primary">Clear Filters</button>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Edit Schedule Modal -->
    <div id="editScheduleModal" class="modal">
        <div class="modal-content p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-bold text-black">Edit Schedule</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="editScheduleForm" onsubmit="submitEditSchedule(event)">
                <input type="hidden" id="editScheduleId">

                <div class="form-group">
                    <label class="form-label">Staff</label>
                    <input type="text" id="editStaffName" class="form-input" readonly disabled
                        style="background: #f3f4f6;">
                </div>

                <div class="form-group">
                    <label class="form-label">Client</label>
                    <input type="text" id="editClientName" class="form-input" readonly disabled
                        style="background: #f3f4f6;">
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" id="editDate" class="form-input" readonly disabled style="background: #f3f4f6;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time <span class="text-red-500">*</span></label>
                        <input type="time" id="editStartTime" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time <span class="text-red-500">*</span></label>
                        <input type="time" id="editEndTime" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Clock In Time</label>
                        <input type="time" id="editClockIn" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Clock Out Time</label>
                        <input type="time" id="editClockOut" class="form-input">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="editStatus" class="form-select" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>

                    </div>
                    <div class="form-group">
                        <label class="form-label">Pay Rate ($/hr) <span class="text-red-500">*</span></label>
                        <input type="number" id="editPayRate" class="form-input" step="0.01" required>
                    </div>
                </div>

                <hr>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:space-x-3">
                    <button type="button" onclick="closeEditModal()" class="btn btn-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Confirmation Modal -->
    <div id="editConfirmModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <div class="confirmation-modal-icon warning">
                    <i class="fas fa-edit"></i>
                </div>
                <h3 class="confirmation-modal-title">Confirm Changes</h3>
                <p class="confirmation-modal-message">Are you sure you want to update this schedule? This action cannot
                    be undone.</p>
            </div>
            <div class="confirmation-modal-actions">
                <button class="btn-cancel" onclick="closeEditConfirmModal()">Cancel</button>
                <button class="btn-confirm" onclick="confirmEditSchedule()">Yes, Update</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <div class="confirmation-modal-icon danger">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h3 class="confirmation-modal-title">Delete Schedule</h3>
                <p class="confirmation-modal-message">Are you sure you want to delete this schedule? This action cannot
                    be undone.</p>
            </div>
            <div class="confirmation-modal-actions">
                <button class="btn-cancel" onclick="closeDeleteConfirmModal()">Cancel</button>
                <button class="btn-danger" onclick="confirmDeleteSchedule()">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Schedule Detail Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-bold text-black" id="scheduleModalTitle">Shift Details</h3>
                <button onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="scheduleDetails" class="space-y-4">
                <!-- Populated by JavaScript -->
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:space-x-3 mt-6">
                <button onclick="closeScheduleModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>


    <script>
        // Global variables
        let schedules = [];
        let filteredSchedules = [];
        let uniqueClients = [];
        let dateRange = [];
        let selectedSchedule = null;
        let staffList = [];
        let clientList = [];

        // Month and weekday names
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const fullMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const weekdayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const fullWeekdayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            document.getElementById('startDate').value = formatDate(startDate);
            document.getElementById('endDate').value = formatDate(endDate);

            fetchSchedules();
        });

        // Fetch schedules from server
        function fetchSchedules() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            document.getElementById('loadingContainer').style.display = 'flex';
            document.getElementById('scheduleTable').style.display = 'none';
            document.getElementById('noResults').classList.add('hidden');

            $.ajax({
                url: 'fetch_schedules',
                method: 'POST',
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        schedules = response.data || [];

                        const staffMap = new Map();
                        schedules.forEach(s => {
                            if (!staffMap.has(s.user_id)) {
                                staffMap.set(s.user_id, {
                                    id: s.user_id,
                                    name: s.staff_name
                                });
                            }
                        });
                        staffList = Array.from(staffMap.values()).sort((a, b) => a.name.localeCompare(b.name));

                        const clientMap = new Map();
                        schedules.forEach(s => {
                            if (!clientMap.has(s.client_id)) {
                                clientMap.set(s.client_id, {
                                    id: s.client_id,
                                    name: s.client_name,
                                    location: s.client_location || ''
                                });
                            }
                        });
                        clientList = Array.from(clientMap.values()).sort((a, b) => a.name.localeCompare(b.name));

                        populateFilterDropdowns();
                        filterSchedule();
                        updateDateRangeDisplay();
                    } else {
                        showNoResults('Failed to load schedules. Please try again.');
                    }
                },
                error: function () {
                    showNoResults('Failed to load schedules. Please check your connection.');
                },
                complete: function () {
                    document.getElementById('loadingContainer').style.display = 'none';
                }
            });
        }

        // Populate filter dropdowns
        function populateFilterDropdowns() {
            const staffFilter = document.getElementById('staffFilter');
            const clientFilter = document.getElementById('clientFilter');

            staffFilter.innerHTML = '<option value="all">All Staff</option>';
            staffList.forEach(staff => {
                staffFilter.innerHTML += `<option value="${staff.id}">${escapeHtml(staff.name)}</option>`;
            });

            clientFilter.innerHTML = '<option value="all">All Clients</option>';
            clientList.forEach(client => {
                clientFilter.innerHTML += `<option value="${client.id}">${escapeHtml(client.name)}</option>`;
            });
        }

        // Filter schedules
        function filterSchedule() {
            const staffFilterValue = document.getElementById('staffFilter').value;
            const clientFilterValue = document.getElementById('clientFilter').value;
            const statusFilterValue = document.getElementById('statusFilter').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            filteredSchedules = schedules.filter(schedule => {
                if (staffFilterValue !== 'all' && schedule.user_id != staffFilterValue) return false;
                if (clientFilterValue !== 'all' && schedule.client_id != clientFilterValue) return false;
                if (statusFilterValue !== 'all' && schedule.status !== statusFilterValue) return false;
                if (startDate && endDate) {
                    return schedule.schedule_date >= startDate && schedule.schedule_date <= endDate;
                }
                return true;
            });

            const clientMap = new Map();
            filteredSchedules.forEach(s => {
                if (!clientMap.has(s.client_id)) {
                    clientMap.set(s.client_id, {
                        id: s.client_id,
                        name: s.client_name,
                        location: s.client_location || ''
                    });
                }
            });

            uniqueClients = Array.from(clientMap.values()).sort((a, b) => a.name.localeCompare(b.name));

            if (startDate && endDate) {
                dateRange = getDaysArray(startDate, endDate);
            } else {
                dateRange = [];
            }

            updateSummaryStats();
            renderScheduleTable();
        }

        // Update summary statistics
        function updateSummaryStats() {
            const totalShifts = filteredSchedules.length;
            const uniqueStaffIds = new Set(filteredSchedules.map(s => s.user_id));
            const uniqueClientIds = new Set(filteredSchedules.map(s => s.client_id));

            let totalHours = 0;
            filteredSchedules.forEach(s => {
                const start = s.start_time.split(':');
                const end = s.end_time.split(':');
                let hours = parseInt(end[0]) - parseInt(start[0]);
                if (hours < 0) hours += 24;
                totalHours += hours;
            });

            document.getElementById('totalShifts').textContent = totalShifts;
            document.getElementById('uniqueStaff').textContent = uniqueStaffIds.size;
            document.getElementById('uniqueClients').textContent = uniqueClientIds.size;
            document.getElementById('totalHours').textContent = totalHours;
        }

        // Update date range display
        function updateDateRangeDisplay() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                const start = new Date(startDate + 'T12:00:00');
                const end = new Date(endDate + 'T12:00:00');
                const daysDiff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;

                document.getElementById('dateRangeInfo').innerHTML =
                    `${fullMonthNames[start.getMonth()]} ${start.getDate()}, ${start.getFullYear()} to ${fullMonthNames[end.getMonth()]} ${end.getDate()}, ${end.getFullYear()}`;
                document.getElementById('dateRangeDays').innerHTML = `(${daysDiff} days)`;
                document.getElementById('totalDays').textContent = daysDiff;
            }
        }

        // Format date
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Format display date
        function formatDisplayDate(dateStr) {
            const date = new Date(dateStr + 'T12:00:00');
            return {
                weekday: weekdayNames[date.getDay()],
                day: date.getDate(),
                month: monthNames[date.getMonth()],
                isToday: isToday(dateStr)
            };
        }

        function isToday(dateStr) {
            const today = new Date();
            return dateStr === formatDate(today);
        }

        function getDaysArray(startDate, endDate) {
            const days = [];
            let currentDate = new Date(startDate);
            const endDateTime = new Date(endDate);
            while (currentDate <= endDateTime) {
                days.push(formatDate(currentDate));
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return days;
        }

        function updateDateRange() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (startDate && endDate) {
                updateDateRangeDisplay();
                fetchSchedules();
            }
        }

        function applyDatePreset(preset) {
            const today = new Date();
            let startDate, endDate;
            switch (preset) {
                case 'week':
                    const dayOfWeek = today.getDay();
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - dayOfWeek);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    break;
                case 'month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'quarter':
                    const quarter = Math.floor(today.getMonth() / 3);
                    startDate = new Date(today.getFullYear(), quarter * 3, 1);
                    endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
                    break;
                case 'year':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = new Date(today.getFullYear(), 11, 31);
                    break;
                default: return;
            }
            document.getElementById('startDate').value = formatDate(startDate);
            document.getElementById('endDate').value = formatDate(endDate);
            updateDateRange();
        }

        function formatTime(timeStr) {
            if (!timeStr) return '';

            // Handle different time formats
            let hour, minute;

            if (timeStr.includes(':')) {
                const parts = timeStr.split(':');
                hour = parseInt(parts[0]);
                minute = parts[1];
            } else {
                return timeStr;
            }

            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            hour = hour ? hour : 12; // Convert 0 to 12

            return `${hour}:${minute} ${ampm}`;
        }

        // Render schedule table
        function renderScheduleTable() {
            const tableHeader = document.getElementById('tableHeader');
            const tableBody = document.getElementById('tableBody');
            const scheduleTable = document.getElementById('scheduleTable');

            if (filteredSchedules.length === 0 || uniqueClients.length === 0 || dateRange.length === 0) {
                scheduleTable.style.display = 'none';
                document.getElementById('noResults').classList.remove('hidden');
                return;
            }

            scheduleTable.style.display = 'table';
            document.getElementById('noResults').classList.add('hidden');

            let headerHtml = '<th>Client / Location</th>';
            dateRange.forEach(date => {
                const display = formatDisplayDate(date);
                headerHtml += `
                    <th>
                        <div class="date-header">
                            <span class="date-weekday">${display.weekday}</span>
                            <span class="date-day">${display.day}</span>
                            <span class="date-month">${display.month}</span>
                            ${display.isToday ? '<div class="today-indicator">Today</div>' : ''}
                        </div>
                    </th>
                `;
            });
            headerHtml += '</tr>';
            tableHeader.innerHTML = headerHtml;

            let bodyHtml = '';
            uniqueClients.forEach(client => {
                bodyHtml += '<tr>';
                bodyHtml += `
                    <td>
                        <div class="client-info">
                            <span class="client-name">${escapeHtml(client.name)}</span>
                            <span class="client-location">${escapeHtml(client.location)}</span>
                        </div>
                    </td>
                `;

                dateRange.forEach(date => {
                    const daySchedules = filteredSchedules.filter(s =>
                        s.client_id === client.id && s.schedule_date === date
                    );

                    if (daySchedules.length > 0) {
                        bodyHtml += '<td><div class="schedule-items">';
                        daySchedules.forEach(schedule => {
                            const statusClass = getStatusClass(schedule.status);
                            const statusDisplay = getStatusDisplay(schedule.status);

                            bodyHtml += `
                                <div class="schedule-card" onclick="viewSchedule(${schedule.id})">
                                    <div class="schedule-card-actions">
                                        <div class="schedule-card-action edit" onclick="event.stopPropagation(); openEditModal(${schedule.id})">
                                            <i class="fas fa-edit text-xs"></i>
                                        </div>
                                        <div class="schedule-card-action delete" onclick="event.stopPropagation(); openDeleteModal(${schedule.id})">
                                            <i class="fas fa-trash text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="staff-name-mini">${escapeHtml(schedule.staff_name)}</div>
                                    <div class="shift-time-mini">${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}</div>
                                    <div class="mt-1">
                                        <span class="status-badge ${statusClass}">${statusDisplay}</span>
                                    </div>
                                </div>
                            `;
                        });
                        bodyHtml += '</div></td>';
                    } else {
                        bodyHtml += '<td><div class="empty-cell">—</div></td>';
                    }
                });
                bodyHtml += '</tr>';
            });
            tableBody.innerHTML = bodyHtml;
        }

        function getStatusClass(status) {
            const classes = {
                'scheduled': 'status-scheduled',
                'confirmed': 'status-confirmed',
                'in-progress': 'status-in-progress',
                'completed': 'status-completed',
                'cancelled': 'status-cancelled'
            };
            return classes[status] || 'status-scheduled';
        }

        function getStatusDisplay(status) {
            const displays = {
                'scheduled': 'Scheduled',
                'confirmed': 'Confirmed',
                'in-progress': 'In Progress',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            return displays[status] || status;
        }

        function clearAllFilters() {
            document.getElementById('staffFilter').value = 'all';
            document.getElementById('clientFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            document.getElementById('startDate').value = formatDate(startDate);
            document.getElementById('endDate').value = formatDate(endDate);
            updateDateRange();
        }

        function showNoResults(message) {
            const noResults = document.getElementById('noResults');
            document.getElementById('scheduleTable').style.display = 'none';
            noResults.classList.remove('hidden');
            noResults.innerHTML = `
                <i class="fas fa-exclamation-triangle text-4xl text-gray-300 mb-3"></i>
                <p>${message}</p>
                <button onclick="fetchSchedules()" class="mt-3 btn btn-primary">Retry</button>
            `;
        }

        // View schedule details
        function viewSchedule(scheduleId) {
            const schedule = schedules.find(s => s.id === scheduleId);
            if (!schedule) return;

            selectedScheduleId = scheduleId;

            const statusClass = getStatusClass(schedule.status);
            const statusDisplay = getStatusDisplay(schedule.status);

            const dateObj = new Date(schedule.schedule_date + 'T12:00:00');
            const formattedDate = `${fullWeekdayNames[dateObj.getDay()]}, ${fullMonthNames[dateObj.getMonth()]} ${dateObj.getDate()}, ${dateObj.getFullYear()}`;

            const detailsHtml = `
                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Staff</p>
                            <p class="font-medium text-sm">${escapeHtml(schedule.staff_name)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Client</p>
                            <p class="font-medium text-sm">${escapeHtml(schedule.client_name)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Location</p>
                            <p class="font-medium text-sm">${escapeHtml(schedule.client_location || 'N/A')}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Date</p>
                            <p class="font-medium text-sm">${formattedDate}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Time</p>
                            <p class="font-medium text-sm">${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Shift Type</p>
                            <p class="font-medium text-sm capitalize">${schedule.shift_type} ${schedule.overnight_type !== 'none' ? `(${schedule.overnight_type})` : ''}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <span class="status-badge ${statusClass}">${statusDisplay}</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Pay Rate</p>
                            <p class="font-medium text-sm">$${parseFloat(schedule.pay_per_hour).toFixed(2)}/hour</p>
                        </div>
                        ${schedule.worked_duration ? `
                        <div>
                            <p class="text-xs text-gray-500">Worked Duration</p>
                            <p class="font-medium text-sm">${schedule.worked_duration}</p>
                        </div>
                        ` : ''}
                        ${schedule.clock_in ? `
                        <div>
                            <p class="text-xs text-gray-500">Clock In</p>
                            <p class="font-medium text-sm">${schedule.clock_in}</p>
                        </div>
                        ` : ''}
                        ${schedule.clock_out ? `
                        <div>
                            <p class="text-xs text-gray-500">Clock Out</p>
                            <p class="font-medium text-sm">${schedule.clock_out}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;

            document.getElementById('scheduleDetails').innerHTML = detailsHtml;
            document.getElementById('scheduleModal').classList.add('active');
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('active');
            selectedScheduleId = null;
        }

        // Open Edit Modal
        function openEditModal(scheduleId) {
            const schedule = schedules.find(s => s.id === scheduleId);
            if (!schedule) return;

            selectedSchedule = schedule;

            document.getElementById('editScheduleId').value = schedule.id;
            document.getElementById('editStaffName').value = schedule.staff_name;
            document.getElementById('editClientName').value = schedule.client_name;
            document.getElementById('editDate').value = schedule.schedule_date;
            document.getElementById('editStartTime').value = schedule.start_time;
            document.getElementById('editEndTime').value = schedule.end_time;
            document.getElementById('editClockIn').value = schedule.clock_in || '';
            document.getElementById('editClockOut').value = schedule.clock_out || '';
            document.getElementById('editPayRate').value = schedule.pay_per_hour;
            document.getElementById('editStatus').value = schedule.status;

            document.getElementById('editScheduleModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editScheduleModal').classList.remove('active');
            selectedSchedule = null;
        }

        function submitEditSchedule(event) {
            event.preventDefault();
            document.getElementById('editConfirmModal').classList.add('active');
        }

        function closeEditConfirmModal() {
            document.getElementById('editConfirmModal').classList.remove('active');
        }

        function confirmEditSchedule() {
            closeEditConfirmModal();

            const scheduleData = {
                id: document.getElementById('editScheduleId').value,
                start_time: document.getElementById('editStartTime').value,
                end_time: document.getElementById('editEndTime').value,
                clock_in: document.getElementById('editClockIn').value,
                clock_out: document.getElementById('editClockOut').value,
                pay_per_hour: document.getElementById('editPayRate').value,
                status: document.getElementById('editStatus').value,
                schedule_date: document.getElementById('editDate').value
            };

            $.ajax({
                url: 'update_schedule',
                method: 'POST',
                data: scheduleData,
                dataType: 'json',
                beforeSend: function () {
                    showToast('Updating schedule...', 'info');
                },
                success: function (response) {
                    if (response.status) {
                        showToast('Schedule updated successfully', 'success');
                        fetchSchedules();
                        closeEditModal();
                    } else {
                        showToast(response.message || 'Failed to update schedule', 'error');
                    }
                },
                error: function () {
                    showToast('Error updating schedule', 'error');
                }
            });
        }

        // Open Delete Modal
        function openDeleteModal(scheduleId) {
            selectedSchedule = schedules.find(s => s.id === scheduleId);
            document.getElementById('deleteConfirmModal').classList.add('active');
        }

        function closeDeleteConfirmModal() {
            document.getElementById('deleteConfirmModal').classList.remove('active');
            selectedSchedule = null;
        }

        function confirmDeleteSchedule() {
            if (!selectedSchedule) return;

            const scheduleId = selectedSchedule.id;

            closeDeleteConfirmModal();

            $.ajax({
                url: 'delete_schedule',
                method: 'POST',
                data: { schedule_id: scheduleId },
                dataType: 'json',
                beforeSend: function () {
                    showToast('Deleting schedule...', 'info');
                },
                success: function (response) {
                    if (response.status) {
                        showToast('Schedule deleted successfully', 'success');
                        fetchSchedules();
                    } else {
                        showToast(response.message || 'Failed to delete schedule', 'error');
                    }
                },
                error: function () {
                    showToast('Error deleting schedule', 'error');
                }
            });
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full ${type === 'error' ? 'bg-red-500' : type === 'info' ? 'bg-blue-500' : 'bg-[#99CC33]'} text-white`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            }, 10);
            setTimeout(() => {
                toast.classList.remove('translate-x-0');
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeEditConfirmModal();
                closeDeleteConfirmModal();
                closeSidebar();
            }
        });
    </script>
</body>

</html>