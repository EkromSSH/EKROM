<?php
require_once __DIR__ . '/api/db.php';
$siteSettings = get_site_settings();
$siteName = htmlspecialchars($siteSettings['site_name'] ?: 'EKROM');
$siteSubtitle = htmlspecialchars($siteSettings['site_subtitle'] ?: 'SHOP');
$siteTitle = htmlspecialchars($siteSettings['site_title'] ?: 'EKROM Shop');
$siteLogo = htmlspecialchars($siteSettings['site_logo'] ?? '');
$siteInitial = htmlspecialchars(mb_substr($siteSettings['site_name'] ?: 'EKROM', 0, 2));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>ประวัติการทำรายการ - <?= $siteName ?> <?= $siteSubtitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; scroll-behavior: smooth; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .history-card:hover { border-color: #fbcfe8; background-color: #f8fafc; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel="stylesheet" href="mobile-layout.css?v=20261031_fixedpinned">
    <link rel="stylesheet" href="cyber-oled.css?v=20261030_cleanlight">
</head>
<body class="mobile-safe-layout bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <div class="mobile-topbar lg:hidden bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-lg tracking-tight italic"><?= $siteName ?> <span class="text-pink-600"><?= $siteSubtitle ?></span></span>
        </div>
        
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <div onclick="window.location.href='topup.php'" class="wallet-badge" title="เติมเงิน">
                <div class="wallet-icon">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="wallet-amount">฿<span id="userBalanceMob">0.00</span></span>
                <span class="wallet-add-btn">+</span>
            </div>
            <button onclick="toggleMobileMenu()" class="hamburger-btn focus:outline-none" aria-label="เปิดเมนู">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="drawer-header flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                        <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
                    </div>
                    <span class="drawer-title font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-pink-600"><?= $siteSubtitle ?></span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2 overflow-y-auto">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🏠</span> <span>หน้าแรก</span></a>
                <a href="buyer-dash.php#my-files" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 hover:text-pink-600 transition-all"><span>📁</span> <span>ดูไฟล์ของฉัน</span></a>
                <?php if (is_backup_config_enabled()): ?>
                <a href="buyer-dash.php?backup=1" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs">
                    <div class="flex items-center gap-3">
                        <span>⚡</span> <span>ไฟล์สำรอง</span>
                    </div>
                    <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
                </a>
                <?php endif; ?>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="mobile-topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📱</span> เติมเงินมือถือ</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงินเข้าเว็บ</a>
                <a href="history.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">SHOP</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🏠</span> <span>หน้าแรก</span></a>
                <a href="buyer-dash.php#my-files" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 hover:text-pink-600 transition-all"><span>📁</span> <span>ดูไฟล์ของฉัน</span></a>
            <?php if (is_backup_config_enabled()): ?>
            <a href="buyer-dash.php?backup=1" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs cursor-pointer">
                <div class="flex items-center gap-3">
                    <span>⚡</span> <span>ไฟล์สำรอง</span>
                </div>
                <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
            </a>
            <?php endif; ?>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="mobile-topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📱</span> เติมเงินมือถือ</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงินเข้าเว็บ</a>
            <a href="history.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ประวัติการทำรายการ 📜</h1>
                <p class="text-gray-500 mt-1 text-xs md:text-sm">ตรวจสอบประวัติการสั่งซื้อและประวัติการเติมเงินของคุณ</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div onclick="window.location.href='topup.php'" class="hidden md:flex bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl items-center gap-2.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                    <div class="w-7 h-7 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-sm">💰</div>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider leading-tight">ยอดเงินคงเหลือ</span>
                        <span class="font-bold text-emerald-700 leading-tight text-xs md:text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                    </div>
                    <span class="ml-1.5 bg-emerald-500 text-white text-[11px] font-bold px-2 py-1 rounded-lg shadow-sm group-hover:bg-emerald-600 transition-all">+ เติมเงิน</span>
                </div>
            </div>
        </header>

        <div class="max-w-5xl mx-auto bg-white rounded-[32px] shadow-sm border border-gray-100 p-4 md:p-8">
            
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 md:gap-4 mb-6 border-b border-gray-100 pb-6">
                <div class="bg-slate-100/80 p-1.5 rounded-2xl flex flex-wrap w-full md:w-auto border border-slate-200/60 shadow-inner gap-1">
                    <button id="tab-vpn" onclick="switchTab('vpn')" class="flex-1 md:flex-none md:px-6 py-2.5 rounded-xl font-bold text-xs md:text-sm bg-white text-pink-600 shadow-sm transition-all">🛒 สั่งซื้อ VPN</button>
                    <button id="tab-topup" onclick="switchTab('topup')" class="flex-1 md:flex-none md:px-6 py-2.5 rounded-xl font-bold text-xs md:text-sm text-slate-500 hover:text-slate-800 transition-all">💰 เติมเงิน</button>
                    <button id="tab-mobile" onclick="switchTab('mobile')" class="flex-1 md:flex-none md:px-6 py-2.5 rounded-xl font-bold text-xs md:text-sm text-slate-500 hover:text-slate-800 transition-all">📱 เติมเงินมือถือ</button>
                </div>
                
                <button onclick="refreshCurrentTab(this)" class="text-pink-600 text-xs md:text-sm font-bold hover:bg-pink-100 transition-all bg-pink-50 px-5 py-2.5 rounded-2xl border border-pink-100 flex items-center justify-center gap-2">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>

            <div id="history-list" class="space-y-4">
                <div class="text-center py-10 text-gray-400">กำลังโหลดข้อมูล...</div>
            </div>
        </div>
    </main>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                document.body.classList.add('drawer-open');
                menu.classList.remove('hidden');
                setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0'); drawer.classList.add('-translate-x-full');
                document.body.classList.remove('drawer-open');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if(data.status === 'success') {
                    document.getElementById('userBalanceDesk').innerText = data.balance;
                    document.getElementById('userBalanceMob').innerText = data.balance;
                }
            } catch(e) { console.error('Failed to load user info'); }
        }

        let currentTab = 'vpn';

        function switchTab(tab) {
            currentTab = tab;
            const btnVpn = document.getElementById('tab-vpn');
            const btnTopup = document.getElementById('tab-topup');
            const btnMobile = document.getElementById('tab-mobile');

            const activeClass = "flex-1 md:flex-none md:px-6 py-2.5 rounded-xl font-bold text-xs md:text-sm bg-white text-pink-600 shadow-sm transition-all";
            const inactiveClass = "flex-1 md:flex-none md:px-6 py-2.5 rounded-xl font-bold text-xs md:text-sm text-slate-500 hover:text-slate-800 transition-all";

            [btnVpn, btnTopup, btnMobile].forEach(b => {
                if (b) b.className = inactiveClass;
            });

            if (tab === 'vpn') {
                if (btnVpn) btnVpn.className = activeClass;
                loadVPNHistory();
            } else if (tab === 'topup') {
                if (btnTopup) btnTopup.className = activeClass;
                loadTopupHistory();
            } else if (tab === 'mobile') {
                if (btnMobile) btnMobile.className = activeClass;
                loadMobileHistory();
            }
        }

        async function refreshCurrentTab(btn) {
            const icon = btn ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;
            try {
                if (currentTab === 'vpn') {
                    await loadVPNHistory();
                } else if (currentTab === 'topup') {
                    await loadTopupHistory();
                } else if (currentTab === 'mobile') {
                    await loadMobileHistory();
                }
                if (btn) {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชประวัติแล้ว' });
                }
            } catch (e) {
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        // โหลดข้อมูล VPN
        async function loadVPNHistory() {
            const historyContainer = document.getElementById('history-list');
            historyContainer.innerHTML = '<div class="text-center py-10 text-gray-400">กำลังโหลดประวัติการสั่งซื้อ... ⏳</div>';
            
            try {
                const res = await fetch('api/get_vpn_list.php', { cache: 'no-store' });
                const result = await res.json();
                
                if (result.status === 'success') {
                    if (result.data.length === 0) {
                        historyContainer.innerHTML = `
                            <div class="text-center py-16 flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">📭</div>
                                <p class="text-gray-500 font-bold">ยังไม่มีประวัติการสั่งซื้อ VPN</p>
                                <p class="text-gray-400 text-sm mt-1">กดซื้อแพ็กเกจ VPN เพื่อเริ่มใช้งานได้เลย</p>
                                <button onclick="window.location.href='store.php'" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-xl font-bold text-sm hover:bg-pink-500 transition-all shadow-md">ไปที่ร้านค้า</button>
                            </div>
                        `;
                    } else {
                        historyContainer.innerHTML = result.data.map(item => {
                            const isActive = item.status_real === 'active';
                            const statusColor = isActive ? 'text-green-600 bg-green-50 border-green-100' : 'text-red-600 bg-red-50 border-red-100';
                            const statusText = isActive ? 'พร้อมใช้งาน' : 'หมดอายุแล้ว';
                            const iconStr = item.server_name.includes('Gaming') ? '🎮' : '🇹🇭';

                            return `
                            <div class="p-5 rounded-2xl border border-gray-100 transition-all history-card flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-pink-50 text-pink-600 rounded-xl flex items-center justify-center text-xl shrink-0">${iconStr}</div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm md:text-base">${item.server_name}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">แพ็กเกจ: <span class="text-pink-600 font-bold">${item.package_name}</span></p>
                                    </div>
                                </div>
                                <div class="flex flex-col md:items-end w-full md:w-auto mt-2 md:mt-0 border-t md:border-none border-gray-50 pt-3 md:pt-0">
                                    <p class="text-[10px] md:text-xs text-gray-400 font-bold mb-1.5 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> หมดอายุ: ${item.expiry_time}</p>
                                    <div class="flex justify-between w-full md:w-auto items-center">
                                        <span class="px-3 py-1 ${statusColor} text-[10px] font-bold rounded-full border uppercase">${statusText}</span>
                                    </div>
                                </div>
                            </div>
                        `}).join('');
                    }
                }
            } catch (e) { 
                historyContainer.innerHTML = '<p class="text-center text-red-500 py-10 font-bold">⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>'; 
            }
        }

        async function loadTopupHistory() {
            const historyContainer = document.getElementById('history-list');
            historyContainer.innerHTML = '<div class="text-center py-10 text-gray-400">กำลังโหลดประวัติการเติมเงิน... ⏳</div>';
            
            try {
                const res = await fetch('api/get_topup_history.php', { cache: 'no-store' });
                const result = await res.json();
                
                if (result.status === 'success') {
                    if (result.data.length === 0) {
                        historyContainer.innerHTML = `
                            <div class="text-center py-16 flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">💳</div>
                                <p class="text-gray-500 font-bold">ยังไม่มีประวัติการเติมเงิน</p>
                                <button onclick="window.location.href='topup.php'" class="mt-6 bg-emerald-500 text-white px-6 py-2 rounded-xl font-bold text-sm hover:bg-emerald-600 transition-all shadow-md">เติมเงินเข้าระบบ</button>
                            </div>
                        `;
                    } else {
                        historyContainer.innerHTML = result.data.map(item => {
                            return `
                            <div class="p-5 rounded-2xl border border-gray-100 transition-all history-card flex justify-between items-center gap-4 hover:border-emerald-200">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">💵</div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm md:text-base">เติมเงินสำเร็จ</h3>
                                        <p class="text-[10px] md:text-xs text-gray-400 mt-0.5">วันที่ทำรายการ: ${item.created_at}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-emerald-600 text-base md:text-lg whitespace-nowrap">+฿${parseFloat(item.amount).toFixed(2)}</p>
                                    <span class="text-[9px] text-emerald-500 font-bold bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded uppercase mt-1 inline-block">Success</span>
                                </div>
                            </div>
                        `}).join('');
                    }
                }
            } catch (e) { 
                historyContainer.innerHTML = '<p class="text-center text-red-500 py-10 font-bold">⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>'; 
            }
        }

        function formatThaiDateTime(dateStr) {
            if (!dateStr) return '-';
            try {
                const parts = dateStr.split(' ');
                if (parts.length < 2) return dateStr;
                const [y, m, d] = parts[0].split('-');
                const [h, min] = parts[1].split(':');
                const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                const mName = months[parseInt(m, 10)] || m;
                const thaiYear = parseInt(y, 10) + 543;
                return `${parseInt(d, 10)} ${mName} ${thaiYear} • ${h}:${min} น.`;
            } catch (e) {
                return dateStr;
            }
        }

        function copyText(text, label = 'ข้อความ') {
            if (!text || text === '-') return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: `คัดลอก${label}แล้ว` });
        }

        let mobileHistoryTimerInterval = null;
        function startHistoryMobileCountdownTimer(list) {
            if (mobileHistoryTimerInterval) clearInterval(mobileHistoryTimerInterval);
            mobileHistoryTimerInterval = setInterval(() => {
                let hasRefundable = false;
                let needRerender = false;
                list.forEach(item => {
                    if (item.can_refund && item.seconds_left > 0) {
                        hasRefundable = true;
                        item.seconds_left -= 1;
                        const m = Math.floor(item.seconds_left / 60);
                        const s = item.seconds_left % 60;
                        if (item.seconds_left <= 0) {
                            item.can_refund = false;
                            needRerender = true;
                        } else {
                            const el = document.querySelector(`[data-mobile-timer-id="${item.id}"]`);
                            if (el) {
                                el.textContent = m > 0 ? (s > 0 ? `${m} นาที ${s} วิ` : `${m} นาที`) : `${s} วินาที`;
                            }
                        }
                    }
                });
                if (needRerender) loadMobileHistory();
            }, 1000);
        }

        // โหลดประวัติการเติมเงินมือถือ
        async function loadMobileHistory() {
            const container = document.getElementById('history-list');
            container.innerHTML = `
                <div class="space-y-3 animate-pulse">
                    <div class="p-5 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3.5"><div class="w-12 h-12 bg-slate-100 rounded-2xl"></div><div class="space-y-2"><div class="w-32 h-4 bg-slate-200 rounded"></div><div class="w-48 h-3 bg-slate-100 rounded"></div></div></div>
                        <div class="w-20 h-6 bg-slate-200 rounded"></div>
                    </div>
                    <div class="p-5 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3.5"><div class="w-12 h-12 bg-slate-100 rounded-2xl"></div><div class="space-y-2"><div class="w-32 h-4 bg-slate-200 rounded"></div><div class="w-48 h-3 bg-slate-100 rounded"></div></div></div>
                        <div class="w-20 h-6 bg-slate-200 rounded"></div>
                    </div>
                </div>
            `;

            try {
                const res = await fetch('api/mobile_topup.php?action=get_history', { cache: 'no-store' });
                const result = await res.json();

                if (result.status === 'success') {
                    const list = result.history || [];
                    if (list.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-16 flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">📱</div>
                                <p class="text-gray-500 font-bold">ยังไม่มีประวัติการเติมเงินมือถือ</p>
                                <p class="text-gray-400 text-sm mt-1">สามารถเติมเงินมือถือ AIS, TrueMove H, DTAC ได้ตลอด 24 ชม.</p>
                                <button onclick="window.location.href='mobile-topup.php'" class="mt-6 bg-pink-600 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-pink-700 transition-all shadow-md">
                                    ไปหน้าเติมเงินมือถือ 📱
                                </button>
                            </div>
                        `;
                    } else {
                        container.innerHTML = list.map(item => {
                            let opGradient = 'from-emerald-500 via-emerald-600 to-green-700 shadow-emerald-500/20';
                            let opDisplayName = 'AIS 1-2-Call';
                            let opSub = '1-2-CALL';
                            let opBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                            if (item.operator === 'TRUE') {
                                opGradient = 'from-red-500 via-rose-600 to-red-700 shadow-red-500/20';
                                opDisplayName = 'TrueMove H';
                                opSub = 'MOVE H';
                                opBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                            } else if (item.operator === 'DTAC') {
                                opGradient = 'from-sky-500 via-blue-600 to-indigo-600 shadow-sky-500/20';
                                opDisplayName = 'Happy DTAC';
                                opSub = 'HAPPY';
                                opBadgeClass = 'bg-sky-50 text-sky-700 border-sky-200';
                            }

                            const isRefunded = (item.status === 'refunded' || item.refund_status === 'success');
                            let statusBadge = isRefunded
                                ? `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> ดึงเงินคืนแล้ว</span>`
                                : `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> เติมสำเร็จ</span>`;

                            const thaiDate = formatThaiDateTime(item.created_at);
                            const cleanTimeLeft = (item.time_left_text || '').replace('เหลือเวลา ', '');

                            let bottomRibbon = '';
                            if (isRefunded) {
                                const rAmt = item.refund_amount ? item.refund_amount.toFixed(2) : item.amount.toFixed(2);
                                bottomRibbon = `
                                    <div class="bg-gradient-to-r from-purple-50 via-indigo-50/40 to-purple-50 border-t border-purple-100 px-4 sm:px-5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                        <div class="flex items-center gap-2 text-purple-950">
                                            <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0">✓</span>
                                            <span>ดึงเงินคืนสำเร็จ: <b class="font-mono text-purple-950 font-bold bg-white px-2 py-0.5 rounded border border-purple-200">+฿${rAmt}</b> โอนคืนเข้ากระเป๋าเครดิตแล้ว</span>
                                        </div>
                                        <span class="text-[11px] font-bold text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full border border-purple-200 self-start sm:self-auto">💳 เครดิตพร้อมใช้งาน</span>
                                    </div>
                                `;
                            } else if (item.can_refund) {
                                bottomRibbon = `
                                    <div class="bg-gradient-to-r from-amber-50 via-orange-50/50 to-amber-50 border-t border-amber-200 px-4 sm:px-5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                        <div class="flex items-center gap-2.5 text-xs text-amber-950">
                                            <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs shrink-0">⏱️</span>
                                            <div>
                                                <span>กรณีเติมผิดเบอร์ สามารถดึงเงินคืนได้ (เหลือเวลาอีก: <b class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300" data-mobile-timer-id="${item.id}">${cleanTimeLeft}</b>)</span>
                                            </div>
                                        </div>
                                        <button onclick="requestRefundMobile(${item.id}, '${item.phone}', ${item.amount})" class="px-3.5 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 active:scale-95 text-white rounded-xl font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0 self-start sm:self-auto">
                                            <span>🔄 ขอดึงเงินคืนทันที</span>
                                        </button>
                                    </div>
                                `;
                            } else {
                                bottomRibbon = `
                                    <div class="bg-slate-50 border-t border-slate-100 px-4 sm:px-5 py-2 flex items-center justify-between text-xs text-slate-400">
                                        <span>ครบกำหนดการคุ้มครอง 15 นาที (รายการสมบูรณ์)</span>
                                        <span class="text-[10px] bg-white px-2 py-0.5 rounded border border-slate-200">เสร็จสิ้น</span>
                                    </div>
                                `;
                            }

                            return `
                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all overflow-hidden">
                                    <div class="p-4 sm:p-5">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="flex items-center gap-3.5 min-w-0">
                                                <div class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl flex flex-col items-center justify-center font-black text-white shrink-0 bg-gradient-to-br ${opGradient} border border-white/20 shadow-md">
                                                    <span class="text-xs sm:text-sm font-black tracking-tight leading-none">${item.operator}</span>
                                                    <span class="text-[8px] font-bold opacity-90 tracking-wider mt-0.5 uppercase">${opSub}</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-mono text-lg font-black text-slate-900 tracking-wide select-all">${item.phone}</span>
                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg border ${opBadgeClass}">${opDisplayName}</span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2 text-slate-400 text-xs mt-1">
                                                        <span>${thaiDate}</span>
                                                        <span>•</span>
                                                        <span class="font-mono text-[10px] text-slate-500 bg-slate-100 hover:bg-slate-200 px-2 py-0.5 rounded border border-slate-200/60 cursor-pointer inline-flex items-center gap-1" onclick="copyText('${item.trans_id}', 'เลขอ้างอิง')" title="คลิกเพื่อคัดลอก">
                                                            <span>#${item.trans_id || '-'}</span> 📋
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between sm:flex-col sm:items-end pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 gap-1.5">
                                                <span class="text-xl font-black text-slate-900 font-mono tracking-tight">฿${item.amount.toFixed(2)}</span>
                                                <div class="flex items-center gap-2">
                                                    ${statusBadge}
                                                    <a href="mobile-topup.php" class="text-[11px] font-bold text-pink-600 bg-pink-50 hover:bg-pink-100 px-2 py-1 rounded-lg border border-pink-200 transition-all">เติมซ้ำ ⚡</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ${bottomRibbon}
                                </div>
                            `;
                        }).join('');
                        startHistoryMobileCountdownTimer(list);
                    }
                } else {
                    container.innerHTML = `<div class="p-8 text-center text-rose-500 font-bold text-xs bg-rose-50 rounded-2xl">⚠️ ${result.message || 'ไม่สามารถโหลดประวัติได้'}</div>`;
                }
            } catch (e) {
                container.innerHTML = '<div class="p-8 text-center text-rose-500 font-bold text-xs bg-rose-50 rounded-2xl">⚠️ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</div>';
            }
        }

        async function requestRefundMobile(topupId, phone, amount) {
            const confirm = await Swal.fire({
                title: 'ขอเงินคืน (กรณีเติมผิดเบอร์)?',
                html: `
                    <div class="text-left bg-gradient-to-br from-amber-50 to-orange-50/40 p-4 rounded-2xl border border-amber-200 text-sm space-y-2 mt-2">
                        <div class="flex justify-between items-center"><span class="text-amber-800">เบอร์ที่เติมผิด:</span> <b class="font-mono text-slate-900">${phone}</b></div>
                        <div class="flex justify-between items-center"><span class="text-amber-800">ยอดเงินที่จะดึงคืน:</span> <b class="text-emerald-600 font-mono font-bold">+฿${parseFloat(amount).toFixed(2)}</b></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-3 text-left">ระบบจะทำการตรวจสอบและดึงยอดเงินคงเหลือจากซิมปลายทาง คืนกลับเข้ากระเป๋าเครดิตเว็บของคุณทันทีแบบอัตโนมัติ</p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '🔄 ยืนยันดึงเงินคืนทันที',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#94a3b8'
            });

            if (!confirm.isConfirmed) return;

            Swal.fire({
                title: 'กำลังตรวจสอบและดึงเงินคืน...',
                html: 'กรุณารอสักครู่ ระบบกำลังประสานงานดึงยอดเงินคืนอัตโนมัติ',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('topup_id', topupId);
                const res = await fetch('api/mobile_topup.php?action=refund', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    await Swal.fire({ icon: 'success', title: 'ดึงเงินคืนสำเร็จ! 🎉', text: data.message, confirmButtonColor: '#10b981' });
                    loadUserInfo();
                    loadMobileHistory();
                } else {
                    Swal.fire({ icon: 'error', title: 'ไม่สามารถดึงเงินคืนได้', text: data.message || 'เกิดข้อผิดพลาด', confirmButtonColor: '#ef4444' });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', confirmButtonColor: '#ef4444' });
            }
        }

        loadUserInfo();
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'mobile') {
            switchTab('mobile');
        } else {
            switchTab('vpn');
        }

        async function checkAdminRoleAndInjectButton() {
            try {
                const res = await fetch('api/check_auth.php');
                const data = await res.json();
                const role = data.role || data.user?.role;
                if (data.status === 'logged_in' && role === 'admin') {
                    const btnHTML = `<a href="admin-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-rose-600 hover:bg-rose-50 transition-all mt-2 border border-rose-100"><span>⚙️</span> จัดการระบบ (Admin)</a>`;
                    const deskNav = document.querySelector('aside nav');
                    if (deskNav && !deskNav.innerHTML.includes('admin-dash.php')) deskNav.insertAdjacentHTML('beforeend', btnHTML);
                    const mobNav = document.querySelector('#mobileDrawer nav');
                    if (mobNav && !mobNav.innerHTML.includes('admin-dash.php')) mobNav.insertAdjacentHTML('beforeend', btnHTML);
                }
            } catch(e) { }
        }

        document.addEventListener('DOMContentLoaded', checkAdminRoleAndInjectButton);
    </script>



</body>
</html>
