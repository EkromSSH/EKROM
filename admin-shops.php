<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>จัดการร้านค้าเช่า (SaaS) - EKROM Admin</title>
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

        /* Prevent auto-zoom on mobile devices */
        @media screen and (max-width: 768px) {
            input, select, textarea, .swal2-input, .swal2-select, .swal2-textarea {
                font-size: 16px !important;
            }
        }

        /* SweetAlert Shops Modal Mobile Optimization */
        .swal-shop-container {
            -webkit-overflow-scrolling: touch !important;
            scroll-behavior: smooth;
        }
        @media (max-width: 768px) {
            .swal-shop-container {
                align-items: flex-start !important;
                overflow-y: auto !important;
                padding-top: max(1rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) !important;
                padding-bottom: max(18rem, 50vh) !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .swal-shop-popup {
                width: 100% !important;
                max-width: min(94vw, 460px) !important;
                margin: 0 auto !important;
                border-radius: 1.5rem !important;
                padding: 1.25rem 1rem !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25) !important;
            }
            .swal-shop-popup .swal2-title {
                font-size: 1.25rem !important;
                padding: 0 0 0.75rem 0 !important;
            }
            .swal-shop-popup .swal2-actions {
                margin-top: 1.25rem !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }
            .swal-shop-popup .swal2-actions button {
                flex: 1 !important;
                padding: 0.75rem 1rem !important;
                font-size: 0.95rem !important;
                border-radius: 0.75rem !important;
                margin: 0 !important;
            }
            .swal-shop-html {
                padding: 0 !important;
                margin: 0.25rem 0 0 0 !important;
                overflow: visible !important;
            }
        }
        .swal-shop-popup input,
        .swal-shop-popup select {
            font-size: 16px !important;
            -webkit-text-size-adjust: 100% !important;
        }
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
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
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
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการร้านค้าเช่า (SaaS) 🏢</h1>
                <p class="text-gray-500 mt-1 text-sm">ดูแลระบบร้านค้าเช่าสำเร็จรูป โดเมนร้านค้า วันหมดอายุสัญญาเช่า และรายได้ประจำ</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateShopModal()" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> เพิ่มร้านค้าเช่าใหม่
                </button>
                <button onclick="loadShops(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl font-bold shrink-0">🏢</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ร้านค้าเช่าทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalShops">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">🟢</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">เปิดบริการอยู่ (Active)</p>
                    <h3 class="text-xl md:text-2xl font-bold text-emerald-600" id="statActiveShops">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold shrink-0">💵</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">รายได้ค่าเช่าระบบ/เดือน</p>
                    <h3 class="text-xl md:text-2xl font-bold text-purple-600" id="statMonthlyRev">฿0.00</h3>
                </div>
            </div>
        </div>

        <!-- Shops List Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-pink-600">🌐</span> รายการร้านค้าเช่าทั้งหมด
                </h3>
                <input type="text" id="searchShop" oninput="filterShops()" placeholder="ค้นหาชื่อร้านหรือโดเมน..." class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:bg-white focus:border-pink-500 w-full sm:w-64">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">ชื่อร้าน</th>
                            <th class="py-3 px-4">โดเมน</th>
                            <th class="py-3 px-4">เจ้าของ (ตัวแทน)</th>
                            <th class="py-3 px-4">แพ็กเกจ</th>
                            <th class="py-3 px-4">ค่าบริการ</th>
                            <th class="py-3 px-4">วันหมดอายุ</th>
                            <th class="py-3 px-4">สถานะ</th>
                            <th class="py-3 px-4 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="shopsTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">กำลังโหลดรายการร้านค้า...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let shopsData = [];
        let usersData = [];

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

        function updateResellerDatalist(users) {
            let dl = document.getElementById('resellerList');
            if (!dl) {
                dl = document.createElement('datalist');
                dl.id = 'resellerList';
                document.body.appendChild(dl);
            }
            dl.innerHTML = (users || []).map(u => `<option value="${escapeHtml(u.username)}">${escapeHtml(u.username)} (${escapeHtml(u.role)})</option>`).join('');
        }

        async function loadShops(btn) {
            const isButton = btn && (btn instanceof Element || typeof btn.querySelector === 'function');
            const icon = isButton ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (isButton) btn.disabled = true;
            try {
                const res = await fetch('api/admin_shops.php?action=list', { cache: 'no-store' });
                const json = await res.json();
                if (json.status === 'success') {
                    shopsData = json.data.shops || [];
                    usersData = json.data.users || [];
                    updateResellerDatalist(usersData);
                    document.getElementById('statTotalShops').innerText = json.data.stats.total_shops;
                    document.getElementById('statActiveShops').innerText = json.data.stats.active_shops;
                    document.getElementById('statMonthlyRev').innerText = '฿' + json.data.stats.total_monthly_rev.toFixed(2);
                    renderShops(shopsData);

                    if (isButton) {
                        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชข้อมูลร้านค้าเช่าแล้ว' });
                    }
                } else {
                    document.getElementById('shopsTableBody').innerHTML = `<tr><td colspan="8" class="py-8 text-center text-red-500">${escapeHtml(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล')}</td></tr>`;
                }
            } catch (e) {
                console.error(e);
                document.getElementById('shopsTableBody').innerHTML = '<tr><td colspan="8" class="py-8 text-center text-red-500">การเชื่อมต่อขัดข้อง ไม่สามารถโหลดข้อมูลได้</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (isButton) btn.disabled = false;
            }
        }

        function renderShops(list) {
            const tbody = document.getElementById('shopsTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-gray-400">ยังไม่มีร้านค้าเช่าในระบบ</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(s => {
                const isExp = s.is_expired;
                let statusBadge = '';
                if (isExp) {
                    statusBadge = `<span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-full font-bold text-xs">หมดอายุ</span>`;
                } else if (s.status === 'active') {
                    statusBadge = `<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-xs">เปิดใช้งาน</span>`;
                } else if (s.status === 'trial') {
                    statusBadge = `<span class="px-2.5 py-1 bg-pink-50 text-pink-700 rounded-full font-bold text-xs">ทดลองใช้</span>`;
                } else {
                    statusBadge = `<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full font-bold text-xs">ระงับชั่วคราว</span>`;
                }

                const cleanDomain = (s.domain || '').replace(/^https?:\/\//i, '').replace(/\/+$/, '');
                const domainUrl = 'https://' + cleanDomain;

                return `
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">🏪</span>
                                <div>
                                    <p>${escapeHtml(s.name)}</p>
                                    <p class="text-[10px] text-gray-400 font-normal font-mono">ID: #${s.id}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-pink-600 hover:underline">
                            <a href="${domainUrl}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1">${escapeHtml(cleanDomain)} <span class="text-[10px]">↗</span></a>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-700">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                ${escapeHtml(s.owner_username)}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-xs text-slate-600"><span class="px-2.5 py-1 bg-slate-100 rounded-md border border-slate-200">${escapeHtml(s.package_tier)}</span></td>
                        <td class="py-3.5 px-4 font-bold text-emerald-600">฿${parseFloat(s.monthly_fee).toFixed(2)}/ด.</td>
                        <td class="py-3.5 px-4 text-xs font-mono ${isExp ? 'text-red-500 font-bold' : 'text-gray-500'}">${escapeHtml(s.expires_at)}</td>
                        <td class="py-3.5 px-4">${statusBadge}</td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <button onclick="openEditShopModal(${s.id})" class="px-2.5 py-1.5 bg-pink-50 text-pink-700 hover:bg-pink-100 rounded-lg font-bold text-xs transition-all">✏️ แก้ไข</button>
                            <button onclick="renewShop(${s.id}, '${escapeHtml(s.name)}')" class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-bold text-xs transition-all">🔄 ต่ออายุ</button>
                            <button onclick="toggleShopStatus(${s.id})" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg font-bold text-xs transition-all">${s.status === 'active' ? '⏸️ พัก' : '▶️ เปิด'}</button>
                            <button onclick="deleteShop(${s.id}, '${escapeHtml(s.name)}')" class="px-2.5 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-bold text-xs transition-all">🗑️ ลบ</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function filterShops() {
            const q = document.getElementById('searchShop').value.toLowerCase();
            const filtered = shopsData.filter(s => s.name.toLowerCase().includes(q) || s.domain.toLowerCase().includes(q) || s.owner_username.toLowerCase().includes(q));
            renderShops(filtered);
        }

        function setupSwalMobileKeyboardScroll(popup) {
            if (!popup) return;
            const container = popup.closest('.swal2-container') || popup.parentElement;
            if (!container) return;

            // Only apply on touch/mobile viewports
            if (window.innerWidth > 768) return;

            const inputs = popup.querySelectorAll('input, select, textarea');
            if (!inputs.length) return;

            let scrollTimer = null;
            let isScrolling = false;

            const scrollToElementSmoothly = (el) => {
                if (!el || document.activeElement !== el) return;
                if (!popup.contains(el)) return;

                requestAnimationFrame(() => {
                    const elRect = el.getBoundingClientRect();

                    // Visible height taking virtual keyboard into account
                    const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;

                    // Desired position from top of viewport:
                    // ~75px on phones, giving comfortable visibility for label and modal context
                    const desiredTop = Math.min(90, Math.max(60, vh * 0.18));
                    const safeBottom = vh - 50;

                    // If already comfortably visible in the upper safe area, don't move
                    if (elRect.top >= desiredTop - 25 && elRect.bottom <= safeBottom && elRect.top <= vh * 0.55) {
                        return;
                    }

                    const diff = elRect.top - desiredTop;
                    const targetScrollTop = Math.max(0, container.scrollTop + diff);

                    if (Math.abs(container.scrollTop - targetScrollTop) > 12) {
                        isScrolling = true;
                        container.scrollTo({
                            top: targetScrollTop,
                            behavior: 'smooth'
                        });
                        setTimeout(() => { isScrolling = false; }, 350);
                    }
                });
            };

            const handleFocus = (e) => {
                const el = e.target;
                if (scrollTimer) clearTimeout(scrollTimer);

                // If virtual keyboard is already visible, respond faster
                const isKeyboardOpen = window.visualViewport && (window.visualViewport.height < window.innerHeight * 0.82);
                const delay = isKeyboardOpen ? 70 : 230;

                scrollTimer = setTimeout(() => {
                    scrollToElementSmoothly(el);
                }, delay);
            };

            inputs.forEach(input => {
                input.style.fontSize = '16px';
                input.addEventListener('focus', handleFocus, { passive: true });
            });

            // Handle viewport resize (keyboard sliding up)
            let resizeTimer = null;
            const onResize = () => {
                if (isScrolling) return;
                if (resizeTimer) clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const active = document.activeElement;
                    if (active && popup.contains(active) && ['INPUT', 'SELECT', 'TEXTAREA'].includes(active.tagName)) {
                        scrollToElementSmoothly(active);
                    }
                }, 120);
            };

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', onResize);
            }

            // Automatically clean up when modal is closed/removed
            const observer = new MutationObserver(() => {
                if (!document.body.contains(popup)) {
                    if (scrollTimer) clearTimeout(scrollTimer);
                    if (resizeTimer) clearTimeout(resizeTimer);
                    if (window.visualViewport) {
                        window.visualViewport.removeEventListener('resize', onResize);
                    }
                    observer.disconnect();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        async function openCreateShopModal() {
            const { value: formValues } = await Swal.fire({
                title: '➕ เพิ่มร้านค้าเช่า SaaS ใหม่',
                customClass: {
                    container: 'swal-shop-container',
                    popup: 'swal-shop-popup',
                    htmlContainer: 'swal-shop-html'
                },
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ชื่อร้านค้า</label>
                            <input id="swalShopName" type="text" placeholder="เช่น FastSpeed VPN Store" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">โดเมน / ซับโดเมน</label>
                            <input id="swalShopDomain" type="text" placeholder="เช่น shop.fastspeed.com" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ชื่อผู้ดูแล (ตัวแทน)</label>
                            <input id="swalShopOwner" type="text" list="resellerList" placeholder="พิมพ์ชื่อผู้ใช้หรือเลือกจากรายการ" value="reseller" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">แพ็กเกจร้าน</label>
                                <select id="swalShopTier" class="swal2-select !m-0 !w-full !text-base">
                                    <option value="Basic">Basic</option>
                                    <option value="Standard" selected>Standard</option>
                                    <option value="VIP Pro">VIP Pro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ค่าเช่า/เดือน (฿)</label>
                                <input id="swalShopFee" type="number" step="any" value="299" class="swal2-input !m-0 !w-full !text-base">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">สถานะเริ่มต้น</label>
                                <select id="swalShopStatus" class="swal2-select !m-0 !w-full !text-base">
                                    <option value="active" selected>เปิดใช้งาน (Active)</option>
                                    <option value="trial">ทดลองใช้ (Trial)</option>
                                    <option value="suspended">ระงับชั่วคราว (Suspended)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ระยะเวลา (วัน)</label>
                                <input id="swalShopDays" type="number" value="30" class="swal2-input !m-0 !w-full !text-base">
                            </div>
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                },
                showCancelButton: true,
                confirmButtonText: 'สร้างร้านค้า',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalShopName').value.trim();
                    let domain = document.getElementById('swalShopDomain').value.trim();
                    domain = domain.replace(/^https?:\/\//i, '').replace(/\/+$/, '');
                    const owner = document.getElementById('swalShopOwner').value.trim();
                    const tier = document.getElementById('swalShopTier').value;
                    const status = document.getElementById('swalShopStatus').value;
                    const fee = parseFloat(document.getElementById('swalShopFee').value) || 0;
                    const days = parseInt(document.getElementById('swalShopDays').value) || 30;

                    if (!name || !domain) {
                        Swal.showValidationMessage('กรุณากรอกชื่อร้านและโดเมนให้ครบ');
                        return false;
                    }
                    return { name, domain, owner_username: owner, package_tier: tier, status, monthly_fee: fee, days };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_shops.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadShops();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openEditShopModal(id) {
            const s = shopsData.find(x => x.id == id);
            if (!s) return;

            // Format expires_at for datetime-local (YYYY-MM-DDTHH:MM)
            let expiryFormatted = '';
            if (s.expires_at) {
                const dt = new Date(s.expires_at.replace(' ', 'T'));
                if (!isNaN(dt.getTime())) {
                    const pad = num => String(num).padStart(2, '0');
                    expiryFormatted = `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
                }
            }

            const { value: formValues } = await Swal.fire({
                title: `✏️ แก้ไขร้านค้า (#${s.id})`,
                customClass: {
                    container: 'swal-shop-container',
                    popup: 'swal-shop-popup',
                    htmlContainer: 'swal-shop-html'
                },
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ชื่อร้านค้า</label>
                            <input id="swalEditName" type="text" value="${escapeHtml(s.name)}" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">โดเมน / ซับโดเมน</label>
                            <input id="swalEditDomain" type="text" value="${escapeHtml(s.domain)}" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ชื่อผู้ดูแล (ตัวแทน)</label>
                            <input id="swalEditOwner" type="text" list="resellerList" value="${escapeHtml(s.owner_username)}" class="swal2-input !m-0 !w-full !text-base">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">แพ็กเกจร้าน</label>
                                <select id="swalEditTier" class="swal2-select !m-0 !w-full !text-base">
                                    <option value="Basic" ${s.package_tier === 'Basic' ? 'selected' : ''}>Basic</option>
                                    <option value="Standard" ${s.package_tier === 'Standard' ? 'selected' : ''}>Standard</option>
                                    <option value="VIP Pro" ${s.package_tier === 'VIP Pro' ? 'selected' : ''}>VIP Pro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ค่าเช่า/เดือน (฿)</label>
                                <input id="swalEditFee" type="number" step="any" value="${parseFloat(s.monthly_fee)}" class="swal2-input !m-0 !w-full !text-base">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">สถานะ</label>
                                <select id="swalEditStatus" class="swal2-select !m-0 !w-full !text-base">
                                    <option value="active" ${s.status === 'active' ? 'selected' : ''}>เปิดใช้งาน (Active)</option>
                                    <option value="trial" ${s.status === 'trial' ? 'selected' : ''}>ทดลองใช้ (Trial)</option>
                                    <option value="suspended" ${s.status === 'suspended' ? 'selected' : ''}>ระงับชั่วคราว (Suspended)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">วันหมดอายุ</label>
                                <input id="swalEditExpiry" type="datetime-local" value="${expiryFormatted}" class="swal2-input !m-0 !w-full !text-base">
                            </div>
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                },
                showCancelButton: true,
                confirmButtonText: 'บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const name = document.getElementById('swalEditName').value.trim();
                    let domain = document.getElementById('swalEditDomain').value.trim();
                    domain = domain.replace(/^https?:\/\//i, '').replace(/\/+$/, '');
                    const owner = document.getElementById('swalEditOwner').value.trim();
                    const tier = document.getElementById('swalEditTier').value;
                    const status = document.getElementById('swalEditStatus').value;
                    const fee = parseFloat(document.getElementById('swalEditFee').value) || 0;
                    const expiry = document.getElementById('swalEditExpiry').value;

                    if (!name || !domain) {
                        Swal.showValidationMessage('กรุณากรอกชื่อร้านและโดเมนให้ครบ');
                        return false;
                    }
                    return { id: s.id, name, domain, owner_username: owner, package_tier: tier, status, monthly_fee: fee, expires_at: expiry };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_shops.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadShops();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function renewShop(id, name) {
            const { value: days } = await Swal.fire({
                title: `🔄 ต่ออายุร้านค้า`,
                text: `เลือกจำนวนวันที่ต้องการต่ออายุสำหรับ "${name}"`,
                customClass: {
                    container: 'swal-shop-container',
                    popup: 'swal-shop-popup',
                    htmlContainer: 'swal-shop-html'
                },
                input: 'select',
                inputOptions: { '30': '30 วัน (1 เดือน)', '60': '60 วัน (2 เดือน)', '90': '90 วัน (3 เดือน)', '365': '365 วัน (1 ปี)' },
                inputValue: '30',
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                },
                showCancelButton: true,
                confirmButtonText: 'ยืนยันต่ออายุ',
                cancelButtonText: 'ยกเลิก'
            });

            if (days) {
                try {
                    const res = await fetch('api/admin_shops.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'renew', id, days: parseInt(days) })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadShops();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function toggleShopStatus(id) {
            try {
                const res = await fetch('api/admin_shops.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle_status', id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Toast.fire({ icon: 'success', title: data.message });
                    loadShops();
                } else {
                    Swal.fire('ผิดพลาด', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
            }
        }

        async function deleteShop(id, name) {
            const confirm = await Swal.fire({
                title: `ลบร้านค้านี้?`,
                text: `คุณต้องการลบร้านค้า "${name}" ออกจากระบบหรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบข้อมูล',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_shops.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadShops();
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

        document.addEventListener('DOMContentLoaded', () => loadShops());
    </script>

    <script>
        // Anti-scroll guard: Keeps window scroll at 0 on mobile app shell so header never detaches
        if (typeof window !== 'undefined') {
            window.addEventListener('scroll', function() {
                if (window.innerWidth <= 1024 && (window.scrollY !== 0 || window.scrollX !== 0)) {
                    // Do not snap window if modal is open or form control is currently focused
                    if (document.querySelector('.swal2-container.swal2-shown') || 
                        (typeof Swal !== 'undefined' && Swal.isVisible()) || 
                        (document.activeElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName))) {
                        return;
                    }
                    window.scrollTo(0, 0);
                }
            }, { passive: true });
        }
    </script>
</body>
</html>
