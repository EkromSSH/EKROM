<?php
require_once __DIR__ . '/api/db.php';
$db = get_db();
$sysWarn = $db->query('SELECT v2ray_warning, ssh_warning FROM system_warnings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$initV2ray = !empty($sysWarn['v2ray_warning']) ? $sysWarn['v2ray_warning'] : "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
$initSsh = !empty($sysWarn['ssh_warning']) ? $sysWarn['ssh_warning'] : "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$proto = $isHttps ? "https://" : "http://";
$currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$autoWebhookUrl = $proto . $currentHost . '/api/line_webhook.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ตั้งค่าระบบ - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }

        /* Scrolling Container */
        #mainContent {
            -webkit-overflow-scrolling: touch;
        }

        .swal2-popup {
            font-family: 'Anuphan', 'Inter', sans-serif !important;
        }

        /* Auto scroll margin for form inputs when focused/scrolled */
        input:not([type="checkbox"]):not([type="radio"]), textarea, select {
            scroll-margin-top: 85px;
        }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="admin-shell bg-slate-50 text-gray-800 antialiased flex flex-col md:flex-row h-screen overflow-hidden">

    <div class="admin-mobile-nav md:hidden bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

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
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
                <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-2 sm:mt-4">🏠 กลับหน้าลูกค้า</a>
            </nav>
            <div class="drawer-footer shrink-0 mt-auto pt-4 border-t border-slate-700 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))]">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex" id="desktopSidebar">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <main id="mainContent" class="flex-grow p-4 md:p-6 lg:p-10 pb-64 md:pb-48 overflow-y-auto relative">
        <header class="mb-5">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 flex items-center gap-2">
                <span>ตั้งค่าระบบ (Settings)</span>
                <span class="text-xl">⚙️</span>
            </h1>
            <p class="text-gray-500 mt-1 text-xs sm:text-sm">จัดการลิงก์ Webhook, ความปลอดภัยของผู้ดูแลระบบ และตั้งค่าหน้าร้านค้า</p>
        </header>

        <!-- ⚡ แถบปุ่มทางลัดเมนูตั้งค่า (Quick Settings Navigation Bar) -->
        <div class="sticky top-0 z-30 bg-slate-50/95 backdrop-blur-md py-2.5 -mx-4 px-4 md:-mx-6 md:px-6 lg:-mx-10 lg:px-10 mb-6 border-b border-gray-200/80 shadow-xs">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
                <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider shrink-0 flex items-center gap-1 mr-1">
                    <span>⚡ ทางลัด:</span>
                </span>
                
                <button type="button" onclick="scrollToSection('sec-admin-security')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 border border-gray-200 hover:border-rose-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🔐</span> รหัสผ่าน & PIN
                </button>
                
                <button type="button" onclick="scrollToSection('sec-slipok')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-pink-50 hover:text-pink-700 text-slate-700 border border-gray-200 hover:border-pink-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🧾</span> ตรวจสลิป SlipOK
                </button>
                
                <button type="button" onclick="scrollToSection('sec-discord')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 border border-gray-200 hover:border-indigo-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🔔</span> Discord Webhooks
                </button>
                
                <button type="button" onclick="scrollToSection('sec-warnings')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-amber-50 hover:text-amber-700 text-slate-700 border border-gray-200 hover:border-amber-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>⚠️</span> คำเตือนก่อนซื้อ
                </button>
                
                <button type="button" onclick="scrollToSection('sec-turnstile')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-orange-50 hover:text-orange-700 text-slate-700 border border-gray-200 hover:border-orange-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🛡️</span> Cloudflare
                </button>
                
                <button type="button" onclick="scrollToSection('sec-contact')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 border border-gray-200 hover:border-emerald-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>💬</span> ช่องทางติดต่อ
                </button>
                
                <button type="button" onclick="scrollToSection('sec-line-bot')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-green-50 hover:text-green-700 text-slate-700 border border-gray-200 hover:border-green-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🤖</span> LINE Bot
                </button>
                
                <button type="button" onclick="scrollToSection('sec-announcement')" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 border border-gray-200 hover:border-rose-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>📣</span> ข่าวสาร & ประกาศ
                </button>
                
                <button type="button" onclick="scrollToSection('system-update-section')" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold transition-all shadow-sm shadow-indigo-600/30 active:scale-95 cursor-pointer">
                    <span>🚀</span> ตรวจสอบอัปเดต
                </button>
            </div>
        </div>

        <!-- 🟢 0. ส่วนจัดการรหัสผ่านและ PIN ผู้ดูแลระบบ -->
        <div id="sec-admin-security" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-rose-50 via-pink-50 to-rose-50 border-b border-rose-100 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500/20 to-pink-500/10 text-rose-600 flex items-center justify-center text-2xl shadow-sm border border-rose-200/50 shrink-0">
                        🔐
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">จัดการรหัสผ่าน & รหัส PIN แอดมิน</h2>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 tracking-wide uppercase">Admin Auth</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">เปลี่ยนรหัสผ่านเข้าสู่ระบบหลังบ้าน และรหัส PIN 4-6 หลักสำหรับยืนยันความปลอดภัย</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm flex items-center gap-2">
                        <span>🛡️</span> กำหนดรหัสผ่านใหม่และรหัส PIN ผู้ดูแลระบบ
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รหัสผ่านเดิม (Current Password)</label>
                            <input type="password" id="admin_old_pass" placeholder="เช่น admin123" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">เว้นว่างได้หากกำลังใช้รหัสเริ่มต้น</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รหัสผ่านใหม่ (New Password) <span class="text-rose-500">*</span></label>
                            <input type="password" id="admin_new_pass" placeholder="อย่างน้อย 6 ตัวอักษร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                            <p class="text-[10px] text-rose-500 mt-1 font-medium">อย่างน้อย 6 ตัวอักษร (ห้ามใช้รหัสเดิม)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รหัส PIN แอดมินใหม่ (4-6 หลัก)</label>
                            <input type="text" id="admin_new_pin" maxlength="6" placeholder="เช่น 123456 (เว้นว่างได้)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้ยืนยันความปลอดภัยหลังบ้าน</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button type="button" onclick="saveAdminCredentials()" id="btnSaveAdminCreds" class="bg-gradient-to-r from-rose-600 via-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-rose-600/30 w-full md:w-auto flex items-center justify-center gap-2 cursor-pointer">
                        <span>🔐</span> บันทึกรหัสผ่านและ PIN ใหม่
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 1. ส่วนตั้งค่าระบบตรวจสอบสลิป -->
        <div id="sec-slipok" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-pink-50 via-rose-50 to-pink-50 border-b border-pink-100 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-500/20 to-rose-500/10 text-pink-600 flex items-center justify-center text-2xl shadow-sm border border-pink-200/50 shrink-0">
                    🧾
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">ตั้งค่าระบบตรวจสลิปโอนเงิน (SlipOK API)</h2>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 tracking-wide uppercase">Auto Slip OCR</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">เชื่อมต่อ SlipOK สำหรับสแกนสลิป ตรวจสอบยอดเงิน และเติมเงินอัตโนมัติ 24 ชม.</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">🔑 ข้อมูลเชื่อมต่อ SlipOK API</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">SlipOK Branch ID <span class="text-rose-500">*</span></label>
                            <input type="text" id="slipok_branch_id" placeholder="เช่น 73171 (เฉพาะตัวเลข)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all font-mono">
                            <p class="text-[10px] text-gray-500 mt-1">รหัสตัวเลขสาขา เช่น <strong>73171</strong> (ใส่เฉพาะตัวเลข ไม่ต้องใส่ URL เต็ม)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">SlipOK API Key (x-authorization) <span class="text-rose-500">*</span></label>
                            <input type="password" id="slipok_api_key" placeholder="เช่น SLIPOKxxxxxx" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all font-mono">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้ส่งใน Header: x-authorization จากหน้าแดชบอร์ด slipok.com</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ยอดเติมเงินขั้นต่ำ (บาท)</label>
                            <input type="number" id="slip_min_amount" value="30" min="30" step="1" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all">
                            <p class="text-[10px] text-pink-600 font-bold mt-1">ขั้นต่ำเริ่มต้น 30 บาท (ต่ำกว่านี้จะไม่สามารถสร้างรายการได้)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">เวลาหมดอายุรายการเติมเงิน (นาที)</label>
                            <input type="number" id="slip_expire_minutes" value="15" min="5" max="60" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">เวลานับถอยหลังในการโอนเงินและแนบสลิป (แนะนำ 15 นาที)</p>
                        </div>
                    </div>

                    <!-- ⚡ แถบทดสอบการเชื่อมต่อ SlipOK API -->
                    <div class="p-4 sm:p-5 bg-gradient-to-r from-pink-500/10 via-rose-500/5 to-pink-500/10 border border-pink-200 rounded-2xl mb-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3.5 w-full sm:w-auto">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-pink-500 to-rose-500 flex items-center justify-center text-white text-lg shrink-0 shadow-md shadow-pink-500/25">
                                ⚡
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>ทดสอบเชื่อมต่อระบบ SlipOK</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700 border border-pink-200">
                                        Live Check
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">กดทดสอบส่งคำขอเพื่อตรวจสอบความถูกต้องของ Branch ID และ API Key</p>
                            </div>
                        </div>
                        <button type="button" onclick="testSlipokConnection()" id="btnTestSlipok" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 hover:from-pink-600 hover:to-rose-600 active:scale-95 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-pink-500/25 transition-all flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                            <span>⚡</span> ทดสอบการเชื่อมต่อ SlipOK ทันที
                        </button>
                    </div>

                    <!-- 💡 ข้อควรรู้เกี่ยวกับ SlipOK -->
                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 mb-4 flex items-start gap-2.5 leading-relaxed">
                        <span class="text-base shrink-0">💡</span>
                        <div>
                            <strong class="text-amber-950">ข้อควรรู้เกี่ยวกับ SlipOK:</strong>
                            หากระบบแจ้งว่า <strong>"Package ของคุณหมดอายุแล้ว"</strong> หมายความว่าแพ็กเกจบัญชีการใช้งานของคุณบนเว็บไซต์ <a href="https://slipok.com" target="_blank" class="text-pink-600 hover:text-pink-700 underline font-bold">slipok.com</a> หมดอายุหรือโควตาสลิปหมด (ไม่ใช่สลิปธนาคารหมดอายุ) กรุณาเข้าสู่ระบบ slipok.com เพื่อต่ออายุแพ็กเกจหรือซื้อโควตาสลิปเพิ่ม
                        </div>
                    </div>

                    <!-- 🟢 การตั้งค่าความปลอดภัย ชื่อ และบัญชี -->
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">🔒 ความปลอดภัย & บัญชีรับเงิน</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">เลขบัญชีธนาคาร / พร้อมเพย์ <span class="text-rose-500">*</span></label>
                            <input type="text" id="slip_receiver_account" placeholder="เช่น 0812345678" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 font-mono transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้สร้าง Dynamic QR และตรวจสอบบัญชีผู้รับในสลิป</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ชื่อบัญชี (ภาษาไทย)</label>
                            <input type="text" id="slip_receiver_th" placeholder="เช่น นายสมชาย ใจดี" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ชื่อบัญชี (ภาษาอังกฤษ)</label>
                            <input type="text" id="slip_receiver_en" placeholder="เช่น SOMCHAI JAIDEE (เว้นว่างได้)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">เบอร์ TrueMoney สำหรับรับซองอังเปา</label>
                            <input type="text" id="truemoney_phone" inputmode="numeric" maxlength="10" placeholder="เช่น 0812345678" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 font-mono transition-all">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveSlipSettings()" id="btnSaveSlip" class="bg-gradient-to-r from-pink-600 via-rose-600 to-pink-600 hover:from-pink-700 hover:to-rose-700 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-pink-600/30 w-full md:w-auto flex items-center justify-center gap-2 cursor-pointer">
                        <span>💾</span> บันทึกตั้งค่าสลิป
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 2. ส่วนตั้งค่า Discord Webhooks -->
        <div id="sec-discord" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-indigo-50 via-[#5865F2]/10 to-indigo-50 border-b border-indigo-100 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#5865F2]/15 text-[#5865F2] flex items-center justify-center text-2xl shadow-sm border border-[#5865F2]/20 shrink-0">
                    👾
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">Discord Webhooks</h2>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-100 text-[#5865F2] tracking-wide uppercase">Realtime Bot</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">รับการแจ้งเตือนยอดซื้อ เติมเงิน ต่ออายุ และกิจกรรมสมาชิกเข้าห้อง Discord</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-2">🛒 1. แจ้งเตือน ซื้อสินค้า</label>
                        <input type="url" id="wb_buy" placeholder="วางลิงก์ Webhook สำหรับแจ้งลูกค้าซื้อไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 font-mono transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-2">💰 2. แจ้งเตือน เติมเงิน</label>
                        <input type="url" id="wb_topup" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนเติมเงิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 font-mono transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-2">♻️ 3. แจ้งเตือน ต่ออายุ</label>
                        <input type="url" id="wb_renew" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนต่ออายุไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 font-mono transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-2">✨ 4. แจ้งเตือน สมัครสมาชิกใหม่</label>
                        <input type="url" id="wb_register" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนสมัคร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 font-mono transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-2">🔑 5. แจ้งเตือน เข้าสู่ระบบ</label>
                        <input type="url" id="wb_login" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนล็อกอิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 font-mono transition-all">
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWebhooks()" class="bg-gradient-to-r from-[#5865F2] to-indigo-600 hover:from-[#4752C4] hover:to-indigo-700 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-[#5865F2]/30 w-full md:w-auto flex items-center justify-center gap-2 cursor-pointer">
                        <span>💾</span> บันทึก Webhooks
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 3. ส่วนตั้งค่าคำแนะนำก่อนสั่งซื้อ -->
        <div id="sec-warnings" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border-b border-amber-100 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-orange-500/10 text-orange-500 flex items-center justify-center text-2xl shadow-sm border border-amber-200/50 shrink-0">
                    📢
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">ข้อความคำแนะนำก่อนสั่งซื้อ (Store Warnings)</h2>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 tracking-wide uppercase">Buyer Notice</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">ข้อความแจ้งเตือนเงื่อนไข แอปที่ใช้เชื่อมต่อ และข้อห้ามก่อนลูกค้าสร้างไฟล์</p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center font-bold">🔐</div>
                            <h3 class="font-bold text-slate-900">คำแนะนำระบบ SSH</h3>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3">พิมพ์ 1 บรรทัด = 1 ข้อย่อย (ใช้แท็ก <b>&lt;b&gt;ข้อความ&lt;/b&gt;</b> ทำตัวหนาได้)</p>
                        <textarea id="warningSsh" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all h-56 resize-none"><?= htmlspecialchars($initSsh, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center font-bold">⚡</div>
                            <h3 class="font-bold text-slate-900">คำแนะนำระบบ V2Ray</h3>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3">พิมพ์ 1 บรรทัด = 1 ข้อย่อย (ใช้แท็ก <b>&lt;b&gt;ข้อความ&lt;/b&gt;</b> ทำตัวหนาได้)</p>
                        <textarea id="warningV2ray" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 transition-all h-56 resize-none"><?= htmlspecialchars($initV2ray, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWarnings()" id="btnSaveWarnings" class="bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-orange-500/30 w-full md:w-auto flex items-center justify-center gap-2 cursor-pointer">
                        <span>💾</span> บันทึกคำแนะนำ
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 4. ส่วนตั้งค่า Cloudflare Turnstile -->
        <div id="sec-turnstile" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border-b border-orange-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-amber-500/10 text-orange-500 flex items-center justify-center text-2xl shadow-sm border border-orange-200/50 shrink-0">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">ตั้งค่าความปลอดภัย Cloudflare Turnstile</h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 tracking-wide uppercase">Bot Guard</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">ระบบยืนยันตัวตนว่าไม่ใช่บอท/หุ่นยนต์ ในหน้าเข้าสู่ระบบและสมัครสมาชิก</p>
                    </div>
                </div>
                
                <!-- 🌟 สวิตช์เปิด-ปิด ดีไซน์พรีเมียม -->
                <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl border border-slate-200 shadow-xs shrink-0 self-start sm:self-auto">
                    <span id="turnstile_status_badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-emerald-50 text-emerald-600 border border-emerald-200">
                        <span id="turnstile_status_dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="turnstile_status_text">เปิดใช้งาน</span>
                    </span>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="turnstile_enabled" class="sr-only peer" onchange="updateTurnstileToggleUI()">
                        <!-- Slider Track -->
                        <div class="w-[52px] h-[28px] bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-gradient-to-r peer-checked:from-emerald-500 peer-checked:to-teal-500 transition-all duration-300 shadow-inner"></div>
                        <!-- Slider Knob -->
                        <div class="absolute left-[3px] top-[3px] bg-white w-[22px] h-[22px] rounded-full transition-all duration-300 peer-checked:translate-x-6 shadow-md shadow-slate-400/40 flex items-center justify-center">
                            <svg id="turnstile_knob_icon" class="w-3 h-3 text-emerald-600 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                            🔑 Turnstile Site Key (Public)
                        </label>
                        <input type="text" id="turnstile_site_key" placeholder="ตัวอย่าง: 0x4AAAAAA..." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 font-mono transition-all">
                        <p class="text-[11px] text-gray-500 mt-1">คีย์สาธารณะสำหรับแสดง Widget หน้าเว็บ (นำมาจาก Cloudflare Dashboard)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                            🔐 Turnstile Secret Key (Private)
                        </label>
                        <input type="text" id="turnstile_secret_key" placeholder="ตัวอย่าง: 0x4AAAAAA..." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 font-mono transition-all">
                        <p class="text-[11px] text-gray-500 mt-1">คีย์ลับสำหรับตรวจสอบความถูกต้องที่ฝั่ง Server (นำมาจาก Cloudflare Dashboard)</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-amber-600 text-lg">💡</span>
                    <div class="text-xs text-amber-800 leading-relaxed">
                        <b>คำแนะนำสำหรับลูกค้า/แอดมิน:</b> สามารถขอคีย์ฟรีได้ที่ <a href="https://dash.cloudflare.com/" target="_blank" class="underline font-bold text-amber-900">Cloudflare Dashboard</a> &gt; เมนู <b>Turnstile</b> &gt; กด <b>Add site</b> กรอกโดเมนร้านค้าของคุณ แล้วนำ Site Key และ Secret Key มากรอกที่นี่ แล้วกดบันทึกได้ทันที
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveTurnstileSettings()" id="btnSaveTurnstile" class="bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-orange-500/30 w-full md:w-auto flex items-center justify-center gap-2 cursor-pointer">
                        <span>💾</span> บันทึกตั้งค่า Cloudflare
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 5. ส่วนตั้งค่าช่องทางติดต่อ (LINE / Facebook / เวลาทำการ) -->
        <div id="sec-contact" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-emerald-600 text-2xl drop-shadow-sm">💬</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">ตั้งค่าช่องทางติดต่อลูกค้า (Contact Channels)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">กำหนดข้อมูล LINE, LINE @, LINE กลุ่ม/OpenChat, Facebook และเวลาทำการที่แสดงในหน้า contact.php</p>
                    </div>
                </div>
                <a href="contact.php" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 rounded-xl text-xs font-bold transition-all shadow-2xs">
                    <span>👁️</span> ดูหน้าติดต่อจริง
                </a>
            </div>

            <div class="p-6 space-y-6">
                <!-- 1. เวลาทำการ & สถานะ -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">
                        <span>🕒</span> เวลาทำการ & สถานะการให้บริการ
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ข้อความเวลาทำการ</label>
                            <input type="text" id="cnt_work_hours" placeholder="เช่น 09:00 - 21:00 น." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">แสดงในหัวข้อหน้าติดต่อ</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">วันเปิดบริการ</label>
                            <input type="text" id="cnt_work_days" placeholder="เช่น เปิดบริการทุกวัน (จันทร์ - อาทิตย์)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">สถานะแอดมิน</label>
                            <select id="cnt_work_status" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                <option value="online">🟢 ออนไลน์ (ตลอดเวลา)</option>
                                <option value="auto">⏰ ตามเวลาทำการ (09:00 - 21:00 น.)</option>
                                <option value="offline">🌙 พักผ่อน (ออฟไลน์)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. LINE Channels -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-emerald-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#06C755]"></span>
                        <h3 class="font-bold text-slate-900 text-sm">ช่องทาง LINE (LINE Official / LINE ส่วนตัว / LINE กลุ่ม)</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- LINE OA -->
                        <div class="p-4 bg-white rounded-xl border border-emerald-100/80 shadow-2xs">
                            <div class="text-xs font-bold text-emerald-800 mb-2 flex items-center gap-1.5">
                                <span>📲</span> 1. LINE Official Account (LINE @)
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ชื่อที่แสดง</label>
                                    <input type="text" id="cnt_line_oa_name" placeholder="เช่น LINE Official Account" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">LINE ID (สำหรับคัดลอก)</label>
                                    <input type="text" id="cnt_line_oa_id" placeholder="เช่น @ekromshop" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ลิงก์เพิ่มเพื่อน (URL)</label>
                                    <input type="text" id="cnt_line_oa_url" placeholder="เช่น https://line.me/R/ti/p/@ekromshop" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </div>

                        <!-- LINE ส่วนตัว -->
                        <div class="p-4 bg-white rounded-xl border border-teal-100/80 shadow-2xs">
                            <div class="text-xs font-bold text-teal-800 mb-2 flex items-center gap-1.5">
                                <span>👤</span> 2. LINE ส่วนตัวแอดมิน (Personal LINE)
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ชื่อที่แสดง</label>
                                    <input type="text" id="cnt_line_personal_name" placeholder="เช่น LINE ส่วนตัวแอดมิน" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-teal-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">LINE ID</label>
                                    <input type="text" id="cnt_line_personal_id" placeholder="เช่น ekrom_support" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-teal-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ลิงก์ทักแชท (URL)</label>
                                    <input type="text" id="cnt_line_personal_url" placeholder="เช่น https://line.me/ti/p/~ekrom_support" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-teal-500">
                                </div>
                            </div>
                        </div>

                        <!-- LINE กลุ่ม / OpenChat -->
                        <div class="p-4 bg-white rounded-xl border border-emerald-100/80 shadow-2xs">
                            <div class="text-xs font-bold text-emerald-800 mb-2 flex items-center gap-1.5">
                                <span>👥</span> 3. LINE กลุ่ม / OpenChat (Community)
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ชื่อกลุ่ม</label>
                                    <input type="text" id="cnt_line_group_name" placeholder="เช่น กลุ่ม LINE OpenChat" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ลิงก์เข้าร่วมกลุ่ม (URL)</label>
                                    <input type="text" id="cnt_line_group_url" placeholder="เช่น https://line.me/ti/g2/..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">คำอธิบายกลุ่มย่อ</label>
                                    <input type="text" id="cnt_line_group_desc" placeholder="เช่น กลุ่มพูดคุย แจ้งปัญหา และรับอัปเดตไฟล์ VPN" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Facebook & Messenger -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#1877F2]"></span>
                        <h3 class="font-bold text-slate-900 text-sm">ช่องทาง Facebook & Messenger</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-white rounded-xl border border-blue-100 shadow-2xs">
                            <div class="text-xs font-bold text-blue-800 mb-2 flex items-center gap-1.5">
                                <span>🌐</span> Facebook Fanpage
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ชื่อเพจ</label>
                                    <input type="text" id="cnt_fb_page_name" placeholder="เช่น Facebook Fanpage" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ลิงก์หน้าเพจ (URL)</label>
                                    <input type="text" id="cnt_fb_page_url" placeholder="เช่น https://www.facebook.com/share/..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white rounded-xl border border-sky-100 shadow-2xs">
                            <div class="text-xs font-bold text-sky-800 mb-2 flex items-center gap-1.5">
                                <span>⚡</span> กลุ่มพูดคุย & แจ้งปัญหา (Messenger)
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ชื่อกลุ่ม</label>
                                    <input type="text" id="cnt_msg_group_name" placeholder="เช่น กลุ่มแชท Messenger" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">ลิงก์กลุ่ม Messenger (URL)</label>
                                    <input type="text" id="cnt_msg_group_url" placeholder="เช่น https://m.me/j/..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-sky-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. หมายเหตุเพิ่มเติม -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ข้อความหมายเหตุใต้หน้าติดต่อ</label>
                    <input type="text" id="cnt_note" placeholder="เช่น หากติดต่อหลังเวลาทำการ ทีมงานจะรีบตอบกลับในเช้าวันถัดไปครับ" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <!-- ปุ่มบันทึก -->
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" onclick="saveContactSettings()" id="btnSaveContactSettings" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-600/30 w-full md:w-auto flex items-center justify-center gap-2 active:scale-95">
                        <span>💾</span> บันทึกตั้งค่าช่องทางติดต่อ
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 6. ส่วนตั้งค่า LINE Messaging API & LINE Bot -->
        <div id="sec-line-bot" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-emerald-600 via-[#06C755] to-teal-600 border-b border-emerald-500/20 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl shadow-inner border border-white/25 shrink-0">
                        💬
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-white tracking-tight">ตั้งค่า LINE Bot (Messaging API)</h2>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-white/20 text-white border border-white/30 tracking-wide uppercase backdrop-blur-sm">LINE OA 24H</span>
                        </div>
                        <p class="text-xs text-emerald-50 mt-0.5 font-normal">ระบบบอทสั่งซื้อ VPN, สแกน QR Code, ตรวจสลิปอัตโนมัติ, เติมเงิน และจัดการไฟล์ VPN ผ่าน LINE</p>
                    </div>
                </div>
                
                <!-- 🌟 สวิตช์เปิด-ปิด ดีไซน์พรีเมียม (Header Status Switch) -->
                <div class="flex items-center gap-3 bg-slate-900/40 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/20 shadow-xs shrink-0 self-start sm:self-auto">
                    <span id="line_bot_status_badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <span id="line_bot_status_dot" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span id="line_bot_status_text">บอทเปิดทำงาน</span>
                    </span>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="line_bot_enabled" class="sr-only peer" checked onchange="updateLineBotToggleLabel()">
                        <!-- Slider Track -->
                        <div class="w-[52px] h-[28px] bg-slate-700/60 peer-focus:outline-none rounded-full peer peer-checked:bg-gradient-to-r peer-checked:from-emerald-400 peer-checked:to-teal-300 transition-all duration-300 shadow-inner border border-white/20"></div>
                        <!-- Slider Knob -->
                        <div class="absolute left-[3px] top-[3px] bg-white w-[22px] h-[22px] rounded-full transition-all duration-300 peer-checked:translate-x-6 shadow-md shadow-slate-900/40 flex items-center justify-center">
                            <svg id="line_bot_knob_icon" class="w-3 h-3 text-emerald-600 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- 1. Webhook URL Card (Developer SaaS Style) -->
                <div class="p-5 sm:p-6 bg-slate-900 rounded-2xl border border-slate-800 text-white shadow-lg shadow-slate-950/20 relative overflow-hidden">
                    <!-- Subtle background glow -->
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-[#06C755]/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">POST</span>
                                <span class="text-xs font-bold text-slate-200">Webhook URL สำหรับใส่ใน LINE Developers Console</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-emerald-950/80 text-emerald-300 border border-emerald-800/80 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    HTTPS Webhook
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-950/90 rounded-xl p-1.5 border border-slate-800/80 shadow-inner">
                            <div class="flex-1 flex items-center gap-2 px-3 py-2 min-w-0">
                                <span class="text-emerald-400 text-sm shrink-0 font-mono select-none">🌐</span>
                                <input type="text" id="line_bot_webhook_url" value="<?= htmlspecialchars($autoWebhookUrl, ENT_QUOTES, 'UTF-8') ?>" readonly class="flex-1 bg-transparent text-xs sm:text-sm font-mono text-emerald-300 outline-none select-all font-semibold tracking-tight truncate cursor-pointer" title="คลิกเพื่อเลือกทั้งหมด" onclick="this.select()">
                            </div>
                            <button type="button" onclick="copyLineWebhookUrl()" id="btnCopyLineWebhook" class="px-5 py-2.5 bg-gradient-to-r from-[#06C755] to-emerald-600 hover:from-[#05b34c] hover:to-emerald-700 active:scale-95 text-white rounded-lg text-xs font-bold transition-all shrink-0 shadow-md shadow-emerald-900/30 flex items-center justify-center gap-2 cursor-pointer">
                                <span id="btnCopyLineWebhookIcon">📋</span>
                                <span id="btnCopyLineWebhookText">คัดลอก URL</span>
                            </button>
                        </div>

                        <!-- 3-Step Setup Guide -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 mt-4 pt-4 border-t border-slate-800/80 text-[11px]">
                            <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[10px] shrink-0">1</span>
                                <span class="text-slate-300">กดปุ่ม <b class="text-white">คัดลอก URL</b> ด้านบน</span>
                            </div>
                            <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[10px] shrink-0">2</span>
                                <span class="text-slate-300">วางใน <b class="text-white">Webhook URL</b> ที่ <a href="https://developers.line.biz/console/" target="_blank" class="text-emerald-400 underline font-bold hover:text-emerald-300">LINE Developers</a> แล้วกด Verify</span>
                            </div>
                            <div class="flex items-start gap-2.5 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[10px] shrink-0">3</span>
                                <span class="text-slate-300">เปิดสวิตช์ <b class="text-white">Use webhook</b> เป็น <b class="text-emerald-400 font-bold">ON</b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Key & Token Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bot Basic ID & Name -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200 space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span>🏷️</span> ข้อมูลบอท (Bot Information)
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Bot Basic ID (LINE ID) <span class="text-rose-500">*</span></label>
                            <input type="text" id="line_bot_basic_id" placeholder="เช่น @578infzg" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">ดูได้จากหน้า Messaging API ใน LINE Developers</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ชื่อบอท (Bot Name)</label>
                            <input type="text" id="line_bot_name" placeholder="เช่น EkromVPN" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>

                    <!-- Channel Secret -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200 space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span>🔑</span> Channel Secret <span class="text-rose-500">*</span>
                        </h3>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Channel Secret (Basic settings)</label>
                            <input type="text" id="line_bot_channel_secret" placeholder="กรอก Channel Secret 32 ตัวอักษร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-xs outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono transition-all">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้สำหรับตรวจสอบความถูกต้องของข้อความ Webhook Signature</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Channel Access Token (Long-lived) -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <span>🎫</span> Channel Access Token (long-lived) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Messaging API tab &gt; Issue</span>
                    </div>
                    <textarea id="line_bot_access_token" rows="3" placeholder="วาง Channel Access Token (long-lived) ที่นี่..." class="w-full bg-white border border-gray-200 rounded-xl p-3 text-xs font-mono text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 resize-y transition-all"></textarea>
                    <p class="text-[10px] text-gray-500">Token สำหรับส่งข้อความ Flex, ส่ง QR Code, ข้อความตอบกลับ และดึงรูปสลิปจาก LINE</p>
                </div>

                <!-- กล่องผลการทดสอบการเชื่อมต่อ -->
                <div id="line_bot_test_result" class="hidden p-4 rounded-2xl border text-xs transition-all duration-300"></div>

                <!-- ปุ่มบันทึก & ทดสอบ -->
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <button type="button" onclick="testLineBotConnection()" id="btnTestLineBot" class="w-full sm:w-auto px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer shadow-xs">
                        <span>🧪</span> ทดสอบเชื่อมต่อ LINE Bot
                    </button>
                    <button type="button" onclick="saveLineBotSettings()" id="btnSaveLineBot" class="w-full sm:w-auto bg-gradient-to-r from-emerald-600 via-[#06C755] to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                        <span>💾</span> บันทึกตั้งค่า LINE Bot
                    </button>
                </div>
            </div>
        </div>

        <section id="sec-announcement" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-pink-50 via-rose-50 to-pink-50 border-b border-pink-100 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-500/20 to-rose-500/10 text-pink-600 flex items-center justify-center text-2xl shadow-sm border border-pink-200/50 shrink-0">
                        📣
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">ประกาศข่าวสารถึงลูกค้า (Announcements)</h2>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 tracking-wide uppercase">Broadcast</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">ลูกค้าจะเห็นประกาศแจ้งเตือนบนหน้า Dashboard ของร้านค้านี้</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <input id="announcementTitle" maxlength="150" placeholder="หัวข้อประกาศ เช่น แจ้งปิดปรับปรุงเซิร์ฟเวอร์ หรือโปรโมชั่นใหม่" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all font-semibold">
                <textarea id="announcementMessage" maxlength="2000" rows="3" placeholder="รายละเอียดข้อความประกาศ..." class="w-full bg-slate-50 border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 resize-y transition-all"></textarea>
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <select id="announcementType" class="bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 font-medium">
                        <option value="info">🔵 ข้อมูลทั่วไป (Info)</option>
                        <option value="success">🟢 สำเร็จ / โปรโมชั่น (Success)</option>
                        <option value="warning">🟠 แจ้งเตือนสำคัญ (Warning)</option>
                        <option value="danger">🔴 ด่วนมาก / ปิดปรับปรุง (Danger)</option>
                    </select>
                    <button onclick="publishAnnouncement()" class="bg-gradient-to-r from-pink-600 via-rose-600 to-pink-600 hover:from-pink-700 hover:to-rose-700 active:scale-95 text-white font-bold px-8 py-2.5 rounded-xl shadow-lg shadow-pink-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>📤</span> เผยแพร่ประกาศ
                    </button>
                </div>
                <div id="announcementList" class="space-y-2 pt-4 border-t border-gray-100"><div class="text-sm text-slate-400">กำลังโหลดประกาศ...</div></div>
            </div>
        </section>

        <!-- 🟢 6. ส่วนตรวจสอบและอัปเดตระบบ (System Update) -->
        <div id="system-update-section" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border-b border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-indigo-600 text-2xl drop-shadow-sm">🚀</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">อัปเดตระบบร้านค้า (System Update)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">ตรวจสอบและอัปเดตระบบเป็นเวอร์ชันล่าสุดได้ในคลิกเดียว (สำรองฐานข้อมูลอัตโนมัติ)</p>
                    </div>
                </div>
                <button type="button" onclick="checkSystemUpdate(true)" id="btnCheckUpdate" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-indigo-600/20 active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>ตรวจสอบเวอร์ชันใหม่</span>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- สถานะปัจจุบัน -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-gray-200">
                        <div class="text-xs font-bold text-slate-500 mb-1 flex items-center gap-1.5">
                            <span>💻</span> เวอร์ชันที่กำลังใช้งาน (Current Version)
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span id="sys_current_commit" class="font-mono text-xs px-2.5 py-1 bg-slate-200 text-slate-700 font-bold rounded-lg">กำลังโหลด...</span>
                            <span id="sys_status_badge" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600">กำลังตรวจสอบสถานะ</span>
                        </div>
                        <p id="sys_current_msg" class="text-xs text-slate-600 mt-2 line-clamp-1 italic">-</p>
                        <p id="sys_current_date" class="text-[11px] text-slate-400 mt-1">-</p>
                    </div>

                    <!-- เวอร์ชันล่าสุดของระบบ -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-indigo-100">
                        <div class="text-xs font-bold text-indigo-700 mb-1 flex items-center gap-1.5">
                            <span>☁️</span> เวอร์ชันล่าสุดของระบบ (Latest Version)
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span id="sys_latest_commit" class="font-mono text-xs px-2.5 py-1 bg-indigo-100 text-indigo-700 font-bold rounded-lg">-</span>
                            <span id="sys_behind_badge" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600">-</span>
                        </div>
                        <p id="sys_latest_msg" class="text-xs text-slate-600 mt-2 line-clamp-1 italic">-</p>
                        <p id="sys_latest_date" class="text-[11px] text-slate-400 mt-1">-</p>
                    </div>
                </div>

                <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl text-xs text-amber-800 flex items-start gap-2.5 leading-relaxed">
                    <span class="text-base leading-none mt-0.5">💡</span>
                    <div>
                        <strong>ระบบสำรองข้อมูลอัตโนมัติ:</strong> เมื่อกดอัปเดต ระบบจะทำการสำรองไฟล์ฐานข้อมูล <code class="font-mono font-bold bg-amber-100 px-1 py-0.5 rounded">database.sqlite</code> (ผู้ใช้, ยอดเงิน, สต็อกเซิร์ฟเวอร์) ไว้ก่อนดึงโค้ดใหม่ และนำกลับมาใช้งานต่อทันที ข้อมูลของร้านค้าจะไม่สูญหายแน่นอนครับ
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่มลอยเลื่อนกลับขึ้นด้านบนสุด -->
        <button id="btnScrollTop" onclick="scrollToTop()" class="fixed bottom-6 right-6 z-40 px-3.5 py-2.5 rounded-2xl bg-slate-900/85 hover:bg-slate-900 text-white shadow-xl backdrop-blur-md transition-all duration-300 opacity-0 pointer-events-none hover:scale-105 active:scale-95 flex items-center justify-center gap-1.5 text-xs font-bold border border-slate-700/50 cursor-pointer" title="เลื่อนขึ้นบนสุด">
            <span>⬆️</span><span class="hidden sm:inline">ขึ้นบนสุด</span>
        </button>
    </main>

    <script>
        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            el.classList.add('ring-4', 'ring-indigo-400/50', 'transition-all', 'duration-300');
            setTimeout(() => {
                el.classList.remove('ring-4', 'ring-indigo-400/50');
            }, 1800);
        }

        function scrollToTop() {
            const container = document.getElementById('mainContent');
            if (container) {
                container.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const scrollContainer = document.getElementById('mainContent');
            const btn = document.getElementById('btnScrollTop');
            if (scrollContainer && btn) {
                scrollContainer.addEventListener('scroll', () => {
                    if (scrollContainer.scrollTop > 350) {
                        btn.classList.remove('opacity-0', 'pointer-events-none');
                        btn.classList.add('opacity-100');
                    } else {
                        btn.classList.add('opacity-0', 'pointer-events-none');
                        btn.classList.remove('opacity-100');
                    }
                });
            }
        });

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0'); drawer.classList.add('-translate-x-full');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        async function loadSlipSettings() {
            try {
                const res = await fetch('api/admin_manage.php?action=get_slip_settings');
                const data = await res.json();
                if (data.status === 'success') {
                    const s = data.data;
                    if (document.getElementById('slipok_branch_id')) document.getElementById('slipok_branch_id').value = s.slipok_branch_id || '';
                    if (document.getElementById('slipok_api_key')) document.getElementById('slipok_api_key').value = s.slipok_api_key || '';
                    if (document.getElementById('slip_min_amount')) document.getElementById('slip_min_amount').value = s.slip_min_amount || 30;
                    if (document.getElementById('slip_expire_minutes')) document.getElementById('slip_expire_minutes').value = s.slip_expire_minutes || 15;
                    if (document.getElementById('slip_receiver_th')) document.getElementById('slip_receiver_th').value = s.slip_receiver_th || '';
                    if (document.getElementById('slip_receiver_en')) document.getElementById('slip_receiver_en').value = s.slip_receiver_en || '';
                    if (document.getElementById('slip_receiver_account')) document.getElementById('slip_receiver_account').value = s.slip_receiver_account || '';
                    if (document.getElementById('truemoney_phone')) document.getElementById('truemoney_phone').value = s.truemoney_phone || '';
                }
            } catch(e) {}
        }

        async function saveSlipSettings() {
            const btn = document.getElementById('btnSaveSlip');
            btn.innerText = 'กำลังบันทึก... ⏳'; btn.disabled = true;

            const payload = { 
                action: 'save_slip_settings', 
                slip_api_mode: 'slipok',
                slipok_branch_id: document.getElementById('slipok_branch_id').value.trim(),
                slipok_api_key: document.getElementById('slipok_api_key').value.trim(),
                slip_min_amount: parseFloat(document.getElementById('slip_min_amount').value) || 30,
                slip_expire_minutes: parseInt(document.getElementById('slip_expire_minutes').value) || 15,
                slip_receiver_th: document.getElementById('slip_receiver_th').value.trim(),
                slip_receiver_en: document.getElementById('slip_receiver_en').value.trim(),
                slip_receiver_account: document.getElementById('slip_receiver_account').value.trim(),
                promptpay_number: document.getElementById('slip_receiver_account').value.trim(),
                promptpay_name: document.getElementById('slip_receiver_th').value.trim(),
                truemoney_phone: document.getElementById('truemoney_phone').value.trim()
            };

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') Swal.fire('สำเร็จ!', data.message, 'success');
                else Swal.fire('ผิดพลาด', data.message, 'error');
            } catch(e) { Swal.fire('Error', 'การเชื่อมต่อมีปัญหา', 'error'); }
            
            btn.innerText = '💾 บันทึกตั้งค่าสลิป'; btn.disabled = false;
        }

        async function testSlipokConnection() {
            const btn = document.getElementById('btnTestSlipok');
            const branch = document.getElementById('slipok_branch_id').value.trim();
            const key = document.getElementById('slipok_api_key').value.trim();
            if (!branch || !key) {
                return Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูล', text: 'กรุณากรอกทั้ง SlipOK Branch ID และ API Key ก่อนทดสอบครับ' });
            }
            if (btn) { btn.disabled = true; btn.innerHTML = '<span>⏳</span> กำลังทดสอบ...'; }
            Swal.fire({
                title: 'กำลังทดสอบเชื่อมต่อ SlipOK...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'test_slipok', branch_id: branch, api_key: key })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'เชื่อมต่อสำเร็จ 🎉', text: data.message });
                } else if (data.status === 'warning') {
                    Swal.fire({ icon: 'warning', title: 'พบข้อควรทราบ ⚠️', text: data.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'การเชื่อมต่อไม่สำเร็จ ❌', text: data.message });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้' });
            } finally {
                if (btn) { btn.disabled = false; btn.innerHTML = '<span>⚡</span> ทดสอบการเชื่อมต่อ SlipOK ทันที'; }
            }
        }

        async function loadWebhooks() {
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_webhooks' })
                });
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    if(data.data.buy) document.getElementById('wb_buy').value = data.data.buy;
                    if(data.data.topup) document.getElementById('wb_topup').value = data.data.topup;
                    if(data.data.renew) document.getElementById('wb_renew').value = data.data.renew;
                    if(data.data.register) document.getElementById('wb_register').value = data.data.register;
                    if(data.data.login) document.getElementById('wb_login').value = data.data.login;
                }
            } catch(e) {}
        }

        async function saveWebhooks() {
            const payload = {
                action: 'save_webhooks',
                webhooks: {
                    buy: document.getElementById('wb_buy').value,
                    topup: document.getElementById('wb_topup').value,
                    renew: document.getElementById('wb_renew').value,
                    register: document.getElementById('wb_register').value,
                    login: document.getElementById('wb_login').value
                }
            };
            Swal.fire({ title: 'กำลังบันทึก...', didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch('api/admin_manage.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    if(data.status === 'success') Swal.fire('บันทึกสำเร็จ!', '', 'success');
                    else Swal.fire('ผิดพลาด', data.message, 'error');
                } catch(err) { Swal.fire('Error Backend', 'เซิร์ฟเวอร์ตอบกลับผิดพลาด', 'error'); }
            } catch(e) { Swal.fire('ผิดพลาด', 'การเชื่อมต่อขัดข้อง', 'error'); }
        }

        async function loadAnnouncements() {
            try { const r=await fetch('api/announcements.php?action=admin_list'); const d=await r.json(); const box=document.getElementById('announcementList'); if(d.status!=='success') return; const esc=s=>{const x=document.createElement('div');x.textContent=s;return x.innerHTML}; box.innerHTML=d.data.length?d.data.map(a=>`<div class="flex items-start justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"><div><p class="font-bold text-sm text-slate-800">${esc(a.title)}</p><p class="text-xs text-slate-500 mt-1 whitespace-pre-line">${esc(a.message)}</p></div><div class="flex gap-2 shrink-0"><button onclick="editAnnouncement(${a.id})" class="text-xs font-bold text-pink-600">แก้ไข</button><button onclick="deleteAnnouncement(${a.id})" class="text-xs font-bold text-red-500">ลบ</button><button onclick="toggleAnnouncement(${a.id})" class="text-xs font-bold ${a.is_active==1?'text-orange-500':'text-emerald-600'}">${a.is_active==1?'ปิด':'เปิด'}</button></div></div>`).join(''):'<div class="text-sm text-slate-400">ยังไม่มีประกาศ</div>'; window.announcementCache=d.data; } catch(e) { document.getElementById('announcementList').innerText='โหลดประกาศไม่สำเร็จ'; }
        }
        async function publishAnnouncement() { const title=document.getElementById('announcementTitle').value.trim(),message=document.getElementById('announcementMessage').value.trim(),type=document.getElementById('announcementType').value; if(!title||!message)return Swal.fire('ข้อมูลไม่ครบ','กรุณากรอกหัวข้อและรายละเอียด','warning'); const r=await fetch('api/announcements.php?action=create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title,message,type})});const d=await r.json();if(d.status==='success'){document.getElementById('announcementTitle').value='';document.getElementById('announcementMessage').value='';loadAnnouncements();Swal.fire('เผยแพร่แล้ว','ลูกค้าจะเห็นประกาศใน Dashboard','success')}else Swal.fire('ผิดพลาด',d.message,'error'); }
        async function toggleAnnouncement(id) { await fetch('api/announcements.php?action=toggle',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})}); loadAnnouncements(); }
        async function editAnnouncement(id) { const a=(window.announcementCache||[]).find(x=>Number(x.id)===Number(id)); if(!a)return; const r=await Swal.fire({title:'แก้ไขประกาศ',html:`<input id="editAnnTitle" class="swal2-input" value="${a.title.replace(/"/g,'&quot;')}"><textarea id="editAnnMsg" class="swal2-textarea">${a.message}</textarea><select id="editAnnType" class="swal2-select"><option value="info">ข้อมูลทั่วไป</option><option value="success">โปรโมชั่น</option><option value="warning">แจ้งเตือน</option><option value="danger">สำคัญ</option></select>`,showCancelButton:true,confirmButtonText:'บันทึก',cancelButtonText:'ยกเลิก',didOpen:()=>{document.getElementById('editAnnType').value=a.type}}); if(!r.isConfirmed)return; const res=await fetch('api/announcements.php?action=update',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,title:document.getElementById('editAnnTitle').value,message:document.getElementById('editAnnMsg').value,type:document.getElementById('editAnnType').value})});const d=await res.json();if(d.status==='success'){loadAnnouncements();Swal.fire('บันทึกแล้ว','','success')}else Swal.fire('ผิดพลาด',d.message,'error'); }
        async function deleteAnnouncement(id) { const c=await Swal.fire({title:'ลบประกาศนี้?',icon:'warning',showCancelButton:true,confirmButtonText:'ลบ',cancelButtonText:'ยกเลิก'});if(!c.isConfirmed)return;await fetch('api/announcements.php?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});loadAnnouncements(); }

        async function loadWarnings() {
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_warnings' })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('warningSsh').value = data.data.warning_ssh;
                    document.getElementById('warningV2ray').value = data.data.warning_v2ray;
                }
            } catch(e) {}
        }

        async function saveWarnings() {
            const btn = document.getElementById('btnSaveWarnings');
            btn.innerText = 'กำลังบันทึก... ⏳'; btn.disabled = true;

            const payload = {
                action: 'save_warnings',
                warning_ssh: document.getElementById('warningSsh').value,
                warning_v2ray: document.getElementById('warningV2ray').value
            };

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') Swal.fire('สำเร็จ!', data.message, 'success');
                    else Swal.fire('ผิดพลาด', data.message, 'error');
                } catch(err) { Swal.fire('Error Backend', 'เซิร์ฟเวอร์ตอบกลับผิดพลาด', 'error'); }
            } catch(e) { Swal.fire('Error', 'การเชื่อมต่อมีปัญหา', 'error'); }
            
            btn.innerText = '💾 บันทึกคำแนะนำ'; btn.disabled = false;
        }

        function updateTurnstileToggleUI() {
            const chk = document.getElementById('turnstile_enabled');
            const badge = document.getElementById('turnstile_status_badge');
            const dot = document.getElementById('turnstile_status_dot');
            const text = document.getElementById('turnstile_status_text');
            const knobIcon = document.getElementById('turnstile_knob_icon');

            if (!chk || !badge) return;

            if (chk.checked) {
                badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-emerald-50 text-emerald-600 border border-emerald-200";
                if (dot) dot.className = "w-2 h-2 rounded-full bg-emerald-500 animate-pulse";
                if (text) text.innerText = "เปิดใช้งาน";
                if (knobIcon) {
                    knobIcon.className = "w-3 h-3 text-emerald-600 transition-all duration-300";
                    knobIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>`;
                }
            } else {
                badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-slate-100 text-slate-500 border border-slate-200";
                if (dot) dot.className = "w-2 h-2 rounded-full bg-slate-400";
                if (text) text.innerText = "ปิดการใช้งาน";
                if (knobIcon) {
                    knobIcon.className = "w-3 h-3 text-slate-400 transition-all duration-300";
                    knobIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>`;
                }
            }
        }

        async function loadTurnstileSettings() {
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_turnstile_settings' })
                });
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    const s = data.data;
                    const chk = document.getElementById('turnstile_enabled');
                    if (chk) {
                        chk.checked = !!s.enabled;
                        updateTurnstileToggleUI();
                    }
                    if (document.getElementById('turnstile_site_key')) document.getElementById('turnstile_site_key').value = s.site_key || '';
                    if (document.getElementById('turnstile_secret_key')) document.getElementById('turnstile_secret_key').value = s.secret_key || '';
                }
            } catch(e) {}
        }

        async function saveTurnstileSettings() {
            const btn = document.getElementById('btnSaveTurnstile');
            btn.innerText = 'กำลังบันทึก... ⏳'; btn.disabled = true;

            const payload = {
                action: 'save_turnstile_settings',
                enabled: document.getElementById('turnstile_enabled').checked ? 1 : 0,
                site_key: document.getElementById('turnstile_site_key').value.trim(),
                secret_key: document.getElementById('turnstile_secret_key').value.trim()
            };

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ!', data.message || 'บันทึกตั้งค่า Cloudflare เรียบร้อย', 'success');
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch(err) {
                    Swal.fire('Error Backend', 'เซิร์ฟเวอร์ตอบกลับผิดพลาด', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'การเชื่อมต่อมีปัญหา', 'error');
            }

            btn.innerText = '💾 บันทึกตั้งค่า Cloudflare'; btn.disabled = false;
        }

        async function saveAdminCredentials() {
            const btn = document.getElementById('btnSaveAdminCreds');
            const oldPass = document.getElementById('admin_old_pass').value;
            const newPass = document.getElementById('admin_new_pass').value;
            const newPin = document.getElementById('admin_new_pin').value.trim();

            if (!newPass) {
                Swal.fire('ข้อผิดพลาด', 'กรุณากรอกรหัสผ่านใหม่', 'warning');
                return;
            }
            if (newPass.length < 6) {
                Swal.fire('ข้อผิดพลาด', 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'warning');
                return;
            }
            if (newPass === 'admin123' || newPass === 'reseller123') {
                Swal.fire('ข้อผิดพลาด', 'กรุณาตั้งรหัสผ่านใหม่ที่ไม่ใช่รหัสเริ่มต้น', 'warning');
                return;
            }
            if (newPin && !/^\d{4,6}$/.test(newPin)) {
                Swal.fire('ข้อผิดพลาด', 'รหัส PIN ต้องเป็นตัวเลข 4 - 6 หลัก', 'warning');
                return;
            }

            btn.disabled = true;
            btn.innerText = 'กำลังบันทึก... ⏳';

            try {
                const res = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        old_password: oldPass,
                        new_password: newPass,
                        new_pin: newPin
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ!', data.message, 'success');
                    document.getElementById('admin_old_pass').value = '';
                    document.getElementById('admin_new_pass').value = '';
                    document.getElementById('admin_new_pin').value = '';
                } else {
                    Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>🔐</span> บันทึกรหัสผ่านและ PIN ใหม่';
            }
        }

        async function loadContactSettings() {
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_contact_settings' })
                });
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    const c = data.data;
                    if (document.getElementById('cnt_work_hours')) document.getElementById('cnt_work_hours').value = c.work_hours || '';
                    if (document.getElementById('cnt_work_days')) document.getElementById('cnt_work_days').value = c.work_days || '';
                    if (document.getElementById('cnt_work_status')) document.getElementById('cnt_work_status').value = c.work_status || 'online';
                    
                    if (document.getElementById('cnt_line_oa_name')) document.getElementById('cnt_line_oa_name').value = c.line_oa_name || '';
                    if (document.getElementById('cnt_line_oa_id')) document.getElementById('cnt_line_oa_id').value = c.line_oa_id || '';
                    if (document.getElementById('cnt_line_oa_url')) document.getElementById('cnt_line_oa_url').value = c.line_oa_url || '';

                    if (document.getElementById('cnt_line_personal_name')) document.getElementById('cnt_line_personal_name').value = c.line_personal_name || '';
                    if (document.getElementById('cnt_line_personal_id')) document.getElementById('cnt_line_personal_id').value = c.line_personal_id || '';
                    if (document.getElementById('cnt_line_personal_url')) document.getElementById('cnt_line_personal_url').value = c.line_personal_url || '';

                    if (document.getElementById('cnt_line_group_name')) document.getElementById('cnt_line_group_name').value = c.line_group_name || '';
                    if (document.getElementById('cnt_line_group_url')) document.getElementById('cnt_line_group_url').value = c.line_group_url || '';
                    if (document.getElementById('cnt_line_group_desc')) document.getElementById('cnt_line_group_desc').value = c.line_group_desc || '';

                    if (document.getElementById('cnt_fb_page_name')) document.getElementById('cnt_fb_page_name').value = c.facebook_page_name || '';
                    if (document.getElementById('cnt_fb_page_url')) document.getElementById('cnt_fb_page_url').value = c.facebook_page_url || '';

                    if (document.getElementById('cnt_msg_group_name')) document.getElementById('cnt_msg_group_name').value = c.messenger_group_name || '';
                    if (document.getElementById('cnt_msg_group_url')) document.getElementById('cnt_msg_group_url').value = c.messenger_group_url || '';

                    if (document.getElementById('cnt_note')) document.getElementById('cnt_note').value = c.contact_note || '';
                }
            } catch(e) { console.error('Failed to load contact settings', e); }
        }

        function cleanContactUrl(str) {
            if (!str) return '';
            const m = str.match(/(https?:\/\/[^\s"'<>]+)/i);
            return m ? m[1] : str.trim();
        }

        async function saveContactSettings() {
            const btn = document.getElementById('btnSaveContactSettings');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> กำลังบันทึก...';

            const rawGroupUrl = document.getElementById('cnt_line_group_url').value.trim();
            let groupName = document.getElementById('cnt_line_group_name').value.trim();
            if ((!groupName || groupName === 'กลุ่ม LINE OpenChat') && rawGroupUrl.includes('"')) {
                const nameMatch = rawGroupUrl.match(/"([^"]+)"/);
                if (nameMatch) {
                    groupName = nameMatch[1];
                    document.getElementById('cnt_line_group_name').value = groupName;
                }
            }

            const cleanGroupUrl = cleanContactUrl(rawGroupUrl);
            document.getElementById('cnt_line_group_url').value = cleanGroupUrl;

            const payload = {
                action: 'save_contact_settings',
                work_hours: document.getElementById('cnt_work_hours').value.trim(),
                work_days: document.getElementById('cnt_work_days').value.trim(),
                work_status: document.getElementById('cnt_work_status').value,
                line_oa_name: document.getElementById('cnt_line_oa_name').value.trim(),
                line_oa_id: document.getElementById('cnt_line_oa_id').value.trim(),
                line_oa_url: cleanContactUrl(document.getElementById('cnt_line_oa_url').value),
                line_personal_name: document.getElementById('cnt_line_personal_name').value.trim(),
                line_personal_id: document.getElementById('cnt_line_personal_id').value.trim(),
                line_personal_url: cleanContactUrl(document.getElementById('cnt_line_personal_url').value),
                line_group_name: groupName || 'กลุ่ม LINE OpenChat',
                line_group_url: cleanGroupUrl,
                line_group_desc: document.getElementById('cnt_line_group_desc').value.trim(),
                facebook_page_name: document.getElementById('cnt_fb_page_name').value.trim(),
                facebook_page_url: cleanContactUrl(document.getElementById('cnt_fb_page_url').value),
                messenger_group_name: document.getElementById('cnt_msg_group_name').value.trim(),
                messenger_group_url: cleanContactUrl(document.getElementById('cnt_msg_group_url').value),
                contact_note: document.getElementById('cnt_note').value.trim()
            };

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ! 🎉', data.message || 'บันทึกช่องทางติดต่อเรียบร้อยแล้ว', 'success');
                } else {
                    Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถบันทึกได้', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>💾</span> บันทึกตั้งค่าช่องทางติดต่อ';
            }
        }

        // ==========================================
        // 🤖 LINE Bot Settings Functions
        // ==========================================
        function updateLineBotToggleLabel() {
            const toggle = document.getElementById('line_bot_enabled');
            const isChecked = toggle ? toggle.checked : true;
            const badge = document.getElementById('line_bot_status_badge');
            const dot = document.getElementById('line_bot_status_dot');
            const text = document.getElementById('line_bot_status_text');
            const icon = document.getElementById('line_bot_knob_icon');

            if (isChecked) {
                if (badge) badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                if (text) text.innerText = 'บอทเปิดทำงาน';
                if (icon) {
                    icon.className = 'w-3 h-3 text-emerald-600 transition-all duration-300';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>';
                }
            } else {
                if (badge) badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 bg-rose-500/20 text-rose-300 border border-rose-400/30';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-rose-400';
                if (text) text.innerText = 'บอทปิดใช้งาน';
                if (icon) {
                    icon.className = 'w-3 h-3 text-rose-500 transition-all duration-300';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>';
                }
            }
        }

        async function loadLineBotSettings() {
            try {
                const res = await fetch('api/admin_manage.php?action=get_line_bot_settings');
                const d = await res.json();
                if (d.status === 'success' && d.data) {
                    const data = d.data;
                    const toggle = document.getElementById('line_bot_enabled');
                    if (toggle) toggle.checked = (data.enabled == 1);
                    document.getElementById('line_bot_basic_id').value = data.bot_basic_id || '';
                    document.getElementById('line_bot_name').value = data.bot_name || 'EkromVPN';
                    document.getElementById('line_bot_channel_secret').value = data.channel_secret || '';
                    document.getElementById('line_bot_access_token').value = data.channel_access_token || '';
                    if (data.webhook_url) {
                        document.getElementById('line_bot_webhook_url').value = data.webhook_url;
                    } else {
                        document.getElementById('line_bot_webhook_url').value = window.location.origin + '/api/line_webhook.php';
                    }
                    updateLineBotToggleLabel();
                }
            } catch (err) {
                console.error('Failed to load LINE Bot settings', err);
            }
        }

        function copyLineWebhookUrl() {
            const urlInput = document.getElementById('line_bot_webhook_url');
            const btn = document.getElementById('btnCopyLineWebhook');
            const icon = document.getElementById('btnCopyLineWebhookIcon');
            const text = document.getElementById('btnCopyLineWebhookText');
            if (!urlInput) return;

            const copySuccess = () => {
                if (icon && text) {
                    icon.innerText = '✓';
                    text.innerText = 'คัดลอกแล้ว!';
                    if (btn) {
                        btn.classList.remove('from-[#06C755]', 'to-emerald-600');
                        btn.classList.add('from-teal-600', 'to-emerald-700', 'scale-105');
                        setTimeout(() => {
                            icon.innerText = '📋';
                            text.innerText = 'คัดลอก URL';
                            btn.classList.remove('from-teal-600', 'to-emerald-700', 'scale-105');
                            btn.classList.add('from-[#06C755]', 'to-emerald-600');
                        }, 2000);
                    }
                }
                Swal.fire({
                    icon: 'success',
                    title: 'คัดลอก Webhook URL สำเร็จ! 📋',
                    html: `
                        <div class="p-3 bg-slate-900 text-emerald-400 font-mono text-xs rounded-xl break-all select-all shadow-inner my-2 border border-slate-800">
                            ${urlInput.value}
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            นำไปวางในช่อง <b>Webhook URL</b> ที่ <b>LINE Developers Console &gt; Messaging API</b> แล้วกด <b>Verify</b> และเปิด <b>Use webhook</b> ได้เลยครับ
                        </p>
                    `,
                    timer: 3500,
                    showConfirmButton: true,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#06C755'
                });
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(urlInput.value).then(copySuccess).catch(() => {
                    urlInput.select();
                    document.execCommand('copy');
                    copySuccess();
                });
            } else {
                urlInput.select();
                document.execCommand('copy');
                copySuccess();
            }
        }

        async function saveLineBotSettings() {
            const btn = document.getElementById('btnSaveLineBot');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin text-xs">⏳</span> กำลังบันทึก...';

            const payload = {
                action: 'save_line_bot_settings',
                enabled: document.getElementById('line_bot_enabled').checked ? 1 : 0,
                bot_basic_id: document.getElementById('line_bot_basic_id').value.trim(),
                bot_name: document.getElementById('line_bot_name').value.trim(),
                channel_secret: document.getElementById('line_bot_channel_secret').value.trim(),
                channel_access_token: document.getElementById('line_bot_access_token').value.trim(),
                webhook_url: document.getElementById('line_bot_webhook_url').value.trim()
            };

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const d = await res.json();
                if (d.status === 'success') {
                    Swal.fire('สำเร็จ! 🎉', d.message || 'บันทึกการตั้งค่า LINE Bot เรียบร้อยแล้ว', 'success');
                    updateLineBotToggleLabel();
                } else {
                    Swal.fire('ผิดพลาด', d.message || 'ไม่สามารถบันทึกได้', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        async function testLineBotConnection() {
            const btn = document.getElementById('btnTestLineBot');
            const token = document.getElementById('line_bot_access_token').value.trim();
            const resultBox = document.getElementById('line_bot_test_result');
            
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin text-xs">⏳</span> กำลังทดสอบ...';

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'test_line_bot',
                        channel_access_token: token
                    })
                });
                const d = await res.json();
                
                resultBox.classList.remove('hidden');
                if (d.status === 'success') {
                    const info = d.bot_info || {};
                    resultBox.className = 'p-4 rounded-2xl border bg-emerald-50 border-emerald-200 text-slate-800 text-xs flex items-center gap-3';
                    resultBox.innerHTML = `
                        ${info.pictureUrl ? `<img src="${info.pictureUrl}" class="w-12 h-12 rounded-full border border-emerald-300 shadow-2xs shrink-0">` : '<div class="w-12 h-12 rounded-full bg-emerald-200 flex items-center justify-center text-xl shrink-0">🤖</div>'}
                        <div class="flex-1">
                            <div class="font-bold text-sm text-emerald-900">${info.displayName || 'EkromVPN'} <span class="text-xs font-mono font-normal text-emerald-700">(${info.basicId || ''})</span></div>
                            <div class="text-[11px] text-emerald-700 mt-0.5">✓ เชื่อมต่อ Messaging API สำเร็จ! บอทพร้อมทำงาน 100%</div>
                        </div>
                    `;
                    Swal.fire({
                        icon: 'success',
                        title: 'เชื่อมต่อ LINE Bot สำเร็จ! 🎉',
                        html: `
                            <div class="flex items-center justify-center gap-3 my-3">
                                ${info.pictureUrl ? `<img src="${info.pictureUrl}" class="w-14 h-14 rounded-full border-2 border-emerald-400">` : ''}
                                <div class="text-left">
                                    <div class="font-bold text-base text-slate-900">${info.displayName || 'EkromVPN'}</div>
                                    <div class="text-xs font-mono text-emerald-600">${info.basicId || ''}</div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500">LINE Messaging API ตอบรับและพร้อมให้บริการแล้วครับ</p>
                        `
                    });
                } else {
                    resultBox.className = 'p-4 rounded-2xl border bg-rose-50 border-rose-200 text-rose-800 text-xs';
                    resultBox.innerHTML = `❌ <b>ทดสอบไม่สำเร็จ:</b> ${d.message || 'ไม่สามารถเชื่อมต่อได้ กรุณาตรวจสอบ Access Token'}`;
                    Swal.fire('เชื่อมต่อไม่สำเร็จ ❌', d.message || 'กรุณาตรวจสอบ Channel Access Token', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        async function checkSystemUpdate(showToast = false) {
            const btn = document.getElementById('btnCheckUpdate');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn && showToast) {
                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin text-xs">⏳</span> กำลังตรวจสอบ...';
            }

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'check_system_update' })
                });
                const d = await res.json();
                if (d.status === 'success') {
                    const data = d.data;
                    document.getElementById('sys_current_commit').innerText = data.current_commit;
                    document.getElementById('sys_current_msg').innerText = data.current_message || '-';
                    document.getElementById('sys_current_date').innerText = 'อัปเดตล่าสุด: ' + (data.current_date || '-');
                    document.getElementById('sys_latest_commit').innerText = data.latest_commit;
                    document.getElementById('sys_latest_msg').innerText = data.latest_message || '-';
                    document.getElementById('sys_latest_date').innerText = 'วันที่เผยแพร่: ' + (data.latest_date || '-');

                    const statusBadge = document.getElementById('sys_status_badge');
                    const behindBadge = document.getElementById('sys_behind_badge');

                    if (data.has_update) {
                        statusBadge.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 animate-pulse";
                        statusBadge.innerText = `มีเวอร์ชันใหม่ (${data.behind_count} อัปเดต)`;
                        behindBadge.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 font-bold";
                        behindBadge.innerText = 'พร้อมอัปเดต 🚀';
                        if (showToast) {
                            const result = await Swal.fire({
                                icon: 'info',
                                title: 'พบเวอร์ชันใหม่พร้อมอัปเดต!',
                                html: `
                                    <div class="text-left text-xs sm:text-sm space-y-3 mt-3">
                                        <div class="p-3 bg-slate-50 rounded-xl border border-gray-200">
                                            <div class="text-xs text-slate-500 font-semibold mb-1">💻 เวอร์ชันปัจจุบัน:</div>
                                            <div class="font-mono text-xs font-bold text-slate-700">${data.current_commit} <span class="font-normal text-slate-500">(${data.current_message || '-'})</span></div>
                                        </div>
                                        <div class="p-3 bg-indigo-50/80 rounded-xl border border-indigo-100">
                                            <div class="text-xs text-indigo-700 font-semibold mb-1">🚀 เวอร์ชันใหม่ล่าสุด:</div>
                                            <div class="font-mono text-xs font-bold text-indigo-900">${data.latest_commit} <span class="font-normal text-slate-600">(${data.latest_message || '-'})</span></div>
                                        </div>
                                        <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-xs">
                                            💡 ระบบจะสำรองข้อมูลฐานข้อมูลเดิมให้อัตโนมัติก่อนอัปเดต ข้อมูลไม่สูญหาย
                                        </div>
                                        <p class="text-xs text-slate-600 font-medium text-center pt-1">ต้องการอัปเดตเป็นเวอร์ชันล่าสุดเลยหรือไม่?</p>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'ยืนยัน',
                                cancelButtonText: 'ยกเลิก',
                                confirmButtonColor: '#4f46e5',
                                cancelButtonColor: '#64748b'
                            });

                            if (result.isConfirmed) {
                                performSystemUpdate();
                            }
                        }
                    } else {
                        statusBadge.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700";
                        statusBadge.innerText = 'เวอร์ชันล่าสุดแล้ว ✓';
                        behindBadge.className = "text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600";
                        behindBadge.innerText = 'ระบบเป็นปัจจุบันแล้ว';
                        if (showToast) {
                            Swal.fire('ระบบเป็นเวอร์ชันล่าสุดแล้ว ✓', 'โค้ดในเซิร์ฟเวอร์ของคุณเป็นเวอร์ชันล่าสุดแล้ว ไม่จำเป็นต้องอัปเดตครับ', 'success');
                        }
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                if (btn && showToast) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        }

        function scrollToUpdateSection(smooth = true) {
            const el = document.getElementById('system-update-section');
            const main = document.getElementById('mainContent') || document.querySelector('main');
            if (el) {
                if (main) {
                    if (smooth) {
                        main.scrollTo({ top: el.offsetTop - 24, behavior: 'smooth' });
                    } else {
                        main.scrollTop = el.offsetTop - 24;
                    }
                }
                try {
                    el.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
                } catch (err) {}
            }
        }

        async function performSystemUpdate() {
            Swal.fire({
                title: 'กำลังอัปเดตระบบ...',
                text: 'กรุณารอสักครู่ ห้ามปิดหน้าต่างนี้ ระบบกำลังดาวน์โหลดอัปเดตและสำรองข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'perform_system_update' })
                });
                const d = await res.json();
                if (d.status === 'success') {
                    // อัปเดตข้อมูลเวอร์ชันบนหน้าจอทันที อยู่กับที่ ไม่ต้อง reload หรือเลื่อนจอ
                    await checkSystemUpdate(false);

                    const el = document.getElementById('system-update-section');
                    if (el) {
                        el.classList.add('ring-4', 'ring-emerald-400/40', 'transition-all', 'duration-500');
                        setTimeout(() => el.classList.remove('ring-4', 'ring-emerald-400/40'), 2500);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตระบบสำเร็จ! 🎉',
                        text: d.message || 'ระบบได้รับการอัปเดตเป็นเวอร์ชันล่าสุดแล้ว'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการอัปเดต',
                        text: d.message || 'ไม่สามารถอัปเดตระบบได้ กรุณาตรวจสอบสิทธิ์หรือ Log'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'การเชื่อมต่อขัดข้อง',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์เพื่ออัปเดตได้ กรุณาลองใหม่อีกครั้ง'
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initInputAutoScroll();
            initContactUrlCleaners();
        });

        function initContactUrlCleaners() {
            const lineGroupUrl = document.getElementById('cnt_line_group_url');
            if (lineGroupUrl) {
                const handleClean = function() {
                    const val = this.value;
                    if (val && (val.includes('"') || val.includes('คุณได้รับคำเชิญ') || val.includes(' ') || val.includes('LINE'))) {
                        const cleaned = cleanContactUrl(val);
                        if (cleaned && cleaned.startsWith('http')) {
                            this.value = cleaned;
                            const nameMatch = val.match(/"([^"]+)"/);
                            const nameInput = document.getElementById('cnt_line_group_name');
                            if (nameMatch && nameInput && (!nameInput.value || nameInput.value === 'กลุ่ม LINE OpenChat')) {
                                nameInput.value = nameMatch[1];
                            }
                        }
                    }
                };
                lineGroupUrl.addEventListener('input', handleClean);
                lineGroupUrl.addEventListener('paste', () => setTimeout(handleClean.bind(lineGroupUrl), 50));
            }
        }

        function initInputAutoScroll() {
            const main = document.getElementById('mainContent');
            if (!main) return;

            // ทำงานเฉพาะบนหน้าจอมือถือ/แท็บเล็ตที่มีคีย์บอร์ดเสมือน
            const isTouchMobile = window.innerWidth <= 768 || ('ontouchstart' in window);
            if (!isTouchMobile) return;

            const inputSelector = 'input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, select';
            
            document.querySelectorAll(inputSelector).forEach(el => {
                if (el.dataset.autoScrollAttached) return;
                el.dataset.autoScrollAttached = 'true';
                el.addEventListener('focus', () => {
                    setTimeout(() => {
                        if (document.activeElement === el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }, 220);
                }, { passive: true });
            });
        }

        window.onload = () => {
            initInputAutoScroll();
            loadAnnouncements();
            loadSlipSettings();
            loadWebhooks();
            loadWarnings();
            loadTurnstileSettings();
            loadContactSettings();
            loadLineBotSettings();
            checkSystemUpdate(false);
        };
    </script>

</body>
</html>
