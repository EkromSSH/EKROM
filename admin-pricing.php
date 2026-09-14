<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>จัดการโซนราคา - EKROM Admin</title>
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
    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 max-w-[85vw] h-full max-h-[100dvh] flex flex-col p-5 sm:p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl overflow-y-auto overscroll-contain">
            <div class="drawer-header flex justify-between items-center mb-6 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="drawer-title font-bold text-xl tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
            </div>
            <nav class="flex-1 min-h-0 overflow-y-auto space-y-1.5 pr-1 overscroll-contain">
                <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
                <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
                <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-2 sm:mt-4">🏠 กลับหน้าลูกค้า</a>
            </nav>
            <div class="drawer-footer shrink-0 mt-auto pt-4 border-t border-slate-700 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))]">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
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
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการโซนราคา (Price Tiers) 🏷️</h1>
                <p class="text-gray-500 mt-1 text-sm">กำหนดและปรับเปลี่ยนอัตราค่าบริการตามแพ็กเกจวัน (1, 7, 15, 30 วัน) สำหรับแต่ละโซน</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateTierModal()" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> เพิ่มโซนราคาใหม่
                </button>
                <button onclick="loadTiers(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold shrink-0">🏷️</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">โซนราคาทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalTiers">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl font-bold shrink-0">⚙️</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">เซิร์ฟเวอร์ที่ผูกโซน</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalServers">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">💎</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ราคาเริ่มต้น (1 วัน)</p>
                    <h3 class="text-xl md:text-2xl font-bold text-emerald-600" id="statMinPrice">฿0</h3>
                </div>
            </div>
        </div>

        <!-- Tiers Grid & Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-amber-500">💰</span> ตารางกำหนดราคาตามโซน
                </h3>
                <input type="text" id="searchTier" oninput="filterTiers()" placeholder="ค้นหาชื่อโซนหรือธีมสี..." class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:bg-white focus:border-pink-500 w-full sm:w-64">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">ชื่อโซนราคา</th>
                            <th class="py-3 px-4">ธีมสี</th>
                            <th class="py-3 px-4 text-center">1 วัน</th>
                            <th class="py-3 px-4 text-center">7 วัน</th>
                            <th class="py-3 px-4 text-center">15 วัน</th>
                            <th class="py-3 px-4 text-center">30 วัน</th>
                            <th class="py-3 px-4 text-center">เซิร์ฟเวอร์ที่ใช้</th>
                            <th class="py-3 px-4 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="tiersTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">กำลังโหลดโซนราคา...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let tiersData = [];

        const themeMap = {
            pink: { name: 'Pink (ชมพูสดใส)', badge: 'bg-pink-50 text-pink-700 border-pink-200', dot: 'bg-pink-500' },
            blue: { name: 'Blue (น้ำเงิน)', badge: 'bg-blue-50 text-blue-700 border-blue-200', dot: 'bg-blue-500' },
            emerald: { name: 'Emerald (เขียว)', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' },
            purple: { name: 'Purple (ม่วง)', badge: 'bg-purple-50 text-purple-700 border-purple-200', dot: 'bg-purple-500' },
            rose: { name: 'Rose (ชมพู/แดง)', badge: 'bg-rose-50 text-rose-700 border-rose-200', dot: 'bg-rose-500' },
            amber: { name: 'Amber (ส้ม/ทอง)', badge: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
            indigo: { name: 'Indigo (น้ำเงินคราม)', badge: 'bg-indigo-50 text-indigo-700 border-indigo-200', dot: 'bg-indigo-500' },
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

        async function loadTiers(btn) {
            const isButton = btn && (btn instanceof Element || typeof btn.querySelector === 'function');
            const icon = isButton ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (isButton) btn.disabled = true;
            try {
                const res = await fetch('api/admin_pricing.php?action=list', { cache: 'no-store' });
                const json = await res.json();
                if (json.status === 'success') {
                    tiersData = json.data || [];
                    const stats = json.stats || {};
                    document.getElementById('statTotalTiers').innerText = stats.total_tiers ?? tiersData.length;
                    document.getElementById('statTotalServers').innerText = stats.total_servers ?? 0;
                    document.getElementById('statMinPrice').innerText = '฿' + (stats.min_price ?? 0);

                    renderTiers(tiersData);

                    if (isButton) {
                        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชข้อมูลราคาแล้ว' });
                    }
                } else {
                    document.getElementById('tiersTableBody').innerHTML = `<tr><td colspan="8" class="py-8 text-center text-red-500">${escapeHtml(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล')}</td></tr>`;
                }
            } catch (e) {
                console.error(e);
                document.getElementById('tiersTableBody').innerHTML = '<tr><td colspan="8" class="py-8 text-center text-red-500">การเชื่อมต่อขัดข้อง ไม่สามารถโหลดข้อมูลได้</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (isButton) btn.disabled = false;
            }
        }

        function renderTiers(list) {
            const tbody = document.getElementById('tiersTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-gray-400">ยังไม่มีการกำหนดโซนราคา</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(t => {
                const themeKey = (t.color_theme || 'pink').toLowerCase();
                const theme = themeMap[themeKey] || { name: t.color_theme, badge: 'bg-slate-50 text-slate-700 border-slate-200', dot: 'bg-slate-400' };
                const prices = t.prices || [5, 25, 45, 80];
                const srvCount = parseInt(t.server_count) || 0;
                const srvBadge = srvCount > 0 
                    ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-pink-50 text-pink-700 rounded-full font-bold text-xs"><span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>${srvCount} เครื่อง</span>`
                    : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full font-bold text-xs">ว่าง (0 เครื่อง)</span>`;

                return `
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-bold text-xs border ${theme.badge}">
                                    <span class="w-2 h-2 rounded-full ${theme.dot}"></span>
                                    ${escapeHtml(t.name)}
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono">ID: #${t.id}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-gray-600">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 font-medium">${escapeHtml(theme.name)}</span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-center text-emerald-600">฿${parseFloat(prices[0] || 0)}</td>
                        <td class="py-3.5 px-4 font-bold text-center text-emerald-600">฿${parseFloat(prices[1] || 0)}</td>
                        <td class="py-3.5 px-4 font-bold text-center text-emerald-600">฿${parseFloat(prices[2] || 0)}</td>
                        <td class="py-3.5 px-4 font-bold text-center text-emerald-600">฿${parseFloat(prices[3] || 0)}</td>
                        <td class="py-3.5 px-4 text-center">${srvBadge}</td>
                        <td class="py-3.5 px-4 text-right space-x-1.5">
                            <button onclick="openEditTier(${t.id})" class="px-3 py-1.5 bg-pink-50 text-pink-600 hover:bg-pink-100 rounded-lg font-bold text-xs transition-all">✏️ แก้ไขราคา</button>
                            <button onclick="deleteTier(${t.id}, '${escapeHtml(t.name)}', ${srvCount})" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-bold text-xs transition-all">🗑️ ลบ</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function filterTiers() {
            const q = (document.getElementById('searchTier').value || '').toLowerCase();
            const filtered = tiersData.filter(t => 
                (t.name || '').toLowerCase().includes(q) || 
                (t.color_theme || '').toLowerCase().includes(q)
            );
            renderTiers(filtered);
        }

        async function openCreateTierModal() {
            const { value: formValues } = await Swal.fire({
                title: '➕ เพิ่มโซนราคาใหม่',
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ชื่อโซนราคา</label>
                            <input id="swalTierName" type="text" placeholder="เช่น สตรีมมิ่ง & เน็ตฟลิกซ์" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ธีมสี</label>
                            <select id="swalTierColor" class="swal2-select !m-0 !w-full">
                                <option value="pink" selected>Pink (ชมพูสดใส)</option>
                                <option value="emerald">Emerald (เขียว)</option>
                                <option value="purple">Purple (ม่วง)</option>
                                <option value="rose">Rose (ชมพู/แดง)</option>
                                <option value="amber">Amber (ส้ม/ทอง)</option>
                                <option value="indigo">Indigo (น้ำเงินคราม)</option>
                                <option value="cyan">Cyan (ฟ้าสดใส)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 1 วัน (฿)</label>
                                <input id="swalP1" type="number" step="any" min="0" value="5" class="swal2-input !m-0 !w-full">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 7 วัน (฿)</label>
                                <input id="swalP7" type="number" step="any" min="0" value="25" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 15 วัน (฿)</label>
                                <input id="swalP15" type="number" step="any" min="0" value="45" class="swal2-input !m-0 !w-full">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 30 วัน (฿)</label>
                                <input id="swalP30" type="number" step="any" min="0" value="80" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalTierName').value.trim();
                    const color = document.getElementById('swalTierColor').value;
                    const p1 = Math.max(0, parseFloat(document.getElementById('swalP1').value) || 0);
                    const p7 = Math.max(0, parseFloat(document.getElementById('swalP7').value) || 0);
                    const p15 = Math.max(0, parseFloat(document.getElementById('swalP15').value) || 0);
                    const p30 = Math.max(0, parseFloat(document.getElementById('swalP30').value) || 0);

                    if (!name) {
                        Swal.showValidationMessage('กรุณาระบุชื่อโซนราคา');
                        return false;
                    }
                    return { name, color_theme: color, p1, p7, p15, p30 };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_pricing.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadTiers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openEditTier(id) {
            const t = tiersData.find(x => x.id === id);
            if (!t) return;
            const prices = t.prices || [5, 25, 45, 80];
            const currentTheme = (t.color_theme || 'pink').toLowerCase();

            const { value: formValues } = await Swal.fire({
                title: `✏️ แก้ไขราคา: ${t.name}`,
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ชื่อโซนราคา</label>
                            <input id="swalEditName" type="text" value="${escapeHtml(t.name)}" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">ธีมสี</label>
                            <select id="swalEditColor" class="swal2-select !m-0 !w-full">
                                <option value="pink" ${currentTheme === 'pink' || currentTheme === 'blue' ? 'selected' : ''}>Pink (ชมพูสดใส)</option>
                                <option value="emerald" ${currentTheme === 'emerald' ? 'selected' : ''}>Emerald (เขียว)</option>
                                <option value="purple" ${currentTheme === 'purple' ? 'selected' : ''}>Purple (ม่วง)</option>
                                <option value="rose" ${currentTheme === 'rose' ? 'selected' : ''}>Rose (ชมพู/แดง)</option>
                                <option value="amber" ${currentTheme === 'amber' ? 'selected' : ''}>Amber (ส้ม/ทอง)</option>
                                <option value="indigo" ${currentTheme === 'indigo' ? 'selected' : ''}>Indigo (น้ำเงินคราม)</option>
                                <option value="cyan" ${currentTheme === 'cyan' ? 'selected' : ''}>Cyan (ฟ้าสดใส)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 1 วัน (฿)</label>
                                <input id="swalEditP1" type="number" step="any" min="0" value="${prices[0]}" class="swal2-input !m-0 !w-full">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 7 วัน (฿)</label>
                                <input id="swalEditP7" type="number" step="any" min="0" value="${prices[1]}" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 15 วัน (฿)</label>
                                <input id="swalEditP15" type="number" step="any" min="0" value="${prices[2]}" class="swal2-input !m-0 !w-full">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ราคา 30 วัน (฿)</label>
                                <input id="swalEditP30" type="number" step="any" min="0" value="${prices[3]}" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalEditName').value.trim();
                    const color = document.getElementById('swalEditColor').value;
                    const p1 = Math.max(0, parseFloat(document.getElementById('swalEditP1').value) || 0);
                    const p7 = Math.max(0, parseFloat(document.getElementById('swalEditP7').value) || 0);
                    const p15 = Math.max(0, parseFloat(document.getElementById('swalEditP15').value) || 0);
                    const p30 = Math.max(0, parseFloat(document.getElementById('swalEditP30').value) || 0);

                    if (!name) {
                        Swal.showValidationMessage('กรุณาระบุชื่อโซนราคา');
                        return false;
                    }
                    return { id: t.id, name, color_theme: color, p1, p7, p15, p30 };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_pricing.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadTiers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function deleteTier(id, name, serverCount) {
            if (serverCount > 0) {
                Swal.fire({
                    title: 'ไม่สามารถลบโซนราคานี้ได้',
                    text: `มีเซิร์ฟเวอร์กำลังใช้โซนนี้อยู่ ${serverCount} เครื่อง กรุณาเปลี่ยนโซนราคาของเซิร์ฟเวอร์ก่อนลบ`,
                    icon: 'warning',
                    confirmButtonText: 'รับทราบ'
                });
                return;
            }

            const confirm = await Swal.fire({
                title: `ลบโซนราคา "${name}"?`,
                text: 'คุณแน่ใจหรือไม่ว่าต้องการลบโซนราคานี้ออกจากระบบ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันลบ',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_pricing.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadTiers();
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

        document.addEventListener('DOMContentLoaded', () => loadTiers());
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
