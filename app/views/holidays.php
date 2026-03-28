<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Tamec - Holiday Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            transition: transform 0.3s ease-in-out;
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
        
        /* Table Container with Horizontal Scroll */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 1rem;
        }
        
        /* Mobile Card View */
        .mobile-card-view {
            display: none;
        }
        
        .card-item {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }
        
        .card-item:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .card-title {
            font-weight: 600;
            color: #003366;
            font-size: 1rem;
        }
        
        .card-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            background: #fee2e2;
            color: #ef4444;
        }
        
        .card-details {
            margin-bottom: 0.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-label {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }
        
        .card-details-full {
            display: none;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .expand-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.5rem;
            margin-top: 0.75rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .expand-btn:hover {
            background: #f3f4f6;
            color: #003366;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .card-action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: #f3f4f6;
        }
        
        .card-action-btn.view {
            color: #003366;
        }
        
        .card-action-btn.view:hover {
            background: #003366;
            color: white;
        }
        
        .card-action-btn.edit {
            color: #99CC33;
        }
        
        .card-action-btn.edit:hover {
            background: #99CC33;
            color: white;
        }
        
        .card-action-btn.delete {
            color: #ef4444;
        }
        
        .card-action-btn.delete:hover {
            background: #ef4444;
            color: white;
        }
        
        @media (max-width: 768px) {
            .desktop-table-view {
                display: none;
            }
            
            .mobile-card-view {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .desktop-table-view {
                display: block;
            }
            
            .mobile-card-view {
                display: none;
            }
        }
        
        /* Table styles */
        .table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .holiday-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }
        
        .holiday-table th {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .holiday-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .holiday-table tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Modal styles */
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
            max-width: 800px;
            width: 95%;
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
        
        /* Form styles */
        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-title {
            font-size: 1rem;
            font-weight: 600;
            color: #003366;
            margin-bottom: 1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 640px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
        }
        
        .form-input, .form-select, .form-textarea {
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.1);
        }
        
        /* Status badges */
        .badge-fixed {
            background: #e5e7eb;
            color: #374151;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .premium-badge {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .status-active {
            background: #99CC33;
            color: white;
        }
        
        .status-inactive {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        /* Action buttons */
        .action-btn {
            padding: 0.375rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            cursor: pointer;
            background: transparent;
            border: none;
        }
        
        .action-btn:hover {
            background: #f3f4f6;
        }
        
        .action-btn.edit:hover {
            color: #99CC33;
        }
        
        .action-btn.delete:hover {
            color: #ef4444;
        }
        
        .action-btn.view:hover {
            color: #003366;
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
                align-items: flex-end;
                justify-content: space-between;
            }
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        
        .filter-select, .filter-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Pagination */
        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background: #f3f4f6;
        }
        
        .pagination-btn.active {
            background: #99CC33;
            color: white;
            border-color: #99CC33;
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #99CC33;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                        <i class="fas fa-sun text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Holiday Management</h2>
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

        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Holiday Management</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Configure holidays and premium pay rates</p>
                </div>
                <button onclick="openAddHolidayModal()" class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Holiday
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label class="filter-label">Search Holidays</label>
                    <input type="text" id="searchInput" class="filter-input" placeholder="Search by holiday name..." onkeyup="searchHolidays()">
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="table-container desktop-table-view">
                <div class="table-wrapper">
                    <table class="holiday-table">
                        <thead>
                            <tr>
                                <th>Holiday Name</th>
                                <th>Date</th>
                                <th>Premium Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="holidayTableBody">
                            <tr>
                                <td colspan="4" class="text-center py-8">
                                    <div class="flex justify-center items-center">
                                        <div class="loading-spinner"></div>
                                        <span class="ml-2 text-gray-500">Loading holidays...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div id="mobileCardView" class="mobile-card-view">
                <!-- Cards will be populated here -->
            </div>

            <!-- Show More/Less Button for Mobile -->
            <div id="showMoreContainer" class="text-center mt-4 hidden">
                <button onclick="toggleShowMore()" id="showMoreBtn" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition">
                    <i class="fas fa-chevron-down mr-2"></i>
                    Show More
                </button>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                <div class="text-sm text-gray-500 order-2 sm:order-1">
                    Showing <span id="startCount">0</span> to <span id="endCount">0</span> of <span id="totalCount">0</span> holidays
                </div>
                <div class="flex space-x-2 order-1 sm:order-2">
                    <button class="pagination-btn" id="prevPage" onclick="changePage(-1)" disabled>
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </button>
                    <div class="flex space-x-1" id="paginationNumbers">
                        <button class="pagination-btn active" onclick="changePage(1)">1</button>
                    </div>
                    <button class="pagination-btn" id="nextPage" onclick="changePage(1)" disabled>
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Add/Edit Holiday Modal -->
    <div id="holidayModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="modalTitle">Add New Holiday</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="holidayForm" onsubmit="saveHoliday(event)">
                    <input type="hidden" id="holidayId" value="">
                    
                    <div class="form-section">
                        <h4 class="form-title">Basic Information</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Holiday Name <span class="text-red-500">*</span></label>
                                <input type="text" id="holidayName" class="form-input" required placeholder="e.g., Canada Day">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="form-title">Date Configuration</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Month</label>
                                <select id="fixedMonth" class="form-select">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Day</label>
                                <select id="fixedDay" class="form-select">
                                    <?php for($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="form-title">Pay Configuration</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Premium Percentage <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" id="premiumPercentage" class="form-input pr-8" value="50" min="0" max="200" step="5" required>
                                    <span class="absolute right-3 top-2 text-gray-500">%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">50% = half pay rate, 100% = full pay rate</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition">Save Holiday</button>
                    </div>
                </form>
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
                    <h3 class="text-xl font-bold text-black mb-2">Delete Holiday</h3>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete this holiday? This action cannot be undone.</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
                        <button onclick="confirmDelete()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600">Delete Holiday</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Holiday Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-black" id="viewModalTitle">Holiday Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="holidayDetails" class="space-y-4"></div>
                <div class="flex justify-end mt-6">
                    <button onclick="closeViewModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="toastMessage">Success!</span>
        </div>
    </div>

    <script>
        // Global variables
        let holidays = [];
        let filteredHolidays = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let selectedHolidayId = null;
        let searchTerm = '';
        let showAllItems = false;
        let currentMobileItems = 5;

        // Fetch holidays from server
        function fetchHolidays() {
            $.ajax({
                url: 'fetch_holidays',
                method: 'POST',
                dataType: 'json',
                success: function(data) {
                    holidays = data || [];
                    filteredHolidays = [...holidays];
                    renderHolidayTable();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching holidays:', error);
                    showToast('Failed to fetch holidays', 'error');
                    holidays = [];
                    filteredHolidays = [];
                    renderHolidayTable();
                }
            });
        }

        // Render holiday table and cards
        function renderHolidayTable() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedHolidays = filteredHolidays.slice(start, end);

            // Render desktop table
            renderDesktopTable(paginatedHolidays);
            
            // Render mobile cards
            renderMobileCards();
            
            // Update pagination
            updatePagination();
        }

        // Render desktop table view
        function renderDesktopTable(paginatedHolidays) {
            const tbody = document.getElementById('holidayTableBody');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            if (!paginatedHolidays || paginatedHolidays.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-alt text-3xl mb-2 text-gray-400"></i>
                            <p>No holidays found</p>
                            <p class="text-xs mt-1">Click "Add New Holiday" to create one</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = paginatedHolidays.map(h => {
                const dateDisplay = `${monthNames[h.fixed_month - 1]} ${h.fixed_day}`;
                return `
                    <tr>
                        <td class="font-medium">${escapeHtml(h.holiday_name)}</td>
                        <td><span class="badge-fixed px-2 py-1 rounded text-xs font-semibold">${dateDisplay}</span></td>
                        <td><span class="premium-badge">${h.premium_percentage}%</span></td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="viewHoliday(${h.holiday_id})" class="action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editHoliday(${h.holiday_id})" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteModal(${h.holiday_id})" class="action-btn delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `
                }).join('');
        }

        // Render mobile card view
        function renderMobileCards() {
            const container = document.getElementById('mobileCardView');
            const showMoreContainer = document.getElementById('showMoreContainer');
            
            if (!container) return;
            
            const itemsToShow = showAllItems ? filteredHolidays.length : Math.min(currentMobileItems, filteredHolidays.length);
            const itemsToDisplay = filteredHolidays.slice(0, itemsToShow);
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            
            if (filteredHolidays.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500 bg-white rounded-lg">
                        <i class="fas fa-calendar-alt text-3xl mb-2 text-gray-400"></i>
                        <p>No holidays found</p>
                        <p class="text-xs mt-1">Click "Add New Holiday" to create one</p>
                    </div>
                `;
                showMoreContainer.classList.add('hidden');
                return;
            }
            
            container.innerHTML = itemsToDisplay.map(h => {
                const dateDisplay = `${monthNames[h.fixed_month - 1]} ${h.fixed_day}`;
                
                return `
                    <div class="card-item" id="card-${h.holiday_id}">
                        <div class="card-header">
                            <span class="card-title">${escapeHtml(h.holiday_name)}</span>
                            <span class="card-badge">${h.premium_percentage}%</span>
                        </div>
                        <div class="card-details">
                            <div class="detail-row">
                                <span class="detail-label">Date</span>
                                <span class="detail-value">${dateDisplay}</span>
                            </div>
                        </div>
                        <div class="card-details-full" id="full-details-${h.holiday_id}">
                            <div class="detail-row">
                                <span class="detail-label">Premium Rate</span>
                                <span class="detail-value text-[#99CC33] font-bold">${h.premium_percentage}%</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created</span>
                                <span class="detail-value">${h.created_at ? new Date(h.created_at).toLocaleDateString() : 'N/A'}</span>
                            </div>
                        </div>
                        <button class="expand-btn" onclick="toggleCardDetails(${h.holiday_id})">
                            <i class="fas fa-chevron-down" id="expand-icon-${h.holiday_id}"></i>
                            <span id="expand-text-${h.holiday_id}">Show More</span>
                        </button>
                        <div class="card-actions">
                            <button onclick="viewHoliday(${h.holiday_id})" class="card-action-btn view">
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            <button onclick="editHoliday(${h.holiday_id})" class="card-action-btn edit">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button onclick="openDeleteModal(${h.holiday_id})" class="card-action-btn delete">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Show/Hide "Show More" button
            if (filteredHolidays.length > currentMobileItems) {
                showMoreContainer.classList.remove('hidden');
                const showMoreBtn = document.getElementById('showMoreBtn');
                showMoreBtn.innerHTML = showAllItems ? 
                    '<i class="fas fa-chevron-up mr-2"></i> Show Less' : 
                    '<i class="fas fa-chevron-down mr-2"></i> Show More (' + (filteredHolidays.length - currentMobileItems) + ' more)';
            } else {
                showMoreContainer.classList.add('hidden');
            }
        }

        // Toggle card details
        function toggleCardDetails(id) {
            const fullDetails = document.getElementById(`full-details-${id}`);
            const icon = document.getElementById(`expand-icon-${id}`);
            const text = document.getElementById(`expand-text-${id}`);
            
            if (fullDetails.style.display === 'none' || !fullDetails.style.display) {
                fullDetails.style.display = 'block';
                icon.className = 'fas fa-chevron-up';
                text.textContent = 'Show Less';
            } else {
                fullDetails.style.display = 'none';
                icon.className = 'fas fa-chevron-down';
                text.textContent = 'Show More';
            }
        }

        // Toggle show more/less on mobile
        function toggleShowMore() {
            showAllItems = !showAllItems;
            renderMobileCards();
        }

        // Search holidays
        function searchHolidays() {
            searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            if (searchTerm) {
                filteredHolidays = holidays.filter(h => 
                    h.holiday_name.toLowerCase().includes(searchTerm)
                );
            } else {
                filteredHolidays = [...holidays];
            }
            
            currentPage = 1;
            showAllItems = false;
            renderHolidayTable();
        }

        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(filteredHolidays.length / itemsPerPage);
            const start = filteredHolidays.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
            const end = Math.min(currentPage * itemsPerPage, filteredHolidays.length);
            
            document.getElementById('startCount').textContent = start;
            document.getElementById('endCount').textContent = end;
            document.getElementById('totalCount').textContent = filteredHolidays.length;
            
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            
            // Update pagination buttons
            const paginationNumbers = document.getElementById('paginationNumbers');
            let buttonsHtml = '';
            
            for (let i = 1; i <= Math.min(3, totalPages); i++) {
                buttonsHtml += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }
            
            if (totalPages > 3) {
                buttonsHtml += `<span class="px-2 text-gray-500">...</span>`;
                buttonsHtml += `<button class="pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
            }
            
            paginationNumbers.innerHTML = buttonsHtml;
        }

        // Change page
        function changePage(direction) {
            const totalPages = Math.ceil(filteredHolidays.length / itemsPerPage);
            
            if (typeof direction === 'number') {
                currentPage = direction;
            } else if (direction === -1 && currentPage > 1) {
                currentPage--;
            } else if (direction === 1 && currentPage < totalPages) {
                currentPage++;
            }
            
            renderHolidayTable();
        }

        // Modal functions
        function openAddHolidayModal() {
            document.getElementById('modalTitle').textContent = 'Add New Holiday';
            document.getElementById('holidayForm').reset();
            document.getElementById('holidayId').value = '';
            document.getElementById('premiumPercentage').value = '50';
            document.getElementById('holidayModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('holidayModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Save holiday
        function saveHoliday(event) {
            event.preventDefault();
            
            const holidayId = document.getElementById('holidayId').value;
            const holidayData = {
                holiday_id: holidayId ? parseInt(holidayId) : 0,
                holiday_name: document.getElementById('holidayName').value,
                fixed_month: parseInt(document.getElementById('fixedMonth').value),
                fixed_day: parseInt(document.getElementById('fixedDay').value),
                premium_percentage: parseInt(document.getElementById('premiumPercentage').value)
            };

            // Show loading on button
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            submitBtn.disabled = true;

            $.ajax({
                url: 'insert_update_holiday',
                method: 'POST',
                data: holidayData,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        if (holidayId) {
                            // Update existing holiday
                            const index = holidays.findIndex(h => h.holiday_id === parseInt(holidayId));
                            if (index !== -1) {
                                holidays[index] = { ...holidayData, holiday_id: parseInt(holidayId) };
                            }
                            showToast('Holiday updated successfully', 'success');
                        } else {
                            // Add new holiday
                            const newHoliday = { ...holidayData, holiday_id: response.holiday_id || Date.now() };
                            holidays.push(newHoliday);
                            showToast('Holiday added successfully', 'success');
                        }
                        
                        filteredHolidays = [...holidays];
                        renderHolidayTable();
                        closeModal();
                    } else {
                        showToast(response.message || 'Failed to save holiday', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving holiday:', error);
                    showToast('Failed to save holiday', 'error');
                },
                complete: function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // Edit holiday
        function editHoliday(id) {
            const holiday = holidays.find(h => h.holiday_id === id);
            if (holiday) {
                document.getElementById('modalTitle').textContent = 'Edit Holiday';
                document.getElementById('holidayId').value = holiday.holiday_id;
                document.getElementById('holidayName').value = holiday.holiday_name;
                document.getElementById('premiumPercentage').value = holiday.premium_percentage;
                document.getElementById('fixedMonth').value = holiday.fixed_month;
                document.getElementById('fixedDay').value = holiday.fixed_day;
                document.getElementById('holidayModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        // View holiday
        function viewHoliday(id) {
            const holiday = holidays.find(h => h.holiday_id === id);
            if (!holiday) return;

            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dateDisplay = `${monthNames[holiday.fixed_month - 1]} ${holiday.fixed_day}`;
            
            document.getElementById('viewModalTitle').textContent = `Holiday Details - ${holiday.holiday_name}`;
            
            const detailsHtml = `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Holiday Name</p>
                            <p class="font-medium">${escapeHtml(holiday.holiday_name)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Date</p>
                            <p class="font-medium">${dateDisplay}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Premium Percentage</p>
                            <p class="font-medium text-[#99CC33] font-bold">${holiday.premium_percentage}%</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('holidayDetails').innerHTML = detailsHtml;
            document.getElementById('viewModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Delete functions
        function openDeleteModal(id) {
            selectedHolidayId = id;
            document.getElementById('deleteModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function confirmDelete() {
            $.ajax({
                url: 'delete_holiday',
                method: 'POST',
                data: { holiday_id: selectedHolidayId },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        holidays = holidays.filter(h => h.holiday_id !== selectedHolidayId);
                        filteredHolidays = [...holidays];
                        renderHolidayTable();
                        closeDeleteModal();
                        showToast('Holiday deleted successfully', 'success');
                    } else {
                        showToast(response.message || 'Failed to delete holiday', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting holiday:', error);
                    showToast('Failed to delete holiday', 'error');
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

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Sidebar functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const clientModal = document.getElementById('holidayModal');
            const deleteModal = document.getElementById('deleteModal');
            const viewModal = document.getElementById('viewModal');
            
            if (event.target === clientModal) closeModal();
            if (event.target === deleteModal) closeDeleteModal();
            if (event.target === viewModal) closeViewModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
                closeViewModal();
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchHolidays();
        });
    </script>
</body>
</html>