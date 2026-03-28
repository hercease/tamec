
    <?php
        // Get the current page filename
        $current_page = basename($_SERVER['REQUEST_URI']);
        $current_page = str_replace('.php', '', $current_page);
        if ($current_page == '' || $current_page == 'index') {
            $current_page = 'dashboard';
        }

        // Function to check if a menu item is active
        function isActive($pageName, $currentPage) {
            return $currentPage == $pageName;
        }

        // Function to get active class
        function getActiveClass($pageName, $currentPage) {
            return isActive($pageName, $currentPage) 
                ? 'bg-[#99CC33] bg-opacity-20 text-white border-l-4 border-[#99CC33]' 
                : 'hover:bg-[#669933] hover:bg-opacity-20';
        }
    ?>
    <aside id="sidebar" class="sidebar fixed top-0 left-0 z-40 w-64 h-screen bg-[#003366] text-white transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Sidebar Header -->
        <div class="flex-shrink-0 relative p-4 border-b border-[#669933] border-opacity-30">
            <div class="flex items-center space-x-3">
                <div class="bg-white p-2 rounded-lg">
                    <div class="text-center">
                        <img src="public/images/tamecfavicon.jpeg" class="w-8 h-8 rounded-full mx-auto">
                    </div>
                </div>
                <div>
                    <h2 class="text-lg font-bold">TAMEC</h2>
                    <p class="text-[#99CC33] text-xs">CARE STAFFING SERVICES</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button onclick="closeSidebar()" class="absolute top-4 right-4 text-white lg:hidden">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <!-- Dashboard -->
                <li>
                    <a href="dashboard" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('dashboard', $current_page); ?>">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                
                <!-- Staffs -->
                <li>
                    <a href="staffs" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('staffs', $current_page); ?>">
                        <i class="fas fa-user-nurse w-6"></i>
                        <span class="ml-3">Staffs</span>
                        <span class="ml-auto bg-[#99CC33] text-white text-xs px-2 py-1 rounded-full"><?php echo $counters['total_staffs']; ?></span>
                    </a>
                </li>
                
                <!-- Clients -->
                <li>
                    <a href="clients" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('clients', $current_page); ?>">
                        <i class="fas fa-user-tie w-6"></i>
                        <span class="ml-3">Clients</span>
                        <span class="ml-auto bg-[#99CC33] text-white text-xs px-2 py-1 rounded-full"><?php echo $counters['total_clients']; ?></span>
                    </a>
                </li>
                
                <!-- Schedule -->
                <li>
                    <a href="schedules" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('schedules', $current_page); ?>">
                        <i class="fas fa-calendar-alt w-6"></i>
                        <span class="ml-3">Schedule</span>
                        <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $counters['total_schedules']; ?></span>
                    </a>
                </li>
                
                <!-- Payroll -->
                <li>
                    <a href="payrolls" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('payrolls', $current_page); ?>">
                        <i class="fas fa-money-bill-wave w-6"></i>
                        <span class="ml-3">Payroll</span>
                        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $counters['total_payrolls']; ?></span>
                    </a>
                </li>
                
                <!-- Invoices -->
                <li>
                    <a href="invoices" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('invoices', $current_page); ?>">
                        <i class="fas fa-file-invoice w-6"></i>
                        <span class="ml-3">Invoices</span>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $counters['total_invoices']; ?></span>
                    </a>
                </li>

                <!-- Activities -->
                <li>
                    <a href="activities" class="flex items-center px-4 py-3 rounded-lg transition <?php echo getActiveClass('activities', $current_page); ?>">
                        <i class="fas fa-history w-6"></i>
                        <span class="ml-3">Activities</span>
                    </a>
                </li>

                <!-- Divider -->
                <li class="my-4 border-t border-[#669933] border-opacity-30"></li>

                <!-- Holidays -->
                <li>
                    <a href="holidays" class="flex items-center px-4 py-3 hover:bg-[#669933] hover:bg-opacity-20 rounded-lg transition <?php echo getActiveClass('holidays', $current_page); ?>">
                        <i class="fas fa-sun w-6"></i>
                        <span class="ml-3">Holidays</span>
                    </a>
                </li>

                <!-- Documents -->
                <li>
                    <a href="documents" class="flex items-center px-4 py-3 hover:bg-[#669933] hover:bg-opacity-20 rounded-lg transition <?php echo getActiveClass('documents', $current_page); ?>">
                        <i class="fas fa-file-alt w-6"></i>
                        <span class="ml-3">Documents</span>
                    </a>
                </li>

                <!-- Change Password -->
                <li>
                    <a href="change_password" class="flex items-center px-4 py-3 hover:bg-[#669933] hover:bg-opacity-20 rounded-lg transition <?php echo getActiveClass('change_password', $current_page); ?>">
                        <i class="fas fa-key w-6"></i>
                        <span class="ml-3">Change Password</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Profile at Bottom -->
        <div class="flex-shrink-0 p-4 border-t border-[#669933] border-opacity-30">
            <div class="flex items-center">
                <?php $adminName = htmlspecialchars($_SESSION['tamec_name'] ?? 'Admin'); ?>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminName); ?>&background=99CC33&color=fff&size=40" class="w-10 h-10 rounded-full">
                <div class="ml-3">
                    <p class="text-sm font-semibold"><?php echo $adminName; ?></p>
                    <p class="text-xs text-[#99CC33]">Administrator</p>
                </div>
                <a href="logout" class="ml-auto text-gray-300 hover:text-white" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>