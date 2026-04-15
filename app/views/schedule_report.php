<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Schedule Report</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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

        .rpt-table { width:100%; border-collapse: collapse; font-size: 0.8125rem; }
        .rpt-table th { background:#f3f4f6; padding:.6rem .875rem; text-align:left; font-size:.68rem; font-weight:700; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb; }
        .rpt-table td { padding:.65rem .875rem; border-bottom:1px solid #f3f4f6; color:#374151; }
        .rpt-table tbody tr:hover { background:#f9fafb; }
        .rpt-table th.r, .rpt-table td.r { text-align:right; }

        .status-badge { padding:.18rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:600; display:inline-block; text-transform:capitalize; }
        .status-scheduled   { background:#dbeafe; color:#1e40af; }
        .status-in-progress { background:#fef3c7; color:#92400e; }
        .status-completed   { background:#d1fae5; color:#065f46; }
        .status-cancelled   { background:#fee2e2; color:#991b1b; }
        .status-no-show     { background:#f3f4f6; color:#374151; }

        .toast-container { position:fixed; top:1rem; right:1rem; z-index:60; display:flex; flex-direction:column; gap:.5rem; }
        .toast { background:#fff; border-left:4px solid; padding:.75rem 1rem; border-radius:.5rem; box-shadow:0 4px 12px rgba(0,0,0,.1); min-width:280px; display:flex; align-items:center; gap:.5rem; animation:toastIn .3s ease; }
        .toast.success { border-color:#10b981; color:#065f46; }
        .toast.error   { border-color:#ef4444; color:#991b1b; }
        .toast.info    { border-color:#3b82f6; color:#1e40af; }
        .toast.warning { border-color:#f59e0b; color:#92400e; }
        @keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    </style>
</head>
<body class="bg-gray-50">
    <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>
    <?php include 'includes/sidebar.php'; ?>

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
                        <i class="fas fa-chart-line text-[#99CC33] text-sm"></i>
                        <h2 class="text-base font-semibold text-gray-700">Schedule Report</h2>
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
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[#003366]">Schedule Report</h1>
                        <p class="text-gray-400 text-sm mt-0.5">Filter and export schedules by staff, client, or date range</p>
                    </div>
                    <button id="downloadBtn" onclick="downloadPdf()" disabled
                            class="inline-flex items-center px-4 py-2.5 bg-[#99CC33] hover:bg-[#88bb22] text-white rounded-xl text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm p-5 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Staff</label>
                        <select id="staffFilter" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                            <option value="">All Staff</option>
                            <?php if (!empty($counters['all_staffs']['staffs'])): foreach ($counters['all_staffs']['staffs'] as $s): ?>
                                <option value="<?= (int)$s['staff_id'] ?>"><?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Client</label>
                        <select id="clientFilter" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                            <option value="">All Clients</option>
                            <?php if (!empty($counters['all_clients']['clients'])): foreach ($counters['all_clients']['clients'] as $c): ?>
                                <option value="<?= (int)$c['client_id'] ?>"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">From Date</label>
                        <input type="date" id="startDate" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">To Date</label>
                        <input type="date" id="endDate" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select id="statusFilter" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                            <option value="">All Statuses</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no-show">No Show</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 justify-end">
                    <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        <i class="fas fa-redo-alt mr-1.5"></i> Reset
                    </button>
                    <button onclick="generateReport()" id="generateBtn" class="px-5 py-2 bg-[#003366] hover:bg-[#002244] text-white rounded-xl text-sm font-semibold transition flex items-center gap-2">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div id="summaryCards" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#003366]">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Schedules</p>
                    <p class="text-lg font-bold text-[#003366] mt-1" id="sumCount">0</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-[#99CC33]">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Hours</p>
                    <p class="text-lg font-bold text-[#003366] mt-1" id="sumHours">0.00</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-amber-500">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Unique Staff</p>
                    <p class="text-lg font-bold text-[#003366] mt-1" id="sumStaff">0</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-rose-500">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Amount</p>
                    <p class="text-lg font-bold text-[#003366] mt-1" id="sumAmount">$0.00</p>
                </div>
            </div>

            <!-- Results -->
            <div id="resultsCard" class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-[#003366]">Results <span class="text-gray-400 font-normal text-sm" id="resultsCount">(0)</span></h3>
                </div>
                <div id="emptyState" class="p-12 text-center text-gray-400">
                    <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm">Apply filters and click <strong>Generate Report</strong> to view schedules.</p>
                </div>
                <div id="tableWrap" class="hidden overflow-x-auto">
                    <table class="rpt-table" id="reportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Staff</th>
                                <th>Client</th>
                                <th>Time</th>
                                <th>Shift</th>
                                <th class="r">Hours</th>
                                <th class="r">Rate</th>
                                <th class="r">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="reportBody"></tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        let currentSchedules = [];
        let currentFilterMeta = {};

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('active');
        }

        function showToast(title, message, type) {
            type = type || 'info';
            const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle', warning:'exclamation-triangle' };
            const el = document.createElement('div');
            el.className = 'toast ' + type;
            el.innerHTML = '<i class="fas fa-' + icons[type] + '"></i><div><p class="font-semibold text-sm">' + title + '</p><p class="text-xs opacity-80">' + message + '</p></div>';
            document.getElementById('toastContainer').appendChild(el);
            setTimeout(function() { el.remove(); }, 3800);
        }

        function escHtml(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
            });
        }

        function fmtDate(s) {
            if (!s) return '—';
            return new Date(s + 'T00:00:00').toLocaleDateString('en-CA', { year:'numeric', month:'short', day:'numeric' });
        }

        function fmtAmt(n) {
            return '$' + parseFloat(n || 0).toLocaleString('en-CA', { minimumFractionDigits:2, maximumFractionDigits:2 });
        }

        function resetFilters() {
            document.getElementById('staffFilter').value = '';
            document.getElementById('clientFilter').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('statusFilter').value = '';
        }

        function generateReport() {
            const staffSel  = document.getElementById('staffFilter');
            const clientSel = document.getElementById('clientFilter');
            const statusSel = document.getElementById('statusFilter');

            currentFilterMeta = {
                staff_id:   staffSel.value,
                staff_name: staffSel.options[staffSel.selectedIndex].text,
                client_id:  clientSel.value,
                client_name:clientSel.options[clientSel.selectedIndex].text,
                start_date: document.getElementById('startDate').value,
                end_date:   document.getElementById('endDate').value,
                status:     statusSel.value,
                status_label: statusSel.options[statusSel.selectedIndex].text
            };

            const btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading…';

            $.ajax({
                url: 'fetch_schedules_report',
                method: 'POST',
                data: {
                    staff_id:   currentFilterMeta.staff_id,
                    client_id:  currentFilterMeta.client_id,
                    start_date: currentFilterMeta.start_date,
                    end_date:   currentFilterMeta.end_date,
                    status:     currentFilterMeta.status
                },
                dataType: 'json',
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search"></i> Generate Report';
                    if (res.status) {
                        currentSchedules = res.schedules || [];
                        renderResults();
                    } else {
                        showToast('Error', res.message || 'Failed to fetch report.', 'error');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-search"></i> Generate Report';
                    showToast('Error', 'Connection error. Please try again.', 'error');
                }
            });
        }

        function renderResults() {
            const emptyState = document.getElementById('emptyState');
            const tableWrap  = document.getElementById('tableWrap');
            const summary    = document.getElementById('summaryCards');
            const downloadBtn= document.getElementById('downloadBtn');
            const resultsCnt = document.getElementById('resultsCount');

            resultsCnt.textContent = '(' + currentSchedules.length + ')';

            if (currentSchedules.length === 0) {
                emptyState.classList.remove('hidden');
                emptyState.innerHTML = '<i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i><p class="text-sm">No schedules match your filters.</p>';
                tableWrap.classList.add('hidden');
                summary.classList.add('hidden');
                downloadBtn.disabled = true;
                return;
            }

            emptyState.classList.add('hidden');
            tableWrap.classList.remove('hidden');
            summary.classList.remove('hidden');
            downloadBtn.disabled = false;

            // Totals
            let totalHours = 0, totalAmt = 0;
            const uniqueStaff = new Set();
            currentSchedules.forEach(function(s) {
                totalHours += parseFloat(s.hours_worked || 0);
                totalAmt   += parseFloat(s.amount || 0);
                uniqueStaff.add(s.staff_name);
            });
            document.getElementById('sumCount').textContent  = currentSchedules.length;
            document.getElementById('sumHours').textContent  = totalHours.toFixed(2);
            document.getElementById('sumStaff').textContent  = uniqueStaff.size;
            document.getElementById('sumAmount').textContent = fmtAmt(totalAmt);

            // Table rows
            const tbody = document.getElementById('reportBody');
            tbody.innerHTML = currentSchedules.map(function(s) {
                const rate = parseFloat(s.holiday_pay) > 0
                    ? (parseFloat(s.pay_per_hour) * parseFloat(s.holiday_pay) / 100)
                    : parseFloat(s.pay_per_hour);
                const holTag = parseFloat(s.holiday_pay) > 0
                    ? ' <span style="color:#c2410c;font-size:10px;">(Holiday)</span>' : '';
                return '<tr>'
                    + '<td>' + fmtDate(s.schedule_date) + '</td>'
                    + '<td>' + escHtml(s.staff_name) + '</td>'
                    + '<td>' + escHtml(s.client_name) + '</td>'
                    + '<td>' + escHtml(s.start_time_fmt) + ' – ' + escHtml(s.end_time_fmt) + '</td>'
                    + '<td class="capitalize">' + escHtml(s.shift_type) + holTag + '</td>'
                    + '<td class="r">' + parseFloat(s.hours_worked || 0).toFixed(2) + '</td>'
                    + '<td class="r text-gray-600">' + fmtAmt(rate) + '</td>'
                    + '<td class="r font-semibold text-[#003366]">' + fmtAmt(s.amount) + '</td>'
                    + '<td><span class="status-badge status-' + s.status + '">' + s.status.replace('-', ' ') + '</span></td>'
                    + '</tr>';
            }).join('');
        }

        // ─── PDF Download ────────────────────────────────────────────────────────
        function buildPdfHtml() {
            const m = currentFilterMeta;
            const fmtRangeDate = function(s) { return s ? fmtDate(s) : 'Any'; };

            let totalHours = 0, totalAmt = 0;
            const uniqueStaff = new Set();
            currentSchedules.forEach(function(s) {
                totalHours += parseFloat(s.hours_worked || 0);
                totalAmt   += parseFloat(s.amount || 0);
                uniqueStaff.add(s.staff_name);
            });

            const logoUrl = window.location.origin
                + window.location.pathname.replace(/\/[^\/]*$/, '/')
                + 'public/images/tameclogo.png';

            const rows = currentSchedules.map(function(s) {
                const rate = parseFloat(s.holiday_pay) > 0
                    ? (parseFloat(s.pay_per_hour) * parseFloat(s.holiday_pay) / 100)
                    : parseFloat(s.pay_per_hour);
                const holTag = parseFloat(s.holiday_pay) > 0
                    ? ' <em style="color:#c2410c;font-size:10px;">(Holiday)</em>' : '';
                return '<tr>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;">' + fmtDate(s.schedule_date) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;">' + escHtml(s.staff_name) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;">' + escHtml(s.client_name) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;">' + escHtml(s.start_time_fmt) + ' – ' + escHtml(s.end_time_fmt) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;text-transform:capitalize;">' + escHtml(s.shift_type) + holTag + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:11px;">' + parseFloat(s.hours_worked || 0).toFixed(2) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:11px;">' + fmtAmt(rate) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:right;font-size:11px;font-weight:700;color:#111827;">' + fmtAmt(s.amount) + '</td>'
                    + '<td style="padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:11px;text-transform:capitalize;">' + escHtml(s.status.replace('-', ' ')) + '</td>'
                    + '</tr>';
            }).join('');

            const styles = ''
                + '*{box-sizing:border-box;margin:0;padding:0;}'
                + 'body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;color:#1f2937;background:#fff;-webkit-font-smoothing:antialiased;}'
                + '.page{padding:28px 32px;max-width:1100px;margin:0 auto;}'
                + '.hd{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;}'
                + '.co-name{font-size:14px;font-weight:800;color:#111827;}'
                + '.co-addr{font-size:11px;color:#4b5563;line-height:1.75;margin-top:4px;}'
                + '.logo img{max-height:64px;}'
                + '.title-bar{border-top:3px solid #003366;padding-top:12px;margin-bottom:16px;}'
                + '.title{font-size:26px;font-weight:900;color:#003366;letter-spacing:1px;}'
                + '.sub{font-size:11px;color:#6b7280;margin-top:4px;}'
                + '.meta{display:flex;gap:24px;flex-wrap:wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:12px 14px;margin-bottom:16px;font-size:11px;}'
                + '.meta b{color:#003366;font-weight:700;margin-right:6px;}'
                + '.summary{display:flex;gap:12px;margin-bottom:16px;}'
                + '.sum-box{flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:10px 12px;background:#fafafa;}'
                + '.sum-label{font-size:10px;text-transform:uppercase;color:#9ca3af;font-weight:700;letter-spacing:.5px;}'
                + '.sum-val{font-size:16px;font-weight:800;color:#003366;margin-top:2px;}'
                + 'table.rpt{width:100%;border-collapse:collapse;}'
                + 'table.rpt thead tr{background:#003366;}'
                + 'table.rpt th{padding:8px;color:#fff;font-size:10px;font-weight:700;text-align:left;text-transform:uppercase;letter-spacing:.5px;}'
                + 'table.rpt th.r{text-align:right;}'
                + 'table.rpt tbody tr:nth-child(even){background:#f9fafb;}'
                + '.footer{margin-top:20px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:10px;color:#9ca3af;text-align:center;}';

            return '<style>' + styles + '</style>'
                + '<div class="page">'
                + '<div class="hd">'
                    + '<div>'
                        + '<div class="co-name">TAMEC CARE STAFFING SERVICES LTD</div>'
                        + '<div class="co-addr">3100 STEELES AVENUE WEST<br>403<br>CONCORD, ONTARIO L4K 3R1<br>info@tameccarestaffing.com</div>'
                    + '</div>'
                    + '<div class="logo"><img src="' + logoUrl + '" alt="TAMEC" onerror="this.style.display=\'none\'"></div>'
                + '</div>'
                + '<div class="title-bar">'
                    + '<div class="title">SCHEDULE REPORT</div>'
                    + '<div class="sub">Generated ' + new Date().toLocaleString('en-CA') + '</div>'
                + '</div>'
                + '<div class="meta">'
                    + '<span><b>Staff:</b>' + escHtml(m.staff_name) + '</span>'
                    + '<span><b>Client:</b>' + escHtml(m.client_name) + '</span>'
                    + '<span><b>Period:</b>' + fmtRangeDate(m.start_date) + ' – ' + fmtRangeDate(m.end_date) + '</span>'
                    + '<span><b>Status:</b>' + escHtml(m.status_label) + '</span>'
                + '</div>'
                + '<div class="summary">'
                    + '<div class="sum-box"><div class="sum-label">Schedules</div><div class="sum-val">' + currentSchedules.length + '</div></div>'
                    + '<div class="sum-box"><div class="sum-label">Total Hours</div><div class="sum-val">' + totalHours.toFixed(2) + '</div></div>'
                    + '<div class="sum-box"><div class="sum-label">Unique Staff</div><div class="sum-val">' + uniqueStaff.size + '</div></div>'
                    + '<div class="sum-box"><div class="sum-label">Total Amount</div><div class="sum-val">' + fmtAmt(totalAmt) + '</div></div>'
                + '</div>'
                + '<table class="rpt">'
                    + '<thead><tr>'
                        + '<th>Date</th><th>Staff</th><th>Client</th><th>Time</th><th>Shift</th>'
                        + '<th class="r">Hours</th><th class="r">Rate</th><th class="r">Amount</th><th>Status</th>'
                    + '</tr></thead>'
                    + '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '<div class="footer">TAMEC Care Staffing Services Ltd &bull; Schedule Report</div>'
                + '</div>';
        }

        function downloadPdf() {
            if (!currentSchedules.length) return;

            showToast('Info', 'Generating PDF…', 'info');

            // Build a full self-contained HTML page string and pass it to html2pdf
            // Using .from(html, 'string') renders inside an internal iframe — no
            // DOM append needed, so blank-page issues are avoided entirely.
            const fullHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
                + buildPdfHtml()
                + '</body></html>';

            const filename = 'schedule-report-' + new Date().toISOString().slice(0, 10) + '.pdf';

            html2pdf()
                .set({
                    margin: [8, 8, 8, 8],
                    filename: filename,
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                })
                .from(fullHtml, 'string')
                .save();
        }
    </script>
</body>
</html>
