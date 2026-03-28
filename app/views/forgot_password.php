<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                Secure Account<br>
                <span class="text-[#99CC33]">Recovery</span>
            </h2>
            <p class="text-blue-200 text-base leading-relaxed opacity-90">
                Enter your registered email address and we'll send you a temporary password to regain access to your account.
            </p>

            <div class="mt-10 grid grid-cols-3 gap-4">
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-bolt text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Instant</p>
                    <p class="text-blue-200 text-xs opacity-75">Delivery</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-shield-alt text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Secure</p>
                    <p class="text-blue-200 text-xs opacity-75">Encrypted</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center border border-white border-opacity-10">
                    <i class="fas fa-redo text-[#99CC33] text-xl mb-2 block"></i>
                    <p class="text-white text-xs font-medium">Easy</p>
                    <p class="text-blue-200 text-xs opacity-75">Reset</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md">

            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-8">
                <img src="public/images/tameclogo.png" alt="TAMEC" class="w-20 h-auto mx-auto">
            </div>

            <!-- Form State -->
            <div id="formState" class="fade-in">
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#003366] bg-opacity-10 mb-4">
                        <i class="fas fa-lock text-[#003366] text-xl"></i>
                    </div>
                    <h1 class="text-3xl font-extrabold text-[#003366] mb-2">Forgot Password?</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        No worries! Enter your registered email below and we'll send you a temporary password to get back in.
                    </p>
                </div>

                <!-- Alert -->
                <div id="alertBox" class="hidden mb-5 p-4 rounded-xl text-sm flex items-start space-x-3">
                    <i id="alertIcon" class="mt-0.5 text-base flex-shrink-0"></i>
                    <span id="alertText"></span>
                </div>

                <form id="forgotForm">
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-[#99CC33] mr-1.5"></i>Email Address
                        </label>
                        <input
                            type="email"
                            id="emailInput"
                            name="email"
                            placeholder="Enter your registered email"
                            autocomplete="email"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33] transition-colors"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full bg-[#99CC33] hover:bg-[#88BB22] text-white font-semibold py-3.5 px-6 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg shadow-green-200"
                    >
                        <span id="btnText"><i class="fas fa-paper-plane mr-2"></i>Send Temporary Password</span>
                        <span id="btnSpinner" class="hidden items-center space-x-2">
                            <span class="spinner"></span>
                            <span>Sending...</span>
                        </span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="login" class="text-sm text-[#003366] hover:text-[#99CC33] transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-1.5"></i>Back to Login
                    </a>
                </div>
            </div>

            <!-- Success State -->
            <div id="successState" class="hidden fade-in text-center">
                <div class="flex items-center justify-center w-20 h-20 rounded-full bg-[#f0f9e0] mx-auto mb-6">
                    <i class="fas fa-check-circle text-[#99CC33] text-4xl"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-[#003366] mb-3">Check Your Email</h2>
                <p class="text-gray-500 text-sm leading-relaxed mb-2">
                    We've sent a temporary password to
                </p>
                <p id="sentEmail" class="text-[#003366] font-semibold text-sm mb-6"></p>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-left mb-8">
                    <p class="text-amber-800 text-xs leading-relaxed">
                        <i class="fas fa-exclamation-triangle mr-1.5 text-amber-500"></i>
                        <strong>Important:</strong> Log in with the temporary password and immediately change it from the <strong>Change Password</strong> section in the sidebar.
                    </p>
                </div>

                <a href="login" class="inline-flex items-center bg-[#003366] hover:bg-[#004080] text-white font-semibold py-3 px-8 rounded-xl transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Go to Login
                </a>

                <div class="mt-5">
                    <button onclick="resetForm()" class="text-sm text-gray-400 hover:text-[#003366] transition-colors">
                        <i class="fas fa-redo mr-1"></i>Try a different email
                    </button>
                </div>
            </div>

        </div>
    </div>

<script>
    $('#forgotForm').on('submit', function(e) {
        e.preventDefault();

        const email = $('#emailInput').val().trim();
        if (!email) return;

        // Loading state
        $('#btnText').addClass('hidden');
        $('#btnSpinner').removeClass('hidden').addClass('flex');
        $('#submitBtn').prop('disabled', true);
        hideAlert();

        $.ajax({
            url: 'forgot_password_action',
            method: 'POST',
            data: { email },
            dataType: 'json',
            success(res) {
                if (res.status) {
                    $('#sentEmail').text(email);
                    $('#formState').addClass('hidden');
                    $('#successState').removeClass('hidden');
                } else {
                    showAlert(res.message || 'Something went wrong. Please try again.', 'error');
                }
            },
            error() {
                showAlert('Unable to connect. Please check your connection and try again.', 'error');
            },
            complete() {
                $('#btnText').removeClass('hidden');
                $('#btnSpinner').addClass('hidden').removeClass('flex');
                $('#submitBtn').prop('disabled', false);
            }
        });
    });

    function showAlert(message, type) {
        const isError = type === 'error';
        $('#alertBox')
            .removeClass('bg-red-50 border-red-200 text-red-700 bg-green-50 border-green-200 text-green-700')
            .addClass(isError ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700')
            .removeClass('hidden');
        $('#alertIcon')
            .removeClass('fa-exclamation-circle fa-check-circle text-red-500 text-green-500')
            .addClass(isError ? 'fas fa-exclamation-circle text-red-500' : 'fas fa-check-circle text-green-500');
        $('#alertText').text(message);
    }

    function hideAlert() { $('#alertBox').addClass('hidden'); }

    function resetForm() {
        $('#emailInput').val('');
        $('#successState').addClass('hidden');
        $('#formState').removeClass('hidden');
        hideAlert();
    }
</script>

</body>
</html>
