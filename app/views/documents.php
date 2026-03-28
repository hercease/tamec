<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamec - Document Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="public/images/tamecfavicon.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #99CC33; border-radius: 5px; }

        .sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) { .sidebar { transform: translateX(0); } }

        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 30; display: none; }
        .overlay.active { display: block; }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 1rem; width: 100%; max-width: 480px; margin: 1rem; max-height: 90vh; overflow-y: auto; }

        .form-label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: .25rem; }
        .form-input  { width: 100%; border: 1px solid #D1D5DB; border-radius: .5rem; padding: .5rem .75rem; font-size: .875rem; outline: none; transition: border-color .15s; }
        .form-input:focus  { border-color: #99CC33; box-shadow: 0 0 0 3px rgba(153,204,51,.15); }
        .form-select { width: 100%; border: 1px solid #D1D5DB; border-radius: .5rem; padding: .5rem .75rem; font-size: .875rem; }

        .toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem; background: white;
            border-radius: .75rem; padding: 1rem 1.25rem; box-shadow: 0 10px 25px rgba(0,0,0,.15);
            z-index: 9999; transform: translateY(6rem); opacity: 0;
            transition: transform .3s, opacity .3s; min-width: 280px;
        }
        .toast.show { transform: translateY(0); opacity: 1; }

        table { border-collapse: collapse; width: 100%; }
        th, td { padding: .75rem 1rem; text-align: left; font-size: .875rem; }
        thead tr { background: #F9FAFB; border-bottom: 2px solid #E5E7EB; }
        tbody tr { border-bottom: 1px solid #F3F4F6; }
        tbody tr:hover { background: #FAFAFA; }
    </style>
</head>
<body class="bg-gray-50">

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">

    <!-- Top Nav -->
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
                    <i class="fas fa-file-alt text-[#99CC33] text-sm"></i>
                    <h2 class="text-base font-semibold text-gray-700">Document Management</h2>
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

    <main class="p-6">

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Total Documents</p>
                <p class="text-2xl font-bold text-[#003366]" id="statTotal">0</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Required</p>
                <p class="text-2xl font-bold text-red-500" id="statRequired">0</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Optional</p>
                <p class="text-2xl font-bold text-[#99CC33]" id="statOptional">0</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-[#003366]">Documents List</h2>
                <div class="flex items-center gap-3">
                    <input type="text" id="searchInput" oninput="filterDocs()" placeholder="Search documents..." class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-[#99CC33]" style="width:200px">
                    <button onclick="openAddModal()" class="flex items-center bg-[#99CC33] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#88BB22] transition whitespace-nowrap">
                        <i class="fas fa-plus mr-2"></i> Add Document
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Document Name</th>
                            <th>Tag</th>
                            <th>Required</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="docsTableBody">
                        <tr><td colspan="6" class="text-center py-8 text-gray-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Add / Edit Modal -->
<div id="docModal" class="modal">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-[#003366]" id="modalTitle">Add Document</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="docForm" onsubmit="saveDocument(event)">
                <input type="hidden" id="docId" value="">

                <div class="mb-4">
                    <label class="form-label">Document Name <span class="text-red-500">*</span></label>
                    <input type="text" id="docName" class="form-input" placeholder="e.g. Government ID" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Doc Tag <span class="text-red-500">*</span></label>
                    <input type="text" id="docTag" class="form-input" placeholder="e.g. government_id" required>
                    <p class="text-xs text-gray-400 mt-1">Unique identifier (lowercase, underscores). Auto-formatted on save.</p>
                </div>

                <div class="mb-6">
                    <label class="form-label">Is Optional?</label>
                    <select id="docOptional" class="form-select">
                        <option value="false">No — Required</option>
                        <option value="true">Yes — Optional</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#99CC33] text-white rounded-lg text-sm hover:bg-[#88BB22] transition">Save Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content max-w-sm">
        <div class="p-6 text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">Delete Document</h3>
            <p class="text-gray-500 text-sm mb-6">This will also remove all associated staff uploads. This cannot be undone.</p>
            <input type="hidden" id="deleteDocId">
            <div class="flex justify-center space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast">
    <div class="flex items-center">
        <i id="toastIcon" class="fas fa-check-circle text-[#99CC33] mr-3 text-xl"></i>
        <div>
            <p id="toastTitle" class="font-semibold text-gray-800 text-sm"></p>
            <p id="toastMessage" class="text-xs text-gray-500"></p>
        </div>
    </div>
</div>

<script>
    let allDocs = [];

    function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); document.getElementById('overlay').classList.toggle('active'); }
    function closeSidebar()  { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('active'); }

    function showToast(title, msg, type = 'success') {
        const toast = document.getElementById('toast');
        document.getElementById('toastTitle').textContent   = title;
        document.getElementById('toastMessage').textContent = msg;
        const icon = document.getElementById('toastIcon');
        icon.className = type === 'success'
            ? 'fas fa-check-circle text-[#99CC33] mr-3 text-xl'
            : 'fas fa-exclamation-circle text-red-500 mr-3 text-xl';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    function fetchDocs() {
        $.ajax({
            url: 'fetch_all_documents',
            method: 'POST',
            dataType: 'json',
            success(res) {
                allDocs = res.documents || [];
                renderTable(allDocs);
                updateStats();
            },
            error() { showToast('Error', 'Failed to load documents', 'error'); }
        });
    }

    function updateStats() {
        document.getElementById('statTotal').textContent    = allDocs.length;
        document.getElementById('statRequired').textContent = allDocs.filter(d => !d.optional).length;
        document.getElementById('statOptional').textContent = allDocs.filter(d => d.optional).length;
    }

    function renderTable(docs) {
        const tbody = document.getElementById('docsTableBody');
        if (!docs.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-400">No documents found. Click "Add Document" to create one.</td></tr>';
            return;
        }
        tbody.innerHTML = docs.map((d, i) => `
            <tr>
                <td class="text-gray-400">${i + 1}</td>
                <td class="font-medium text-gray-800">${d.doc_name}</td>
                <td><span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono">${d.doc_tag}</span></td>
                <td>
                    ${d.optional
                        ? '<span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Optional</span>'
                        : '<span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">Required</span>'}
                </td>
                <td class="text-gray-400 text-xs">${d.created_at ? d.created_at.split(' ')[0] : '—'}</td>
                <td>
                    <div class="flex space-x-2">
                        <button onclick="editDoc(${d.doc_id})" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="Edit">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="openDeleteModal(${d.doc_id})" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100" title="Delete">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function filterDocs() {
        const term = document.getElementById('searchInput').value.toLowerCase();
        renderTable(allDocs.filter(d =>
            d.doc_name.toLowerCase().includes(term) || d.doc_tag.toLowerCase().includes(term)
        ));
    }

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Document';
        document.getElementById('docForm').reset();
        document.getElementById('docId').value = '';
        document.getElementById('docModal').classList.add('active');
    }

    function closeModal() { document.getElementById('docModal').classList.remove('active'); }

    function editDoc(id) {
        const d = allDocs.find(x => x.doc_id === id);
        if (!d) return;
        document.getElementById('modalTitle').textContent = 'Edit Document';
        document.getElementById('docId').value      = d.doc_id;
        document.getElementById('docName').value    = d.doc_name;
        document.getElementById('docTag').value     = d.doc_tag;
        document.getElementById('docOptional').value = d.optional ? 'true' : 'false';
        document.getElementById('docModal').classList.add('active');
    }

    function saveDocument(event) {
        event.preventDefault();
        const docId = document.getElementById('docId').value;
        const payload = {
            doc_name: document.getElementById('docName').value,
            doc_tag:  document.getElementById('docTag').value,
            optional: document.getElementById('docOptional').value,
        };
        const url = docId ? 'update_document' : 'create_document';
        if (docId) payload.doc_id = docId;

        $.ajax({
            url,
            method: 'POST',
            data: payload,
            dataType: 'json',
            success(res) {
                if (res.status) {
                    closeModal();
                    fetchDocs();
                    showToast('Success', docId ? 'Document updated' : 'Document created');
                } else {
                    showToast('Error', res.message || 'Operation failed', 'error');
                }
            },
            error() { showToast('Error', 'Request failed', 'error'); }
        });
    }

    function openDeleteModal(id) {
        document.getElementById('deleteDocId').value = id;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('active'); }

    function confirmDelete() {
        const id = document.getElementById('deleteDocId').value;
        $.ajax({
            url: 'delete_document',
            method: 'POST',
            data: { doc_id: id },
            dataType: 'json',
            success(res) {
                closeDeleteModal();
                if (res.status) { fetchDocs(); showToast('Deleted', 'Document removed'); }
                else showToast('Error', res.message, 'error');
            },
            error() { showToast('Error', 'Request failed', 'error'); }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchDocs);
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
