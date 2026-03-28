<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Dashboard</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #99CC33; border-radius: 4px; }

        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }

        .overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            z-index: 30; opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .overlay.active { opacity: 1; visibility: visible; }

        .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px -5px rgba(0,0,0,0.12); }

        .timeline-dot {
            position: absolute; left: 0; top: 0.9rem;
            width: 0.65rem; height: 0.65rem;
            border-radius: 50%; background: #99CC33;
        }
        .timeline-line {
            position: absolute; left: 0.3rem; top: 1.7rem;
            width: 2px; height: calc(100% - 1.2rem);
            background: #E5E7EB;
        }

        .quick-action { transition: all 0.2s; }
        .quick-action:hover { transform: translateY(-1px); }
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
                    <!-- Mobile: hamburger + brand -->
                    <div class="flex items-center lg:hidden">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <span class="ml-3 text-sm font-bold text-[#003366]">TAMEC</span>
                    </div>

                    <!-- Desktop: page title -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <i class="fas fa-tachometer-alt text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Dashboard</h2>
                    </div>

                    <!-- Right: quick actions + user -->
                    <div class="flex items-center space-x-2">
                        <!-- Quick create (desktop) -->
                        <div class="hidden md:flex items-center space-x-2 mr-2">
                            <a href="create_schedule" class="quick-action inline-flex items-center px-3 py-1.5 text-xs font-semibold bg-[#003366] text-white rounded-lg hover:bg-[#004080]">
                                <i class="fas fa-plus mr-1.5"></i>Schedule
                            </a>
                            <a href="create_payroll" class="quick-action inline-flex items-center px-3 py-1.5 text-xs font-semibold border border-[#99CC33] text-[#003366] rounded-lg hover:bg-[#99CC33] hover:text-white">
                                <i class="fas fa-plus mr-1.5"></i>Payroll
                            </a>
                            <a href="create_invoice" class="quick-action inline-flex items-center px-3 py-1.5 text-xs font-semibold border border-gray-200 text-gray-600 rounded-lg hover:border-[#99CC33] hover:text-[#003366]">
                                <i class="fas fa-plus mr-1.5"></i>Invoice
                            </a>
                        </div>

                        <!-- User info -->
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

            <!-- Welcome Banner -->
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-l-4 border-[#99CC33]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[#003366]"><?php echo $greetings; ?></h1>
                        <p class="text-gray-400 text-sm mt-0.5"><?php echo $formattedDate; ?></p>
                    </div>
                    <!-- Mobile quick actions -->
                    <div class="flex flex-wrap gap-2 md:hidden">
                        <a href="create_schedule" class="inline-flex items-center px-3 py-2 text-xs font-semibold bg-[#003366] text-white rounded-lg">
                            <i class="fas fa-plus mr-1.5"></i>Schedule
                        </a>
                        <a href="create_payroll" class="inline-flex items-center px-3 py-2 text-xs font-semibold bg-[#99CC33] text-white rounded-lg">
                            <i class="fas fa-plus mr-1.5"></i>Payroll
                        </a>
                        <a href="create_invoice" class="inline-flex items-center px-3 py-2 text-xs font-semibold border border-gray-200 text-gray-600 rounded-lg">
                            <i class="fas fa-plus mr-1.5"></i>Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <!-- Staffs -->
                <div class="stat-card bg-white rounded-xl shadow-sm p-4 sm:p-5 border-t-4 border-[#99CC33]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Total Staffs</p>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1"><?php echo $stats['total_staffs']; ?></h3>
                            <span class="text-[#99CC33] text-xs font-semibold">+<?php echo $stats['staffs_increment']; ?> this month</span>
                        </div>
                        <div class="w-10 h-10 bg-[#99CC33] bg-opacity-10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-nurse text-[#99CC33] text-base"></i>
                        </div>
                    </div>
                </div>

                <!-- Clients -->
                <div class="stat-card bg-white rounded-xl shadow-sm p-4 sm:p-5 border-t-4 border-[#003366]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Total Clients</p>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1"><?php echo $stats['total_clients']; ?></h3>
                            <span class="text-blue-600 text-xs font-semibold">+<?php echo $stats['clients_increment']; ?> new</span>
                        </div>
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-tie text-[#003366] text-base"></i>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="stat-card bg-white rounded-xl shadow-sm p-4 sm:p-5 border-t-4 border-amber-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Today's Shifts</p>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1"><?php echo $stats['today_schedules']; ?></h3>
                            <span class="text-amber-500 text-xs font-semibold"><?php echo $stats['ongoing_schedules']; ?> in progress</span>
                        </div>
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-day text-amber-500 text-base"></i>
                        </div>
                    </div>
                </div>

                <!-- Weekly Payroll -->
                <div class="stat-card bg-white rounded-xl shadow-sm p-4 sm:p-5 border-t-4 border-emerald-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Week's Payroll</p>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1">$<?php echo number_format($stats['weekly_payroll'], 2); ?></h3>
                            <span class="text-emerald-600 text-xs font-semibold"><?php echo $stats['total_staffs_by_payroll']; ?> schedules</span>
                        </div>
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-emerald-500 text-base"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule + Availability -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">

                <!-- Today's Schedule Timeline -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Today's Care Schedule</h3>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo $todaySchedule['date_formatted'] ?? date('F j, Y'); ?></p>
                        </div>
                        <a href="schedules" class="text-xs font-semibold text-[#003366] hover:text-[#99CC33] transition-colors">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">

                        <?php if (!empty($todaySchedule['grouped']['day'])): ?>
                        <div class="relative pl-5">
                            <span class="timeline-dot bg-[#99CC33]"></span>
                            <span class="timeline-line"></span>
                            <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center text-xs font-bold px-3 py-1 bg-[#99CC33] text-white rounded-full">
                                        <i class="fas fa-sun mr-1.5"></i>Day Shift
                                    </span>
                                    <span class="text-xs font-semibold text-[#003366]">
                                        <?php echo count($todaySchedule['grouped']['day']); ?> staff
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($todaySchedule['grouped']['day'], 0, 4) as $s): ?>
                                    <div class="flex items-center bg-white rounded-lg px-2 py-1 border border-gray-100">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($s['staff_firstname'] . '+' . $s['staff_lastname']); ?>&background=99CC33&color=fff&size=24" class="w-5 h-5 rounded-full">
                                        <span class="ml-1.5 text-xs text-gray-700"><?php echo htmlspecialchars($s['staff_firstname'] . ' ' . $s['staff_lastname']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($todaySchedule['grouped']['day']) > 4): ?>
                                    <span class="text-xs text-gray-400 self-center">+<?php echo count($todaySchedule['grouped']['day']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $dayLocations = array_unique(array_filter(array_column($todaySchedule['grouped']['day'], 'location_name')));
                                if (!empty($dayLocations)):
                                ?>
                                <p class="mt-2 text-xs text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars(implode(', ', array_slice($dayLocations, 0, 2))); ?><?php echo count($dayLocations) > 2 ? '...' : ''; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($todaySchedule['grouped']['evening'])): ?>
                        <div class="relative pl-5">
                            <span class="timeline-dot bg-amber-400"></span>
                            <span class="timeline-line"></span>
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center text-xs font-bold px-3 py-1 bg-amber-400 text-white rounded-full">
                                        <i class="fas fa-cloud-sun mr-1.5"></i>Evening Shift
                                    </span>
                                    <span class="text-xs font-semibold text-[#003366]">
                                        <?php echo count($todaySchedule['grouped']['evening']); ?> staff
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($todaySchedule['grouped']['evening'], 0, 4) as $s): ?>
                                    <div class="flex items-center bg-white rounded-lg px-2 py-1 border border-gray-100">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($s['staff_firstname'] . '+' . $s['staff_lastname']); ?>&background=F59E0B&color=fff&size=24" class="w-5 h-5 rounded-full">
                                        <span class="ml-1.5 text-xs text-gray-700"><?php echo htmlspecialchars($s['staff_firstname'] . ' ' . $s['staff_lastname']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($todaySchedule['grouped']['evening']) > 4): ?>
                                    <span class="text-xs text-gray-400 self-center">+<?php echo count($todaySchedule['grouped']['evening']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($todaySchedule['grouped']['overnight'])): ?>
                        <div class="relative pl-5">
                            <span class="timeline-dot bg-[#003366]"></span>
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center text-xs font-bold px-3 py-1 bg-[#003366] text-white rounded-full">
                                        <i class="fas fa-moon mr-1.5"></i>Night Shift
                                    </span>
                                    <span class="text-xs font-semibold text-[#003366]">
                                        <?php echo count($todaySchedule['grouped']['overnight']); ?> staff
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($todaySchedule['grouped']['overnight'], 0, 4) as $s): ?>
                                    <div class="flex items-center bg-white rounded-lg px-2 py-1 border border-gray-100">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($s['staff_firstname'] . '+' . $s['staff_lastname']); ?>&background=003366&color=fff&size=24" class="w-5 h-5 rounded-full">
                                        <span class="ml-1.5 text-xs text-gray-700"><?php echo htmlspecialchars($s['staff_firstname'] . ' ' . $s['staff_lastname']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($todaySchedule['grouped']['overnight']) > 4): ?>
                                    <span class="text-xs text-gray-400 self-center">+<?php echo count($todaySchedule['grouped']['overnight']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (($todaySchedule['counts']['total'] ?? 0) == 0): ?>
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-calendar-day text-4xl text-gray-200 mb-3 block"></i>
                            <p class="text-sm font-medium">No shifts scheduled for today</p>
                            <a href="create_schedule" class="mt-3 inline-flex items-center text-xs text-[#003366] hover:text-[#99CC33] font-semibold">
                                <i class="fas fa-plus mr-1"></i>Create a schedule
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Staff Availability -->
                <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900">Staff Status</h3>
                        <span class="text-xs text-gray-400">Today</span>
                    </div>

                    <!-- Summary pills -->
                    <div class="grid grid-cols-3 gap-2 mb-5">
                        <div class="text-center p-3 bg-emerald-50 rounded-xl">
                            <span class="text-xl font-extrabold text-emerald-600"><?php echo $staffavailability['summary']['on_duty']; ?></span>
                            <p class="text-xs text-gray-500 mt-0.5">On Duty</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <span class="text-xl font-extrabold text-blue-600"><?php echo $staffavailability['summary']['available']; ?></span>
                            <p class="text-xs text-gray-500 mt-0.5">Scheduled</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-xl">
                            <span class="text-xl font-extrabold text-gray-500"><?php echo $staffavailability['summary']['off_duty']; ?></span>
                            <p class="text-xs text-gray-500 mt-0.5">Off Duty</p>
                        </div>
                    </div>

                    <!-- Staff list -->
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Scheduled Today</h4>
                    <div class="space-y-2 max-h-[240px] overflow-y-auto">
                        <?php if (!empty($staffavailability['preview_staffs'])): ?>
                            <?php foreach ($staffavailability['preview_staffs'] as $staff): ?>
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <div class="flex items-center min-w-0">
                                    <img src="<?php echo $staff['avatar_url']; ?>" class="w-8 h-8 rounded-full flex-shrink-0">
                                    <div class="ml-2 min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($staff['staff_name']); ?></p>
                                        <p class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($staff['shift_type'] ?? 'Scheduled'); ?> shift</p>
                                    </div>
                                </div>
                                <span class="ml-2 flex-shrink-0 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">Ready</span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-gray-400 text-center py-6">No staff scheduled today</p>
                        <?php endif; ?>
                    </div>

                    <a href="staffs" class="mt-4 flex items-center justify-center w-full py-2.5 border border-[#99CC33] text-[#003366] text-xs font-semibold rounded-xl hover:bg-[#99CC33] hover:text-white transition-colors">
                        View All Staff (<?php echo $staffavailability['summary']['total_staffs']; ?>)
                    </a>
                </div>
            </div>

            <!-- Recent Activity (full width) -->
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Recent Activity</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Latest actions across the platform</p>
                    </div>
                    <a href="activities" class="text-xs text-[#99CC33] hover:text-[#88BB22] font-semibold flex items-center">
                        <i class="fas fa-external-link-alt mr-1"></i>View All
                    </a>
                </div>

                <?php if ($recentActivities['total'] > 0 && !empty($recentActivities['activities'])): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php foreach ($recentActivities['activities'] as $activity): ?>
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-9 h-9 <?php echo $activity['icon_bg']; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-<?php echo $activity['icon']; ?> <?php echo $activity['icon_color']; ?> text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-gray-800 leading-tight"><?php echo htmlspecialchars($activity['activity_title']); ?></p>
                            <?php if (!empty($activity['activity_description'])): ?>
                            <p class="text-xs text-gray-500 mt-0.5 truncate"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-gray-400"><?php echo $activity['time_ago']; ?></p>
                                <?php if (!empty($activity['user_name'])): ?>
                                <span class="text-xs text-[#99CC33] font-medium"><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-history text-4xl text-gray-200 mb-3 block"></i>
                    <p class="text-sm font-medium">No recent activity yet</p>
                    <p class="text-xs mt-1">Actions like adding staff or creating schedules will appear here</p>
                </div>
                <?php endif; ?>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <script>
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

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
        });
    </script>
</body>
</html>
