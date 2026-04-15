<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Agreement — TAMEC Care Staffing</title>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }

        .pdf-frame { width: 100%; height: 75vh; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
        @media (max-width: 768px) { .pdf-frame { height: 60vh; } }

        .sig-canvas { border: 2px dashed #99CC33; border-radius: 8px; background: #fff; width: 100%; height: 180px; touch-action: none; }

        .toast { position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; background: #fff; border-radius: .5rem; box-shadow: 0 10px 30px rgba(0,0,0,.15); z-index: 100; transform: translateX(420px); transition: transform .3s; border-left: 4px solid #99CC33; min-width: 280px; }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: #ef4444; }

        .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<?php if (!empty($error)): ?>
    <!-- Error State -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-lg max-w-md w-full p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Cannot Load Agreement</h1>
            <p class="text-gray-600 text-sm mb-6"><?php echo htmlspecialchars($error); ?></p>
            <p class="text-xs text-gray-400">If you believe this is a mistake, please contact <a href="mailto:info@tameccarestaffing.com" class="text-[#003366] font-semibold">info@tameccarestaffing.com</a>.</p>
        </div>
    </div>

<?php else: ?>
    <!-- Header -->
    <header class="bg-[#003366] text-white">
        <div class="max-w-5xl mx-auto px-4 py-5 flex items-center justify-between">
            <div>
                <p class="font-extrabold text-lg">TAMEC Care Staffing Services</p>
                <p class="text-xs text-[#99CC33]">Agreement For Signature</p>
            </div>
            <div class="hidden sm:block text-right">
                <p class="text-xs opacity-80">Signing as</p>
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($agreement['staff_name']); ?></p>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6">

        <!-- Intro Card -->
        <div class="bg-white rounded-2xl shadow-sm p-5 mb-5 border-l-4 border-[#99CC33]">
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#003366]">Please Review & Sign Your Agreement</h1>
            <p class="text-gray-500 text-sm mt-1">Take your time to read the document below, then sign and submit when ready.</p>
        </div>

        <!-- PDF Viewer -->
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 mb-5">
            <?php $pdfUrl = 'public/' . ltrim($agreement['template_file'], '/'); ?>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-[#003366]">Agreement Document</h2>
                <a href="<?php echo htmlspecialchars($pdfUrl); ?>" target="_blank" class="text-sm text-[#003366] hover:underline">
                    <i class="fas fa-external-link-alt mr-1"></i> Open in new tab
                </a>
            </div>
            <iframe class="pdf-frame" src="<?php echo htmlspecialchars($pdfUrl); ?>#view=FitH"></iframe>
        </div>

        <!-- Signature Section -->
        <div class="bg-white rounded-2xl shadow-sm p-5 mb-5">
            <h2 class="font-semibold text-[#003366] mb-4">Your Signature</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Legal Name</label>
                <input type="text" id="signerName" placeholder="e.g. John Michael Smith"
                       class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#99CC33]">
                <p class="text-xs text-gray-500 mt-1">Type your full legal name as it appears on official documents.</p>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">Draw Your Signature</label>
                    <button onclick="clearSignature()" class="text-xs text-gray-500 hover:text-red-500">
                        <i class="fas fa-eraser mr-1"></i> Clear
                    </button>
                </div>
                <canvas id="signatureCanvas" class="sig-canvas"></canvas>
                <p class="text-xs text-gray-500 mt-1">Use your mouse (or finger on touch devices) to draw your signature above.</p>
            </div>

            <label class="flex items-start gap-2 cursor-pointer mt-4 text-sm text-gray-700">
                <input type="checkbox" id="consentCheck" class="mt-0.5 w-4 h-4 accent-[#99CC33]">
                <span>I have read and agree to the terms of this agreement. I understand that my typed name and digital signature are legally binding and equivalent to a handwritten signature.</span>
            </label>
        </div>

        <!-- Submit -->
        <div class="flex justify-end mb-8">
            <button id="submitBtn" onclick="submitSignature()"
                    class="px-6 py-3 bg-[#99CC33] hover:bg-[#88bb22] text-white rounded-xl font-semibold text-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <i class="fas fa-check-circle"></i> Submit Signed Agreement
            </button>
        </div>

        <footer class="text-center text-xs text-gray-400 pb-8">
            TAMEC Care Staffing Services Ltd &bull; 3100 Steeles Ave W, Suite 403, Concord, ON L4K 3R1 &bull;
            <a href="mailto:info@tameccarestaffing.com" class="text-[#003366]">info@tameccarestaffing.com</a>
        </footer>
    </main>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] mr-3 text-xl"></i>
            <div>
                <p id="toastTitle" class="font-semibold text-gray-800">Success</p>
                <p id="toastMessage" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>

    <script>
        const TOKEN = <?php echo json_encode($_GET['token'] ?? ''); ?>;
        let signaturePad;

        function initSignaturePad() {
            const canvas = document.getElementById('signatureCanvas');
            resizeCanvas(canvas);
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 51, 102)'
            });
            window.addEventListener('resize', () => resizeCanvas(canvas));
        }

        function resizeCanvas(canvas) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width  = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            if (signaturePad) signaturePad.clear();
        }

        function clearSignature() { signaturePad.clear(); }

        function showToast(title, message, type) {
            type = type || 'success';
            const toast = document.getElementById('toast');
            const icon  = document.getElementById('toastIcon');
            document.getElementById('toastTitle').textContent   = title;
            document.getElementById('toastMessage').textContent = message;
            toast.classList.remove('error');
            if (type === 'error') {
                toast.classList.add('error');
                icon.className = 'fas fa-exclamation-circle text-red-500 mr-3 text-xl';
            } else {
                icon.className = 'fas fa-check-circle text-[#99CC33] mr-3 text-xl';
            }
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3600);
        }

        function submitSignature() {
            const name     = document.getElementById('signerName').value.trim();
            const consent  = document.getElementById('consentCheck').checked;

            if (!name) return showToast('Missing Name', 'Please type your full legal name.', 'error');
            if (signaturePad.isEmpty()) return showToast('Missing Signature', 'Please draw your signature above.', 'error');
            if (!consent) return showToast('Consent Required', 'Please check the consent box before submitting.', 'error');

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Submitting…';

            $.ajax({
                url: 'submit_signature',
                method: 'POST',
                data: {
                    token: TOKEN,
                    signer_name: name,
                    signature_data: signaturePad.toDataURL('image/png')
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        document.querySelector('main').innerHTML = `
                            <div class="bg-white rounded-2xl shadow-sm p-8 text-center mt-8">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                                </div>
                                <h1 class="text-2xl font-extrabold text-[#003366] mb-2">Agreement Signed!</h1>
                                <p class="text-gray-600 mb-5">Thank you. Your signed agreement has been recorded and sent to TAMEC.</p>
                                <p class="text-xs text-gray-400">You may now close this window.</p>
                            </div>`;
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Signed Agreement';
                        showToast('Error', res.message || 'Failed to submit signature.', 'error');
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Signed Agreement';
                    showToast('Error', 'Connection error. Please try again.', 'error');
                }
            });
        }

        window.addEventListener('DOMContentLoaded', initSignaturePad);
    </script>
<?php endif; ?>

</body>
</html>
