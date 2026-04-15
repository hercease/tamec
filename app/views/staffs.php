<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Staff Management</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for easier DOM manipulation -->
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
        
        .badge-admin {
            background-color: #003366;
            color: white;
        }
        
        .badge-staff {
            background-color: #6B7280;
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
        
        .staff-table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
        }
        
        .staff-table th {
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
        
        .staff-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .staff-table tbody tr:hover {
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

        .action-btn.agreement:hover {
            color: #7c3aed;
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
            
            .staff-table th, .staff-table td {
                padding: 0.75rem 1rem;
            }
        }

        /* Province flags */
        .province-flag {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
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
                        <i class="fas fa-user-nurse text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Staff Management</h2>
                    </div>
                    <!-- Right: user -->
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

        <!-- Dashboard Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black">Staff Members</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage all your caregivers and their information</p>
                </div>
                <button onclick="openAddStaffModal()" class="mt-4 sm:mt-0 px-4 py-2 bg-[#99CC33] text-white text-sm rounded-lg hover:bg-[#88BB22] transition flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Staff
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Staff</p>
                            <p class="text-2xl font-bold text-black" id="totalStaff">0</p>
                        </div>
                        <div class="bg-[#99CC33] bg-opacity-10 p-3 rounded-lg">
                            <i class="fas fa-users text-[#99CC33] text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Active</p>
                            <p class="text-2xl font-bold text-black" id="activeStaff">0</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Inactive</p>
                            <p class="text-2xl font-bold text-black" id="inactiveStaff">0</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <i class="fas fa-minus-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Admins</p>
                            <p class="text-2xl font-bold text-black" id="adminCount">0</p>
                        </div>
                        <div class="bg-[#003366] bg-opacity-10 p-3 rounded-lg">
                            <i class="fas fa-user-shield text-[#003366] text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search staff by name, email, phone, city..." onkeyup="searchStaff()">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="statusFilter" class="filter-select" onchange="filterStaff()">
                        <option value="all">All Staff</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                    <select id="roleFilter" class="filter-select" onchange="filterStaff()">
                        <option value="all">All Roles</option>
                        <option value="admin">Admins</option>
                        <option value="staff">Regular Staff</option>
                    </select>
                    <select id="provinceFilter" class="filter-select" onchange="filterStaff()">
                        <option value="all">All Provinces</option>
                        <option value="Ontario">Ontario</option>
                        <option value="British Columbia">British Columbia</option>
                        <option value="Alberta">Alberta</option>
                        <option value="Manitoba">Manitoba</option>
                    </select>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="table-container">
                <table class="staff-table" id="staffTable">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        <!-- Staff rows will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500" id="paginationInfo">
                    Showing 0 to 0 of 0 staff
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50" id="prevPage" onclick="changePage(-1)" disabled>
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-[#99CC33] text-white rounded-lg text-sm" id="page1">1</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="page2" onclick="changePage(2)">2</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="page3" onclick="changePage(3)">3</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50" id="nextPage" onclick="changePage(1)">
                        Next
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Add/Edit Staff Modal -->
    <div id="staffModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="modalTitle">Add New Staff</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Staff Form -->
                <form id="staffForm" onsubmit="saveStaff(event)">
                    <input type="hidden" id="staffId" value="">
                    
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
                                <label class="form-label">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="form-label flex items-center">
                                    <input type="checkbox" id="isActive" class="rounded border-gray-300 text-[#99CC33] focus:ring-[#99CC33]">
                                    <span class="ml-2 text-sm text-gray-700">Active Staff</span>
                                </label>
                            </div>
                            <div>
                                <label class="form-label flex items-center">
                                    <input type="checkbox" id="isAdmin" class="rounded border-gray-300 text-[#99CC33] focus:ring-[#99CC33]">
                                    <span class="ml-2 text-sm text-gray-700">Admin Privileges</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h4 class="text-lg font-semibold text-[#003366] mb-4">Address Information</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="form-label">Street Address <span class="text-red-500">*</span></label>
                                <input type="text" id="address" class="form-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label">City <span class="text-red-500">*</span></label>
                                <input type="text" id="city" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Province <span class="text-red-500">*</span></label>
                                <select id="province" class="form-select" required>
                                    <option value="">Select Province</option>
                                    <option value="Alberta">Alberta</option>
                                    <option value="British Columbia">British Columbia</option>
                                    <option value="Manitoba">Manitoba</option>
                                    <option value="New Brunswick">New Brunswick</option>
                                    <option value="Newfoundland and Labrador">Newfoundland and Labrador</option>
                                    <option value="Nova Scotia">Nova Scotia</option>
                                    <option value="Ontario" selected>Ontario</option>
                                    <option value="Prince Edward Island">Prince Edward Island</option>
                                    <option value="Quebec">Quebec</option>
                                    <option value="Saskatchewan">Saskatchewan</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Postal Code <span class="text-red-500">*</span></label>
                                <input type="text" id="postalCode" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Country <span class="text-red-500">*</span></label>
                                <input type="text" id="country" class="form-input" required value="Canada">
                            </div>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div class="form-section" id="documentsSection" style="display:none;">
                        <h4 class="text-lg font-semibold text-[#003366] mb-4">Documents</h4>
                        <div id="documentFields">
                            <p class="text-sm text-gray-400">Loading documents...</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" id="saveStaffBtn" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition">
                            Save Staff
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
                    <h3 class="text-xl font-bold text-black mb-2">Delete Staff</h3>
                    <p class="text-gray-600 mb-6">Are you sure you want to delete this staff member? This action cannot be undone.</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button onclick="confirmDelete()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                            Delete Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Staff Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content max-w-3xl">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-black" id="viewModalTitle">Staff Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Staff Details Content -->
                <div class="space-y-6" id="staffDetails">
                    <!-- Will be populated by JavaScript -->
                </div>

                <div class="flex justify-end mt-6">
                    <button onclick="closeViewModal()" class="px-4 py-2 bg-[#003366] text-white rounded-lg text-sm hover:bg-[#002244] transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Agreement Management Modal -->
    <div id="agreementModal" class="modal">
        <div class="modal-content max-w-3xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-black">Staff Agreements</h3>
                        <p class="text-xs text-gray-500 mt-0.5" id="agreementStaffName">—</p>
                    </div>
                    <button onclick="closeAgreementModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Upload new agreement -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-5">
                    <p class="text-sm font-semibold text-[#003366] mb-3"><i class="fas fa-paper-plane mr-1.5"></i> Send New Agreement</p>
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-medium text-gray-600 mb-1">PDF File (max 10MB)</label>
                            <input type="file" id="agreementFile" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white">
                        </div>
                        <button onclick="sendAgreement()" id="sendAgreementBtn"
                                class="px-4 py-2 bg-[#99CC33] hover:bg-[#88bb22] text-white rounded-lg text-sm font-semibold disabled:opacity-50 transition flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>

                <!-- Agreement list -->
                <p class="text-sm font-semibold text-[#003366] mb-3"><i class="fas fa-list mr-1.5"></i> Agreement History</p>
                <div id="agreementList" class="space-y-2">
                    <p class="text-center text-sm text-gray-400 py-6">Loading…</p>
                </div>

                <div class="flex justify-end mt-5 border-t pt-4">
                    <button onclick="closeAgreementModal()" class="px-4 py-2 bg-[#003366] text-white rounded-lg text-sm hover:bg-[#002244] transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Signed Agreement Modal -->
    <div id="signedAgreementModal" class="modal">
        <div class="modal-content max-w-4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-black">Signed Agreement</h3>
                    <button onclick="closeSignedModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="signedAgreementBody">
                    <!-- Populated by JS -->
                </div>

                <div class="flex justify-end mt-5 border-t pt-4">
                    <button onclick="closeSignedModal()" class="px-4 py-2 bg-[#003366] text-white rounded-lg text-sm hover:bg-[#002244] transition">
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
                <p id="toastMessage" class="text-sm text-gray-600">Staff saved successfully</p>
            </div>
        </div>
    </div>

    <script>
        // Sample staff data (would come from database in production)
        let staff = [];

        function fetch_all_staff() {
            // This function would normally fetch data from the server and populate the staff array, make an AJAX call to get staff data from the server
            $.ajax({
                url: 'fetch_all_staff', // Replace with your actual API endpoint
                method: 'POST',
                dataType: 'json',
                success: function(data) {
                    staff = data.staffs;
                    filteredStaff = [...staff];
                    updateStats();
                    renderStaffTable();
                },
                error: function() {
                    console.error('Failed to fetch staff data');
                }
            });
        }

        let currentPage = 1;
        let itemsPerPage = 5;
        let filteredStaff = [...staff];
        let selectedStaffId = null;
        let searchTerm = '';

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            fetch_all_staff();
        });

        // Update statistics
        function updateStats() {
            document.getElementById('totalStaff').textContent = staff.length;
            document.getElementById('activeStaff').textContent = staff.filter(s => s.isActive).length;
            document.getElementById('inactiveStaff').textContent = staff.filter(s => !s.isActive).length;
            document.getElementById('adminCount').textContent = staff.filter(s => s.isAdmin).length;
        }

        // Render staff table
        function renderStaffTable() {
            const tbody = document.getElementById('staffTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedStaff = filteredStaff.slice(start, end);

            if (paginatedStaff.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            No staff members found. Click "Add New Staff" to create one.
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = paginatedStaff.map(s => `
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-[#003366] rounded-lg flex items-center justify-center text-white font-bold text-sm">
                                    ${s.firstname.charAt(0)}${s.lastname.charAt(0)}
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900">${s.firstname} ${s.middlename ? s.middlename + ' ' : ''}${s.lastname}</p>
                                    <p class="text-xs text-gray-500">${s.email}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-sm">${s.phone}</p>
                            <p class="text-xs text-gray-500">${s.email}</p>
                        </td>
                        <td>
                            <p class="text-sm">${s.address}</p>
                            <p class="text-xs text-gray-500">${s.city}, ${s.province} ${s.postalCode}</p>
                        </td>
                        <td>
                            <span class="badge ${s.isActive ? 'badge-active' : 'badge-inactive'}">
                                ${s.isActive ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${s.isAdmin ? 'badge-admin' : 'badge-staff'}">
                                ${s.isAdmin ? 'Admin' : 'Staff'}
                            </span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button onclick="viewStaff(${s.id})" class="action-btn view" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editStaff(${s.id})" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openAgreementModal(${s.id}, '${s.firstname} ${s.lastname}')" class="action-btn agreement" title="Agreements">
                                    <i class="fas fa-file-signature"></i>
                                </button>
                                <button onclick="openDeleteModal(${s.id})" class="action-btn delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            updatePagination();
        }

        // Search staff
        function searchStaff() {
            searchTerm = document.getElementById('searchInput').value.toLowerCase();
            applyStaffFilters();
        }

        // Filter staff (called when any filter changes)
        function filterStaff() {
            applyStaffFilters();
        }

        // Combined filter function
        function applyStaffFilters() {
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;
            const provinceFilter = document.getElementById('provinceFilter').value;
            
            console.log('Applying filters:', { searchTerm, statusFilter, roleFilter, provinceFilter });
            
            // Always start from the original staff array
            let tempStaff = [...staff];
            
            // Apply search filter
            if (searchTerm) {
                tempStaff = tempStaff.filter(s => 
                    (s.firstname && s.firstname.toLowerCase().includes(searchTerm)) ||
                    (s.lastname && s.lastname.toLowerCase().includes(searchTerm)) ||
                    (s.email && s.email.toLowerCase().includes(searchTerm)) ||
                    (s.phone && s.phone.includes(searchTerm)) ||
                    (s.city && s.city.toLowerCase().includes(searchTerm)) ||
                    (s.province && s.province.toLowerCase().includes(searchTerm))
                );
            }
            
            // Apply status filter
            if (statusFilter !== 'all') {
                const isActive = statusFilter === 'active';
                tempStaff = tempStaff.filter(s => s.isActive === isActive);
            }
            
            // Apply role filter
            if (roleFilter !== 'all') {
                const isAdmin = roleFilter === 'admin';
                tempStaff = tempStaff.filter(s => s.isAdmin === isAdmin);
            }
            
            // Apply province filter
            if (provinceFilter !== 'all') {
                tempStaff = tempStaff.filter(s => s.province === provinceFilter);
            }
            
            console.log(`Filtered from ${staff.length} to ${tempStaff.length} staff members`);
            
            filteredStaff = tempStaff;
            currentPage = 1;
            renderStaffTable();
        }

        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(filteredStaff.length / itemsPerPage);
            document.getElementById('paginationInfo').textContent = 
                `Showing ${filteredStaff.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0} to ${Math.min(currentPage * itemsPerPage, filteredStaff.length)} of ${filteredStaff.length} staff`;
            
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
            
            // Update page buttons
            for (let i = 1; i <= 3; i++) {
                const btn = document.getElementById(`page${i}`);
                if (i <= totalPages) {
                    btn.classList.remove('hidden');
                    btn.textContent = i;
                    btn.onclick = function() { changePage(i); };
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
        function changePage(page) {
            const totalPages = Math.ceil(filteredStaff.length / itemsPerPage);
            if (page === -1 && currentPage > 1) {
                currentPage--;
            } else if (page === 1 && currentPage < totalPages) {
                currentPage++;
            } else if (typeof page === 'number' && page >= 1 && page <= totalPages) {
                currentPage = page;
            }
            renderStaffTable();
        }

        // ── Documents helpers ──────────────────────────────────────────────────

        let allDocuments = [];

        function loadDocuments(staffId) {
            $.ajax({
                url: 'fetch_all_documents',
                method: 'POST',
                dataType: 'json',
                success(res) {
                    allDocuments = res.documents || [];
                    if (!allDocuments.length) {
                        document.getElementById('documentsSection').style.display = 'none';
                        return;
                    }
                    document.getElementById('documentsSection').style.display = '';
                    if (staffId) {
                        // Editing: fetch existing uploads then render
                        $.ajax({
                            url: 'fetch_user_documents',
                            method: 'POST',
                            data: { staff_id: staffId },
                            dataType: 'json',
                            success(udRes) {
                                renderDocumentFields(allDocuments, udRes.user_documents || []);
                            },
                            error() { renderDocumentFields(allDocuments, []); }
                        });
                    } else {
                        renderDocumentFields(allDocuments, []);
                    }
                },
                error() {
                    document.getElementById('documentsSection').style.display = 'none';
                }
            });
        }

        function renderDocumentFields(docs, userDocs) {
            const container = document.getElementById('documentFields');
            const uploadMap = {};
            userDocs.forEach(ud => { uploadMap[ud.doc_id] = ud; });

            container.innerHTML = docs.map(doc => {
                const ud        = uploadMap[doc.doc_id];
                const isRequired = !doc.optional;
                const uploaded  = ud && ud.file_path;

                return `
                <div class="mb-4 p-3 rounded-lg border ${isRequired ? 'border-orange-200 bg-orange-50' : 'border-gray-200 bg-gray-50'}">
                    <label class="form-label mb-1">
                        ${doc.doc_name}
                        ${isRequired ? '<span class="text-red-500">*</span>' : '<span class="text-gray-400 text-xs">(optional)</span>'}
                    </label>
                    ${uploaded ? `
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-green-600 flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> ${ud.original_name}
                            </span>
                            <label class="text-xs text-blue-500 cursor-pointer hover:underline">
                                Replace
                                <input type="file" class="doc-file hidden" data-doc-id="${doc.doc_id}" data-doc-tag="${doc.doc_tag}" data-required="${isRequired}" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </label>
                        </div>
                    ` : `
                        <input type="file" class="doc-file form-input p-1" data-doc-id="${doc.doc_id}" data-doc-tag="${doc.doc_tag}" data-required="${isRequired}" ${isRequired ? 'required' : ''} accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <p class="text-xs text-gray-400 mt-1">Allowed: PDF, JPG, PNG, DOC, DOCX (max 5MB)</p>
                    `}
                </div>`;
            }).join('');
        }

        // ── Modal functions ────────────────────────────────────────────────────

        function openAddStaffModal() {
            document.getElementById('modalTitle').textContent = 'Add New Staff';
            document.getElementById('staffForm').reset();
            document.getElementById('staffId').value = '';
            document.getElementById('isActive').checked = false;
            document.getElementById('isAdmin').checked = false;
            document.getElementById('country').value = 'Canada';
            document.getElementById('province').value = 'Ontario';
            document.getElementById('documentFields').innerHTML = '<p class="text-sm text-gray-400">Loading documents...</p>';
            document.getElementById('staffModal').classList.add('active');
            loadDocuments(null);
        }

        function closeModal() {
            document.getElementById('staffModal').classList.remove('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        // ── Upload pending document files for a staff ──────────────────────────
        function uploadPendingDocuments(staffId, callback) {
            const fileInputs = document.querySelectorAll('.doc-file');
            const uploads = [];
            fileInputs.forEach(input => {
                if (input.files && input.files[0]) {
                    uploads.push({ input, doc_id: input.dataset.docId, doc_tag: input.dataset.docTag });
                }
            });
            if (!uploads.length) { callback(); return; }

            let done = 0;
            uploads.forEach(({ input, doc_id, doc_tag }) => {
                const fd = new FormData();
                fd.append('staff_id', staffId);
                fd.append('doc_id',   doc_id);
                fd.append('doc_tag',  doc_tag);
                fd.append('document', input.files[0]);

                $.ajax({
                    url: 'save_user_document',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    complete() {
                        done++;
                        if (done === uploads.length) callback();
                    }
                });
            });
        }

        // ── Validate required documents not yet uploaded ───────────────────────
        function validateRequiredDocs(staffId) {
            const fileInputs = document.querySelectorAll('.doc-file');
            for (const input of fileInputs) {
                if (input.dataset.required === 'true' && (!input.files || !input.files[0])) {
                    // If editing and document already uploaded (input is the hidden "Replace" type), skip
                    if (input.classList.contains('hidden')) continue;
                    const label = input.closest('.mb-4').querySelector('.form-label');
                    const docName = label ? label.textContent.trim().replace('*', '').trim() : 'A required document';
                    showToast('Validation Error', `"${docName}" is required`, 'error');
                    return false;
                }
            }
            return true;
        }

        // Save staff
        function saveStaff(event) {
            event.preventDefault();

            if (!validateRequiredDocs()) return;

            const staffId = document.getElementById('staffId').value;
            const staffData = {
                id:         staffId ? parseInt(staffId) : 0,
                firstname:  document.getElementById('firstname').value,
                middlename: document.getElementById('middlename').value,
                lastname:   document.getElementById('lastname').value,
                isActive:   document.getElementById('isActive').checked,
                isAdmin:    document.getElementById('isAdmin').checked,
                address:    document.getElementById('address').value,
                city:       document.getElementById('city').value,
                province:   document.getElementById('province').value,
                postalCode: document.getElementById('postalCode').value,
                country:    document.getElementById('country').value,
                phone:      document.getElementById('phone').value,
                email:      document.getElementById('email').value,
            };

            const btn = document.getElementById('saveStaffBtn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const finalize = (resolvedStaffId) => {
                uploadPendingDocuments(resolvedStaffId, () => {
                    fetch_all_staff();
                    closeModal();
                    btn.disabled = false;
                    btn.textContent = 'Save Staff';
                    showToast('Success', staffId ? 'Staff updated successfully' : 'Staff added successfully', 'success');
                });
            };

            if (staffId) {
                $.ajax({
                    url: 'update_staff',
                    method: 'POST',
                    data: staffData,
                    dataType: 'json',
                    success(response) {
                        if (response.status) {
                            finalize(parseInt(staffId));
                        } else {
                            btn.disabled = false; btn.textContent = 'Save Staff';
                            showToast('Error', response.message, 'error');
                        }
                    },
                    error() {
                        btn.disabled = false; btn.textContent = 'Save Staff';
                        showToast('Error', 'Failed to update staff', 'error');
                    }
                });
            } else {
                $.ajax({
                    url: 'create_new_staff',
                    method: 'POST',
                    data: staffData,
                    dataType: 'json',
                    success(response) {
                        if (response.status) {
                            finalize(response.staff_id);
                        } else {
                            btn.disabled = false; btn.textContent = 'Save Staff';
                            showToast('Error', response.message || 'Failed to add staff', 'error');
                        }
                    },
                    error() {
                        btn.disabled = false; btn.textContent = 'Save Staff';
                        showToast('Error', 'Failed to add staff', 'error');
                    }
                });
            }
        }

        // Edit staff
        function editStaff(id) {
            const s = staff.find(s => s.id === id);
            if (s) {
                document.getElementById('modalTitle').textContent = 'Edit Staff';
                document.getElementById('staffId').value = s.id;
                document.getElementById('firstname').value = s.firstname;
                document.getElementById('middlename').value = s.middlename || '';
                document.getElementById('lastname').value = s.lastname;
                document.getElementById('phone').value = s.phone;
                document.getElementById('email').value = s.email;
                document.getElementById('isActive').checked = s.isActive;
                document.getElementById('isAdmin').checked = s.isAdmin;
                document.getElementById('address').value = s.address;
                document.getElementById('city').value = s.city;
                document.getElementById('province').value = s.province;
                document.getElementById('postalCode').value = s.postalCode;
                document.getElementById('country').value = s.country;
                document.getElementById('documentFields').innerHTML = '<p class="text-sm text-gray-400">Loading documents...</p>';
                document.getElementById('staffModal').classList.add('active');
                loadDocuments(s.id);
            }
        }

        // View staff
        function viewStaff(id) {
            console.log('Viewing staff with ID:', id);
            console.log('Current staff list:', staff);
            const s = staff.find(s => s.id === id);
            console.log('Staff details:', s);
            if (s) {
                document.getElementById('viewModalTitle').textContent = `Staff Details - ${s.firstname} ${s.lastname}`;
                
                const detailsHtml = `
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2">Personal Information</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Full Name</p>
                                    <p class="font-medium break-words whitespace-normal">${s.firstname} ${s.middlename ? s.middlename + ' ' : ''}${s.lastname}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Status</p>
                                    <span class="badge ${s.isActive ? 'badge-active' : 'badge-inactive'} mt-1">
                                        ${s.isActive ? 'Active' : 'Inactive'}
                                    </span>
                                    <span class="badge ${s.isAdmin ? 'badge-admin' : 'badge-staff'} ml-2">
                                        ${s.isAdmin ? 'Admin' : 'Staff'}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Phone</p>
                                    <p class="font-medium break-words whitespace-normal">${s.phone}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-medium break-words whitespace-normal">${s.email}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#003366] mb-2">Address</h4>
                            <p class="font-medium break-words whitespace-normal">${s.address}</p>
                            <p class="break-words whitespace-normal">${s.city}, ${s.province} ${s.postalCode}</p>
                            <p class="break-words whitespace-normal">${s.country}</p>
                        </div>
                        
                        <div class="col-span-2 text-xs text-gray-500">
                            Registered on: ${new Date(s.regDate).toLocaleString()}
                        </div>
                    </div>
                `;
                
                document.getElementById('staffDetails').innerHTML = detailsHtml;
                document.getElementById('viewModal').classList.add('active');
            }
        }

        // Delete functions
        function openDeleteModal(id) {
            selectedStaffId = id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function confirmDelete() {
            //write a function to delete staff data from the database using AJAX, make an AJAX call to delete staff data on the server
            $.ajax({
                url: 'delete_staff', // Replace with your actual API endpoint
                method: 'POST',
                data: { staff_id: selectedStaffId },
                dataType: 'json',
                success: function(response) {
                    if(response.status){
                        staff = staff.filter(s => s.id !== selectedStaffId);
                        filteredStaff = [...staff];
                        updateStats();
                        renderStaffTable();
                        closeDeleteModal();
                        showToast('Success', 'Staff deleted successfully', 'success');
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function() {
                    console.error('Failed to delete staff data');
                    showToast('Error', 'Failed to delete staff', 'error');
                }
            });
        }

        // Toast notification
        // ─── Staff Agreements ────────────────────────────────────────────────
        let currentAgreementStaffId = null;

        function openAgreementModal(staffId, staffName) {
            currentAgreementStaffId = staffId;
            document.getElementById('agreementStaffName').textContent = 'For: ' + staffName;
            document.getElementById('agreementFile').value = '';
            document.getElementById('agreementList').innerHTML = '<p class="text-center text-sm text-gray-400 py-6">Loading…</p>';
            document.getElementById('agreementModal').classList.add('active');
            loadAgreements();
        }

        function closeAgreementModal() {
            document.getElementById('agreementModal').classList.remove('active');
            currentAgreementStaffId = null;
        }

        function loadAgreements() {
            $.ajax({
                url: 'fetch_staff_agreements',
                method: 'POST',
                data: { staff_id: currentAgreementStaffId },
                dataType: 'json',
                success: function(res) {
                    if (res.status) renderAgreements(res.agreements || []);
                    else document.getElementById('agreementList').innerHTML = '<p class="text-center text-sm text-red-500 py-6">' + (res.message || 'Failed to load') + '</p>';
                },
                error: function() {
                    document.getElementById('agreementList').innerHTML = '<p class="text-center text-sm text-red-500 py-6">Connection error.</p>';
                }
            });
        }

        function renderAgreements(list) {
            const wrap = document.getElementById('agreementList');
            if (list.length === 0) {
                wrap.innerHTML = '<p class="text-center text-sm text-gray-400 py-6"><i class="far fa-folder-open mr-1"></i> No agreements sent yet.</p>';
                return;
            }
            const statusColors = {
                pending:  { bg:'bg-gray-100', text:'text-gray-600', icon:'fa-clock' },
                viewed:   { bg:'bg-blue-100', text:'text-blue-700', icon:'fa-eye' },
                signed:   { bg:'bg-green-100', text:'text-green-700', icon:'fa-check-circle' },
                expired:  { bg:'bg-red-100', text:'text-red-700', icon:'fa-times-circle' }
            };
            wrap.innerHTML = list.map(function(a) {
                const sc = statusColors[a.status] || statusColors.pending;
                const fmt = function(d) { return d ? new Date(d).toLocaleString('en-CA') : '—'; };
                const viewBtn = a.status === 'signed'
                    ? '<button onclick="viewSignedAgreement(' + a.agreement_id + ')" class="text-[#003366] hover:underline text-xs font-semibold ml-3"><i class="fas fa-eye mr-1"></i>View</button>'
                    : '';
                const copyBtn = a.status !== 'signed'
                    ? '<button onclick="copySigningLink(\'' + a.token + '\')" class="text-[#003366] hover:underline text-xs font-semibold ml-3"><i class="fas fa-link mr-1"></i>Copy Link</button>'
                    : '';
                return '<div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 bg-white">'
                    + '<div class="flex-1 min-w-0">'
                        + '<div class="flex items-center gap-2 mb-1">'
                            + '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' + sc.bg + ' ' + sc.text + '">'
                                + '<i class="fas ' + sc.icon + ' mr-1"></i>' + (a.status.charAt(0).toUpperCase() + a.status.slice(1))
                            + '</span>'
                            + '<p class="text-sm font-semibold text-gray-800 truncate">' + escapeHtml(a.original_filename) + '</p>'
                        + '</div>'
                        + '<p class="text-xs text-gray-500">Sent: ' + fmt(a.sent_at) + (a.signed_at ? ' &bull; Signed: ' + fmt(a.signed_at) + ' by ' + escapeHtml(a.signer_name || '—') : '') + '</p>'
                    + '</div>'
                    + '<div class="flex items-center">' + viewBtn + copyBtn + '</div>'
                + '</div>';
            }).join('');
        }

        function copySigningLink(token) {
            const url = window.location.origin + '/sign_agreement?token=' + token;
            navigator.clipboard.writeText(url).then(function() {
                showToast('Copied', 'Signing link copied to clipboard.', 'success');
            }).catch(function() {
                prompt('Copy this signing link:', url);
            });
        }

        function escapeHtml(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
            });
        }

        function sendAgreement() {
            const fileInput = document.getElementById('agreementFile');
            if (!fileInput.files.length) {
                return showToast('No File', 'Please choose a PDF file first.', 'error');
            }
            const file = fileInput.files[0];
            if (file.type !== 'application/pdf') {
                return showToast('Invalid Type', 'Only PDF files are allowed.', 'error');
            }

            const btn = document.getElementById('sendAgreementBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';

            const fd = new FormData();
            fd.append('staff_id', currentAgreementStaffId);
            fd.append('agreement', file);

            $.ajax({
                url: 'send_agreement',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
                    if (res.status) {
                        showToast('Sent', 'Agreement sent for signature.', 'success');
                        fileInput.value = '';
                        loadAgreements();
                    } else {
                        showToast('Error', res.message || 'Failed to send agreement.', 'error');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
                    showToast('Error', 'Connection error. Please try again.', 'error');
                }
            });
        }

        function viewSignedAgreement(agreementId) {
            $.ajax({
                url: 'view_signed_agreement',
                method: 'POST',
                data: { agreement_id: agreementId },
                dataType: 'json',
                success: function(res) {
                    if (!res.status) return showToast('Error', res.message || 'Failed to load.', 'error');
                    const a = res.agreement;
                    const fmt = function(d) { return d ? new Date(d).toLocaleString('en-CA') : '—'; };
                    document.getElementById('signedAgreementBody').innerHTML = `
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Signer</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${escapeHtml(a.signer_name || '—')}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Signed At</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${fmt(a.signed_at)}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase font-semibold">IP Address</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${escapeHtml(a.signer_ip || '—')}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Document</p>
                                <a href="public/${escapeHtml(a.template_file.replace(/^\//, ''))}" target="_blank" class="text-sm font-semibold text-[#003366] hover:underline mt-1 inline-block">
                                    <i class="fas fa-file-pdf mr-1"></i> ${escapeHtml(a.original_filename)}
                                </a>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-white">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Signature</p>
                            <div class="border border-dashed border-gray-300 rounded p-3 bg-gray-50 text-center">
                                <img src="${a.signature_data}" alt="Signature" style="max-height:120px;max-width:100%;display:inline-block;">
                            </div>
                        </div>
                        ${a.signer_user_agent ? `<p class="text-xs text-gray-400 mt-3"><strong>Device:</strong> ${escapeHtml(a.signer_user_agent)}</p>` : ''}
                    `;
                    document.getElementById('signedAgreementModal').classList.add('active');
                },
                error: function() { showToast('Error', 'Failed to load agreement.', 'error'); }
            });
        }

        function closeSignedModal() {
            document.getElementById('signedAgreementModal').classList.remove('active');
        }

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
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>