<?php
require_once __DIR__ . '/api/db.php';
release_session_lock();

$siteSettings = get_site_settings();
$siteName = htmlspecialchars($siteSettings['site_name'] ?: 'EKROM');
$siteSubtitle = htmlspecialchars($siteSettings['site_subtitle'] ?: 'SHOP');
$siteTitle = htmlspecialchars($siteSettings['site_title'] ?: 'EKROM Shop');
$siteLogo = htmlspecialchars($siteSettings['site_logo'] ?? '');
$siteInitial = htmlspecialchars(mb_substr($siteSettings['site_name'] ?: 'EKROM', 0, 2));

$totalUsers = 0;
$totalSales = 0;

try {
    $db = get_db();
    $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $ordersCount = (int)$db->query("SELECT COUNT(*) FROM orders_history WHERE type IN ('buy', 'renew')")->fetchColumn();
    $vpnCount = (int)$db->query("SELECT COUNT(*) FROM vpn_configs")->fetchColumn();
    $totalSales = max($ordersCount, $vpnCount);
} catch (Exception $e) {
    // fallback if db error
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Dashboard - <?= $siteName ?> <?= $siteSubtitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="qrcode.min.js?v=20260927_1"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link rel="stylesheet" href="announcement.css?v=<?= filemtime(__DIR__ . '/announcement.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .vpn-card { min-width: 0; }
        .vpn-card:hover { border-color: #f9a8d4; transform: translateY(-4px); cursor: pointer; box-shadow: 0 10px 25px -5px rgba(236, 72, 153, 0.1); }
        .modal-active { display: flex !important; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        img {
            max-width: 100%;
            height: auto;
        }
        @media (max-width: 767px) {
            .vpn-card:hover { transform: none; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); }
        }
    </style>
    <script>
        let current_opened_id = null;
        let deleteCountdownInterval = null;
        let isUserReseller = false; 
        let resellerDiscountPercent = 30;
        let vpnItems = [];
        const vpnItemsById = new Map();
        const vpnTrafficStates = Object.create(null);
        const vpnTrafficQueue = [];
        const vpnTrafficQueued = new Set();
        let vpnTrafficActive = 0;
        let vpnFilterTimer = null;

        const authReady = fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') {
                window.location.href = 'login.php';
                throw new Error('auth_required');
            }
            return data;
        }).catch(() => {
            window.location.href = 'login.php';
            throw new Error('auth_required');
        });

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    </script>
    <link rel="stylesheet" href="mobile-layout.css?v=20261031_stickytop3">
    <link rel="stylesheet" href="cyber-oled.css?v=20261030_sharpborder">
</head>

<body class="mobile-safe-layout bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Mobile Header & Drawer -->
    <div class="mobile-topbar lg:hidden bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="brand-title font-bold text-lg tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">HOME</span></span>
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
                    <span class="drawer-title font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">HOME</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2 overflow-y-auto">
                <a href="buyer-dash.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>🏠</span> <span>หน้าแรก</span></a>
                <a href="javascript:void(0)" onclick="toggleMobileMenu(); scrollToMyFiles();" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 hover:text-pink-600 transition-all"><span>📁</span> <span>ดูไฟล์ของฉัน</span></a>
                <a href="javascript:void(0)" onclick="toggleMobileMenu(); openBackupModal();" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs">
                    <div class="flex items-center gap-3">
                        <span>⚡</span> <span>ไฟล์สำรอง</span>
                    </div>
                    <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
                </a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> <span>บริการ VPN</span></a>
                <a href="mobile-topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📱</span> <span>เติมเงินมือถือ</span></a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> <span>เติมเงินเข้าเว็บ</span></a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> <span>ประวัติการทำรายการ</span></a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> <span>โปรเสริม</span></a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> <span>ติดต่อแอดมิน</span></a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> <span>ออกจากระบบ</span></button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">HOME</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>🏠</span> <span>หน้าแรก</span></a>
            <a href="javascript:void(0)" onclick="scrollToMyFiles();" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 hover:text-pink-600 transition-all"><span>📁</span> <span>ดูไฟล์ของฉัน</span></a>
            <a href="javascript:void(0)" onclick="openBackupModal()" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs cursor-pointer">
                <div class="flex items-center gap-3">
                    <span>⚡</span> <span>ไฟล์สำรอง</span>
                </div>
                <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
            </a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="mobile-topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📱</span> เติมเงินมือถือ</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงินเข้าเว็บ</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0 gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] md:text-[11px] font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span>
                        Connection Hub
                    </span>
                    <span class="text-xs text-slate-400 hidden sm:inline">•</span>
                    <span class="text-xs text-slate-400 font-medium hidden sm:inline">ระบบอัตโนมัติ 24 ชม.</span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2 truncate">ศูนย์ควบคุมการเชื่อมต่อ <span class="text-pink-500">⚡</span></h1>
                <p class="text-gray-400 text-xs md:text-sm truncate">ตรวจสอบสถานะ จัดการคอนฟิก และต่ออายุแพ็กเกจความเร็วสูงของคุณ</p>
            </div>

            <div class="flex items-center gap-3 md:gap-4 shrink-0">
                <div onclick="window.location.href='topup.php'" class="hidden md:flex bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl items-center gap-2.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                    <div class="w-7 h-7 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-sm">💰</div>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider leading-tight">ยอดเงินคงเหลือ</span>
                        <span class="font-bold text-emerald-700 leading-tight text-xs md:text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                    </div>
                    <span class="ml-1.5 bg-emerald-500 text-white text-[11px] font-bold px-2 py-1 rounded-lg shadow-sm group-hover:bg-emerald-600 transition-all">+ เติมเงิน</span>
                </div>
                <div onclick="openProfile()" class="flex items-center gap-2 md:gap-3 cursor-pointer bg-white border border-gray-200 pl-3 md:pl-4 pr-1 md:pr-1.5 py-1 md:py-1.5 rounded-full hover:bg-gray-50 transition-all shadow-sm max-w-[140px] sm:max-w-[200px] md:max-w-xs">
                    <span id="userNameDisplay" class="font-bold text-slate-700 text-xs md:text-sm truncate block">กำลังโหลด...</span>
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-pink-100 rounded-full border-2 border-white shadow-sm flex items-center justify-center font-bold text-pink-600 shrink-0">👤</div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">
            <!-- การ์ดสถานะร้านค้า (โทนสีดำอ่อนๆ พรีเมียม) -->
            <div class="p-5 md:p-6 rounded-3xl shadow-sm flex flex-col justify-between gap-3 overflow-hidden relative text-white"
                 style="background: linear-gradient(145deg, #181d29 0%, #11151f 55%, #151922 100%) !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.25), 0 4px 10px -2px rgba(0, 0, 0, 0.15) !important;">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-36 h-36 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] md:text-[11px] font-bold bg-slate-800/90 border border-slate-700/80 text-pink-400 mb-2.5 shadow-xs max-w-full">
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse shrink-0"></span>
                        <span class="truncate">HIGH-SPEED SERVERS · 24/7 AUTO DELIVERY</span>
                    </div>
                    <div class="flex items-center justify-between gap-2.5 mb-3.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-2xl bg-slate-800/90 text-pink-400 flex items-center justify-center text-lg md:text-xl shadow-xs border border-slate-700 shrink-0">🏪</div>
                            <h3 class="text-base md:text-lg font-black text-white tracking-tight truncate">สถานะร้าน</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-800/60 shadow-xs shrink-0">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shadow-sm shadow-emerald-400/50"></span>
                            เปิดบริการ
                        </span>
                    </div>

                    <!-- แถบข้อความวิ่ง (Marquee Ticker) โทนสีเข้มพรีเมียม -->
                    <div class="relative overflow-hidden bg-slate-900/90 border border-slate-700/60 rounded-2xl px-3.5 py-2.5 flex items-center gap-2 shadow-inner">
                        <span class="shrink-0 text-xs font-bold text-pink-400 bg-pink-500/15 px-2 py-1 rounded-xl border border-pink-500/30 flex items-center gap-1">
                            <span class="animate-pulse">📢</span>
                        </span>
                        <div class="shop-ticker-wrap overflow-hidden w-full relative">
                            <div class="shop-ticker-content flex whitespace-nowrap">
                                <span id="shopTickerText1" class="inline-flex items-center gap-4 px-2 text-xs font-semibold text-slate-200">
                                    <span>🟢 สถานะร้านเปิดบริการตามปกติ ตลอด 24 ชม.</span>
                                    <span>🚀 ยินดีต้อนรับสู่ <?= htmlspecialchars($siteSettings['site_name'] ?: 'EKROM SHOP') ?></span>
                                    <span>⚡ ระบบจัดส่งไฟล์อัตโนมัติ รวดเร็ว ใช้งานได้ทันที</span>
                                    <span>💬 สอบถามแอดมินได้ตลอดเวลา</span>
                                </span>
                                <span id="shopTickerText2" class="inline-flex items-center gap-4 px-2 text-xs font-semibold text-slate-200" aria-hidden="true">
                                    <span>🟢 สถานะร้านเปิดบริการตามปกติ ตลอด 24 ชม.</span>
                                    <span>🚀 ยินดีต้อนรับสู่ <?= htmlspecialchars($siteSettings['site_name'] ?: 'EKROM SHOP') ?></span>
                                    <span>⚡ ระบบจัดส่งไฟล์อัตโนมัติ รวดเร็ว ใช้งานได้ทันที</span>
                                    <span>💬 สอบถามแอดมินได้ตลอดเวลา</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 pt-2.5 border-t border-slate-700/60 relative z-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/90 text-indigo-300 border border-slate-700 text-xs font-bold shadow-xs whitespace-nowrap">
                        <span>👥</span> ผู้ใช้ <span id="statTotalUsers" class="text-white"><?= number_format($totalUsers) ?></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/90 text-pink-300 border border-slate-700 text-xs font-bold shadow-xs whitespace-nowrap">
                        <span>🛒</span> ขายแล้ว <span id="statTotalSales" class="text-white"><?= number_format($totalSales) ?></span>
                    </span>
                </div>
            </div>

            <!-- การ์ดสั่งซื้อไฟล์ VPN ความเร็วสูง -->
            <div class="col-span-1 md:col-span-2 p-5 md:p-6 lg:p-7 rounded-3xl shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between group gap-5 relative overflow-hidden"
                 style="background: linear-gradient(135deg, #ffffff 0%, rgba(253, 242, 248, 0.75) 45%, rgba(255, 241, 242, 0.65) 100%) !important; border: 1px solid rgba(244, 114, 182, 0.35) !important; box-shadow: 0 4px 20px -2px rgba(244, 114, 182, 0.1), 0 2px 6px rgba(15, 23, 42, 0.03) !important;">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-pink-400/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-indigo-400/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 max-w-xl">
                    <h3 class="text-slate-900 font-black text-lg md:text-xl lg:text-2xl tracking-tight flex items-center gap-2">
                        <span>สั่งซื้อไฟล์ VPN ความเร็วสูง</span>
                        <span class="text-pink-600 text-xl shrink-0">⚡</span>
                    </h3>
                </div>
                <button onclick="window.location.href='store.php'" class="w-full lg:w-auto shrink-0 bg-gradient-to-r from-pink-500 via-rose-500 to-pink-500 hover:from-pink-600 hover:to-rose-600 text-white px-7 py-3.5 rounded-2xl font-bold text-xs md:text-sm shadow-md shadow-pink-500/25 hover:shadow-lg hover:shadow-pink-500/35 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 cursor-pointer relative z-10">
                    <span>🛒 ไปที่คลังร้านค้า</span>
                    <span class="transition-transform group-hover:translate-x-1 font-black text-base">→</span>
                </button>
            </div>
        </div>

        <!-- การ์ดแจ้งเตือนไฟล์สำรองฉุกเฉิน (AIS & TRUE) -->
        <div class="mb-6 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-3xl p-4 md:p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 md:w-12 md:h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-xl md:text-2xl shrink-0 shadow-md shadow-amber-500/20">
                    ⚡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-900 text-sm md:text-base">เซิร์ฟเวอร์สำรองฉุกเฉิน (AIS & TRUE)</h3>
                        <span class="bg-amber-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full animate-pulse">ใช้งานฟรี</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">
                        หากเชื่อมต่อเซิร์ฟเวอร์หลักไม่ได้ สามารถเลือกคัดลอกโค้ดสำรองตามค่ายซิม (AIS หรือ TRUE) ไปใช้งานได้ทันที
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
                <button onclick="quickCopyBackupConfig('ais')" class="flex-1 md:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5">
                    🟢 คัดลอก AIS
                </button>
                <button onclick="quickCopyBackupConfig('true')" class="flex-1 md:flex-none bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center justify-center gap-1.5">
                    🔴 คัดลอก TRUE
                </button>
                <button onclick="openBackupModal()" class="w-full md:w-auto bg-white hover:bg-slate-50 border border-amber-300 text-amber-800 px-3.5 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5">
                    🔍 รายละเอียด & QR
                </button>
            </div>
        </div>

        <div id="announcementArea" class="hidden mb-6 space-y-3"></div>
        <div id="my-files" class="scroll-mt-20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 md:mb-4">
            <h2 class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2"><span class="text-pink-600">📁</span> รายการเซิร์ฟเวอร์ของคุณ</h2>
            <div class="flex items-center gap-3">
                <span id="vpnResultCount" class="text-[11px] md:text-xs font-bold text-slate-400">กำลังโหลดรายการ...</span>
                <button onclick="refreshDashboard(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                    <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                </button>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-3 md:p-4 mb-4 md:mb-6 shadow-sm">
            <div class="flex flex-col md:flex-row gap-2 md:gap-3">
                <label class="relative flex-1 min-w-0">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base" aria-hidden="true">⌕</span>
                    <input id="vpnSearchInput" type="search" autocomplete="off" placeholder="ค้นหาชื่อไฟล์ / เซิร์ฟเวอร์ / แพ็กเกจ / UUID" oninput="scheduleVpnFilter()" class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 outline-none focus:bg-white focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 transition-all">
                </label>
                <label class="md:w-48 shrink-0">
                    <span class="sr-only">กรองตามสถานะ</span>
                    <select id="vpnStatusFilter" onchange="applyVpnFilters()" class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl px-3 text-sm text-slate-700 font-bold outline-none focus:bg-white focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 transition-all">
                        <option value="all">แสดงทั้งหมด</option>
                        <option value="active">ยังใช้งานอยู่</option>
                        <option value="expired">หมดอายุแล้ว</option>
                    </select>
                </label>
            </div>
            <p class="text-[10px] md:text-xs text-slate-400 mt-2 px-1">ค้นหาได้ทันทีโดยไม่ต้องเลื่อนหาไฟล์ทีละรายการ</p>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 md:gap-6" id="vpn-list">
            <div class="skeleton-card"><div class="skeleton skeleton-line short"></div><div class="skeleton skeleton-line mid"></div><div class="skeleton skeleton-line"></div></div><div class="skeleton-card"><div class="skeleton skeleton-line short"></div><div class="skeleton skeleton-line mid"></div><div class="skeleton skeleton-line"></div></div>
        </div>
    </main>

    <!-- Modal รายละเอียด -->
    <div id="detailModal" onclick="if(event.target === this) closeDetail()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl animate-in fade-in zoom-in duration-300 flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-6 md:p-8 bg-slate-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                <div class="min-w-0 pr-4">
                    <h2 id="modalTitle" class="text-xl md:text-2xl font-bold text-slate-900 truncate">รายละเอียดไฟล์</h2>
                    <p id="modalPkg" class="text-pink-600 font-bold text-xs md:text-sm mt-1 truncate">--</p>
                </div>
                <button onclick="closeDetail()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            <div class="p-6 md:p-8 overflow-y-auto hide-scroll flex-grow">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">สถานะ</p>
                        <p id="modalStatusText" class="font-bold text-xs md:text-sm truncate">--</p>
                    </div>
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">ดาวน์โหลด</p>
                        <p id="modalDownload" class="font-bold text-slate-900 text-xs md:text-sm truncate transition-all duration-300">0 MB</p>
                    </div>
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">อัปโหลด</p>
                        <p id="modalUpload" class="font-bold text-slate-900 text-xs md:text-sm truncate transition-all duration-300">0 MB</p>
                    </div>
                    <div class="bg-orange-50 p-3 md:p-4 rounded-2xl border border-orange-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-orange-500 font-bold uppercase mb-1">เวลาคงเหลือ</p>
                        <p id="modalDaysLeft" class="font-bold text-orange-600 text-[11px] md:text-[13px] whitespace-nowrap tracking-tight">--</p>
                    </div>
                </div>

                <div id="expiredWarningContainer" class="hidden mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 flex-col md:flex-row items-start md:items-center justify-between gap-3 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 text-6xl opacity-10">⚠️</div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-bold text-red-600 flex items-center gap-2"><span class="animate-pulse w-2 h-2 bg-red-600 rounded-full"></span> ไฟล์นี้หมดอายุแล้ว!</h3>
                        <p class="text-[11px] md:text-xs text-red-500 mt-1" id="warningSubText">ระบบจะลบไฟล์นี้ออกจากระบบอัตโนมัติ</p>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-xl border border-red-100 shadow-sm relative z-10 shrink-0 w-full md:w-auto text-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase mb-0.5">สถานะการลบ</p>
                        <p id="deleteCountdown" class="font-bold text-red-600 text-sm font-mono tracking-widest">--:--:--</p>
                    </div>
                </div>

                <div id="qrContainer" class="hidden mb-6 flex flex-col items-center p-5 md:p-6 bg-slate-50/90 border border-slate-200 rounded-3xl shadow-sm transition-all duration-300">
                    <div class="flex items-center justify-between w-full mb-3 px-1">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-pulse"></span> QR Code เชื่อมต่อ (Scan Config)
                        </span>
                    </div>

                    <!-- Large & Ultra-Crisp QR Code Box -->
                    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-md border border-gray-200 flex items-center justify-center w-[250px] h-[250px] sm:w-[290px] sm:h-[290px] max-w-full overflow-hidden">
                        <div id="qrSvgWrapper" class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>svg]:max-w-full [&>svg]:max-h-full"></div>
                        <img id="qrImage" src="" alt="QR Code" class="w-full h-full object-contain hidden" style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges;">
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <p class="text-[11px] md:text-xs text-slate-600 font-semibold text-center bg-white border border-slate-200 px-4 py-1.5 rounded-full shadow-xs">
                            📷 เปิดแอป VPN หรือกล้องมือถือแล้วสแกนได้ทันที
                        </p>
                    </div>
                </div>

                <div class="mb-5" id="configSection">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            🔗 ข้อมูลการเชื่อมต่อ / Config
                        </label>
                        <button type="button" onclick="toggleConfigBox()" id="btnToggleConfig" class="text-[11px] font-bold text-slate-500 hover:text-pink-600 flex items-center gap-1 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition-all" title="ย่อหรือขยายขนาดกล่อง">
                            <span id="toggleConfigIcon">↕️</span> <span id="toggleConfigText">ย่อ/ขยาย</span>
                        </button>
                    </div>
                    
                    <!-- 🟢 ตัวเลือกแอป (แสดงเฉพาะระบบ SSH) -->
                    <div id="sshAppSelector" class="hidden grid grid-cols-2 gap-2 mb-3">
                        <button onclick="showConfigFormat('npv')" id="btnAppNpv" class="bg-slate-50 border border-slate-200 text-slate-500 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700">
                            <span class="text-base">🛡️</span> NPV Tunnel
                        </button>
                        <button onclick="showConfigFormat('netmod')" id="btnAppNetmod" class="bg-slate-50 border border-slate-200 text-slate-500 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5 hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700">
                            <span class="text-base">🔥</span> NetMod
                        </button>
                    </div>

                    <div id="sshConfigVariants" class="hidden mb-3 space-y-2"></div>

                    <!-- กล่อง Config ขนาดกะทัดรัด (Compact) และย่อ/ขยายได้ -->
                    <textarea id="modalConfig" readonly class="w-full bg-slate-900 text-emerald-400 p-2.5 sm:p-3 rounded-xl h-14 border border-slate-800 resize-none font-mono focus:outline-none transition-all duration-300" style="font-size: 10px; line-height: 1.45; letter-spacing: -0.01em;"></textarea>
                    
                    <div id="genericConfigActions" class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2.5">
                        <button onclick="copyConfig()" class="bg-slate-800 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-slate-700 active:scale-[0.98] transition-all shadow-sm flex items-center justify-center gap-1.5">📋 คัดลอกไฟล์</button>
                        <button onclick="downloadConfigFile()" class="bg-emerald-600 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-emerald-500 active:scale-[0.98] transition-all shadow-sm shadow-emerald-500/20 flex items-center justify-center gap-1.5">📥 ดาวน์โหลด</button>
                        <button id="btnQrCode" onclick="toggleQRCode()" class="bg-pink-600 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-pink-500 active:scale-[0.98] transition-all shadow-sm shadow-pink-500/30 flex items-center justify-center gap-1.5 col-span-2 sm:col-span-1">📱 QR Code</button>
                    </div>
                </div>

                <div id="renewContainer" class="border-t border-gray-100 pt-6">
                    <p class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between gap-2">
                        <span class="flex items-center gap-2"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> ต่ออายุการใช้งาน (เพิ่มวัน)</span>
                        <span id="renewResellerBadge" class="hidden text-[10px] font-bold bg-pink-100 text-pink-700 px-2.5 py-0.5 rounded-full border border-pink-200">ลด 30% ตัวแทน</span>
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <button onclick="renewVPN(1)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-2.5 px-2 rounded-xl font-bold transition-all shadow-sm flex flex-col items-center justify-center">
                            <span class="text-base md:text-lg">1 วัน</span>
                            <span id="renewPriceText1" class="text-xs text-emerald-600 font-semibold mt-0.5">฿5</span>
                        </button>
                        <button onclick="renewVPN(7)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-2.5 px-2 rounded-xl font-bold transition-all shadow-sm flex flex-col items-center justify-center">
                            <span class="text-base md:text-lg">7 วัน</span>
                            <span id="renewPriceText7" class="text-xs text-emerald-600 font-semibold mt-0.5">฿25</span>
                        </button>
                        <button onclick="renewVPN(15)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-2.5 px-2 rounded-xl font-bold transition-all shadow-sm flex flex-col items-center justify-center">
                            <span class="text-base md:text-lg">15 วัน</span>
                            <span id="renewPriceText15" class="text-xs text-emerald-600 font-semibold mt-0.5">฿45</span>
                        </button>
                        <button onclick="renewVPN(30)" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-100 py-2.5 px-2 rounded-xl font-bold transition-all shadow-sm flex flex-col items-center justify-center">
                            <span class="text-base md:text-lg">30 วัน</span>
                            <span id="renewPriceText30" class="text-xs text-emerald-700 font-semibold mt-0.5">฿80</span>
                        </button>
                    </div>
                </div>

                <div id="deleteContainer" class="hidden border-t border-red-100 mt-6 pt-4 pb-2 space-y-2">
                    <button onclick="switchServer()" id="btnSwitchServer" class="hidden w-full bg-purple-50 text-purple-600 hover:bg-purple-500 hover:text-white py-3 rounded-xl text-xs font-bold transition-all shadow-sm">🔄 ย้ายเซิร์ฟเวอร์ (สำหรับตัวแทน)</button>
                    <button onclick="deleteVPN()" id="btnDeleteVPN" class="w-full bg-red-50 text-red-600 hover:bg-red-500 hover:text-white py-3 rounded-xl text-xs font-bold transition-all shadow-sm">🗑️ ลบไฟล์นี้ออกจากระบบถาวร</button>
                    <p id="refundNotice" class="text-center text-[10px] text-emerald-600 font-bold mt-2">💡 ลบภายใน 10 นาที คืนเงินเต็ม 100% หรือคืนเงินตามเวลาคงเหลือที่ไม่ได้ใช้</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal โปรไฟล์ -->
    <div id="profileModal" onclick="if(event.target === this) closeProfile()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl animate-in fade-in zoom-in duration-300 flex flex-col overflow-hidden">
            <div class="p-6 md:p-8 bg-slate-50 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl md:text-2xl font-bold text-slate-900">โปรไฟล์ของคุณ</h2>
                <button onclick="closeProfile()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            <div class="p-6 md:p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center text-3xl font-bold text-pink-600 border-4 border-pink-50 shadow-sm shrink-0">👤</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-400 font-bold uppercase">ชื่อผู้ใช้ (Username)</p>
                        <p id="profileUsername" class="font-bold text-lg md:text-xl text-slate-900 truncate">--</p>
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2"><span class="w-2 h-2 bg-slate-900 rounded-full"></span> เปลี่ยนรหัสผ่าน</h3>
                    <div class="space-y-3">
                        <input type="password" id="oldPwd" placeholder="รหัสผ่านเดิม" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 transition-all">
                        <input type="password" id="newPwd" placeholder="รหัสผ่านใหม่" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 transition-all">
                        <input type="password" id="confirmPwd" placeholder="ยืนยันรหัสผ่านใหม่" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 transition-all">
                        <button onclick="changePassword()" id="btnChangePwd" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-pink-600 transition-all mt-2 shadow-lg">บันทึกรหัสผ่านใหม่</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ไฟล์สำรอง -->
    <div id="backupModal" onclick="if(event.target === this) closeBackupModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl animate-in fade-in zoom-in duration-300 flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-6 md:p-8 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-transparent border-b border-gray-100 flex justify-between items-center shrink-0">
                <div class="min-w-0 pr-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">⚡</span>
                        <h2 class="text-xl md:text-2xl font-bold text-slate-900 truncate">ไฟล์คอนฟิกสำรอง (AIS & TRUE)</h2>
                    </div>
                    <p class="text-amber-700 font-bold text-xs md:text-sm">โปรโตคอล VLESS WebSocket • เลือกค่ายซิมเพื่อรับโค้ด</p>
                </div>
                <button onclick="closeBackupModal()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            <div class="p-6 md:p-8 overflow-y-auto hide-scroll flex-grow">
                <!-- แท็บเลือกค่าย AIS / TRUE -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <button type="button" onclick="switchBackupCarrier('ais')" id="btnCarrierAis" class="p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-50 text-emerald-800 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all shadow-sm">
                        <span class="text-lg">🟢</span>
                        <span>AIS 5G (Play)</span>
                    </button>
                    <button type="button" onclick="switchBackupCarrier('true')" id="btnCarrierTrue" class="p-3.5 rounded-2xl border-2 border-slate-200 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-700 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all">
                        <span class="text-lg">🔴</span>
                        <span>TRUE 5G (WeTV)</span>
                    </button>
                </div>

                <!-- กล่อง QR Code -->
                <div id="backupQrContainer" class="hidden mb-6 flex flex-col items-center p-5 md:p-6 bg-amber-50/50 border border-amber-200 rounded-3xl shadow-sm transition-all duration-300">
                    <div class="flex items-center justify-between w-full mb-3 px-1">
                        <span id="backupQrTitle" class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> สแกน QR Code สำหรับ AIS
                        </span>
                    </div>
                    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-md border border-gray-200 flex items-center justify-center w-[250px] h-[250px] sm:w-[280px] sm:h-[280px] max-w-full overflow-hidden">
                        <div id="backupQrSvgWrapper" class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>svg]:max-w-full [&>svg]:max-h-full"></div>
                    </div>
                    <p class="text-[11px] md:text-xs text-slate-600 font-semibold text-center mt-3 bg-white border border-slate-200 px-4 py-1.5 rounded-full shadow-xs">
                        📷 เปิดแอป VPN แล้วเลือกสแกน QR Code เพื่อนำเข้าได้ทันที
                    </p>
                </div>

                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2 gap-2 flex-wrap">
                        <label id="backupConfigLabel" class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">🔗 โค้ดคอนฟิกสำรอง AIS (VLESS URI)</label>
                        <div class="flex items-center gap-2 shrink-0">
                            <span id="backupCarrierBadge" class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-200">AIS 5G</span>
                            <button type="button" onclick="toggleBackupConfigBox()" id="btnToggleBackupConfig" class="text-[11px] font-bold text-slate-500 hover:text-pink-600 flex items-center gap-1 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition-all" title="ย่อหรือขยายขนาดกล่อง">
                                <span id="toggleBackupConfigIcon">↕️</span> <span id="toggleBackupConfigText">ย่อ/ขยาย</span>
                            </button>
                        </div>
                    </div>
                    
                    <textarea id="backupConfigText" readonly class="w-full bg-slate-900 text-amber-400 p-2.5 sm:p-3 rounded-xl h-14 border border-slate-800 resize-none font-mono focus:outline-none transition-all duration-300 select-all" style="font-size: 10px; line-height: 1.45; letter-spacing: -0.01em;"></textarea>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2.5">
                        <button onclick="copyBackupConfig()" id="btnCopyBackup" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold active:scale-[0.98] transition-all shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                            📋 คัดลอกโค้ด
                        </button>
                        <button onclick="downloadBackupFile()" class="bg-slate-700 hover:bg-slate-600 text-white py-2.5 rounded-xl text-xs font-bold active:scale-[0.98] transition-all shadow-sm flex items-center justify-center gap-1.5">
                            📥 ดาวน์โหลด .txt
                        </button>
                        <button onclick="toggleBackupQRCode()" class="bg-slate-900 hover:bg-slate-800 text-white py-2.5 rounded-xl text-xs font-bold active:scale-[0.98] transition-all shadow-sm flex items-center justify-center gap-1.5 col-span-2 sm:col-span-1">
                            📱 QR Code
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 md:p-5">
                    <h4 class="font-bold text-slate-800 text-xs md:text-sm mb-2.5 flex items-center gap-1.5">
                        <span>💡</span> วิธีนำไปใช้งานในแอป (3 ขั้นตอนง่ายๆ)
                    </h4>
                    <ol class="text-xs text-slate-600 space-y-2 list-decimal list-inside leading-relaxed">
                        <li>กดเลือกค่ายซิมของคุณ (<strong class="text-emerald-700">AIS</strong> หรือ <strong class="text-rose-700">TRUE</strong>) ด้านบน</li>
                        <li>กดปุ่ม <strong class="text-slate-800">"คัดลอกโค้ด"</strong></li>
                        <li>เปิดแอป VPN ในมือถือ (เช่น <strong>v2rayNG</strong> สำหรับ Android หรือ <strong>Shadowrocket</strong> สำหรับ iOS)</li>
                        <li>กดเครื่องหมาย <strong>+</strong> ในแอป แล้วเลือก <strong>"นำเข้าจากคลิปบอร์ด" (Import config from clipboard)</strong> แล้วกดเชื่อมต่อ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                document.body.classList.add('drawer-open');
                menu.classList.remove('hidden');
                setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0');
                drawer.classList.add('-translate-x-full');
                document.body.classList.remove('drawer-open');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        function openProfile() {
            document.body.classList.add('modal-open');
            document.getElementById('profileModal').classList.add('modal-active');
        }
        function closeProfile() {
            document.getElementById('profileModal').classList.remove('modal-active');
            if (!document.getElementById('detailModal')?.classList.contains('modal-active') && !document.getElementById('backupModal')?.classList.contains('modal-active')) {
                document.body.classList.remove('modal-open');
            }
        }

        const BACKUP_CONFIGS = {
            ais: {
                name: 'AIS 5G',
                file: 'backup_vless_ais.txt',
                color: 'emerald',
                config: "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@aisplay.ais.co.th:80?path=%2F&encryption=none&security=none&host=d2qcpub79wb31.cloudfront.net&type=ws#ais%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99"
            },
            true: {
                name: 'TRUE 5G',
                file: 'backup_vless_true.txt',
                color: 'rose',
                config: "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@debugaccess.wetv.vip:80?type=ws&path=%2F&encryption=none&host=d2qcpub79wb31.cloudfront.net&security=none#%E0%B8%97%E0%B8%A3%E0%B8%B9%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99"
            }
        };

        let currentBackupCarrier = 'ais';

        function switchBackupCarrier(carrier) {
            currentBackupCarrier = carrier;
            const item = BACKUP_CONFIGS[carrier] || BACKUP_CONFIGS.ais;
            const btnAis = document.getElementById('btnCarrierAis');
            const btnTrue = document.getElementById('btnCarrierTrue');
            const txt = document.getElementById('backupConfigText');
            const label = document.getElementById('backupConfigLabel');
            const badge = document.getElementById('backupCarrierBadge');
            const btnCopy = document.getElementById('btnCopyBackup');
            const qrTitle = document.getElementById('backupQrTitle');

            if (carrier === 'ais') {
                if (btnAis) btnAis.className = "p-3.5 rounded-2xl border-2 border-emerald-500 bg-emerald-50 text-emerald-800 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all shadow-sm";
                if (btnTrue) btnTrue.className = "p-3.5 rounded-2xl border-2 border-slate-200 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-700 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all";
                if (badge) {
                    badge.className = "text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-200";
                    badge.innerText = "AIS 5G";
                }
                if (btnCopy) {
                    btnCopy.className = "bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold active:scale-[0.98] transition-all shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-1.5";
                }
                if (label) label.innerText = "🔗 โค้ดคอนฟิกสำรอง AIS (VLESS URI)";
                if (qrTitle) qrTitle.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> สแกน QR Code สำหรับ AIS';
            } else {
                if (btnTrue) btnTrue.className = "p-3.5 rounded-2xl border-2 border-rose-500 bg-rose-50 text-rose-800 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all shadow-sm";
                if (btnAis) btnAis.className = "p-3.5 rounded-2xl border-2 border-slate-200 bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 font-extrabold text-xs md:text-sm flex items-center justify-center gap-2 transition-all";
                if (badge) {
                    badge.className = "text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200";
                    badge.innerText = "TRUE 5G";
                }
                if (btnCopy) {
                    btnCopy.className = "bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-xl text-xs font-bold active:scale-[0.98] transition-all shadow-sm shadow-rose-600/20 flex items-center justify-center gap-1.5";
                }
                if (label) label.innerText = "🔗 โค้ดคอนฟิกสำรอง TRUE (VLESS URI)";
                if (qrTitle) qrTitle.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span> สแกน QR Code สำหรับ TRUE';
            }

            if (txt) txt.value = item.config;

            const qrContainer = document.getElementById('backupQrContainer');
            if (qrContainer && !qrContainer.classList.contains('hidden')) {
                renderBackupQRCode(item.config);
            }
        }

        function toggleBackupConfigBox() {
            const el = document.getElementById('backupConfigText');
            const icon = document.getElementById('toggleBackupConfigIcon');
            const text = document.getElementById('toggleBackupConfigText');
            if (!el) return;

            if (el.classList.contains('h-14')) {
                el.classList.remove('h-14');
                el.classList.add('h-36');
                if (icon) icon.innerText = '🔼';
                if (text) text.innerText = 'ย่อลง';
            } else {
                el.classList.remove('h-36');
                el.classList.add('h-14');
                if (icon) icon.innerText = '🔽';
                if (text) text.innerText = 'ขยาย';
            }
        }

        function openBackupModal(carrier = 'ais') {
            document.body.classList.add('modal-open');
            document.getElementById('backupModal').classList.add('modal-active');
            const bBox = document.getElementById('backupConfigText');
            if (bBox) {
                bBox.classList.remove('h-36');
                bBox.classList.add('h-14');
            }
            const bIcon = document.getElementById('toggleBackupConfigIcon');
            const bText = document.getElementById('toggleBackupConfigText');
            if (bIcon) bIcon.innerText = '↕️';
            if (bText) bText.innerText = 'ย่อ/ขยาย';
            switchBackupCarrier(carrier);
        }

        function closeBackupModal() {
            document.getElementById('backupModal').classList.remove('modal-active');
            if (!document.getElementById('detailModal')?.classList.contains('modal-active') && !document.getElementById('profileModal')?.classList.contains('modal-active')) {
                document.body.classList.remove('modal-open');
            }
        }

        function quickCopyBackupConfig(carrier = 'ais') {
            const item = BACKUP_CONFIGS[carrier] || BACKUP_CONFIGS.ais;
            copyTextToClipboard(item.config).then(() => {
                Toast.fire({ icon: 'success', title: `คัดลอกโค้ดสำรอง ${item.name} สำเร็จ! นำไปวางในแอปได้ทันที` });
            }).catch(() => {
                openBackupModal(carrier);
            });
        }

        function copyBackupConfig() {
            const item = BACKUP_CONFIGS[currentBackupCarrier] || BACKUP_CONFIGS.ais;
            const ta = document.getElementById('backupConfigText');
            const val = ta ? ta.value : item.config;
            copyTextToClipboard(val).then(() => {
                Toast.fire({ icon: 'success', title: `คัดลอกโค้ดสำรอง ${item.name} สำเร็จ!` });
            }).catch(() => {
                if (ta) { ta.focus(); ta.select(); }
                Toast.fire({ icon: 'error', title: 'คัดลอกไม่สำเร็จ กรุณากดค้างที่ข้อความเพื่อคัดลอก' });
            });
        }

        function downloadBackupFile() {
            const item = BACKUP_CONFIGS[currentBackupCarrier] || BACKUP_CONFIGS.ais;
            const blob = new Blob([item.config], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = item.file;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            Toast.fire({ icon: 'success', title: `ดาวน์โหลดไฟล์สำรอง ${item.name} สำเร็จ!` });
        }

        function renderBackupQRCode(configStr) {
            const svgWrapper = document.getElementById('backupQrSvgWrapper');
            if (typeof qrcode === 'function' && svgWrapper) {
                try {
                    let qr;
                    try {
                        qr = qrcode(0, 'M');
                        qr.addData(configStr);
                        qr.make();
                    } catch (e1) {
                        qr = qrcode(0, 'L');
                        qr.addData(configStr);
                        qr.make();
                    }
                    svgWrapper.innerHTML = qr.createSvgTag({ scalable: true, margin: 2 });
                } catch (e) {
                    console.error('QR code generation error:', e);
                }
            }
        }

        function toggleBackupQRCode() {
            const container = document.getElementById('backupQrContainer');
            if (!container.classList.contains('hidden')) {
                container.classList.add('hidden');
                return;
            }
            container.classList.remove('hidden');
            const item = BACKUP_CONFIGS[currentBackupCarrier] || BACKUP_CONFIGS.ais;
            renderBackupQRCode(item.config);
        }

        function parseShopDate(dateStr) {
            if (!dateStr) return new Date(NaN);
            // Database DATETIME is stored in Asia/Bangkok. Add the offset
            // explicitly so a customer's device timezone cannot expire it early.
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(dateStr)) {
                return new Date(dateStr.replace(' ', 'T') + '+07:00');
            }
            return new Date(dateStr);
        }

        function formatThaiDateTime(dateStr) {
            if (!dateStr) return '--';
            const d = parseShopDate(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear() + 543} เวลา ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} น.`;
        }

        function getDetailedTimeLeft(expireStr) {
            if (!expireStr) return '--';
            const diff = parseShopDate(expireStr) - new Date();
            if (diff <= 0) return 'หมดอายุแล้ว';
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const mins = Math.floor((diff / 1000 / 60) % 60);
            let res = [];
            if (days > 0) res.push(`${days} วัน`);
            if (hours > 0) res.push(`${hours} ชม.`);
            if (mins > 0) res.push(`${mins} นาที`);
            return res.length === 0 ? 'น้อยกว่า 1 นาที' : res.join(' ');
        }

        function startDeleteCountdown(expireStr, isSSH) {
            clearInterval(deleteCountdownInterval);
            const warningBox = document.getElementById('expiredWarningContainer');
            const countdownEl = document.getElementById('deleteCountdown');
            const subText = document.getElementById('warningSubText');
            countdownEl.innerText = "กำลังลบบัญชี...";
            subText.innerText = "ระบบจะลบบัญชีออกจากเซิร์ฟเวอร์และฐานข้อมูลโดยอัตโนมัติหลังหมดอายุ";
            warningBox.classList.remove('hidden'); warningBox.classList.add('flex');
        }

        function stopDeleteCountdown() {
            clearInterval(deleteCountdownInterval);
            document.getElementById('expiredWarningContainer').classList.replace('flex', 'hidden');
        }

        async function refreshDashboard(btn) {
            const icon = btn ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;
            try {
                await Promise.all([loadUserInfo(), loadVPNList(), loadAnnouncements()]);
                if (btn) {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชข้อมูลแล้ว' });
                }
            } catch (e) {
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php', { cache: 'no-store' });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('userBalanceDesk').innerText = data.balance;
                    document.getElementById('userBalanceMob').innerText = data.balance;
                    const nameToShow = data.line_display_name ? `${data.line_display_name} (${data.username})` : data.username;
                    if (document.getElementById('userNameDisplay')) document.getElementById('userNameDisplay').innerText = data.line_display_name || data.username;
                    if (document.getElementById('profileUsername')) document.getElementById('profileUsername').innerText = nameToShow;
                    if (document.getElementById('statTotalUsers') && data.total_users !== undefined) {
                        document.getElementById('statTotalUsers').innerText = Number(data.total_users).toLocaleString();
                    }
                    if (document.getElementById('statTotalSales') && data.total_sales !== undefined) {
                        document.getElementById('statTotalSales').innerText = Number(data.total_sales).toLocaleString();
                    }
                    if (data.role === 'reseller') {
                        isUserReseller = true;
                        if (data.reseller_discount_percent !== undefined) {
                            resellerDiscountPercent = Number(data.reseller_discount_percent);
                        }
                        const badge = document.getElementById('renewResellerBadge');
                        if (badge) {
                            badge.innerText = `ลด ${resellerDiscountPercent}% ตัวแทน`;
                            badge.classList.remove('hidden');
                        }
                    }
                }
            } catch (e) {}
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        }

        function getVpnBadgeId(uuid) {
            return `vpn-badge-${String(uuid ?? '').replace(/[^a-zA-Z0-9_-]/g, '_')}`;
        }

        function getVpnBadgeMeta(data) {
            if (!data) return { className: 'px-2 py-1 bg-slate-100 text-slate-500 border-slate-200 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: '⏳ โหลด...' };
            if (data.status === 'error') return { className: 'px-2 py-1 bg-red-50 text-red-600 border-red-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'เชื่อมต่อล้มเหลว' };
            if (data.real_status === 'active') return { className: 'px-2 py-1 bg-green-50 text-green-600 border-green-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shadow-sm shadow-green-500/20 shrink-0', text: '🟢 ใช้งานได้' };
            if (data.real_status === 'expired') return { className: 'px-2 py-1 bg-orange-50 text-orange-600 border-orange-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: '🔴 หมดอายุแล้ว' };
            if (data.real_status === 'unknown') return { className: 'px-2 py-1 bg-amber-50 text-amber-700 border-amber-200 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'ตรวจสอบวันหมดอายุ' };
            return { className: 'px-2 py-1 bg-slate-100 text-slate-500 border-slate-300 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'ไม่พบในระบบ' };
        }

        function isVpnExpired(item) {
            const expiry = parseShopDate(item?.expiry_time);
            return !isNaN(expiry.getTime()) && expiry.getTime() <= Date.now();
        }

        function renderVpnCard(item) {
            const key = String(item.uuid);
            if (isVpnExpired(item) && !vpnTrafficStates[key]) {
                vpnTrafficStates[key] = { status: 'success', real_status: 'expired' };
            }
            const badge = getVpnBadgeMeta(vpnTrafficStates[key]);
            const badgeId = getVpnBadgeId(item.uuid);
            const serverName = escapeHtml(item.server_name || 'ไม่ระบุชื่อเซิร์ฟเวอร์');
            const packageName = escapeHtml(item.package_name || 'ไม่ระบุแพ็กเกจ');
            const expiry = escapeHtml(formatThaiDateTime(item.expiry_time));
            const id = escapeHtml(item.id);
            return `<article class="bg-white p-3 md:p-6 rounded-2xl md:rounded-[24px] shadow-sm border border-gray-200 hover:border-pink-300 vpn-card transition-all flex flex-col cursor-pointer" data-vpn-id="${id}" role="button" tabindex="0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm md:text-xl font-bold text-slate-900 truncate">${serverName}</h3>
                        <p class="text-[10px] md:text-xs text-pink-600 font-bold truncate mt-0.5">📦 ${packageName}</p>
                    </div>
                    <span id="${badgeId}" class="${badge.className}">${badge.text}</span>
                </div>

                <div class="mt-2 md:mt-4 pt-2 md:pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase truncate"><span class="md:hidden">หมดอายุ</span><span class="hidden md:inline">⏱️ หมดอายุเวลา</span></p>
                        <p class="text-[11px] md:text-sm font-bold text-slate-800 truncate">${expiry}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase">เหลือ</p>
                        <p class="text-[10px] md:text-sm font-bold text-orange-500 whitespace-nowrap">${escapeHtml(getDetailedTimeLeft(item.expiry_time))}</p>
                    </div>
                </div>

                <button type="button" class="w-full mt-3 md:mt-5 bg-pink-50 text-pink-600 py-2.5 md:py-3 rounded-xl font-bold text-xs md:text-sm hover:bg-pink-600 hover:text-white transition-all shadow-sm"><span class="sm:hidden">ดูไฟล์</span><span class="hidden sm:inline">ดูรายละเอียดไฟล์</span><span class="ml-1" aria-hidden="true">→</span></button>
            </article>`;
        }

        function renderVpnCards(items) {
            const vpnContainer = document.getElementById('vpn-list');
            vpnContainer.innerHTML = items.map(renderVpnCard).join('');
            vpnContainer.querySelectorAll('[data-vpn-id]').forEach(card => {
                const open = () => openDetailById(card.dataset.vpnId);
                card.addEventListener('click', open);
                card.addEventListener('keydown', event => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        open();
                    }
                });
            });
            enqueueVpnTraffic(items);
        }

        function applyVpnFilters() {
            const vpnContainer = document.getElementById('vpn-list');
            const searchInput = document.getElementById('vpnSearchInput');
            const statusFilter = document.getElementById('vpnStatusFilter');
            const query = String(searchInput?.value || '').trim().toLowerCase();
            const status = statusFilter?.value || 'all';
            const visibleItems = vpnItems.filter(item => {
                const searchable = [item.server_name, item.package_name, item.uuid, item.username, item.id, item.config_name, item.name].filter(Boolean).join(' ').toLowerCase();
                if (query && !searchable.includes(query)) return false;
                if (status === 'expired' && !isVpnExpired(item)) return false;
                if (status === 'active' && isVpnExpired(item)) return false;
                return true;
            });

            const count = document.getElementById('vpnResultCount');
            if (count) count.innerText = `แสดง ${visibleItems.length} จาก ${vpnItems.length} ไฟล์`;

            if (!vpnItems.length) {
                vpnContainer.innerHTML = '<p class="col-span-full text-center text-gray-400 py-8 md:py-10 bg-white rounded-2xl md:rounded-[24px] border border-gray-100">คุณยังไม่มีไฟล์เซิร์ฟเวอร์ในขณะนี้</p>';
            } else if (!visibleItems.length) {
                vpnContainer.innerHTML = '<div class="col-span-full text-center py-8 md:py-10 px-4 bg-white rounded-2xl md:rounded-[24px] border border-dashed border-slate-200"><div class="text-3xl mb-2">🔎</div><p class="font-bold text-slate-700">ไม่พบไฟล์ที่ตรงกับการค้นหา</p><p class="text-xs text-slate-400 mt-1">ลองเปลี่ยนคำค้นหาหรือเลือก “แสดงทั้งหมด”</p></div>';
            } else {
                renderVpnCards(visibleItems);
            }
        }

        function scrollToMyFiles() {
            const el = document.getElementById('my-files');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function scheduleVpnFilter() {
            clearTimeout(vpnFilterTimer);
            vpnFilterTimer = setTimeout(applyVpnFilters, 120);
        }

        function updateVpnBadge(uuid) {
            const badge = document.getElementById(getVpnBadgeId(uuid));
            if (!badge) return;
            const meta = getVpnBadgeMeta(vpnTrafficStates[String(uuid)]);
            badge.className = meta.className;
            badge.innerText = meta.text;
        }

        function loadVpnTraffic(item) {
            return fetch(`api/get_traffic.php?uuid=${encodeURIComponent(item.uuid)}&server=${encodeURIComponent(item.server_name)}`)
                .then(response => response.json())
                .then(data => {
                    vpnTrafficStates[String(item.uuid)] = data;
                    updateVpnBadge(item.uuid);
                })
                .catch(() => {
                    vpnTrafficStates[String(item.uuid)] = { status: 'error' };
                    updateVpnBadge(item.uuid);
                });
        }

        function enqueueVpnTraffic(items) {
            items.forEach(item => {
                const key = String(item.uuid);
                if (Object.prototype.hasOwnProperty.call(vpnTrafficStates, key) || vpnTrafficQueued.has(key)) return;
                if (isVpnExpired(item)) {
                    vpnTrafficStates[key] = { status: 'success', real_status: 'expired' };
                    updateVpnBadge(item.uuid);
                    return;
                }
                vpnTrafficQueued.add(key);
                vpnTrafficQueue.push(item);
            });
            processVpnTrafficQueue();
        }

        function processVpnTrafficQueue() {
            while (vpnTrafficActive < 4 && vpnTrafficQueue.length) {
                const item = vpnTrafficQueue.shift();
                const key = String(item.uuid);
                vpnTrafficQueued.delete(key);
                if (Object.prototype.hasOwnProperty.call(vpnTrafficStates, key)) {
                    updateVpnBadge(item.uuid);
                    continue;
                }
                vpnTrafficActive++;
                loadVpnTraffic(item).finally(() => {
                    vpnTrafficActive--;
                    processVpnTrafficQueue();
                });
            }
        }

        function openDetailById(id) {
            const item = vpnItemsById.get(String(id));
            if (!item) return;
            openDetail(item.id, item.uuid, item.server_name, item.package_name, item.expiry_time, encodeURIComponent(item.config_link || ''));
        }

        async function loadVPNList() {
            const vpnContainer = document.getElementById('vpn-list');
            try {
                const res = await fetch('api/get_vpn_list.php', { cache: 'no-store' });
                const result = await res.json();
                if (result.status !== 'success') throw new Error(result.message || 'load_failed');

                vpnItems = Array.isArray(result.data) ? result.data : [];
                vpnItemsById.clear();
                vpnItems.forEach(item => vpnItemsById.set(String(item.id), item));
                applyVpnFilters();
            } catch (e) {
                vpnItems = [];
                vpnItemsById.clear();
                const count = document.getElementById('vpnResultCount');
                if (count) count.innerText = 'โหลดรายการไม่สำเร็จ';
                vpnContainer.innerHTML = '<p class="col-span-full text-center text-red-500 py-8 md:py-10 bg-white rounded-2xl md:rounded-3xl border border-gray-100">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>';
            }
        }

        function formatBytes(bytes) {
            bytes = Number(bytes) || 0;
            if (bytes <= 0) return '0 MB';
            if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return bytes + ' B';
        }

        function openDetail(id, uuid, title, pkg, expire, encodedConfig) {
            current_opened_id = id;
            stopDeleteCountdown();

            const configStr = decodeURIComponent(encodedConfig);
            let parsedConfig = {};
            let isSSH = false;

            // ตรวจสอบว่าเป็น JSON (ระบบ SSH ใหม่) หรือ Text ธรรมดา (V2Ray / SSH เก่า)
            try {
                parsedConfig = JSON.parse(configStr);
                isSSH = true;
            } catch(e) {
                parsedConfig = { raw: configStr };
                isSSH = configStr.startsWith('npvt-ssh://') || configStr.startsWith('IP:');
            }

            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalPkg').innerHTML = `<span class="text-pink-600">${pkg}</span> <span class="mx-1.5 text-gray-300 font-normal">|</span> <span class="text-[10px] md:text-xs text-gray-500 font-normal">หมดอายุ: ${formatThaiDateTime(expire)}</span>`;

            const npvVariants = isSSH ? normalizeSshConfigVariants(parsedConfig.npv, 'NPV Tunnel') : [];
            const netmodVariants = isSSH ? normalizeSshConfigVariants(parsedConfig.netmod, 'NetMod') : [];
            const hasSeparateSshConfigs = npvVariants.length || netmodVariants.length;
            document.getElementById('sshAppSelector').classList.add('hidden');
            document.getElementById('sshConfigVariants').classList.toggle('hidden', !hasSeparateSshConfigs);
            document.getElementById('modalConfig').classList.toggle('hidden', hasSeparateSshConfigs);
            document.getElementById('genericConfigActions').classList.toggle('hidden', hasSeparateSshConfigs);
            document.getElementById('btnToggleConfig')?.classList.toggle('hidden', hasSeparateSshConfigs);
            document.getElementById('btnQrCode').classList.toggle('hidden', isSSH);

            // รีเซ็ตขนาดกล่อง Config ให้เป็นขนาดกะทัดรัด (Compact) เสมอเมื่อเปิด
            const cfgBox = document.getElementById('modalConfig');
            if (cfgBox) {
                cfgBox.classList.remove('h-36');
                cfgBox.classList.add('h-14');
            }
            const cfgIcon = document.getElementById('toggleConfigIcon');
            const cfgText = document.getElementById('toggleConfigText');
            if (cfgIcon) cfgIcon.innerText = '↕️';
            if (cfgText) cfgText.innerText = 'ย่อ/ขยาย';
            window.currentSshConfig = parsedConfig;
            if (hasSeparateSshConfigs) {
                renderSshConfigVariants(npvVariants, netmodVariants);
            } else {
                document.getElementById('modalConfig').value = isSSH ? String(parsedConfig.raw || configStr) : configStr;
            }

            if (window.modalTrafficTimer) {
                clearInterval(window.modalTrafficTimer);
                window.modalTrafficTimer = null;
            }

            const item = vpnItemsById.get(String(id));
            const cachedTraffic = vpnTrafficStates[String(uuid)];
            const isExpired = item ? isVpnExpired(item) : (parseShopDate(expire).getTime() <= Date.now());

            const statusEl = document.getElementById('modalStatusText');
            const uploadEl = document.getElementById('modalUpload');
            const downloadEl = document.getElementById('modalDownload');

            // 1. แสดงค่ายอดใช้งานทันที (0 วินาที) จาก Cache หรือ DB
            const initialUp = cachedTraffic?.up ?? (item && item.upload_bytes != null ? formatBytes(item.upload_bytes) : '0 MB');
            const initialDown = cachedTraffic?.down ?? (item && item.download_bytes != null ? formatBytes(item.download_bytes) : '0 MB');
            uploadEl.innerText = initialUp;
            downloadEl.innerText = initialDown;

            // 2. แสดงสถานะเบื้องต้นทันที
            if (cachedTraffic?.real_status === 'active') {
                if (cachedTraffic.is_online) {
                    statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>ออนไลน์ (ใช้งานอยู่)</span>';
                } else {
                    statusEl.innerText = 'ใช้งานได้';
                    statusEl.className = 'font-bold text-green-600 text-xs md:text-sm truncate';
                }
            } else if (isExpired || cachedTraffic?.real_status === 'expired' || item?.status_real === 'expired') {
                statusEl.innerText = 'หมดอายุ';
                statusEl.className = 'font-bold text-orange-600 text-xs md:text-sm truncate';
                document.getElementById('modalDaysLeft').innerText = 'หมดอายุแล้ว';
                startDeleteCountdown(expire, isSSH);
            } else if (cachedTraffic?.real_status === 'disabled' || item?.status_real === 'disabled') {
                statusEl.innerText = 'ถูกปิดใช้งาน';
                statusEl.className = 'font-bold text-slate-500 text-xs md:text-sm truncate';
            } else {
                statusEl.innerText = 'ใช้งานได้';
                statusEl.className = 'font-bold text-green-600 text-xs md:text-sm truncate';
            }

            document.getElementById('modalDaysLeft').innerText = isExpired ? 'หมดอายุแล้ว' : getDetailedTimeLeft(expire);

            // 3. แสดงปุ่มลบไฟล์และต่ออายุทันที ไม่ต้องรอ traffic โหลด
            document.getElementById('deleteContainer').classList.remove('hidden');
            document.getElementById('renewContainer').classList.remove('hidden');
            updateRenewButtons(item);
            
            if (isUserReseller) {
                document.getElementById('btnSwitchServer').classList.remove('hidden');
            } else {
                document.getElementById('btnSwitchServer').classList.add('hidden');
            }
            document.getElementById('refundNotice').classList.remove('hidden');

            function fetchLiveTraffic(isInitial = false) {
                const modal = document.getElementById('detailModal');
                if (!modal || !modal.classList.contains('modal-active')) {
                    if (window.modalTrafficTimer) {
                        clearInterval(window.modalTrafficTimer);
                        window.modalTrafficTimer = null;
                    }
                    return;
                }

                fetch(`api/get_traffic.php?uuid=${encodeURIComponent(uuid)}&server=${encodeURIComponent(title)}&_t=${Date.now()}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            vpnTrafficStates[String(uuid)] = data;
                            updateVpnBadge(uuid);

                            if (uploadEl && data.up) {
                                if (uploadEl.innerText !== data.up && !isInitial && uploadEl.innerText !== '⏳') {
                                    uploadEl.classList.add('text-emerald-500', 'scale-105');
                                    setTimeout(() => uploadEl.classList.remove('text-emerald-500', 'scale-105'), 800);
                                }
                                uploadEl.innerText = data.up;
                            }
                            if (downloadEl && data.down) {
                                if (downloadEl.innerText !== data.down && !isInitial && downloadEl.innerText !== '⏳') {
                                    downloadEl.classList.add('text-emerald-500', 'scale-105');
                                    setTimeout(() => downloadEl.classList.remove('text-emerald-500', 'scale-105'), 800);
                                }
                                downloadEl.innerText = data.down;
                            }

                            if (data.real_status === 'active') {
                                if (data.is_online) {
                                    statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>ออนไลน์ (ใช้งานอยู่)</span>';
                                } else {
                                    statusEl.innerText = 'ใช้งานได้';
                                    statusEl.className = 'font-bold text-green-600 text-xs md:text-sm truncate';
                                }
                            } else if (data.real_status === 'expired') {
                                statusEl.innerText = 'หมดอายุ';
                                statusEl.className = 'font-bold text-orange-600 text-xs md:text-sm truncate';
                                document.getElementById('modalDaysLeft').innerText = 'หมดอายุแล้ว';
                                startDeleteCountdown(expire, isSSH);
                            } else if (data.real_status === 'not_found') {
                                statusEl.innerText = 'ไม่พบในเซิร์ฟเวอร์';
                                statusEl.className = 'font-bold text-red-600 text-xs md:text-sm truncate';
                                document.getElementById('modalDaysLeft').innerText = 'ถูกลบแล้ว';
                            } else if (data.real_status === 'disabled') {
                                statusEl.innerText = 'ถูกปิดใช้งาน';
                                statusEl.className = 'font-bold text-slate-500 text-xs md:text-sm truncate';
                            } else if (data.real_status === 'unknown') {
                                statusEl.innerText = 'ข้อมูลวันหมดอายุไม่ถูกต้อง';
                                statusEl.className = 'font-bold text-amber-600 text-xs md:text-sm truncate';
                                document.getElementById('modalDaysLeft').innerText = 'กรุณาติดต่อแอดมิน';
                            }
                        }
                    }).catch(() => {
                        // เก็บค่าเดิมที่แสดงอยู่ไว้
                    });
            }

            fetchLiveTraffic(true);
            window.modalTrafficTimer = setInterval(() => fetchLiveTraffic(false), 3000);

            document.getElementById('qrContainer').classList.add('hidden');
            document.body.classList.add('modal-open');
            document.getElementById('detailModal').classList.add('modal-active');
        }

        async function switchServer() {
            if (!isUserReseller || !current_opened_id) {
                return Swal.fire('ไม่สามารถใช้งานได้', 'ระบบย้ายเซิร์ฟเวอร์นี้สำหรับตัวแทนจำหน่ายเท่านั้น', 'warning');
            }

            Swal.fire({ title: 'กำลังโหลดเซิร์ฟเวอร์...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch('api/switch_server.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_options', config_id: current_opened_id })
                });
                const result = await response.json();
                Swal.close();
                if (result.status !== 'success') throw new Error(result.message || 'โหลดข้อมูลย้ายเซิร์ฟเวอร์ไม่สำเร็จ');

                const source = result.data?.source || {};
                const servers = Array.isArray(result.data?.servers) ? result.data.servers : [];
                const movable = servers.filter(server => server.can_move);
                if (!movable.length) {
                    return Swal.fire('ยังย้ายไม่ได้', servers[0]?.reason || 'ยังไม่มีเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน', 'warning');
                }

                const firstId = movable[0].id;
                const optionsHtml = servers.map(server => {
                    const price = server.target_customer_price == null ? '-' : `฿${Number(server.target_customer_price).toFixed(2)}`;
                    const suffix = server.can_move ? ` · ราคาขาย ${price}` : ` · ${server.reason || 'ไม่พร้อมใช้งาน'}`;
                    const selected = server.can_move && server.id === firstId ? ' selected' : '';
                    const disabled = server.can_move ? '' : ' disabled';
                    return `<option value="${server.id}"${selected}${disabled}>${escapeHtml(server.name)} · ${server.is_ssh ? 'SSH' : 'VPN'}${escapeHtml(suffix)}</option>`;
                }).join('');

                const html = `<div class="text-left space-y-3">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-[10px] font-bold uppercase text-slate-400">ไฟล์ปัจจุบัน</p>
                        <p class="mt-1 text-sm font-bold text-slate-900 truncate">${escapeHtml(source.server_name || '-')}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">แพ็กเกจ</span><b class="mt-0.5 block text-slate-800">${escapeHtml(source.package_name || '-')}</b></div>
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">เหลือเวลา</span><b class="mt-0.5 block text-slate-800">${Number(source.remaining_days || 0).toFixed(2)} วัน</b></div>
                        </div>
                    </div>
                    ${source.is_paid ? `<div><label class="block text-xs font-bold text-slate-700 mb-1.5">ราคาที่ขายให้ลูกค้า (บาท)</label><input id="switch-sale-price" type="number" min="0.01" max="1000000" step="0.01" inputmode="decimal" class="swal2-input !m-0 !w-full !text-sm" placeholder="เช่น 50" autofocus><p class="mt-1 text-[10px] text-slate-400">กรอกราคาขายจริงของไฟล์นี้ ไม่ใช่ราคาทุนตัวแทน</p></div>` : `<div class="rounded-xl bg-pink-50 px-3 py-2 text-[11px] font-bold text-pink-700">ไฟล์ฟรี/ทดลอง ระบบจะคงเวลาที่เหลือเดิมโดยไม่ต้องกรอกราคา</div>`}
                    <label class="block text-xs font-bold text-slate-700">เลือกเซิร์ฟเวอร์ปลายทาง</label>
                    <select id="switch-server" class="swal2-input !m-0 !w-full !text-sm">${optionsHtml}</select>
                    <div id="switch-preview" class="rounded-2xl border border-purple-100 bg-purple-50 p-3 text-xs font-bold text-purple-700"></div>
                    <div id="switch-ssh-credentials" class="hidden space-y-2 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                        <p id="switch-ssh-help" class="text-[11px] font-bold text-amber-700"></p>
                        <input id="switch-ssh-user" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH username ใหม่ (เว้นว่างเพื่อใช้ชื่อเดิม)">
                        <input id="switch-ssh-pass" type="password" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH password ใหม่ (เว้นว่างเพื่อใช้รหัสเดิม)">
                    </div>
                    <p class="rounded-xl bg-emerald-50 px-3 py-2 text-[10px] font-bold text-emerald-700">✅ ระบบจะสร้างไฟล์ใหม่ก่อน แล้วลบไฟล์เดิม · ไม่หักเงินเพิ่ม</p>
                </div>`;

                const dialog = await Swal.fire({
                    title: '🔄 ย้ายเซิร์ฟเวอร์',
                    html,
                    width: 560,
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันการย้าย',
                    cancelButtonText: 'ยกเลิก',
                    focusConfirm: false,
                    didOpen: () => {
                        const serverSelect = document.getElementById('switch-server');
                        const priceInput = document.getElementById('switch-sale-price');
                        const previewEl = document.getElementById('switch-preview');
                        const credentialBox = document.getElementById('switch-ssh-credentials');
                        const helpEl = document.getElementById('switch-ssh-help');
                        const sync = () => {
                            const selected = servers.find(server => String(server.id) === String(serverSelect.value));
                            if (!selected) return;
                            const salePrice = Number(priceInput?.value || 0);
                            const targetPrice = Number(selected.target_customer_price || 0);
                            if (!selected.can_move) {
                                previewEl.innerText = `⚠️ ${selected.reason || 'เซิร์ฟเวอร์นี้ยังไม่พร้อมย้าย'}`;
                            } else if (source.is_paid && salePrice <= 0) {
                                previewEl.innerText = 'กรุณากรอกราคาที่ขายให้ลูกค้า เพื่อคำนวณวันปลายทาง';
                            } else if (source.is_paid) {
                                const convertedDays = Number(source.remaining_days || 0) * salePrice / targetPrice;
                                previewEl.innerHTML = `📊 ราคาขายลูกค้า ฿${salePrice.toFixed(2)} → ราคาปลายทาง ฿${targetPrice.toFixed(2)}<br><span class="font-normal">${Number(source.remaining_days || 0).toFixed(2)} วัน → ${convertedDays.toFixed(2)} วัน</span>`;
                            } else {
                                previewEl.innerHTML = '📊 ไฟล์ฟรี/ทดลอง<br><span class="font-normal">คงเวลาที่เหลือเดิม</span>';
                            }
                            credentialBox.classList.toggle('hidden', !selected.is_ssh);
                            helpEl.innerText = selected.requires_ssh_credentials
                                ? 'ปลายทางเป็น SSH กรุณากรอก Username และ Password ใหม่'
                                : 'ปลายทางเป็น SSH · เว้นว่างได้ ระบบจะใช้บัญชีเดิมถ้าใช้ได้';
                        };
                        serverSelect.addEventListener('change', sync);
                        priceInput?.addEventListener('input', sync);
                        sync();
                    },
                    preConfirm: () => {
                        const selected = servers.find(server => String(server.id) === String(document.getElementById('switch-server').value));
                        if (!selected || !selected.can_move) {
                            Swal.showValidationMessage('กรุณาเลือกเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน');
                            return false;
                        }
                        const salePrice = Number(document.getElementById('switch-sale-price')?.value || 0);
                        if (source.is_paid && (!Number.isFinite(salePrice) || salePrice <= 0)) {
                            Swal.showValidationMessage('กรุณากรอกราคาที่ขายให้ลูกค้าให้ถูกต้อง');
                            return false;
                        }
                        const sshUser = document.getElementById('switch-ssh-user')?.value.trim() || '';
                        const sshPass = document.getElementById('switch-ssh-pass')?.value.trim() || '';
                        if (selected.requires_ssh_credentials && (sshUser === '' || sshPass === '')) {
                            Swal.showValidationMessage('กรุณากรอก SSH username และ password ของปลายทาง');
                            return false;
                        }
                        return { new_server_id: String(selected.key || `sv${selected.id}`), sale_price: salePrice, new_ssh_user: sshUser, new_ssh_pass: sshPass };
                    }
                });
                if (!dialog.isConfirmed) return;

                Swal.fire({ title: 'กำลังย้ายเซิร์ฟเวอร์...', text: 'ระบบกำลังสร้างไฟล์ใหม่และตรวจสอบไฟล์เดิม', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const moveResponse = await fetch('api/switch_server.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch', config_id: current_opened_id, ...dialog.value })
                });
                const moveResult = await moveResponse.json();
                if (moveResult.status !== 'success') throw new Error(moveResult.message || 'ย้ายเซิร์ฟเวอร์ไม่สำเร็จ');
                await Swal.fire({ icon: 'success', title: 'ย้ายสำเร็จ', text: moveResult.message || 'ย้ายเซิร์ฟเวอร์เรียบร้อยแล้ว' });
                location.reload();
            } catch (e) {
                Swal.close();
                Swal.fire('ย้ายไม่สำเร็จ', e.message || 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
            }
        }

        async function deleteVPN() {
            const btn = document.getElementById('btnDeleteVPN');
            const originalText = btn.innerText;
            btn.innerText = "กำลังตรวจสอบ... ⏳";
            btn.disabled = true;

            try {
                // ตรวจสอบยอดเงินคืนก่อนยืนยันลบ
                const previewRes = await fetch('api/delete_vpn.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ config_id: current_opened_id, action: 'preview' })
                });
                const preview = await previewRes.json();

                if (preview.status !== 'success') {
                    throw new Error(preview.message || 'ไม่สามารถตรวจสอบข้อมูลการคืนเงินได้');
                }

                let confirmHtml = '';
                if (preview.refund_amount > 0) {
                    if (preview.is_grace) {
                        confirmHtml = `
                            <div class="text-left bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800 text-xs md:text-sm space-y-2 mb-3">
                                <p class="font-bold text-emerald-700 flex items-center gap-1.5 text-sm">
                                    <span>⚡</span> ลบภายใน 10 นาที (คืนเงินเต็ม 100%)
                                </p>
                                <div class="grid grid-cols-2 gap-2 pt-1 text-[11px] text-emerald-700 border-t border-emerald-200/60">
                                    <div>ใช้งานไป: <strong class="text-emerald-950">${preview.used_time_str || (preview.used_hours + ' ชม.')}</strong></div>
                                    <div>ราคาเดิม: <strong>฿${preview.price_paid.toFixed(2)}</strong></div>
                                </div>
                                <div class="pt-2 border-t border-emerald-200/60 flex justify-between items-center">
                                    <span class="font-bold text-emerald-800 text-xs">ยอดเงินคืนเข้ากระเป๋า:</span>
                                    <span class="font-bold text-lg text-emerald-600">฿${preview.refund_amount.toFixed(2)}</span>
                                </div>
                                <p class="text-[11px] text-emerald-600/80">ระบบจะคืนเงินเต็มจำนวนเข้ากระเป๋าของคุณอัตโนมัติทันที</p>
                            </div>
                            <p class="text-xs text-gray-500">หากลบแล้วจะไม่สามารถกู้คืนไฟล์นี้ได้ ต้องการลบหรือไม่?</p>
                        `;
                    } else {
                        const usedDisplay = preview.used_time_str || (preview.used_hours + ' ชม.');
                        const remainingDisplay = preview.remaining_time_str || (preview.remaining_hours + ' ชม.');
                        confirmHtml = `
                            <div class="text-left bg-slate-50 border border-gray-200 rounded-2xl p-4 text-slate-800 text-xs md:text-sm space-y-2 mb-3">
                                <p class="font-bold text-slate-800 flex items-center gap-1.5 text-sm">
                                    <span>⏱️</span> คืนเงินตามเวลาคงเหลือที่ไม่ได้ใช้
                                </p>
                                <div class="grid grid-cols-2 gap-2 pt-1 text-[11px] text-gray-600 border-t border-gray-200">
                                    <div>ใช้งานไป: <strong class="text-slate-800">${usedDisplay}</strong></div>
                                    <div>คงเหลือ: <strong class="text-slate-800">${remainingDisplay}</strong></div>
                                    <div>อัตราเฉลี่ย: <strong>฿${preview.hourly_rate.toFixed(2)}/ชม.</strong></div>
                                    <div>ราคาเดิม: <strong>฿${preview.price_paid.toFixed(2)}</strong></div>
                                </div>
                                <div class="pt-2 border-t border-gray-200 flex justify-between items-center">
                                    <span class="font-bold text-emerald-700 text-xs">ยอดเงินคืนเข้ากระเป๋า:</span>
                                    <span class="font-bold text-lg text-emerald-600">฿${preview.refund_amount.toFixed(2)}</span>
                                </div>
                                <p class="text-[10px] text-gray-400">ระบบหักเฉพาะเวลาที่ใช้จริง และคืนเงินตามเวลาที่เหลือให้คุณ</p>
                            </div>
                            <p class="text-xs text-gray-500">หากลบแล้วจะไม่สามารถกู้คืนไฟล์นี้ได้ ต้องการลบหรือไม่?</p>
                        `;
                    }
                } else {
                    confirmHtml = `
                        <div class="text-left bg-red-50 border border-red-200 rounded-2xl p-4 text-red-800 text-xs md:text-sm mb-3">
                            <p class="font-bold text-red-700">⚠️ ไม่มียอดเงินคืนสำหรับการลบไฟล์นี้</p>
                            <p class="text-[11px] text-red-600 mt-1">${preview.is_expired ? 'ไฟล์ VPN นี้หมดอายุแล้ว' : 'ไฟล์นี้เป็นแพ็กเกจฟรีหรือใช้งานครบกำหนดเวลาแล้ว'}</p>
                        </div>
                        <p class="text-xs text-gray-500">หากลบแล้วจะไม่สามารถกู้คืนไฟล์นี้ได้ ยืนยันการลบหรือไม่?</p>
                    `;
                }

                btn.innerText = originalText;
                btn.disabled = false;

                const confirmDelete = await Swal.fire({
                    title: 'ยืนยันการลบไฟล์ VPN',
                    html: confirmHtml,
                    icon: preview.refund_amount > 0 ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: preview.refund_amount > 0 ? 'ยืนยันการลบและรับเงินคืน' : 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                });

                if (!confirmDelete.isConfirmed) return;

                btn.innerText = "กำลังลบข้อมูล... ⏳";
                btn.disabled = true;

                // หยุด Traffic Timer ทันทีเพื่อไม่ให้ส่ง request ไปเขียนทับสถานะใน DB
                stopDeleteCountdown();
                if (window.modalTrafficTimer) {
                    clearInterval(window.modalTrafficTimer);
                    window.modalTrafficTimer = null;
                }

                const response = await fetch('api/delete_vpn.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ config_id: current_opened_id, action: 'delete' })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    const deletedId = current_opened_id;
                    closeDetail();

                    // ลบออกจากรายการบนหน้าเว็บทันที
                    vpnItems = vpnItems.filter(item => String(item.id) !== String(deletedId));
                    vpnItemsById.delete(String(deletedId));
                    applyVpnFilters();
                    loadUserInfo();

                    await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.message });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'การเชื่อมต่อขัดข้อง', text: e.message || 'เกิดข้อผิดพลาด' });
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        function getRenewPricing(days, item) {
            const tierPrices = (item && Array.isArray(item.tier_prices) && item.tier_prices.length >= 4)
                ? item.tier_prices
                : [5, 25, 45, 80];

            let basePrice = 0;
            if (days === 1) basePrice = Number(tierPrices[0]) || 5;
            else if (days === 7) basePrice = Number(tierPrices[1]) || 25;
            else if (days === 15) basePrice = Number(tierPrices[2]) || 45;
            else if (days === 30) basePrice = Number(tierPrices[3]) || 80;
            else basePrice = Math.max(5.00, Math.round(days * 2.50 * 100) / 100);

            const discRate = (100 - (isUserReseller ? resellerDiscountPercent : 0)) / 100;
            const finalPrice = isUserReseller ? Math.round(basePrice * discRate * 100) / 100 : basePrice;

            return { basePrice, finalPrice };
        }

        function updateRenewButtons(item) {
            [1, 7, 15, 30].forEach(d => {
                const pricing = getRenewPricing(d, item);
                const priceVal = isUserReseller ? pricing.finalPrice : pricing.basePrice;
                const fmt = '฿' + (priceVal % 1 === 0 ? priceVal : priceVal.toFixed(2));
                const el = document.getElementById(`renewPriceText${d}`);
                if (el) el.innerText = fmt;
            });
        }

        async function renewVPN(days) {
            const item = vpnItemsById.get(String(current_opened_id));
            const pricing = getRenewPricing(days, item);
            const basePrice = pricing.basePrice;
            const finalPrice = pricing.finalPrice;
            const fmtFinal = (finalPrice % 1 === 0 ? finalPrice : finalPrice.toFixed(2));
            const fmtBase = (basePrice % 1 === 0 ? basePrice : basePrice.toFixed(2));
            const resellerPriceHtml = isUserReseller
                ? `<div class="mt-2.5 p-2 bg-pink-50 border border-pink-200 rounded-xl text-xs font-bold text-pink-700 flex items-center justify-center gap-1.5"><span>🏷️</span> ได้รับส่วนลดตัวแทน ${resellerDiscountPercent}%: <span class="line-through text-gray-400 font-normal">฿${fmtBase}</span> <span class="text-pink-600 font-bold">฿${fmtFinal}</span></div>`
                : `<div class="mt-2 text-sm text-gray-600 font-semibold">ยอดที่ต้องชำระ: <b class="text-emerald-600">฿${fmtBase}</b></div>`;

            const confirmRenew = await Swal.fire({ 
                title: 'ยืนยันการต่ออายุ', 
                html: `ต้องการต่ออายุเพิ่มอีก <b>${days} วัน</b> ใช่หรือไม่?${resellerPriceHtml}<br><span class="text-xs text-gray-400 mt-2 block">ระบบจะหักเงินจากยอดคงเหลือของคุณ</span>`, 
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonColor: '#10b981', 
                cancelButtonColor: '#64748b', 
                confirmButtonText: 'ยืนยัน', 
                cancelButtonText: 'ยกเลิก' 
            });
            if (!confirmRenew.isConfirmed) return;

            Swal.fire({ title: 'กำลังดำเนินการ...', text: 'กรุณารอสักครู่ ระบบกำลังต่ออายุเซิร์ฟเวอร์', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            try {
                const response = await fetch('api/renew_vpn.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ config_id: current_opened_id, days: days }) });
                const result = await response.json();
                if (result.status === 'success') {
                    // หากกรองสถานะ 'หมดอายุแล้ว' อยู่ ให้เปลี่ยนเป็น 'แสดงทั้งหมด' เพื่อให้รายการที่ต่ออายุแล้วยังคงแสดงอยู่
                    const statusFilter = document.getElementById('vpnStatusFilter');
                    if (statusFilter && statusFilter.value === 'expired') {
                        statusFilter.value = 'all';
                    }

                    // อัปเดตสถานะ Traffic ใน Cache ของ UUID นี้ให้เป็นปกติและรีเซ็ต Traffic ทันที
                    if (item && item.uuid) {
                        vpnTrafficStates[String(item.uuid)] = { status: 'success', real_status: 'active', up: '0 MB', down: '0 MB' };
                    }

                    // รีโหลดข้อมูลยอดเงินคงเหลือและรายการเซิร์ฟเวอร์ใหม่ (ไม่รีเฟรชหน้า ทำให้ข้อความค้นหาไม่หาย)
                    await Promise.all([loadUserInfo(), loadVPNList()]);

                    // อัปเดตข้อมูลไฟล์ในหน้าต่างรายละเอียดทันที เพื่อให้ดูต่อได้
                    if (current_opened_id && vpnItemsById.has(String(current_opened_id))) {
                        openDetailById(current_opened_id);
                    }

                    await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.message });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้' });
            }
        }

        function toggleConfigBox() {
            const el = document.getElementById('modalConfig');
            const icon = document.getElementById('toggleConfigIcon');
            const text = document.getElementById('toggleConfigText');
            if (!el) return;

            if (el.classList.contains('h-14')) {
                el.classList.remove('h-14');
                el.classList.add('h-36');
                if (icon) icon.innerText = '🔼';
                if (text) text.innerText = 'ย่อลง';
            } else {
                el.classList.remove('h-36');
                el.classList.add('h-14');
                if (icon) icon.innerText = '🔽';
                if (text) text.innerText = 'ขยาย';
            }
        }

        function toggleQRCode() {
            const container = document.getElementById('qrContainer');
            const svgWrapper = document.getElementById('qrSvgWrapper');
            const img = document.getElementById('qrImage');
            const config = (document.getElementById('modalConfig')?.value || '').trim();

            if (!container.classList.contains('hidden')) {
                container.classList.add('hidden');
                return;
            }

            if (!config) {
                Toast.fire({ icon: 'warning', title: 'ไม่พบข้อมูล Config สำหรับสร้าง QR Code' });
                return;
            }

            container.classList.remove('hidden');

            let generated = false;
            // 1. First priority: Pure Vector SVG via local qrcode-generator (mathematically 100% sharp)
            if (typeof qrcode === 'function') {
                try {
                    let qr;
                    try {
                        qr = qrcode(0, 'M');
                        qr.addData(config);
                        qr.make();
                    } catch (mErr) {
                        qr = qrcode(0, 'L');
                        qr.addData(config);
                        qr.make();
                    }

                    if (svgWrapper) {
                        svgWrapper.innerHTML = qr.createSvgTag({ scalable: true, margin: 2 });
                        svgWrapper.classList.remove('hidden');
                    }
                    if (img) img.classList.add('hidden');
                    generated = true;
                } catch (e) {
                    console.warn('Local SVG QR generation failed:', e);
                }
            }

            // 2. Fallback: High-resolution vector SVG via API (600x600 SVG)
            if (!generated && img) {
                if (svgWrapper) svgWrapper.classList.add('hidden');
                img.classList.remove('hidden');
                img.src = `https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=8&format=svg&data=${encodeURIComponent(config)}`;
            }

            // Smooth scroll modal to make QR Code immediately visible
            setTimeout(() => {
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 60);
        }

        function copyTextToClipboard(text) {
            return new Promise((resolve, reject) => {
                if (!text || !text.trim()) {
                    reject(new Error('No text provided'));
                    return;
                }

                // Method 1: Modern navigator.clipboard (available only in secure contexts: HTTPS / localhost)
                if (window.isSecureContext && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(text).then(resolve).catch(() => {
                        execFallback();
                    });
                    return;
                }

                execFallback();

                function execFallback() {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.top = '-9999px';
                    ta.style.left = '-9999px';
                    ta.style.opacity = '0';
                    ta.setAttribute('readonly', '');
                    document.body.appendChild(ta);

                    ta.focus({ preventScroll: true });
                    ta.select();
                    ta.setSelectionRange(0, 999999);

                    let success = false;
                    try {
                        success = document.execCommand('copy');
                    } catch (e) {
                        success = false;
                    }

                    if (!success) {
                        ta.removeAttribute('readonly');
                        ta.select();
                        ta.setSelectionRange(0, 999999);
                        try {
                            success = document.execCommand('copy');
                        } catch (e) {
                            success = false;
                        }
                    }

                    document.body.removeChild(ta);

                    if (success) {
                        resolve();
                    } else {
                        reject(new Error('Copy command failed'));
                    }
                }
            });
        }

        function copyConfig() {
            const c = document.getElementById("modalConfig");
            const val = c ? c.value : '';
            if (!val || !val.trim()) {
                Toast.fire({ icon: 'warning', title: 'ไม่พบข้อมูล Config' });
                return;
            }
            copyTextToClipboard(val).then(() => {
                Toast.fire({ icon: 'success', title: 'คัดลอกไฟล์สำเร็จ!' });
            }).catch(() => {
                if (c) { c.focus(); c.select(); }
                Toast.fire({ icon: 'error', title: 'คัดลอกไม่สำเร็จ กรุณากดค้างที่ข้อความเพื่อคัดลอก' });
            });
        }

        function downloadConfigFile() {
            const config = document.getElementById("modalConfig").value;
            if (!config || !config.trim()) {
                Toast.fire({ icon: 'error', title: 'ไม่พบข้อมูล Config' });
                return;
            }
            const title = document.getElementById('modalTitle').innerText || 'vpn-config';
            const safeTitle = title.replace(/[/\\?%*:|"<>]/g, '-').replace(/\s+/g, ' ').trim();
            const blob = new Blob([config], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${safeTitle}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            Toast.fire({ icon: 'success', title: 'ดาวน์โหลดไฟล์ Config สำเร็จ!' });
        }

        function closeDetail() {
            stopDeleteCountdown();
            if (window.modalTrafficTimer) {
                clearInterval(window.modalTrafficTimer);
                window.modalTrafficTimer = null;
            }
            document.getElementById('detailModal').classList.remove('modal-active');
            if (!document.getElementById('profileModal')?.classList.contains('modal-active') && !document.getElementById('backupModal')?.classList.contains('modal-active')) {
                document.body.classList.remove('modal-open');
            }
        }

        function normalizeSshConfigVariants(value, label) {
            if (Array.isArray(value)) return value.map((item, index) => ({
                name: String(item?.name || item?.template_name || `${label} ${index + 1}`),
                config: String(item?.config || item?.value || '')
            })).filter(item => item.config);
            if (typeof value === 'string' && value.trim()) return [{ name: `${label} 1`, config: value.trim() }];
            return [];
        }

        function renderSshConfigVariants(npvVariants, netmodVariants) {
            const container = document.getElementById('sshConfigVariants');
            if (!container) return;
            const renderGroup = (label, items, theme) => {
                const palette = theme === 'orange'
                    ? { box: 'border-orange-100 bg-orange-50', title: 'text-orange-800', button: 'bg-orange-600 hover:bg-orange-700' }
                    : { box: 'border-emerald-100 bg-emerald-50', title: 'text-emerald-800', button: 'bg-emerald-600 hover:bg-emerald-700' };
                if (!items.length) return '';
                return `<section class="rounded-2xl border ${palette.box} p-3"><div class="mb-2 flex items-center justify-between gap-2"><h3 class="text-xs font-extrabold ${palette.title}">${theme === 'orange' ? '🔥' : '🛡️'} ${label}</h3><span class="text-[10px] font-bold ${palette.title}">${items.length} แบบ</span></div><div class="space-y-2">${items.map((item, index) => { const fieldId = `ssh-variant-${theme}-${index}`; const vName = escapeHtml(item.name || `${label} ${index + 1}`); return `<div class="rounded-xl border border-white/80 bg-white p-2.5 shadow-sm"><div class="mb-1.5 flex items-center justify-between gap-2"><span class="min-w-0 truncate text-[11px] font-bold text-slate-700">${vName}</span><div class="flex items-center gap-1.5 shrink-0"><button type="button" onclick="downloadConfigField('${fieldId}', '${vName}')" class="rounded-lg bg-slate-700 hover:bg-slate-800 px-2 py-1.5 text-[10px] font-bold text-white shadow-sm flex items-center gap-1">📥 โหลด</button><button type="button" onclick="copyConfigField('${fieldId}')" class="rounded-lg ${palette.button} px-2 py-1.5 text-[10px] font-bold text-white shadow-sm flex items-center gap-1">📋 คัดลอก</button></div></div><textarea id="${fieldId}" readonly rows="1" class="w-full rounded-lg bg-slate-50 p-2 text-slate-700 font-mono outline-none h-9 resize-none" style="font-size: 10px; line-height: 1.45; letter-spacing: -0.01em;">${escapeHtml(item.config)}</textarea></div>`; }).join('')}</div></section>`;
            };
            container.innerHTML = renderGroup('NPV Tunnel', npvVariants, 'emerald') + renderGroup('NetMod', netmodVariants, 'orange');
        }

        function downloadConfigField(fieldId, variantName) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) return;
            const mainTitle = document.getElementById('modalTitle').innerText || 'vpn-config';
            const fullName = `${mainTitle} - ${variantName}`;
            const safeName = fullName.replace(/[/\\?%*:|"<>]/g, '-').replace(/\s+/g, ' ').trim();
            const blob = new Blob([field.value], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${safeName}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            Toast.fire({ icon: 'success', title: 'ดาวน์โหลดไฟล์ Config สำเร็จ!' });
        }

        function copyConfigField(fieldId) {
            const field = document.getElementById(fieldId);
            const val = field ? field.value : '';
            if (!val || !val.trim()) return;
            copyTextToClipboard(val).then(() => {
                Toast.fire({ icon: 'success', title: 'คัดลอกไฟล์สำเร็จ!' });
            }).catch(() => {
                if (field) { field.focus(); field.select(); }
                Toast.fire({ icon: 'error', title: 'คัดลอกไม่สำเร็จ กรุณากดค้างที่ข้อความเพื่อคัดลอก' });
            });
        }

        window.showConfigFormat = function(app) {
            const btnNpv = document.getElementById('btnAppNpv');
            const btnNetmod = document.getElementById('btnAppNetmod');
            
            // รีเซ็ตปุ่ม
            btnNpv.className = "bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700";
            btnNetmod.className = "bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700";

            if (app === 'npv') {
                btnNpv.className = "bg-emerald-50 border-2 border-emerald-500 text-emerald-700 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md flex flex-col items-center justify-center gap-1.5";
                const variants = normalizeSshConfigVariants(window.currentSshConfig.npv, 'NPV Tunnel');
                document.getElementById('modalConfig').value = variants[0]?.config || window.currentSshConfig.raw;
            } else if (app === 'netmod') {
                btnNetmod.className = "bg-orange-50 border-2 border-orange-500 text-orange-700 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md flex flex-col items-center justify-center gap-1.5";
                const variants = normalizeSshConfigVariants(window.currentSshConfig.netmod, 'NetMod');
                document.getElementById('modalConfig').value = variants[0]?.config || window.currentSshConfig.raw;
            } else {
                document.getElementById('modalConfig').value = window.currentSshConfig.raw;
            }
        }

        async function loadAnnouncements() {
            try {
                const r = await fetch('api/announcements.php?action=list', { cache: 'no-store' });
                const d = await r.json();
                const announcements = Array.isArray(d.data) ? d.data : [];
                if (!announcements.length) return;

                const esc = s => {
                    const x = document.createElement('div');
                    x.textContent = s;
                    return x.innerHTML;
                };

                // 1. แสดงผลการ์ดประกาศด้านบนของ Dashboard เสมอ (#announcementArea)
                const annArea = document.getElementById('announcementArea');
                if (annArea) {
                    const bannerMeta = {
                        info: { icon: '📣', cls: 'ann-card-info', tag: 'ข่าวสาร' },
                        success: { icon: '🎉', cls: 'ann-card-success', tag: 'สำเร็จ/โปรโมชั่น' },
                        warning: { icon: '⚠️', cls: 'ann-card-warning', tag: 'แจ้งเตือน' },
                        danger: { icon: '🚨', cls: 'ann-card-danger', tag: 'สำคัญมาก' }
                    };
                    const bannersHtml = announcements.map(a => {
                        const bm = bannerMeta[a.type] || bannerMeta.info;
                        return `
                        <div class="announcement-banner-card ${bm.cls}">
                            <div class="ann-card-icon">${bm.icon}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="font-bold text-sm sm:text-base leading-snug">${esc(a.title)}</h4>
                                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-white/70 backdrop-blur-xs border border-current/20">${bm.tag}</span>
                                </div>
                                <p class="text-xs sm:text-sm opacity-90 whitespace-pre-line leading-relaxed">${esc(a.message || a.content || '')}</p>
                            </div>
                        </div>`;
                    }).join('');
                    annArea.innerHTML = bannersHtml;
                    annArea.classList.remove('hidden');
                }

                // อัปเดตข้อความวิ่ง (Ticker) จากประกาศระบบ
                const t1 = document.getElementById('shopTickerText1');
                const t2 = document.getElementById('shopTickerText2');
                if (t1 && t2 && announcements.length) {
                    const annParts = announcements.map(a => `<span>📢 <strong>${esc(a.title)}:</strong> ${esc(a.message || a.content || '')}</span>`).join('');
                    const combinedTicker = `<span>🟢 สถานะร้านเปิดบริการตามปกติ ตลอด 24 ชม.</span>${annParts}<span>🚀 ยินดีต้อนรับสู่ <?= htmlspecialchars($siteSettings['site_name'] ?: 'EKROM SHOP') ?></span><span>⚡ ระบบจัดส่งไฟล์อัตโนมัติ รวดเร็ว ใช้งานได้ทันที</span>`;
                    t1.innerHTML = combinedTicker;
                    t2.innerHTML = combinedTicker;
                }

                // 2. ตรวจสอบป๊อปอัปแจ้งเตือน (Modal)
                const now = Date.now();
                const visibleAnnouncements = announcements.filter(a => {
                    const key = `ekrom_ann_dismissed_${a.id}`;
                    const dismissedUntil = Number(localStorage.getItem(key) || 0);
                    if (dismissedUntil > now) return false;
                    if (dismissedUntil) localStorage.removeItem(key);
                    return true;
                });

                localStorage.removeItem('ekrom_ann_force');
                localStorage.removeItem('ekrom_ann_closed_at');
                if (!visibleAnnouncements.length) return;

                const meta = {
                    info: { icon: '📢', tag: 'ข้อมูลข่าวสาร' },
                    success: { icon: '🎉', tag: 'สำเร็จ & โปรโมชั่น' },
                    warning: { icon: '⚠️', tag: 'ข้อควรทราบ' },
                    danger: { icon: '🚨', tag: 'ประกาศด่วน' }
                };

                const items = visibleAnnouncements.map(a => {
                    const m = meta[a.type] || meta.info;
                    return `
                    <div class="ann-modal-card">
                        <div class="ann-modal-card-header">
                            <span class="ann-modal-card-badge type-${a.type || 'info'}">
                                ${m.icon} ${m.tag}
                            </span>
                            <span class="ann-modal-card-date">📌 ประกาศล่าสุด</span>
                        </div>
                        <div class="ann-modal-card-title">${esc(a.title)}</div>
                        <div class="ann-modal-card-msg">${esc(a.message || a.content || '')}</div>
                    </div>`;
                }).join('');

                const html = `
                <div class="ann-hero-banner">
                    <div class="ann-hero-icon-box">📢</div>
                    <div class="ann-hero-tag">✨ <?= $siteName ?> OFFICIAL NEWS</div>
                    <h3 class="ann-hero-title">ประกาศข่าวสารสำคัญ</h3>
                    <p class="ann-hero-subtitle">ข้อมูลอัปเดตและข้อความแจ้งเตือนล่าสุดสำหรับคุณ</p>
                </div>
                <div class="ann-body-content">
                    <div class="ann-card-stack">
                        ${items}
                    </div>
                    <label class="ann-snooze-wrapper">
                        <input id="ekromAnnSnooze" type="checkbox">
                        <span class="ann-snooze-text">ไม่ต้องแสดงป๊อปอัปนี้ซ้ำอีกใน 1 ชั่วโมง</span>
                    </label>
                </div>`;

                const saveAnnouncementDismissal = () => {
                    if (document.getElementById('ekromAnnSnooze')?.checked) {
                        const dismissedUntil = Date.now() + 3600000;
                        visibleAnnouncements.forEach(a => {
                            localStorage.setItem(`ekrom_ann_dismissed_${a.id}`, String(dismissedUntil));
                        });
                    }
                };

                Swal.fire({
                    html,
                    showConfirmButton: true,
                    confirmButtonText: 'รับทราบข้อความแล้ว ✓',
                    customClass: {
                        container: 'ekrom-ann-backdrop',
                        popup: 'ekrom-ann-popup',
                        confirmButton: 'ekrom-ann-confirm'
                    },
                    showClass: { popup: 'ekrom-ann-enter' },
                    hideClass: { popup: 'ekrom-ann-leave' },
                    buttonsStyling: false,
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    willClose: saveAnnouncementDismissal
                });
            } catch (e) {}
        }
        async function changePassword() {
            const oldPwd = document.getElementById('oldPwd').value;
            const newPwd = document.getElementById('newPwd').value;
            const confirmPwd = document.getElementById('confirmPwd').value;

            if (!oldPwd || !newPwd || !confirmPwd) {
                return Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบทุกช่อง', 'warning');
            }
            if (newPwd !== confirmPwd) {
                return Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน', 'warning');
            }
            if (newPwd.length < 4) {
                return Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 4 ตัวอักษร', 'warning');
            }

            const btn = document.getElementById('btnChangePwd');
            const orig = btn.innerText;
            btn.innerText = 'กำลังบันทึก...';
            btn.disabled = true;

            try {
                const res = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ old_password: oldPwd, new_password: newPwd })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ', data.message || 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว', 'success');
                    document.getElementById('oldPwd').value = '';
                    document.getElementById('newPwd').value = '';
                    document.getElementById('confirmPwd').value = '';
                } else {
                    Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                }
            } catch (e) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            } finally {
                btn.innerText = orig;
                btn.disabled = false;
            }
        }

        async function checkAdminRoleAndInjectButton() {
            try {
                const data = await authReady;
                const role = data.role || data.user?.role;
                if (data.status === 'logged_in' && role === 'admin') {
                    const btnHTML = `<a href="admin-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-rose-600 hover:bg-rose-50 transition-all mt-2 border border-rose-100"><span>⚙️</span> จัดการระบบ (Admin)</a>`;
                    const deskNav = document.querySelector('aside nav');
                    if (deskNav && !deskNav.innerHTML.includes('admin-dash.php')) deskNav.insertAdjacentHTML('beforeend', btnHTML);
                    const mobNav = document.querySelector('#mobileDrawer nav');
                    if (mobNav && !mobNav.innerHTML.includes('admin-dash.php')) mobNav.insertAdjacentHTML('beforeend', btnHTML);
                }
            } catch (e) { }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            // Prevent background drag when touching non-scrollable parts of modals
            ['detailModal', 'profileModal', 'backupModal'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.addEventListener('touchmove', function(e) {
                    const scrollable = e.target.closest('.overflow-y-auto, textarea, input, select');
                    if (!scrollable) {
                        e.preventDefault();
                    }
                }, { passive: false });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (document.getElementById('detailModal')?.classList.contains('modal-active')) closeDetail();
                    if (document.getElementById('profileModal')?.classList.contains('modal-active')) closeProfile();
                    if (document.getElementById('backupModal')?.classList.contains('modal-active')) closeBackupModal();
                    const menu = document.getElementById('mobileMenu');
                    if (menu && !menu.classList.contains('hidden')) toggleMobileMenu();
                }
            });

            if (new URLSearchParams(window.location.search).get('backup') === '1') {
                openBackupModal();
            }

            if (window.location.hash === '#my-files' || window.location.search.includes('scroll=my-files')) {
                setTimeout(() => {
                    scrollToMyFiles();
                }, 400);
            }

            try {
                await authReady;
                loadUserInfo();
                loadVPNList();
                loadAnnouncements();
                checkAdminRoleAndInjectButton();
            } catch (e) {}
        });
    </script>



</body>
</html>
