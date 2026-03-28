<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Create Invoice</title>
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
            position: sticky; top: 80px;
            max-height: calc(100vh - 100px); overflow-y: auto;
        }

        /* Staff group */
        .staff-group { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 1rem; }
        .staff-header {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1rem 1.25rem; cursor: pointer;
            border-bottom: 2px solid transparent; transition: background 0.15s;
        }
        .staff-header:hover { background: #f9fafb; }
        .staff-header.has-selection { border-bottom-color: #003366; }
        .staff-schedules { overflow: hidden; transition: max-height 0.3s ease; }
        .staff-schedules.collapsed { max-height: 0 !important; }

        /* Schedule row – 6 columns: checkbox | date/time | shift | hours | amount */
        .schedule-row {
            display: grid;
            grid-template-columns: 32px 1fr 120px 80px 90px;
            align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #f3f4f6; transition: background 0.1s;
        }
        .schedule-row:last-child { border-bottom: none; }
        .schedule-row:hover { background: #fafafa; }
        .schedule-row.is-selected { background: #f0f9e0; }
        .schedule-row.is-processed { opacity: 0.55; background: #fafafa; }

        @media (max-width: 640px) {
            .schedule-row { grid-template-columns: 32px 1fr 80px 90px; }
            .col-shift { display: none; }
        }

        input[type="checkbox"] { width: 16px; height: 16px; accent-color: #99CC33; cursor: pointer; flex-shrink: 0; }
        input[type="checkbox"]:disabled { cursor: not-allowed; accent-color: #9ca3af; }

        .badge {
            padding: 0.2rem 0.6rem; border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600; display: inline-block; white-space: nowrap;
        }
        .badge-day      { background: #fef3c7; color: #92400e; }
        .badge-evening  { background: #ddd6fe; color: #5b21b6; }
        .badge-overnight { background: #1e293b; color: #94a3b8; }
        .badge-holiday  { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-processed { background: #ede9fe; color: #7c3aed; }

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

        /* Mobile bottom bar */
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
                            <a href="invoices" class="hover:text-[#003366]">Invoices</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="font-semibold text-gray-700">Create Invoice</span>
                        </div>
                        <span class="lg:hidden text-sm font-bold text-[#003366]">Create Invoice</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="invoices" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition flex items-center gap-2">
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
                <h1 class="text-2xl sm:text-3xl font-bold text-black">Create Invoice</h1>
                <p class="text-gray-500 mt-1 text-sm">Select a client and date range, pick the schedules to bill, and generate the invoice.</p>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Search Schedules</h2>
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="w-full sm:flex-1 sm:min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                        <select id="clientSelect" class="form-select">
                            <option value="">— Select a client —</option>
                            <?php
                            $clients_list = $counters['all_clients']['clients'] ?? [];
                            foreach ($clients_list as $c):
                                $name = htmlspecialchars($c['firstname'] . ' ' . $c['lastname']);
                                $rate = isset($c['bill_rate']) && $c['bill_rate'] !== null ? ' ($' . number_format($c['bill_rate'], 2) . '/hr)' : ' (no rate)';
                            ?>
                            <option value="<?= (int)$c['client_id'] ?>" data-rate="<?= htmlspecialchars($c['bill_rate'] ?? '0') ?>">
                                <?= $name . $rate ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" id="startDate" class="form-input">
                    </div>
                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" id="endDate" class="form-input">
                    </div>
                    <button onclick="searchSchedules()" id="searchBtn"
                        class="px-5 py-2.5 bg-[#003366] text-white rounded-lg text-sm font-medium hover:bg-[#002244] transition flex items-center gap-2">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </div>
                <!-- Quick Presets -->
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="text-xs text-gray-400 self-center mr-1">Quick:</span>
                    <button onclick="setPreset('thisWeek')"  class="filter-pill">This Week</button>
                    <button onclick="setPreset('lastWeek')"  class="filter-pill">Last Week</button>
                    <button onclick="setPreset('thisMonth')" class="filter-pill">This Month</button>
                    <button onclick="setPreset('lastMonth')" class="filter-pill">Last Month</button>
                    <button onclick="setPreset('thisYear')"  class="filter-pill">This Year</button>
                </div>
            </div>

            <!-- Results + Summary panel -->
            <div class="flex gap-6 items-start">

                <!-- Left: Schedule List -->
                <div class="flex-1 min-w-0">

                    <div id="stateInitial" class="state-box">
                        <i class="fas fa-file-invoice text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500 font-medium">No search performed yet</p>
                        <p class="text-gray-400 text-sm mt-1">Select a client and date range above, then click Search.</p>
                    </div>

                    <div id="stateLoading" class="state-box hidden">
                        <i class="fas fa-spinner fa-spin text-3xl text-[#99CC33] mb-3 block"></i>
                        <p class="text-gray-500">Loading schedules...</p>
                    </div>

                    <div id="stateEmpty" class="state-box hidden">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500 font-medium">No schedules found</p>
                        <p class="text-gray-400 text-sm mt-1">This client has no schedules in the selected date range.</p>
                    </div>

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
                                <button onclick="setFilter('all')"       id="pill-all"       class="filter-pill active">All</button>
                                <button onclick="setFilter('pending')"   id="pill-pending"   class="filter-pill">Pending Only</button>
                                <button onclick="setFilter('processed')" id="pill-processed" class="filter-pill">Invoiced</button>
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

                <!-- Right: Summary Panel (desktop) -->
                <div class="w-80 flex-shrink-0 hidden lg:block">
                    <div class="summary-panel bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-bold text-[#003366] text-base mb-4 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-[#99CC33]"></i>
                            Invoice Summary
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
                                <span class="text-sm text-gray-500">Subtotal</span>
                                <span class="font-bold text-[#003366]" id="sumSubtotal">$0.00</span>
                            </div>
                            <div class="summary-stat">
                                <span class="text-sm text-gray-500">GST (13%)</span>
                                <span class="font-bold text-gray-600" id="sumGst">$0.00</span>
                            </div>
                            <div class="summary-stat mb-2">
                                <span class="text-sm font-semibold text-gray-700">Total</span>
                                <span class="font-bold text-lg text-[#99CC33]" id="sumTotal">$0.00</span>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date</label>
                                <input type="date" id="invoiceDueDate" class="form-input mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                                <textarea id="invoiceNotes" class="form-textarea" placeholder="e.g. March billing cycle..."></textarea>
                            </div>

                            <button onclick="submitInvoice()" id="createBtn"
                                class="w-full mt-4 px-4 py-3 bg-[#99CC33] text-white rounded-lg font-semibold hover:bg-[#88BB22] transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plus-circle"></i>
                                Create Invoice
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
            <p class="text-xs text-gray-500">Selected</p>
            <p class="text-sm font-bold text-[#003366]"><span id="mobSchedules">0</span> schedules · <span id="mobTotal">$0.00</span></p>
        </div>
        <button onclick="openMobileModal()" id="mobOpenBtn"
            class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm font-semibold hover:bg-[#88BB22] transition disabled:opacity-40"
            disabled>
            <i class="fas fa-file-invoice mr-1"></i> Review & Create
        </button>
    </div>

    <!-- Mobile Summary Modal -->
    <div id="mobileModal" class="hidden fixed inset-0 z-50 flex items-end">
        <div class="absolute inset-0 bg-black bg-opacity-40" onclick="closeMobileModal()"></div>
        <div class="relative bg-white rounded-t-2xl w-full p-5 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-black">Invoice Summary</h3>
                <button onclick="closeMobileModal()" class="text-gray-400"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="space-y-2.5 mb-4">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Schedules</span><strong id="mobModalSchedules">0</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Staff</span><strong id="mobModalStaff">0</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Hours</span><strong id="mobModalHours">0.00</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal</span><strong id="mobModalSubtotal">$0.00</strong></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">GST (13%)</span><strong id="mobModalGst">$0.00</strong></div>
                <div class="flex justify-between text-sm font-semibold border-t pt-2.5"><span>Total</span><span class="text-[#99CC33] text-base" id="mobModalTotal">$0.00</span></div>
            </div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date" id="mobileDueDate" class="form-input mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="mobileNotes" class="form-textarea mb-4" placeholder="Optional notes..."></textarea>
            <button onclick="submitInvoice(true)" class="w-full py-3 bg-[#99CC33] text-white rounded-lg font-semibold hover:bg-[#88BB22] transition">
                <i class="fas fa-plus-circle mr-2"></i>Create Invoice
            </button>
        </div>
    </div>

    <!-- Confirm Create Invoice Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeConfirmModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-[#003366] bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-file-invoice text-[#003366] text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Confirm Invoice Creation</h3>
                <p class="text-sm text-gray-500 mt-1">Please review the details before proceeding.</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 mb-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Client</span>
                    <span class="font-semibold text-right" id="confirmClient">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Period</span>
                    <span class="font-semibold" id="confirmPeriod">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Due Date</span>
                    <span class="font-semibold" id="confirmDueDate">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Schedules</span>
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
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-semibold" id="confirmSubtotal">$0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">GST (13%)</span>
                    <span class="font-semibold" id="confirmGst">$0.00</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="font-bold text-gray-800">Total</span>
                    <span class="font-bold text-[#99CC33] text-base" id="confirmTotal">$0.00</span>
                </div>
            </div>
            <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-3 mb-5">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Once created, selected schedules will be marked as invoiced and cannot be added to another invoice.
            </p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button onclick="doCreateInvoice()" id="confirmCreateBtn"
                    class="flex-1 px-4 py-2.5 bg-[#99CC33] text-white rounded-lg text-sm font-semibold hover:bg-[#88BB22] transition flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    Yes, Create Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="flex items-center gap-3">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] text-xl"></i>
            <div>
                <p id="toastTitle"   class="font-semibold text-gray-800"></p>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>

    <script>
        // ─── State ────────────────────────────────────────────────────────────────
        let allGroups   = [];
        let selectedIds = new Set();
        let activeFilter = 'all';
        let _pendingFromMobile = false;

        // ─── Init ─────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const now  = new Date();
            const y    = now.getFullYear();
            const m    = String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById('startDate').value = `${y}-${m}-01`;
            const last = new Date(y, now.getMonth() + 1, 0).getDate();
            document.getElementById('endDate').value   = `${y}-${m}-${String(last).padStart(2, '0')}`;
            // Default due date: 30 days from today
            const due = new Date(); due.setDate(due.getDate() + 30);
            const dy = due.getFullYear();
            const dm = String(due.getMonth() + 1).padStart(2, '0');
            const dd = String(due.getDate()).padStart(2, '0');
            const dueStr = `${dy}-${dm}-${dd}`;
            document.getElementById('invoiceDueDate').value = dueStr;
            document.getElementById('mobileDueDate').value  = dueStr;
        });

        // ─── Search ───────────────────────────────────────────────────────────────
        function searchSchedules() {
            const clientId = document.getElementById('clientSelect').value;
            const start    = document.getElementById('startDate').value;
            const end      = document.getElementById('endDate').value;

            if (!clientId) { showToast('Warning', 'Please select a client first.', 'warning'); return; }
            if (!start || !end) { showToast('Warning', 'Please select a date range.', 'warning'); return; }
            if (start > end)    { showToast('Warning', 'Start date must be before end date.', 'warning'); return; }

            selectedIds.clear();
            setState('loading');
            updateSummary();

            const btn = document.getElementById('searchBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching…';

            $.ajax({
                url: 'fetch_schedules_for_invoice',
                method: 'POST',
                data: { client_id: clientId, start_date: start, end_date: end },
                dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search"></i> Search';
                    if (!res.status) { showToast('Error', res.message, 'error'); setState('initial'); return; }
                    allGroups = res.groups || [];
                    if (allGroups.length === 0) { setState('empty'); return; }
                    setState('results');
                    renderGroups();
                    updateSummary();
                    updateSummaryPeriod();
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search"></i> Search';
                    showToast('Error', 'Request failed. Please try again.', 'error');
                    setState('initial');
                }
            });
        }

        // ─── State helpers ────────────────────────────────────────────────────────
        function setState(s) {
            ['stateInitial','stateLoading','stateEmpty','stateResults'].forEach(id => {
                document.getElementById(id).classList.toggle('hidden', id !== 'state' + s.charAt(0).toUpperCase() + s.slice(1));
            });
        }

        // ─── Filter ───────────────────────────────────────────────────────────────
        function setFilter(f) {
            activeFilter = f;
            ['all','pending','processed'].forEach(p => {
                document.getElementById('pill-' + p).classList.toggle('active', p === f);
            });
            renderGroups();
        }

        function filterStaff() {
            renderGroups();
        }

        // ─── Quick Presets ────────────────────────────────────────────────────────
        function setPreset(p) {
            const now = new Date(), y = now.getFullYear(), m = now.getMonth();
            let s, e;
            if (p === 'thisWeek')  { const d = now.getDay() || 7; s = new Date(now); s.setDate(now.getDate() - d + 1); e = new Date(s); e.setDate(s.getDate() + 6); }
            else if (p === 'lastWeek')  { const d = now.getDay() || 7; s = new Date(now); s.setDate(now.getDate() - d - 6); e = new Date(now); e.setDate(now.getDate() - d); }
            else if (p === 'thisMonth') { s = new Date(y, m, 1); e = new Date(y, m + 1, 0); }
            else if (p === 'lastMonth') { s = new Date(y, m - 1, 1); e = new Date(y, m, 0); }
            else                        { s = new Date(y, 0, 1); e = new Date(y, 11, 31); }
            document.getElementById('startDate').value = fmt(s);
            document.getElementById('endDate').value   = fmt(e);
        }
        function fmt(d) { return d.toISOString().slice(0, 10); }

        // ─── Render Groups ────────────────────────────────────────────────────────
        function renderGroups() {
            const container = document.getElementById('staffGroups');
            const query     = document.getElementById('staffSearch').value.toLowerCase();

            let filtered = allGroups.filter(g => {
                if (query && !g.staff_name.toLowerCase().includes(query)) return false;
                if (activeFilter === 'pending')   return g.schedules.some(s => !s.invoice_id);
                if (activeFilter === 'processed') return g.schedules.some(s =>  s.invoice_id);
                return true;
            });

            if (filtered.length === 0) {
                container.innerHTML = `<div class="state-box"><i class="fas fa-filter text-3xl text-gray-300 mb-3 block"></i><p class="text-gray-400">No groups match the current filter.</p></div>`;
                return;
            }
            container.innerHTML = '';
            filtered.forEach(g => container.appendChild(buildGroupEl(g)));
        }

        function buildGroupEl(group) {
            const div = document.createElement('div');
            div.className = 'staff-group';
            div.id = `group-${group.staff_id}`;

            let schedules = group.schedules;
            if (activeFilter === 'pending')   schedules = schedules.filter(s => !s.invoice_id);
            if (activeFilter === 'processed') schedules = schedules.filter(s =>  s.invoice_id);

            const selInGroup    = schedules.filter(s => !s.invoice_id && selectedIds.has(s.schedule_id)).length;
            const pendingInGroup = schedules.filter(s => !s.invoice_id).length;

            div.innerHTML = `
                <div class="staff-header ${selInGroup > 0 ? 'has-selection' : ''}" id="hdr-${group.staff_id}"
                     onclick="toggleGroup(${group.staff_id})">
                    <input type="checkbox" id="grp-chk-${group.staff_id}"
                        onclick="event.stopPropagation()"
                        onchange="handleGroupCheckbox(${group.staff_id})"
                        ${pendingInGroup === 0 ? 'disabled' : ''}>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900">${escHtml(group.staff_name)}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${schedules.length} schedule${schedules.length !== 1 ? 's' : ''} · ${pendingInGroup} pending</p>
                    </div>
                    <div class="text-right mr-2">
                        <p class="text-sm font-bold text-[#003366]">$${group.pending_amount.toFixed(2)}</p>
                        <p class="text-xs text-gray-400">${group.pending_hours.toFixed(2)} hrs</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform" id="chev-${group.staff_id}"></i>
                </div>
                <div class="staff-schedules" id="schedules-${group.staff_id}">
                    <!-- Column headers -->
                    <div class="schedule-row bg-gray-50 border-b border-gray-200" style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;font-weight:600;letter-spacing:0.04em;">
                        <div></div>
                        <div>Date / Time</div>
                        <div class="col-shift">Shift</div>
                        <div>Hours</div>
                        <div>Amount</div>
                    </div>
                    ${schedules.map(s => buildScheduleRow(s)).join('')}
                </div>
            `;

            // Set checkbox indeterminate state
            const chk = div.querySelector(`#grp-chk-${group.staff_id}`);
            if (selInGroup > 0 && selInGroup < pendingInGroup) chk.indeterminate = true;
            else chk.checked = pendingInGroup > 0 && selInGroup === pendingInGroup;

            // Set max-height for animation
            requestAnimationFrame(() => {
                const panel = div.querySelector(`#schedules-${group.staff_id}`);
                if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
            });

            return div;
        }

        function buildScheduleRow(s) {
            const isProcessed = !!s.invoice_id;
            const isSelected  = !isProcessed && selectedIds.has(s.schedule_id);
            const hours       = parseFloat(s.hours_worked    || 0).toFixed(2);
            const amount      = parseFloat(s.estimated_amount || 0).toFixed(2);
            const date        = formatDate(s.schedule_date);
            const timeStr     = (s.start_time_fmt || '') + ' – ' + (s.end_time_fmt || '');
            const isHoliday   = parseFloat(s.holiday_pay) > 0;

            const shiftClass  = { day: 'badge-day', evening: 'badge-evening', overnight: 'badge-overnight' }[s.shift_type] || 'badge-day';
            const shiftLabel  = s.shift_type + (s.overnight_type && s.overnight_type !== 'none' ? ` (${s.overnight_type})` : '');

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
                    <div class="col-shift">
                        <span class="badge ${shiftClass} capitalize">${shiftLabel}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${hours} hrs</p>
                    </div>
                    <div>
                        ${isProcessed
                            ? `<span class="badge badge-processed">Invoiced</span>`
                            : `<p class="text-sm font-bold text-[#003366]">$${amount}</p>`
                        }
                    </div>
                </div>
            `;
        }

        // ─── Checkbox Logic ───────────────────────────────────────────────────────
        function handleGlobalCheckbox() {
            const chk = document.getElementById('globalCheckbox');
            allGroups.forEach(g => g.schedules.forEach(s => {
                if (!s.invoice_id) {
                    if (chk.checked) selectedIds.add(s.schedule_id);
                    else             selectedIds.delete(s.schedule_id);
                }
            }));
            renderGroups();
            updateSummary();
        }

        function handleGroupCheckbox(staffId) {
            const chk   = document.getElementById(`grp-chk-${staffId}`);
            const group = allGroups.find(g => g.staff_id == staffId);
            if (!group) return;

            const visible = group.schedules.filter(s => {
                if (activeFilter === 'pending')   return !s.invoice_id;
                if (activeFilter === 'processed') return  s.invoice_id;
                return true;
            });

            visible.forEach(s => {
                if (!s.invoice_id) {
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

            const row = document.getElementById(`row-${scheduleId}`);
            if (row) row.classList.toggle('is-selected', chk.checked);

            syncGroupCheckbox(staffId);
            syncGlobalCheckbox();
            updateSummary();
        }

        function syncGroupCheckbox(staffId) {
            const group = allGroups.find(g => g.staff_id == staffId);
            if (!group) return;
            const pending = group.schedules.filter(s => !s.invoice_id);
            const sel     = pending.filter(s => selectedIds.has(s.schedule_id));
            const chk     = document.getElementById(`grp-chk-${staffId}`);
            if (!chk) return;
            chk.indeterminate = sel.length > 0 && sel.length < pending.length;
            chk.checked       = pending.length > 0 && sel.length === pending.length;

            const hdr = document.getElementById(`hdr-${staffId}`);
            if (hdr) hdr.classList.toggle('has-selection', sel.length > 0);
        }

        function syncGlobalCheckbox() {
            const allPending = allGroups.flatMap(g => g.schedules.filter(s => !s.invoice_id));
            const selCount   = allPending.filter(s => selectedIds.has(s.schedule_id)).length;
            const chk = document.getElementById('globalCheckbox');
            chk.indeterminate = selCount > 0 && selCount < allPending.length;
            chk.checked       = allPending.length > 0 && selCount === allPending.length;
        }

        // ─── Summary ──────────────────────────────────────────────────────────────
        function updateSummary() {
            let schedCount = 0, hours = 0, subtotal = 0;
            const staffSet = new Set();

            allGroups.forEach(g => g.schedules.forEach(s => {
                if (selectedIds.has(s.schedule_id)) {
                    schedCount++;
                    staffSet.add(s.user_id);
                    hours    += parseFloat(s.hours_worked    || 0);
                    subtotal += parseFloat(s.estimated_amount || 0);
                }
            }));

            hours    = Math.round(hours    * 100) / 100;
            subtotal = Math.round(subtotal * 100) / 100;
            const gst   = Math.round(subtotal * 0.13 * 100) / 100;
            const total = Math.round((subtotal + gst) * 100) / 100;

            const fmt2 = n => '$' + n.toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Desktop
            document.getElementById('sumSchedules').textContent = schedCount;
            document.getElementById('sumStaff').textContent     = staffSet.size;
            document.getElementById('sumHours').textContent     = hours.toFixed(2);
            document.getElementById('sumSubtotal').textContent  = fmt2(subtotal);
            document.getElementById('sumGst').textContent       = fmt2(gst);
            document.getElementById('sumTotal').textContent     = fmt2(total);

            const hasSelection = schedCount > 0;
            document.getElementById('summaryEmpty').classList.toggle('hidden', hasSelection);
            document.getElementById('summaryDetails').classList.toggle('hidden', !hasSelection);
            document.getElementById('createBtn').disabled = !hasSelection;

            // Mobile bar
            document.getElementById('mobSchedules').textContent = schedCount;
            document.getElementById('mobTotal').textContent     = fmt2(total);
            document.getElementById('mobOpenBtn').disabled      = !hasSelection;

            // Mobile modal
            document.getElementById('mobModalSchedules').textContent = schedCount;
            document.getElementById('mobModalStaff').textContent     = staffSet.size;
            document.getElementById('mobModalHours').textContent     = hours.toFixed(2);
            document.getElementById('mobModalSubtotal').textContent  = fmt2(subtotal);
            document.getElementById('mobModalGst').textContent       = fmt2(gst);
            document.getElementById('mobModalTotal').textContent     = fmt2(total);

            // Selection label
            document.getElementById('selectionLabel').textContent = `${schedCount} selected`;
        }

        function updateSummaryPeriod() {
            const fmtD = str => str ? new Date(str + 'T00:00:00').toLocaleDateString('en-CA', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
            document.getElementById('sumPeriodStart').textContent = fmtD(document.getElementById('startDate').value);
            document.getElementById('sumPeriodEnd').textContent   = fmtD(document.getElementById('endDate').value);
        }

        // ─── Toggle Group ─────────────────────────────────────────────────────────
        function toggleGroup(staffId) {
            const panel  = document.getElementById(`schedules-${staffId}`);
            const chevron = document.getElementById(`chev-${staffId}`);
            if (!panel) return;
            if (panel.classList.contains('collapsed')) {
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

        // ─── Submit Invoice ───────────────────────────────────────────────────────
        function submitInvoice(fromMobile = false) {
            if (selectedIds.size === 0) {
                showToast('Warning', 'Please select at least one schedule.', 'warning');
                return;
            }
            _pendingFromMobile = fromMobile;

            const clientSel  = document.getElementById('clientSelect');
            const clientName = clientSel.options[clientSel.selectedIndex]?.text || '—';
            const start      = document.getElementById('startDate').value;
            const end        = document.getElementById('endDate').value;
            const fmtD       = str => str ? new Date(str + 'T00:00:00').toLocaleDateString('en-CA', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

            const dueDate = fromMobile
                ? document.getElementById('mobileDueDate').value
                : document.getElementById('invoiceDueDate').value;

            document.getElementById('confirmClient').textContent    = clientName;
            document.getElementById('confirmPeriod').textContent    = fmtD(start) + ' – ' + fmtD(end);
            document.getElementById('confirmDueDate').textContent   = dueDate ? fmtD(dueDate) : '—';
            document.getElementById('confirmSchedules').textContent = document.getElementById('sumSchedules').textContent;
            document.getElementById('confirmStaff').textContent     = document.getElementById('sumStaff').textContent;
            document.getElementById('confirmHours').textContent     = document.getElementById('sumHours').textContent + ' hrs';
            document.getElementById('confirmSubtotal').textContent  = document.getElementById('sumSubtotal').textContent;
            document.getElementById('confirmGst').textContent       = document.getElementById('sumGst').textContent;
            document.getElementById('confirmTotal').textContent     = document.getElementById('sumTotal').textContent;

            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function doCreateInvoice() {
            const fromMobile = _pendingFromMobile;
            closeConfirmModal();

            const notes = fromMobile
                ? document.getElementById('mobileNotes').value
                : document.getElementById('invoiceNotes').value;
            const due_date = fromMobile
                ? document.getElementById('mobileDueDate').value
                : document.getElementById('invoiceDueDate').value;

            const btn = fromMobile
                ? document.querySelector('#mobileModal button[onclick]')
                : document.getElementById('createBtn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating…'; }

            $.ajax({
                url: 'create_invoice_from_selection',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    client_id:    document.getElementById('clientSelect').value,
                    period_start: document.getElementById('startDate').value,
                    period_end:   document.getElementById('endDate').value,
                    schedule_ids: Array.from(selectedIds),
                    due_date:     due_date,
                    notes:        notes
                }),
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        showToast('Invoice Created!', res.message, 'success');
                        if (fromMobile) closeMobileModal();
                        setTimeout(() => { window.location.href = 'invoices'; }, 2000);
                    } else {
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Create Invoice'; }
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Create Invoice'; }
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
            return d.toLocaleDateString('en-CA', { weekday: 'short', month: 'short', day: 'numeric' });
        }
        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>
</body>
</html>
