<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ยอดขายตัวแทน - EKROM Admin</title>
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
            <div class="flex justify-between items-center mb-6 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
            </div>
            <nav class="flex-1 min-h-0 overflow-y-auto space-y-1.5 pr-1 overscroll-contain">
                <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
                <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🤝 ยอดขายตัวแทน</a>
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
                <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-2 sm:mt-4">🏠 กลับหน้าลูกค้า</a>
            </nav>
            <div class="shrink-0 mt-auto pt-4 border-t border-slate-700 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))]">
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
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ยอดขายตัวแทนจำหน่าย 🤝</h1>
                <p class="text-gray-500 mt-1 text-sm">ตรวจสอบรายชื่อตัวแทน ยอดขาย เครดิตคงเหลือ และประวัติการทำรายการ</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openPromoteModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> แต่งตั้งตัวแทนใหม่
                </button>
                <button onclick="loadResellers(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold shrink-0">🤝</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ตัวแทนทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statResellers">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">💰</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">เครดิตตัวแทนรวม</p>
                    <h3 class="text-xl md:text-2xl font-bold text-emerald-600" id="statBalance">฿0.00</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl font-bold shrink-0">📁</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">VPN ที่สร้างโดยตัวแทน</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statVpns">0</h3>
                </div>
            </div>
        </div>

        <!-- Resellers List Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-indigo-600">📋</span> รายชื่อตัวแทนจำหน่ายในระบบ
                </h3>
                <input type="text" id="searchReseller" oninput="filterResellers()" placeholder="ค้นหาตัวแทน..." class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:bg-white focus:border-indigo-500 w-full sm:w-64">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">รหัส</th>
                            <th class="py-3 px-4">ชื่อตัวแทน (Username)</th>
                            <th class="py-3 px-4">ยอดเงินคงเหลือ</th>
                            <th class="py-3 px-4">VPN ทั้งหมด</th>
                            <th class="py-3 px-4">VPN ใช้งานอยู่</th>
                            <th class="py-3 px-4">วันที่สมัคร</th>
                            <th class="py-3 px-4 text-right">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody id="resellerTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="py-8 text-center text-gray-400">กำลังโหลดข้อมูลตัวแทน...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Reseller Orders -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="text-emerald-600">📜</span> รายการคำสั่งซื้อล่าสุดของตัวแทน
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">ตัวแทน</th>
                            <th class="py-3 px-4">ประเภท</th>
                            <th class="py-3 px-4">จำนวนเงิน</th>
                            <th class="py-3 px-4">รายละเอียด</th>
                            <th class="py-3 px-4">เวลา</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="6" class="py-6 text-center text-gray-400">กำลังโหลดรายการ...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let resellersData = [];

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

        async function loadResellers(btn) {
            const isButton = btn && (btn instanceof Element || typeof btn.querySelector === 'function');
            const icon = isButton ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (isButton) btn.disabled = true;
            try {
                const res = await fetch('api/admin_resellers.php?action=list', { cache: 'no-store' });
                const json = await res.json();
                if (json.status === 'success') {
                    const data = json.data;
                    resellersData = data.resellers || [];
                    document.getElementById('statResellers').innerText = data.stats.total_resellers;
                    document.getElementById('statBalance').innerText = '฿' + data.stats.total_balance.toFixed(2);
                    document.getElementById('statVpns').innerText = data.stats.total_vpns;

                    renderResellers(resellersData);
                    renderOrders(data.recent_orders || []);
                    window.eligibleUsers = data.eligible_users || [];

                    if (isButton) {
                        Toast.fire({ icon: 'success', title: 'รีเฟรชข้อมูลตัวแทนแล้ว' });
                    }
                } else {
                    document.getElementById('resellerTableBody').innerHTML = `<tr><td colspan="7" class="py-8 text-center text-red-500">${escapeHtml(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล')}</td></tr>`;
                }
            } catch (e) {
                console.error(e);
                document.getElementById('resellerTableBody').innerHTML = '<tr><td colspan="7" class="py-8 text-center text-red-500">การเชื่อมต่อขัดข้อง ไม่สามารถโหลดข้อมูลได้</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (isButton) btn.disabled = false;
            }
        }

        function renderResellers(list) {
            const tbody = document.getElementById('resellerTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-gray-400">ยังไม่มีตัวแทนจำหน่ายในระบบ</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(r => `
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="py-3.5 px-4 font-mono text-xs text-gray-400">#${r.id}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-800 flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center font-bold text-xs">🤝</div>
                        ${escapeHtml(r.username)}
                    </td>
                    <td class="py-3.5 px-4 font-bold text-emerald-600">฿${parseFloat(r.balance).toFixed(2)}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-700">${r.total_vpns} เครื่อง</td>
                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full font-bold text-xs">${r.active_vpns || 0} กำลังใช้งาน</span></td>
                    <td class="py-3.5 px-4 text-xs text-gray-400">${r.created_at || '--'}</td>
                    <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                        <button onclick="openAdjustBalance(${r.id}, '${escapeHtml(r.username)}', ${r.balance})" class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1">💰 เติม/หักเงิน</button>
                        <button onclick="openResetPassword(${r.id}, '${escapeHtml(r.username)}')" class="px-2.5 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1">🔑 รหัสผ่าน</button>
                        <button onclick="demoteReseller(${r.id}, '${escapeHtml(r.username)}')" class="px-2.5 py-1.5 bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1">ปลดตัวแทน</button>
                    </td>
                </tr>
            `).join('');
        }

        function formatOrderType(type) {
            switch (type) {
                case 'admin_adjust':
                    return '<span class="px-2 py-0.5 rounded text-[11px] bg-amber-50 text-amber-700 font-semibold border border-amber-200">ปรับยอดเงิน</span>';
                case 'create_vpn':
                    return '<span class="px-2 py-0.5 rounded text-[11px] bg-blue-50 text-blue-700 font-semibold border border-blue-200">สร้าง VPN</span>';
                case 'renew_vpn':
                    return '<span class="px-2 py-0.5 rounded text-[11px] bg-purple-50 text-purple-700 font-semibold border border-purple-200">ต่ออายุ VPN</span>';
                case 'topup':
                    return '<span class="px-2 py-0.5 rounded text-[11px] bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">เติมเงิน</span>';
                default:
                    return `<span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 font-semibold">${escapeHtml(type)}</span>`;
            }
        }

        function renderOrders(orders) {
            const tbody = document.getElementById('ordersTableBody');
            if (!orders.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-gray-400">ยังไม่มีรายการสั่งซื้อของตัวแทน</td></tr>`;
                return;
            }

            tbody.innerHTML = orders.map((o, idx) => `
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="py-3 px-4 text-gray-400 text-xs">${idx + 1}</td>
                    <td class="py-3 px-4 font-bold text-indigo-600">${escapeHtml(o.username)}</td>
                    <td class="py-3 px-4">${formatOrderType(o.type)}</td>
                    <td class="py-3 px-4 font-bold text-slate-900">฿${parseFloat(o.amount).toFixed(2)}</td>
                    <td class="py-3 px-4 text-xs text-gray-500">${escapeHtml(o.description || '')}</td>
                    <td class="py-3 px-4 text-xs text-gray-400">${o.created_at || '--'}</td>
                </tr>
            `).join('');
        }

        function filterResellers() {
            const q = document.getElementById('searchReseller').value.toLowerCase();
            const filtered = resellersData.filter(r => r.username.toLowerCase().includes(q));
            renderResellers(filtered);
        }

        async function openAdjustBalance(userId, username, currentBalance) {
            const { value: formValues } = await Swal.fire({
                title: `💰 ปรับยอดเงิน: ${escapeHtml(username)}`,
                html: `
                    <div class="text-left text-sm space-y-3">
                        <p class="text-gray-500">ยอดเงินปัจจุบัน: <strong class="text-emerald-600">฿${parseFloat(currentBalance).toFixed(2)}</strong></p>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">จำนวนเงิน (บาท)</label>
                            <input id="swalAmount" type="number" step="0.01" placeholder="เช่น 100 หรือ -50" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">ใส่ค่าบวกเพื่อเพิ่มยอด หรือใส่ค่าลบ (-) เพื่อหักเงิน</p>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700">หมายเหตุ</label>
                            <input id="swalNote" type="text" placeholder="เช่น เติมเครดิตตัวแทน, คืนเงิน" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const amount = document.getElementById('swalAmount').value;
                    const note = document.getElementById('swalNote').value.trim();
                    if (!amount || isNaN(amount) || parseFloat(amount) === 0) {
                        Swal.showValidationMessage('กรุณาระบุจำนวนเงินที่ต้องการปรับ (ห้ามเป็น 0)');
                        return false;
                    }
                    return { amount: parseFloat(amount), note: note || 'แอดมินปรับยอดเงินตัวแทน' };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'adjust_balance', user_id: userId, amount: formValues.amount, note: formValues.note })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadResellers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถปรับยอดเงินได้', 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openResetPassword(userId, username) {
            const { value: newPassword } = await Swal.fire({
                title: `🔑 รีเซ็ตรหัสผ่าน: ${escapeHtml(username)}`,
                input: 'password',
                inputLabel: 'กำหนดรหัสผ่านใหม่',
                inputPlaceholder: 'อย่างน้อย 4 ตัวอักษร',
                showCancelButton: true,
                confirmButtonText: 'บันทึกรหัสผ่านใหม่',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value || value.trim().length < 4) {
                        return 'กรุณากรอกรหัสผ่านอย่างน้อย 4 ตัวอักษร';
                    }
                }
            });

            if (newPassword) {
                try {
                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'reset_password', user_id: userId, new_password: newPassword.trim() })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openPromoteModal() {
            const users = window.eligibleUsers || [];
            let optionsHtml = users.map(u => `<option value="${u.id}">${escapeHtml(u.username)} (#${u.id})</option>`).join('');

            const { value: formValues } = await Swal.fire({
                title: '➕ แต่งตั้งตัวแทนใหม่',
                html: `
                    <div class="text-left text-sm space-y-4">
                        <div class="flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                            <button type="button" id="tabPromoteBtn" onclick="switchPromoteTab('promote')" class="flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all bg-white shadow-sm text-indigo-600">เลื่อนขั้นสมาชิกเดิม</button>
                            <button type="button" id="tabCreateBtn" onclick="switchPromoteTab('create')" class="flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all text-slate-500 hover:text-slate-800">สร้างตัวแทนใหม่</button>
                        </div>
                        
                        <div id="modePromoteSection" class="space-y-3">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">เลือกสมาชิกในระบบ</label>
                                <select id="swalUserId" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 bg-white">
                                    ${optionsHtml ? optionsHtml : '<option value="">ไม่มีสมาชิกทั่วไปที่สามารถเลื่อนขั้นได้</option>'}
                                </select>
                                <p class="text-xs text-slate-400 mt-1">สมาชิกที่เลือกจะได้รับสิทธิ์ตัวแทนจำหน่ายทันที</p>
                            </div>
                        </div>

                        <div id="modeCreateSection" class="space-y-3 hidden">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ชื่อผู้ใช้ใหม่ (Username)</label>
                                <input id="swalNewUser" type="text" placeholder="เช่น agent_pro" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">รหัสผ่าน (Password)</label>
                                <input id="swalNewPass" type="password" placeholder="ตั้งรหัสผ่าน 4 ตัวขึ้นไป" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700">ยอดเงินเริ่มต้น (บาท)</label>
                                <input id="swalNewBalance" type="number" step="0.01" min="0" placeholder="0.00" value="0" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    window.currentPromoteMode = 'promote';
                    window.switchPromoteTab = function(mode) {
                        window.currentPromoteMode = mode;
                        const pSec = document.getElementById('modePromoteSection');
                        const cSec = document.getElementById('modeCreateSection');
                        const pBtn = document.getElementById('tabPromoteBtn');
                        const cBtn = document.getElementById('tabCreateBtn');
                        if (mode === 'promote') {
                            pSec.classList.remove('hidden');
                            cSec.classList.add('hidden');
                            pBtn.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all bg-white shadow-sm text-indigo-600';
                            cBtn.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all text-slate-500 hover:text-slate-800';
                        } else {
                            pSec.classList.add('hidden');
                            cSec.classList.remove('hidden');
                            cBtn.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all bg-white shadow-sm text-indigo-600';
                            pBtn.className = 'flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all text-slate-500 hover:text-slate-800';
                        }
                    };
                },
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    if (window.currentPromoteMode === 'promote') {
                        const selectedId = document.getElementById('swalUserId').value;
                        if (!selectedId) {
                            Swal.showValidationMessage('กรุณาเลือกสมาชิกในระบบ หรือเปลี่ยนไปแท็บสร้างตัวแทนใหม่');
                            return false;
                        }
                        return { type: 'promote', user_id: parseInt(selectedId) };
                    } else {
                        const newUser = document.getElementById('swalNewUser').value.trim();
                        const newPass = document.getElementById('swalNewPass').value.trim();
                        const newBal = parseFloat(document.getElementById('swalNewBalance').value || '0');
                        if (!newUser || newUser.length < 3) {
                            Swal.showValidationMessage('ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร');
                            return false;
                        }
                        if (!newPass || newPass.length < 4) {
                            Swal.showValidationMessage('รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร');
                            return false;
                        }
                        return { type: 'create', username: newUser, password: newPass, initial_balance: isNaN(newBal) ? 0 : newBal };
                    }
                }
            });

            if (formValues) {
                try {
                    let payload = (formValues.type === 'create') 
                        ? { action: 'create_reseller', username: formValues.username, password: formValues.password, initial_balance: formValues.initial_balance }
                        : { action: 'promote', user_id: formValues.user_id };

                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadResellers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถดำเนินการได้', 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function demoteReseller(userId, username) {
            const confirm = await Swal.fire({
                title: `ยืนยันยกเลิกตัวแทน?`,
                text: `คุณต้องการปรับสถานะ "${username}" กลับเป็นสมาชิกทั่วไปหรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e11d48'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'demote', user_id: userId })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadResellers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถยกเลิกสถานะตัวแทนได้', 'error');
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

        document.addEventListener('DOMContentLoaded', () => loadResellers());
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
