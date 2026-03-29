<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Invoice Management</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

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
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .card-hover {
            transition: all 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

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
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.25s ease;
        }

        .modal-content.sm {
            max-width: 480px;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-40px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .inv-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .inv-table th {
            background: #f9fafb;
            padding: 0.875rem 1.25rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .inv-table td {
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .inv-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inv-table tbody tr:hover {
            background: #f9fafb;
        }

        .sched-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .sched-table th {
            background: #f3f4f6;
            padding: 0.5rem 0.875rem;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .sched-table td {
            padding: 0.6rem 0.875rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }

        .sched-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }

        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-sent {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-paid {
            background: #dcfce7;
            color: #15803d;
        }

        .status-overdue {
            background: #fee2e2;
            color: #ef4444;
        }

        .status-cancelled {
            background: #fef3c7;
            color: #92400e;
        }

        .action-btn {
            padding: 0.4rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.15s;
            cursor: pointer;
            color: #6b7280;
        }

        .action-btn:hover {
            background: #f3f4f6;
        }

        .action-btn.view:hover {
            color: #003366;
        }

        .action-btn.print:hover {
            color: #0369a1;
        }

        .action-btn.pdf:hover {
            color: #dc2626;
        }

        .action-btn.email:hover {
            color: #7c3aed;
        }

        .action-btn.paid:hover {
            color: #15803d;
        }

        .action-btn.delete:hover {
            color: #ef4444;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .search-box {
            flex: 1;
            min-width: 260px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.4rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .filter-select {
            padding: 0.6rem 2rem 0.6rem 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: white;
            cursor: pointer;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        .form-select {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .form-select:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.2);
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            z-index: 100;
            transform: translateX(420px);
            transition: transform 0.3s ease;
            border-left: 4px solid #99CC33;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast.warning {
            border-left-color: #f59e0b;
        }

        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .inv-table th:nth-child(3),
            .inv-table td:nth-child(3) {
                display: none;
            }
        }

        @media (max-width: 600px) {

            .inv-table th:nth-child(4),
            .inv-table td:nth-child(4),
            .inv-table th:nth-child(5),
            .inv-table td:nth-child(5) {
                display: none;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
            }
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
                        <i class="fas fa-file-invoice text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Invoices</h2>
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

        <!-- Main Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Invoices</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">View and manage client invoices</p>
                </div>
                <a href="create_invoice"
                    class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Invoice
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Invoices</p>
                            <p class="text-2xl font-bold text-black" id="statTotal">0</p>
                        </div>
                        <div class="bg-[#003366] bg-opacity-10 p-3 rounded-lg">
                            <i class="fas fa-file-invoice text-[#003366] text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Sent / Pending</p>
                            <p class="text-2xl font-bold text-black" id="statSent">0</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <i class="fas fa-paper-plane text-blue-500 text-xl"></i>
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
                            <p class="text-sm text-gray-500">Total Billed</p>
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
                    <input type="text" id="searchInput" placeholder="Search by invoice # or client..."
                        onkeyup="applyFilters()">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="statusFilter" class="filter-select" onchange="applyFilters()">
                        <option value="all">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="inv-table" id="invoiceTable">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Invoice Date</th>
                            <th>Period</th>
                            <th>Client</th>
                            <th>Staff Visits</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                                Loading invoices...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500" id="paginationInfo">Showing 0 of 0 invoices</div>
                <div class="flex space-x-2" id="paginationButtons"></div>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         VIEW MODAL
    ════════════════════════════════════════════════════════════════════ -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-black" id="viewModalTitle">Invoice Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="viewModalContent">
                    <div class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i></div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button onclick="closeViewModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         PRINT CONFIRM MODAL
    ════════════════════════════════════════════════════════════════════ -->
    <div id="printModal" class="modal">
        <div class="modal-content sm">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-print text-blue-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-black mb-2">Print Invoice</h3>
                <p class="text-gray-600 mb-1">You are about to print</p>
                <p class="font-semibold text-[#003366] mb-5" id="printInvoiceNum">—</p>
                <div class="flex justify-center gap-3">
                    <button onclick="closePrintModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button onclick="confirmPrint()" id="printConfirmBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-print"></i> Print Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         EMAIL CONFIRM MODAL
    ════════════════════════════════════════════════════════════════════ -->
    <div id="emailModal" class="modal">
        <div class="modal-content sm">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-purple-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-black mb-2">Email Invoice</h3>
                <p class="text-gray-600 mb-1"><strong id="emailInvoiceNum">—</strong> will be sent to</p>
                <p class="text-[#003366] font-semibold text-sm mb-4" id="emailRecipient">—</p>
                <p class="text-xs text-gray-400 mb-5">The invoice status will be updated to <strong>Sent</strong> upon
                    successful delivery.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="closeEmailModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button onclick="confirmEmail()" id="emailConfirmBtn"
                        class="px-4 py-2 bg-[#7c3aed] text-white rounded-lg text-sm hover:bg-[#6d28d9] transition flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         DELETE CONFIRM MODAL
    ════════════════════════════════════════════════════════════════════ -->
    <div id="deleteModal" class="modal">
        <div class="modal-content sm">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-black mb-2">Delete Invoice</h3>
                <p class="text-gray-600 mb-1">Are you sure you want to delete</p>
                <p class="font-semibold text-[#003366] mb-2" id="deleteInvoiceNum">—</p>
                <p class="text-sm text-amber-600 mb-5">All linked schedules will be reset to pending status.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button onclick="confirmDelete()" id="deleteConfirmBtn"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition flex items-center gap-2">
                        <i class="fas fa-trash"></i> Delete Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         MARK AS PAID MODAL
    ════════════════════════════════════════════════════════════════════ -->
    <div id="paidModal" class="modal">
        <div class="modal-content sm">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-black mb-2">Mark as Paid</h3>
                <p class="text-gray-600 mb-1">Confirm payment received for</p>
                <p class="font-semibold text-[#003366] mb-3" id="paidInvoiceNum">—</p>
                <p class="text-xs text-gray-400 bg-gray-50 rounded-lg p-2 mb-5">The invoice status will be permanently
                    updated to <strong>Paid</strong>.</p>
                <div class="flex justify-center gap-3">
                    <button onclick="closePaidModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button onclick="confirmMarkPaid()" id="paidConfirmBtn"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Mark as Paid
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden print area -->
    <div id="printArea" style="display:none;"></div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] mr-3 text-xl"></i>
            <div>
                <p id="toastTitle" class="font-semibold text-gray-800"></p>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>

    <script>
        let invoices = [];
        let filteredInvoices = [];
        let currentPage = 1;
        const itemsPerPage = 10;
        let selectedInvoiceId = null;
        let selectedInvoiceData = null; // cache for print

        // ─── Init ─────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', fetchInvoices);

        // ─── Fetch ────────────────────────────────────────────────────────────────
        function fetchInvoices() {
            $.ajax({
                url: 'fetch_all_invoices', method: 'POST', dataType: 'json',
                success: function (data) {
                    if (data.status) {
                        invoices = data.invoices;
                        filteredInvoices = [...invoices];
                        updateStats();
                        renderTable();
                    } else {
                        showEmpty('Failed to load: ' + (data.message || ''));
                    }
                },
                error: function () { showEmpty('Connection error. Please refresh.'); }
            });
        }

        // ─── Stats ────────────────────────────────────────────────────────────────
        function updateStats() {
            document.getElementById('statTotal').textContent = invoices.length;
            document.getElementById('statSent').textContent = invoices.filter(i => i.status === 'sent').length;
            document.getElementById('statPaid').textContent = invoices.filter(i => i.status === 'paid').length;
            const total = invoices.reduce((s, i) => s + parseFloat(i.total_amount || 0), 0);
            document.getElementById('statAmount').textContent = '$' + total.toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // ─── Filter ───────────────────────────────────────────────────────────────
        function applyFilters() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            const st = document.getElementById('statusFilter').value;
            filteredInvoices = invoices.filter(i => {
                const matchQ = !q || (i.invoice_number && i.invoice_number.toLowerCase().includes(q))
                    || (i.client_name && i.client_name.toLowerCase().includes(q));
                const matchSt = st === 'all' || i.status === st;
                return matchQ && matchSt;
            });
            currentPage = 1;
            renderTable();
        }

        // ─── Render Table ─────────────────────────────────────────────────────────
        function renderTable() {
            const tbody = document.getElementById('invoiceTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const page = filteredInvoices.slice(start, start + itemsPerPage);

            if (page.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="8" class="text-center py-10 text-gray-500">
                        <i class="fas fa-file-invoice text-3xl text-gray-300 mb-3 block"></i>
                        No invoices found.
                    </td></tr>`;
                updatePagination();
                return;
            }

            tbody.innerHTML = page.map(inv => `
                <tr>
                    <td><span class="font-semibold text-[#003366]">${escHtml(inv.invoice_number)}</span></td>
                    <td>
                        <p class="text-sm">${formatDate(inv.invoice_date)}</p>
                        ${inv.due_date ? `<p class="text-xs text-gray-400">Due ${formatDate(inv.due_date)}</p>` : ''}
                    </td>
                    <td>
                        <p class="text-sm">${formatDate(inv.period_start)}</p>
                        <p class="text-xs text-gray-400">to ${formatDate(inv.period_end)}</p>
                    </td>
                    <td>
                        <p class="text-sm font-medium">${escHtml(inv.client_name || '—')}</p>
                        ${inv.client_email ? `<p class="text-xs text-gray-400 truncate max-w-[160px]">${escHtml(inv.client_email)}</p>` : ''}
                    </td>
                    <td>
                        <span class="font-medium">${inv.total_staff}</span>
                        <span class="text-gray-400 text-xs ml-1">visits</span>
                    </td>
                    <td>
                        <p class="font-semibold text-[#003366]">$${parseFloat(inv.total_amount || 0).toLocaleString('en-CA', { minimumFractionDigits: 2 })}</p>
                        ${parseFloat(inv.tax_amount) > 0 ? `<p class="text-xs text-gray-400">incl. $${parseFloat(inv.tax_amount).toFixed(2)} tax</p>` : ''}
                    </td>
                    <td>
                        <span class="status-badge status-${inv.status}">${capitalize(inv.status)}</span>
                        ${dueBadge(inv.due_date, inv.status)}
                    </td>
                    <td>
                        <div class="flex items-center gap-0.5">
                            <button onclick="viewInvoice(${inv.invoice_id})" class="action-btn view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openPrintModal(${inv.invoice_id}, '${escHtml(inv.invoice_number)}')" class="action-btn print" title="Print">
                                <i class="fas fa-print"></i>
                            </button>
                            <button onclick="downloadInvoicePdf(${inv.invoice_id}, '${escHtml(inv.invoice_number)}')" class="action-btn pdf" title="Download PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                            <button onclick="openEmailModal(${inv.invoice_id}, '${escHtml(inv.invoice_number)}', '${escHtml(inv.client_email || '')}')" class="action-btn email" title="Email to Client">
                                <i class="fas fa-envelope"></i>
                            </button>
                            ${inv.status !== 'paid' && inv.status !== 'cancelled' ? `
                            <button onclick="openPaidModal(${inv.invoice_id}, '${escHtml(inv.invoice_number)}')" class="action-btn paid" title="Mark as Paid">
                                <i class="fas fa-check-circle"></i>
                            </button>` : ''}
                            <button onclick="openDeleteModal(${inv.invoice_id}, '${escHtml(inv.invoice_number)}')" class="action-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            updatePagination();
        }

        function showEmpty(msg) {
            document.getElementById('invoiceTableBody').innerHTML = `
                <tr><td colspan="8" class="text-center py-10 text-gray-500">
                    <i class="fas fa-exclamation-circle text-3xl text-red-300 mb-3 block"></i>
                    ${escHtml(msg)}
                </td></tr>`;
        }

        // ─── Pagination ───────────────────────────────────────────────────────────
        function updatePagination() {
            const total = filteredInvoices.length;
            const pages = Math.ceil(total / itemsPerPage);
            const start = total > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
            const end = Math.min(currentPage * itemsPerPage, total);
            document.getElementById('paginationInfo').textContent = `Showing ${start}–${end} of ${total} invoices`;

            const container = document.getElementById('paginationButtons');
            container.innerHTML = '';

            const prev = document.createElement('button');
            prev.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-40';
            prev.textContent = 'Previous'; prev.disabled = currentPage === 1;
            prev.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
            container.appendChild(prev);

            for (let i = 1; i <= pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = i === currentPage
                    ? 'px-3 py-1 bg-[#99CC33] text-white rounded-lg text-sm'
                    : 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
                btn.onclick = ((pg) => () => { currentPage = pg; renderTable(); })(i);
                container.appendChild(btn);
            }

            const next = document.createElement('button');
            next.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-40';
            next.textContent = 'Next'; next.disabled = currentPage === pages || pages === 0;
            next.onclick = () => { if (currentPage < pages) { currentPage++; renderTable(); } };
            container.appendChild(next);
        }

        // ─── View Modal ───────────────────────────────────────────────────────────
        function viewInvoice(id) {
            document.getElementById('viewModalTitle').textContent = 'Loading…';
            document.getElementById('viewModalContent').innerHTML =
                '<div class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i></div>';
            document.getElementById('viewModal').classList.add('active');

            $.ajax({
                url: 'get_invoice_details', method: 'POST',
                data: { invoice_id: id }, dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        selectedInvoiceData = res; // cache for print
                        renderViewModal(res.invoice, res.schedules);
                    } else {
                        document.getElementById('viewModalContent').innerHTML =
                            `<p class="text-red-500 text-center py-4">${escHtml(res.message)}</p>`;
                    }
                },
                error: function () {
                    document.getElementById('viewModalContent').innerHTML =
                        '<p class="text-red-500 text-center py-4">Failed to load invoice details.</p>';
                }
            });
        }

        function renderViewModal(inv, schedules) {
            document.getElementById('viewModalTitle').textContent = 'Invoice — ' + inv.invoice_number;

            const billing = [inv.billing_address || inv.residential_address,
            inv.billing_city || inv.residential_city,
            inv.billing_province || inv.residential_province].filter(Boolean).join(', ');

            const scheduleRows = schedules.length === 0
                ? '<tr><td colspan="6" class="text-center py-4 text-gray-400">No schedules linked.</td></tr>'
                : schedules.map(s => `
                    <tr>
                        <td>${escHtml(s.staff_name)}</td>
                        <td>${formatDate(s.schedule_date)}<br><span class="text-gray-400 text-xs">${s.start_time_fmt} – ${s.end_time_fmt}</span></td>
                        <td class="capitalize">${s.shift_type}${parseFloat(s.holiday_pay) > 0 ? ' <span style="color:#c2410c;font-size:11px;">(Holiday)</span>' : ''}</td>
                        <td class="text-right">${parseFloat(s.hours_worked || 0).toFixed(2)} hrs</td>
                        <td class="text-right text-gray-600">$${parseFloat(s.bill_rate || 0).toFixed(2)}</td>
                        <td class="text-right font-semibold text-[#003366]">$${parseFloat(s.amount || 0).toFixed(2)}</td>
                    </tr>`).join('');

            document.getElementById('viewModalContent').innerHTML = `
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Invoice Date</p>
                        <p class="text-sm font-semibold">${formatDate(inv.invoice_date)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Due Date</p>
                        <p class="text-sm font-semibold">${formatDate(inv.due_date)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Status</p>
                        <span class="status-badge status-${inv.status}">${capitalize(inv.status)}</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Period</p>
                        <p class="text-sm font-semibold">${formatDate(inv.period_start)} – ${formatDate(inv.period_end)}</p>
                    </div>
                </div>
                <!-- Client & Totals -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Bill To</p>
                        <p class="font-semibold text-gray-900">${escHtml(inv.client_name)}</p>
                        ${billing ? `<p class="text-sm text-gray-500 mt-0.5">${escHtml(billing)}</p>` : ''}
                        ${inv.client_email ? `<p class="text-sm text-gray-500 mt-0.5">${escHtml(inv.client_email)}</p>` : ''}
                        ${inv.po_number ? `<p class="text-xs text-gray-400 mt-1">PO: ${escHtml(inv.po_number)}</p>` : ''}
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-medium">$${parseFloat(inv.subtotal || 0).toFixed(2)}</span>
                        </div>
                        ${parseFloat(inv.tax_amount) > 0 ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax (${parseFloat(inv.tax_rate || 0).toFixed(0)}%)</span>
                            <span class="font-medium">$${parseFloat(inv.tax_amount).toFixed(2)}</span>
                        </div>` : ''}
                        ${parseFloat(inv.discount) > 0 ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Discount</span>
                            <span class="font-medium text-green-600">-$${parseFloat(inv.discount).toFixed(2)}</span>
                        </div>` : ''}
                        ${parseFloat(inv.shipping) > 0 ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Shipping</span>
                            <span class="font-medium">$${parseFloat(inv.shipping).toFixed(2)}</span>
                        </div>` : ''}
                        <div class="flex justify-between border-t pt-2 mt-1">
                            <span class="font-bold text-[#003366]">Total</span>
                            <span class="font-bold text-[#003366] text-base">$${parseFloat(inv.total_amount || 0).toLocaleString('en-CA', { minimumFractionDigits: 2 })}</span>
                        </div>
                    </div>
                </div>
                ${inv.notes ? `
                <div class="bg-amber-50 rounded-lg p-3 mb-5 text-sm text-gray-600">
                    <span class="font-semibold text-amber-700">Notes: </span>${escHtml(inv.notes)}
                </div>` : ''}
                <!-- Schedules -->
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-[#003366]">Service Lines <span class="text-gray-400 font-normal text-sm">(${schedules.length})</span></h4>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="sched-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Date / Time</th>
                                <th>Shift</th>
                                <th class="text-right">Hours</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Amount</th>
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

        // ─── Print Modal ──────────────────────────────────────────────────────────
        function openPrintModal(id, number) {
            selectedInvoiceId = id;
            document.getElementById('printInvoiceNum').textContent = number;
            document.getElementById('printModal').classList.add('active');
        }
        function closePrintModal() {
            document.getElementById('printModal').classList.remove('active');
            selectedInvoiceId = null;
        }
        function confirmPrint() {
            const idToPrint = selectedInvoiceId;
            closePrintModal();
            const btn = document.getElementById('printConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading…';

            $.ajax({
                url: 'get_invoice_details', method: 'POST',
                data: { invoice_id: idToPrint }, dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print"></i> Print Now';
                    if (res.status) {
                        doPrint(res.invoice, res.schedules, res.outstanding || []);
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print"></i> Print Now';
                    showToast('Error', 'Failed to load invoice.', 'error');
                }
            });
        }

        // ─── Invoice HTML builder (shared by print and PDF download) ────────────
        function buildInvoiceHtml(inv, schedules, outstanding) {
            outstanding = outstanding || [];

            function fmtSlash(s) {
                if (!s) return '—';
                const p = s.split('-');
                return p[1] + '/' + p[2] + '/' + p[0].slice(2);
            }
            function fmtLong(s) {
                if (!s) return '—';
                return new Date(s + 'T00:00:00').toLocaleDateString('en-CA', { year: 'numeric', month: 'short', day: 'numeric' });
            }
            function fmtAmt(n) {
                return '$' + parseFloat(n || 0).toLocaleString('en-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const billing = [inv.billing_address || inv.residential_address,
            inv.billing_city || inv.residential_city,
            inv.billing_province || inv.residential_province].filter(Boolean).join(', ');

            const logoUrl = window.location.origin
                + window.location.pathname.replace(/\/[^\/]*$/, '/')
                + 'public/images/tameclogo.png';

            const rows = schedules.map(function (s) {
                const dayN = s.day_name || '';
                const startT = (s.start_short || s.start_time_fmt || '').trim().toLowerCase();
                const endT = (s.end_short || s.end_time_fmt || '').trim().toLowerCase();
                const staffD = escHtml(s.staff_name_last || s.staff_name || '');
                const desc = fmtSlash(s.schedule_date) + ' (' + dayN + ') @ ' + startT + ' - ' + endT + ' - CG: ' + staffD;
                const hp = parseFloat(s.holiday_pay || 0);
                const rate = hp > 0 ? (parseFloat(s.bill_rate || 0) * hp / 100) : parseFloat(s.bill_rate || 0);
                const holTag = hp > 0 ? ' <em style="color:#c2410c;font-size:11px;">(Holiday Pay)</em>' : '';
                return '<tr>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#111827;line-height:1.4;">' + desc + holTag + '</td>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#374151;">Hours Normal</td>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#374151;">Yes</td>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:13px;color:#374151;">' + parseFloat(s.hours_worked || 0).toFixed(2) + '</td>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:13px;color:#374151;">' + fmtAmt(rate) + '</td>'
                    + '<td style="padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:700;font-size:13px;color:#111827;">' + fmtAmt(s.amount) + '</td>'
                    + '</tr>';
            }).join('');

            const subtotal = parseFloat(inv.subtotal || 0);
            const gst = parseFloat(inv.tax_amount || 0);
            const total = parseFloat(inv.total_amount || 0);
            const outstandingSum = outstanding.reduce(function (sum, o) { return sum + parseFloat(o.total_amount || 0); }, 0);
            const payThisAmount = total + outstandingSum;

            const outstandingRows = outstanding.map(function (o) {
                return '<tr>'
                    + '<td style="padding:4px 20px 4px 0;font-size:13px;">' + fmtSlash(o.invoice_date) + '</td>'
                    + '<td style="padding:4px 20px 4px 0;font-size:13px;">' + fmtAmt(o.total_amount) + '</td>'
                    + '<td style="padding:4px 0;font-weight:700;font-size:13px;">' + fmtAmt(o.total_amount) + '</td>'
                    + '</tr>';
            }).join('');

            const outstandingSection = outstanding.length > 0
                ? '<div style="display:flex;gap:48px;margin-bottom:20px;">'
                + '<div>'
                + '<p style="font-size:12px;font-weight:700;color:#003366;text-decoration:underline;margin-bottom:8px;">Outstanding Unpaid Invoices</p>'
                + '<table style="border-collapse:collapse;">'
                + '<thead><tr>'
                + '<th style="text-align:left;padding:2px 20px 6px 0;font-size:12px;font-weight:700;color:#111827;">Date</th>'
                + '<th style="text-align:left;padding:2px 20px 6px 0;font-size:12px;font-weight:700;color:#111827;">Amount</th>'
                + '<th style="text-align:left;padding:2px 0 6px;font-size:12px;font-weight:700;color:#111827;">Outstanding</th>'
                + '</tr></thead>'
                + '<tbody>' + outstandingRows + '</tbody>'
                + '</table>'
                + '</div>'
                + '</div>'
                : '';

            const otherRow = outstandingSum > 0
                ? '<div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px;">'
                + '<span>Other outstanding balance:</span><span>' + fmtAmt(outstandingSum) + '</span>'
                + '</div>'
                : '';

            const notesHtml = inv.notes
                ? '<div style="margin-top:20px;font-size:13px;color:#374151;"><strong>Notes:</strong> ' + escHtml(inv.notes) + '</div>'
                : '';

            const styles = ''
                + '* { box-sizing:border-box; margin:0; padding:0; }'
                + 'body { font-family:"Helvetica Neue",Helvetica,Arial,sans-serif; color:#1f2937; background:#fff; -webkit-font-smoothing:antialiased; }'
                + '.page { padding:32px 40px; max-width:900px; margin:0 auto; }'
                + '.co-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; }'
                + '.co-name { font-size:14px; font-weight:800; color:#111827; letter-spacing:.3px; }'
                + '.co-addr { font-size:12px; color:#4b5563; line-height:1.75; margin-top:4px; }'
                + '.logo img { max-height:72px; }'
                + '.inv-title-bar { display:flex; justify-content:space-between; align-items:flex-end; border-top:3px solid #003366; padding-top:14px; margin-bottom:18px; }'
                + '.inv-title { font-size:38px; font-weight:900; color:#003366; letter-spacing:2px; }'
                + '.bd-label { font-size:12px; color:#6b7280; font-weight:700; text-align:right; text-transform:uppercase; letter-spacing:.3px; }'
                + '.bd-amount { font-size:24px; font-weight:800; color:#003366; text-align:right; }'
                + '.bd-due { font-size:12px; color:#6b7280; text-align:right; margin-top:4px; }'
                + '.bill-meta { display:flex; justify-content:space-between; gap:40px; margin-bottom:22px; padding:14px 16px; border:1px solid #e5e7eb; border-radius:4px; background:#fafafa; }'
                + '.bt-label { font-size:11px; font-weight:700; text-transform:uppercase; color:#9ca3af; margin-bottom:5px; letter-spacing:.5px; }'
                + '.bt-name { font-size:15px; font-weight:700; color:#111827; }'
                + '.bt-addr { font-size:12px; color:#4b5563; margin-top:3px; }'
                + '.meta-tbl { border-collapse:collapse; font-size:13px; }'
                + '.meta-tbl td { padding:3px 14px 3px 0; color:#374151; vertical-align:top; }'
                + '.meta-tbl td:first-child { font-weight:700; color:#003366; white-space:nowrap; }'
                + 'table.svc { width:100%; border-collapse:collapse; margin-bottom:16px; }'
                + 'table.svc thead tr { background:#003366; }'
                + 'table.svc th { padding:9px 10px; color:#fff; font-size:11px; font-weight:700; text-align:left; text-transform:uppercase; letter-spacing:.5px; }'
                + 'table.svc th.r { text-align:right; }'
                + 'table.svc tbody tr:nth-child(even) { background:#f9fafb; }'
                + 'table.svc td.r { text-align:right; }'
                + '.tot-wrap { display:flex; justify-content:flex-end; margin-bottom:24px; }'
                + '.tot-tbl { border-collapse:collapse; font-size:13px; min-width:280px; }'
                + '.tot-tbl td { padding:5px 10px; }'
                + '.tot-tbl td:last-child { text-align:right; font-weight:700; }'
                + '.tot-tbl .nt { color:#9ca3af; }'
                + '.tot-tbl .ttl td { border-top:2px solid #003366; padding-top:10px; font-size:15px; font-weight:800; color:#003366; background:#f0f4ff; }'
                + '.pay-wrap { display:flex; justify-content:flex-end; margin-top:8px; }'
                + '.pay-box { border:1px solid #e5e7eb; border-radius:4px; padding:14px 18px; font-size:13px; min-width:300px; }'
                + '.pay-amt { display:flex; justify-content:space-between; margin-top:10px; padding-top:10px; border-top:2px solid #003366; font-size:17px; font-weight:900; color:#003366; }'
                + '.footer { margin-top:30px; padding-top:14px; border-top:1px solid #e5e7eb; font-size:12px; color:#9ca3af; text-align:center; }'
                + '@media print { body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } @page { size:A4; margin:1.5cm; } }';

            const body = '<div class="page">'
                + '<div class="co-header">'
                + '<div>'
                + '<div class="co-name">TAMEC CARE STAFFING SERVICES LTD</div>'
                + '<div class="co-addr">3100 STEELES AVENUE WEST<br>403<br>CONCORD, ONTARIO L4K 3R1<br>info@tameccarestaffing.com</div>'
                + '</div>'
                + '<div class="logo"><img src="' + logoUrl + '" alt="TAMEC" onerror="this.style.display=\'none\'"></div>'
                + '</div>'
                + '<div class="inv-title-bar">'
                + '<div class="inv-title">INVOICE</div>'
                + '<div>'
                + '<div class="bd-label">Balance Due</div>'
                + '<div class="bd-amount">' + fmtAmt(payThisAmount) + '</div>'
                + '<div class="bd-due">Due Date &nbsp; ' + fmtSlash(inv.due_date) + '</div>'
                + '</div>'
                + '</div>'
                + '<div class="bill-meta">'
                + '<div>'
                + '<div class="bt-label">Bill To</div>'
                + '<div class="bt-name">' + escHtml(inv.client_name || '') + '</div>'
                + (billing ? '<div class="bt-addr">' + escHtml(billing) + '</div>' : '')
                + (inv.client_email ? '<div class="bt-addr">' + escHtml(inv.client_email) + '</div>' : '')
                + '</div>'
                + '<table class="meta-tbl">'
                + '<tr><td>Invoice #</td><td>' + escHtml(inv.invoice_number) + '</td></tr>'
                + '<tr><td>Date</td><td>' + fmtSlash(inv.invoice_date) + '</td></tr>'
                + '<tr><td>Status</td><td>' + (inv.status ? inv.status.charAt(0).toUpperCase() + inv.status.slice(1) : '') + '</td></tr>'
                + '<tr><td>Service From</td><td>' + fmtSlash(inv.period_start) + '</td></tr>'
                + '<tr><td>To</td><td>' + fmtSlash(inv.period_end) + '</td></tr>'
                + '<tr><td>Client</td><td>' + escHtml(inv.client_name || '') + '</td></tr>'
                + '</table>'
                + '</div>'
                + '<table class="svc">'
                + '<thead><tr>'
                + '<th style="width:42%">Description</th>'
                + '<th>Type</th>'
                + '<th>Taxable</th>'
                + '<th class="r">Quantity</th>'
                + '<th class="r">Rate</th>'
                + '<th class="r">Amount</th>'
                + '</tr></thead>'
                + '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '<div class="tot-wrap"><table class="tot-tbl">'
                + '<tr class="nt"><td>Non-Taxable Subtotal</td><td>$0.00</td></tr>'
                + '<tr><td>Taxable Subtotal</td><td>' + fmtAmt(subtotal) + '</td></tr>'
                + '<tr><td>GST</td><td>' + fmtAmt(gst) + '</td></tr>'
                + '<tr class="ttl"><td>Total</td><td>' + fmtAmt(total) + '</td></tr>'
                + '</table></div>'
                + outstandingSection
                + '<div class="pay-wrap"><div class="pay-box">'
                + '<div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px;">'
                + '<span>Amount due on this invoice:</span><span>' + fmtAmt(total) + '</span>'
                + '</div>'
                + otherRow
                + '<div class="pay-amt"><span>PAY THIS AMOUNT:</span><span>' + fmtAmt(payThisAmount) + '</span></div>'
                + '</div></div>'
                + notesHtml
                + '<div class="footer">Thank you for your business. Please remit payment by ' + fmtLong(inv.due_date) + '.</div>'
                + '</div>';

            return { styles: styles, body: body };
        }

        function doPrint(inv, schedules, outstanding) {
            const doc = buildInvoiceHtml(inv, schedules, outstanding);
            const win = window.open('', '_blank');
            win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice ' + escHtml(inv.invoice_number) + '</title><style>' + doc.styles + '</style></head><body>' + doc.body + '</body></html>');
            win.document.close();
            win.focus();
            setTimeout(function () { win.print(); }, 500);
        }

        function doDownloadPdf(inv, schedules, outstanding) {
            const doc = buildInvoiceHtml(inv, schedules, outstanding);
            // Build a full self-contained HTML page string and pass it to html2pdf
            // Using .from(html, 'string') renders inside an internal iframe — no
            // DOM append needed, so blank-page issues are avoided entirely.
            const fullHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
                + '<style>' + doc.styles + '</style></head><body>' + doc.body + '</body></html>';

            html2pdf()
                .set({
                    margin: [8, 8, 8, 8],
                    filename: 'Invoice-' + inv.invoice_number + '.pdf',
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                })
                .from(fullHtml, 'string')
                .save();
        }

        function downloadInvoicePdf(id) {
            showToast('Info', 'Preparing PDF…', 'info');
            $.ajax({
                url: 'get_invoice_details', method: 'POST',
                data: { invoice_id: id }, dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        doDownloadPdf(res.invoice, res.schedules, res.outstanding || []);
                    } else {
                        showToast('Error', res.message || 'Failed to load invoice.', 'error');
                    }
                },
                error: function () { showToast('Error', 'Failed to load invoice.', 'error'); }
            });
        }

        // ─── Email Modal ──────────────────────────────────────────────────────────
        function openEmailModal(id, number, email) {
            selectedInvoiceId = id;
            document.getElementById('emailInvoiceNum').textContent = number;
            document.getElementById('emailRecipient').textContent = email || 'No email on file';
            document.getElementById('emailModal').classList.add('active');
        }
        function closeEmailModal() {
            document.getElementById('emailModal').classList.remove('active');
            selectedInvoiceId = null;
        }
        function confirmEmail() {
            if (!selectedInvoiceId) return;
            const btn = document.getElementById('emailConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';

            $.ajax({
                url: 'send_invoice_email', method: 'POST',
                data: { invoice_id: selectedInvoiceId }, dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
                    closeEmailModal();
                    if (res.status) {
                        showToast('Email Sent', res.message, 'success');
                        fetchInvoices();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
                    showToast('Error', 'Request failed. Please try again.', 'error');
                }
            });
        }

        // ─── Delete Modal ─────────────────────────────────────────────────────────
        function openDeleteModal(id, number) {
            selectedInvoiceId = id;
            document.getElementById('deleteInvoiceNum').textContent = number;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            selectedInvoiceId = null;
        }
        function confirmDelete() {
            if (!selectedInvoiceId) return;
            const btn = document.getElementById('deleteConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting…';

            $.ajax({
                url: 'delete_invoice', method: 'POST',
                data: { invoice_id: selectedInvoiceId }, dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Delete Invoice';
                    closeDeleteModal();
                    if (res.status) {
                        showToast('Deleted', res.message, 'success');
                        fetchInvoices();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Delete Invoice';
                    showToast('Error', 'Delete request failed.', 'error');
                }
            });
        }

        // ─── Mark as Paid Modal ───────────────────────────────────────────────────
        function openPaidModal(id, number) {
            selectedInvoiceId = id;
            document.getElementById('paidInvoiceNum').textContent = number;
            document.getElementById('paidModal').classList.add('active');
        }
        function closePaidModal() {
            document.getElementById('paidModal').classList.remove('active');
            selectedInvoiceId = null;
        }
        function confirmMarkPaid() {
            if (!selectedInvoiceId) return;
            const btn = document.getElementById('paidConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';

            $.ajax({
                url: 'update_invoice_status', method: 'POST',
                data: { invoice_id: selectedInvoiceId, new_status: 'paid' }, dataType: 'json',
                success: function (res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Mark as Paid';
                    closePaidModal();
                    if (res.status) {
                        showToast('Payment Recorded', res.message, 'success');
                        fetchInvoices();
                    } else {
                        showToast('Error', res.message, 'error');
                    }
                },
                error: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Mark as Paid';
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
            const icon = document.getElementById('toastIcon');
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastMessage').textContent = message;
            toast.className = 'toast ' + (type !== 'success' ? type : '');
            icon.className = type === 'success' ? 'fas fa-check-circle text-[#99CC33] mr-3 text-xl'
                : type === 'error' ? 'fas fa-times-circle text-red-500 mr-3 text-xl'
                    : 'fas fa-exclamation-circle text-yellow-500 mr-3 text-xl';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        }

        // ─── Helpers ──────────────────────────────────────────────────────────────
        function dueBadge(due_date, status) {
            if (!due_date || status === 'paid' || status === 'cancelled') return '';
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const due = new Date(due_date + 'T00:00:00');
            const days = Math.round((due - today) / 86400000);
            if (days < 0) return `<span class="status-badge mt-1" style="background:#fee2e2;color:#ef4444;display:block;">${Math.abs(days)}d overdue</span>`;
            if (days === 0) return `<span class="status-badge mt-1" style="background:#fee2e2;color:#ef4444;display:block;">Due today</span>`;
            if (days <= 7) return `<span class="status-badge mt-1" style="background:#fef9c3;color:#a16207;display:block;">${days}d left</span>`;
            return `<span class="status-badge mt-1" style="background:#dcfce7;color:#15803d;display:block;">${days}d left</span>`;
        }

        function formatDate(str) {
            if (!str) return '—';
            const d = new Date(str + (str.length === 10 ? 'T00:00:00' : ''));
            return d.toLocaleDateString('en-CA', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        function capitalize(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        }
        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    </script>
</body>

</html>