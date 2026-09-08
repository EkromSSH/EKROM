<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>จัดการหมวดหมู่ - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="mobile-fix.css">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        th, td { white-space: nowrap; }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') {
                window.location.href = 'login.php';
            }
        }).catch(() => { window.location.href = 'login.php'; });

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    </script>
</head>
<body class="admin-shell bg-slate-50 text-gray-800 antialiased flex flex-col md:flex-row h-screen overflow-hidden">

    <!-- Mobile Header -->
    <div class="admin-mobile-nav md:hidden bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-1.5 overflow-y-auto">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
            <div class="mt-auto pt-4 border-t border-slate-700">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex">
        <div class="flex items-center gap-3 mb-8 cursor-pointer" onclick="window.location.href='admin-dash.php'">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-1.5 overflow-y-auto">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-4 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 md:mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการหมวดหมู่เซิร์ฟเวอร์ 📑</h1>
                <p class="text-gray-500 mt-1 text-sm">จัดกลุ่มประเภทเซิร์ฟเวอร์ เช่น เครือข่ายซิม (AIS, True, DTAC), เล่นเกม, ดูหนัง หรือโซนเฉพาะ</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateCategoryModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> เพิ่มหมวดหมู่ใหม่
                </button>
                <button onclick="loadCategories()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span>🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold shrink-0">📑</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">หมวดหมู่ทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalCats">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">🖥️</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">เซิร์ฟเวอร์ในหมวดหมู่</p>
                    <h3 class="text-xl md:text-2xl font-bold text-emerald-600" id="statAssignedServers">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold shrink-0">⚠️</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ยังไม่ระบุหมวดหมู่</p>
                    <h3 class="text-xl md:text-2xl font-bold text-amber-600" id="statUnassignedServers">0</h3>
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-indigo-600">📁</span> รายการหมวดหมู่ทั้งหมด
                </h3>
                <input type="text" id="searchCategory" oninput="filterCategories()" placeholder="ค้นหาชื่อหมวดหมู่หรือธีม..." class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:bg-white focus:border-indigo-500 w-full sm:w-64">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">ลำดับ (Sort)</th>
                            <th class="py-3 px-4">ชื่อหมวดหมู่</th>
                            <th class="py-3 px-4">ธีมสี</th>
                            <th class="py-3 px-4 text-center">เซิร์ฟเวอร์ในหมวดนี้</th>
                            <th class="py-3 px-4 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="catsTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">กำลังโหลดหมวดหมู่...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let catsData = [];

        const themeMap = {
            blue: { name: 'Blue (น้ำเงินสดใส)', badge: 'bg-blue-50 text-blue-700 border-blue-200', dot: 'bg-blue-500' },
            indigo: { name: 'Indigo (น้ำเงินคราม)', badge: 'bg-indigo-50 text-indigo-700 border-indigo-200', dot: 'bg-indigo-500' },
            emerald: { name: 'Emerald (เขียว)', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' },
            rose: { name: 'Rose (แดง/ชมพู)', badge: 'bg-rose-50 text-rose-700 border-rose-200', dot: 'bg-rose-500' },
            amber: { name: 'Amber (ส้ม/ทอง)', badge: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
            purple: { name: 'Purple (ม่วง)', badge: 'bg-purple-50 text-purple-700 border-purple-200', dot: 'bg-purple-500' },
            cyan: { name: 'Cyan (ฟ้าสดใส)', badge: 'bg-cyan-50 text-cyan-700 border-cyan-200', dot: 'bg-cyan-500' }
        };

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0');
                drawer.classList.add('-translate-x-full');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        async function loadCategories() {
            try {
                const res = await fetch('api/admin_categories.php?action=list');
                const json = await res.json();
                if (json.status === 'success') {
                    catsData = json.data || [];
                    const stats = json.stats || {};
                    document.getElementById('statTotalCats').innerText = stats.total_categories ?? catsData.length;
                    document.getElementById('statAssignedServers').innerText = stats.total_assigned_servers ?? 0;
                    document.getElementById('statUnassignedServers').innerText = stats.unassigned_servers ?? 0;

                    renderCategories(catsData);
                }
            } catch (e) {
                console.error(e);
            }
        }

        function renderCategories(list) {
            const tbody = document.getElementById('catsTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-gray-400">ยังไม่มีหมวดหมู่เซิร์ฟเวอร์</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(c => {
                const themeKey = (c.color_theme || 'blue').toLowerCase();
                const theme = themeMap[themeKey] || { name: c.color_theme, badge: 'bg-slate-50 text-slate-700 border-slate-200', dot: 'bg-slate-400' };
                const srvCount = parseInt(c.server_count) || 0;
                const srvBadge = srvCount > 0 
                    ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-xs"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>${srvCount} เครื่อง</span>`
                    : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full font-bold text-xs">ว่าง (0 เครื่อง)</span>`;

                return `
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="py-3.5 px-4 font-mono font-bold text-gray-400">#${c.sort_order || 0}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-bold text-xs border ${theme.badge}">
                                <span class="w-2 h-2 rounded-full ${theme.dot}"></span>
                                ${escapeHtml(c.name)}
                            </span>
                            <span class="text-[10px] text-gray-400 font-mono">ID: #${c.id}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 font-mono text-xs text-gray-600">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 font-medium">${escapeHtml(theme.name)}</span>
                    </td>
                    <td class="py-3.5 px-4 text-center">${srvBadge}</td>
                    <td class="py-3.5 px-4 text-right space-x-1.5">
                        <button onclick="openEditCategory(${c.id})" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-bold text-xs transition-all">✏️ แก้ไข</button>
                        <button onclick="deleteCategory(${c.id}, '${escapeHtml(c.name)}', ${srvCount})" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-bold text-xs transition-all">🗑️ ลบ</button>
                    </td>
                </tr>
                `;
            }).join('');
        }

        function filterCategories() {
            const q = (document.getElementById('searchCategory').value || '').toLowerCase();
            const filtered = catsData.filter(c => 
                (c.name || '').toLowerCase().includes(q) || 
                (c.color_theme || '').toLowerCase().includes(q)
            );
            renderCategories(filtered);
        }

        async function openCreateCategoryModal() {
            const { value: formValues } = await Swal.fire({
                title: '➕ เพิ่มหมวดหมู่ใหม่',
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ชื่อหมวดหมู่</label>
                            <input id="swalCatName" type="text" placeholder="เช่น โปรเน็ต True / DTAC" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ธีมสี</label>
                            <select id="swalCatColor" class="swal2-select !m-0 !w-full">
                                <option value="blue" selected>Blue (น้ำเงินสดใส)</option>
                                <option value="indigo">Indigo (น้ำเงินคราม)</option>
                                <option value="emerald">Emerald (เขียว)</option>
                                <option value="rose">Rose (แดง/ชมพู)</option>
                                <option value="amber">Amber (ส้ม/ทอง)</option>
                                <option value="purple">Purple (ม่วง)</option>
                                <option value="cyan">Cyan (ฟ้าสดใส)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ลำดับการแสดงผล (Sort Order)</label>
                            <input id="swalCatOrder" type="number" value="1" class="swal2-input !m-0 !w-full">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'สร้างหมวดหมู่',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalCatName').value.trim();
                    const color = document.getElementById('swalCatColor').value;
                    const order = parseInt(document.getElementById('swalCatOrder').value) || 0;

                    if (!name) {
                        Swal.showValidationMessage('กรุณาระบุชื่อหมวดหมู่');
                        return false;
                    }
                    return { name, color_theme: color, sort_order: order };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_categories.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadCategories();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openEditCategory(id) {
            const c = catsData.find(x => x.id === id);
            if (!c) return;

            const currentTheme = (c.color_theme || 'blue').toLowerCase();

            const { value: formValues } = await Swal.fire({
                title: `✏️ แก้ไขหมวดหมู่ (#${c.id})`,
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ชื่อหมวดหมู่</label>
                            <input id="swalEditName" type="text" value="${escapeHtml(c.name)}" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ธีมสี</label>
                            <select id="swalEditColor" class="swal2-select !m-0 !w-full">
                                <option value="blue" ${currentTheme === 'blue' ? 'selected' : ''}>Blue (น้ำเงินสดใส)</option>
                                <option value="indigo" ${currentTheme === 'indigo' ? 'selected' : ''}>Indigo (น้ำเงินคราม)</option>
                                <option value="emerald" ${currentTheme === 'emerald' ? 'selected' : ''}>Emerald (เขียว)</option>
                                <option value="rose" ${currentTheme === 'rose' ? 'selected' : ''}>Rose (แดง/ชมพู)</option>
                                <option value="amber" ${currentTheme === 'amber' ? 'selected' : ''}>Amber (ส้ม/ทอง)</option>
                                <option value="purple" ${currentTheme === 'purple' ? 'selected' : ''}>Purple (ม่วง)</option>
                                <option value="cyan" ${currentTheme === 'cyan' ? 'selected' : ''}>Cyan (ฟ้าสดใส)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ลำดับการแสดงผล (Sort Order)</label>
                            <input id="swalEditOrder" type="number" value="${c.sort_order || 0}" class="swal2-input !m-0 !w-full">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalEditName').value.trim();
                    const color = document.getElementById('swalEditColor').value;
                    const order = parseInt(document.getElementById('swalEditOrder').value) || 0;

                    if (!name) {
                        Swal.showValidationMessage('กรุณาระบุชื่อหมวดหมู่');
                        return false;
                    }
                    return { id, name, color_theme: color, sort_order: order };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_categories.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadCategories();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function deleteCategory(id, name, srvCount) {
            if (srvCount > 0) {
                Swal.fire({
                    title: 'ไม่สามารถลบหมวดหมู่นี้ได้',
                    text: `มีเซิร์ฟเวอร์กำลังใช้งานหมวดหมู่นี้อยู่ ${srvCount} เครื่อง กรุณาย้ายเซิร์ฟเวอร์ไปหมวดอื่นก่อนลบ`,
                    icon: 'warning',
                    confirmButtonText: 'รับทราบ'
                });
                return;
            }

            const confirm = await Swal.fire({
                title: `ลบหมวดหมู่ "${name}"?`,
                text: 'คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่นี้ออกจากระบบ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันลบ',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_categories.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadCategories();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        document.addEventListener('DOMContentLoaded', loadCategories);
    </script>

    <script>
        // Anti-scroll guard: Keeps window scroll at 0 on mobile app shell so header never detaches
        if (typeof window !== 'undefined') {
            window.addEventListener('scroll', function() {
                if (window.innerWidth <= 1024 && (window.scrollY !== 0 || window.scrollX !== 0)) {
                    window.scrollTo(0, 0);
                }
            }, { passive: true });
        }
    </script>
</body>
</html>
