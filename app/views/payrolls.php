<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Payroll Management</title>
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
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 30; opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        .overlay.active { opacity: 1; visibility: visible; }

        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }

        .modal {
            display: none; position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 50; justify-content: center; align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background-color: white; border-radius: 0.75rem;
            max-width: 800px; width: 90%; max-height: 90vh;
            overflow-y: auto; animation: slideIn 0.3s ease;
        }
        .modal-content.wide { max-width: 1000px; }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }

        .table-container {
            background: white; border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); overflow-x: auto; overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }
        .payroll-table { width: 100%; min-width: 820px; border-collapse: collapse; }
        .payroll-table th {
            background: #f9fafb; padding: 1rem 1.5rem; text-align: left;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            color: #6b7280; border-bottom: 1px solid #e5e7eb;
        }
        .payroll-table td {
            padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;
            color: #374151; font-size: 0.875rem;
        }
        .payroll-table tbody tr:hover { background: #f9fafb; }

        .schedules-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        .schedules-table th {
            background: #f3f4f6; padding: 0.625rem 1rem; text-align: left;
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
            color: #6b7280; border-bottom: 1px solid #e5e7eb;
        }
        .schedules-table td { padding: 0.625rem 1rem; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .schedules-table tbody tr:hover { background: #fafafa; }

        .status-badge {
            padding: 0.25rem 0.75rem; border-radius: 9999px;
            font-size: 0.75rem; font-weight: 600; display: inline-block;
        }
        .status-draft      { background: #f3f4f6; color: #6b7280; }
        .status-processed  { background: #dbeafe; color: #1d4ed8; }
        .status-paid       { background: #dcfce7; color: #15803d; }
        .status-cancelled  { background: #fee2e2; color: #ef4444; }

        .sched-badge {
            padding: 0.15rem 0.5rem; border-radius: 9999px;
            font-size: 0.7rem; font-weight: 600; display: inline-block;
        }
        .sched-scheduled  { background: #e0f2fe; color: #0369a1; }
        .sched-in-progress { background: #fef9c3; color: #a16207; }
        .sched-completed   { background: #dcfce7; color: #15803d; }
        .sched-cancelled   { background: #fee2e2; color: #ef4444; }
        .sched-no-show     { background: #f3f4f6; color: #6b7280; }

        .action-btn { padding: 0.5rem; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer; }
        .action-btn:hover { background-color: #f3f4f6; }
        .action-btn.view:hover        { color: #003366; }
        .action-btn.change-status:hover { color: #7c3aed; }
        .action-btn.delete:hover      { color: #EF4444; }

        .filter-bar {
            display: flex; flex-wrap: wrap; gap: 1rem;
            align-items: center; justify-content: space-between; margin-bottom: 1.5rem;
        }
        .search-box { flex: 1; min-width: 280px; position: relative; }
        .search-box input {
            width: 100%; padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;
        }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .filter-select {
            padding: 0.625rem 2rem 0.625rem 1rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; background-color: white; cursor: pointer;
        }

        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 0.625rem 1rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;
        }
        .form-textarea { resize: vertical; min-height: 80px; }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none; border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153,204,51,0.2);
        }

        .toast {
            position: fixed; top: 20px; right: 20px;
            padding: 1rem 1.5rem; background-color: white;
            border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            z-index: 100; transform: translateX(400px);
            transition: transform 0.3s ease; border-left: 4px solid #99CC33;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { border-left-color: #99CC33; }
        .toast.error   { border-left-color: #EF4444; }
        .toast.warning { border-left-color: #F59E0B; }

        @media (max-width: 768px) {
            .filter-bar { flex-direction: column; align-items: stretch; }
            .search-box { min-width: 100%; }
            .payroll-table th, .payroll-table td { padding: 0.75rem 1rem; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>

    <?php include 'includes/sidebar.php'; ?>

    <div class="lg:ml-64">
        <!-- Navbar -->
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
                        <i class="fas fa-money-bill-wave text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Payroll</h2>
                    </div>
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
        </nav>

        <!-- Main Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Payroll</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage and generate staff pay runs</p>
                </div>
                <a href="create_payroll" class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Generate Payroll
                </a>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Payrolls</p>
                            <p class="text-2xl font-bold text-black" id="statTotal">0</p>
                        </div>
                        <div class="bg-[#003366] bg-opacity-10 p-3 rounded-lg">
                            <i class="fas fa-money-bill-wave text-[#003366] text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Draft</p>
                            <p class="text-2xl font-bold text-black" id="statDraft">0</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <i class="fas fa-file-alt text-gray-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Paid</p>
                            <p class="text-2xl font-bold text-black" id="statPaid">0</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Payout</p>
                            <p class="text-2xl font-bold text-black" id="statAmount">$0</p>
                        </div>
                        <div class="bg-[#99CC33] bg-opacity-10 p-3 rounded-lg">
                            <i class="fas fa-dollar-sign text-[#99CC33] text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by payroll number or notes..." onkeyup="applyFilters()">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="statusFilter" class="filter-select" onchange="applyFilters()">
                        <option value="all">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="processed">Processed</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Payroll Table -->
            <div class="table-container">
                <table class="payroll-table" id="payrollTable">
                    <thead>
                        <tr>
                            <th>Payroll #</th>
                            <th>Period</th>
                            <th>Staff</th>
                            <th>Total Hours</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Loading payrolls...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500" id="paginationInfo">Showing 0 of 0 payrolls</div>
                <div class="flex space-x-2" id="paginationButtons"></div>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Generate Payroll Modal -->
    <div id="generateModal" class="modal">
        <div class="modal-content max-w-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black">Generate Payroll</h3>
                    <button onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="generateForm" onsubmit="submitGeneratePayroll(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Period Start <span class="text-red-500">*</span></label>
                            <input type="date" id="periodStart" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Period End <span class="text-red-500">*</span></label>
                            <input type="date" id="periodEnd" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea id="payrollNotes" class="form-textarea" placeholder="Optional notes for this payroll run..."></textarea>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            All pending schedules within the selected period will be included and marked as processed.
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" id="generateBtn" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition flex items-center">
                            <i class="fas fa-cog mr-2"></i>
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Payroll Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content wide">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="viewModalTitle">Payroll Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="viewModalContent">
                    <!-- Populated by JS -->
                </div>
                <div class="flex justify-end mt-6">
                    <button onclick="closeViewModal()" class="px-4 py-2 bg-[#003366] text-white rounded-lg text-sm hover:bg-[#002244] transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-black">Change Payroll Status</h3>
                    <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">Updating status for <strong id="statusPayrollNum"></strong></p>
                <div class="mb-5">
                    <label class="form-label">New Status <span class="text-red-500">*</span></label>
                    <select id="statusSelect" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="processed">Processed</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button onclick="confirmStatusChange()" id="statusSaveBtn" class="px-4 py-2 bg-[#7c3aed] text-white rounded-lg text-sm hover:bg-[#6d28d9] transition flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Save Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-black mb-2">Delete Payroll</h3>
                    <p class="text-gray-600 mb-2">Are you sure you want to delete <strong id="deletePayrollNum"></strong>?</p>
                    <p class="text-sm text-amber-600 mb-6">All linked schedules will be reset to pending status.</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button onclick="confirmDelete()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                            Delete Payroll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] mr-3 text-xl"></i>
            <div>
                <p id="toastTitle" class="font-semibold text-gray-800">Success</p>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>

    <script>
        let payrolls = [];
        let filteredPayrolls = [];
        let currentPage = 1;
        const itemsPerPage = 10;
        let selectedPayrollId = null;

        // ─── Init ────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            fetchPayrolls();
            // Default period to current month
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById('periodStart').value = `${y}-${m}-01`;
            const last = new Date(y, now.getMonth() + 1, 0).getDate();
            document.getElementById('periodEnd').value = `${y}-${m}-${last}`;
        });

        // ─── Fetch All Payrolls ──────────────────────────────────────────────────
        function fetchPayrolls() {
            $.ajax({
                url: 'fetch_all_payrolls',
                method: 'POST',
                dataType: 'json',
                success: function (data) {
                    if (data.status) {
                        payrolls = data.payrolls;
                        filteredPayrolls = [...payrolls];
                        updateStats();
                        renderTable();
                    } else {
                        showEmptyState('Failed to load payrolls: ' + (data.message || ''));
                    }
                },
                error: function () {
                    showEmptyState('Connection error. Please refresh the page.');
                }
            });
        }

        // ─── Stats ───────────────────────────────────────────────────────────────
        function updateStats() {
            document.getElementById('statTotal').textContent = payrolls.length;
            document.getElementById('statDraft').textContent = payrolls.filter(p => p.status === 'draft').length;
            document.getElementById('statPaid').textContent  = payrolls.filter(p => p.status === 'paid').length;
            const total = payrolls.reduce((sum, p) => sum + parseFloat(p.total_amount || 0), 0);
            document.getElementById('statAmount').textContent = '$' + total.toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // ─── Filter ───────────────────────────────────────────────────────────────
        function applyFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;

            filteredPayrolls = payrolls.filter(p => {
                const matchSearch = !search ||
                    (p.payroll_number && p.payroll_number.toLowerCase().includes(search)) ||
                    (p.notes && p.notes.toLowerCase().includes(search));
                const matchStatus = status === 'all' || p.status === status;
                return matchSearch && matchStatus;
            });
            currentPage = 1;
            renderTable();
        }

        // ─── Render Table ─────────────────────────────────────────────────────────
        function renderTable() {
            const tbody = document.getElementById('payrollTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const page  = filteredPayrolls.slice(start, start + itemsPerPage);

            if (page.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-500">
                            <i class="fas fa-money-bill-wave text-3xl text-gray-300 mb-3 block"></i>
                            No payrolls found. Click "Generate Payroll" to create one.
                        </td>
                    </tr>`;
                updatePagination();
                return;
            }

            tbody.innerHTML = page.map(p => `
                <tr>
                    <td>
                        <span class="font-semibold text-[#003366]">${escHtml(p.payroll_number)}</span>
                    </td>
                    <td>
                        <p class="text-sm">${formatDate(p.period_start)}</p>
                        <p class="text-xs text-gray-500">to ${formatDate(p.period_end)}</p>
                    </td>
                    <td>
                        <span class="font-medium">${p.total_staff}</span>
                        <span class="text-gray-400 text-xs ml-1">staff</span>
                    </td>
                    <td>${parseFloat(p.total_hours || 0).toFixed(2)} hrs</td>
                    <td class="font-semibold text-[#003366]">$${parseFloat(p.total_amount || 0).toLocaleString('en-CA', { minimumFractionDigits: 2 })}</td>
                    <td><span class="status-badge status-${p.status}">${capitalize(p.status)}</span></td>
                    <td>
                        <p class="text-sm">${formatDateTime(p.created_at)}</p>
                        ${p.created_by_name ? `<p class="text-xs text-gray-500">${escHtml(p.created_by_name)}</p>` : ''}
                    </td>
                    <td>
                        <div class="flex space-x-1">
                            <button onclick="viewPayroll(${p.payroll_id})" class="action-btn view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openStatusModal(${p.payroll_id}, '${escHtml(p.payroll_number)}', '${p.status}')" class="action-btn change-status" title="Change Status">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                            <button onclick="openDeleteModal(${p.payroll_id}, '${escHtml(p.payroll_number)}')" class="action-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            updatePagination();
        }

        function showEmptyState(msg) {
            document.getElementById('payrollTableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-10 text-gray-500">
                        <i class="fas fa-exclamation-circle text-3xl text-red-300 mb-3 block"></i>
                        ${escHtml(msg)}
                    </td>
                </tr>`;
        }

        // ─── Pagination ───────────────────────────────────────────────────────────
        function updatePagination() {
            const total      = filteredPayrolls.length;
            const totalPages = Math.ceil(total / itemsPerPage);
            const start      = total > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
            const end        = Math.min(currentPage * itemsPerPage, total);

            document.getElementById('paginationInfo').textContent = `Showing ${start}–${end} of ${total} payrolls`;

            const container = document.getElementById('paginationButtons');
            container.innerHTML = '';

            const prev = document.createElement('button');
            prev.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-40';
            prev.textContent = 'Previous';
            prev.disabled = currentPage === 1;
            prev.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
            container.appendChild(prev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = i === currentPage
                    ? 'px-3 py-1 bg-[#99CC33] text-white rounded-lg text-sm'
                    : 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
                btn.onclick = (function (pg) { return function () { currentPage = pg; renderTable(); }; })(i);
                container.appendChild(btn);
            }

            const next = document.createElement('button');
            next.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-40';
            next.textContent = 'Next';
            next.disabled = currentPage === totalPages || totalPages === 0;
            next.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
            container.appendChild(next);
        }

        // ─── Generate Payroll ─────────────────────────────────────────────────────
        function openGenerateModal() {
            document.getElementById('generateModal').classList.add('active');
        }
        function closeGenerateModal() {
            document.getElementById('generateModal').classList.remove('active');
            document.getElementById('generateForm').reset();
        }

        function submitGeneratePayroll(event) {
            event.preventDefault();
            const btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';

            $.ajax({
                url: 'generate_payroll',
                method: 'POST',
                data: {
                    period_start: document.getElementById('periodStart').value,
                    period_end:   document.getElementById('periodEnd').value,
                    notes:        document.getElementById('payrollNotes').value
                },
                dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-cog mr-2"></i>Generate';
                    if (res.status) {
                        closeGenerateModal();
                        showToast('Success', res.message, 'success');
                        fetchPayrolls();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-cog mr-2"></i>Generate';
                    showToast('Error', 'Request failed. Please try again.', 'error');
                }
            });
        }

        // ─── View Payroll ─────────────────────────────────────────────────────────
        function viewPayroll(id) {
            document.getElementById('viewModalTitle').textContent = 'Loading...';
            document.getElementById('viewModalContent').innerHTML = `
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                </div>`;
            document.getElementById('viewModal').classList.add('active');

            $.ajax({
                url: 'get_payroll_details',
                method: 'POST',
                data: { payroll_id: id },
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        renderViewModal(res.payroll, res.schedules);
                    } else {
                        document.getElementById('viewModalContent').innerHTML =
                            `<p class="text-red-500 text-center py-4">${escHtml(res.message)}</p>`;
                    }
                },
                error: function () {
                    document.getElementById('viewModalContent').innerHTML =
                        `<p class="text-red-500 text-center py-4">Failed to load payroll details.</p>`;
                }
            });
        }

        function renderViewModal(p, schedules) {
            document.getElementById('viewModalTitle').textContent = `Payroll — ${p.payroll_number}`;

            const scheduleRows = schedules.length === 0
                ? `<tr><td colspan="7" class="text-center py-4 text-gray-400">No schedules linked to this payroll.</td></tr>`
                : schedules.map(s => `
                    <tr>
                        <td>${escHtml(s.staff_name)}</td>
                        <td>${escHtml(s.client_name)}</td>
                        <td>${formatDate(s.schedule_date)}</td>
                        <td class="capitalize">${s.shift_type}</td>
                        <td>${parseFloat(s.hours_worked || 0).toFixed(2)} hrs</td>
                        <td>$${parseFloat(s.pay_per_hour || 0).toFixed(2)}/hr${parseFloat(s.holiday_pay) > 0 ? ` <span class="text-amber-600 text-xs">+$${parseFloat(s.holiday_pay).toFixed(2)} holiday</span>` : ''}</td>
                        <td class="font-semibold text-[#003366]">$${parseFloat(s.amount || 0).toFixed(2)}</td>
                    </tr>
                `).join('');

            document.getElementById('viewModalContent').innerHTML = `
                <!-- Payroll Summary -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Period</p>
                        <p class="text-sm font-semibold mt-1">${formatDate(p.period_start)} – ${formatDate(p.period_end)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Status</p>
                        <span class="status-badge status-${p.status} mt-1 inline-block">${capitalize(p.status)}</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Total Hours</p>
                        <p class="text-lg font-bold text-[#003366] mt-1">${parseFloat(p.total_hours || 0).toFixed(2)} hrs</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Total Amount</p>
                        <p class="text-lg font-bold text-[#99CC33] mt-1">$${parseFloat(p.total_amount || 0).toLocaleString('en-CA', { minimumFractionDigits: 2 })}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Staff Count</p>
                        <p class="text-sm font-semibold mt-1">${p.total_staff} staff</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Created</p>
                        <p class="text-sm font-semibold mt-1">${formatDateTime(p.created_at)}</p>
                    </div>
                    ${p.notes ? `
                    <div class="bg-gray-50 rounded-lg p-3 col-span-2 sm:col-span-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Notes</p>
                        <p class="text-sm mt-1">${escHtml(p.notes)}</p>
                    </div>` : ''}
                </div>

                <!-- Schedules Table -->
                <div class="mb-2 flex items-center justify-between">
                    <h4 class="font-semibold text-[#003366]">Linked Schedules <span class="text-gray-400 font-normal text-sm">(${schedules.length})</span></h4>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="schedules-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Hours</th>
                                <th>Rate</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>${scheduleRows}</tbody>
                    </table>
                </div>
            `;
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        // ─── Delete ───────────────────────────────────────────────────────────────
        function openDeleteModal(id, number) {
            selectedPayrollId = id;
            document.getElementById('deletePayrollNum').textContent = number;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            selectedPayrollId = null;
        }
        function confirmDelete() {
            if (!selectedPayrollId) return;
            $.ajax({
                url: 'delete_payroll',
                method: 'POST',
                data: { payroll_id: selectedPayrollId },
                dataType: 'json',
                success: function (res) {
                    closeDeleteModal();
                    if (res.status) {
                        showToast('Deleted', res.message, 'success');
                        fetchPayrolls();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    closeDeleteModal();
                    showToast('Error', 'Delete request failed.', 'error');
                }
            });
        }

        // ─── Change Status ────────────────────────────────────────────────────────
        let selectedStatusPayrollId = null;

        function openStatusModal(id, number, currentStatus) {
            selectedStatusPayrollId = id;
            document.getElementById('statusPayrollNum').textContent = number;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').classList.add('active');
        }
        function closeStatusModal() {
            document.getElementById('statusModal').classList.remove('active');
            selectedStatusPayrollId = null;
        }
        function confirmStatusChange() {
            if (!selectedStatusPayrollId) return;
            const newStatus = document.getElementById('statusSelect').value;
            const btn = document.getElementById('statusSaveBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            $.ajax({
                url: 'update_payroll_status',
                method: 'POST',
                data: { payroll_id: selectedStatusPayrollId, status: newStatus },
                dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Status';
                    closeStatusModal();
                    if (res.status) {
                        showToast('Updated', res.message, 'success');
                        fetchPayrolls();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Status';
                    showToast('Error', 'Request failed. Please try again.', 'error');
                }
            });
        }

        // ─── Sidebar ──────────────────────────────────────────────────────────────
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        // ─── Toast ────────────────────────────────────────────────────────────────
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon  = document.getElementById('toastIcon');
            document.getElementById('toastTitle').textContent   = title;
            document.getElementById('toastMessage').textContent = message;

            toast.className = `toast ${type}`;
            if (type === 'success') icon.className = 'fas fa-check-circle text-[#99CC33] mr-3 text-xl';
            else if (type === 'error') icon.className = 'fas fa-times-circle text-red-500 mr-3 text-xl';
            else icon.className = 'fas fa-exclamation-circle text-yellow-500 mr-3 text-xl';

            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        }

        // ─── Helpers ──────────────────────────────────────────────────────────────
        function formatDate(str) {
            if (!str) return '—';
            const d = new Date(str + (str.length === 10 ? 'T00:00:00' : ''));
            return d.toLocaleDateString('en-CA', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        function formatDateTime(str) {
            if (!str) return '—';
            const d = new Date(str);
            return d.toLocaleDateString('en-CA', { year: 'numeric', month: 'short', day: 'numeric' })
                 + ' ' + d.toLocaleTimeString('en-CA', { hour: '2-digit', minute: '2-digit' });
        }
        function capitalize(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        }
        function escHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>
