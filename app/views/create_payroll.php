<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Create Payroll</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #99CC33; border-radius: 5px; }

        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }

        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5); z-index: 30;
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .overlay.active { opacity: 1; visibility: visible; }

        /* Summary panel sticky */
        .summary-panel {
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }

        /* Staff group */
        .staff-group { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 1rem; }
        .staff-header {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1rem 1.25rem; cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: background 0.15s;
        }
        .staff-header:hover { background: #f9fafb; }
        .staff-header.has-selection { border-bottom-color: #99CC33; }

        .staff-schedules { overflow: hidden; transition: max-height 0.3s ease; }
        .staff-schedules.collapsed { max-height: 0 !important; }

        .schedule-row {
            display: grid;
            grid-template-columns: 32px 1fr 1fr 120px 80px 90px;
            align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.1s;
        }
        .schedule-row:last-child { border-bottom: none; }
        .schedule-row:hover { background: #fafafa; }
        .schedule-row.is-selected { background: #f0f9e0; }
        .schedule-row.is-processed { opacity: 0.55; background: #fafafa; }

        @media (max-width: 900px) {
            .schedule-row { grid-template-columns: 32px 1fr 1fr 80px 80px; }
            .col-shift { display: none; }
        }
        @media (max-width: 640px) {
            .schedule-row { grid-template-columns: 32px 1fr 80px 80px; }
            .col-client { display: none; }
        }

        /* Checkbox styling */
        input[type="checkbox"] { width: 16px; height: 16px; accent-color: #99CC33; cursor: pointer; flex-shrink: 0; }
        input[type="checkbox"]:disabled { cursor: not-allowed; accent-color: #9ca3af; }

        .badge {
            padding: 0.2rem 0.6rem; border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600; display: inline-block; white-space: nowrap;
        }
        .badge-scheduled   { background: #e0f2fe; color: #0369a1; }
        .badge-in-progress { background: #fef9c3; color: #a16207; }
        .badge-completed   { background: #dcfce7; color: #15803d; }
        .badge-cancelled   { background: #fee2e2; color: #ef4444; }
        .badge-no-show     { background: #f3f4f6; color: #6b7280; }
        .badge-processed   { background: #ede9fe; color: #7c3aed; }
        .badge-day         { background: #fef3c7; color: #92400e; }
        .badge-evening     { background: #ddd6fe; color: #5b21b6; }
        .badge-overnight   { background: #1e293b; color: #94a3b8; }
        .badge-holiday     { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }

        /* Filter pills */
        .filter-pill {
            padding: 0.35rem 0.9rem; border-radius: 9999px; font-size: 0.8rem;
            font-weight: 500; cursor: pointer; border: 1px solid #e5e7eb;
            transition: all 0.15s; white-space: nowrap;
        }
        .filter-pill.active { background: #003366; color: white; border-color: #003366; }
        .filter-pill:not(.active):hover { background: #f3f4f6; }

        /* Summary panel */
        .summary-stat { display: flex; justify-content: space-between; align-items: baseline; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .summary-stat:last-of-type { border-bottom: none; }

        /* Form inputs */
        .form-input, .form-select {
            width: 100%; padding: 0.625rem 1rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153,204,51,0.2);
        }
        .form-textarea {
            width: 100%; padding: 0.625rem 1rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; resize: vertical; min-height: 72px;
        }
        .form-textarea:focus { outline: none; border-color: #99CC33; box-shadow: 0 0 0 3px rgba(153,204,51,0.2); }

        /* Toast */
        .toast {
            position: fixed; top: 20px; right: 20px;
            padding: 1rem 1.5rem; background: white; border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12); z-index: 100;
            transform: translateX(420px); transition: transform 0.3s ease;
            border-left: 4px solid #99CC33; min-width: 280px;
        }
        .toast.show  { transform: translateX(0); }
        .toast.error { border-left-color: #EF4444; }

        /* Empty / loading states */
        .state-box { background: white; border-radius: 0.75rem; padding: 3rem 2rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

        /* Mobile bottom summary bar */
        @media (max-width: 1024px) {
            .summary-panel { display: none; }
            .mobile-summary-bar { display: flex !important; }
        }
        .mobile-summary-bar {
            display: none; position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #e5e7eb; padding: 0.75rem 1rem;
            z-index: 40; gap: 1rem; align-items: center; box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>
    <?php include 'includes/sidebar.php'; ?>

    <div class="lg:ml-64 pb-24 lg:pb-0">
        <!-- Top Nav -->
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div class="hidden lg:flex items-center gap-2 text-sm text-gray-500">
                            <a href="payrolls" class="hover:text-[#003366]">Payroll</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="font-semibold text-gray-700">Create Payroll</span>
                        </div>
                        <span class="lg:hidden text-sm font-bold text-[#003366]">Create Payroll</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="payrolls" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition flex items-center gap-2">
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
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-black">Create Payroll</h1>
                <p class="text-gray-500 mt-1 text-sm">Search schedules by date range, select what to include, and generate the payroll run.</p>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Select Date Range</h2>
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="startDate" class="form-input">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="endDate" class="form-input">
                    </div>
                    <button onclick="searchSchedules()" id="searchBtn" class="px-5 py-2.5 bg-[#003366] text-white rounded-lg text-sm font-medium hover:bg-[#002244] transition flex items-center gap-2">
                        <i class="fas fa-search"></i>
                        Search Schedules
                    </button>
                </div>
                <!-- Quick Presets -->
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="text-xs text-gray-400 self-center mr-1">Quick:</span>
                    <button onclick="setPreset('thisWeek')"   class="filter-pill">This Week</button>
                    <button onclick="setPreset('lastWeek')"   class="filter-pill">Last Week</button>
                    <button onclick="setPreset('thisMonth')"  class="filter-pill">This Month</button>
                    <button onclick="setPreset('lastMonth')"  class="filter-pill">Last Month</button>
                    <button onclick="setPreset('thisYear')"   class="filter-pill">This Year</button>
                </div>
            </div>

            <!-- Results area + Summary panel -->
            <div class="flex gap-6 items-start">

                <!-- ── Left: Schedule List ── -->
                <div class="flex-1 min-w-0">

                    <!-- Initial empty state -->
                    <div id="stateInitial" class="state-box">
                        <i class="fas fa-calendar-search text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500 font-medium">No search performed yet</p>
                        <p class="text-gray-400 text-sm mt-1">Select a date range above and click Search Schedules.</p>
                    </div>

                    <!-- Loading state -->
                    <div id="stateLoading" class="state-box hidden">
                        <i class="fas fa-spinner fa-spin text-3xl text-[#99CC33] mb-3 block"></i>
                        <p class="text-gray-500">Loading schedules...</p>
                    </div>

                    <!-- Empty results state -->
                    <div id="stateEmpty" class="state-box hidden">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500 font-medium">No schedules found</p>
                        <p class="text-gray-400 text-sm mt-1">Try a different date range.</p>
                    </div>

                    <!-- Results -->
                    <div id="stateResults" class="hidden">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-sm px-5 py-3 mb-4 flex flex-wrap gap-3 items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" id="globalCheckbox" onchange="handleGlobalCheckbox()" class="w-4 h-4">
                                    <span class="text-sm font-semibold text-gray-700">Select All Pending</span>
                                </label>
                                <span class="text-xs text-gray-400" id="selectionLabel">0 selected</span>
                            </div>
                            <div class="flex flex-wrap gap-2 items-center">
                                <!-- Filter pills -->
                                <button onclick="setFilter('all')"     id="pill-all"     class="filter-pill active">All</button>
                                <button onclick="setFilter('pending')" id="pill-pending" class="filter-pill">Pending Only</button>
                                <button onclick="setFilter('processed')" id="pill-processed" class="filter-pill">Processed</button>
                                <!-- Staff search -->
                                <div class="relative">
                                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" id="staffSearch" placeholder="Filter staff..." oninput="filterStaff()"
                                        class="pl-7 pr-3 py-1.5 border border-gray-200 rounded-lg text-sm w-36 focus:outline-none focus:border-[#99CC33]">
                                </div>
                            </div>
                        </div>

                        <!-- Staff Groups -->
                        <div id="staffGroups"></div>
                    </div>
                </div>

                <!-- ── Right: Summary Panel (desktop) ── -->
                <div class="w-80 flex-shrink-0 hidden lg:block">
                    <div class="summary-panel bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-bold text-[#003366] text-base mb-4 flex items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-[#99CC33]"></i>
                            Payroll Summary
                        </h3>

                        <div id="summaryEmpty" class="text-center py-4 text-gray-400 text-sm">
                            Search and select schedules to see a summary.
                        </div>

                        <div id="summaryDetails" class="hidden">
                            <div class="summary-stat">
                                <span class="text-sm text-gray-500">Selected Schedules</span>
                                <span class="font-bold text-[#003366]" id="sumSchedules">0</span>
                            </div>
                            <div class="summary-stat">
                                <span class="text-sm text-gray-500">Staff Included</span>
                                <span class="font-bold text-[#003366]" id="sumStaff">0</span>
                            </div>
                            <div class="summary-stat">
                                <span class="text-sm text-gray-500">Total Hours</span>
                                <span class="font-bold text-[#003366]" id="sumHours">0.00</span>
                            </div>
                            <div class="summary-stat">
                                <span class="text-sm text-gray-500">Holiday Pay</span>
                                <span class="font-bold text-amber-600" id="sumHoliday">$0.00</span>
                            </div>
                            <div class="summary-stat mb-2">
                                <span class="text-sm font-semibold text-gray-700">Estimated Total</span>
                                <span class="font-bold text-lg text-[#99CC33]" id="sumAmount">$0.00</span>
                            </div>

                            <div class="border-t border-gray-100 pt-4 mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Period</label>
                                <div class="flex gap-2 mb-4">
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-400 mb-0.5">From</p>
                                        <p class="text-sm font-semibold text-[#003366]" id="sumPeriodStart">—</p>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-400 mb-0.5">To</p>
                                        <p class="text-sm font-semibold text-[#003366]" id="sumPeriodEnd">—</p>
                                    </div>
                                </div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                                <textarea id="payrollNotes" class="form-textarea" placeholder="e.g. March week 2 pay run..."></textarea>
                            </div>

                            <button onclick="submitPayroll()" id="createBtn"
                                class="w-full mt-4 px-4 py-3 bg-[#99CC33] text-white rounded-lg font-semibold hover:bg-[#88BB22] transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plus-circle"></i>
                                Create Payroll
                            </button>
                        </div>
                    </div>
                </div>

            </div><!-- end flex -->
        </main>
    </div>

    <!-- Mobile Summary Bar -->
    <div class="mobile-summary-bar" id="mobileSummaryBar">
        <div class="flex-1">
            <p class="text-xs text-gray-500">Selected: <strong id="mobSelCount">0</strong> &nbsp;|&nbsp; Total: <strong class="text-[#99CC33]" id="mobSelAmount">$0.00</strong></p>
        </div>
        <button onclick="openMobileModal()" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar"></i>
            Review & Create
        </button>
    </div>

    <!-- Mobile Summary Modal -->
    <div id="mobileModal" class="hidden fixed inset-0 z-50 flex items-end">
        <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeMobileModal()"></div>
        <div class="relative bg-white w-full rounded-t-2xl p-6 z-10" style="max-height: 80vh; overflow-y: auto;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-[#003366]">Payroll Summary</h3>
                <button onclick="closeMobileModal()" class="text-gray-400"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="space-y-3 mb-4">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Schedules</span><strong id="mobModalSchedules">0</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Staff</span><strong id="mobModalStaff">0</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Hours</span><strong id="mobModalHours">0.00</strong></div>
                <div class="flex justify-between text-sm font-semibold border-t pt-3"><span>Estimated Total</span><span class="text-[#99CC33] text-base" id="mobModalAmount">$0.00</span></div>
            </div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="mobileNotes" class="form-textarea mb-4" placeholder="Optional notes..."></textarea>
            <button onclick="submitPayroll(true)" class="w-full py-3 bg-[#99CC33] text-white rounded-lg font-semibold hover:bg-[#88BB22] transition">
                <i class="fas fa-plus-circle mr-2"></i>Create Payroll
            </button>
        </div>
    </div>

    <!-- Confirm Create Payroll Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeConfirmModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6 animate-[slideIn_0.2s_ease]">
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-[#99CC33] bg-opacity-15 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-file-invoice-dollar text-[#99CC33] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Confirm Payroll Creation</h3>
                <p class="text-sm text-gray-500 mt-1">Please review the summary before proceeding.</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 mb-5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Period</span>
                    <span class="font-semibold" id="confirmPeriod">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Schedules Selected</span>
                    <span class="font-semibold" id="confirmSchedules">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Staff</span>
                    <span class="font-semibold" id="confirmStaff">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Hours</span>
                    <span class="font-semibold" id="confirmHours">0.00 hrs</span>
                </div>
                <div class="flex justify-between border-t pt-2 mt-1">
                    <span class="text-gray-700 font-semibold">Estimated Total</span>
                    <span class="text-[#99CC33] font-bold text-base" id="confirmAmount">$0.00</span>
                </div>
            </div>
            <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-3 mb-5">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Once created, the selected schedules will be marked as processed and cannot be added to another payroll.
            </p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button onclick="doCreatePayroll()" id="confirmCreateBtn" class="flex-1 px-4 py-2.5 bg-[#99CC33] text-white rounded-lg text-sm font-semibold hover:bg-[#88BB22] transition flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    Yes, Create Payroll
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="flex items-center gap-3">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] text-xl"></i>
            <div>
                <p id="toastTitle" class="font-semibold text-gray-800 text-sm"></p>
                <p id="toastMessage" class="text-xs text-gray-500"></p>
            </div>
        </div>
    </div>

    <script>
        // ─── State ───────────────────────────────────────────────────────────────
        let allGroups    = [];          // raw API response groups
        let schData      = {};          // scheduleId → { hours, amount, holiday_pay, user_id }
        let selectedIds  = new Set();   // currently checked (pending) schedule IDs
        let activeFilter = 'all';       // 'all' | 'pending' | 'processed'
        let staffFilter  = '';

        // ─── Init ─────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date();
            const y = today.getFullYear(), m = String(today.getMonth() + 1).padStart(2,'0');
            const last = new Date(y, today.getMonth() + 1, 0).getDate();
            document.getElementById('startDate').value = `${y}-${m}-01`;
            document.getElementById('endDate').value   = `${y}-${m}-${String(last).padStart(2,'0')}`;
        });

        // ─── Date Presets ─────────────────────────────────────────────────────────
        function setPreset(preset) {
            const now   = new Date();
            const y     = now.getFullYear();
            const month = now.getMonth();
            let start, end;

            if (preset === 'thisWeek') {
                const day = now.getDay() || 7;
                start = new Date(now); start.setDate(now.getDate() - day + 1);
                end   = new Date(now); end.setDate(now.getDate() - day + 7);
            } else if (preset === 'lastWeek') {
                const day = now.getDay() || 7;
                start = new Date(now); start.setDate(now.getDate() - day - 6);
                end   = new Date(now); end.setDate(now.getDate() - day);
            } else if (preset === 'thisMonth') {
                start = new Date(y, month, 1);
                end   = new Date(y, month + 1, 0);
            } else if (preset === 'lastMonth') {
                start = new Date(y, month - 1, 1);
                end   = new Date(y, month, 0);
            } else if (preset === 'thisYear') {
                start = new Date(y, 0, 1);
                end   = new Date(y, 11, 31);
            }
            document.getElementById('startDate').value = toInputDate(start);
            document.getElementById('endDate').value   = toInputDate(end);
        }

        function toInputDate(d) {
            return d.getFullYear() + '-' +
                String(d.getMonth()+1).padStart(2,'0') + '-' +
                String(d.getDate()).padStart(2,'0');
        }

        // ─── Search ───────────────────────────────────────────────────────────────
        function searchSchedules() {
            const start = document.getElementById('startDate').value;
            const end   = document.getElementById('endDate').value;
            if (!start || !end) { showToast('Error', 'Please select both start and end dates.', 'error'); return; }
            if (start > end)    { showToast('Error', 'Start date cannot be after end date.', 'error'); return; }

            setState('loading');
            selectedIds.clear();

            $.ajax({
                url: 'fetch_schedules_for_payroll',
                method: 'POST',
                data: { startDate: start, endDate: end },
                dataType: 'json',
                success: function(res) {
                    if (!res.status) { setState('empty'); showToast('Error', res.message, 'error'); return; }
                    if (!res.groups || res.groups.length === 0) { setState('empty'); return; }
                    allGroups = res.groups;
                    buildSchData();
                    setState('results');
                    renderGroups();
                    updateSummary();
                    updatePeriodDisplay();
                },
                error: function() { setState('empty'); showToast('Error', 'Connection error.', 'error'); }
            });
        }

        function buildSchData() {
            schData = {};
            allGroups.forEach(g => {
                g.schedules.forEach(s => {
                    schData[s.schedule_id] = {
                        hours:       parseFloat(s.hours_worked    || 0),
                        amount:      parseFloat(s.estimated_amount || 0),
                        holiday_pay: parseFloat(s.holiday_pay      || 0),
                        user_id:     s.user_id,
                        payroll_id:  s.payroll_id
                    };
                });
            });
        }

        // ─── State Display ────────────────────────────────────────────────────────
        function setState(state) {
            document.getElementById('stateInitial').classList.add('hidden');
            document.getElementById('stateLoading').classList.add('hidden');
            document.getElementById('stateEmpty').classList.add('hidden');
            document.getElementById('stateResults').classList.add('hidden');
            if (state === 'initial')  document.getElementById('stateInitial').classList.remove('hidden');
            if (state === 'loading')  document.getElementById('stateLoading').classList.remove('hidden');
            if (state === 'empty')    document.getElementById('stateEmpty').classList.remove('hidden');
            if (state === 'results')  document.getElementById('stateResults').classList.remove('hidden');
        }

        // ─── Filter ───────────────────────────────────────────────────────────────
        function setFilter(f) {
            activeFilter = f;
            ['all','pending','processed'].forEach(p => {
                const el = document.getElementById('pill-' + p);
                if (el) el.classList.toggle('active', p === f);
            });
            renderGroups();
        }

        function filterStaff() {
            staffFilter = document.getElementById('staffSearch').value.toLowerCase();
            renderGroups();
        }

        // ─── Render Groups ────────────────────────────────────────────────────────
        function renderGroups() {
            const container = document.getElementById('staffGroups');
            container.innerHTML = '';

            const filtered = allGroups.filter(g => {
                if (staffFilter && !g.staff_name.toLowerCase().includes(staffFilter)) return false;
                if (activeFilter === 'pending')   return g.pending_count  > 0;
                if (activeFilter === 'processed') return (g.total_count - g.pending_count) > 0;
                return true;
            });

            if (filtered.length === 0) {
                container.innerHTML = `<div class="state-box"><i class="fas fa-filter text-3xl text-gray-300 mb-3 block"></i><p class="text-gray-400">No groups match the current filter.</p></div>`;
                return;
            }

            filtered.forEach(g => container.appendChild(buildGroupEl(g)));
        }

        function buildGroupEl(group) {
            const div = document.createElement('div');
            div.className = 'staff-group';
            div.id = `group-${group.staff_id}`;

            // Filter schedules based on activeFilter
            const schedules = group.schedules.filter(s => {
                if (activeFilter === 'pending')   return !s.payroll_id;
                if (activeFilter === 'processed') return  !!s.payroll_id;
                return true;
            });

            const initials = group.staff_name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
            const pendingInGroup = schedules.filter(s => !s.payroll_id).length;
            const selInGroup = schedules.filter(s => !s.payroll_id && selectedIds.has(s.schedule_id)).length;

            // Determine group checkbox state
            const allChecked = pendingInGroup > 0 && selInGroup === pendingInGroup;
            const partial    = selInGroup > 0 && selInGroup < pendingInGroup;

            // Per-group selected totals
            const gHours  = schedules.filter(s => selectedIds.has(s.schedule_id)).reduce((a,s) => a + parseFloat(s.hours_worked||0), 0);
            const gAmount = schedules.filter(s => selectedIds.has(s.schedule_id)).reduce((a,s) => a + parseFloat(s.estimated_amount||0), 0);

            const hasSel = selInGroup > 0;

            div.innerHTML = `
                <div class="staff-header ${hasSel ? 'has-selection' : ''}" onclick="toggleGroup(${group.staff_id}, event)">
                    <input type="checkbox" id="grp-chk-${group.staff_id}"
                        onclick="event.stopPropagation()"
                        onchange="handleGroupCheckbox(${group.staff_id})"
                        ${pendingInGroup === 0 ? 'disabled title="No pending schedules"' : ''}
                        class="w-4 h-4 flex-shrink-0">
                    <div class="w-9 h-9 rounded-full bg-[#003366] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        ${escHtml(initials)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">${escHtml(group.staff_name)}</p>
                        <p class="text-xs text-gray-400 capitalize">${group.staff_role} &bull; ${schedules.length} schedule${schedules.length !== 1 ? 's' : ''}</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-4 text-right flex-shrink-0">
                        <div>
                            <p class="text-xs text-gray-400">Pending</p>
                            <p class="text-sm font-semibold ${pendingInGroup > 0 ? 'text-[#003366]' : 'text-gray-400'}">${pendingInGroup}</p>
                        </div>
                        ${hasSel ? `
                        <div>
                            <p class="text-xs text-gray-400">Selected hrs</p>
                            <p class="text-sm font-semibold text-[#99CC33]">${gHours.toFixed(2)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Est. Amount</p>
                            <p class="text-sm font-semibold text-[#99CC33]">$${gAmount.toFixed(2)}</p>
                        </div>` : ''}
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0" id="chevron-${group.staff_id}"></i>
                </div>
                <div class="staff-schedules" id="schedules-${group.staff_id}">
                    <!-- Column headers -->
                    <div class="schedule-row bg-gray-50 border-b border-gray-200" style="font-size:0.7rem; color:#6b7280; text-transform:uppercase; font-weight:600; letter-spacing:0.04em;">
                        <div></div>
                        <div>Date / Time</div>
                        <div class="col-client">Client</div>
                        <div class="col-shift">Shift</div>
                        <div>Hours</div>
                        <div>Amount</div>
                    </div>
                    ${schedules.map(s => buildScheduleRow(s)).join('')}
                </div>
            `;

            // Set checkbox states
            const chk = div.querySelector(`#grp-chk-${group.staff_id}`);
            if (chk && !chk.disabled) {
                chk.checked       = allChecked;
                chk.indeterminate = partial;
            }

            // Set schedule row checkbox states
            schedules.forEach(s => {
                const rowChk = div.querySelector(`#chk-${s.schedule_id}`);
                if (rowChk && !rowChk.disabled) {
                    rowChk.checked = selectedIds.has(s.schedule_id);
                }
            });

            return div;
        }

        function buildScheduleRow(s) {
            const isProcessed = !!s.payroll_id;
            const isSelected  = !isProcessed && selectedIds.has(s.schedule_id);
            const hours   = parseFloat(s.hours_worked    || 0).toFixed(2);
            const amount  = parseFloat(s.estimated_amount || 0).toFixed(2);
            const date    = formatDate(s.schedule_date);
            const timeStr = (s.start_time_fmt || '') + ' – ' + (s.end_time_fmt || '');

            const isHoliday  = parseFloat(s.holiday_pay) > 0;
            const shiftClass = { 'day': 'badge-day', 'evening': 'badge-evening', 'overnight': 'badge-overnight' }[s.shift_type] || 'badge-day';
            const shiftLabel = s.shift_type + (s.overnight_type && s.overnight_type !== 'none' ? ` (${s.overnight_type})` : '');

            return `
                <div class="schedule-row ${isSelected ? 'is-selected' : ''} ${isProcessed ? 'is-processed' : ''}"
                     id="row-${s.schedule_id}">
                    <div>
                        <input type="checkbox" id="chk-${s.schedule_id}"
                            data-schedule-id="${s.schedule_id}"
                            data-staff-id="${s.user_id}"
                            ${isProcessed ? 'disabled' : ''}
                            onchange="handleRowCheckbox(${s.schedule_id}, ${s.user_id})">
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${date}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${timeStr}</p>
                        ${isHoliday ? `<span class="badge badge-holiday mt-1"><i class="fas fa-sun mr-1"></i>Holiday Pay</span>` : ''}
                    </div>
                    <div class="col-client">
                        <p class="text-sm text-gray-700 truncate">${escHtml(s.client_name)}</p>
                        ${s.client_location ? `<p class="text-xs text-gray-400 truncate">${escHtml(s.client_location)}</p>` : ''}
                    </div>
                    <div class="col-shift">
                        <span class="badge ${shiftClass} capitalize">${shiftLabel}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${hours} hrs</p>
                    </div>
                    <div>
                        ${isProcessed
                            ? `<span class="badge badge-processed">Processed</span>`
                            : `<p class="text-sm font-bold text-[#003366]">$${amount}</p>`
                        }
                    </div>
                </div>
            `;
        }

        // ─── Checkbox Logic ───────────────────────────────────────────────────────
        function handleGlobalCheckbox() {
            const chk = document.getElementById('globalCheckbox');
            allGroups.forEach(g => {
                g.schedules.forEach(s => {
                    if (!s.payroll_id) {
                        if (chk.checked) selectedIds.add(s.schedule_id);
                        else             selectedIds.delete(s.schedule_id);
                    }
                });
            });
            renderGroups();
            updateSummary();
        }

        function handleGroupCheckbox(staffId) {
            const chk   = document.getElementById(`grp-chk-${staffId}`);
            const group = allGroups.find(g => g.staff_id == staffId);
            if (!group) return;

            const visible = group.schedules.filter(s => {
                if (activeFilter === 'pending')   return !s.payroll_id;
                if (activeFilter === 'processed') return !!s.payroll_id;
                return true;
            });

            visible.forEach(s => {
                if (!s.payroll_id) {
                    if (chk.checked) selectedIds.add(s.schedule_id);
                    else             selectedIds.delete(s.schedule_id);
                }
            });

            renderGroups();
            updateSummary();
        }

        function handleRowCheckbox(scheduleId, staffId) {
            const chk = document.getElementById(`chk-${scheduleId}`);
            if (chk.checked) selectedIds.add(scheduleId);
            else             selectedIds.delete(scheduleId);

            // Update row highlight
            const row = document.getElementById(`row-${scheduleId}`);
            if (row) row.classList.toggle('is-selected', chk.checked);

            // Sync group checkbox
            syncGroupCheckbox(staffId);
            syncGlobalCheckbox();
            updateSummary();
        }

        function syncGroupCheckbox(staffId) {
            const group = allGroups.find(g => g.staff_id == staffId);
            if (!group) return;
            const chk = document.getElementById(`grp-chk-${staffId}`);
            if (!chk || chk.disabled) return;

            const pending = group.schedules.filter(s => !s.payroll_id);
            const sel     = pending.filter(s => selectedIds.has(s.schedule_id));

            chk.checked       = sel.length > 0 && sel.length === pending.length;
            chk.indeterminate = sel.length > 0 && sel.length < pending.length;

            // Update header border
            const header = document.querySelector(`#group-${staffId} .staff-header`);
            if (header) header.classList.toggle('has-selection', sel.length > 0);
        }

        function syncGlobalCheckbox() {
            const globalChk = document.getElementById('globalCheckbox');
            const allPending = Object.values(schData).filter(s => !s.payroll_id);
            const selPending  = allPending.filter(s => selectedIds.has(parseInt(Object.keys(schData).find(id => schData[id] === s))));

            // Re-count properly
            let totalPending = 0, totalSel = 0;
            allGroups.forEach(g => g.schedules.forEach(s => {
                if (!s.payroll_id) {
                    totalPending++;
                    if (selectedIds.has(s.schedule_id)) totalSel++;
                }
            }));

            globalChk.checked       = totalPending > 0 && totalSel === totalPending;
            globalChk.indeterminate = totalSel > 0 && totalSel < totalPending;
        }

        // ─── Summary ──────────────────────────────────────────────────────────────
        function updateSummary() {
            const count = selectedIds.size;
            const staffSet = new Set();
            let totalHours = 0, totalAmount = 0, totalHoliday = 0;

            selectedIds.forEach(id => {
                const d = schData[id];
                if (!d) return;
                staffSet.add(d.user_id);
                totalHours   += d.hours;
                totalAmount  += d.amount;
                totalHoliday += d.holiday_pay;
            });

            // Update selection label
            document.getElementById('selectionLabel').textContent =
                count === 0 ? '0 selected' : `${count} schedule${count !== 1 ? 's' : ''} selected`;

            // Desktop summary
            const hasSel = count > 0;
            document.getElementById('summaryEmpty').classList.toggle('hidden', hasSel || allGroups.length === 0);
            document.getElementById('summaryDetails').classList.toggle('hidden', !hasSel && allGroups.length === 0);

            if (hasSel || allGroups.length > 0) {
                document.getElementById('summaryEmpty').classList.toggle('hidden', hasSel);
                document.getElementById('summaryDetails').classList.toggle('hidden', !hasSel);
            }

            document.getElementById('sumSchedules').textContent = count;
            document.getElementById('sumStaff').textContent     = staffSet.size;
            document.getElementById('sumHours').textContent     = totalHours.toFixed(2) + ' hrs';
            document.getElementById('sumHoliday').textContent   = '$' + totalHoliday.toFixed(2);
            document.getElementById('sumAmount').textContent    = '$' + totalAmount.toLocaleString('en-CA', {minimumFractionDigits:2, maximumFractionDigits:2});

            // Mobile bar
            document.getElementById('mobSelCount').textContent  = count;
            document.getElementById('mobSelAmount').textContent = '$' + totalAmount.toFixed(2);
            document.getElementById('mobModalSchedules').textContent = count;
            document.getElementById('mobModalStaff').textContent     = staffSet.size;
            document.getElementById('mobModalHours').textContent     = totalHours.toFixed(2) + ' hrs';
            document.getElementById('mobModalAmount').textContent    = '$' + totalAmount.toFixed(2);

            // Enable/disable create button
            const btn = document.getElementById('createBtn');
            if (btn) btn.disabled = count === 0;
        }

        function updatePeriodDisplay() {
            const s = document.getElementById('startDate').value;
            const e = document.getElementById('endDate').value;
            document.getElementById('sumPeriodStart').textContent = s ? formatDate(s) : '—';
            document.getElementById('sumPeriodEnd').textContent   = e ? formatDate(e) : '—';
            // Show summary panel content once searched
            if (allGroups.length > 0) {
                document.getElementById('summaryEmpty').classList.remove('hidden');
            }
        }

        // ─── Collapse / Expand ────────────────────────────────────────────────────
        function toggleGroup(staffId, event) {
            if (event && event.target.type === 'checkbox') return;
            const panel   = document.getElementById(`schedules-${staffId}`);
            const chevron = document.getElementById(`chevron-${staffId}`);
            if (!panel) return;
            const isCollapsed = panel.classList.contains('collapsed');
            if (isCollapsed) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.classList.remove('collapsed');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(() => { panel.style.maxHeight = '0px'; });
                panel.classList.add('collapsed');
                chevron.style.transform = 'rotate(-90deg)';
            }
        }

        // ─── Submit Payroll ───────────────────────────────────────────────────────
        let _pendingFromMobile = false;

        function submitPayroll(fromMobile = false) {
            if (selectedIds.size === 0) {
                showToast('Warning', 'Please select at least one schedule.', 'warning');
                return;
            }
            _pendingFromMobile = fromMobile;

            // Build confirm modal summary from current summary values
            const start  = document.getElementById('startDate').value;
            const end    = document.getElementById('endDate').value;
            const fmt    = str => str ? new Date(str + 'T00:00:00').toLocaleDateString('en-CA', { year:'numeric', month:'short', day:'numeric' }) : '—';

            document.getElementById('confirmPeriod').textContent    = fmt(start) + ' – ' + fmt(end);
            document.getElementById('confirmSchedules').textContent = document.getElementById('sumSchedules').textContent;
            document.getElementById('confirmStaff').textContent     = document.getElementById('sumStaff').textContent;
            document.getElementById('confirmHours').textContent     = document.getElementById('sumHours').textContent + ' hrs';
            document.getElementById('confirmAmount').textContent    = document.getElementById('sumAmount').textContent;

            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function doCreatePayroll() {
            const fromMobile = _pendingFromMobile;
            closeConfirmModal();

            const notes = fromMobile
                ? document.getElementById('mobileNotes').value
                : document.getElementById('payrollNotes').value;

            const btn = fromMobile
                ? document.querySelector('#mobileModal button[onclick]')
                : document.getElementById('createBtn');

            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...'; }

            $.ajax({
                url: 'create_payroll_from_selection',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    period_start:  document.getElementById('startDate').value,
                    period_end:    document.getElementById('endDate').value,
                    schedule_ids:  Array.from(selectedIds),
                    notes:         notes
                }),
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        showToast('Payroll Created!', res.message, 'success');
                        if (fromMobile) closeMobileModal();
                        setTimeout(() => { window.location.href = 'payrolls'; }, 2000);
                    } else {
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Create Payroll'; }
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function() {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Create Payroll'; }
                    showToast('Error', 'Request failed. Please try again.', 'error');
                }
            });
        }

        // ─── Mobile Modal ─────────────────────────────────────────────────────────
        function openMobileModal()  { document.getElementById('mobileModal').classList.remove('hidden'); }
        function closeMobileModal() { document.getElementById('mobileModal').classList.add('hidden'); }

        // ─── Sidebar ──────────────────────────────────────────────────────────────
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('active');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
        }

        // ─── Toast ────────────────────────────────────────────────────────────────
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            document.getElementById('toastTitle').textContent   = title;
            document.getElementById('toastMessage').textContent = message;
            const icon = document.getElementById('toastIcon');
            if (type === 'success') { toast.className = 'toast'; icon.className = 'fas fa-check-circle text-[#99CC33] text-xl'; }
            else if (type === 'error') { toast.className = 'toast error'; icon.className = 'fas fa-times-circle text-red-500 text-xl'; }
            else { toast.className = 'toast'; icon.className = 'fas fa-exclamation-circle text-yellow-500 text-xl'; }
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        }

        // ─── Helpers ─────────────────────────────────────────────────────────────
        function formatDate(str) {
            if (!str) return '—';
            const d = new Date(str + (str.length === 10 ? 'T00:00:00' : ''));
            return d.toLocaleDateString('en-CA', { weekday:'short', month:'short', day:'numeric' });
        }
        function formatTime(str) {
            if (!str) return '—';
            // str can be full datetime or just time HH:MM:SS
            const parts = str.includes('T') ? str.split('T')[1] : str;
            const [h, m] = parts.split(':');
            const hr = parseInt(h);
            return (hr % 12 || 12) + ':' + m + (hr < 12 ? ' AM' : ' PM');
        }
        function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>
</body>
</html>
