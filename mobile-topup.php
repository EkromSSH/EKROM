<?php
require_once __DIR__ . '/api/db.php';
release_session_lock();
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
    <title>เติมเงินมือถือ - <?= $siteName ?> <?= $siteSubtitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; scroll-behavior: smooth; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .op-card { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .op-card:hover { transform: translateY(-2px); }
        .amount-card { transition: all 0.15s ease-in-out; }
        .amount-card:hover { transform: translateY(-2px); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        const authReady = fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
            return data;
        }).catch(() => {
            window.location.href = 'login.php';
            throw new Error('auth_required');
        });
    </script>
    <link rel="stylesheet" href="mobile-layout.css?v=20261031_stickytop3">
    <link rel="stylesheet" href="cyber-oled.css?v=20261030_cleanlight">
</head>
<body class="mobile-safe-layout bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Mobile Topbar -->
    <div class="mobile-topbar lg:hidden bg-white border-b border-gray-100 px-4 py-3 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-lg tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">AIRTIME</span></span>
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

    <!-- Mobile Drawer -->
    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="drawer-header flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                        <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
                    </div>
                    <span class="drawer-title font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-pink-600">SHOP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2 overflow-y-auto">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🏠</span> <span>หน้าแรก</span></a>
                <a href="buyer-dash.php#my-files" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 hover:text-pink-600 transition-all"><span>📁</span> <span>ดูไฟล์ของฉัน</span></a>
                <a href="buyer-dash.php?backup=1" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs">
                    <div class="flex items-center gap-3">
                        <span>⚡</span> <span>ไฟล์สำรอง</span>
                    </div>
                    <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
                </a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="mobile-topup.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📱</span> เติมเงินมือถือ</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงินเข้าเว็บ</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
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
            <a href="buyer-dash.php?backup=1" class="sidebar-link flex items-center justify-between gap-3 px-4 py-3 rounded-xl font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all shadow-xs cursor-pointer">
                <div class="flex items-center gap-3">
                    <span>⚡</span> <span>ไฟล์สำรอง</span>
                </div>
                <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2 py-0.5 rounded-full uppercase">ใช้ชั่วคราว</span>
            </a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="mobile-topup.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📱</span> เติมเงินมือถือ</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงินเข้าเว็บ</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <!-- Header Banner: Pro FinTech Wallet Card -->
        <header class="max-w-4xl mx-auto mb-8 mt-2 md:mt-0">
            <div class="bg-[#11151f] text-white rounded-3xl p-6 sm:p-7 shadow-xl border border-slate-800 relative overflow-hidden">
                <!-- Background decorative glowing circle -->
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-pink-300 text-xs font-bold mb-2.5 border border-white/10">
                            <span>📱</span> AIRTIME TOP-UP EXPRESS
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2">
                            เติมเงินมือถือออนไลน์
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-md">
                            เติมเงินซิมระบบเติมเงิน AIS, TrueMove H, DTAC สะดวกรวดเร็ว ปลอดภัย ไร้กังวล
                        </p>
                    </div>

                    <!-- Wallet Balance Card on Header -->
                    <div class="bg-[#090d16] p-4 sm:p-5 rounded-2xl border border-emerald-500/40 flex items-center justify-between md:justify-end gap-5 shrink-0 shadow-xl shadow-black/40">
                        <div>
                            <div class="text-xs sm:text-sm text-white font-bold flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] animate-pulse shrink-0"></span>
                                <span>เครดิตกระเป๋าเงินของคุณ</span>
                            </div>
                            <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight mt-1" id="userBalanceDisplay" style="color: #6ee7b7; text-shadow: 0 0 12px rgba(110, 231, 183, 0.35);">0.00 ฿</div>
                        </div>
                        <button onclick="window.location.href='topup.php'" class="px-4 py-2.5 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white text-xs font-bold rounded-xl transition-all shadow-md hover:shadow-lg hover:scale-105 active:scale-95 shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span class="text-base leading-none font-black">+</span>
                            <span>เติมเงินเข้าเว็บ</span>
                        </button>
                    </div>
                </div>

                <!-- Trust Badges Strip -->
                <div class="mt-5 pt-4 border-t border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">⚡</span>
                        <span class="font-medium">ฟรีค่าธรรมเนียมบริการ <b class="text-emerald-400">(Fee: ฿0)</b></span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-lg bg-pink-500/20 text-pink-400 flex items-center justify-center text-xs font-bold">🛡️</span>
                        <span class="font-medium">มีระบบคุ้มครองดึงเงินคืนใน 15 นาที</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-200">
                        <span class="w-5 h-5 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center text-xs font-bold">🚀</span>
                        <span class="font-medium">ระบบ API แท้ เงินเข้าทันที</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Form Area -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 shadow-sm space-y-6">
                    <!-- Wizard Stepper Indicator -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <!-- Step 1 Indicator -->
                        <div class="flex items-center gap-2.5 cursor-pointer" onclick="goToStep(1)">
                            <div id="stepperDot1" class="w-8 h-8 rounded-xl bg-pink-600 text-white font-black text-xs flex items-center justify-center shadow-xs transition-all">
                                1
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ขั้นตอน 1</div>
                                <div id="stepperTitle1" class="text-xs font-bold text-slate-900">เลือกค่าย & เบอร์โทร</div>
                            </div>
                        </div>

                        <!-- Stepper Connector Bar -->
                        <div class="flex-grow mx-3 max-w-[80px] sm:max-w-[120px] h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="stepperProgress" class="h-full bg-pink-600 transition-all duration-300 w-0"></div>
                        </div>

                        <!-- Step 2 Indicator -->
                        <div class="flex items-center gap-2.5 cursor-pointer" onclick="goToStep(2)">
                            <div id="stepperDot2" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-black text-xs flex items-center justify-center transition-all">
                                2
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ขั้นตอน 2</div>
                                <div id="stepperTitle2" class="text-xs font-bold text-slate-400">เลือกจำนวนเงิน</div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 1 CONTAINER: Choose Operator & Enter Phone -->
                    <div id="step1Section" class="space-y-6">
                        <!-- Step 1.1: Select Operator -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xs font-black">1</span>
                                    เลือกเครือข่ายมือถือ
                                </label>
                                <span class="text-[11px] text-slate-400 font-medium">ตรวจจับค่ายอัตโนมัติเมื่อพิมพ์เบอร์</span>
                            </div>
                            <div class="grid grid-cols-3 gap-3" id="operatorGrid">
                                <!-- AIS -->
                                <div onclick="selectOperator('AIS')" id="op-AIS" class="op-card p-3 rounded-2xl border-2 border-emerald-400 bg-emerald-50 text-center ring-2 ring-emerald-500 transition-all">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-500 text-white shadow-xs flex items-center justify-center font-black text-sm mb-1.5">AIS</div>
                                    <div class="font-bold text-xs text-slate-800">1-2-Call</div>
                                    <div class="text-[10px] text-emerald-600 font-semibold">เอไอเอส</div>
                                </div>
                                <!-- TRUE -->
                                <div onclick="selectOperator('TRUE')" id="op-TRUE" class="op-card p-3 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-red-600 text-white shadow-xs flex items-center justify-center font-black text-sm mb-1.5">TRUE</div>
                                    <div class="font-bold text-xs text-slate-800">TrueMove H</div>
                                    <div class="text-[10px] text-red-600 font-semibold">ทรูมูฟ เอช</div>
                                </div>
                                <!-- DTAC -->
                                <div onclick="selectOperator('DTAC')" id="op-DTAC" class="op-card p-3 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all">
                                    <div class="w-10 h-10 mx-auto rounded-xl bg-sky-500 text-white shadow-xs flex items-center justify-center font-black text-sm mb-1.5">DTAC</div>
                                    <div class="font-bold text-xs text-slate-800">Happy DTAC</div>
                                    <div class="text-[10px] text-sky-600 font-semibold">ดีแทค</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1.2: Phone Number Input -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xs font-black">2</span>
                                    หมายเลขโทรศัพท์ที่ต้องการเติมเงิน
                                </span>
                                <span id="phoneHint" class="text-[11px] font-normal text-slate-400">10 หลัก</span>
                            </label>
                            <div class="relative">
                                <input type="tel" id="phoneNumber" maxlength="12" placeholder="08x-xxx-xxxx" oninput="formatPhoneNumberInput(this)" class="w-full px-4 py-3.5 rounded-2xl border-2 border-slate-200 focus:border-pink-500 focus:outline-none font-mono text-xl font-bold tracking-wider text-slate-800 placeholder-slate-300 transition-all bg-slate-50/50">
                                <div id="detectedBadge" onclick="cycleOperator()" title="คลิกเพื่อสลับค่าย" class="absolute right-3.5 top-1/2 -translate-y-1/2 hidden px-2.5 py-1 rounded-xl text-xs font-black uppercase cursor-pointer transition-all select-none"></div>
                            </div>
                            
                            <!-- Recent Numbers Quick Chips -->
                            <div id="recentPhonesContainer" class="hidden mt-3 p-3 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                                <div class="text-[11px] font-bold text-slate-500 mb-2 flex items-center gap-1">
                                    <span>⚡ เบอร์ที่เติมล่าสุด (แตะเพื่อเลือก):</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5" id="recentPhonesList"></div>
                            </div>

                            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                                <span>💡</span> <span>ระบบตรวจจับค่ายอัตโนมัติ (หากเป็นเบอร์ย้ายค่าย สามารถคลิกเลือกค่ายด้านบนหรือคลิกที่ป้ายค่ายในช่องเบอร์ได้เลยครับ)</span>
                            </p>
                        </div>

                        <!-- Step 1 Button: เลือกจำนวนเงิน -->
                        <button type="button" onclick="goToStep(2)" id="btnGoToStep2" class="w-full bg-pink-600 hover:bg-pink-700 text-white py-3.5 rounded-2xl font-bold text-base transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                            <span>เลือกจำนวนเงิน</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>

                    <!-- STEP 2 CONTAINER: Select Amount & Confirm (Initially Hidden) -->
                    <div id="step2Section" class="hidden space-y-6">
                        <!-- Step 2 Target Phone & Carrier Summary Banner -->
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-slate-100/70 border border-slate-200/80 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3.5">
                                <div id="step2OpIcon" class="w-11 h-11 rounded-xl bg-emerald-500 text-white font-black text-sm flex items-center justify-center shadow-xs">AIS</div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span id="step2OpTitle" class="text-xs font-bold text-slate-600">AIS 1-2-Call</span>
                                        <span id="step2TopupTag" class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded-md">เติมเงิน</span>
                                    </div>
                                    <div id="step2PhoneDisplay" class="text-lg font-black font-mono text-slate-900 tracking-wider">08x-xxx-xxxx</div>
                                </div>
                            </div>
                            <button type="button" onclick="goToStep(1)" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-pink-600 bg-white hover:bg-pink-50 border border-slate-200 hover:border-pink-200 transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                <span>เปลี่ยนเบอร์</span>
                            </button>
                        </div>

                        <!-- Step 2 Amount Selector -->
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-xs font-black">2</span>
                                    เลือกจำนวนเงินที่ต้องการเติม
                                </label>
                                <span class="text-[11px] text-slate-400 font-medium">เลือกยอดเงินตามที่ต้องการ</span>
                            </div>

                            <!-- Amount Grid Container -->
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5" id="amountGrid">
                                <!-- Populated dynamically via renderAmountGrid() based on selected operator -->
                            </div>

                            <!-- Selected Amount Live Preview Bar -->
                            <div class="mt-3.5 p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-white shadow-2xs flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200/60">
                                        💳
                                    </div>
                                    <div class="flex flex-wrap items-baseline gap-1.5">
                                        <span class="text-slate-400 font-medium text-[11px]">ยอดที่เลือก:</span>
                                        <strong id="previewSelectedAmount" class="font-black text-slate-900 font-mono text-sm sm:text-base">50.00 บาท</strong>
                                        <span id="amountOperatorBadge" class="text-[10px] font-bold px-2 py-0.2 rounded-md border ml-1">AIS</span>
                                    </div>
                                </div>
                                <div class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 shrink-0">
                                    ไม่มีค่าธรรมเนียม
                                </div>
                            </div>
                        </div>

                        <!-- Warning Disclaimer -->
                        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-amber-800 text-xs leading-relaxed flex items-start gap-2.5">
                            <span class="text-base shrink-0">⚠️</span>
                            <div>
                                <strong class="font-bold">โปรดตรวจทานเบอร์โทรศัพท์ให้ถูกต้อง:</strong> ยอดเงินจะเข้าสู่เบอร์ปลายทางทันทีหลังทำรายการ 
                                <span class="block text-amber-700 text-[11px] mt-0.5 font-medium">กรณีเติมผิดเบอร์ ท่านสามารถกดยื่นเรื่อง "ดึงเงินคืน" ได้ภายใน 15 นาที ในตารางประวัติด้านล่าง โดยระบบจะดึงยอดเงินคงเหลือจากซิมปลายทางคืนเข้ากระเป๋าอัตโนมัติ (แนะนำให้กดยื่นเรื่องทันทีก่อนปลายทางนำยอดเงินไปสมัครเน็ต)</span>
                            </div>
                        </div>

                        <!-- Action Buttons in Step 2: Back & Confirm -->
                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" onclick="goToStep(1)" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                <span>ย้อนกลับ</span>
                            </button>
                            <button type="button" onclick="submitTopup()" id="btnSubmitTopup" class="w-2/3 bg-pink-600 hover:bg-pink-700 text-white py-3.5 rounded-2xl font-bold text-base transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                                <span>⚡</span>
                                <span>ยืนยันการเติมเงิน <span id="submitAmountText">50 ฿</span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Summary & Feature Card -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span>📋</span> สรุปรายการคำสั่งซื้อ
                        </h3>
                        <span id="summaryStepBadge" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-lg">ขั้นตอน 1/2</span>
                    </div>
                    
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100/70">
                            <span class="text-slate-400 font-medium">เครือข่าย</span>
                            <span id="summaryOp" class="font-bold text-emerald-600">AIS 1-2-Call</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100/70">
                            <span class="text-slate-400 font-medium">เบอร์โทรศัพท์</span>
                            <span id="summaryPhone" class="font-mono font-bold text-slate-800">ยังไม่ได้ระบุ</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100/70">
                            <span class="text-slate-400 font-medium">ยอดเงินที่เติม</span>
                            <span id="summaryAmount" class="font-bold text-slate-900 font-mono text-sm">50.00 ฿</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100/70">
                            <span class="text-slate-400 font-medium">ค่าธรรมเนียมบริการ</span>
                            <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">0.00 ฿ (ฟรี)</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100/70">
                            <span class="text-slate-400 font-medium">ยอดหักจากกระเป๋าเงิน</span>
                            <span id="summaryDeduct" class="font-bold text-pink-600 font-mono text-sm">50.00 ฿</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-slate-50 rounded-xl border border-slate-200/60">
                            <span class="text-slate-600 font-bold">คงเหลือหลังทำรายการ</span>
                            <span id="summaryRemaining" class="font-bold text-emerald-600 font-mono text-sm">0.00 ฿</span>
                        </div>
                    </div>

                    <!-- Safe Airtime Guarantee Box -->
                    <div class="p-3.5 rounded-2xl bg-gradient-to-br from-pink-50/80 to-rose-50/50 border border-pink-100 text-xs text-pink-950 space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5 text-pink-700">
                            <span>🛡️</span> รับประกันความปลอดภัย 100%
                        </div>
                        <p class="text-[11px] text-pink-900/85 leading-relaxed">
                            หากพบว่าเติมผิดเบอร์ ท่านสามารถกดยื่นเรื่อง <b>"ดึงเงินคืน"</b> ได้ทันทีภายใน 15 นาที ในตารางประวัติด้านล่าง ระบบจะดึงเงินคืนเข้ากระเป๋าอัตโนมัติ
                        </p>
                    </div>
                </div>

                <!-- Feature Benefits Card -->
                <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-3 text-xs">
                    <h4 class="font-bold text-slate-900 flex items-center gap-2 text-xs uppercase tracking-wider">
                        <span>✨</span> จุดเด่นระบบเติมเงิน
                    </h4>
                    <div class="space-y-2 text-slate-500">
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><b>เงินเข้าทันที:</b> เชื่อมต่อระบบ API โดยตรง ยอดเข้าไวในไม่กี่วินาที</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><b>ฟรีค่าบริการ:</b> ไม่มีบวกเพิ่ม 2-5 บาท เหมือนตู้เติมเงินทั่วไป</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><b>คุ้มครอง 15 นาที:</b> อุ่นใจ มีระบบกู้คืนยอดเงินหากเติมผิดเบอร์</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Topup History & Auto Refund (Modern FinTech Section) -->
        <section class="max-w-4xl mx-auto mt-12 pt-8 border-t border-slate-200/80">
            <!-- Header with Title & Live Search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-pink-500/20 shrink-0">
                        📜
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                ประวัติการเติมเงินมือถือ
                            </h2>
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span>
                                คุ้มครอง 15 นาที
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            ตรวจสอบสถานะการทำรายการ และรับประกันสิทธิ์กดดึงเงินคืนเข้ากระเป๋าได้อัตโนมัติภายใน 15 นาที
                        </p>
                    </div>
                </div>

                <!-- Live Search & Refresh Button -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none text-xs">🔍</span>
                        <input type="text" id="historySearchInput" oninput="handleHistorySearch(this.value)" placeholder="ค้นหาเบอร์โทร, เลขอ้างอิง..." class="w-full bg-white border border-slate-200 pl-8 pr-4 py-2 rounded-xl text-xs outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-all shadow-sm">
                    </div>
                    <button type="button" onclick="loadTopupHistory(true)" id="btnRefreshHistory" class="p-2 text-pink-600 hover:text-pink-700 bg-white hover:bg-pink-50 rounded-xl transition-all shadow-sm border border-slate-200 hover:border-pink-200 cursor-pointer shrink-0" title="รีเฟรชข้อมูล">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Mini Quick Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-5">
                <!-- Card 1: Total -->
                <div onclick="setHistoryFilter('all')" class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 cursor-pointer flex items-center justify-between group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl font-bold shrink-0 group-hover:scale-105 transition-transform">
                            📱
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-400 font-semibold">รายการเติมเงินทั้งหมด</div>
                            <div class="text-base sm:text-lg font-black text-slate-900 tracking-tight" id="statTotalCount">0 รายการ</div>
                        </div>
                    </div>
                    <span class="text-xs text-slate-300 group-hover:text-pink-600 group-hover:translate-x-0.5 transition-all">→</span>
                </div>

                <!-- Card 2: Protection Window (Can Refund) -->
                <div onclick="setHistoryFilter('can_refund')" class="bg-gradient-to-br from-amber-50/70 via-orange-50/30 to-white rounded-2xl p-4 border border-amber-200/90 shadow-sm hover:shadow-md hover:border-amber-300 transition-all duration-200 cursor-pointer flex items-center justify-between group">
                    <div class="flex items-center gap-3.5">
                        <div class="relative w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold shrink-0 group-hover:scale-105 transition-transform">
                            ⏱️
                            <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                            </span>
                        </div>
                        <div>
                            <div class="text-[11px] text-amber-800 font-semibold">อยู่ในช่วงคุ้มครองขอคืนเงิน (15 นาที)</div>
                            <div class="text-base sm:text-lg font-black text-amber-900 tracking-tight" id="statCanRefundCount">0 รายการ</div>
                        </div>
                    </div>
                    <span class="text-xs text-amber-400 group-hover:text-amber-700 group-hover:translate-x-0.5 transition-all">→</span>
                </div>

                <!-- Card 3: Refunded -->
                <div onclick="setHistoryFilter('refunded')" class="bg-gradient-to-br from-purple-50/70 via-indigo-50/30 to-white rounded-2xl p-4 border border-purple-200/90 shadow-sm hover:shadow-md hover:border-purple-300 transition-all duration-200 cursor-pointer flex items-center justify-between group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl font-bold shrink-0 group-hover:scale-105 transition-transform">
                            ↩️
                        </div>
                        <div>
                            <div class="text-[11px] text-purple-800 font-semibold">ดึงเงินคืนสำเร็จ</div>
                            <div class="text-base sm:text-lg font-black text-purple-900 tracking-tight" id="statRefundedCount">0 รายการ</div>
                        </div>
                    </div>
                    <span class="text-xs text-purple-400 group-hover:text-purple-700 group-hover:translate-x-0.5 transition-all">→</span>
                </div>
            </div>

            <!-- Filter Pills with Live Badges -->
            <div class="flex items-center justify-between gap-3 mb-4 overflow-x-auto pb-1 hide-scroll">
                <div class="inline-flex bg-slate-200/70 p-1 rounded-2xl text-xs font-bold text-slate-600 shrink-0">
                    <button type="button" onclick="setHistoryFilter('all')" id="filter-all" class="px-3.5 py-1.5 rounded-xl transition-all bg-white text-slate-900 shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <span>ทั้งหมด</span>
                        <span id="filterAllCount" class="px-1.5 py-0.5 rounded-full bg-slate-100 text-[10px] text-slate-600 font-bold">0</span>
                    </button>
                    <button type="button" onclick="setHistoryFilter('success')" id="filter-success" class="px-3.5 py-1.5 rounded-xl transition-all hover:text-slate-900 flex items-center gap-1.5 text-slate-600 cursor-pointer">
                        <span>เติมสำเร็จ</span>
                        <span id="filterSuccessCount" class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-[10px] text-emerald-700 font-bold">0</span>
                    </button>
                    <button type="button" onclick="setHistoryFilter('can_refund')" id="filter-can_refund" class="px-3.5 py-1.5 rounded-xl transition-all hover:text-slate-900 flex items-center gap-1.5 text-slate-600 cursor-pointer">
                        <span>ขอคืนเงินได้</span>
                        <span id="filterCanRefundBadge" class="px-1.5 py-0.5 rounded-full bg-amber-100 text-[10px] text-amber-700 font-bold">0</span>
                    </button>
                    <button type="button" onclick="setHistoryFilter('refunded')" id="filter-refunded" class="px-3.5 py-1.5 rounded-xl transition-all hover:text-slate-900 flex items-center gap-1.5 text-slate-600 cursor-pointer">
                        <span>ดึงเงินคืนแล้ว</span>
                        <span id="filterRefundedCount" class="px-1.5 py-0.5 rounded-full bg-purple-100 text-[10px] text-purple-700 font-bold">0</span>
                    </button>
                </div>

                <div class="text-[11px] text-slate-400 font-medium hidden sm:block">
                    แสดงข้อมูลล่าสุด 50 รายการ
                </div>
            </div>

            <!-- Card List Container -->
            <div id="historyCardsContainer" class="space-y-3.5">
                <div class="bg-white rounded-3xl p-10 border border-slate-100 text-center text-slate-400 shadow-sm flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-xl mb-2 animate-pulse">⏳</div>
                    <p class="font-bold text-xs text-slate-500">กำลังโหลดประวัติการเติมเงิน...</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        let currentOperator = 'AIS';
        let currentAmount = 50;
        let userBalance = 0;

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                document.body.classList.add('drawer-open');
                menu.classList.remove('hidden');
                setTimeout(() => {
                    menu.classList.remove('opacity-0');
                    drawer.classList.remove('-translate-x-full');
                }, 10);
            } else {
                menu.classList.add('opacity-0');
                drawer.classList.add('-translate-x-full');
                document.body.classList.remove('drawer-open');
                setTimeout(() => menu.classList.add('hidden'), 300);
            }
        }

        // Denominations according to wePAY per operator
        const operatorAmounts = {
            'AIS':  [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
            'DTAC': [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
            'TRUE': [5, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500]
        };

        // Denominations purpose badges (helps user decide without discounts)
        const amountPurposeBadges = {
            10: 'พอดีเติม',
            20: 'โทรสั้น',
            50: 'ยอดฮิต',
            100: 'ยอดนิยม 🔥',
            150: 'เน็ต 7 วัน ⚡',
            200: 'เน็ตคุ้ม 🚀',
            300: 'เน็ตรายเดือน ⭐',
            500: 'คุ้มสุด'
        };

        // Render Dynamic Amount Grid
        function renderAmountGrid() {
            const grid = document.getElementById('amountGrid');
            if (!grid) return;

            const allAmounts = operatorAmounts[currentOperator] || operatorAmounts['AIS'];

            // Ensure valid currentAmount
            if (!allAmounts.includes(currentAmount)) {
                currentAmount = allAmounts.includes(50) ? 50 : allAmounts[0];
            }

            // Dynamic brand styling according to operator
            let activeColorClasses = 'border-emerald-500 bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-md shadow-emerald-500/20 ring-2 ring-emerald-500/30';
            let opBadgeStyle = 'text-emerald-700 bg-emerald-50 border-emerald-200';
            let opDisplayName = 'AIS 1-2-Call';

            if (currentOperator === 'TRUE') {
                activeColorClasses = 'border-red-600 bg-gradient-to-br from-red-600 to-red-700 text-white shadow-md shadow-red-600/20 ring-2 ring-red-600/30';
                opBadgeStyle = 'text-red-600 bg-red-50 border-red-200';
                opDisplayName = 'TrueMove H';
            } else if (currentOperator === 'DTAC') {
                activeColorClasses = 'border-sky-500 bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/20 ring-2 ring-sky-500/30';
                opBadgeStyle = 'text-sky-700 bg-sky-50 border-sky-200';
                opDisplayName = 'Happy DTAC';
            }

            // Update badge in live preview
            const badge = document.getElementById('amountOperatorBadge');
            if (badge) {
                badge.innerText = opDisplayName;
                badge.className = `text-[10px] font-bold px-2 py-0.5 rounded-md border ${opBadgeStyle}`;
            }

            let html = '';
            allAmounts.forEach(val => {
                const isActive = (val === currentAmount);
                const badgeText = amountPurposeBadges[val] || '';

                const badgeHtml = badgeText
                    ? `<span class="absolute -top-2 left-1/2 -translate-x-1/2 px-1.5 py-0.2 rounded-full text-[8px] font-bold shadow-2xs whitespace-nowrap transition-all ${isActive ? 'bg-amber-300 text-amber-950 font-black' : 'bg-slate-100 text-slate-600 border border-slate-200/80'}">${badgeText}</span>`
                    : '';

                const checkIcon = isActive
                    ? `<span class="absolute top-1 right-1.5 w-3.5 h-3.5 rounded-full bg-white/25 text-white flex items-center justify-center text-[9px] font-black leading-none">✓</span>`
                    : '';

                const cardClass = isActive
                    ? `${activeColorClasses} transform scale-[1.03]`
                    : 'bg-white hover:bg-slate-50 border-slate-200/90 text-slate-800 hover:border-slate-300 shadow-2xs';

                html += `
                    <button type="button" onclick="selectAmount(${val})" class="amount-card relative pt-3.5 pb-2.5 sm:pt-4 sm:pb-3 px-1 rounded-2xl border-2 transition-all duration-150 flex flex-col items-center justify-center cursor-pointer ${cardClass}">
                        ${badgeHtml}
                        ${checkIcon}
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-base sm:text-lg font-black font-mono leading-none tracking-tight">${val}</span>
                            <span class="text-[11px] font-bold ${isActive ? 'text-white/90' : 'text-slate-400'}">฿</span>
                        </div>
                    </button>
                `;
            });

            grid.innerHTML = html;

            // Update preview and submit button
            const previewEl = document.getElementById('previewSelectedAmount');
            if (previewEl) previewEl.innerText = parseFloat(currentAmount).toFixed(2) + ' บาท';

            const submitBtnText = document.getElementById('submitAmountText');
            if (submitBtnText) submitBtnText.innerText = currentAmount + ' ฿';

            updateSummary();
        }

        let currentStep = 1;
        let operatorManuallySelected = false;

        // Multi-step Wizard Navigation
        function goToStep(step) {
            const rawPhone = document.getElementById('phoneNumber').value.trim();
            const cleanDigits = rawPhone.replace(/\D/g, '');

            if (step === 2) {
                // Validation: phone number must be 10 digits starting with 06, 08, 09
                if (cleanDigits.length !== 10 || !['06', '08', '09'].includes(cleanDigits.substring(0, 2))) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง',
                        text: 'หมายเลขโทรศัพท์ต้องขึ้นต้นด้วย 06, 08 หรือ 09 และมีครบ 10 หลักครับ',
                        confirmButtonColor: '#db2777'
                    });
                    document.getElementById('phoneNumber').focus();
                    return;
                }

                currentStep = 2;

                // Toggle views
                document.getElementById('step1Section').classList.add('hidden');
                document.getElementById('step2Section').classList.remove('hidden');

                // Update Stepper Progress & Dots
                const sDot1 = document.getElementById('stepperDot1');
                if (sDot1) {
                    sDot1.innerHTML = '✓';
                    sDot1.className = 'w-8 h-8 rounded-xl bg-emerald-500 text-white font-black text-xs flex items-center justify-center shadow-xs transition-all';
                }
                const sTitle1 = document.getElementById('stepperTitle1');
                if (sTitle1) sTitle1.className = 'text-xs font-bold text-emerald-700';

                const sBar = document.getElementById('stepperProgress');
                if (sBar) {
                    sBar.style.width = '100%';
                    sBar.className = 'h-full bg-emerald-500 transition-all duration-300';
                }

                const sDot2 = document.getElementById('stepperDot2');
                if (sDot2) {
                    sDot2.innerHTML = '2';
                    sDot2.className = 'w-8 h-8 rounded-xl bg-pink-600 text-white font-black text-xs flex items-center justify-center shadow-xs transition-all';
                }
                const sTitle2 = document.getElementById('stepperTitle2');
                if (sTitle2) sTitle2.className = 'text-xs font-bold text-slate-900';

                // Update Target Banner in Step 2
                const opNames = { 'AIS': 'AIS 1-2-Call', 'TRUE': 'TrueMove H', 'DTAC': 'Happy DTAC' };
                const s2Phone = document.getElementById('step2PhoneDisplay');
                if (s2Phone) s2Phone.innerText = document.getElementById('phoneNumber').value;

                const s2OpTitle = document.getElementById('step2OpTitle');
                if (s2OpTitle) s2OpTitle.innerText = opNames[currentOperator] || currentOperator;

                const s2OpIcon = document.getElementById('step2OpIcon');
                if (s2OpIcon) {
                    s2OpIcon.innerText = currentOperator;
                    if (currentOperator === 'AIS') {
                        s2OpIcon.className = 'w-11 h-11 rounded-xl bg-emerald-500 text-white font-black text-sm flex items-center justify-center shadow-xs';
                    } else if (currentOperator === 'TRUE') {
                        s2OpIcon.className = 'w-11 h-11 rounded-xl bg-red-600 text-white font-black text-sm flex items-center justify-center shadow-xs';
                    } else {
                        s2OpIcon.className = 'w-11 h-11 rounded-xl bg-sky-500 text-white font-black text-sm flex items-center justify-center shadow-xs';
                    }
                }

                const s2Tag = document.getElementById('step2TopupTag');
                if (s2Tag) {
                    if (currentOperator === 'AIS') {
                        s2Tag.className = 'text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded-md';
                    } else if (currentOperator === 'TRUE') {
                        s2Tag.className = 'text-[10px] bg-red-100 text-red-800 font-bold px-1.5 py-0.2 rounded-md';
                    } else {
                        s2Tag.className = 'text-[10px] bg-sky-100 text-sky-800 font-bold px-1.5 py-0.2 rounded-md';
                    }
                }

                const stepBadge = document.getElementById('summaryStepBadge');
                if (stepBadge) {
                    stepBadge.innerText = 'ขั้นตอน 2/2';
                    stepBadge.className = 'text-[10px] font-bold text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-lg';
                }

                renderAmountGrid();
                updateSummary();
            } else {
                currentStep = 1;

                // Toggle views
                document.getElementById('step2Section').classList.add('hidden');
                document.getElementById('step1Section').classList.remove('hidden');

                // Reset Stepper Progress & Dots
                const sDot1 = document.getElementById('stepperDot1');
                if (sDot1) {
                    sDot1.innerHTML = '1';
                    sDot1.className = 'w-8 h-8 rounded-xl bg-pink-600 text-white font-black text-xs flex items-center justify-center shadow-xs transition-all';
                }
                const sTitle1 = document.getElementById('stepperTitle1');
                if (sTitle1) sTitle1.className = 'text-xs font-bold text-slate-900';

                const sBar = document.getElementById('stepperProgress');
                if (sBar) {
                    sBar.style.width = '0%';
                    sBar.className = 'h-full bg-pink-600 transition-all duration-300';
                }

                const sDot2 = document.getElementById('stepperDot2');
                if (sDot2) {
                    sDot2.innerHTML = '2';
                    sDot2.className = 'w-8 h-8 rounded-xl bg-slate-100 text-slate-400 font-black text-xs flex items-center justify-center transition-all';
                }
                const sTitle2 = document.getElementById('stepperTitle2');
                if (sTitle2) sTitle2.className = 'text-xs font-bold text-slate-400';

                const stepBadge = document.getElementById('summaryStepBadge');
                if (stepBadge) {
                    stepBadge.innerText = 'ขั้นตอน 1/2';
                    stepBadge.className = 'text-[10px] font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-lg';
                }

                updateSummary();
            }
        }

        // Cycle Operator (when clicking the badge inside the input field)
        function cycleOperator() {
            const order = ['AIS', 'TRUE', 'DTAC'];
            const nextIdx = (order.indexOf(currentOperator) + 1) % order.length;
            selectOperator(order[nextIdx]);
        }

        // Select Operator
        function selectOperator(code) {
            operatorManuallySelected = true;
            currentOperator = code;
            updateOperatorCards(code);
            updateDetectedBadge();
            renderAmountGrid();
            updateSummary();
        }

        function updateOperatorCards(code) {
            ['AIS', 'TRUE', 'DTAC'].forEach(op => {
                const el = document.getElementById('op-' + op);
                if (!el) return;
                if (op === code) {
                    if (op === 'AIS') {
                        el.className = 'op-card p-3 rounded-2xl border-2 border-emerald-400 bg-emerald-50 text-center ring-2 ring-emerald-500 transition-all';
                    } else if (op === 'TRUE') {
                        el.className = 'op-card p-3 rounded-2xl border-2 border-red-500 bg-red-50 text-center ring-2 ring-red-500 transition-all';
                    } else if (op === 'DTAC') {
                        el.className = 'op-card p-3 rounded-2xl border-2 border-sky-400 bg-sky-50 text-center ring-2 ring-sky-500 transition-all';
                    }
                } else {
                    el.className = 'op-card p-3 rounded-2xl border-2 border-slate-200 bg-white text-center transition-all';
                }
            });
        }

        // Update badge inside phone input to always match currentOperator
        function updateDetectedBadge() {
            const badge = document.getElementById('detectedBadge');
            if (!badge) return;
            const rawPhone = (document.getElementById('phoneNumber')?.value || '').trim();
            const cleanDigits = rawPhone.replace(/\D/g, '');

            if (cleanDigits.length < 3) {
                badge.classList.add('hidden');
                return;
            }

            badge.innerText = currentOperator;
            if (currentOperator === 'AIS') {
                badge.className = 'absolute right-3.5 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-xl text-xs font-black uppercase bg-emerald-100 text-emerald-700 border border-emerald-300 shadow-2xs cursor-pointer hover:bg-emerald-200 transition-all select-none';
                badge.title = 'เครือข่าย AIS (คลิกเพื่อสลับค่าย)';
            } else if (currentOperator === 'TRUE') {
                badge.className = 'absolute right-3.5 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-xl text-xs font-black uppercase bg-red-100 text-red-700 border border-red-300 shadow-2xs cursor-pointer hover:bg-red-200 transition-all select-none';
                badge.title = 'เครือข่าย TrueMove H (คลิกเพื่อสลับค่าย)';
            } else {
                badge.className = 'absolute right-3.5 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-xl text-xs font-black uppercase bg-sky-100 text-sky-700 border border-sky-300 shadow-2xs cursor-pointer hover:bg-sky-200 transition-all select-none';
                badge.title = 'เครือข่าย DTAC (คลิกเพื่อสลับค่าย)';
            }
            badge.classList.remove('hidden');
        }

        // Find operator for a phone number from past top-up history
        function findHistoryOperator(cleanPhone) {
            if (!allHistoryItems || allHistoryItems.length === 0) return null;
            const matches = allHistoryItems.filter(item => {
                const p = (item.phone || '').replace(/\D/g, '');
                return p === cleanPhone;
            });
            if (matches.length === 0) return null;

            const counts = {};
            matches.forEach(m => {
                if (m.operator) counts[m.operator] = (counts[m.operator] || 0) + 1;
            });

            let bestOp = null;
            let maxCount = 0;
            for (const [op, cnt] of Object.entries(counts)) {
                if (cnt > maxCount) {
                    maxCount = cnt;
                    bestOp = op;
                }
            }
            return bestOp;
        }

        // Detect operator by prefix (3-4 digits)
        function detectPrefixOperator(digits) {
            if (digits.length < 3) return null;
            const p3 = digits.substring(0, 3);

            // TrueMove H prefixes (including 083, 086, 088, 090, 091, 095, 096, 097, 064)
            const truePrefixes = ['083', '086', '088', '090', '091', '095', '096', '097', '064'];
            // DTAC prefixes
            const dtacPrefixes = ['080', '085', '094', '066'];
            // AIS prefixes
            const aisPrefixes = ['082', '084', '087', '092', '093', '098'];

            if (truePrefixes.includes(p3)) return 'TRUE';
            if (dtacPrefixes.includes(p3)) return 'DTAC';
            if (aisPrefixes.includes(p3)) return 'AIS';

            // 4-digit prefixes
            if (digits.length >= 4) {
                const p4 = digits.substring(0, 4);
                if (['0995', '0996', '0997'].includes(p4)) return 'TRUE';
                if (['0990', '0991', '0992', '0993', '0994'].includes(p4)) return 'DTAC';
            }

            // Shared / MNP prefixes
            if (p3 === '099') return 'DTAC';
            if (p3 === '063') return 'TRUE';
            if (p3 === '065') return 'AIS';
            if (p3 === '061' || p3 === '062') return 'AIS';
            if (p3 === '081' || p3 === '089') return 'AIS';

            return null;
        }

        // Select Amount
        function selectAmount(val) {
            currentAmount = val;
            renderAmountGrid();
        }

        // Format Phone Number on input
        function formatPhoneNumberInput(input) {
            let val = input.value.replace(/\D/g, '');
            if (val.length > 10) val = val.substring(0, 10);

            // 1. Check if full 10 digits match user past history (highest priority for ported numbers)
            let matchedOp = null;
            if (val.length === 10) {
                matchedOp = findHistoryOperator(val);
            }

            if (matchedOp) {
                if (matchedOp !== currentOperator) {
                    currentOperator = matchedOp;
                    operatorManuallySelected = true;
                    updateOperatorCards(matchedOp);
                    renderAmountGrid();
                }
            } else if (!operatorManuallySelected && val.length >= 3) {
                // 2. Prefix auto-detection if user has not manually locked an operator
                const detected = detectPrefixOperator(val);
                if (detected && detected !== currentOperator) {
                    currentOperator = detected;
                    updateOperatorCards(detected);
                    renderAmountGrid();
                }
            }

            // Format with hyphens 08x-xxx-xxxx
            let formatted = val;
            if (val.length > 6) {
                formatted = val.substring(0, 3) + '-' + val.substring(3, 6) + '-' + val.substring(6);
            } else if (val.length > 3) {
                formatted = val.substring(0, 3) + '-' + val.substring(3);
            }
            input.value = formatted;

            updateDetectedBadge();
            updateSummary();
        }

        function updateSummary() {
            const rawPhone = document.getElementById('phoneNumber').value.trim();
            const cleanDigits = rawPhone.replace(/\D/g, '');
            
            document.getElementById('summaryPhone').innerText = cleanDigits.length === 10 ? rawPhone : (cleanDigits.length > 0 ? cleanDigits : 'ยังไม่ได้ระบุ');
            
            const opNames = { 'AIS': 'AIS 1-2-Call', 'TRUE': 'TrueMove H', 'DTAC': 'DTAC' };
            const opEl = document.getElementById('summaryOp');
            opEl.innerText = opNames[currentOperator] || currentOperator;
            if (currentOperator === 'AIS') opEl.className = 'font-bold text-emerald-600';
            else if (currentOperator === 'TRUE') opEl.className = 'font-bold text-red-600';
            else opEl.className = 'font-bold text-sky-600';

            document.getElementById('summaryAmount').innerText = currentAmount.toFixed(2) + ' ฿';
            document.getElementById('summaryDeduct').innerText = currentAmount.toFixed(2) + ' ฿';

            const remaining = userBalance - currentAmount;
            const remEl = document.getElementById('summaryRemaining');
            remEl.innerText = remaining.toFixed(2) + ' ฿';
            if (remaining < 0) {
                remEl.className = 'font-bold text-rose-600';
            } else {
                remEl.className = 'font-bold text-emerald-600';
            }
        }

        // Load Initial Info
        async function loadUserInfo() {
            try {
                const res = await fetch('api/mobile_topup.php?action=get_info');
                const data = await res.json();
                if (data.status === 'success') {
                    userBalance = parseFloat(data.balance || 0);
                    document.getElementById('userBalanceDisplay').innerText = userBalance.toFixed(2) + ' ฿';
                    document.getElementById('userBalanceMob').innerText = userBalance.toFixed(2);
                    updateSummary();
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Submit Topup
        async function submitTopup() {
            const rawPhone = document.getElementById('phoneNumber').value.trim();
            const cleanDigits = rawPhone.replace(/\D/g, '');

            if (cleanDigits.length !== 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'เบอร์โทรศัพท์ไม่ถูกต้อง',
                    text: 'กรุณากรอกหมายเลขโทรศัพท์ให้ครบ 10 หลัก (เช่น 081-234-5678)',
                    confirmButtonColor: '#db2777'
                });
                return;
            }

            if (userBalance < currentAmount) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยอดเงินคงเหลือไม่พอ',
                    html: `คุณมียอดเงิน <b>฿${userBalance.toFixed(2)}</b> แต่ต้องการเติม <b>฿${currentAmount.toFixed(2)}</b><br>กรุณาเติมเงินเข้ากระเป๋าก่อนทำรายการครับ`,
                    showCancelButton: true,
                    confirmButtonText: 'ไปหน้าเติมเงิน',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#db2777'
                }).then(res => {
                    if (res.isConfirmed) window.location.href = 'topup.php';
                });
                return;
            }

            // Confirmation Modal 2-step (Pro Fintech Bill Style)
            const confirm = await Swal.fire({
                title: 'ยืนยันการเติมเงินมือถือ',
                html: `
                    <div class="text-left bg-gradient-to-br from-slate-50 to-slate-100/70 p-4 rounded-2xl border border-slate-200 text-sm space-y-2.5 mt-2">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200/80">
                            <span class="text-slate-500 font-medium">เครือข่าย</span>
                            <span class="font-bold text-slate-800 bg-white px-2.5 py-0.5 rounded-lg border border-slate-200">${currentOperator}</span>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200/80">
                            <span class="text-slate-500 font-medium">เบอร์โทรศัพท์</span>
                            <b class="font-mono text-xl text-pink-600 tracking-wider">${rawPhone}</b>
                        </div>
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200/80">
                            <span class="text-slate-500 font-medium">ยอดเงินที่เติม</span>
                            <b class="text-lg text-slate-900 font-mono font-black">฿${currentAmount.toFixed(2)}</b>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500 font-medium">ค่าธรรมเนียมบริการ</span>
                            <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">฿0.00 (ฟรี)</span>
                        </div>
                    </div>
                    <div class="mt-3 p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px] text-left leading-relaxed">
                        🛡️ <b>คุ้มครอง 15 นาที:</b> ตรวจทานเบอร์ให้ถูกต้อง หากเติมผิดเบอร์สามารถกดยื่นเรื่องดึงเงินคืนในประวัติได้ทันที
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันเติมเงินทันที ⚡',
                cancelButtonText: 'กลับไปแก้ไข',
                confirmButtonColor: '#db2777',
                cancelButtonColor: '#64748b'
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('btnSubmitTopup');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> กำลังประมวลผลการเติมเงิน...';

            try {
                const formData = new FormData();
                formData.append('phone_number', cleanDigits);
                formData.append('operator', currentOperator);
                formData.append('amount', currentAmount);

                const res = await fetch('api/mobile_topup.php?action=topup', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'เติมเงินสำเร็จเรียบร้อย! 🎉',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    document.getElementById('phoneNumber').value = '';
                    goToStep(1);
                    loadUserInfo();
                    loadTopupHistory();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถทำรายการได้',
                        text: data.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<span>⚡</span> <span>ยืนยันการเติมเงิน <span id="submitAmountText">${currentAmount} ฿</span></span>`;
            }
        }

        let currentHistoryFilter = 'all';
        let searchHistoryQuery = '';
        let allHistoryItems = [];

        function handleHistorySearch(val) {
            searchHistoryQuery = (val || '').trim().toLowerCase();
            renderHistoryCards();
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
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1600
            }).fire({
                icon: 'success',
                title: `คัดลอก${label}แล้ว`
            });
        }

        function repeatTopup(phone, operator) {
            const raw = (phone || '').replace(/\D/g, '');
            const phoneInput = document.getElementById('phoneNumber');
            if (phoneInput) {
                phoneInput.value = phone;
                formatPhoneNumberInput(phoneInput);
            }
            if (operator) {
                selectOperator(operator);
            }
            goToStep(2);
            window.scrollTo({ top: 0, behavior: 'smooth' });
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

        function updateHistoryCounts() {
            const total = allHistoryItems.length;
            const successCount = allHistoryItems.filter(i => i.status === 'success' && i.refund_status !== 'success').length;
            const refundedCount = allHistoryItems.filter(i => i.status === 'refunded' || i.refund_status === 'success').length;
            const canRefundCount = allHistoryItems.filter(i => i.can_refund).length;

            const elTotal = document.getElementById('statTotalCount');
            if (elTotal) elTotal.textContent = total + ' รายการ';

            const elCanRefund = document.getElementById('statCanRefundCount');
            if (elCanRefund) elCanRefund.textContent = canRefundCount + ' รายการ';

            const elRefunded = document.getElementById('statRefundedCount');
            if (elRefunded) elRefunded.textContent = refundedCount + ' รายการ';

            const elFAll = document.getElementById('filterAllCount');
            if (elFAll) elFAll.textContent = total;

            const elFSuccess = document.getElementById('filterSuccessCount');
            if (elFSuccess) elFSuccess.textContent = successCount;

            const elFCanRefund = document.getElementById('filterCanRefundBadge');
            if (elFCanRefund) elFCanRefund.textContent = canRefundCount;

            const elFRefunded = document.getElementById('filterRefundedCount');
            if (elFRefunded) elFRefunded.textContent = refundedCount;
        }

        function setHistoryFilter(filter) {
            currentHistoryFilter = filter;
            
            const btnAll = document.getElementById('filter-all');
            const btnSuccess = document.getElementById('filter-success');
            const btnCanRefund = document.getElementById('filter-can_refund');
            const btnRefunded = document.getElementById('filter-refunded');

            const activeClasses = ['bg-white', 'text-slate-900', 'shadow-sm'];
            const inactiveClasses = ['hover:text-slate-900', 'text-slate-600'];

            [btnAll, btnSuccess, btnCanRefund, btnRefunded].forEach(b => {
                if (b) {
                    b.classList.remove(...activeClasses);
                    b.classList.add(...inactiveClasses);
                }
            });

            const activeBtn = document.getElementById('filter-' + filter);
            if (activeBtn) {
                activeBtn.classList.remove(...inactiveClasses);
                activeBtn.classList.add(...activeClasses);
            }

            renderHistoryCards();
        }

        function renderSkeletonHistory() {
            const container = document.getElementById('historyCardsContainer');
            if (!container) return;
            container.innerHTML = `
                <div class="space-y-3.5 animate-pulse">
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-slate-200/70 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100"></div>
                                <div class="space-y-2">
                                    <div class="h-4.5 w-36 bg-slate-200 rounded-lg"></div>
                                    <div class="h-3 w-48 bg-slate-100 rounded-md"></div>
                                </div>
                            </div>
                            <div class="space-y-2 text-right">
                                <div class="h-6 w-20 bg-slate-200 rounded-lg ml-auto"></div>
                                <div class="h-4 w-16 bg-slate-100 rounded-md ml-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-slate-200/70 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100"></div>
                                <div class="space-y-2">
                                    <div class="h-4.5 w-36 bg-slate-200 rounded-lg"></div>
                                    <div class="h-3 w-48 bg-slate-100 rounded-md"></div>
                                </div>
                            </div>
                            <div class="space-y-2 text-right">
                                <div class="h-6 w-20 bg-slate-200 rounded-lg ml-auto"></div>
                                <div class="h-4 w-16 bg-slate-100 rounded-md ml-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        let countdownTimerInterval = null;
        function startRefundCountdownTimer() {
            if (countdownTimerInterval) clearInterval(countdownTimerInterval);
            countdownTimerInterval = setInterval(() => {
                let hasRefundable = false;
                let needRerender = false;

                allHistoryItems.forEach(item => {
                    if (item.can_refund && item.seconds_left > 0) {
                        hasRefundable = true;
                        item.seconds_left -= 1;
                        const m = Math.floor(item.seconds_left / 60);
                        const s = item.seconds_left % 60;
                        if (item.seconds_left <= 0) {
                            item.can_refund = false;
                            item.time_left_text = 'หมดเวลาขอคืนเงินแล้ว (ครบกำหนด 15 นาที)';
                            needRerender = true;
                        } else {
                            item.time_left_text = (m > 0 ? `${m} นาที ${s} วินาที` : `${s} วินาที`);
                            const el = document.querySelector(`[data-timer-id="${item.id}"]`);
                            if (el) {
                                el.textContent = m > 0 ? (s > 0 ? `${m} นาที ${s} วิ` : `${m} นาที`) : `${s} วินาที`;
                            }
                        }
                    }
                });

                if (needRerender) {
                    updateHistoryCounts();
                    renderHistoryCards();
                } else if (hasRefundable) {
                    updateHistoryCounts();
                }
            }, 1000);
        }

        // Render Recent Top-up Phone Chips
        function renderRecentPhones() {
            const container = document.getElementById('recentPhonesContainer');
            const list = document.getElementById('recentPhonesList');
            if (!container || !list) return;

            if (!allHistoryItems || allHistoryItems.length === 0) {
                container.classList.add('hidden');
                return;
            }

            const seen = new Set();
            const recents = [];
            for (const item of allHistoryItems) {
                const clean = (item.phone || '').replace(/\D/g, '');
                if (clean.length === 10 && !seen.has(clean)) {
                    seen.add(clean);
                    recents.push({ phone: item.phone, cleanPhone: clean, operator: item.operator || 'AIS' });
                    if (recents.length >= 4) break;
                }
            }

            if (recents.length === 0) {
                container.classList.add('hidden');
                return;
            }

            const opStyles = {
                'AIS': 'border-emerald-200 bg-white hover:bg-emerald-50 text-emerald-800',
                'TRUE': 'border-red-200 bg-white hover:bg-red-50 text-red-800',
                'DTAC': 'border-sky-200 bg-white hover:bg-sky-50 text-sky-800'
            };

            list.innerHTML = recents.map(r => {
                const cls = opStyles[r.operator] || 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50';
                return `
                    <button type="button" onclick="fillRecentPhone('${r.cleanPhone}', '${r.operator}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all hover:scale-105 active:scale-95 shadow-2xs cursor-pointer ${cls}" title="คลิกเพื่อเลือกเบอร์นี้">
                        <span class="text-[9px] font-black uppercase px-1 py-0.2 rounded bg-slate-100">${r.operator}</span>
                        <span class="font-mono font-bold">${r.phone}</span>
                        <span class="text-[10px] text-pink-500 font-bold">⚡</span>
                    </button>
                `;
            }).join('');

            container.classList.remove('hidden');
        }

        // Fill phone and operator from recent chip
        function fillRecentPhone(cleanPhone, operator) {
            const input = document.getElementById('phoneNumber');
            if (input) {
                input.value = cleanPhone;
                formatPhoneNumberInput(input);
            }
            if (operator) {
                selectOperator(operator);
            }
        }

        // Load Top-up History
        async function loadTopupHistory(isUserRefresh = false) {
            const container = document.getElementById('historyCardsContainer');
            if (!container) return;

            const refreshBtn = document.getElementById('btnRefreshHistory');
            if (isUserRefresh && refreshBtn) {
                refreshBtn.classList.add('animate-spin');
            } else {
                renderSkeletonHistory();
            }

            try {
                const res = await fetch('api/mobile_topup.php?action=get_history', { cache: 'no-store' });
                const data = await res.json();

                if (data.status === 'success') {
                    allHistoryItems = data.history || [];
                    updateHistoryCounts();
                    renderHistoryCards();
                    renderRecentPhones();
                    startRefundCountdownTimer();

                    // Auto-sync operator if phone number already in input
                    const curPhone = (document.getElementById('phoneNumber')?.value || '').replace(/\D/g, '');
                    if (curPhone.length === 10) {
                        const histOp = findHistoryOperator(curPhone);
                        if (histOp && histOp !== currentOperator) {
                            selectOperator(histOp);
                        }
                    }
                } else {
                    container.innerHTML = `
                        <div class="bg-white rounded-3xl p-8 border border-rose-100 text-center text-rose-500 shadow-sm">
                            <div class="text-2xl mb-1">⚠️</div>
                            <p class="font-bold text-xs">${data.message || 'ไม่สามารถโหลดประวัติได้'}</p>
                        </div>
                    `;
                }
            } catch (e) {
                container.innerHTML = `
                    <div class="bg-white rounded-3xl p-8 border border-rose-100 text-center text-rose-500 shadow-sm">
                        <div class="text-2xl mb-1">⚠️</div>
                        <p class="font-bold text-xs">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</p>
                    </div>
                `;
            } finally {
                if (refreshBtn) refreshBtn.classList.remove('animate-spin');
            }
        }

        // Render History as Modern FinTech Cards
        function renderHistoryCards() {
            const container = document.getElementById('historyCardsContainer');
            if (!container) return;

            // 1. Filter items by status tab
            let filtered = allHistoryItems;
            if (currentHistoryFilter === 'success') {
                filtered = allHistoryItems.filter(item => item.status === 'success' && item.refund_status !== 'success');
            } else if (currentHistoryFilter === 'can_refund') {
                filtered = allHistoryItems.filter(item => item.can_refund);
            } else if (currentHistoryFilter === 'refunded') {
                filtered = allHistoryItems.filter(item => item.status === 'refunded' || item.refund_status === 'success');
            }

            // 2. Filter items by live search query
            if (searchHistoryQuery) {
                filtered = filtered.filter(item => {
                    const cleanP = (item.phone || '').replace(/\D/g, '');
                    const rawP = (item.phone || '').toLowerCase();
                    const trans = (item.trans_id || '').toLowerCase();
                    const op = (item.operator || '').toLowerCase();
                    return cleanP.includes(searchHistoryQuery) || rawP.includes(searchHistoryQuery) || trans.includes(searchHistoryQuery) || op.includes(searchHistoryQuery);
                });
            }

            if (!filtered || filtered.length === 0) {
                let emptyMsg = 'ยังไม่มีประวัติการทำรายการเติมเงินมือถือ';
                if (searchHistoryQuery) {
                    emptyMsg = `ไม่พบรายการที่ตรงกับ "${searchHistoryQuery}"`;
                } else if (currentHistoryFilter === 'success') {
                    emptyMsg = 'ไม่มีรายการเติมเงินที่สำเร็จในสถานะปกติ';
                } else if (currentHistoryFilter === 'can_refund') {
                    emptyMsg = 'ไม่มีรายการที่อยู่ในช่วงคุ้มครองขอคืนเงินในขณะนี้';
                } else if (currentHistoryFilter === 'refunded') {
                    emptyMsg = 'ยังไม่มีรายการที่ถูกดึงเงินคืน';
                }

                container.innerHTML = `
                    <div class="bg-white rounded-3xl p-10 sm:p-12 border border-slate-200/80 text-center shadow-sm">
                        <div class="w-16 h-16 rounded-3xl bg-pink-50 text-pink-500 flex items-center justify-center text-3xl mx-auto mb-3 shadow-sm">📱</div>
                        <h4 class="font-bold text-slate-800 text-base mb-1.5">${emptyMsg}</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                            ${searchHistoryQuery ? 'ลองตรวจสอบหมายเลขโทรศัพท์หรือเลขอ้างอิงใหม่อีกครั้ง' : 'เมื่อคุณทำรายการเติมเงินมือถือ ข้อมูลจะมาแสดงที่นี่ทันที พร้อมระบบรับประกันสิทธิ์ดึงเงินคืนอัตโนมัติภายใน 15 นาที'}
                        </p>
                        ${!searchHistoryQuery ? `
                        <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); document.getElementById('phoneNumber').focus();" class="mt-4 px-5 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs transition-all shadow-sm">
                            + เติมเงินมือถือทันที
                        </button>` : ''}
                    </div>
                `;
                return;
            }

            let html = '';
            filtered.forEach(item => {
                // Operator styling & brand accents
                let opGradient = 'from-emerald-500 via-emerald-600 to-green-700 shadow-emerald-500/20';
                let opDisplayName = 'AIS 1-2-Call';
                let opSubText = '1-2-CALL';
                let opLabelStyle = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                if (item.operator === 'TRUE') {
                    opGradient = 'from-red-500 via-rose-600 to-red-700 shadow-red-500/20';
                    opDisplayName = 'TrueMove H';
                    opSubText = 'MOVE H';
                    opLabelStyle = 'bg-rose-50 text-rose-700 border-rose-200';
                } else if (item.operator === 'DTAC') {
                    opGradient = 'from-sky-500 via-blue-600 to-indigo-600 shadow-sky-500/20';
                    opDisplayName = 'Happy DTAC';
                    opSubText = 'HAPPY';
                    opLabelStyle = 'bg-sky-50 text-sky-700 border-sky-200';
                }

                // Status Badge
                const isRefunded = (item.status === 'refunded' || item.refund_status === 'success');
                let statusBadge = '';
                if (isRefunded) {
                    statusBadge = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-sm whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            <span>ดึงเงินคืนแล้ว</span>
                        </span>
                    `;
                } else {
                    statusBadge = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>เติมเงินสำเร็จ</span>
                        </span>
                    `;
                }

                // Clean Thai formatted date
                const thaiDateTime = formatThaiDateTime(item.created_at);

                // Clean remaining time text (strip duplicate 'เหลือเวลา')
                const cleanTimeLeft = (item.time_left_text || '').replace('เหลือเวลา ', '');

                // Bottom Action / Info Bar
                let bottomBar = '';
                if (isRefunded) {
                    const refundAmt = item.refund_amount ? item.refund_amount.toFixed(2) : item.amount.toFixed(2);
                    bottomBar = `
                        <div class="bg-gradient-to-r from-purple-50/90 via-indigo-50/40 to-purple-50/70 border-t border-purple-100 px-4 sm:px-6 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                            <div class="flex items-center gap-3 text-purple-950 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                                    ✓
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-purple-950 flex items-center gap-2">
                                        <span>ดึงเงินคืนสำเร็จเรียบร้อย</span>
                                        <span class="text-[10px] bg-purple-200/70 text-purple-800 px-2 py-0.2 rounded-md font-bold">อัตโนมัติ</span>
                                    </div>
                                    <div class="text-[11px] text-purple-800 mt-0.5 flex flex-wrap items-center gap-1.5">
                                        <span>โอนเงินคืนเข้ากระเป๋าเครดิต:</span>
                                        <span class="inline-flex items-center whitespace-nowrap font-black font-mono text-purple-950 bg-white px-2 py-0.5 rounded-md border border-purple-200 shadow-sm">
                                            +฿${refundAmt}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-bold text-xs flex items-center gap-1.5 self-start sm:self-auto border border-purple-200 shrink-0 whitespace-nowrap">
                                <span>💳</span> เครดิตพร้อมใช้งาน
                            </span>
                        </div>
                    `;
                } else if (item.can_refund) {
                    bottomBar = `
                        <div class="bg-gradient-to-r from-amber-50 via-orange-50/50 to-amber-50/60 border-t border-amber-200/90 px-4 sm:px-6 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 text-xs text-amber-950 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-sm font-bold shrink-0 shadow-sm">
                                    ⏱️
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-amber-950 flex items-center gap-2">
                                        <span>กรณีเติมผิดเบอร์ สามารถดึงเงินคืนได้</span>
                                        <span class="text-[10px] font-bold text-amber-800 bg-amber-200/70 px-2 py-0.2 rounded-md border border-amber-300/80">ระบบอัตโนมัติ</span>
                                    </div>
                                    <div class="text-[11px] text-amber-800 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        <span>เหลือเวลาขอดึงเงินคืนอีก:</span>
                                        <span class="font-mono font-bold text-amber-950 bg-white px-2 py-0.5 rounded-md border border-amber-300 shadow-sm inline-block" data-timer-id="${item.id}">
                                            ${cleanTimeLeft}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="requestRefund(${item.id}, '${item.phone}', ${item.amount})" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 active:scale-95 text-white rounded-xl font-bold text-xs shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                                <span>🔄</span>
                                <span>ขอดึงเงินคืนทันที</span>
                            </button>
                        </div>
                    `;
                } else {
                    bottomBar = `
                        <div class="bg-slate-50 border-t border-slate-100 px-4 sm:px-6 py-2.5 flex items-center justify-between text-xs text-slate-400">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                <span>หมดระยะเวลารับประกันดึงเงินคืน (ครบกำหนด 15 นาที)</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200 font-medium">รายการสมบูรณ์</span>
                        </div>
                    `;
                }

                html += `
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 overflow-hidden">
                        <!-- Top Content Area -->
                        <div class="p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4">
                                <!-- Left side: Carrier Avatar + Number + Details -->
                                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                                    <!-- Operator Avatar Badge (Exact 52px / 56px) -->
                                    <div class="w-[52px] h-[52px] sm:w-14 sm:h-14 rounded-2xl flex flex-col items-center justify-center font-black text-white shrink-0 bg-gradient-to-br ${opGradient} border border-white/20 shadow-md">
                                        <span class="text-sm sm:text-base font-black tracking-tight leading-none">${item.operator}</span>
                                        <span class="text-[9px] font-bold opacity-90 tracking-wider mt-0.5 leading-none uppercase">${opSubText}</span>
                                    </div>

                                    <!-- Number & Meta Info -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-lg sm:text-xl font-black text-slate-900 tracking-wide select-all">${item.phone}</span>
                                            <span class="text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-lg border ${opLabelStyle}">${opDisplayName}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-slate-400 text-xs mt-1">
                                            <span class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                ${thaiDateTime}
                                            </span>
                                            <span class="text-slate-300 hidden sm:inline">•</span>
                                            <span class="inline-flex items-center gap-1 font-mono text-[10px] sm:text-[11px] text-slate-500 bg-slate-100 hover:bg-slate-200/80 px-2 py-0.5 rounded-md border border-slate-200/60 cursor-pointer transition-all" onclick="copyText('${item.trans_id}', 'เลขอ้างอิง')" title="คลิกเพื่อคัดลอกเลขอ้างอิง">
                                                <span>#${item.trans_id || '-'}</span>
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right side: Amount + Status Pill + Quick Re-topup -->
                                <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center pt-2.5 sm:pt-0 border-t sm:border-t-0 border-slate-100 shrink-0 gap-1.5 sm:gap-2">
                                    <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight whitespace-nowrap font-mono">
                                        ฿${item.amount.toFixed(2)}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        ${statusBadge}
                                        <button type="button" onclick="repeatTopup('${item.phone}', '${item.operator}')" class="text-[11px] font-bold text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 active:scale-95 px-2.5 py-1 rounded-lg border border-pink-200 transition-all shadow-sm flex items-center gap-1 cursor-pointer" title="เติมเงินซ้ำเบอร์นี้">
                                            <span>⚡ เติมซ้ำ</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Ribbon -->
                        ${bottomBar}
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Request Refund (ดึงเงินคืนกรณีเติมผิดเบอร์อัตโนมัติ)
        async function requestRefund(topupId, phone, amount) {
            const confirm = await Swal.fire({
                title: 'ขอเงินคืน (กรณีเติมผิดเบอร์)?',
                html: `
                    <div class="text-left bg-gradient-to-br from-amber-50 to-orange-50/40 p-4 rounded-2xl border border-amber-200 text-sm space-y-2.5 mt-2">
                        <div class="flex justify-between items-center pb-2 border-b border-amber-200/60">
                            <span class="text-amber-800 font-medium">เบอร์ปลายทางที่เติมผิด:</span> 
                            <b class="font-mono text-lg text-slate-900 tracking-wider font-black">${phone}</b>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-amber-800 font-medium">ยอดเงินที่จะดึงคืน:</span> 
                            <b class="text-emerald-600 font-black font-mono text-lg">+฿${parseFloat(amount).toFixed(2)}</b>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500">ช่องทางโอนคืน:</span> 
                            <span class="font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">เข้ากระเป๋าเครดิตเว็บทันที</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-slate-50 rounded-xl text-left border border-slate-200">
                        <p class="text-xs text-slate-600 leading-relaxed flex items-start gap-2">
                            <span>🛡️</span>
                            <span><strong>ระบบรับประกันความปลอดภัย:</strong> ระบบจะเชื่อมต่อเพื่อตรวจสอบยอดเงินคงเหลือจากซิมปลายทางและดึงเงินคืนเข้ากระเป๋าคุณแบบอัตโนมัติภายในไม่กี่วินาที</span>
                        </p>
                    </div>
                `,
                icon: 'warning',
                iconColor: '#f59e0b',
                showCancelButton: true,
                confirmButtonText: '🔄 ยืนยันดึงเงินคืนทันที',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#94a3b8'
            });

            if (!confirm.isConfirmed) return;

            Swal.fire({
                title: 'กำลังตรวจสอบและดึงเงินคืน...',
                html: '<div class="py-2 text-slate-500 text-xs">ระบบกำลังประสานงานดึงยอดเงินจากซิมปลายทาง กรุณารอสักครู่...</div>',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('topup_id', topupId);

                const res = await fetch('api/mobile_topup.php?action=refund', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'ดึงเงินคืนสำเร็จเรียบร้อย! 🎉',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10b981'
                    });
                    loadUserInfo();
                    loadTopupHistory();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถดึงเงินคืนได้',
                        text: data.message || 'เกิดข้อผิดพลาด',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#ef4444'
                });
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
            } catch (e) {}
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            checkAdminRoleAndInjectButton();
            loadUserInfo();
            renderAmountGrid();
            loadTopupHistory();

            const phoneInput = document.getElementById('phoneNumber');
            if (phoneInput) {
                phoneInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        goToStep(2);
                    }
                });
            }
        });
    </script>
</body>
</html>
