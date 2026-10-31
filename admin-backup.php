<?php
require_once __DIR__ . '/api/db.php';
$siteSettings = get_site_settings();
$siteName = htmlspecialchars($siteSettings['site_name'] ?: 'EKROM');
$siteLogo = htmlspecialchars($siteSettings['site_logo'] ?? '');
$siteInitial = htmlspecialchars(mb_substr($siteSettings['site_name'] ?: 'EKROM', 0, 2));

$jsonPath = __DIR__ . '/backup_config.json';
$defaultAis = "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@aisplay.ais.co.th:80?path=%2F&encryption=none&security=none&host=d2qcpub79wb31.cloudfront.net&type=ws#ais%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99";
$defaultTrue = "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@debugaccess.wetv.vip:80?type=ws&path=%2F&encryption=none&host=d2qcpub79wb31.cloudfront.net&security=none#%E0%B8%97%E0%B8%A3%E0%B8%B9%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99";

$backupData = [
    'enabled' => true,
    'ais_config' => $defaultAis,
    'true_config' => $defaultTrue,
    'updated_at' => '-'
];

if (file_exists($jsonPath)) {
    $raw = @file_get_contents($jsonPath);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $backupData['enabled'] = isset($decoded['enabled']) ? (bool)$decoded['enabled'] : true;
        if (!empty($decoded['ais_config'])) $backupData['ais_config'] = (string)$decoded['ais_config'];
        if (!empty($decoded['true_config'])) $backupData['true_config'] = (string)$decoded['true_config'];
        if (!empty($decoded['updated_at'])) $backupData['updated_at'] = (string)$decoded['updated_at'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>จัดการไฟล์สำรองฉุกเฉิน - <?= $siteName ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="qrcode.min.js?v=20260927_1"></script>
    <link rel="stylesheet" href="admin-mobile.css?v=20260926_5">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom Switch */
        .switch-checkbox:checked + .switch-label {
            background-color: #10b981;
        }
        .switch-checkbox:checked + .switch-label .switch-dot {
            transform: translateX(1.5rem);
        }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') {
                window.location.href = 'login.php';
            }
        }).catch(() => { window.location.href = 'login.php'; });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>
</head>
<body class="admin-shell bg-slate-50 text-gray-800 antialiased flex flex-col md:flex-row h-screen overflow-hidden">

    <!-- Mobile Header (มีปุ่มเมนูสามขีด) -->
    <div class="admin-mobile-nav md:hidden bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-lg tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
        </div>
        <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white focus:outline-none p-1 rounded-lg hover:bg-slate-800" title="เปิดเมนู">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Mobile Drawer (เมนูสามขีดบนมือถือ) -->
    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 max-w-[85vw] h-full max-h-[100dvh] flex flex-col p-5 sm:p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl overflow-y-auto overscroll-contain">
            <div class="drawer-header flex justify-between items-center mb-6 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                        <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
                    </div>
                    <span class="drawer-title font-bold text-xl tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
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
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-backup.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-amber-500/20 text-amber-300 transition-all border border-amber-500/40">⚡ ไฟล์สำรองฉุกเฉิน</a>
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
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden shrink-0">
                <?= render_site_logo($siteLogo, $siteName, $siteInitial) ?>
            </div>
            <span class="font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-1.5 overflow-y-auto pr-1">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-backup.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-amber-500/20 text-amber-300 transition-all border border-amber-500/40">⚡ ไฟล์สำรองฉุกเฉิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-4 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-6 lg:p-8 overflow-y-auto">
        <!-- Page Header -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl shadow-inner border border-amber-500/20">⚡</span>
                    จัดการไฟล์สำรองฉุกเฉิน
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    ควบคุมการเปิด-ปิด และอัปเดตโค้ดสำรอง (AIS & TRUE) สำหรับหน้าเว็บและ LINE Bot อัตโนมัติ โดยไม่ต้องแตะไฟล์ระบบ
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="buyer-dash.php" target="_blank" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-xs transition-all flex items-center gap-2">
                    <span>👁️</span> ดูหน้าลูกค้า
                </a>
                <button type="button" onclick="saveBackupSettings()" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md shadow-amber-500/25 transition-all flex items-center gap-2">
                    <span>💾</span> บันทึกข้อมูล
                </button>
            </div>
        </header>

        <!-- Status Card with Main Switch -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 md:p-6 mb-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                <div class="flex items-start gap-4">
                    <div id="statusIconBg" class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0 transition-colors <?= $backupData['enabled'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200' ?>">
                        <span id="statusIcon"><?= $backupData['enabled'] ? '🟢' : '⚪' ?></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900">สถานะระบบไฟล์สำรองฉุกเฉิน</h2>
                            <span id="statusBadge" class="text-xs font-bold px-3 py-1 rounded-full uppercase <?= $backupData['enabled'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                                <?= $backupData['enabled'] ? 'เปิดใช้งาน (Active)' : 'ปิดใช้งาน (Inactive)' ?>
                            </span>
                        </div>
                        <p id="statusDesc" class="text-xs text-slate-500 mt-1">
                            <?= $backupData['enabled'] 
                                ? 'ขณะนี้เปิดให้ลูกค้าขอไฟล์สำรองได้: เมนูบนเว็บจะแสดง และ LINE Bot จะส่งโค้ดสำรองเมื่อลูกค้าทักหา' 
                                : 'ขณะนี้ปิดระบบไฟล์สำรอง: เมนูบนเว็บถูกซ่อน และ LINE Bot จะแจ้งลูกค้าว่าเซิร์ฟเวอร์หลักทำงานตามปกติ' ?>
                        </p>
                    </div>
                </div>

                <!-- Toggle Switch -->
                <div class="flex items-center gap-3 self-end sm:self-center">
                    <span class="text-xs font-bold text-slate-600">เปิดระบบ</span>
                    <label class="relative inline-block w-14 h-8 cursor-pointer select-none">
                        <input type="checkbox" id="backupEnabledToggle" onchange="handleStatusToggle(this.checked)" class="switch-checkbox sr-only" <?= $backupData['enabled'] ? 'checked' : '' ?>>
                        <div class="switch-label block w-14 h-8 bg-slate-300 rounded-full transition-colors duration-200">
                            <div class="switch-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-200 shadow-md"></div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex flex-wrap items-center justify-between text-xs text-slate-400 gap-2">
                <span class="flex items-center gap-1.5">
                    <span>🕒</span> อัปเดตล่าสุด: <strong id="lblUpdatedAt" class="text-slate-600"><?= htmlspecialchars($backupData['updated_at']) ?></strong>
                </span>
                <span class="text-[11px] text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/60 font-semibold">
                    ⚡ ซิงค์เรียลไทม์กับ LINE Bot (@578infzg) และหน้าลูกค้าทันทีเมื่อกดบันทึก
                </span>
            </div>
        </div>

        <!-- 2 Column Config Editors -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- AIS Config Card -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-5 md:p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-black text-sm">AIS</span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">โค้ดสำรองเครือข่าย AIS 5G</h3>
                            <p class="text-[11px] text-slate-400">สำหรับลูกค้าซิม AIS (VLESS / Trojan / Shadowsocks URI)</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-md">AIS PLAY</span>
                </div>

                <div class="flex-grow flex flex-col mb-4">
                    <label for="aisConfigText" class="text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>โค้ดคอนฟิก (URI / Config String):</span>
                        <span id="aisCharCount" class="text-[11px] font-mono text-slate-400">0 ตัวอักษร</span>
                    </label>
                    <textarea id="aisConfigText" rows="6" oninput="updateCharCount('ais')" placeholder="วางโค้ด vless://..." class="w-full bg-slate-900 text-emerald-400 p-3.5 rounded-2xl border border-slate-800 font-mono text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all resize-y select-all leading-relaxed"><?= htmlspecialchars($backupData['ais_config']) ?></textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100 mt-auto">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyCode('ais')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>📋</span> คัดลอก
                        </button>
                        <button type="button" onclick="showQrModal('AIS 5G', 'aisConfigText')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>📱</span> ตัวอย่าง QR
                        </button>
                    </div>
                    <button type="button" onclick="resetDefaultConfig('ais')" class="text-xs text-slate-400 hover:text-rose-600 transition-colors">
                        รีเซ็ตค่าเริ่มต้น
                    </button>
                </div>
            </div>

            <!-- TRUE Config Card -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-5 md:p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center font-black text-sm">TRUE</span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">โค้ดสำรองเครือข่าย TRUE 5G</h3>
                            <p class="text-[11px] text-slate-400">สำหรับลูกค้าซิม TRUE (VLESS / Trojan / Shadowsocks URI)</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-md">TRUE WETV</span>
                </div>

                <div class="flex-grow flex flex-col mb-4">
                    <label for="trueConfigText" class="text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>โค้ดคอนฟิก (URI / Config String):</span>
                        <span id="trueCharCount" class="text-[11px] font-mono text-slate-400">0 ตัวอักษร</span>
                    </label>
                    <textarea id="trueConfigText" rows="6" oninput="updateCharCount('true')" placeholder="วางโค้ด vless://..." class="w-full bg-slate-900 text-rose-400 p-3.5 rounded-2xl border border-slate-800 font-mono text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none transition-all resize-y select-all leading-relaxed"><?= htmlspecialchars($backupData['true_config']) ?></textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100 mt-auto">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyCode('true')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>📋</span> คัดลอก
                        </button>
                        <button type="button" onclick="showQrModal('TRUE 5G', 'trueConfigText')" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>📱</span> ตัวอย่าง QR
                        </button>
                    </div>
                    <button type="button" onclick="resetDefaultConfig('true')" class="text-xs text-slate-400 hover:text-rose-600 transition-colors">
                        รีเซ็ตค่าเริ่มต้น
                    </button>
                </div>
            </div>
        </div>

        <!-- Help & Instructions Card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-slate-200 rounded-3xl p-5 md:p-6 mb-8 shadow-md">
            <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2 mb-3">
                <span class="text-amber-400">💡</span> วิธีการใช้งานระบบไฟล์สำรองฉุกเฉิน (ไม่ต้องพึ่ง AI)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-300 leading-relaxed">
                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/60">
                    <strong class="text-amber-300 font-bold block mb-1">1. เวลาต้องการปิดไฟล์สำรอง:</strong>
                    เมื่อเซิร์ฟเวอร์หลักหายมีปัญหาแล้ว ให้กดสลับสวิตช์ด้านบนเป็น <b>"ปิด"</b> แล้วกดปุ่ม <b>"บันทึกข้อมูล"</b> ระบบจะซ่อนปุ่มไฟล์สำรองบนหน้าเว็บ และ LINE บอทจะแจ้งลูกค้าทันทีว่าเซิร์ฟเวอร์หลักทำงานปกติ
                </div>
                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/60">
                    <strong class="text-emerald-300 font-bold block mb-1">2. เวลาจะใส่โค้ดใหม่ในอนาคต:</strong>
                    หากคุณมีโค้ด VLESS หรือคอนฟิกใหม่ เพียงก๊อปปี้มาวางทับในกล่อง AIS หรือ TRUE แล้วกดสวิตช์เป็น <b>"เปิด"</b> จากนั้นกด <b>"บันทึกข้อมูล"</b> ได้ทันที ระบบอัปเดตแบบเรียลไทม์
                </div>
                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/60">
                    <strong class="text-cyan-300 font-bold block mb-1">3. ลูกค้าเข้าถึงได้จากไหนบ้าง:</strong>
                    - <b>หน้าเว็บ:</b> การ์ดแจ้งเตือนและเมนู "⚡ ไฟล์สำรอง" ในหน้าลูกค้า<br>
                    - <b>LINE Bot:</b> พิมพ์คำว่า "สำรอง", "ขอไฟล์สำรอง", "เน็ตหลุด", หรือกดเมนูเลข 5 บอทจะส่งโค้ดสำรองให้ทันที
                </div>
            </div>
        </div>
    </main>

    <!-- QR Code Preview Modal -->
    <div id="qrModal" onclick="if(event.target === this) closeQrModal()" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[110] hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl transform transition-all">
            <div class="flex items-center justify-between mb-4">
                <h3 id="qrModalTitle" class="font-extrabold text-slate-900 text-sm sm:text-base">ตัวอย่าง QR Code</h3>
                <button type="button" onclick="closeQrModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-all">✕</button>
            </div>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 inline-block mb-3">
                <div id="qrModalWrapper" class="w-52 h-52 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>canvas]:w-full [&>canvas]:h-full"></div>
            </div>
            <p class="text-xs text-slate-500 mb-4">ลูกค้าสามารถใช้แอป V2rayNG, NekoBox, หรือ V2Box สแกน QR Code นี้เพื่อเชื่อมต่อได้ทันที</p>
            <button type="button" onclick="closeQrModal()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl text-xs transition-all">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>

    <script>
        const DEFAULT_AIS = <?= json_encode($defaultAis) ?>;
        const DEFAULT_TRUE = <?= json_encode($defaultTrue) ?>;

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => {
                    menu.classList.remove('opacity-0');
                    drawer.classList.remove('-translate-x-full');
                }, 10);
            } else {
                menu.classList.add('opacity-0');
                drawer.classList.add('-translate-x-full');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        function updateCharCount(carrier) {
            const el = document.getElementById(carrier + 'ConfigText');
            const countEl = document.getElementById(carrier + 'CharCount');
            if (el && countEl) {
                countEl.innerText = `${(el.value || '').trim().length} ตัวอักษร`;
            }
        }

        function handleStatusToggle(isChecked) {
            const iconBg = document.getElementById('statusIconBg');
            const icon = document.getElementById('statusIcon');
            const badge = document.getElementById('statusBadge');
            const desc = document.getElementById('statusDesc');

            if (isChecked) {
                iconBg.className = 'w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0 transition-colors bg-emerald-50 text-emerald-600 border border-emerald-200';
                icon.innerText = '🟢';
                badge.className = 'text-xs font-bold px-3 py-1 rounded-full uppercase bg-emerald-100 text-emerald-800 border border-emerald-200';
                badge.innerText = 'เปิดใช้งาน (Active)';
                desc.innerText = 'ขณะนี้เปิดให้ลูกค้าขอไฟล์สำรองได้: เมนูบนเว็บจะแสดง และ LINE Bot จะส่งโค้ดสำรองเมื่อลูกค้าทักหา';
            } else {
                iconBg.className = 'w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0 transition-colors bg-slate-100 text-slate-400 border border-slate-200';
                icon.innerText = '⚪';
                badge.className = 'text-xs font-bold px-3 py-1 rounded-full uppercase bg-slate-100 text-slate-600 border border-slate-200';
                badge.innerText = 'ปิดใช้งาน (Inactive)';
                desc.innerText = 'ขณะนี้ปิดระบบไฟล์สำรอง: เมนูบนเว็บถูกซ่อน และ LINE Bot จะแจ้งลูกค้าว่าเซิร์ฟเวอร์หลักทำงานตามปกติ';
            }
        }

        function copyCode(carrier) {
            const el = document.getElementById(carrier + 'ConfigText');
            if (!el || !el.value.trim()) {
                Toast.fire({ icon: 'warning', title: 'ไม่มีข้อความโค้ดที่จะคัดลอก' });
                return;
            }
            navigator.clipboard.writeText(el.value.trim()).then(() => {
                Toast.fire({ icon: 'success', title: `คัดลอกโค้ดสำรอง ${carrier.toUpperCase()} สำเร็จ!` });
            }).catch(() => {
                el.focus();
                el.select();
                Toast.fire({ icon: 'info', title: 'กดค้างที่กล่องข้อความเพื่อคัดลอก' });
            });
        }

        function resetDefaultConfig(carrier) {
            Swal.fire({
                title: `รีเซ็ตโค้ด ${carrier.toUpperCase()}?`,
                text: 'ต้องการเปลี่ยนโค้ดกลับไปเป็นค่าเริ่มต้นจากระบบหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ใช่, รีเซ็ตเลย',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b'
            }).then((res) => {
                if (res.isConfirmed) {
                    const el = document.getElementById(carrier + 'ConfigText');
                    if (carrier === 'ais') el.value = DEFAULT_AIS;
                    if (carrier === 'true') el.value = DEFAULT_TRUE;
                    updateCharCount(carrier);
                    Toast.fire({ icon: 'success', title: `รีเซ็ตโค้ด ${carrier.toUpperCase()} เรียบร้อยแล้ว (อย่าลืมกดบันทึกข้อมูล)` });
                }
            });
        }

        function showQrModal(title, textareaId) {
            const el = document.getElementById(textareaId);
            const val = el ? el.value.trim() : '';
            if (!val) {
                Toast.fire({ icon: 'warning', title: 'กรุณากรอกโค้ดคอนฟิกก่อนดู QR Code' });
                return;
            }

            document.getElementById('qrModalTitle').innerText = `QR Code: ${title}`;
            const wrapper = document.getElementById('qrModalWrapper');
            wrapper.innerHTML = '';

            try {
                if (typeof QRCode !== 'undefined') {
                    new QRCode(wrapper, {
                        text: val,
                        width: 208,
                        height: 208,
                        colorDark: "#0f172a",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } else {
                    wrapper.innerHTML = '<span class="text-xs text-slate-400">QRCode library not loaded</span>';
                }
            } catch (e) {
                wrapper.innerHTML = `<span class="text-xs text-rose-500">สร้าง QR ไม่สำเร็จ: โค้ดยาวเกินไป</span>`;
            }

            const modal = document.getElementById('qrModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeQrModal() {
            const modal = document.getElementById('qrModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function saveBackupSettings() {
            const isEnabled = document.getElementById('backupEnabledToggle').checked;
            const aisConfig = document.getElementById('aisConfigText').value.trim();
            const trueConfig = document.getElementById('trueConfigText').value.trim();

            if (isEnabled && (!aisConfig || !trueConfig)) {
                const conf = await Swal.fire({
                    title: 'ข้อความโค้ดว่างเปล่า?',
                    text: 'คุณเปิดใช้งานระบบไฟล์สำรอง แต่มีช่องโค้ดสำรองที่เว้นว่างไว้ ต้องการให้ระบบใช้ค่าเริ่มต้นหรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ใช้ค่าเริ่มต้นและบันทึก',
                    cancelButtonText: 'กลับไปแก้ไข'
                });
                if (!conf.isConfirmed) return;
            }

            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                text: 'กำลังอัปเดตไฟล์คอนฟิกสำรองและซิงค์กับ LINE Bot',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch('api/admin_backup.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        enabled: isEnabled,
                        ais_config: aisConfig,
                        true_config: trueConfig
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    if (data.data?.updated_at) {
                        document.getElementById('lblUpdatedAt').innerText = data.data.updated_at;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ!',
                        text: isEnabled 
                            ? 'ระบบเปิดไฟล์สำรองแล้ว หน้าเว็บและ LINE บอทพร้อมให้บริการทันที' 
                            : 'ระบบปิดไฟล์สำรองแล้ว หน้าเว็บจะซ่อนปุ่ม และบอทจะแจ้งว่าระบบหลักปกติ',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: data.message || 'ไม่สามารถบันทึกข้อมูลได้',
                        confirmButtonText: 'ตกลง'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'เชื่อมต่อล้มเหลว',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonText: 'ตกลง'
                });
            }
        }

        // Initialize char counts on load
        document.addEventListener('DOMContentLoaded', () => {
            updateCharCount('ais');
            updateCharCount('true');
        });
    </script>
</body>
</html>
