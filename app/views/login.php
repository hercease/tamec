<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Admin Login</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 18px; height: 18px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .bg-pattern {
            background-color: #003366;
            background-image: radial-gradient(circle at 20% 50%, rgba(153,204,51,0.15) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(0,77,153,0.6) 0%, transparent 50%),
                              radial-gradient(circle at 60% 80%, rgba(153,204,51,0.08) 0%, transparent 40%);
        }

        .fade-in { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake { animation: shake 0.5s ease-in-out; }

        .error { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block; }
        .error-field { border-color: #dc2626 !important; }
        .success-field { border-color: #99CC33 !important; }
    </style>
</head>
<body class="min-h-screen flex bg-gray-50">

    <!-- Left Panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-pattern flex-col items-center justify-center p-12 relative overflow-hidden">
        <!-- Decorative circles -->
        <div class="absolute top-[-60px] right-[-60px] w-64 h-64 rounded-full border border-[#99CC33] border-opacity-20"></div>
        <div class="absolute bottom-[-40px] left-[-40px] w-48 h-48 rounded-full border border-[#99CC33] border-opacity-15"></div>
        <div class="absolute top-1/2 right-8 w-24 h-24 rounded-full bg-[#99CC33] bg-opacity-10"></div>

        <div class="relative text-center max-w-md">
            <img src="public/images/tameclogo.png" alt="TAMEC" class="w-28 h-auto mx-auto mb-8 drop-shadow-lg">
            <h2 class="text-4xl font-extrabold text-white leading-tight mb-4">
                Care Staffing<br>
                <span class="text-[#99CC33]">Made Simple</span>
            </h2>
            <p class="text-blue-200 text-base leading-relaxed opacity-90">
                Your complete platform for managing healthcare staff, scheduling, payroll, and client services — all in one place.
            </p>

            <div class="mt-10 grid grid-cols-3 gap-4">
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-user-nurse text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Staff</p>
                    <p class="text-blue-200 text-xs opacity-75">Management</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-calendar-alt text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Smart</p>
                    <p class="text-blue-200 text-xs opacity-75">Scheduling</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-money-bill-wave text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Payroll</p>
                    <p class="text-blue-200 text-xs opacity-75">& Invoicing</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md fade-in">

            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-8">
                <img src="public/images/tameclogo.png" alt="TAMEC" class="w-20 h-auto mx-auto">
            </div>

            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#003366] bg-opacity-10 mb-4">
                    <i class="fas fa-shield-alt text-[#003366] text-xl"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-[#003366] mb-2">Welcome Back</h1>
                <p class="text-gray-500 text-sm leading-relaxed">Sign in to your TAMEC admin dashboard.</p>
            </div>

            <!-- Alert -->
            <div id="alertBox" class="hidden mb-5 p-4 rounded-xl text-sm flex items-start space-x-3">
                <i id="alertIcon" class="mt-0.5 text-base flex-shrink-0"></i>
                <span id="alertText"></span>
            </div>

            <form id="loginForm" class="space-y-5">
                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope text-[#99CC33] mr-1.5"></i>Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                        required
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-[#99CC33] mr-1.5"></i>Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                            required
                            minlength="6"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-3.5 text-gray-400 hover:text-[#669933] transition-colors">
                            <i class="far fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-[#99CC33] focus:ring-[#99CC33]">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot_password" class="text-sm text-[#003366] hover:text-[#99CC33] transition-colors font-medium">Forgot Password?</a>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    id="loginButton"
                    class="w-full bg-[#99CC33] hover:bg-[#88BB22] text-white font-semibold py-3.5 px-6 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg shadow-green-200"
                >
                    <span id="btnText"><i class="fas fa-sign-in-alt mr-2"></i>Access Dashboard</span>
                    <span id="btnSpinner" class="hidden items-center space-x-2">
                        <span class="spinner"></span>
                        <span>Signing in...</span>
                    </span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Need access? <a href="#" class="text-[#003366] font-semibold hover:text-[#99CC33] transition-colors">Contact TAMEC Support</a>
            </p>

        </div>
    </div>

<script>
    $(document).ready(function() {
        $('#loginForm').validate({
            rules: {
                email: { required: true, email: true },
                password: { required: true, minlength: 6 }
            },
            messages: {
                email: { required: 'Please enter your email address', email: 'Please enter a valid email address' },
                password: { required: 'Please enter your password', minlength: 'Password must be at least 6 characters' }
            },
            errorElement: 'span',
            errorClass: 'error',
            highlight: function(element) { $(element).addClass('error-field').removeClass('success-field'); },
            unhighlight: function(element) { $(element).removeClass('error-field').addClass('success-field'); },
            errorPlacement: function(error, element) { error.insertAfter(element.closest('.relative, div')); },
            submitHandler: function() { submitLogin(); }
        });
    });

    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'far fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye';
        }
    }

    function setLoading(isLoading) {
        $('#loginButton').prop('disabled', isLoading);
        if (isLoading) {
            $('#btnText').addClass('hidden');
            $('#btnSpinner').removeClass('hidden').addClass('flex');
        } else {
            $('#btnText').removeClass('hidden');
            $('#btnSpinner').addClass('hidden').removeClass('flex');
        }
    }

    function showAlert(message, type) {
        const isError = type === 'error';
        $('#alertBox')
            .removeClass('bg-red-50 border-red-200 text-red-700 bg-green-50 border-green-200 text-green-700')
            .addClass(isError ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700')
            .removeClass('hidden');
        $('#alertIcon')
            .removeClass('fas fa-exclamation-circle fa-check-circle text-red-500 text-green-500')
            .addClass(isError ? 'fas fa-exclamation-circle text-red-500' : 'fas fa-check-circle text-green-500');
        $('#alertText').text(message);
    }

    function hideAlert() { $('#alertBox').addClass('hidden'); }

    function submitLogin() {
        hideAlert();
        setLoading(true);

        fetch('loginauth', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                email: $('#email').val(),
                password: $('#password').val()
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === false) {
                showAlert(data.message || 'Invalid email or password. Please try again.', 'error');
                $('#loginForm').addClass('animate-shake');
                setTimeout(() => $('#loginForm').removeClass('animate-shake'), 500);
                setLoading(false);
            } else {
                showAlert('Login successful! Redirecting to dashboard...', 'success');
                setTimeout(() => { window.location.href = 'dashboard'; }, 1500);
            }
        })
        .catch(() => {
            showAlert('Unable to connect. Please check your connection and try again.', 'error');
            setLoading(false);
        });
    }
</script>

</body>
</html>
