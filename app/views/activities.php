<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Activity Log</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { background: #99CC33; border-radius: 4px; }
        .sidebar { transform: translateX(-100%); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }
        .overlay { position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:30;opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s; }
        .overlay.active { opacity:1;visibility:visible; }
        .spinner { border:3px solid #e5e7eb;border-top:3px solid #003366;width:22px;height:22px;border-radius:50%;animation:spin .8s linear infinite;display:inline-block; }
        @keyframes spin { 100% { transform:rotate(360deg); } }
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
                        <i class="fas fa-history text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Activity Log</h2>
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
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-l-4 border-[#99CC33]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[#003366]">Activity Log</h1>
                        <p class="text-gray-400 text-sm mt-0.5">Full audit trail of all actions performed on the platform</p>
                    </div>
                    <div class="inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600">
                        <i class="fas fa-list-ul mr-2 text-[#99CC33]"></i>
                        <span id="totalCount">—</span> records
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="text" id="searchInput" placeholder="Search title, description, user..."
                            class="w-full pl-9 pr-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors">
                    </div>

                    <!-- Type filter -->
                    <select id="typeFilter" class="px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors text-gray-700">
                        <option value="">All Activity Types</option>
                        <optgroup label="Authentication">
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="password_changed">Password Changed</option>
                        </optgroup>
                        <optgroup label="Staff">
                            <option value="staff_created">Staff Added</option>
                            <option value="staff_updated">Staff Updated</option>
                            <option value="staff_deleted">Staff Removed</option>
                        </optgroup>
                        <optgroup label="Clients">
                            <option value="client_created">Client Added</option>
                            <option value="client_updated">Client Updated</option>
                            <option value="client_deleted">Client Removed</option>
                        </optgroup>
                        <optgroup label="Schedules">
                            <option value="schedule_created">Schedule Created</option>
                            <option value="schedule_updated">Schedule Updated</option>
                            <option value="schedule_cancelled">Schedule Cancelled</option>
                        </optgroup>
                        <optgroup label="Payroll">
                            <option value="payroll_generated">Payroll Generated</option>
                            <option value="payroll_processed">Payroll Processed</option>
                            <option value="payroll_paid">Payroll Paid</option>
                        </optgroup>
                        <optgroup label="Invoices">
                            <option value="invoice_generated">Invoice Generated</option>
                            <option value="invoice_sent">Invoice Sent</option>
                            <option value="invoice_paid">Invoice Paid</option>
                        </optgroup>
                        <optgroup label="Holidays">
                            <option value="holiday_created">Holiday Added</option>
                            <option value="holiday_updated">Holiday Updated</option>
                            <option value="holiday_deleted">Holiday Removed</option>
                        </optgroup>
                        <optgroup label="Documents">
                            <option value="create">Document Created</option>
                            <option value="update">Document/File Updated</option>
                            <option value="delete">Item Deleted</option>
                        </optgroup>
                    </select>

                    <!-- Date from -->
                    <div class="relative">
                        <i class="fas fa-calendar absolute left-3 top-3 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="date" id="dateFrom" class="w-full pl-9 pr-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors">
                    </div>

                    <!-- Date to + clear -->
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i class="fas fa-calendar absolute left-3 top-3 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="date" id="dateTo" class="w-full pl-9 pr-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors">
                        </div>
                        <button onclick="clearFilters()" title="Clear all filters"
                            class="flex-shrink-0 px-3 py-2.5 border-2 border-gray-200 rounded-xl text-gray-400 hover:border-red-300 hover:text-red-400 transition-colors text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-5">

                <!-- Loading -->
                <div id="loadingState" class="py-16 text-center">
                    <span class="spinner"></span>
                    <p class="mt-3 text-sm text-gray-400">Loading activities...</p>
                </div>

                <!-- Empty -->
                <div id="emptyState" class="hidden py-16 text-center">
                    <i class="fas fa-history text-5xl text-gray-200 mb-4 block"></i>
                    <p class="text-gray-500 font-semibold">No activities found</p>
                    <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or perform some actions on the system</p>
                </div>

                <!-- Table -->
                <div id="tableWrapper" class="hidden overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wide">Action</th>
                                <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Performed By</th>
                                <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wide hidden md:table-cell">Description</th>
                                <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wide hidden lg:table-cell">IP / Device</th>
                                <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wide">When</th>
                            </tr>
                        </thead>
                        <tbody id="activityBody"></tbody>
                    </table>
                </div>

                <!-- Pagination bar -->
                <div id="paginationBar" class="hidden px-5 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p id="paginationInfo" class="text-sm text-gray-500 order-2 sm:order-1"></p>
                    <div id="paginationBtns" class="flex items-center space-x-1 order-1 sm:order-2"></div>
                </div>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

<script>
let currentPage = 1;
let totalPages  = 1;
let searchTimer = null;

const badgeMap = {
    login:              'bg-green-100 text-green-700',
    logout:             'bg-gray-100 text-gray-600',
    failed_login:       'bg-red-100 text-red-700',
    staff_created:      'bg-emerald-100 text-emerald-700',
    staff_updated:      'bg-blue-100 text-blue-700',
    staff_deleted:      'bg-red-100 text-red-700',
    client_created:     'bg-emerald-100 text-emerald-700',
    client_updated:     'bg-blue-100 text-blue-700',
    client_deleted:     'bg-red-100 text-red-700',
    schedule_created:   'bg-green-100 text-green-700',
    schedule_updated:   'bg-blue-100 text-blue-700',
    schedule_cancelled: 'bg-red-100 text-red-700',
    payroll_generated:  'bg-purple-100 text-purple-700',
    payroll_processed:  'bg-blue-100 text-blue-700',
    payroll_paid:       'bg-green-100 text-green-700',
    invoice_generated:  'bg-indigo-100 text-indigo-700',
    invoice_sent:       'bg-blue-100 text-blue-700',
    invoice_paid:       'bg-green-100 text-green-700',
    holiday_created:    'bg-amber-100 text-amber-700',
    holiday_updated:    'bg-amber-100 text-amber-700',
    holiday_deleted:    'bg-red-100 text-red-700',
    password_changed:   'bg-yellow-100 text-yellow-700',
    create:             'bg-emerald-100 text-emerald-700',
    update:             'bg-blue-100 text-blue-700',
    delete:             'bg-red-100 text-red-700',
};

const deviceIcon = { desktop: 'desktop', tablet: 'tablet-alt', mobile: 'mobile-alt' };

function badge(type) {
    const cls   = badgeMap[type] || 'bg-gray-100 text-gray-600';
    const label = type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${cls} whitespace-nowrap">${label}</span>`;
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadActivities(page) {
    currentPage = page || 1;
    $('#loadingState').removeClass('hidden');
    $('#tableWrapper, #emptyState, #paginationBar').addClass('hidden');

    $.post('fetch_all_activities', {
        page:        currentPage,
        per_page:    25,
        type_filter: $('#typeFilter').val(),
        date_from:   $('#dateFrom').val(),
        date_to:     $('#dateTo').val(),
        search:      $('#searchInput').val().trim()
    }, function(res) {
        $('#loadingState').addClass('hidden');

        if (!res.success || !res.activities || res.activities.length === 0) {
            $('#emptyState').removeClass('hidden');
            $('#totalCount').text('0');
            return;
        }

        $('#totalCount').text(res.total.toLocaleString());
        totalPages = res.total_pages;

        let rows = '';
        res.activities.forEach(function(a) {
            const avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(a.user_name || 'User')}&background=003366&color=fff&size=28`;
            const desc   = a.activity_description
                ? `<span class="text-xs text-gray-500 leading-relaxed line-clamp-2">${esc(a.activity_description)}</span>`
                : `<span class="text-xs text-gray-300">—</span>`;
            const dIcon  = deviceIcon[a.device_type] || 'question-circle';

            rows += `
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="px-5 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 ${esc(a.icon_bg)} rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-${esc(a.icon)} ${esc(a.icon_color)} text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">${esc(a.activity_title)}</p>
                            <div class="mt-1">${badge(a.activity_type)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 hidden sm:table-cell">
                    <div class="flex items-center space-x-2">
                        <img src="${avatar}" class="w-7 h-7 rounded-full flex-shrink-0">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 whitespace-nowrap">${esc(a.user_name)}</p>
                            <p class="text-xs text-gray-400 capitalize">${esc(a.user_role || 'admin')}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 hidden md:table-cell max-w-xs">${desc}</td>
                <td class="px-5 py-4 hidden lg:table-cell">
                    <p class="text-xs text-gray-500 font-mono">${esc(a.ip_address || '—')}</p>
                    <p class="text-xs text-gray-400 mt-0.5 capitalize">
                        <i class="fas fa-${dIcon} mr-1"></i>${esc(a.device_type || 'desktop')}
                    </p>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <p class="text-xs font-semibold text-gray-700">${esc(a.time_ago)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${esc(a.date_formatted)}</p>
                </td>
            </tr>`;
        });

        $('#activityBody').html(rows);
        $('#tableWrapper').removeClass('hidden');
        renderPagination(res.page, res.total_pages, res.total, res.per_page);

    }, 'json').fail(function() {
        $('#loadingState').addClass('hidden');
        $('#emptyState').removeClass('hidden');
    });
}

function renderPagination(page, totalPgs, total, perPage) {
    if (totalPgs <= 1) {
        $('#paginationBar').addClass('hidden');
        return;
    }

    const start = (page - 1) * perPage + 1;
    const end   = Math.min(page * perPage, total);
    $('#paginationInfo').text('Showing ' + start.toLocaleString() + '–' + end.toLocaleString() + ' of ' + total.toLocaleString() + ' records');

    const baseBtn   = 'px-3 py-1.5 text-sm border rounded-lg transition-colors';
    const activeBtn = baseBtn + ' bg-[#003366] text-white border-[#003366]';
    const normBtn   = baseBtn + ' border-gray-200 hover:border-[#99CC33] hover:text-[#003366]';
    const disBtn    = baseBtn + ' border-gray-100 text-gray-300 cursor-not-allowed';

    let btns = `<button onclick="loadActivities(${page - 1})" ${page <= 1 ? 'disabled' : ''} class="${page <= 1 ? disBtn : normBtn}">
        <i class="fas fa-chevron-left text-xs"></i>
    </button>`;

    const s = Math.max(1, page - 2);
    const e = Math.min(totalPgs, page + 2);

    if (s > 1) btns += `<button onclick="loadActivities(1)" class="${normBtn}">1</button>`;
    if (s > 2) btns += `<span class="px-1 text-gray-400 text-sm">…</span>`;

    for (let p = s; p <= e; p++) {
        btns += `<button onclick="loadActivities(${p})" class="${p === page ? activeBtn : normBtn}">${p}</button>`;
    }

    if (e < totalPgs - 1) btns += `<span class="px-1 text-gray-400 text-sm">…</span>`;
    if (e < totalPgs)     btns += `<button onclick="loadActivities(${totalPgs})" class="${normBtn}">${totalPgs}</button>`;

    btns += `<button onclick="loadActivities(${page + 1})" ${page >= totalPgs ? 'disabled' : ''} class="${page >= totalPgs ? disBtn : normBtn}">
        <i class="fas fa-chevron-right text-xs"></i>
    </button>`;

    $('#paginationBtns').html(btns);
    $('#paginationBar').removeClass('hidden');
}

function clearFilters() {
    $('#searchInput').val('');
    $('#typeFilter').val('');
    $('#dateFrom').val('');
    $('#dateTo').val('');
    loadActivities(1);
}

// Live search debounce
$('#searchInput').on('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { loadActivities(1); }, 400);
});

// Immediate on dropdown/date change
$('#typeFilter, #dateFrom, #dateTo').on('change', function() { loadActivities(1); });

// Sidebar
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
    if (window.innerWidth >= 1024) { sidebar.classList.remove('open'); overlay.classList.remove('active'); document.body.style.overflow = ''; }
});
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });

$(document).ready(function() { loadActivities(1); });
</script>
</body>
</html>
