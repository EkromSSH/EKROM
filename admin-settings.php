<?php
require_once __DIR__ . '/api/db.php';
$db = get_db();
$sysWarn = $db->query('SELECT v2ray_warning, ssh_warning FROM system_warnings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$initV2ray = !empty($sysWarn['v2ray_warning']) ? $sysWarn['v2ray_warning'] : "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
$initSsh = !empty($sysWarn['ssh_warning']) ? $sysWarn['ssh_warning'] : "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";
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

        /* Smooth Scrolling Container */
        #mainContent {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* SweetAlert Global & Mobile Enhancements */
        .swal2-container {
            -webkit-overflow-scrolling: touch !important;
            scroll-behavior: smooth;
            z-index: 99999 !important;
        }

        .swal2-popup {
            font-family: 'Anuphan', 'Inter', sans-serif !important;
            border-radius: 1.5rem !important;
            padding: 1.5rem 1.25rem !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }

        .swal2-title {
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .swal2-html-container {
            font-size: 0.925rem !important;
            color: #475569 !important;
        }

        /* Ensure Confirm/OK Buttons are always prominent, styled, and visible */
        .swal2-actions {
            margin-top: 1.25rem !important;
            width: 100% !important;
            gap: 0.5rem !important;
            display: flex !important;
            justify-content: center !important;
        }

        .swal2-styled.swal2-confirm {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            padding: 0.75rem 1.75rem !important;
            border-radius: 0.85rem !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
            transition: all 0.2s ease !important;
        }

        .swal2-styled.swal2-confirm:hover {
            background-color: #4338ca !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4) !important;
        }

        .swal2-styled.swal2-cancel {
            background-color: #64748b !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 0.85rem !important;
            transition: all 0.2s ease !important;
        }

        .swal2-styled.swal2-cancel:hover {
            background-color: #475569 !important;
        }

        /* Mobile Specific Swal Modal Optimization */
        @media (max-width: 768px) {
            .swal2-container {
                align-items: flex-start !important;
                overflow-y: auto !important;
                padding-top: max(1rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) !important;
                padding-bottom: max(18rem, 50vh) !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .swal2-popup {
                width: 100% !important;
                max-width: min(94vw, 480px) !important;
                margin: 0 auto !important;
                padding: 1.25rem 1rem !important;
                border-radius: 1.25rem !important;
            }
            .swal2-actions {
                flex-direction: row !important;
                gap: 0.5rem !important;
            }
            .swal2-actions button {
                flex: 1 1 0% !important;
                min-width: 0 !important;
                padding: 0.75rem 0.5rem !important;
                font-size: 0.92rem !important;
            }
        }

        /* Active Nav Button Highlight */
        .nav-shortcut-btn.active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2) !important;
        }
        .nav-shortcut-btn.active span:first-child {
            transform: scale(1.1);
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

    <main id="mainContent" class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto relative">
        <header class="mb-5">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 flex items-center gap-2">
                <span>ตั้งค่าระบบ (Settings)</span>
                <span class="text-xl">⚙️</span>
            </h1>
            <p class="text-gray-500 mt-1 text-xs sm:text-sm">จัดการลิงก์ Webhook, ความปลอดภัยของผู้ดูแลระบบ และตั้งค่าหน้าร้านค้า</p>
        </header>

        <!-- ⚡ แถบปุ่มทางลัดเมนูตั้งค่า (Quick Settings Navigation Bar) -->
        <div class="sticky top-0 z-30 bg-slate-50/95 backdrop-blur-md py-2.5 -mx-4 px-4 md:-mx-6 md:px-6 lg:-mx-10 lg:px-10 mb-6 border-b border-gray-200/80 shadow-xs">
            <div id="quickNavScrollBox" class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
                <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider shrink-0 flex items-center gap-1 mr-1 select-none">
                    <span>⚡ ทางลัด:</span>
                </span>
                
                <button type="button" data-target="sec-admin-security" onclick="scrollToSection('sec-admin-security')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 border border-gray-200 hover:border-rose-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🔐</span> รหัสผ่าน & PIN
                </button>
                
                <button type="button" data-target="sec-slipok" onclick="scrollToSection('sec-slipok')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-pink-50 hover:text-pink-700 text-slate-700 border border-gray-200 hover:border-pink-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🧾</span> ตรวจสลิป SlipOK
                </button>
                
                <button type="button" data-target="sec-discord" onclick="scrollToSection('sec-discord')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 border border-gray-200 hover:border-indigo-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🔔</span> Discord Webhooks
                </button>
                
                <button type="button" data-target="sec-warnings" onclick="scrollToSection('sec-warnings')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-amber-50 hover:text-amber-700 text-slate-700 border border-gray-200 hover:border-amber-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>⚠️</span> คำเตือนก่อนซื้อ
                </button>
                
                <button type="button" data-target="sec-turnstile" onclick="scrollToSection('sec-turnstile')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-orange-50 hover:text-orange-700 text-slate-700 border border-gray-200 hover:border-orange-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>🛡️</span> Cloudflare
                </button>
                
                <button type="button" data-target="sec-contact" onclick="scrollToSection('sec-contact')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 border border-gray-200 hover:border-emerald-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>💬</span> ช่องทางติดต่อ
                </button>
                
                <button type="button" data-target="sec-announcement" onclick="scrollToSection('sec-announcement')" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 text-slate-700 border border-gray-200 hover:border-rose-300 font-medium transition-all shadow-2xs active:scale-95 cursor-pointer">
                    <span>📣</span> ข่าวสาร & ประกาศ
                </button>
                
                <button type="button" data-target="system-update-section" onclick="handleUpdateShortcutClick()" class="nav-shortcut-btn shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold transition-all shadow-sm shadow-indigo-600/30 active:scale-95 cursor-pointer">
                    <span>🚀</span> ตรวจสอบอัปเดต
                </button>
            </div>
        </div>

        <!-- 🟢 0. ส่วนจัดการรหัสผ่านและ PIN ผู้ดูแลระบบ -->
        <div id="sec-admin-security" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-rose-600 text-2xl drop-shadow-sm">🔐</span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">จัดการรหัสผ่าน & รหัส PIN แอดมิน</h2>
                        <p class="text-xs text-gray-500 mt-0.5">เปลี่ยนรหัสผ่านเข้าสู่ระบบหลังบ้าน และรหัส PIN 4-6 หลักสำหรับยืนยันความปลอดภัย</p>
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
                            <input type="password" id="admin_old_pass" placeholder="เช่น admin123" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-colors duration-150">
                            <p class="text-[10px] text-gray-500 mt-1">เว้นว่างได้หากกำลังใช้รหัสเริ่มต้น</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รหัสผ่านใหม่ (New Password) <span class="text-rose-500">*</span></label>
                            <input type="password" id="admin_new_pass" placeholder="อย่างน้อย 6 ตัวอักษร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-colors duration-150">
                            <p class="text-[10px] text-rose-500 mt-1 font-medium">อย่างน้อย 6 ตัวอักษร (ห้ามใช้รหัสเดิม)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รหัส PIN แอดมินใหม่ (4-6 หลัก)</label>
                            <input type="text" id="admin_new_pin" maxlength="6" placeholder="เช่น 123456 (เว้นว่างได้)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-colors duration-150">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้ยืนยันความปลอดภัยหลังบ้าน</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button type="button" onclick="saveAdminCredentials()" id="btnSaveAdminCreds" class="bg-gradient-to-r from-rose-600 via-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 active:scale-95 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-rose-600/30 w-full md:w-auto flex items-center justify-center gap-2">
                        <span>🔐</span> บันทึกรหัสผ่านและ PIN ใหม่
                    </button>
                </div>
            </div>
        </div>

        <!-- 🟢 1. ส่วนตั้งค่าระบบตรวจสอบสลิป -->
        <div id="sec-slipok" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                <span class="text-pink-600 text-2xl drop-shadow-sm">🧾</span>
                <h2 class="text-lg font-bold text-slate-900">ตั้งค่าระบบตรวจสลิปโอนเงิน (SlipOK API)</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">🔑 ข้อมูลเชื่อมต่อ SlipOK API</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SlipOK Branch ID <span class="text-red-500">*</span></label>
                            <input type="text" id="slipok_branch_id" placeholder="เช่น 73171 (เฉพาะตัวเลข)" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">รหัสตัวเลขสาขา เช่น <strong>73171</strong> (ใส่เฉพาะตัวเลข ไม่ต้องใส่ URL เต็ม)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SlipOK API Key (x-authorization) <span class="text-red-500">*</span></label>
                            <input type="password" id="slipok_api_key" placeholder="เช่น SLIPOKxxxxxx" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้ส่งใน Header: x-authorization จากหน้าแดชบอร์ด slipok.com</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ยอดเติมเงินขั้นต่ำ (บาท)</label>
                            <input type="number" id="slip_min_amount" value="30" min="30" step="1" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-pink-600 font-bold mt-1">ขั้นต่ำเริ่มต้น 30 บาท (ต่ำกว่านี้จะไม่สามารถสร้างรายการได้)</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">เวลาหมดอายุรายการเติมเงิน (นาที)</label>
                            <input type="number" id="slip_expire_minutes" value="15" min="5" max="60" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
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
                        <button type="button" onclick="testSlipokConnection()" id="btnTestSlipok" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 hover:from-pink-600 hover:to-rose-600 active:scale-95 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-pink-500/25 transition-all flex items-center justify-center gap-2 shrink-0">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1">เลขบัญชีธนาคาร / พร้อมเพย์ <span class="text-red-500">*</span></label>
                            <input type="text" id="slip_receiver_account" placeholder="เช่น 0812345678" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้สร้าง Dynamic QR และตรวจสอบบัญชีผู้รับในสลิป</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อบัญชี (ภาษาไทย)</label>
                            <input type="text" id="slip_receiver_th" placeholder="เช่น นายสมชาย ใจดี" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อบัญชี (ภาษาอังกฤษ)</label>
                            <input type="text" id="slip_receiver_en" placeholder="เช่น SOMCHAI JAIDEE (เว้นว่างได้)" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">เบอร์ TrueMoney สำหรับรับซองอังเปา</label>
                            <input type="text" id="truemoney_phone" inputmode="numeric" maxlength="10" placeholder="เช่น 0812345678" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-orange-500">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveSlipSettings()" id="btnSaveSlip" class="bg-emerald-500 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/30 w-full md:w-auto">💾 บันทึกตั้งค่าสลิป</button>
                </div>
            </div>
        </div>

        <!-- 🟢 2. ส่วนตั้งค่า Discord Webhooks -->
        <div id="sec-discord" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                <span class="text-[#5865F2] text-2xl drop-shadow-sm">👾</span>
                <h2 class="text-lg font-bold text-slate-900">Discord Webhooks</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">🛒 1. แจ้งเตือน ซื้อสินค้า</label>
                        <input type="url" id="wb_buy" placeholder="วางลิงก์ Webhook สำหรับแจ้งลูกค้าซื้อไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">💰 2. แจ้งเตือน เติมเงิน</label>
                        <input type="url" id="wb_topup" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนเติมเงิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">♻️ 3. แจ้งเตือน ต่ออายุ</label>
                        <input type="url" id="wb_renew" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนต่ออายุไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">✨ 4. แจ้งเตือน สมัครสมาชิกใหม่</label>
                        <input type="url" id="wb_register" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนสมัคร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-colors duration-150">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">🔑 5. แจ้งเตือน เข้าสู่ระบบ</label>
                        <input type="url" id="wb_login" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนล็อกอิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-colors duration-150">
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWebhooks()" class="bg-[#5865F2] text-white font-bold px-8 py-3.5 rounded-xl hover:bg-[#4752C4] transition-all shadow-lg shadow-[#5865F2]/30 w-full md:w-auto">💾 บันทึก Webhooks</button>
                </div>
            </div>
        </div>

        <!-- 🟢 3. ส่วนตั้งค่าคำแนะนำก่อนสั่งซื้อ -->
        <div id="sec-warnings" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                <span class="text-orange-500 text-2xl drop-shadow-sm">📢</span>
                <h2 class="text-lg font-bold text-slate-900">ข้อความคำแนะนำก่อนสั่งซื้อ (Store Warnings)</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center font-bold">🔐</div>
                            <h3 class="font-bold text-slate-900">คำแนะนำระบบ SSH</h3>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3">พิมพ์ 1 บรรทัด = 1 ข้อย่อย (ใช้แท็ก <b>&lt;b&gt;ข้อความ&lt;/b&gt;</b> ทำตัวหนาได้)</p>
                        <textarea id="warningSsh" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-pink-500 transition-colors duration-150 h-56 resize-none"><?= htmlspecialchars($initSsh, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center font-bold">⚡</div>
                            <h3 class="font-bold text-slate-900">คำแนะนำระบบ V2Ray</h3>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3">พิมพ์ 1 บรรทัด = 1 ข้อย่อย (ใช้แท็ก <b>&lt;b&gt;ข้อความ&lt;/b&gt;</b> ทำตัวหนาได้)</p>
                        <textarea id="warningV2ray" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-orange-500 transition-colors duration-150 h-56 resize-none"><?= htmlspecialchars($initV2ray, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWarnings()" id="btnSaveWarnings" class="bg-orange-500 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 w-full md:w-auto">💾 บันทึกคำแนะนำ</button>
                </div>
            </div>
        </div>

        <!-- 🟢 4. ส่วนตั้งค่า Cloudflare Turnstile -->
        <div id="sec-turnstile" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                        <input type="text" id="turnstile_site_key" placeholder="ตัวอย่าง: 0x4AAAAAA..." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 font-mono transition-colors duration-150">
                        <p class="text-[11px] text-gray-500 mt-1">คีย์สาธารณะสำหรับแสดง Widget หน้าเว็บ (นำมาจาก Cloudflare Dashboard)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                            🔐 Turnstile Secret Key (Private)
                        </label>
                        <input type="text" id="turnstile_secret_key" placeholder="ตัวอย่าง: 0x4AAAAAA..." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 font-mono transition-colors duration-150">
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
                    <button onclick="saveTurnstileSettings()" id="btnSaveTurnstile" class="bg-orange-500 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 w-full md:w-auto">💾 บันทึกตั้งค่า Cloudflare</button>
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
                            <input type="text" id="cnt_work_hours" placeholder="เช่น 09:00 - 21:00 น." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-150">
                            <p class="text-[10px] text-gray-500 mt-1">แสดงในหัวข้อหน้าติดต่อ</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">วันเปิดบริการ</label>
                            <input type="text" id="cnt_work_days" placeholder="เช่น เปิดบริการทุกวัน (จันทร์ - อาทิตย์)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-150">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">สถานะแอดมิน</label>
                            <select id="cnt_work_status" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-150">
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
                    <input type="text" id="cnt_note" placeholder="เช่น หากติดต่อหลังเวลาทำการ ทีมงานจะรีบตอบกลับในเช้าวันถัดไปครับ" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-150">
                </div>

                <!-- ปุ่มบันทึก -->
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" onclick="saveContactSettings()" id="btnSaveContactSettings" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-600/30 w-full md:w-auto flex items-center justify-center gap-2 active:scale-95">
                        <span>💾</span> บันทึกตั้งค่าช่องทางติดต่อ
                    </button>
                </div>
            </div>
        </div>

        <section id="sec-announcement" class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8 scroll-mt-24">
            <div class="p-6 bg-gradient-to-r from-pink-50 to-rose-50 border-b border-pink-100">
                <div class="flex items-center gap-3"><span class="text-2xl">📣</span><div><h2 class="text-lg font-bold text-slate-900">ประกาศข่าวสารถึงลูกค้า</h2><p class="text-xs text-slate-500 mt-1">ลูกค้าจะเห็นประกาศในหน้า Dashboard ของร้านนี้</p></div></div>
            </div>
            <div class="p-6 space-y-4">
                <input id="announcementTitle" maxlength="150" placeholder="หัวข้อประกาศ เช่น แจ้งปิดปรับปรุงเซิร์ฟเวอร์" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500">
                <textarea id="announcementMessage" maxlength="2000" rows="3" placeholder="รายละเอียดประกาศ" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 resize-y"></textarea>
                <div class="flex flex-col sm:flex-row gap-3"><select id="announcementType" class="border border-gray-200 rounded-xl px-4 py-3 text-sm"><option value="info">🔵 ข้อมูลทั่วไป</option><option value="success">🟢 สำเร็จ/โปรโมชั่น</option><option value="warning">🟠 แจ้งเตือน</option><option value="danger">🔴 สำคัญ</option></select><button onclick="publishAnnouncement()" class="bg-pink-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-pink-700 shadow-lg shadow-pink-200">📤 เผยแพร่ประกาศ</button></div>
                <div id="announcementList" class="space-y-2 pt-2"><div class="text-sm text-slate-400">กำลังโหลดประกาศ...</div></div>
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
        function updateActiveNavButton(targetId) {
            document.querySelectorAll('.nav-shortcut-btn').forEach(btn => {
                if (btn.getAttribute('data-target') === targetId) {
                    btn.classList.add('active');
                    const container = document.getElementById('quickNavScrollBox');
                    if (container) {
                        const btnLeft = btn.offsetLeft - container.offsetLeft;
                        const scrollPos = btnLeft - (container.clientWidth / 2) + (btn.clientWidth / 2);
                        container.scrollTo({ left: Math.max(0, scrollPos), behavior: 'smooth' });
                    }
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        let isProgrammaticScroll = false;
        function scrollToSection(id, focusFirstInput = true) {
            const el = document.getElementById(id);
            const main = document.getElementById('mainContent') || document.querySelector('main');
            if (!el || !main) return;

            isProgrammaticScroll = true;
            const navBar = document.querySelector('.sticky');
            const navHeight = navBar ? navBar.offsetHeight : 54;

            const mainRect = main.getBoundingClientRect();
            const elRect = el.getBoundingClientRect();
            const targetScrollTop = main.scrollTop + (elRect.top - mainRect.top) - navHeight - 16;

            main.scrollTo({
                top: Math.max(0, targetScrollTop),
                behavior: 'smooth'
            });

            updateActiveNavButton(id);

            el.classList.remove('ring-4', 'ring-indigo-400/50', 'ring-rose-400/50');
            el.classList.add('ring-4', 'ring-indigo-400/50', 'transition-shadow', 'duration-300');
            setTimeout(() => {
                el.classList.remove('ring-4', 'ring-indigo-400/50');
                isProgrammaticScroll = false;
            }, 1000);

            if (focusFirstInput) {
                setTimeout(() => {
                    const input = el.querySelector('input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled])');
                    if (input) {
                        input.focus({ preventScroll: true });
                    }
                }, 380);
            }
        }

        function handleUpdateShortcutClick() {
            scrollToSection('system-update-section', false);
            setTimeout(() => {
                checkSystemUpdate(true);
            }, 450);
        }

        function setupSwalMobileKeyboardScroll(popup) {
            if (!popup) return;
            const container = popup.closest('.swal2-container') || popup.parentElement;
            if (!container) return;

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
                    const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                    const desiredTop = Math.min(90, Math.max(60, vh * 0.18));
                    const safeBottom = vh - 50;

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
        }

        function scrollToTop() {
            const container = document.getElementById('mainContent') || document.querySelector('main');
            if (container) {
                container.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            updateActiveNavButton('');
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
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ! 🎉',
                        text: data.message || 'บันทึกตั้งค่า SlipOK เรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10b981',
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: data.message || 'ไม่สามารถบันทึกได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10b981'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'การเชื่อมต่อมีปัญหา กรุณาลองใหม่อีกครั้ง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10b981'
                });
            } finally {
                btn.innerText = '💾 บันทึกตั้งค่าสลิป'; btn.disabled = false;
            }
        }

        async function testSlipokConnection() {
            const btn = document.getElementById('btnTestSlipok');
            const branch = document.getElementById('slipok_branch_id').value.trim();
            const key = document.getElementById('slipok_api_key').value.trim();
            if (!branch || !key) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูล',
                    text: 'กรุณากรอกทั้ง SlipOK Branch ID และ API Key ก่อนทดสอบครับ',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#ec4899'
                });
            }
            if (btn) { btn.disabled = true; btn.innerHTML = '<span>⏳</span> กำลังทดสอบ...'; }
            Swal.fire({
                title: 'กำลังทดสอบเชื่อมต่อ SlipOK...',
                text: 'กรุณารอสักครู่ ระบบกำลังส่งคำขอตรวจสอบสิทธิ์',
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
                    Swal.fire({
                        icon: 'success',
                        title: 'เชื่อมต่อสำเร็จ 🎉',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10b981'
                    });
                } else if (data.status === 'warning') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'พบข้อควรทราบ ⚠️',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#f59e0b'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'การเชื่อมต่อไม่สำเร็จ ❌',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#ef4444'
                });
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
                    buy: document.getElementById('wb_buy').value.trim(),
                    topup: document.getElementById('wb_topup').value.trim(),
                    renew: document.getElementById('wb_renew').value.trim(),
                    register: document.getElementById('wb_register').value.trim(),
                    login: document.getElementById('wb_login').value.trim()
                }
            };
            Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch('api/admin_manage.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    if(data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ! 🎉',
                            text: 'อัปเดตการตั้งค่า Discord Webhooks เรียบร้อยแล้ว',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#5865F2',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: data.message || 'ไม่สามารถบันทึกได้',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#5865F2'
                        });
                    }
                } catch(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Backend',
                        text: 'เซิร์ฟเวอร์ตอบกลับผิดพลาด',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#5865F2'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'การเชื่อมต่อขัดข้อง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#5865F2'
                });
            }
        }

        async function loadAnnouncements() {
            try {
                const r = await fetch('api/announcements.php?action=admin_list');
                const d = await r.json();
                const box = document.getElementById('announcementList');
                if(d.status !== 'success') return;
                const esc = s => {
                    const x = document.createElement('div');
                    x.textContent = s;
                    return x.innerHTML;
                };
                box.innerHTML = d.data.length ? d.data.map(a => `
                    <div class="flex items-start justify-between gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition-colors">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md ${a.type==='danger'?'bg-red-100 text-red-700':a.type==='warning'?'bg-amber-100 text-amber-700':a.type==='success'?'bg-emerald-100 text-emerald-700':'bg-blue-100 text-blue-700'}">
                                    ${a.type==='danger'?'🔴 สำคัญ':a.type==='warning'?'🟠 แจ้งเตือน':a.type==='success'?'🟢 โปรโมชั่น':'🔵 ทั่วไป'}
                                </span>
                                <p class="font-bold text-sm text-slate-800">${esc(a.title)}</p>
                            </div>
                            <p class="text-xs text-slate-500 whitespace-pre-line leading-relaxed">${esc(a.message)}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button onclick="editAnnouncement(${a.id})" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-pink-600 bg-pink-50 hover:bg-pink-100 transition-colors">✏️ แก้ไข</button>
                            <button onclick="deleteAnnouncement(${a.id})" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition-colors">🗑️ ลบ</button>
                            <button onclick="toggleAnnouncement(${a.id})" class="px-2.5 py-1.5 rounded-lg text-xs font-bold ${a.is_active==1?'text-amber-700 bg-amber-50 hover:bg-amber-100':'text-emerald-700 bg-emerald-50 hover:bg-emerald-100'} transition-colors">${a.is_active==1?'⏸️ ปิด':'▶️ เปิด'}</button>
                        </div>
                    </div>
                `).join('') : '<div class="text-sm text-slate-400 py-3 text-center">ยังไม่มีประกาศในขณะนี้</div>';
                window.announcementCache = d.data;
            } catch(e) {
                document.getElementById('announcementList').innerText = 'โหลดประกาศไม่สำเร็จ';
            }
        }

        async function publishAnnouncement() {
            const title = document.getElementById('announcementTitle').value.trim();
            const message = document.getElementById('announcementMessage').value.trim();
            const type = document.getElementById('announcementType').value;
            if(!title || !message) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกทั้งหัวข้อและรายละเอียดประกาศ',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#db2777'
                });
            }
            try {
                const r = await fetch('api/announcements.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title, message, type })
                });
                const d = await r.json();
                if(d.status === 'success') {
                    document.getElementById('announcementTitle').value = '';
                    document.getElementById('announcementMessage').value = '';
                    loadAnnouncements();
                    Swal.fire({
                        icon: 'success',
                        title: 'เผยแพร่ประกาศแล้ว 🎉',
                        text: 'ลูกค้าจะเห็นประกาศใน Dashboard หน้าร้านค้าทันที',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#db2777',
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: d.message || 'ไม่สามารถเผยแพร่ประกาศได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#db2777'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'การเชื่อมต่อขัดข้อง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#db2777'
                });
            }
        }

        async function toggleAnnouncement(id) {
            await fetch('api/announcements.php?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            loadAnnouncements();
        }

        async function editAnnouncement(id) {
            const a = (window.announcementCache || []).find(x => Number(x.id) === Number(id));
            if(!a) return;

            const esc = s => {
                const x = document.createElement('div');
                x.textContent = s;
                return x.innerHTML;
            };

            const r = await Swal.fire({
                title: '✏️ แก้ไขประกาศข่าวสาร',
                customClass: {
                    container: 'swal-settings-container',
                    popup: 'swal-settings-popup',
                    htmlContainer: 'swal-settings-html'
                },
                html: `
                    <div class="text-left space-y-3.5 mt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">หัวข้อประกาศ (Title) <span class="text-rose-500">*</span></label>
                            <input id="editAnnTitle" type="text" maxlength="150" value="${esc(a.title)}" class="w-full bg-slate-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-colors" placeholder="ระบุหัวข้อประกาศ">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">รายละเอียดประกาศ (Message) <span class="text-rose-500">*</span></label>
                            <textarea id="editAnnMsg" rows="4" maxlength="2000" class="w-full bg-slate-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-colors resize-y" placeholder="ระบุเนื้อหาประกาศ">${esc(a.message)}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ประเภทประกาศ (Category)</label>
                            <select id="editAnnType" class="w-full bg-slate-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-colors">
                                <option value="info">🔵 ข้อมูลทั่วไป (General)</option>
                                <option value="success">🟢 สำเร็จ / โปรโมชั่น (Promotion)</option>
                                <option value="warning">🟠 แจ้งเตือน (Warning)</option>
                                <option value="danger">🔴 สำคัญเร่งด่วน (Urgent)</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '💾 บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                focusConfirm: false,
                didOpen: (popup) => {
                    const sel = document.getElementById('editAnnType');
                    if (sel) sel.value = a.type || 'info';
                    setupSwalMobileKeyboardScroll(popup);
                },
                preConfirm: () => {
                    const title = document.getElementById('editAnnTitle')?.value.trim();
                    const message = document.getElementById('editAnnMsg')?.value.trim();
                    if (!title) {
                        Swal.showValidationMessage('กรุณากรอกหัวข้อประกาศ');
                        return false;
                    }
                    if (!message) {
                        Swal.showValidationMessage('กรุณากรอกรายละเอียดประกาศ');
                        return false;
                    }
                    return {
                        id,
                        title,
                        message,
                        type: document.getElementById('editAnnType')?.value || 'info'
                    };
                }
            });

            if(!r.isConfirmed || !r.value) return;

            try {
                const res = await fetch('api/announcements.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(r.value)
                });
                const d = await res.json();
                if(d.status === 'success') {
                    loadAnnouncements();
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ! 🎉',
                        text: 'อัปเดตข้อมูลประกาศเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48',
                        timer: 2500,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: d.message || 'ไม่สามารถแก้ไขได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48'
                    });
                }
            } catch(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'การเชื่อมต่อขัดข้อง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
            }
        }

        async function deleteAnnouncement(id) {
            const c = await Swal.fire({
                title: 'ยืนยันลบประกาศนี้?',
                text: 'หากลบแล้ว ประกาศนี้จะหายไปจากหน้าร้านค้าของลูกค้าทันที',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '🗑️ ยืนยันลบ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b'
            });
            if(!c.isConfirmed) return;
            try {
                const res = await fetch('api/announcements.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const d = await res.json();
                if(d.status === 'success') {
                    loadAnnouncements();
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบประกาศแล้ว',
                        text: 'นำประกาศออกจากระบบเรียบร้อย',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48',
                        timer: 2000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: d.message || 'ไม่สามารถลบได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'การเชื่อมต่อขัดข้อง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
            }
        }

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
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ! 🎉',
                            text: data.message || 'บันทึกคำแนะนำเรียบร้อยแล้ว',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#ea580c',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: data.message || 'ไม่สามารถบันทึกได้',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#ea580c'
                        });
                    }
                } catch(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Backend',
                        text: 'เซิร์ฟเวอร์ตอบกลับผิดพลาด',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#ea580c'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'การเชื่อมต่อมีปัญหา',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#ea580c'
                });
            } finally {
                btn.innerText = '💾 บันทึกคำแนะนำ'; btn.disabled = false;
            }
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
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ! 🎉',
                            text: data.message || 'บันทึกตั้งค่า Cloudflare เรียบร้อย',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#ea580c',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: data.message || 'ไม่สามารถบันทึกได้',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#ea580c'
                        });
                    }
                } catch(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Backend',
                        text: 'เซิร์ฟเวอร์ตอบกลับผิดพลาด',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#ea580c'
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'การเชื่อมต่อมีปัญหา',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#ea580c'
                });
            } finally {
                btn.innerText = '💾 บันทึกตั้งค่า Cloudflare'; btn.disabled = false;
            }
        }

        async function saveAdminCredentials() {
            const btn = document.getElementById('btnSaveAdminCreds');
            const oldPass = document.getElementById('admin_old_pass').value;
            const newPass = document.getElementById('admin_new_pass').value;
            const newPin = document.getElementById('admin_new_pin').value.trim();

            if (!newPass) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณากรอกรหัสผ่านใหม่',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
            }
            if (newPass.length < 6) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อผิดพลาด',
                    text: 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
            }
            if (newPass === 'admin123' || newPass === 'reseller123') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณาตั้งรหัสผ่านใหม่ที่ไม่ใช่รหัสเริ่มต้น',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
            }
            if (newPin && !/^\d{4,6}$/.test(newPin)) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อผิดพลาด',
                    text: 'รหัส PIN ต้องเป็นตัวเลข 4 - 6 หลัก',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
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
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ! 🎉',
                        text: data.message || 'เปลี่ยนรหัสผ่านและ PIN เรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    document.getElementById('admin_old_pass').value = '';
                    document.getElementById('admin_new_pass').value = '';
                    document.getElementById('admin_new_pin').value = '';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: data.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#e11d48'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#e11d48'
                });
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

        async function saveContactSettings() {
            const btn = document.getElementById('btnSaveContactSettings');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> กำลังบันทึก...';

            const payload = {
                action: 'save_contact_settings',
                work_hours: document.getElementById('cnt_work_hours').value.trim(),
                work_days: document.getElementById('cnt_work_days').value.trim(),
                work_status: document.getElementById('cnt_work_status').value,
                line_oa_name: document.getElementById('cnt_line_oa_name').value.trim(),
                line_oa_id: document.getElementById('cnt_line_oa_id').value.trim(),
                line_oa_url: document.getElementById('cnt_line_oa_url').value.trim(),
                line_personal_name: document.getElementById('cnt_line_personal_name').value.trim(),
                line_personal_id: document.getElementById('cnt_line_personal_id').value.trim(),
                line_personal_url: document.getElementById('cnt_line_personal_url').value.trim(),
                line_group_name: document.getElementById('cnt_line_group_name').value.trim(),
                line_group_url: document.getElementById('cnt_line_group_url').value.trim(),
                line_group_desc: document.getElementById('cnt_line_group_desc').value.trim(),
                facebook_page_name: document.getElementById('cnt_fb_page_name').value.trim(),
                facebook_page_url: document.getElementById('cnt_fb_page_url').value.trim(),
                messenger_group_name: document.getElementById('cnt_msg_group_name').value.trim(),
                messenger_group_url: document.getElementById('cnt_msg_group_url').value.trim(),
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
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ! 🎉',
                        text: data.message || 'บันทึกช่องทางติดต่อเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#059669',
                        showConfirmButton: true,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: data.message || 'ไม่สามารถบันทึกได้',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#059669'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'การเชื่อมต่อเซิร์ฟเวอร์ผิดพลาด',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#059669'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>💾</span> บันทึกตั้งค่าช่องทางติดต่อ';
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
                                title: 'พบเวอร์ชันใหม่พร้อมอัปเดต! 🚀',
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
                                confirmButtonText: '🚀 เริ่มอัปเดตทันที',
                                cancelButtonText: 'ไว้ภายหลัง',
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
                            Swal.fire({
                                icon: 'success',
                                title: 'ระบบเป็นเวอร์ชันล่าสุดแล้ว ✓',
                                text: 'โค้ดในเซิร์ฟเวอร์ของคุณเป็นเวอร์ชันล่าสุดแล้ว ไม่จำเป็นต้องอัปเดตครับ',
                                confirmButtonText: 'ตกลง',
                                confirmButtonColor: '#4f46e5',
                                showConfirmButton: true,
                                timer: 3500,
                                timerProgressBar: true
                            });
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
            scrollToSection('system-update-section', false);
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
                    sessionStorage.setItem('scroll_to_update', '1');
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปเดตระบบสำเร็จ! 🎉',
                        text: d.message || 'ระบบได้รับการอัปเดตเป็นเวอร์ชันล่าสุดแล้ว',
                        confirmButtonText: 'ตกลง (รีโหลดหน้าเว็บ)',
                        confirmButtonColor: '#4f46e5',
                        showConfirmButton: true,
                        timer: 2500,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการอัปเดต',
                        text: d.message || 'ไม่สามารถอัปเดตระบบได้ กรุณาตรวจสอบสิทธิ์หรือ Log',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            } catch (e) {
                sessionStorage.setItem('scroll_to_update', '1');
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        }

        function initScrollSpy() {
            const main = document.getElementById('mainContent');
            if (!main) return;

            const sectionIds = [
                'sec-admin-security',
                'sec-slipok',
                'sec-discord',
                'sec-warnings',
                'sec-turnstile',
                'sec-contact',
                'sec-announcement',
                'system-update-section'
            ];

            const navBar = document.querySelector('.sticky');
            
            const handleScrollSpy = () => {
                if (isProgrammaticScroll) return;
                const navHeight = navBar ? navBar.offsetHeight : 54;
                const mainTop = main.getBoundingClientRect().top;
                
                let currentId = '';
                for (let i = 0; i < sectionIds.length; i++) {
                    const el = document.getElementById(sectionIds[i]);
                    if (!el) continue;
                    const elTop = el.getBoundingClientRect().top - mainTop - navHeight - 30;
                    if (elTop <= 0) {
                        currentId = sectionIds[i];
                    }
                }
                if (currentId) {
                    updateActiveNavButton(currentId);
                }
            };

            main.addEventListener('scroll', handleScrollSpy, { passive: true });
        }

        function initMobileInputFocus() {
            if (window.innerWidth > 768) return;
            const main = document.getElementById('mainContent');
            if (!main) return;

            document.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea, select').forEach(el => {
                el.addEventListener('focus', () => {
                    setTimeout(() => {
                        const navBar = document.querySelector('.sticky');
                        const navHeight = navBar ? navBar.offsetHeight : 54;
                        const elRect = el.getBoundingClientRect();
                        const mainRect = main.getBoundingClientRect();
                        if (elRect.top < mainRect.top + navHeight + 15 || elRect.bottom > window.innerHeight - 80) {
                            const targetTop = main.scrollTop + (elRect.top - mainRect.top) - navHeight - 25;
                            main.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                        }
                    }, 220);
                }, { passive: true });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('scroll_to_update') === '1') {
                scrollToSection('system-update-section', false);
            }
        });

        window.onload = () => {
            loadAnnouncements();
            loadSlipSettings();
            loadWebhooks();
            loadWarnings();
            loadTurnstileSettings();
            loadContactSettings();
            checkSystemUpdate(false);
            initScrollSpy();
            initMobileInputFocus();

            if (sessionStorage.getItem('scroll_to_update') === '1') {
                sessionStorage.removeItem('scroll_to_update');
                scrollToSection('system-update-section', false);
                setTimeout(() => scrollToSection('system-update-section', false), 150);
            }
        };
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
