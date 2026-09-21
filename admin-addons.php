<?php
require_once __DIR__ . '/api/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, interactive-widget=resizes-content">
    <title>จัดการโปรเสริม - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="mobile-fix.css">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        th, td { white-space: nowrap; }

        /* SweetAlert Addon Modal Smooth Scrolling & Touch Fix */
        body.admin-shell.swal2-shown {
            position: static !important;
            overflow: hidden !important;
        }
        html.swal2-shown {
            overflow: hidden !important;
        }
        .swal2-container {
            -webkit-overflow-scrolling: touch !important;
            padding: 0.5rem !important;
        }
        @media (max-width: 640px) {
            .swal2-container {
                align-items: flex-start !important;
                padding: max(0.5rem, env(safe-area-inset-top, 0px)) 0.25rem 0.5rem !important;
            }
        }
        .addon-custom-modal {
            max-height: 92vh !important;
            max-height: 92dvh !important;
            display: flex !important;
            flex-direction: column !important;
            padding: 1.25rem 1.25rem 0.75rem !important;
            border-radius: 1.5rem !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            width: 100% !important;
            max-width: 760px !important;
        }
        @media (max-width: 640px) {
            .addon-custom-modal {
                padding: 0.875rem 0.75rem 0.75rem !important;
                max-height: calc(100dvh - 0.75rem) !important;
                margin-top: 0.25rem !important;
                width: 98% !important;
                border-radius: 1.25rem !important;
            }
        }
        .addon-custom-modal .swal2-title {
            flex-shrink: 0 !important;
            padding: 0 0 0.5rem 0 !important;
            margin: 0 !important;
            font-size: 1.125rem !important;
            line-height: 1.4 !important;
            width: 100% !important;
            text-align: center !important;
        }
        .addon-custom-modal .swal2-html-container {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 4px 10rem 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-y: contain !important;
            scroll-behavior: smooth !important;
            touch-action: pan-y !important;
            display: flex !important;
            flex-direction: column !important;
            text-align: left !important;
            width: 100% !important;
        }
        @media (max-width: 640px) {
            .addon-custom-modal .swal2-html-container {
                padding: 0 4px 14rem 0 !important;
            }
        }
        .addon-custom-modal .swal2-actions {
            flex-shrink: 0 !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0 !important;
            padding: 0.5rem 0 0 0 !important;
            border-top: 1px solid #f1f5f9 !important;
            background: #ffffff !important;
            gap: 0.5rem !important;
            width: 100% !important;
            justify-content: flex-end !important;
        }
        @media (max-width: 640px) {
            .addon-custom-modal .swal2-actions {
                display: flex !important;
                flex-direction: row !important;
                gap: 0.5rem !important;
            }
            .addon-custom-modal .swal2-actions button {
                flex: 1 !important;
                margin: 0 !important;
                padding: 0.625rem 0.5rem !important;
                font-size: 0.8125rem !important;
            }
        }
        /* Touch action and smooth scrollbar */
        .addon-custom-modal .swal2-html-container input,
        .addon-custom-modal .swal2-html-container textarea,
        .addon-custom-modal .swal2-html-container select {
            touch-action: pan-y !important;
        }
        .addon-custom-modal .swal2-html-container::-webkit-scrollbar {
            width: 6px;
        }
        .addon-custom-modal .swal2-html-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }
        .addon-custom-modal .swal2-html-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .addon-custom-modal .swal2-html-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📦 โปรเสริม</a>
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
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📦 โปรเสริม</a>
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
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการโปรเน็ตเสริม (Addons) 📦</h1>
                <p class="text-gray-500 mt-1 text-sm">เพิ่มและแก้ไขแพ็กเกจโปรเน็ตแนะนำสำหรับค่าย AIS, True, DTAC พร้อมรหัส USSD กดสมัคร</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateAddonModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> เพิ่มโปรเสริมใหม่
                </button>
                <button onclick="loadAddons(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">📦</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">แพ็กเกจโปรเสริมทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalAddons">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-2xl font-bold shrink-0">📶</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ค่ายเครือข่ายที่มีโปร</p>
                    <h3 class="text-xl md:text-2xl font-bold text-pink-600" id="statCarriers">0</h3>
                </div>
            </div>
        </div>

        <!-- Addons Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="text-emerald-600">📑</span> รายการโปรเสริมทั้งหมดในระบบ
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">กด "แก้ไข" เพื่อเปลี่ยนข้อความอธิบาย, คุณสมบัติ, คำเตือน, ราคา หรือรหัส USSD ได้ทุกแพ็กเกจ</p>
                </div>
                <!-- Filters & Search -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-bold flex-wrap gap-1" id="carrierFilterGroup">
                        <button type="button" onclick="setCarrierFilter('all')" data-carrier="all" class="carrier-filter-btn px-3 py-1.5 rounded-lg bg-white shadow-sm text-slate-800 transition-all">ทั้งหมด</button>
                    </div>
                    <div>
                        <input type="text" id="addonSearch" oninput="handleSearch(this.value)" placeholder="🔍 ค้นหาโปร..." class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:bg-white focus:border-emerald-500 outline-none w-36 sm:w-44 transition-all">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4 w-32 sm:w-44">เครือข่าย</th>
                            <th class="py-3 px-4">ชื่อโปรโมชั่น</th>
                            <th class="py-3 px-4 text-right w-36 sm:w-44">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="addonsTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="3" class="py-8 text-center text-gray-400">กำลังโหลดโปรเสริม...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let addonsData = [];
        let currentCarrierFilter = 'all';
        let searchQuery = '';

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

        async function loadAddons(btn) {
            const isButton = btn && (btn instanceof Element || typeof btn.querySelector === 'function');
            const icon = isButton ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (isButton) btn.disabled = true;
            try {
                const res = await fetch('api/admin_addons.php?action=list', { cache: 'no-store' });
                const json = await res.json();
                if (json.status === 'success') {
                    addonsData = json.data || [];
                    document.getElementById('statTotalAddons').innerText = addonsData.length;
                    
                    const carrierSet = new Set(addonsData.map(a => a.carrier));
                    document.getElementById('statCarriers').innerText = carrierSet.size;

                    renderCarrierFilterButtons();
                    renderAddons(getFilteredAddons());

                    if (isButton) {
                        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชโปรเสริมแล้ว' });
                    }
                } else {
                    document.getElementById('addonsTableBody').innerHTML = `<tr><td colspan="3" class="py-8 text-center text-red-500">${escapeHtml(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล')}</td></tr>`;
                }
            } catch (e) {
                console.error(e);
                document.getElementById('addonsTableBody').innerHTML = '<tr><td colspan="3" class="py-8 text-center text-red-500">การเชื่อมต่อขัดข้อง ไม่สามารถโหลดข้อมูลได้</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (isButton) btn.disabled = false;
            }
        }

        function renderCarrierFilterButtons() {
            const group = document.getElementById('carrierFilterGroup');
            if (!group) return;
            const carriers = [...new Set(addonsData.map(a => (a.carrier || '').trim()).filter(Boolean))];
            const order = ['AIS', 'True', 'Dtac', 'NT Mobile'];
            carriers.sort((a, b) => {
                const ia = order.indexOf(a);
                const ib = order.indexOf(b);
                if (ia !== -1 && ib !== -1) return ia - ib;
                if (ia !== -1) return -1;
                if (ib !== -1) return 1;
                return a.localeCompare(b);
            });

            if (currentCarrierFilter !== 'all' && !carriers.some(c => c.toLowerCase() === currentCarrierFilter.toLowerCase())) {
                currentCarrierFilter = 'all';
            }

            let html = `<button type="button" onclick="setCarrierFilter('all')" data-carrier="all" class="carrier-filter-btn px-3 py-1.5 rounded-lg ${currentCarrierFilter === 'all' ? 'bg-white shadow-sm text-slate-800 font-bold' : 'text-slate-500 hover:text-slate-800'} transition-all">ทั้งหมด</button>`;
            carriers.forEach(c => {
                const isSel = currentCarrierFilter.toLowerCase() === c.toLowerCase();
                html += `<button type="button" onclick="setCarrierFilter('${escapeHtml(c)}')" data-carrier="${escapeHtml(c)}" class="carrier-filter-btn px-3 py-1.5 rounded-lg ${isSel ? 'bg-white shadow-sm text-slate-800 font-bold' : 'text-slate-500 hover:text-slate-800'} transition-all">📶 ${escapeHtml(c)}</button>`;
            });
            group.innerHTML = html;
        }

        function setCarrierFilter(carrier) {
            currentCarrierFilter = carrier;
            renderCarrierFilterButtons();
            renderAddons(getFilteredAddons());
        }

        function handleSearch(q) {
            searchQuery = (q || '').trim().toLowerCase();
            renderAddons(getFilteredAddons());
        }

        function getFilteredAddons() {
            return addonsData.filter(a => {
                if (currentCarrierFilter !== 'all') {
                    const c = (a.carrier || '').toLowerCase();
                    const f = currentCarrierFilter.toLowerCase();
                    if (!c.includes(f)) return false;
                }
                if (searchQuery) {
                    const matchText = [
                        a.title,
                        a.subtitle,
                        a.carrier,
                        a.description,
                        a.desc_html,
                        a.warning
                    ].filter(Boolean).join(' ').toLowerCase();
                    if (!matchText.includes(searchQuery)) return false;
                }
                return true;
            });
        }

        function countFeatures(html, fallbackDesc) {
            if (html && html.includes('<p>')) {
                const matches = html.match(/<p>/gi);
                return matches ? matches.length : 1;
            }
            if (fallbackDesc) {
                return fallbackDesc.split(/\r?\n/).filter(l => l.trim().length > 0).length || 1;
            }
            return 0;
        }

        function renderAddons(list) {
            const tbody = document.getElementById('addonsTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="3" class="py-12 text-center text-gray-400">ไม่พบแพ็กเกจโปรเสริมตามเงื่อนไขที่เลือก</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(a => {
                let badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                const lowerCarrier = (a.carrier || '').toLowerCase();
                if (lowerCarrier.includes('ais')) badgeClass = 'bg-green-50 text-green-700 border-green-200';
                else if (lowerCarrier.includes('true')) badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                else if (lowerCarrier.includes('dtac')) badgeClass = 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200';
                else if (lowerCarrier.includes('nt')) badgeClass = 'bg-yellow-50 text-yellow-800 border-yellow-200';

                return `
                    <tr class="hover:bg-slate-50/80 transition-all align-middle">
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold border ${badgeClass}">
                                📶 ${escapeHtml(a.carrier)}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 text-sm">${escapeHtml(a.title)}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                            <button onclick="openEditAddon(${a.id})" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-600 rounded-xl font-bold text-xs transition-all shadow-sm">✏️ แก้ไข</button>
                            <button onclick="deleteAddon(${a.id}, '${escapeHtml(a.title)}')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs transition-all">🗑️ ลบ</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Helper: แปลง desc_html เป็นข้อความบรรทัดต่อบรรทัดสำหรับแสดงใน Textarea
        function descHtmlToLines(html, fallbackDesc) {
            if (!html || !html.trim()) return fallbackDesc || '';
            let txt = html.trim();
            if (txt.startsWith('<div')) return txt;
            txt = txt.replace(/<\/p>\s*<p[^>]*>/gi, '\n');
            txt = txt.replace(/<br\s*[\/]?>/gi, '\n');
            txt = txt.replace(/<p[^>]*>/gi, '');
            txt = txt.replace(/<\/p>/gi, '');
            return txt.trim();
        }

        // Helper: แปลงข้อความบรรทัดต่อบรรทัดกลับเป็น <p>...</p>
        function linesToDescHtml(text) {
            if (!text || !text.trim()) return '';
            const trimmed = text.trim();
            if (trimmed.startsWith('<div') || trimmed.startsWith('<ul') || trimmed.startsWith('<ol')) {
                return trimmed;
            }
            const lines = trimmed.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
            if (!lines.length) return '';
            return lines.map(l => {
                if (l.startsWith('<p>') && l.endsWith('</p>')) return l;
                return `<p>${l}</p>`;
            }).join('');
        }

        // Helper: แปลง extra_html กลับเป็นข้อความธรรมดาหลายบรรทัดสำหรับแสดงในช่องแก้ไข
        function extraHtmlToLines(html) {
            if (!html || !html.trim()) return '';
            const trimmed = html.trim();
            if (!trimmed.startsWith('<')) return trimmed;

            try {
                const div = document.createElement('div');
                div.innerHTML = trimmed;

                const lines = [];
                div.querySelectorAll('p, ol, ul').forEach(el => {
                    const tag = el.tagName.toLowerCase();
                    if (tag === 'p') {
                        const text = el.textContent.trim();
                        if (text && !lines.includes(text)) lines.push(text);
                    } else if (tag === 'ol') {
                        let idx = 1;
                        el.querySelectorAll(':scope > li').forEach(li => {
                            const liText = li.textContent.trim();
                            if (liText) {
                                lines.push(`${idx}. ${liText}`);
                                idx++;
                            }
                        });
                    } else if (tag === 'ul') {
                        el.querySelectorAll(':scope > li').forEach(li => {
                            const liText = li.textContent.trim();
                            if (liText) lines.push(`• ${liText}`);
                        });
                    }
                });

                return lines.length ? lines.join('\n') : div.textContent.trim();
            } catch (e) {
                return trimmed;
            }
        }

        // Helper: แปลงข้อความดิบวิธีที่ 2 ให้เป็นกล่องสีส้มสวยงามอัตโนมัติ (Live Preview & Web View)
        function autoExtra(txt) {
            if (!txt || !txt.trim()) return '';
            if (txt.trim().startsWith('<div class="mt-4 p-4 bg-orange-50')) return txt;
            const lines = txt.trim().split(/\r?\n/);
            let out = '<div class="p-4 bg-orange-50 rounded-2xl border border-orange-200 shadow-sm text-left">';
            let inList = false;

            const formatLineWithLinks = (s) => {
                let clean = escapeHtml(s);
                clean = clean.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener" class="text-pink-600 font-bold underline hover:text-pink-700">$1</a>');
                clean = clean.replace(/(^|[^"'>])(topping\.truemoney\.com[^\s<]*)/g, '$1<a href="https://$2" target="_blank" rel="noopener" class="text-pink-600 font-bold underline hover:text-pink-700">$2</a>');
                return clean;
            };

            for (let ln of lines) {
                ln = ln.trim();
                if (!ln) continue;
                if (/^[━─=_-]{3,}$/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    out += '<hr class="my-2.5 border-orange-200/80">';
                    continue;
                }
                if (/^(🧧|📌|📲|🔥|🛡|▶)/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    out += `<p class="text-orange-700 font-bold text-sm mb-1">${formatLineWithLinks(ln)}</p>`;
                    continue;
                }
                if (/^(💡|⚠️)/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    const icon = ln.startsWith('💡') ? '💡' : '⚠️';
                    const clean = ln.replace(/^(💡|⚠️)\s*/, '');
                    out += `<div class="mt-2.5 p-2.5 bg-orange-100/80 rounded-xl text-orange-800 text-xs font-semibold leading-relaxed border border-orange-200/60 flex items-start gap-1.5"><span>${icon}</span><span>${formatLineWithLinks(clean)}</span></div>`;
                    continue;
                }
                if (/^\d+[\.\)]\s*/.test(ln)) {
                    if (!inList) {
                        out += '<ol class="text-gray-600 text-xs my-2 space-y-1.5 list-decimal list-inside font-medium leading-relaxed">';
                        inList = true;
                    }
                    const clean = ln.replace(/^\d+[\.\)]\s*/, '');
                    out += `<li>${formatLineWithLinks(clean)}</li>`;
                    continue;
                }
                if (inList) { out += '</ol>'; inList = false; }
                out += `<p class="text-gray-600 text-xs my-1 leading-relaxed">${formatLineWithLinks(ln)}</p>`;
            }
            if (inList) out += '</ol>';
            out += '</div>';
            return out;
        }

        // Helper: แทรกเทมเพลตมาตรฐาน
        window.insertDefaultDescTemplate = function() {
            const ta = document.getElementById('swalDescLines');
            if (!ta) return;
            const template = "📶 เซิร์ฟเวอร์ทั่วไป เดือนละ ฿50 (ไม่รวมโปรเสริม)\n♾️ ใช้งานได้ ไม่จำกัด GB\n📱 ใช้งานได้ 1-2 อุปกรณ์\n⚡ ความเร็วสูงสุด 20-100 Mbps";
            if (ta.value.trim() && !confirm('ต้องการแทนที่ข้อความเดิมด้วยเทมเพลตมาตรฐานหรือไม่?')) {
                return;
            }
            ta.value = template;
            window.updateDescPreview();
        };

        // Helper: แทรกเทมเพลต TrueMoney (วิธีที่ 2)
        window.insertTrueMoneyTemplate = function() {
            const ta = document.getElementById('swalExtraHtml');
            if (!ta) return;
            const template = `🧧 วิธีที่ 2 (สมัครในเว็บ TrueMoney)\n1. เติมเงินเข้าซิม 100฿\n2. คลิก topping.truemoney.com\n3. ผูกเบอร์ที่ใช้\n4. ไปที่หน้าเติมเน็ต\n5. ค้นหา "ROV"\n6. เลือก RoV Extreme 99บ. 30วัน\n7. หลังสมัครเปิด-ปิดเครื่องบิน 1 รอบ\n💡 แนะนำ: เล่น ROV ปิด VPN ก่อน`;
            if (ta.value.trim() && !confirm('ต้องการแทนที่ข้อความเดิมด้วยเทมเพลต TrueMoney หรือไม่?')) {
                return;
            }
            ta.value = template;
            window.updateExtraPreview();
        };

        // Helper: อัปเดตตัวอย่าง Live Preview คุณสมบัติ
        window.updateDescPreview = function() {
            const ta = document.getElementById('swalDescLines');
            const preview = document.getElementById('swalDescPreview');
            if (!ta || !preview) return;
            const html = linesToDescHtml(ta.value);
            if (!html) {
                preview.innerHTML = '<span class="text-[11px] text-gray-400 italic">พิมพ์ข้อความด้านบนเพื่อดูตัวอย่าง</span>';
            } else {
                preview.innerHTML = `<div class="bg-gray-50 rounded-xl p-3 text-xs space-y-1 text-gray-700 leading-relaxed border border-gray-200">${html}</div>`;
            }
        };

        // Helper: อัปเดตตัวอย่าง Live Preview กล่องสีส้มวิธีที่ 2
        window.updateExtraPreview = function() {
            const ta = document.getElementById('swalExtraHtml');
            const preview = document.getElementById('swalExtraPreview');
            if (!ta || !preview) return;
            const txt = ta.value.trim();
            if (!txt) {
                preview.innerHTML = '<span class="text-[11px] text-gray-400 italic">ยังไม่มีข้อความ (เว้นว่างไว้หากโปรนี้ไม่มีขั้นตอนเพิ่มเติม)</span>';
            } else {
                preview.innerHTML = autoExtra(txt);
            }
        };

        // Helper: แผนผังสีพื้นหลังกล่องแจ้งเตือน
        function getWarningBgClass(colorKey) {
            const key = (colorKey || 'pink').toLowerCase();
            switch (key) {
                case 'red':
                    return 'bg-red-50 border-red-200 text-red-700';
                case 'orange':
                    return 'bg-orange-50 border-orange-200 text-orange-800';
                case 'yellow':
                case 'amber':
                    return 'bg-amber-50 border-amber-200 text-amber-800';
                case 'green':
                case 'emerald':
                    return 'bg-emerald-50 border-emerald-200 text-emerald-800';
                case 'blue':
                    return 'bg-blue-50 border-blue-200 text-blue-800';
                case 'purple':
                    return 'bg-purple-50 border-purple-200 text-purple-800';
                case 'cyan':
                case 'teal':
                    return 'bg-cyan-50 border-cyan-200 text-cyan-800';
                case 'gray':
                case 'slate':
                    return 'bg-slate-100 border-slate-200 text-slate-700';
                case 'pink':
                default:
                    return 'bg-pink-50 border-pink-200 text-pink-700';
            }
        }

        const addonThemeGradientMap = {
            'green':   'linear-gradient(to right, #10b981, #34d399)',
            'orange':  'linear-gradient(to right, #f59e0b, #fbbf24)',
            'purple':  'linear-gradient(to right, #d946ef, #e879f9)',
            'yellow':  'linear-gradient(to right, #eab308, #fbbf24)',
            'blue':    'linear-gradient(to right, #2563eb, #38bdf8)',
            'red':     'linear-gradient(to right, #e11d48, #f87171)',
            'pink':    'linear-gradient(to right, #ec4899, #fb7185)',
            'cyan':    'linear-gradient(to right, #14b8a6, #22d3ee)',
            'slate':   'linear-gradient(to right, #334155, #475569)'
        };

        // Helper: เปลี่ยนสีธีมอัตโนมัติตามค่ายที่เลือก (หากต้องการ)
        window.autoSelectThemeByCarrier = function(carrier) {
            const themeSelect = document.getElementById('swalThemeColor');
            if (!themeSelect) return;
            const c = (carrier || '').toLowerCase();
            if (c.includes('true')) themeSelect.value = 'orange';
            else if (c.includes('dtac')) themeSelect.value = 'purple';
            else if (c.includes('nt')) themeSelect.value = 'yellow';
            else themeSelect.value = 'green';
            if (window.updateThemePreview) window.updateThemePreview();
        };

        // Helper: อัปเดตตัวอย่าง Live Preview สีหัวการ์ดโปรเสริม
        window.updateThemePreview = function() {
            const select = document.getElementById('swalThemeColor');
            const preview = document.getElementById('swalThemePreviewBox');
            const previewText = document.getElementById('swalThemePreviewText');
            if (!select || !preview) return;
            const val = select.value;
            const grad = addonThemeGradientMap[val] || addonThemeGradientMap['green'];
            preview.style.background = grad;
            if (previewText) {
                const title = (document.getElementById('swalTitle')?.value || '').trim() || 'ตัวอย่างหัวการ์ดโปรเสริม';
                previewText.innerText = title;
            }
        };

        // Helper: จัดรูปแบบข้อความแจ้งเตือนให้เหมือนหน้าเว็บจริง ลดรูปไอคอนซ้ำ และแปลงตัวหนาอัตโนมัติ
        function formatWarningText(raw) {
            if (!raw) return '';
            let txt = String(raw).trim();
            if (!txt) return '';

            // 1. ตรวจสอบและลดรูปเครื่องหมาย ⚠️ ที่ซ้ำซ้อนด้านหน้าให้เหลือตัวเดียว
            txt = txt.replace(/^(⚠️\s*)+/u, '⚠️ ');

            // 2. ถ้ายังไม่มีไอคอนเตือนด้านหน้า ให้เติม ⚠️ นำหน้า 1 ตัว
            const hasIcon = /^(<[^>]+>)*\s*(⚠️|🚨|🌸|💡|📌|🔥|⚡|❗|⛔)/u.test(txt);
            if (!hasIcon) {
                txt = '⚠️ ' + txt;
            }

            // 3. ปรับแท็กเปิด-ปิดที่พิมพ์ไม่สมบูรณ์ เช่น <b>ข้อความ<b> ให้เป็น <b>ข้อความ</b>
            txt = txt.replace(/<b\b([^>]*)>(.*?)<[\/]?b\s*>/gi, '<b$1>$2</b>');
            txt = txt.replace(/<strong\b([^>]*)>(.*?)<[\/]?strong\s*>/gi, '<strong$1>$2</strong>');

            // 4. รองรับ Markdown ตัวหนา **ข้อความ** หรือ __ข้อความ__
            txt = txt.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
            txt = txt.replace(/__(.*?)__/g, '<b>$1</b>');

            // 5. ปิดแท็ก <b> หรือ <strong> ที่เปิดค้างไว้ให้อัตโนมัติ
            const openB = (txt.match(/<b\b[^>]*>/gi) || []).length;
            const closeB = (txt.match(/<\/b>/gi) || []).length;
            if (openB > closeB) {
                txt += '</b>'.repeat(openB - closeB);
            }

            const openStrong = (txt.match(/<strong\b[^>]*>/gi) || []).length;
            const closeStrong = (txt.match(/<\/strong>/gi) || []).length;
            if (openStrong > closeStrong) {
                txt += '</strong>'.repeat(openStrong - closeStrong);
            }

            return txt;
        }

        // Helper: อัปเดตตัวอย่าง Live Preview กล่องแจ้งเตือน
        window.updateWarningPreview = function() {
            const input = document.getElementById('swalWarning');
            const select = document.getElementById('swalWarningBg');
            const preview = document.getElementById('swalWarningPreview');
            const container = document.getElementById('swalWarningPreviewContainer');
            if (!input || !select || !preview) return;
            const txt = input.value.trim();
            if (!txt) {
                if (container) container.classList.add('hidden');
                return;
            }
            if (container) container.classList.remove('hidden');
            const bgClass = getWarningBgClass(select.value);
            preview.className = `p-2.5 rounded-xl border text-xs font-medium leading-relaxed ${bgClass}`;
            preview.innerHTML = formatWarningText(txt);
        };

        // Helper: สลับแท็บใน Modal
        window.switchModalTab = function(tab) {
            const tabs = ['general', 'desc', 'ussd'];
            tabs.forEach(t => {
                const content = document.getElementById('modalTab-' + t);
                const btn = document.getElementById('tab-btn-' + t);
                if (content) {
                    if (t === tab) content.classList.remove('hidden');
                    else content.classList.add('hidden');
                }
                if (btn) {
                    if (t === tab) {
                        btn.className = 'modal-tab-btn flex-1 py-2 px-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-emerald-600 text-white shadow-sm flex items-center justify-center gap-1.5';
                    } else {
                        btn.className = 'modal-tab-btn flex-1 py-2 px-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-transparent text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5';
                    }
                }
            });
            const htmlContainer = document.querySelector('.addon-custom-modal .swal2-html-container');
            if (htmlContainer) {
                htmlContainer.scrollTop = 0;
            }
            if (window.initModalScrollHelpers) {
                setTimeout(window.initModalScrollHelpers, 50);
            }
        };

        // Helper: จัดการ Auto-Scroll เมื่อโฟกัสช่องพิมพ์ใดๆ ใน Modal เพื่อไม่ให้คีย์บอร์ดบัง
        window.initModalScrollHelpers = function() {
            const popup = Swal.getPopup();
            if (!popup) return;
            const inputs = popup.querySelectorAll('input, select, textarea');
            inputs.forEach(el => {
                if (el.dataset.scrollAttached) return;
                el.dataset.scrollAttached = 'true';
                el.addEventListener('focus', () => {
                    setTimeout(() => {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                    }, 280);
                });
            });
        };

        // Helper: เพิ่มรหัส USSD แถวใหม่ใน Modal
        window.addSwalCodeRow = function(name = '', price = '', code = '') {
            const list = document.getElementById('swalCodesList');
            if (!list) return;
            const div = document.createElement('div');
            div.className = 'swal-code-row flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm transition-all';
            div.innerHTML = `
                <div class="flex-1">
                    <span class="block sm:hidden text-[10px] text-gray-400 font-bold mb-0.5">ชื่อรหัส</span>
                    <input type="text" class="swal-code-name w-full bg-slate-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold placeholder:text-gray-400 focus:bg-white focus:border-emerald-500 outline-none" placeholder="" value="${escapeHtml(name)}">
                </div>
                <div class="w-full sm:w-36">
                    <span class="block sm:hidden text-[10px] text-gray-400 font-bold mb-0.5">ราคา/หมายเหตุ</span>
                    <input type="text" class="swal-code-price w-full bg-slate-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs placeholder:text-gray-400 focus:bg-white focus:border-emerald-500 outline-none" placeholder="" value="${escapeHtml(price)}">
                </div>
                <div class="w-full sm:w-36">
                    <span class="block sm:hidden text-[10px] text-gray-400 font-bold mb-0.5">รหัส USSD</span>
                    <input type="text" class="swal-code-val w-full bg-slate-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-pink-600 placeholder:text-gray-400 focus:bg-white focus:border-pink-500 outline-none" placeholder="" value="${escapeHtml(code)}">
                </div>
                <button type="button" onclick="this.closest('.swal-code-row').remove(); window.updateUssdTabBadge();" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg text-xs font-bold transition-all shrink-0 self-end sm:self-center" title="ลบรหัสนี้">✕ ลบ</button>
            `;
            list.appendChild(div);
            window.updateUssdTabBadge();
            if (window.initModalScrollHelpers) {
                setTimeout(window.initModalScrollHelpers, 50);
            }
        };

        // Helper: ใส่ตัวอย่าง 4 รหัสของ AIS
        window.insertAisSampleCodes = function() {
            const list = document.getElementById('swalCodesList');
            if (!list) return;
            list.innerHTML = '';
            window.addSwalCodeRow('🛡️ โปรกันรั่ว', '35 บาท / เดือน', '*777*7068#');
            window.addSwalCodeRow('▶️ AISPLAY 1 วัน', '9.63 บาท', '*777*7310#');
            window.addSwalCodeRow('▶️ AISPLAY 7 วัน', '20.33 บาท', '*777*7311#');
            window.addSwalCodeRow('▶️ AISPLAY 30 วัน', '63.13 บาท', '*777*885#');
            window.updateUssdTabBadge();
        };

        // Helper: อัปเดตตัวเลขจำนวนรหัสบนแท็บ USSD
        window.updateUssdTabBadge = function() {
            const count = document.querySelectorAll('#swalCodesList .swal-code-row').length;
            const badge = document.getElementById('tabBadgeUssdCount');
            if (badge) {
                badge.innerText = count > 0 ? `(${count})` : '';
            }
        };

        function getAddonModalHtml(item = null) {
            const isEdit = !!item;
            const carrier = item ? item.carrier : 'AIS';
            const themeColor = item ? (item.theme_color || 'green') : (carrier === 'True' ? 'orange' : (carrier === 'Dtac' ? 'purple' : (carrier === 'NT Mobile' ? 'yellow' : 'green')));
            const duration = item ? (item.duration_text || '30 วัน') : '30 วัน';
            const title = item ? item.title : '';
            const subtitle = item ? (item.subtitle || '') : '';
            const price = item ? item.price : 50;
            const priceLabel = item ? (item.price_label || '') : '';
            const pricePer = item ? (item.price_per || '/ 30 วัน') : '/ 30 วัน';
            const badge = item ? (item.badge || 'ไม่จำกัด GB ✅') : 'ไม่จำกัด GB ✅';
            
            let descLines = '';
            if (item && item.desc_html) {
                descLines = descHtmlToLines(item.desc_html, item.description || '');
            } else if (item && item.description) {
                descLines = item.description;
            }

            const warning = item ? (item.warning || '') : '';
            const warningBg = item ? (item.warning_bg || 'pink') : 'pink';
            
            let extraLines = '';
            if (item && item.extra_html) {
                extraLines = extraHtmlToLines(item.extra_html);
            }

            return `
            <div class="text-left text-slate-800 text-xs sm:text-sm flex flex-col flex-1 w-full">
                <!-- Sticky Tabs Navigation -->
                <div class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm pb-2 pt-0.5">
                    <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/80">
                        <button type="button" onclick="switchModalTab('general')" id="tab-btn-general" class="modal-tab-btn flex-1 py-2 px-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-emerald-600 text-white shadow-sm flex items-center justify-center gap-1.5">
                            <span>⚙️</span> <span>ข้อมูล & ราคา</span>
                        </button>
                        <button type="button" onclick="switchModalTab('desc')" id="tab-btn-desc" class="modal-tab-btn flex-1 py-2 px-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-transparent text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5">
                            <span>📝</span> <span>รายละเอียด & วิธีที่ 2</span>
                        </button>
                        <button type="button" onclick="switchModalTab('ussd')" id="tab-btn-ussd" class="modal-tab-btn flex-1 py-2 px-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-transparent text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5">
                            <span>📲</span> <span>รหัส USSD <span id="tabBadgeUssdCount" class="text-emerald-600 font-bold ml-0.5"></span></span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 pb-28 sm:pb-36">
                    <!-- Tab 1: ข้อมูลทั่วไป & ราคา -->
                    <div id="modalTab-general" class="space-y-4">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 flex items-center gap-1.5">
                                <span>📌</span> ข้อมูลหลักของแพ็กเกจ
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">ค่ายเครือข่าย <span class="text-red-500">*</span></label>
                                    <select id="swalCarrier" onchange="autoSelectThemeByCarrier(this.value)" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none font-semibold">
                                        <option value="AIS" ${carrier === 'AIS' ? 'selected' : ''}>📶 AIS</option>
                                        <option value="True" ${carrier === 'True' ? 'selected' : ''}>📶 True</option>
                                        <option value="Dtac" ${carrier === 'Dtac' ? 'selected' : ''}>📶 Dtac</option>
                                        <option value="NT Mobile" ${carrier === 'NT Mobile' ? 'selected' : ''}>📶 NT Mobile</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">ระยะเวลาแพ็กเกจ</label>
                                    <input id="swalDuration" type="text" value="${escapeHtml(duration)}" placeholder="เช่น 30 วัน" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block font-bold text-gray-700 mb-1">ชื่อแพ็กเกจ (Title) <span class="text-red-500">*</span></label>
                                <input id="swalTitle" type="text" value="${escapeHtml(title)}" placeholder="เช่น AIS PLAY เหมาๆ 30 วัน" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div class="mt-3">
                                <label class="block font-bold text-gray-700 mb-1">คำโปรยย่อย (Subtitle)</label>
                                <input id="swalSubtitle" type="text" value="${escapeHtml(subtitle)}" placeholder="เช่น เน็ตไม่อั้น ไม่ลดสปีด" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <h4 class="font-bold text-slate-700 text-xs uppercase mb-3 flex items-center gap-1.5">
                                <span>💰</span> ค่าบริการ, สีธีม & ป้ายกำกับ
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">ราคาหักจากซิม (บาท) <span class="text-red-500">*</span></label>
                                    <input id="swalPrice" type="number" step="0.01" value="${price}" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">หน่วยเวลาราคา</label>
                                    <input id="swalPricePer" type="text" value="${escapeHtml(pricePer)}" placeholder="เช่น / 30 วัน" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">ข้อความกำกับราคา (Price Label)</label>
                                    <input id="swalPriceLabel" type="text" value="${escapeHtml(priceLabel)}" placeholder="เช่น ค่าบริการรวม VAT" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">ป้ายกำกับ (Badge)</label>
                                    <input id="swalBadge" type="text" value="${escapeHtml(badge)}" placeholder="เช่น ไม่จำกัด GB ✅" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block font-bold text-gray-700 mb-1">🎨 สีธีมแพ็กเกจ (Theme Color)</label>
                                <select id="swalThemeColor" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none font-semibold">
                                    <option value="green" ${themeColor === 'green' ? 'selected' : ''}>🟢 เขียว (Green / Emerald - มาตรฐาน AIS)</option>
                                    <option value="orange" ${themeColor === 'orange' ? 'selected' : ''}>🟠 ส้ม (Orange / Amber - มาตรฐาน True)</option>
                                    <option value="purple" ${themeColor === 'purple' ? 'selected' : ''}>🟣 ม่วง (Purple / Fuchsia - มาตรฐาน Dtac)</option>
                                    <option value="yellow" ${themeColor === 'yellow' ? 'selected' : ''}>🟡 เหลือง (Yellow / Amber - มาตรฐาน NT Mobile)</option>
                                    <option value="blue" ${themeColor === 'blue' ? 'selected' : ''}>🔵 น้ำเงิน (Blue / Sky)</option>
                                    <option value="red" ${themeColor === 'red' ? 'selected' : ''}>🔴 แดง (Red / Rose)</option>
                                    <option value="pink" ${themeColor === 'pink' ? 'selected' : ''}>🌸 ชมพู (Pink)</option>
                                    <option value="cyan" ${themeColor === 'cyan' ? 'selected' : ''}>🌊 ฟ้าคราม (Cyan / Teal)</option>
                                    <option value="slate" ${themeColor === 'slate' ? 'selected' : ''}>⚫ เทาเข้ม (Slate / Dark)</option>
                                </select>
                                <div class="mt-2 p-2.5 rounded-xl text-white font-bold text-xs flex items-center justify-between shadow-sm transition-all" id="swalThemePreviewBox" style="background: linear-gradient(to right, #10b981, #34d399);">
                                    <span id="swalThemePreviewText">ตัวอย่างหัวการ์ดโปรเสริม</span>
                                    <span class="text-[10px] opacity-80 font-normal">ตัวอย่างสีหัวการ์ด</span>
                                </div>
                            </div>
                        </div>
                        <div class="h-16 sm:h-24 pointer-events-none" aria-hidden="true"></div>
                    </div>

                    <!-- Tab 2: รายละเอียด & คำเตือน & วิธีที่ 2 -->
                    <div id="modalTab-desc" class="space-y-4 hidden">
                        <!-- คุณสมบัติ -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <h4 class="font-bold text-slate-700 text-xs uppercase mb-2 flex items-center gap-1.5">
                                <span>📝</span> คุณสมบัติแพ็กเกจ (Features)
                            </h4>
                            <textarea id="swalDescLines" oninput="updateDescPreview()" rows="5" style="font-size: 11px !important; line-height: 1.6;" class="w-full bg-white border border-gray-300 rounded-xl p-2.5 text-[11px] leading-relaxed text-slate-800 font-sans focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="">${escapeHtml(descLines)}</textarea>
                            <div class="mt-2.5">
                                <span class="text-[11px] font-bold text-gray-500 block mb-1">👁️ ตัวอย่างการแสดงผลบนการ์ด:</span>
                                <div id="swalDescPreview"></div>
                            </div>
                        </div>

                        <!-- คำเตือน -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-gray-700 mb-1">⚠️ ข้อความเตือน (เว้นว่างไว้หากไม่มี)</label>
                                    <input id="swalWarning" oninput="updateWarningPreview()" type="text" value="${escapeHtml(warning)}" placeholder="" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">สีพื้นหลังกล่องเตือน</label>
                                    <select id="swalWarningBg" onchange="updateWarningPreview()" class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none font-semibold">
                                        <option value="pink" ${warningBg === 'pink' ? 'selected' : ''}>🌸 ชมพู (Pink - ทั่วไป)</option>
                                        <option value="red" ${warningBg === 'red' ? 'selected' : ''}>🚨 แดง (Red - สำคัญ)</option>
                                        <option value="orange" ${warningBg === 'orange' ? 'selected' : ''}>🍊 ส้ม (Orange)</option>
                                        <option value="yellow" ${warningBg === 'yellow' || warningBg === 'amber' ? 'selected' : ''}>⭐ เหลือง (Yellow)</option>
                                        <option value="green" ${warningBg === 'green' || warningBg === 'emerald' ? 'selected' : ''}>🌿 เขียว (Green)</option>
                                        <option value="blue" ${warningBg === 'blue' ? 'selected' : ''}>💧 น้ำเงิน (Blue)</option>
                                        <option value="purple" ${warningBg === 'purple' ? 'selected' : ''}>🔮 ม่วง (Purple)</option>
                                        <option value="cyan" ${warningBg === 'cyan' || warningBg === 'teal' ? 'selected' : ''}>🌊 ฟ้าคราม (Cyan)</option>
                                        <option value="gray" ${warningBg === 'gray' || warningBg === 'slate' ? 'selected' : ''}>⚙️ เทา (Slate / Gray)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2.5 ${warning ? '' : 'hidden'}" id="swalWarningPreviewContainer">
                                <span class="text-[11px] font-bold text-gray-500 block mb-1">👁️ ตัวอย่างการแสดงผลกล่องเตือน:</span>
                                <div id="swalWarningPreview" class="p-2.5 rounded-xl border text-xs font-medium leading-relaxed"></div>
                            </div>
                        </div>

                        <!-- วิธีที่ 2 -->
                        <div class="bg-orange-50/70 p-3.5 sm:p-4 rounded-2xl border border-orange-200">
                            <textarea id="swalExtraHtml" oninput="updateExtraPreview()" rows="6" style="font-size: 11px !important; line-height: 1.6;" class="w-full bg-white border border-orange-300 rounded-xl p-2.5 text-[11px] leading-relaxed text-slate-800 font-sans focus:ring-2 focus:ring-orange-500 outline-none" placeholder="">${escapeHtml(extraLines)}</textarea>
                            <div class="mt-2.5">
                                <span class="text-[11px] font-bold text-orange-800 block mb-1">👁️ ตัวอย่างการแสดงผลบนหน้าเว็บ (Live Preview):</span>
                                <div id="swalExtraPreview"></div>
                            </div>
                        </div>
                        <div class="h-16 sm:h-24 pointer-events-none" aria-hidden="true"></div>
                    </div>

                    <!-- Tab 3: รหัส USSD -->
                    <div id="modalTab-ussd" class="space-y-4 hidden">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                <div>
                                    <h4 class="font-bold text-slate-700 text-xs uppercase flex items-center gap-1.5">
                                        <span>📲</span> รหัส USSD กดสมัคร
                                    </h4>
                                    <p class="text-[11px] text-gray-400 mt-0.5">เพิ่มได้หลายรหัส พร้อมชื่อและราคา (หากเป็นโปร NOPRO ให้เว้นว่างไว้)</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="insertAisSampleCodes()" class="hidden sm:inline-block bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold px-2.5 py-1.5 rounded-xl transition-all">
                                        ⚡ ตัวอย่างรหัส AIS
                                    </button>
                                    <button type="button" onclick="addSwalCodeRow('', '', '')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition-all flex items-center gap-1 shadow-sm">
                                        <span>➕</span> เพิ่มรหัส
                                    </button>
                                </div>
                            </div>
                            
                            <div id="swalCodesList" class="space-y-2 mt-3">
                                <!-- Code rows appended dynamically -->
                            </div>
                        </div>
                        <div class="h-16 sm:h-24 pointer-events-none" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
            `;
        }

        async function openCreateAddonModal() {
            const { value: formValues } = await Swal.fire({
                title: '➕ เพิ่มโปรเสริมใหม่',
                html: getAddonModalHtml(null),
                customClass: { popup: 'addon-custom-modal' },
                showCancelButton: true,
                confirmButtonText: '💾 บันทึกข้อมูล',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#94a3b8',
                didOpen: () => {
                    window.switchModalTab('general');
                    window.updateDescPreview();
                    window.updateExtraPreview();
                    window.updateWarningPreview();
                    window.updateThemePreview();
                    document.getElementById('swalThemeColor')?.addEventListener('change', window.updateThemePreview);
                    document.getElementById('swalTitle')?.addEventListener('input', window.updateThemePreview);
                    const list = document.getElementById('swalCodesList');
                    if (list) {
                        list.innerHTML = '';
                        window.addSwalCodeRow('', '', '');
                    }
                    if (window.initModalScrollHelpers) {
                        window.initModalScrollHelpers();
                    }
                },
                preConfirm: () => {
                    try {
                        const titleEl = document.getElementById('swalTitle');
                        const title = titleEl ? titleEl.value.trim() : '';
                        if (!title) {
                            window.switchModalTab('general');
                            if (titleEl) titleEl.focus();
                            Swal.showValidationMessage('กรุณาระบุชื่อแพ็กเกจ (Title)');
                            return false;
                        }

                        const carrier = document.getElementById('swalCarrier')?.value || 'AIS';
                        const duration = document.getElementById('swalDuration')?.value?.trim() || '30 วัน';
                        const subtitle = document.getElementById('swalSubtitle')?.value?.trim() || '';
                        const price = parseFloat(document.getElementById('swalPrice')?.value) || 0;
                        const priceLabel = document.getElementById('swalPriceLabel')?.value?.trim() || '';
                        const pricePer = document.getElementById('swalPricePer')?.value?.trim() || '/ 30 วัน';
                        const badge = document.getElementById('swalBadge')?.value?.trim() || 'ไม่จำกัด GB ✅';
                        const themeColor = document.getElementById('swalThemeColor')?.value || 'green';
                        const descLinesRaw = document.getElementById('swalDescLines')?.value || '';
                        const warning = document.getElementById('swalWarning')?.value?.trim() || '';
                        const warningBg = document.getElementById('swalWarningBg')?.value || 'pink';
                        const extraHtml = document.getElementById('swalExtraHtml')?.value?.trim() || '';

                        const descHtml = linesToDescHtml(descLinesRaw);
                        const firstLine = descLinesRaw.split(/\r?\n/).map(l => l.trim()).find(l => l.length > 0) || '';
                        const description = firstLine.replace(/<[^>]*>/g, '');

                        // รวบรวมรหัส USSD ทั้งหมด
                        const rows = document.querySelectorAll('#swalCodesList .swal-code-row');
                        const codes = [];
                        rows.forEach(r => {
                            const cName = r.querySelector('.swal-code-name')?.value?.trim() || '';
                            const cPrice = r.querySelector('.swal-code-price')?.value?.trim() || '';
                            const cVal = r.querySelector('.swal-code-val')?.value?.trim() || '';
                            if (cVal || cName) {
                                codes.push({
                                    name: cName || 'สมัครแพ็กเกจ',
                                    price: cPrice || '',
                                    code: cVal || ''
                                });
                            }
                        });

                        return {
                            carrier,
                            duration_text: duration || '30 วัน',
                            title,
                            subtitle,
                            price,
                            price_label: priceLabel,
                            price_per: pricePer || '/ 30 วัน',
                            badge: badge || 'ไม่จำกัด GB ✅',
                            theme_color: themeColor,
                            description,
                            desc_html: descHtml,
                            warning,
                            warning_bg: warningBg,
                            extra_html: extraHtml,
                            codes
                        };
                    } catch (err) {
                        console.error('preConfirm error:', err);
                        Swal.showValidationMessage('เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: ' + err.message);
                        return false;
                    }
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create', ...formValues })
                    });
                    const data = await res.json().catch(() => null);
                    if (data && data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Toast.fire({ icon: 'error', title: (data && data.message) ? data.message : 'บันทึกไม่สำเร็จ' });
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้: ' + e.message });
                }
            }
        }

        async function openEditAddon(id) {
            const a = addonsData.find(x => x.id == id);
            if (!a) return;

            const { value: formValues } = await Swal.fire({
                title: `✏️ แก้ไขโปรเสริม: ${escapeHtml(a.title)}`,
                html: getAddonModalHtml(a),
                customClass: { popup: 'addon-custom-modal' },
                showCancelButton: true,
                confirmButtonText: '💾 บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#94a3b8',
                didOpen: () => {
                    window.switchModalTab('general');
                    window.updateDescPreview();
                    window.updateExtraPreview();
                    window.updateWarningPreview();
                    window.updateThemePreview();
                    document.getElementById('swalThemeColor')?.addEventListener('change', window.updateThemePreview);
                    document.getElementById('swalTitle')?.addEventListener('input', window.updateThemePreview);
                    const list = document.getElementById('swalCodesList');
                    if (list) {
                        list.innerHTML = '';
                        const codes = Array.isArray(a.codes) ? a.codes : [];
                        if (codes.length > 0) {
                            codes.forEach(c => {
                                window.addSwalCodeRow(c.name || '', c.price || '', c.code || '');
                            });
                        }
                    }
                    if (window.initModalScrollHelpers) {
                        window.initModalScrollHelpers();
                    }
                },
                preConfirm: () => {
                    try {
                        const titleEl = document.getElementById('swalTitle');
                        const title = titleEl ? titleEl.value.trim() : '';
                        if (!title) {
                            window.switchModalTab('general');
                            if (titleEl) titleEl.focus();
                            Swal.showValidationMessage('กรุณาระบุชื่อแพ็กเกจ (Title)');
                            return false;
                        }

                        const carrier = document.getElementById('swalCarrier')?.value || 'AIS';
                        const duration = document.getElementById('swalDuration')?.value?.trim() || '30 วัน';
                        const subtitle = document.getElementById('swalSubtitle')?.value?.trim() || '';
                        const price = parseFloat(document.getElementById('swalPrice')?.value) || 0;
                        const priceLabel = document.getElementById('swalPriceLabel')?.value?.trim() || '';
                        const pricePer = document.getElementById('swalPricePer')?.value?.trim() || '/ 30 วัน';
                        const badge = document.getElementById('swalBadge')?.value?.trim() || 'ไม่จำกัด GB ✅';
                        const themeColor = document.getElementById('swalThemeColor')?.value || (a ? a.theme_color : 'green') || 'green';
                        const descLinesRaw = document.getElementById('swalDescLines')?.value || '';
                        const warning = document.getElementById('swalWarning')?.value?.trim() || '';
                        const warningBg = document.getElementById('swalWarningBg')?.value || 'pink';
                        const extraHtml = document.getElementById('swalExtraHtml')?.value?.trim() || '';

                        const descHtml = linesToDescHtml(descLinesRaw);
                        const firstLine = descLinesRaw.split(/\r?\n/).map(l => l.trim()).find(l => l.length > 0) || '';
                        const description = firstLine.replace(/<[^>]*>/g, '');

                        // รวบรวมรหัส USSD ทั้งหมด
                        const rows = document.querySelectorAll('#swalCodesList .swal-code-row');
                        const codes = [];
                        rows.forEach(r => {
                            const cName = r.querySelector('.swal-code-name')?.value?.trim() || '';
                            const cPrice = r.querySelector('.swal-code-price')?.value?.trim() || '';
                            const cVal = r.querySelector('.swal-code-val')?.value?.trim() || '';
                            if (cVal || cName) {
                                codes.push({
                                    name: cName || 'สมัครแพ็กเกจ',
                                    price: cPrice || '',
                                    code: cVal || ''
                                });
                            }
                        });

                        return {
                            id,
                            carrier,
                            duration_text: duration || '30 วัน',
                            title,
                            subtitle,
                            price,
                            price_label: priceLabel,
                            price_per: pricePer || '/ 30 วัน',
                            badge: badge || 'ไม่จำกัด GB ✅',
                            theme_color: themeColor,
                            description,
                            desc_html: descHtml,
                            warning,
                            warning_bg: warningBg,
                            extra_html: extraHtml,
                            codes
                        };
                    } catch (err) {
                        console.error('preConfirm error:', err);
                        Swal.showValidationMessage('เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: ' + err.message);
                        return false;
                    }
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', ...formValues })
                    });
                    const data = await res.json().catch(() => null);
                    if (data && data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Toast.fire({ icon: 'error', title: (data && data.message) ? data.message : 'บันทึกไม่สำเร็จ' });
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้: ' + e.message });
                }
            }
        }

        async function deleteAddon(id, title) {
            const confirm = await Swal.fire({
                title: `ลบโปรเสริม "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันลบ',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Toast.fire({ icon: 'error', title: data.message || 'ลบไม่สำเร็จ' });
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้' });
                }
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        document.addEventListener('DOMContentLoaded', () => loadAddons());
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
