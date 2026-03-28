<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #99CC33; border-radius: 5px; }

        .sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }

        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 30; display: none; }
        .overlay.active { display: block; }

        .strength-bar { height: 4px; border-radius: 999px; transition: background-color 0.3s; }

        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 18px; height: 18px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            background: white; border-radius: .75rem; padding: 1rem 1.25rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.15); z-index: 9999;
            transform: translateY(6rem); opacity: 0;
            transition: transform .3s, opacity .3s; min-width: 280px;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
    </style>
</head>
<body class="bg-gray-50">

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">

    <!-- Top Nav -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center">
        <button onclick="openSidebar()" class="lg:hidden text-gray-500 mr-4">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div>
            <h1 class="text-xl font-bold text-[#003366]">Change Password</h1>
            <p class="text-xs text-gray-500">Update your account password</p>
        </div>
    </header>

    <main class="p-6 flex items-start justify-center">
        <div class="w-full max-w-lg">

            <!-- Security Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start space-x-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-blue-700 text-sm leading-relaxed">
                    Choose a strong password with at least 6 characters. Avoid using easily guessable information.
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Card Header -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-[#003366] bg-opacity-10 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-key text-[#003366]"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-[#003366]">Update Password</h2>
                        <p class="text-xs text-gray-400">Logged in as <?php echo htmlspecialchars($_SESSION['tamec_name'] ?? 'Admin'); ?></p>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6 space-y-5">

                    <!-- Alert -->
                    <div id="alertBox" class="hidden rounded-xl p-4 text-sm flex items-start space-x-3">
                        <i id="alertIcon" class="mt-0.5 flex-shrink-0 text-base"></i>
                        <span id="alertText"></span>
                    </div>

                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="currentPassword"
                                placeholder="Enter your current password"
                                class="w-full pl-4 pr-10 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                            >
                            <button type="button" onclick="toggleVisibility('currentPassword', 'eyeCurrent')" class="absolute right-3 top-3.5 text-gray-400 hover:text-[#003366]">
                                <i id="eyeCurrent" class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-100"></div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="newPassword"
                                placeholder="Enter a new password"
                                class="w-full pl-4 pr-10 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                                oninput="checkStrength(this.value)"
                            >
                            <button type="button" onclick="toggleVisibility('newPassword', 'eyeNew')" class="absolute right-3 top-3.5 text-gray-400 hover:text-[#003366]">
                                <i id="eyeNew" class="far fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength bars -->
                        <div class="mt-2 space-y-1">
                            <div class="flex space-x-1">
                                <div id="bar1" class="strength-bar flex-1 bg-gray-200"></div>
                                <div id="bar2" class="strength-bar flex-1 bg-gray-200"></div>
                                <div id="bar3" class="strength-bar flex-1 bg-gray-200"></div>
                                <div id="bar4" class="strength-bar flex-1 bg-gray-200"></div>
                            </div>
                            <p id="strengthLabel" class="text-xs text-gray-400">Enter a password to check strength</p>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="confirmPassword"
                                placeholder="Re-enter new password"
                                class="w-full pl-4 pr-10 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                                oninput="checkMatch()"
                            >
                            <button type="button" onclick="toggleVisibility('confirmPassword', 'eyeConfirm')" class="absolute right-3 top-3.5 text-gray-400 hover:text-[#003366]">
                                <i id="eyeConfirm" class="far fa-eye"></i>
                            </button>
                        </div>
                        <p id="matchLabel" class="text-xs mt-1 hidden"></p>
                    </div>

                    <!-- Requirements -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Password requirements</p>
                        <ul class="space-y-1">
                            <li id="req_len" class="flex items-center text-xs text-gray-400">
                                <i class="fas fa-circle w-3 mr-2 text-gray-300 text-xs"></i>At least 6 characters
                            </li>
                            <li id="req_upper" class="flex items-center text-xs text-gray-400">
                                <i class="fas fa-circle w-3 mr-2 text-gray-300 text-xs"></i>At least one uppercase letter
                            </li>
                            <li id="req_num" class="flex items-center text-xs text-gray-400">
                                <i class="fas fa-circle w-3 mr-2 text-gray-300 text-xs"></i>At least one number
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Card Footer -->
                <div class="px-6 pb-6 flex items-center justify-between">
                    <a href="dashboard" class="text-sm text-gray-400 hover:text-[#003366] transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Cancel
                    </a>
                    <button
                        onclick="submitChange()"
                        id="saveBtn"
                        class="bg-[#99CC33] hover:bg-[#88BB22] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-6 rounded-xl transition-all flex items-center space-x-2 shadow-lg shadow-green-100"
                    >
                        <span id="saveBtnText"><i class="fas fa-save mr-1.5"></i>Update Password</span>
                        <span id="saveBtnSpinner" class="hidden items-center space-x-1.5">
                            <span class="spinner"></span><span>Saving...</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- Toast -->
<div id="toast" class="toast">
    <div class="flex items-center space-x-3">
        <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] text-xl flex-shrink-0"></i>
        <div>
            <p id="toastTitle" class="font-semibold text-gray-800 text-sm"></p>
            <p id="toastMsg" class="text-xs text-gray-500"></p>
        </div>
    </div>
</div>

<script>
    function openSidebar()  { document.getElementById('sidebar').classList.add('open'); document.getElementById('overlay').classList.add('active'); }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('active'); }

    function toggleVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'far fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye';
        }
    }

    function checkStrength(val) {
        const bars   = ['bar1','bar2','bar3','bar4'];
        const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-[#99CC33]'];
        const labels = ['Too short','Weak','Fair','Good','Strong'];

        let score = 0;
        if (val.length >= 6)  score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/\d/.test(val))   score++;
        if (/[^a-zA-Z0-9]/.test(val)) score = Math.min(score + 1, 4);

        bars.forEach((id, i) => {
            const el = document.getElementById(id);
            el.className = 'strength-bar flex-1 ' + (i < score ? colors[Math.min(score - 1, 3)] : 'bg-gray-200');
        });
        document.getElementById('strengthLabel').textContent = val.length ? labels[score] : 'Enter a password to check strength';

        // Requirements
        setReq('req_len',   val.length >= 6);
        setReq('req_upper', /[A-Z]/.test(val));
        setReq('req_num',   /\d/.test(val));
    }

    function setReq(id, met) {
        const el   = document.getElementById(id);
        const icon = el.querySelector('i');
        if (met) {
            el.classList.replace('text-gray-400', 'text-green-600');
            icon.className = 'fas fa-check-circle w-3 mr-2 text-green-500 text-xs';
        } else {
            el.classList.replace('text-green-600', 'text-gray-400');
            icon.className = 'fas fa-circle w-3 mr-2 text-gray-300 text-xs';
        }
    }

    function checkMatch() {
        const np = document.getElementById('newPassword').value;
        const cp = document.getElementById('confirmPassword').value;
        const lbl = document.getElementById('matchLabel');
        if (!cp) { lbl.classList.add('hidden'); return; }
        if (np === cp) {
            lbl.textContent = '✓ Passwords match';
            lbl.className   = 'text-xs mt-1 text-green-600';
        } else {
            lbl.textContent = '✗ Passwords do not match';
            lbl.className   = 'text-xs mt-1 text-red-500';
        }
        lbl.classList.remove('hidden');
    }

    function showAlert(message, type) {
        const isError = type === 'error';
        const box  = document.getElementById('alertBox');
        const icon = document.getElementById('alertIcon');
        box.className = 'rounded-xl p-4 text-sm flex items-start space-x-3 ' +
            (isError ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700');
        icon.className = 'mt-0.5 flex-shrink-0 text-base fas ' +
            (isError ? 'fa-exclamation-circle text-red-500' : 'fa-check-circle text-green-500');
        document.getElementById('alertText').textContent = message;
        box.classList.remove('hidden');
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function showToast(title, msg, type = 'success') {
        const toast = document.getElementById('toast');
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastMsg').textContent   = msg;
        document.getElementById('toastIcon').className    = type === 'success'
            ? 'fas fa-check-circle text-[#99CC33] text-xl flex-shrink-0'
            : 'fas fa-exclamation-circle text-red-500 text-xl flex-shrink-0';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    function submitChange() {
        const current  = document.getElementById('currentPassword').value.trim();
        const newPass  = document.getElementById('newPassword').value.trim();
        const confirm  = document.getElementById('confirmPassword').value.trim();

        document.getElementById('alertBox').classList.add('hidden');

        if (!current) { showAlert('Please enter your current password.', 'error'); return; }
        if (!newPass)  { showAlert('Please enter a new password.', 'error'); return; }
        if (newPass.length < 6) { showAlert('New password must be at least 6 characters.', 'error'); return; }
        if (newPass !== confirm) { showAlert('New passwords do not match.', 'error'); return; }
        if (current === newPass) { showAlert('New password must be different from your current password.', 'error'); return; }

        // Loading state
        document.getElementById('saveBtnText').classList.add('hidden');
        document.getElementById('saveBtnSpinner').classList.remove('hidden');
        document.getElementById('saveBtnSpinner').classList.add('flex');
        document.getElementById('saveBtn').disabled = true;

        $.ajax({
            url: 'change_password',
            method: 'POST',
            data: { current_password: current, new_password: newPass },
            dataType: 'json',
            success(res) {
                if (res.status) {
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value     = '';
                    document.getElementById('confirmPassword').value = '';
                    checkStrength('');
                    document.getElementById('matchLabel').classList.add('hidden');
                    showToast('Password Updated', 'Your password has been changed successfully.');
                } else {
                    showAlert(res.message || 'Failed to update password.', 'error');
                }
            },
            error() {
                showAlert('Connection error. Please try again.', 'error');
            },
            complete() {
                document.getElementById('saveBtnText').classList.remove('hidden');
                document.getElementById('saveBtnSpinner').classList.add('hidden');
                document.getElementById('saveBtnSpinner').classList.remove('flex');
                document.getElementById('saveBtn').disabled = false;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
