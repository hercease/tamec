<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Page Not Found (404)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Floating animation for 404 number */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Pulse animation for search button */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Medical cross pattern background */
        .medical-bg {
            background-image: radial-gradient(circle at 10px 10px, rgba(153, 204, 51, 0.05) 2px, transparent 2px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="bg-white font-sans antialiased min-h-screen flex items-center justify-center medical-bg">
    <!-- Main Container -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="grid md:grid-cols-2 gap-0">
                <!-- Left Side - Error Visual with Deep Blue -->
                <div class="bg-[#003366] p-12 flex flex-col items-center justify-center relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 left-0 w-40 h-40 border-2 border-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                        <div class="absolute bottom-0 right-0 w-80 h-80 border-2 border-white rounded-full translate-x-1/3 translate-y-1/3"></div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <i class="fas fa-plus text-white text-9xl opacity-5"></i>
                        </div>
                    </div>
                    
                    <!-- 404 Number with Animation -->
                    <div class="relative z-10 text-center">
                        <div class="float-animation">
                            <span class="text-9xl font-bold text-white">404</span>
                        </div>
                        <div class="mt-4 inline-flex items-center bg-[#669933] bg-opacity-20 px-6 py-2 rounded-full">
                            <i class="fas fa-exclamation-triangle text-[#99CC33] mr-2"></i>
                            <span class="text-[#99CC33] font-medium">Page Not Found</span>
                        </div>
                    </div>

                    <!-- Medical Icon -->
                    <div class="relative z-10 mt-8 flex justify-center space-x-4">
                        <div class="w-12 h-12 bg-[#669933] bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-stethoscope text-[#99CC33] text-xl"></i>
                        </div>
                        <div class="w-12 h-12 bg-[#669933] bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-heartbeat text-[#99CC33] text-xl"></i>
                        </div>
                        <div class="w-12 h-12 bg-[#669933] bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ambulance text-[#99CC33] text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Content -->
                <div class="p-12 bg-white flex flex-col justify-center">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="bg-[#003366] p-2 rounded-lg">
                            <div class="text-center">
                                <span class="text-white font-bold text-xl block leading-tight">TAMEC</span>
                                <span class="text-[#99CC33] text-[8px] font-semibold tracking-wider block">CARE</span>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#003366]">TAMEC</h2>
                            <p class="text-[#669933] text-xs font-medium">STAFFING SERVICES LTD</p>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <h1 class="text-3xl font-bold text-black mb-4">Oops! Page Not Found</h1>
                    <p class="text-gray-600 mb-6">
                        The page you're looking for might have been moved, deleted, or perhaps it never existed. 
                        Don't worry though, we're here to help you get back on track.
                    </p>

                    <!-- Quick Links with Lime Green Accents -->
                    <div class="space-y-4 mb-8">
                        <h3 class="text-sm font-semibold text-[#003366] flex items-center">
                            <i class="fas fa-arrow-right text-[#669933] mr-2"></i>
                            Quick Links
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="dashboard" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-tachometer-alt text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Dashboard</span>
                            </a>
                            <a href="staff" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-users text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Staff</span>
                            </a>
                            <a href="clients" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-user-tie text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Clients</span>
                            </a>
                            <a href="invoices" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-file-invoice text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Invoices</span>
                            </a>
                            <a href="payroll" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-money-bill-wave text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Payroll</span>
                            </a>
                            <a href="schedule" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-[#99CC33] hover:bg-opacity-10 transition group">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-[#99CC33] group-hover:bg-opacity-20">
                                    <i class="fas fa-calendar-alt text-[#669933] text-sm group-hover:text-[#99CC33]"></i>
                                </div>
                                <span class="text-sm text-black group-hover:text-[#003366]">Schedule</span>
                            </a>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="border-t border-gray-200 pt-6 mt-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-full flex items-center justify-center">
                                    <i class="fas fa-headset text-[#669933] text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-600">Need assistance?</span>
                            </div>
                            <a href="#" class="text-[#003366] font-semibold hover:text-[#99CC33] transition flex items-center">
                                Contact Support
                                <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Back to Home Button -->
                    <div class="mt-6">
                        <a href="/" class="w-full bg-[#003366] text-white font-semibold py-3 px-4 rounded-lg hover:bg-[#002244] transition duration-300 flex items-center justify-center pulse-animation">
                            <i class="fas fa-home mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Help Section -->
        <div class="mt-8 grid md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-lg shadow-md border border-gray-100">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle text-[#669933]"></i>
                    </div>
                    <h4 class="font-semibold text-black">Common Issues</h4>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>• Broken link from another page</li>
                    <li>• Page was moved or renamed</li>
                    <li>• Typo in the URL address</li>
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow-md border border-gray-100">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-[#669933] bg-opacity-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-envelope text-[#669933]"></i>
                    </div>
                    <h4 class="font-semibold text-black">Report Problem</h4>
                </div>
                <p class="text-sm text-gray-600 mb-2">Found a broken link?</p>
                <a href="#" class="text-[#003366] text-sm font-semibold hover:text-[#99CC33] transition">
                    Let us know →
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>© <?php echo date('Y'); ?> TAMEC Staffing Services Ltd. All rights reserved.</p>
            <div class="flex justify-center space-x-4 mt-2">
                <a href="#" class="hover:text-[#99CC33] transition">Privacy Policy</a>
                <span>•</span>
                <a href="#" class="hover:text-[#99CC33] transition">Terms of Use</a>
                <span>•</span>
                <a href="#" class="hover:text-[#99CC33] transition">Contact</a>
            </div>
        </div>
    </div>

    <!-- Toast for search (hidden by default) -->
    <div id="searchToast" class="fixed bottom-4 right-4 bg-[#003366] text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-x-full z-50 flex items-center">
        <i class="fas fa-info-circle mr-2 text-[#99CC33]"></i>
        <span>Search feature coming soon!</span>
    </div>

    <script>
        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (searchTerm) {
                // Show toast notification
                const toast = document.getElementById('searchToast');
                toast.classList.remove('translate-x-full');
                
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                }, 3000);
                
                // Clear input after search
                document.getElementById('searchInput').value = '';
            } else {
                // Shake the search input if empty
                const searchInput = document.getElementById('searchInput');
                searchInput.classList.add('animate-shake');
                searchInput.placeholder = 'Please enter a search term';
                
                setTimeout(() => {
                    searchInput.classList.remove('animate-shake');
                }, 500);
            }
        }

        // Handle enter key in search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Add shake animation style if not exists
        if (!document.querySelector('#shake-style')) {
            const style = document.createElement('style');
            style.id = 'shake-style';
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                .animate-shake {
                    animation: shake 0.5s ease-in-out;
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>