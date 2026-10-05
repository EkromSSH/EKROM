<?php
require_once __DIR__ . '/api/db.php';
$siteSettings = get_site_settings();
$siteName = htmlspecialchars($siteSettings['site_name'] ?: 'EKROM');
$siteLogo = htmlspecialchars($siteSettings['site_logo'] ?? '');
$siteInitial = htmlspecialchars(mb_substr($siteSettings['site_name'] ?: 'EKROM', 0, 2));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>ยอดขายตัวแทน - <?= $siteName ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css?v=20260926_5">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* SweetAlert Resellers Modal Mobile Optimization */
        .swal-reseller-container {
            -webkit-overflow-scrolling: touch !important;
            scroll-behavior: smooth;
        }
        @media (max-width: 768px) {
            .swal-reseller-container {
                align-items: flex-start !important;
                overflow-y: auto !important;
                padding-top: max(1rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) !important;
                padding-bottom: max(18rem, 50vh) !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .swal-reseller-popup {
                width: 100% !important;
                max-width: min(94vw, 460px) !important;
                margin: 0 auto !important;
                border-radius: 1.5rem !important;
                padding: 1.25rem 1rem !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25) !important;
            }
            .swal-reseller-popup .swal2-title {
                font-size: 1.25rem !important;
                padding: 0 0 0.75rem 0 !important;
            }
            .swal-reseller-popup .swal2-actions {
                margin-top: 1.25rem !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }
            .swal-reseller-popup .swal2-actions button {
                flex: 1 !important;
                padding: 0.75rem 1rem !important;
                font-size: 0.95rem !important;
                border-radius: 0.75rem !important;
                margin: 0 !important;
            }
            .swal-reseller-html {
                padding: 0 !important;
                margin: 0.25rem 0 0 0 !important;
                overflow: visible !important;
            }
        }
        .swal-reseller-popup input,
        .swal-reseller-popup select {
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
            <div class="w-8 h-8 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden">
                <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
            </div>
            <span class="font-bold text-lg tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
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
                    <div class="drawer-logo w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden">
                        <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
                    </div>
                    <span class="drawer-title font-bold text-xl tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
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
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden">
                <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
            </div>
            <span class="font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ยอดขายตัวแทนจำหน่าย 🤝</h1>
                <p class="text-gray-500 mt-1 text-sm">ตรวจสอบรายชื่อตัวแทน ยอดขาย เครดิตคงเหลือ และประวัติการทำรายการ</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <button onclick="openEditDiscountModal()" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>🏷️</span> ส่วนลดกลางระบบ: <span id="headerDiscountVal">30%</span>
                </button>
                <button onclick="openPromoteModal()" class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                    <span>➕</span> แต่งตั้งตัวแทนใหม่
                </button>
                <button onclick="loadResellers(this)" class="bg-white border border-slate-200 hover:bg-slate-50 active:scale-95 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
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
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold shrink-0">🏷️</div>
                    <div>
                        <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ส่วนลดกลางระบบ (Default)</p>
                        <h3 class="text-xl md:text-2xl font-bold text-amber-600" id="statDiscount">30%</h3>
                    </div>
                </div>
                <button onclick="openEditDiscountModal()" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer shadow-2xs" title="คลิกเพื่อปรับเปลี่ยนเปอร์เซ็นต์ส่วนลดกลางของระบบ">
                    ⚙️ ปรับลด
                </button>
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
                            <th class="py-3 px-4 text-center">ส่วนลดตัวแทน (%)</th>
                            <th class="py-3 px-4">VPN ทั้งหมด</th>
                            <th class="py-3 px-4">VPN ใช้งานอยู่</th>
                            <th class="py-3 px-4">วันที่สมัคร</th>
                            <th class="py-3 px-4 text-right">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody id="resellerTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">กำลังโหลดข้อมูลตัวแทน...</td></tr>
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

                    const curDiscount = (data.reseller_discount_percent !== undefined) ? Number(data.reseller_discount_percent) : 30;
                    window.currentResellerDiscount = curDiscount;
                    window.systemDiscountPercent = (data.system_discount_percent !== undefined) ? Number(data.system_discount_percent) : curDiscount;
                    const discStr = (Math.round(curDiscount) === curDiscount) ? curDiscount : curDiscount.toFixed(1);
                    if (document.getElementById('statDiscount')) document.getElementById('statDiscount').innerText = discStr + '%';
                    if (document.getElementById('headerDiscountVal')) document.getElementById('headerDiscountVal').innerText = discStr + '%';

                    renderResellers(resellersData);
                    renderOrders(data.recent_orders || []);
                    window.eligibleUsers = data.eligible_users || [];

                    if (isButton) {
                        Toast.fire({ icon: 'success', title: 'รีเฟรชข้อมูลตัวแทนแล้ว' });
                    }
                } else {
                    document.getElementById('resellerTableBody').innerHTML = `<tr><td colspan="8" class="py-8 text-center text-red-500">${escapeHtml(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล')}</td></tr>`;
                }
            } catch (e) {
                console.error(e);
                document.getElementById('resellerTableBody').innerHTML = '<tr><td colspan="8" class="py-8 text-center text-red-500">การเชื่อมต่อขัดข้อง ไม่สามารถโหลดข้อมูลได้</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (isButton) btn.disabled = false;
            }
        }

        function renderResellers(list) {
            const tbody = document.getElementById('resellerTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-gray-400">ยังไม่มีตัวแทนจำหน่ายในระบบ</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(r => {
                const hasCustom = (r.reseller_discount_percent !== null && r.reseller_discount_percent !== undefined);
                const sysPct = window.currentResellerDiscount !== undefined ? window.currentResellerDiscount : 30;
                const effDisc = Number(r.effective_discount_percent !== undefined ? r.effective_discount_percent : (hasCustom ? r.reseller_discount_percent : sysPct));
                const effDiscStr = (Math.round(effDisc) === effDisc) ? effDisc : effDisc.toFixed(1);
                const customPercentArg = hasCustom ? Number(r.reseller_discount_percent) : 'null';

                let discountBadge = '';
                if (hasCustom) {
                    discountBadge = `
                        <button onclick="openEditUserDiscountModal(${r.id}, '${escapeHtml(r.username)}', ${customPercentArg})" 
                                class="group inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-full font-bold text-xs shadow-2xs transition-all cursor-pointer"
                                title="คลิกเพื่อปรับส่วนลดเฉพาะคนของ ${escapeHtml(r.username)} (กำหนดเอง ${effDiscStr}%)">
                            <span class="text-amber-600">🏷️</span>
                            <span>${effDiscStr}%</span>
                            <span class="text-[9px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded-full font-bold">เฉพาะคน</span>
                            <span class="text-[11px] opacity-70 group-hover:opacity-100 transition-opacity">✏️</span>
                        </button>
                    `;
                } else {
                    discountBadge = `
                        <button onclick="openEditUserDiscountModal(${r.id}, '${escapeHtml(r.username)}', null)" 
                                class="group inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-200 hover:border-amber-300 rounded-full font-bold text-xs transition-all cursor-pointer"
                                title="คลิกเพื่อกำหนดส่วนลดเฉพาะคนของ ${escapeHtml(r.username)} (ปัจจุบันใช้ค่ากลางระบบ ${effDiscStr}%)">
                            <span class="text-slate-400 group-hover:text-amber-600">🏷️</span>
                            <span>${effDiscStr}%</span>
                            <span class="text-[9px] text-slate-400 font-normal">(ตามระบบ)</span>
                            <span class="text-[11px] opacity-0 group-hover:opacity-100 transition-opacity">✏️</span>
                        </button>
                    `;
                }

                const isLine = Boolean(r.is_line_user || r.line_user_id || r.line_display_name || (r.username && r.username.startsWith('line_')));
                const lineName = r.line_display_name ? escapeHtml(r.line_display_name) : '';
                const uname = escapeHtml(r.username);
                const picUrl = r.line_picture_url ? escapeHtml(r.line_picture_url) : '';

                let resellerColHtml = '';
                if (isLine) {
                    resellerColHtml = `
                    <div class="flex items-center gap-2.5">
                        <div class="relative shrink-0">
                            ${picUrl ? `<img src="${picUrl}" class="w-8 h-8 rounded-full object-cover border border-emerald-400 shadow-xs" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('${lineName || 'LINE'}') + '&background=06c755&color=fff';">` : `<div class="w-8 h-8 rounded-full bg-[#06c755] text-white flex items-center justify-center font-bold text-xs shadow-xs">💬</div>`}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 flex items-center gap-1">
                                <span>${lineName || uname}</span>
                                <span class="text-[9px] bg-[#06c755]/10 text-[#059b43] border border-[#06c755]/30 px-1 rounded font-bold">LINE</span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">${uname}</div>
                        </div>
                    </div>
                    `;
                } else {
                    resellerColHtml = `
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">🤝</div>
                        <span class="font-bold text-slate-800">${uname}</span>
                    </div>
                    `;
                }

                return `
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="py-3.5 px-4 font-mono text-xs text-gray-400">#${r.id}</td>
                    <td class="py-3.5 px-4">${resellerColHtml}</td>
                    <td class="py-3.5 px-4 font-bold text-emerald-600">฿${parseFloat(r.balance).toFixed(2)}</td>
                    <td class="py-3.5 px-4 text-center">${discountBadge}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-700">${r.total_vpns} เครื่อง</td>
                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-full font-bold text-xs">${r.active_vpns || 0} กำลังใช้งาน</span></td>
                    <td class="py-3.5 px-4 text-xs text-gray-400">${r.created_at || '--'}</td>
                    <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                        <button onclick="openAdjustBalance(${r.id}, '${escapeHtml(r.username)}', ${r.balance})" class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1 cursor-pointer">💰 เติม/หักเงิน</button>
                        <button onclick="openEditUserDiscountModal(${r.id}, '${escapeHtml(r.username)}', ${customPercentArg})" class="px-2.5 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1 cursor-pointer" title="ตั้งค่าเปอร์เซ็นต์ส่วนลดเฉพาะตัวแทนนี้">🏷️ ปรับ %</button>
                        <button onclick="openResetPassword(${r.id}, '${escapeHtml(r.username)}')" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1 cursor-pointer">🔑 รหัสผ่าน</button>
                        <button onclick="demoteReseller(${r.id}, '${escapeHtml(r.username)}')" class="px-2.5 py-1.5 bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 rounded-lg font-bold text-xs transition-all inline-flex items-center gap-1 cursor-pointer">ปลดตัวแทน</button>
                    </td>
                </tr>
                `;
            }).join('');
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
            const q = document.getElementById('searchReseller').value.toLowerCase().trim();
            const filtered = resellersData.filter(r => {
                const uName = (r.username || '').toLowerCase();
                const lName = (r.line_display_name || '').toLowerCase();
                const idStr = String(r.id || '');
                return uName.includes(q) || lName.includes(q) || idStr === q || ('#' + idStr) === q;
            });
            renderResellers(filtered);
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

        async function openAdjustBalance(userId, username, currentBalance) {
            const { value: formValues } = await Swal.fire({
                title: `💰 ปรับยอดเงิน: ${escapeHtml(username)}`,
                customClass: {
                    container: 'swal-reseller-container',
                    popup: 'swal-reseller-popup',
                    htmlContainer: 'swal-reseller-html'
                },
                html: `
                    <div class="text-left text-sm space-y-3">
                        <p class="text-gray-500 text-xs sm:text-sm">ยอดเงินปัจจุบัน: <strong class="text-emerald-600 font-bold">฿${parseFloat(currentBalance).toFixed(2)}</strong></p>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">จำนวนเงิน (บาท)</label>
                            <input id="swalAmount" type="number" step="0.01" placeholder="เช่น 100 หรือ -50" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 !text-base focus:outline-none focus:border-indigo-500">
                            <p class="text-[11px] text-gray-400 mt-1">ใส่ค่าบวกเพื่อเพิ่มยอด หรือใส่ค่าลบ (-) เพื่อหักเงิน</p>
                        </div>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">หมายเหตุ</label>
                            <input id="swalNote" type="text" placeholder="เช่น เติมเครดิตตัวแทน, คืนเงิน" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 !text-base focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                },
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
                customClass: {
                    container: 'swal-reseller-container',
                    popup: 'swal-reseller-popup',
                    htmlContainer: 'swal-reseller-html'
                },
                input: 'password',
                inputLabel: 'กำหนดรหัสผ่านใหม่',
                inputPlaceholder: 'อย่างน้อย 4 ตัวอักษร',
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                },
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
            const sysDiscount = window.systemDiscountPercent !== undefined ? window.systemDiscountPercent : (window.currentResellerDiscount !== undefined ? window.currentResellerDiscount : 30);
            const sysDiscountStr = (Math.round(sysDiscount) === sysDiscount) ? sysDiscount : sysDiscount.toFixed(1);
            let optionsHtml = users.map(u => {
                const isLine = Boolean(u.is_line_user || u.line_user_id || u.line_display_name);
                const title = u.line_display_name ? `${escapeHtml(u.line_display_name)} [LINE: ${escapeHtml(u.username)}]` : escapeHtml(u.username);
                return `<option value="${u.id}">${title} (#${u.id})</option>`;
            }).join('');

            const { value: formValues } = await Swal.fire({
                title: '➕ แต่งตั้งตัวแทนใหม่',
                customClass: {
                    container: 'swal-reseller-container',
                    popup: 'swal-reseller-popup',
                    htmlContainer: 'swal-reseller-html'
                },
                html: `
                    <div class="text-left text-sm space-y-4">
                        <div class="flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                            <button type="button" id="tabPromoteBtn" onclick="switchPromoteTab('promote')" class="flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all bg-white shadow-sm text-indigo-600">เลื่อนขั้นสมาชิกเดิม</button>
                            <button type="button" id="tabCreateBtn" onclick="switchPromoteTab('create')" class="flex-1 py-1.5 px-3 rounded-lg font-bold text-xs transition-all text-slate-500 hover:text-slate-800">สร้างตัวแทนใหม่</button>
                        </div>
                        
                        <div id="modePromoteSection" class="space-y-3">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">เลือกสมาชิกในระบบ</label>
                                <select id="swalUserId" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500 bg-white">
                                    ${optionsHtml ? optionsHtml : '<option value="">ไม่มีสมาชิกทั่วไปที่สามารถเลื่อนขั้นได้</option>'}
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">สมาชิกที่เลือกจะได้รับสิทธิ์ตัวแทนจำหน่ายทันที</p>
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">เปอร์เซ็นต์ส่วนลด (%) <span class="font-normal text-slate-400 text-xs">(กำหนดเฉพาะคน)</span></label>
                                <div class="relative">
                                    <input id="swalPromotePercent" type="number" min="0" max="100" step="1" placeholder="ค่าเริ่มต้นระบบ (${sysDiscountStr}%)" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500 pr-10">
                                    <span class="absolute right-3 top-2.5 font-bold text-slate-400 text-base">%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">เว้นว่างไว้เพื่อใช้ค่าเริ่มต้นระบบ (${sysDiscountStr}%) หรือระบุ % เฉพาะคนนี้</p>
                            </div>
                        </div>

                        <div id="modeCreateSection" class="space-y-3 hidden">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ชื่อผู้ใช้ใหม่ (Username)</label>
                                <input id="swalNewUser" type="text" placeholder="เช่น agent_pro" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">รหัสผ่าน (Password)</label>
                                <input id="swalNewPass" type="password" placeholder="ตั้งรหัสผ่าน 4 ตัวขึ้นไป" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">ยอดเงินเริ่มต้น (บาท)</label>
                                <input id="swalNewBalance" type="number" step="0.01" min="0" placeholder="0.00" value="0" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">เปอร์เซ็นต์ส่วนลด (%) <span class="font-normal text-slate-400 text-xs">(กำหนดเฉพาะคน)</span></label>
                                <div class="relative">
                                    <input id="swalCreatePercent" type="number" min="0" max="100" step="1" placeholder="ค่าเริ่มต้นระบบ (${sysDiscountStr}%)" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm !text-base focus:outline-none focus:border-indigo-500 pr-10">
                                    <span class="absolute right-3 top-2.5 font-bold text-slate-400 text-base">%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">เว้นว่างไว้เพื่อใช้ค่าเริ่มต้นระบบ (${sysDiscountStr}%) หรือระบุ % เฉพาะคนนี้</p>
                            </div>
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
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
                        const pDisc = document.getElementById('swalPromotePercent').value.trim();
                        let discVal = null;
                        if (pDisc !== '') {
                            discVal = parseFloat(pDisc);
                            if (isNaN(discVal) || discVal < 0 || discVal > 100) {
                                Swal.showValidationMessage('เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100%');
                                return false;
                            }
                        }
                        return { type: 'promote', user_id: parseInt(selectedId), discount_percent: discVal };
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
                        const cDisc = document.getElementById('swalCreatePercent').value.trim();
                        let discVal = null;
                        if (cDisc !== '') {
                            discVal = parseFloat(cDisc);
                            if (isNaN(discVal) || discVal < 0 || discVal > 100) {
                                Swal.showValidationMessage('เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100%');
                                return false;
                            }
                        }
                        return { type: 'create', username: newUser, password: newPass, initial_balance: isNaN(newBal) ? 0 : newBal, discount_percent: discVal };
                    }
                }
            });

            if (formValues) {
                try {
                    let payload = (formValues.type === 'create') 
                        ? { action: 'create_reseller', username: formValues.username, password: formValues.password, initial_balance: formValues.initial_balance, discount_percent: formValues.discount_percent }
                        : { action: 'promote', user_id: formValues.user_id, discount_percent: formValues.discount_percent };

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

        async function openEditUserDiscountModal(userId, username, currentCustomPercent) {
            const sysDiscount = window.systemDiscountPercent !== undefined ? window.systemDiscountPercent : (window.currentResellerDiscount !== undefined ? window.currentResellerDiscount : 30);
            const sysDiscountStr = (Math.round(sysDiscount) === sysDiscount) ? sysDiscount : sysDiscount.toFixed(1);
            const hasCustom = (currentCustomPercent !== null && currentCustomPercent !== undefined && !isNaN(currentCustomPercent));
            const initialPercent = hasCustom ? Number(currentCustomPercent) : sysDiscount;
            const initialPercentStr = (Math.round(initialPercent) === initialPercent) ? initialPercent : initialPercent.toFixed(1);

            const { value: formResult } = await Swal.fire({
                title: `🏷️ ตั้งค่าส่วนลด: ${escapeHtml(username)}`,
                customClass: {
                    container: 'swal-reseller-container',
                    popup: 'swal-reseller-popup',
                    htmlContainer: 'swal-reseller-html'
                },
                html: `
                    <div class="text-left text-sm space-y-4">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 font-semibold">ชื่อตัวแทน</p>
                                <h4 class="font-bold text-slate-800 text-base flex items-center gap-1.5">
                                    <span>🤝</span> ${escapeHtml(username)}
                                </h4>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 font-semibold">ค่าเริ่มต้นกลางของระบบ</p>
                                <h4 class="font-bold text-amber-600 text-base">${sysDiscountStr}%</h4>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold mb-1.5 text-slate-700 text-xs sm:text-sm">เลือกรูปแบบเปอร์เซ็นต์ส่วนลด:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" id="btnModeDefault" onclick="switchUserDiscountMode('default')" 
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                    <span>⚙️ ตามค่าเริ่มต้นระบบ</span>
                                    <span class="text-[11px] opacity-80">(${sysDiscountStr}%)</span>
                                </button>
                                <button type="button" id="btnModeCustom" onclick="switchUserDiscountMode('custom')" 
                                    class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                    <span>✨ กำหนดเองเฉพาะคน</span>
                                    <span class="text-[11px] opacity-80">ระบุ % สำหรับยูเซอร์นี้</span>
                                </button>
                            </div>
                        </div>

                        <div id="userCustomInputSection" class="space-y-2.5">
                            <div>
                                <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">เปอร์เซ็นต์ส่วนลดที่ยูเซอร์นี้จะได้รับ (%)</label>
                                <div class="relative">
                                    <input id="swalUserDiscountPercent" type="number" min="0" max="100" step="1" value="${initialPercentStr}" 
                                        class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-base font-bold text-slate-800 focus:outline-none focus:border-amber-500 pr-10" 
                                        oninput="updateUserDiscountCalcPreview(this.value, false)">
                                    <span class="absolute right-3 top-2.5 font-bold text-slate-400 text-base">%</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ตัวเลือกเปอร์เซ็นต์ด่วน:</label>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button type="button" onclick="setUserDiscountPreset(10)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">10%</button>
                                    <button type="button" onclick="setUserDiscountPreset(20)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">20%</button>
                                    <button type="button" onclick="setUserDiscountPreset(25)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">25%</button>
                                    <button type="button" onclick="setUserDiscountPreset(30)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">30%</button>
                                    <button type="button" onclick="setUserDiscountPreset(35)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">35%</button>
                                    <button type="button" onclick="setUserDiscountPreset(40)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">40%</button>
                                    <button type="button" onclick="setUserDiscountPreset(50)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">50%</button>
                                    <button type="button" onclick="setUserDiscountPreset(70)" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">70%</button>
                                </div>
                            </div>
                        </div>

                        <!-- ตัวอย่างการคำนวณราคาทุน -->
                        <div id="swalUserDiscountCalcPreview" class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-3 text-xs space-y-1 text-amber-950">
                            <!-- Dynamic preview -->
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                    let currentMode = hasCustom ? 'custom' : 'default';

                    window.updateUserDiscountCalcPreview = function(val, isDefault) {
                        const num = Math.max(0, Math.min(100, parseFloat(val) || 0));
                        const p30 = (30 * (100 - num) / 100).toFixed(2);
                        const p50 = (50 * (100 - num) / 100).toFixed(2);
                        const p100 = (100 * (100 - num) / 100).toFixed(2);
                        const labelMode = isDefault ? `ค่าเริ่มต้นระบบ (${sysDiscountStr}%)` : `กำหนดเอง (${num}%)`;
                        const box = document.getElementById('swalUserDiscountCalcPreview');
                        if (box) {
                            box.innerHTML = `
                                <div class="font-bold flex items-center justify-between">
                                    <span class="flex items-center gap-1"><span>💡</span> ตัวอย่างราคาทุนที่ตัวแทนนี้จะจ่าย:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${isDefault ? 'bg-slate-200 text-slate-700' : 'bg-amber-200 text-amber-900'}">${labelMode}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 pt-1"><span>ราคาปกติ ฿30.00 (7 วัน):</span> <b class="text-amber-800">ตัวแทนจ่าย ฿${p30} (ลด ฿${(30 - p30).toFixed(2)})</b></div>
                                <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿50.00 (15 วัน):</span> <b class="text-amber-800">ตัวแทนจ่าย ฿${p50} (ลด ฿${(50 - p50).toFixed(2)})</b></div>
                                <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿100.00 (30 วัน):</span> <b class="text-amber-800">ตัวแทนจ่าย ฿${p100} (ลด ฿${(100 - p100).toFixed(2)})</b></div>
                            `;
                        }
                    };

                    window.switchUserDiscountMode = function(mode) {
                        currentMode = mode;
                        const btnDef = document.getElementById('btnModeDefault');
                        const btnCust = document.getElementById('btnModeCustom');
                        const inputSec = document.getElementById('userCustomInputSection');
                        const inputEl = document.getElementById('swalUserDiscountPercent');

                        if (mode === 'default') {
                            btnDef.className = 'py-2.5 px-3 rounded-xl border border-amber-500 bg-amber-500 text-white shadow-sm text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer';
                            btnCust.className = 'py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer';
                            inputSec.classList.add('opacity-40', 'pointer-events-none');
                            window.updateUserDiscountCalcPreview(sysDiscount, true);
                        } else {
                            btnCust.className = 'py-2.5 px-3 rounded-xl border border-amber-500 bg-amber-500 text-white shadow-sm text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer';
                            btnDef.className = 'py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-0.5 cursor-pointer';
                            inputSec.classList.remove('opacity-40', 'pointer-events-none');
                            window.updateUserDiscountCalcPreview(inputEl ? inputEl.value : initialPercent, false);
                        }
                    };

                    window.setUserDiscountPreset = function(percent) {
                        const inputEl = document.getElementById('swalUserDiscountPercent');
                        if (inputEl) inputEl.value = percent;
                        window.switchUserDiscountMode('custom');
                    };

                    window.switchUserDiscountMode(currentMode);
                    window.userDiscountCurrentModeGetter = () => currentMode;
                },
                showCancelButton: true,
                confirmButtonText: '💾 บันทึกเปอร์เซ็นต์',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b',
                preConfirm: () => {
                    const mode = (typeof window.userDiscountCurrentModeGetter === 'function') ? window.userDiscountCurrentModeGetter() : 'custom';
                    if (mode === 'default') {
                        return { discount_percent: null };
                    }
                    const input = document.getElementById('swalUserDiscountPercent');
                    const val = parseFloat(input ? input.value : '');
                    if (isNaN(val) || val < 0 || val > 100) {
                        Swal.showValidationMessage('กรุณากรอกเปอร์เซ็นต์ส่วนลดระหว่าง 0 ถึง 100%');
                        return false;
                    }
                    return { discount_percent: val };
                }
            });

            if (formResult !== undefined) {
                try {
                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            action: 'save_user_discount', 
                            user_id: userId, 
                            discount_percent: formResult.discount_percent 
                        })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadResellers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'บันทึกไม่สำเร็จ', 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openEditDiscountModal() {
            const currentDiscount = window.currentResellerDiscount !== undefined ? window.currentResellerDiscount : 30;
            const { value: newPercent } = await Swal.fire({
                title: '🏷️ ตั้งค่าส่วนลดกลางของระบบ (Global)',
                customClass: {
                    container: 'swal-reseller-container',
                    popup: 'swal-reseller-popup',
                    htmlContainer: 'swal-reseller-html'
                },
                html: `
                    <div class="text-left text-sm space-y-3">
                        <p class="text-xs text-slate-500">กำหนดเปอร์เซ็นต์ส่วนลดเริ่มต้นสำหรับตัวแทนจำหน่ายทุกคน (ตัวแทนที่ไม่ได้ตั้งค่าเฉพาะคน จะได้รับส่วนลดตามค่าเริ่มต้นนี้)</p>
                        <div>
                            <label class="block font-bold mb-1 text-slate-700 text-xs sm:text-sm">เปอร์เซ็นต์ส่วนลดกลางระบบ (%)</label>
                            <div class="relative">
                                <input id="swalDiscountPercent" type="number" min="0" max="100" step="1" value="${currentDiscount}" 
                                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-base font-bold text-slate-800 focus:outline-none focus:border-amber-500 pr-10" 
                                    oninput="updateDiscountPreview(this.value)">
                                <span class="absolute right-3 top-2.5 font-bold text-slate-400 text-base">%</span>
                            </div>
                        </div>

                        <!-- ตัวเลือกด่วน -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">เลือกรวดเร็ว:</label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" onclick="document.getElementById('swalDiscountPercent').value=10; updateDiscountPreview(10);" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">10%</button>
                                <button type="button" onclick="document.getElementById('swalDiscountPercent').value=20; updateDiscountPreview(20);" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">20%</button>
                                <button type="button" onclick="document.getElementById('swalDiscountPercent').value=30; updateDiscountPreview(30);" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">30% (เดิม)</button>
                                <button type="button" onclick="document.getElementById('swalDiscountPercent').value=40; updateDiscountPreview(40);" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">40%</button>
                                <button type="button" onclick="document.getElementById('swalDiscountPercent').value=50; updateDiscountPreview(50);" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-800 transition-all cursor-pointer">50%</button>
                            </div>
                        </div>

                        <!-- ตัวอย่างการคำนวณ -->
                        <div id="swalDiscountCalcPreview" class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3 text-xs space-y-1 text-amber-900">
                            <div class="font-bold flex items-center gap-1"><span>💡</span> ตัวอย่างการคำนวณราคาทุนตัวแทน:</div>
                            <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿50.00:</span> <b class="text-amber-700">ตัวแทนจ่าย ฿${(50 * (100 - currentDiscount) / 100).toFixed(2)} (ประหยัด ฿${(50 * currentDiscount / 100).toFixed(2)})</b></div>
                            <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿80.00:</span> <b class="text-amber-700">ตัวแทนจ่าย ฿${(80 * (100 - currentDiscount) / 100).toFixed(2)} (ประหยัด ฿${(80 * currentDiscount / 100).toFixed(2)})</b></div>
                        </div>
                    </div>
                `,
                didOpen: (popup) => {
                    setupSwalMobileKeyboardScroll(popup);
                    window.updateDiscountPreview = function(val) {
                        const num = Math.max(0, Math.min(100, parseFloat(val) || 0));
                        const p50 = (50 * (100 - num) / 100).toFixed(2);
                        const p80 = (80 * (100 - num) / 100).toFixed(2);
                        const box = document.getElementById('swalDiscountCalcPreview');
                        if (box) {
                            box.innerHTML = `
                                <div class="font-bold flex items-center gap-1"><span>💡</span> ตัวอย่างการคำนวณราคาทุนตัวแทน:</div>
                                <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿50.00:</span> <b class="text-amber-700">ตัวแทนจ่าย ฿${p50} (ประหยัด ฿${(50 - p50).toFixed(2)})</b></div>
                                <div class="flex justify-between text-slate-600"><span>ราคาปกติ ฿80.00:</span> <b class="text-amber-700">ตัวแทนจ่าย ฿${p80} (ประหยัด ฿${(80 - p80).toFixed(2)})</b></div>
                            `;
                        }
                    };
                },
                showCancelButton: true,
                confirmButtonText: '💾 บันทึกเปอร์เซ็นต์',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b',
                preConfirm: () => {
                    const input = document.getElementById('swalDiscountPercent');
                    const val = parseFloat(input ? input.value : '');
                    if (isNaN(val) || val < 0 || val > 100) {
                        Swal.showValidationMessage('กรุณากรอกเปอร์เซ็นต์ส่วนลดระหว่าง 0 ถึง 100%');
                        return false;
                    }
                    return val;
                }
            });

            if (newPercent !== undefined) {
                try {
                    const res = await fetch('api/admin_resellers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'save_discount', percent: newPercent })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadResellers();
                    } else {
                        Swal.fire('ผิดพลาด', data.message || 'บันทึกไม่สำเร็จ', 'error');
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
</body>
</html>
