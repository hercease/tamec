<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Client Management</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for easier DOM manipulation and AJAX -->
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

        /* Status badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .badge-active {
            background-color: #99CC33;
            color: white;
        }

        .badge-inactive {
            background-color: #EF4444;
            color: white;
        }

        /* Sidebar transitions */
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        /* Mobile menu overlay */
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

        /* Card hover effects */
        .card-hover {
            transition: all 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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

        /* Form styles */
        .form-section {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.2);
        }

        .form-select {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: white;
            transition: all 0.2s;
        }

        .form-select:focus {
            outline: none;
            border-color: #99CC33;
            box-shadow: 0 0 0 3px rgba(153, 204, 51, 0.2);
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 100;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            border-left: 4px solid #99CC33;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left-color: #99CC33;
        }

        .toast.error {
            border-left-color: #EF4444;
        }

        .toast.warning {
            border-left-color: #F59E0B;
        }

        /* Table styles */
        .table-container {
            overflow-x: auto;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .client-table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
        }

        .client-table th {
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .client-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.875rem;
        }

        .client-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .action-btn:hover {
            background-color: #f3f4f6;
        }

        .action-btn.edit:hover {
            color: #99CC33;
        }

        .action-btn.delete:hover {
            color: #EF4444;
        }

        .action-btn.view:hover {
            color: #003366;
        }

        /* Filter bar */
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
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .filter-select {
            padding: 0.625rem 2rem 0.625rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background-color: white;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .client-table th,
            .client-table td {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" class="overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar - Deep Blue -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Navigation Bar -->
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Mobile: hamburger + brand -->
                    <div class="flex items-center lg:hidden">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <span class="ml-3 text-sm font-bold text-[#003366]">TAMEC</span>
                    </div>
                    <!-- Desktop: page title -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <i class="fas fa-user-tie text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Client Management</h2>
                    </div>
                    <!-- Right: user -->
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

        <!-- Dashboard Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Clients</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage all your client information and
                        assignments</p>
                </div>
                <button onclick="openAddClientModal()"
                    class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Client
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search clients by name, email, phone..."
                        onkeyup="searchClients()">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="provinceFilter" class="filter-select" onchange="filterClients()">
                        <option value="all" selected>All Provinces</option>
                        <option value="Ontario">Ontario</option>
                        <option value="Quebec">Quebec</option>
                        <option value="Nova Scotia">Nova Scotia</option>
                        <option value="New Brunswick">New Brunswick</option>
                        <option value="Manitoba">Manitoba</option>
                        <option value="British Columbia">British Columbia</option>
                        <option value="Prince Edward Island">Prince Edward Island</option>
                        <option value="Saskatchewan">Saskatchewan</option>
                        <option value="Alberta">Alberta</option>
                        <option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
                        <option value="Northwest Territories">Northwest Territories</option>
                        <option value="Yukon">Yukon</option>
                        <option value="Nunavut">Nunavut</option>
                        <option value="Greenland">Greenland
                    </select>
                    </select>
                </div>
            </div>

            <!-- Clients Table -->
            <div class="table-container">
                <table class="client-table" id="clientTable">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Residential Address</th>
                            <th>Billing Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="clientTableBody">
                        <!-- Client rows will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500" id="paginationInfo">
                    Showing 1 to 10 of 10 clients
                </div>
                <div class="flex space-x-2">
                    <button
                        class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50"
                        id="prevPage" onclick="changePage(-1)" disabled>
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-[#99CC33] text-white rounded-lg text-sm" id="page1">1</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="page2"
                        onclick="changePage(2)">2</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="page3"
                        onclick="changePage(3)">3</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="nextPage"
                        onclick="changePage(1)">
                        Next
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Add/Edit Client Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="modalTitle">Add New Client</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Client Form -->
                <form id="clientForm" onsubmit="saveClient(event)">
                    <input type="hidden" id="clientId" value="">

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h4 class="text-lg font-semibold text-[#003366] mb-4">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">First Name <span class="text-red-500">*</span></label>
                                <input type="text" id="firstname" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Middle Name</label>
                                <input type="text" id="middlename" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" id="lastname" class="form-input" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="form-label">Mobile <span class="text-red-500">*</span></label>
                                <input type="tel" id="mobile" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" class="form-input" required>
                            </div>
                        </div>

                    </div>

                    <!-- Residential Address -->
                    <div class="form-section">
                        <h4 class="text-lg font-semibold text-[#003366] mb-4">Residential Address</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="form-label">Residence/Facility Name</label>
                                <input type="text" id="residentialName" class="form-input"
                                    placeholder="e.g., Thompson Residence">
                            </div>
                            <div>
                                <label class="form-label">Street Address <span class="text-red-500">*</span></label>
                                <input type="text" id="residentialAddress" class="form-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label">City <span class="text-red-500">*</span></label>
                                <input type="text" id="residentialCity" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Province <span class="text-red-500">*</span></label>
                                <select id="residentialProvince" class="form-input">
                                    <option value="Ontario" selected>Ontario</option>
                                    <option value="Quebec">Quebec</option>
                                    <option value="Nova Scotia">Nova Scotia</option>
                                    <option value="New Brunswick">New Brunswick</option>
                                    <option value="Manitoba">Manitoba</option>
                                    <option value="British Columbia">British Columbia</option>
                                    <option value="Prince Edward Island">Prince Edward Island</option>
                                    <option value="Saskatchewan">Saskatchewan</option>
                                    <option value="Alberta">Alberta</option>
                                    <option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Postal Code</label>
                                <input type="text" id="residentialPostalCode" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Country <span class="text-red-500">*</span></label>
                                <input type="text" id="residentialCountry" class="form-input" required value="Canada">
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="form-section">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-[#003366]">Billing Address</h4>
                            <label class="flex items-center">
                                <input type="checkbox" id="sameAsResidential" onclick="copyResidentialToBilling()"
                                    class="rounded border-gray-300 text-[#99CC33] focus:ring-[#99CC33]">
                                <span class="ml-2 text-sm text-gray-600">Same as residential address</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="form-label">Billing Name <span class="text-red-500">*</span></label>
                                <input type="text" id="billingName" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Street Address <span class="text-red-500">*</span></label>
                                <input type="text" id="billingAddress" class="form-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label">City</label>
                                <input type="text" id="billingCity" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Province <span class="text-red-500">*</span></label>
                                <select id="billingProvince" class="form-input">
                                    <option value="Ontario" selected>Ontario</option>
                                    <option value="Quebec">Quebec</option>
                                    <option value="Nova Scotia">Nova Scotia</option>
                                    <option value="New Brunswick">New Brunswick</option>
                                    <option value="Manitoba">Manitoba</option>
                                    <option value="British Columbia">British Columbia</option>
                                    <option value="Prince Edward Island">Prince Edward Island</option>
                                    <option value="Saskatchewan">Saskatchewan</option>
                                    <option value="Alberta">Alberta</option>
                                    <option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Postal Code</label>
                                <input type="text" id="billingPostalCode" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Country <span class="text-red-500">*</span></label>
                                <input type="text" id="billingCountry" class="form-input" required value="Canada">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label">Billing Email</label>
                            <input type="email" id="billingEmail" class="form-input" placeholder="billing@example.com">
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-lg font-semibold text-[#003366] mb-4">Billing Rate</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Hourly Rate ($) <span
                                                class="text-red-500">*</span></label>
                                        <input type="number" id="billingRate" class="form-input" min="0" step="0.01"
                                            placeholder="0.00" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Hourly Rate ($) (Rest)<span
                                                class="text-red-500">*</span></label>
                                        <input type="number" id="billingRateRest" class="form-input" min="0" step="0.01"
                                            placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="text-lg font-semibold text-[#003366] mb-4">Geographic Coordinates</h4>
                        <p class="text-xs text-gray-500 mb-3">These are used for mapping and distance calculations</p>
                        <div class="coordinates-group">
                            <div class="form-group">
                                <label class="form-label">Latitude <span class="text-red-500">*</span></label>
                                <input type="number" id="latitude" class="form-input" step="0.00000001"
                                    placeholder="43.6532" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Longitude <span class="text-red-500">*</span></label>
                                <input type="number" id="longitude" class="form-input" step="0.00000001"
                                    placeholder="-79.3832" required>
                            </div>
                        </div>
                        <button type="button" onclick="getCoordinates()"
                            class="mt-2 text-sm text-[#003366] hover:text-[#99CC33]">
                            <i class="fas fa-map-pin mr-1"></i>Get coordinates from address
                        </button>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition">
                            Save Client
                        </button>
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
                    <h3 class="text-xl font-bold text-black mb-2">Delete Client</h3>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete this client? This action cannot be
                        undone.</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeDeleteModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button onclick="confirmDelete()"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                            Delete Client
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Client Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content max-w-3xl">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="viewModalTitle">Client Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Client Details Content -->
                <div class="space-y-6" id="clientDetails">
                    <!-- Will be populated by JavaScript -->
                </div>

                <div class="flex justify-end mt-6">
                    <button onclick="closeViewModal()"
                        class="px-4 py-2 bg-[#003366] text-white rounded-lg text-sm hover:bg-[#002244] transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] mr-3 text-xl"></i>
            <div>
                <p id="toastTitle" class="font-semibold text-gray-800">Success</p>
                <p id="toastMessage" class="text-sm text-gray-600">Client saved successfully</p>
            </div>
        </div>
    </div>

    <script>
        // Sample client data (would come from database in production)
        let clients = [];

        function fetch_all_clients() {
            // This function would normally fetch data from the server and populate the clients array, make an AJAX call to get client data from the server
            $.ajax({
                url: 'fetch_all_clients', // Replace with your actual API endpoint
                method: 'POST',
                dataType: 'json',
                success: function (data) {
                    clients = data.clients;
                    filteredClients = [...clients];
                    renderClientTable();
                },
                error: function () {
                    console.error('Failed to fetch client data');
                }
            });
        }
        let currentPage = 1;
        let itemsPerPage = 5;
        let filteredClients = [...clients];
        let selectedClientId = null;
        let searchTerm = '';

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            fetch_all_clients();
        });

        // Render client table
        function renderClientTable() {
            const tbody = document.getElementById('clientTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedClients = filteredClients.slice(start, end);

            if (paginatedClients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            No clients found. Click "Add New Client" to create one.
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = paginatedClients.map(client => `
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-[#003366] rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                    ${client.firstname.charAt(0)}${client.lastname.charAt(0)}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">${client.firstname} ${client.middlename ? client.middlename + ' ' : ''}${client.lastname}</p>
                                    <p class="text-xs text-gray-500">${client.email}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-sm">${client.mobile}</p>
                            <p class="text-xs text-gray-500">${client.email}</p>
                        </td>
                        <td>
                            <p class="text-sm">${client.residentialName || 'N/A'}</p>
                            <p class="text-xs text-gray-500">${client.residentialAddress}, ${client.residentialCity}</p>
                        </td>
                        <td>
                            <p class="text-sm">${client.billingName}</p>
                            <p class="text-xs text-gray-500">${client.billingAddress}, ${client.billingCity}</p>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="viewClient(${client.id})" class="action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editClient(${client.id})" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteModal(${client.id})" class="action-btn delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            updatePagination();
        }

        function getCoordinates() {

            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(document.getElementById('residentialAddress').value + ', ' + document.getElementById('residentialCity').value + ', ' + document.getElementById('residentialProvince').value))
                .then(response => response.json())
                .then(data => {
                    //console.log('Geocoding data:', data);
                    if (data && data.length > 0) {
                        document.getElementById('latitude').value = data[0].lat;
                        document.getElementById('longitude').value = data[0].lon;
                    } else {
                        document.getElementById('latitude').value = '';
                        document.getElementById('longitude').value = '';
                        showToast('Error', 'Unable to fetch coordinates for the provided address.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching coordinates:', error);
                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                    showToast('Error', 'An error occurred while fetching coordinates. Please try again later.', 'error');
                });
            // Simulate filling coordinates
            // document.getElementById('latitude').value = '43.6532';
            // document.getElementById('longitude').value = '-79.3832';
        }

        // Search function
        function searchClients() {
            searchTerm = document.getElementById('searchInput').value.toLowerCase();
            applyFilters();
        }

        // Filter function
        function filterClients() {
            applyFilters();
        }

        // Combined filter function
        function applyFilters() {
            const provinceFilter = document.getElementById('provinceFilter').value;

            // Start with ALL clients
            let tempClients = [...clients];

            // Apply search filter if there's a search term
            if (searchTerm) {
                tempClients = tempClients.filter(client =>
                    (client.firstname && client.firstname.toLowerCase().includes(searchTerm)) ||
                    (client.lastname && client.lastname.toLowerCase().includes(searchTerm)) ||
                    (client.email && client.email.toLowerCase().includes(searchTerm)) ||
                    (client.mobile && client.mobile.includes(searchTerm)) ||
                    (client.residentialCity && client.residentialCity.toLowerCase().includes(searchTerm))
                );
            }

            // Apply province filter if not 'all'
            if (provinceFilter !== 'all') {
                tempClients = tempClients.filter(client =>
                    client.residentialProvince === provinceFilter
                );
            }

            // Update filtered clients
            filteredClients = tempClients;
            currentPage = 1;
            renderClientTable();
        }
        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(filteredClients.length / itemsPerPage);
            document.getElementById('paginationInfo').textContent =
                `Showing ${Math.min(1, filteredClients.length)} to ${Math.min(itemsPerPage, filteredClients.length)} of ${filteredClients.length} clients`;

            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;

            // Update page buttons
            for (let i = 1; i <= 3; i++) {
                const btn = document.getElementById(`page${i}`);
                if (i <= totalPages) {
                    btn.classList.remove('hidden');
                    btn.textContent = i;
                    if (i === currentPage) {
                        btn.className = 'px-3 py-1 bg-[#99CC33] text-white rounded-lg text-sm';
                    } else {
                        btn.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
                    }
                } else {
                    btn.classList.add('hidden');
                }
            }
        }

        // Change page
        function changePage(direction) {
            const totalPages = Math.ceil(filteredClients.length / itemsPerPage);
            if (direction === -1 && currentPage > 1) {
                currentPage--;
            } else if (direction === 1 && currentPage < totalPages) {
                currentPage++;
            } else if (typeof direction === 'number') {
                currentPage = direction;
            }
            renderClientTable();
        }

        // Modal functions
        function openAddClientModal() {
            document.getElementById('modalTitle').textContent = 'Add New Client';
            document.getElementById('clientForm').reset();
            document.getElementById('clientId').value = '';
            document.getElementById('clientModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('clientModal').classList.remove('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        // Copy residential to billing
        function copyResidentialToBilling() {
            const checkbox = document.getElementById('sameAsResidential');
            if (checkbox.checked) {
                document.getElementById('billingName').value = document.getElementById('residentialName').value;
                document.getElementById('billingAddress').value = document.getElementById('residentialAddress').value;
                document.getElementById('billingCity').value = document.getElementById('residentialCity').value;
                document.getElementById('billingProvince').value = document.getElementById('residentialProvince').value;
                document.getElementById('billingPostalCode').value = document.getElementById('residentialPostalCode').value;
                document.getElementById('billingCountry').value = document.getElementById('residentialCountry').value;
            }
        }

        // Save client
        function saveClient(event) {
            event.preventDefault();

            const clientId = document.getElementById('clientId').value;
            const clientData = {
                id: clientId ? parseInt(clientId) : 0,
                firstname: document.getElementById('firstname').value,
                middlename: document.getElementById('middlename').value,
                lastname: document.getElementById('lastname').value,
                mobile: document.getElementById('mobile').value,
                email: document.getElementById('email').value,
                residentialName: document.getElementById('residentialName').value,
                residentialAddress: document.getElementById('residentialAddress').value,
                residentialCity: document.getElementById('residentialCity').value,
                residentialProvince: document.getElementById('residentialProvince').value,
                residentialPostalCode: document.getElementById('residentialPostalCode').value,
                residentialCountry: document.getElementById('residentialCountry').value,
                billingName: document.getElementById('billingName').value,
                billingAddress: document.getElementById('billingAddress').value,
                billingCity: document.getElementById('billingCity').value,
                billingProvince: document.getElementById('billingProvince').value,
                billingPostalCode: document.getElementById('billingPostalCode').value,
                billingCountry: document.getElementById('billingCountry').value,
                billingEmail: document.getElementById('billingEmail').value,
                billingRate: document.getElementById('billingRate').value,
                billingRateRest: document.getElementById('billingRateRest').value,
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value,
                regDate: clientId ? clients.find(c => c.id === parseInt(clientId)).regDate : new Date().toISOString().slice(0, 19).replace('T', ' ')
            };

            if (clientId) {
                // Update existing client


                fetch('create_or_update_client', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(clientData).toString()
                }).then(response => response.json()).then(data => {

                    if (data.status) {
                        const index = clients.findIndex(c => c.id === parseInt(clientId));
                        clients[index] = clientData;
                        filteredClients = [...clients];
                        renderClientTable();
                        showToast('Success', 'Client updated successfully', 'success');
                        closeModal();
                    } else {
                        throw new Error(data.message || 'Failed to update client');
                    }

                }).catch(error => {
                    console.error(error);
                    showToast('Error', error, 'error');
                });

            } else {
                // Add new client
                fetch('create_or_update_client', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(clientData).toString()
                }).then(response => {

                    if (!response.ok) {
                        throw new Error('Failed to update client');
                    }

                    return response.json();

                }).then(data => {

                    if (data.status) {
                        clients.push(clientData);
                        filteredClients = [...clients];
                        renderClientTable();
                        showToast('Success', 'Client updated successfully', 'success');
                        closeModal();
                    } else {
                        throw new Error(data.message || 'Failed to update client');
                    }

                }).catch(error => {
                    console.error(error);
                    showToast('Error', 'Failed to update client', 'error');
                });
            }


        }

        // Edit client
        function editClient(id) {
            const client = clients.find(c => c.id === id);
            if (client) {
                document.getElementById('modalTitle').textContent = 'Edit Client';
                document.getElementById('clientId').value = client.id;
                document.getElementById('firstname').value = client.firstname;
                document.getElementById('middlename').value = client.middlename || '';
                document.getElementById('lastname').value = client.lastname;
                document.getElementById('mobile').value = client.mobile;
                document.getElementById('email').value = client.email;
                document.getElementById('residentialName').value = client.residentialName || '';
                document.getElementById('residentialAddress').value = client.residentialAddress;
                document.getElementById('residentialCity').value = client.residentialCity;
                document.getElementById('residentialProvince').value = client.residentialProvince;
                document.getElementById('residentialPostalCode').value = client.residentialPostalCode;
                document.getElementById('residentialCountry').value = client.residentialCountry;
                document.getElementById('billingName').value = client.billingName;
                document.getElementById('billingAddress').value = client.billingAddress;
                document.getElementById('billingCity').value = client.billingCity;
                document.getElementById('billingProvince').value = client.billingProvince;
                document.getElementById('billingPostalCode').value = client.billingPostalCode;
                document.getElementById('billingCountry').value = client.billingCountry;
                document.getElementById('billingEmail').value = client.billingEmail || '';
                document.getElementById('billingRate').value = client.billingRate || 0;
                document.getElementById('billingRateRest').value = client.billingRateRest || 0;
                document.getElementById('latitude').value = client.latitude || '';
                document.getElementById('longitude').value = client.longitude || '';

                document.getElementById('clientModal').classList.add('active');
            }
        }

        // View client
        function viewClient(id) {
            const client = clients.find(c => c.id === id);
            if (client) {
                document.getElementById('viewModalTitle').textContent = `Client Details - ${client.firstname} ${client.lastname}`;

                const detailsHtml = `
                    <div class="gap-4">
                        <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2 underline">Personal Information</h4>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Full Name</p>
                                    <p class="font-medium break-words whitespace-normal">${client.firstname} ${client.middlename ? client.middlename + ' ' : ''}${client.lastname}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Mobile</p>
                                    <p class="font-medium break-words whitespace-normal">${client.mobile}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-medium break-words whitespace-normal">${client.email}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2 underline">Residential Address</h4>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Residence/Facility Name</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialName || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Street Address</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialAddress}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">City</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialCity}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Province</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialProvince}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Postal Code</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialPostalCode}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Country</p>
                                    <p class="font-medium break-words whitespace-normal">${client.residentialCountry}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2 underline">Billing Address</h4>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Billing Name</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingName}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Street Address</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingAddress}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">City</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingCity}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Province</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingProvince}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Postal Code</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingPostalCode}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Country</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingCountry}</p>
                                </div>
                            </div>
                            ${client.billingEmail ? `
                                <div class="mt-4">
                                    <p class="text-xs text-gray-500">Billing Email</p>
                                    <p class="font-medium break-words whitespace-normal">${client.billingEmail}</p>
                                </div>` : ''}
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2 underline">Billing Rate</h4>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Rate</p>
                                    <p class="font-medium break-words whitespace-normal">$${client.billingRate}/hr</p>
                                </div>
                            </div>

                        </div>
                        
                        <div class="col-span-2 text-xs text-gray-500">
                            Registered on: ${new Date(client.regDate).toLocaleString()}
                        </div>
                    </div>
                `;

                document.getElementById('clientDetails').innerHTML = detailsHtml;
                document.getElementById('viewModal').classList.add('active');
            }
        }

        // Delete functions
        function openDeleteModal(id) {
            selectedClientId = id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function confirmDelete() {
            // write a function to delete client data from the database using AJAX, make an AJAX call to delete client data on the server
            $.ajax({
                url: 'delete_client', // Replace with your actual API endpoint
                method: 'POST',
                data: { client_id: selectedClientId },
                dataType: 'json',
                success: function (data) {
                    if (data.status) {
                        clients = clients.filter(c => c.id !== selectedClientId);
                        filteredClients = [...clients];
                        renderClientTable();
                        closeDeleteModal();
                        showToast('Success', 'Client deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete client');
                    }
                },
                error: function () {
                    console.error('Failed to delete client');
                    showToast('Error', 'Failed to delete client', 'error');
                }
            });
        }

        // Toast notification
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');

            toastTitle.textContent = title;
            toastMessage.textContent = message;

            if (type === 'success') {
                icon.className = 'fas fa-check-circle text-[#99CC33] mr-3 text-xl';
            } else if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle text-red-500 mr-3 text-xl';
            }

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

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

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) { // lg breakpoint
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
    </script>
</body>

</html>