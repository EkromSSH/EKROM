<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>จัดการเซิร์ฟเวอร์ - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> body { font-family: 'Anuphan', 'Inter', sans-serif; } .hide-scroll::-webkit-scrollbar { display: none; } .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; } th, td { white-space: nowrap; } 
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
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
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

    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
    </aside>

    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการเซิร์ฟเวอร์ (VPN Servers) ⚙️</h1>
            <p class="text-gray-500 mt-1 text-sm">เพิ่ม ลบ แก้ไข และเปิด/ปิด เซิร์ฟเวอร์ที่จะนำไปแสดงขายในหน้า Store</p>
        </header>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-50">
                <h2 class="text-base md:text-lg font-bold text-slate-900">เซิร์ฟเวอร์ทั้งหมด</h2>
                <div class="flex items-center gap-3">
                    <button onclick="loadServers(this)" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                        <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                    </button>
                    <button onclick="openModal('add')" class="bg-pink-600 text-white font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-pink-700 transition-all shadow-md shadow-pink-500/30">➕ เพิ่มเซิร์ฟเวอร์ใหม่</button>
                </div>
            </div>

            <div class="overflow-x-auto hide-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 text-[10px] md:text-xs uppercase tracking-wider border-b border-gray-200">
                            <th class="px-4 md:px-6 py-4 font-bold">ชื่อเซิร์ฟเวอร์</th>
                            <th class="px-4 md:px-6 py-4 font-bold">หมวดหมู่ / โซนราคา</th>
                            <th class="px-4 md:px-6 py-4 font-bold">Domain & Inbound</th>
                            <th class="px-4 md:px-6 py-4 font-bold text-center">สถานะ</th>
                            <th class="px-4 md:px-6 py-4 font-bold text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="serverTableBody" class="text-xs md:text-sm divide-y divide-gray-100">
                        <tr><td colspan="5" class="text-center py-10 text-gray-400 font-bold">กำลังโหลดข้อมูล...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="svModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden items-center justify-center p-2 md:p-4 opacity-0 transition-opacity duration-300">
        <div id="svModalContent" class="bg-white w-full max-w-3xl rounded-[24px] shadow-2xl flex flex-col max-h-[95vh] overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-4 md:p-6 border-b border-gray-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h2 id="modalTitle" class="text-lg md:text-xl font-bold text-slate-900">➕ เพิ่มเซิร์ฟเวอร์ใหม่</h2>
                <button onclick="closeModal()" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm text-gray-400 hover:text-slate-900">✕</button>
            </div>

            <div class="p-4 md:p-6 overflow-y-auto hide-scroll flex-grow bg-white">
                <form id="svForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" id="frm_id">

                    <div class="col-span-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อเซิร์ฟเวอร์ (แสดงหน้าเว็บ)</label>
                        <input type="text" id="frm_name" placeholder="เช่น Ais Server 1" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">หมวดหมู่ (เครือข่าย)</label>
                        <select id="frm_category" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                            <option value="">-- ไม่จัดหมวดหมู่ (แสดงรวม) --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ประเภทระบบ</label>
                        <select id="frm_type" onchange="toggleFields()" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                            <option value="vmess">VMess (มาตรฐาน)</option>
                            <option value="vless">VLESS Reality (เกมมิ่ง)</option>
                            <option value="ssh_script">AutoScript (SSH VPS Direct)</option>
                            <option value="udp_custom">UDP Custom (ระบบ UDP)</option>
                        </select>
                    </div>
                    <div id="connection_mode_wrap">
                        <label class="block text-xs font-bold text-slate-700 mb-1">ระบบเชื่อมต่อเซิร์ฟเวอร์</label>
                        <select id="frm_connection_mode" onchange="toggleConnectionMode()" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                            <option value="legacy">ระบบเดิม (Legacy 3x-ui)</option>
                            <option value="api">ระบบใหม่ (3x-ui API Token)</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" id="frm_ghost_cleanup_enabled" class="w-5 h-5 text-red-600 rounded">
                        <label for="frm_ghost_cleanup_enabled" class="text-xs font-bold text-red-700">อนุญาตให้ลบไฟล์ผีของเซิร์ฟเวอร์นี้</label>
                    </div>

                    <!-- 🟢 กล่องเลือกโปรเสริมแบบ Checkbox ใช้งานง่ายทั้งมือถือและคอม -->
                    <div class="col-span-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">📦 โปรเสริมที่ต้องใช้ร่วม (กดติ๊กถูกเพื่อผูกโปรเสริมกับเซิร์ฟเวอร์นี้ / เลือกได้หลายโปร)</label>
                        <div id="addonCheckboxes" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2.5 bg-slate-50 border border-gray-200 rounded-xl"></div>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1">คำอธิบายเซิร์ฟเวอร์ (โชว์หน้า Store)</label>
                        <select id="frm_desc_mode" onchange="toggleDescField()" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500 mb-2">
                            <option value="default_standard">ข้อความระบบ: เซิร์ฟเวอร์มาตรฐาน ทะลุบล็อกใช้งานทั่วไป...</option>
                            <option value="default_gaming">ข้อความระบบ: เซิร์ฟเวอร์ VIP ปิงต่ำพิเศษ เหมาะสำหรับ...</option>
                            <option value="custom">เขียนคำอธิบายเอง</option>
                            <option value="none">ปล่อยว่าง</option>
                        </select>
                        <textarea id="frm_desc_custom" placeholder="พิมพ์คำอธิบายของคุณ..." class="hidden w-full bg-white border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" rows="2"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">โซนราคา</label>
                        <select id="frm_tier" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                            <option value="">กำลังโหลดโซนราคา...</option>
                        </select>
                    </div>

                    <div id="xui_config_title" class="col-span-full mt-2 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-pink-600 mb-3">การเชื่อมต่อ 3x-ui Panel</h3>
                    </div>
                    
                    <div id="ssh_config_title" class="col-span-full mt-2 pt-4 border-t border-gray-100 hidden">
                        <h3 class="text-sm font-bold text-emerald-600 mb-3">การเชื่อมต่อ VPS (SSH)</h3>
                    </div>

                    <div id="ssh_template_box" class="col-span-full hidden mb-2 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div><h3 class="text-sm font-bold text-emerald-800">🛡️ แม่แบบ NPV Tunnel</h3><p class="mt-1 text-[10px] font-semibold text-emerald-600">เพิ่มได้หลายแม่แบบ ลูกค้าจะเห็นแยกเป็นรายการให้เลือกคัดลอก</p></div>
                            <button type="button" onclick="addSshTemplateRow('npv')" class="rounded-lg bg-white px-3 py-2 text-[10px] font-bold text-emerald-700 shadow-sm hover:bg-emerald-100">➕ เพิ่ม NPV Tunnel</button>
                        </div>
                        <div id="npv_templates_list" class="space-y-3"></div>
                    </div>

                    <div id="netmod_template_box" class="col-span-full hidden mb-2 rounded-xl border border-orange-200 bg-orange-50 p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div><h3 class="text-sm font-bold text-orange-800">🔥 แม่แบบ NetMod</h3><p class="mt-1 text-[10px] font-semibold text-orange-600">เพิ่มได้หลายแม่แบบ ระบบจะเปลี่ยน Username / Password ให้โดยอัตโนมัติ</p></div>
                            <button type="button" onclick="addSshTemplateRow('netmod')" class="rounded-lg bg-white px-3 py-2 text-[10px] font-bold text-orange-700 shadow-sm hover:bg-orange-100">➕ เพิ่ม NetMod</button>
                        </div>
                        <div id="netmod_templates_list" class="space-y-3"></div>
                    </div>

                    <div id="url_field" class="col-span-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Panel URL (เอาแบบมี /xxx ต่อท้าย)</label>
                        <input type="url" id="frm_url" placeholder="https://ip:port/path" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                    </div>
                    
                    <div id="legacy_user_field">
                        <label id="user_label" class="block text-xs font-bold text-slate-700 mb-1">Username (Panel)</label>
                        <input type="text" id="frm_user" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" required>
                    </div>
                    <div id="legacy_pass_field">
                        <label id="pass_label" class="block text-xs font-bold text-slate-700 mb-1">Password (Panel)</label>
                        <input type="password" id="frm_pass" autocomplete="new-password" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" required>
                    </div>
                    <div id="api_token_field" class="col-span-full hidden p-4 bg-cyan-50 border border-cyan-200 rounded-xl">
                        <label class="block text-xs font-bold text-cyan-900 mb-1">API Token ของ 3x-ui <span class="text-red-600">*</span></label>
                        <input type="password" id="frm_api_token" autocomplete="new-password" placeholder="วาง Bearer Token ที่สร้างจาก 3x-ui" class="w-full bg-white border border-cyan-300 rounded-lg px-3 py-2 outline-none focus:border-cyan-600 font-mono text-sm">
                        <p class="text-[11px] text-cyan-800 mt-2">ใช้ Token จากระบบ 3x-ui ใหม่เท่านั้น — ไม่ต้องกรอก Username หรือ Password ของ Panel</p>
                    </div>

                    <div id="inbound_config_title" class="col-span-full mt-2 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-pink-600 mb-3">การตั้งค่า Config (Inbound)</h3>
                    </div>
                    <div id="inbound_field">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Inbound ID</label>
                        <input type="number" id="frm_inbound" placeholder="เช่น 1, 9, 12" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                    </div>
                    <div>
                        <label id="domain_label" class="block text-xs font-bold text-slate-700 mb-1">Domain (Address)</label>
                        <input type="text" id="frm_domain" placeholder="server1.domain.com" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" required>
                    </div>
                    <div id="bug_field">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bug Host (SNI/Host)</label>
                        <input type="text" id="frm_bug" placeholder="www.speedtest.net" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500">
                    </div>
                    <div>
                        <label id="port_label" class="block text-xs font-bold text-slate-700 mb-1">Port (พอร์ตหลักเชื่อมต่อ)</label>
                        <input type="number" id="frm_port" placeholder="443" class="w-full bg-slate-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-pink-500" required>
                    </div>

                    <div id="vless_box" class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 pt-4 border-t border-purple-100 bg-purple-50/50 p-4 rounded-xl hidden">
                        <div class="col-span-full">
                            <h3 class="text-sm font-bold text-purple-600 mb-1">ตั้งค่าเฉพาะ VLESS Reality 🎮</h3>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-purple-900 mb-1">VLESS Port</label>
                            <input type="number" id="frm_vport" placeholder="8080" class="w-full bg-white border border-purple-200 rounded-lg px-3 py-2 outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-purple-900 mb-1">Public Key (PBK)</label>
                            <input type="text" id="frm_pbk" class="w-full bg-white border border-purple-200 rounded-lg px-3 py-2 outline-none focus:border-purple-500">
                        </div>
                        <div class="col-span-full">
                            <label class="block text-[10px] font-bold text-purple-900 mb-1">Short IDs (SIDs) คั่นด้วยลูกน้ำ (,)</label>
                            <input type="text" id="frm_sids" placeholder="1621d911,ac5d,4cc0a2" class="w-full bg-white border border-purple-200 rounded-lg px-3 py-2 outline-none focus:border-purple-500">
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-4 border-t border-gray-100 bg-slate-50 shrink-0">
                <button onclick="saveServer()" class="w-full bg-pink-600 text-white font-bold py-3 rounded-xl hover:bg-pink-700 transition-all shadow-md">💾 บันทึกเซิร์ฟเวอร์</button>
            </div>
        </div>
    </div>

    <script>
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

        const serverThemeMap = {
            emerald: { badge: 'bg-emerald-50 text-emerald-700 border-emerald-200', bgHex: '#ecfdf5', textHex: '#047857', borderHex: '#a7f3d0' },
            green:   { badge: 'bg-emerald-50 text-emerald-700 border-emerald-200', bgHex: '#ecfdf5', textHex: '#047857', borderHex: '#a7f3d0' },
            amber:   { badge: 'bg-amber-50 text-amber-700 border-amber-200', bgHex: '#fffbeb', textHex: '#b45309', borderHex: '#fde68a' },
            yellow:  { badge: 'bg-amber-50 text-amber-700 border-amber-200', bgHex: '#fffbeb', textHex: '#b45309', borderHex: '#fde68a' },
            rose:    { badge: 'bg-rose-50 text-rose-700 border-rose-200', bgHex: '#fff1f2', textHex: '#be123c', borderHex: '#fecdd3' },
            red:     { badge: 'bg-red-50 text-red-700 border-red-200', bgHex: '#fef2f2', textHex: '#b91c1c', borderHex: '#fecaca' },
            orange:  { badge: 'bg-orange-50 text-orange-700 border-orange-200', bgHex: '#fff7ed', textHex: '#c2410c', borderHex: '#fed7aa' },
            cyan:    { badge: 'bg-cyan-50 text-cyan-700 border-cyan-200', bgHex: '#ecfeff', textHex: '#0e7490', borderHex: '#a5f3fc' },
            sky:     { badge: 'bg-sky-50 text-sky-700 border-sky-200', bgHex: '#f0f9ff', textHex: '#0369a1', borderHex: '#bae6fd' },
            blue:    { badge: 'bg-blue-50 text-blue-700 border-blue-200', bgHex: '#eff6ff', textHex: '#1d4ed8', borderHex: '#bfdbfe' },
            indigo:  { badge: 'bg-indigo-50 text-indigo-700 border-indigo-200', bgHex: '#eef2ff', textHex: '#4338ca', borderHex: '#c7d2fe' },
            purple:  { badge: 'bg-purple-50 text-purple-700 border-purple-200', bgHex: '#faf5ff', textHex: '#7e22ce', borderHex: '#e9d5ff' },
            violet:  { badge: 'bg-purple-50 text-purple-700 border-purple-200', bgHex: '#faf5ff', textHex: '#7e22ce', borderHex: '#e9d5ff' },
            pink:    { badge: 'bg-pink-50 text-pink-700 border-pink-200', bgHex: '#fdf2f8', textHex: '#be185d', borderHex: '#fbcfe8' },
            teal:    { badge: 'bg-teal-50 text-teal-700 border-teal-200', bgHex: '#f0fdfa', textHex: '#0f766e', borderHex: '#99f6e4' },
            slate:   { badge: 'bg-slate-100 text-slate-700 border-slate-300', bgHex: '#f8fafc', textHex: '#334155', borderHex: '#cbd5e1' }
        };

        let allServers = [];
        let categoryList = [];

        function escapeServerHtml(value) {
            const node = document.createElement('div');
            node.textContent = String(value ?? '');
            return node.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function sshTemplateContainer(type) {
            return document.getElementById(type === 'npv' ? 'npv_templates_list' : 'netmod_templates_list');
        }

        function addSshTemplateRow(type, name = '', value = '') {
            const list = sshTemplateContainer(type);
            if (!list) return;
            const label = type === 'npv' ? 'NPV Tunnel' : 'NetMod';
            const row = document.createElement('div');
            row.className = 'ssh-template-row rounded-xl border border-white/80 bg-white p-3 shadow-sm';
            row.dataset.templateType = type;
            row.innerHTML = `<div class="mb-2 flex items-center gap-2"><input data-template-name type="text" value="${escapeServerHtml(name)}" placeholder="ชื่อแม่แบบ เช่น ${label} AIS" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold outline-none focus:border-pink-500"><button type="button" onclick="this.closest('.ssh-template-row').remove()" class="shrink-0 rounded-lg bg-red-50 px-2.5 py-2 text-[10px] font-bold text-red-600 hover:bg-red-100">ลบ</button></div><textarea data-template-value rows="3" placeholder="วางลิงก์ ${type === 'npv' ? 'npvt-ssh://...' : 'ssh://...'} แบบเต็มที่นี่" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-[10px] outline-none focus:border-pink-500">${escapeServerHtml(value)}</textarea>`;
            list.appendChild(row);
        }

        function clearSshTemplateRows() {
            ['npv_templates_list', 'netmod_templates_list'].forEach(id => {
                const list = document.getElementById(id);
                if (list) list.innerHTML = '';
            });
        }

        function populateSshTemplateRows(server) {
            clearSshTemplateRows();
            const npv = Array.isArray(server?.ssh_templates) && server.ssh_templates.length
                ? server.ssh_templates
                : (server?.ssh_template ? [{ template_name: 'NPV Tunnel แบบเดิม', template_value: server.ssh_template }] : []);
            const netmod = Array.isArray(server?.netmod_templates) && server.netmod_templates.length
                ? server.netmod_templates
                : (server?.netmod_template ? [{ template_name: 'NetMod แบบเดิม', template_value: server.netmod_template }] : []);
            npv.forEach(item => addSshTemplateRow('npv', item.template_name || item.name, item.template_value || item.value));
            netmod.forEach(item => addSshTemplateRow('netmod', item.template_name || item.name, item.template_value || item.value));
            if (!npv.length) addSshTemplateRow('npv');
            if (!netmod.length) addSshTemplateRow('netmod');
        }

        function collectSshTemplates(type) {
            const list = sshTemplateContainer(type);
            if (!list) return [];
            return Array.from(list.querySelectorAll('.ssh-template-row')).map((row, index) => ({
                name: row.querySelector('[data-template-name]')?.value.trim() || `${type === 'npv' ? 'NPV Tunnel' : 'NetMod'} ${index + 1}`,
                value: row.querySelector('[data-template-value]')?.value.trim() || ''
            })).filter(item => item.value !== '');
        }

        async function loadCategoriesForDropdown() {
            try {
                const res = await fetch('api/admin_categories.php?action=list');
                const data = await res.json();
                if (data.status === 'success') {
                    categoryList = data.data;
                    let html = '<option value="">-- ไม่จัดหมวดหมู่ (แสดงรวม) --</option>';
                    data.data.forEach(cat => { 
                        const th = serverThemeMap[(cat.color_theme || 'pink').toLowerCase()] || serverThemeMap['pink'];
                        html += `<option value="${cat.id}">📂 ${cat.name} (${th.name})</option>`; 
                    });
                    document.getElementById('frm_category').innerHTML = html;
                }
            } catch (e) { }
        }

        async function loadAddonsForDropdown() {
            try {
                const res = await fetch('api/addons.php?action=get');
                const data = await res.json();
                if (data.status === 'success') {
                    let html = '';
                    data.data.forEach(cat => {
                        html += `<div class="col-span-full font-bold text-[11px] text-slate-500 uppercase mt-1 mb-0.5">ค่าย ${cat.name}</div>`;
                        cat.items.forEach(addon => {
                            html += `
                                <label class="flex items-center gap-2.5 p-2 rounded-lg bg-white border border-gray-200 hover:border-pink-300 cursor-pointer transition-all shadow-xs">
                                    <input type="checkbox" name="server_addon_cb" value="${addon.id}" class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500 cursor-pointer">
                                    <span class="text-xs font-semibold text-slate-700 truncate">${addon.title} <span class="text-pink-600 font-bold">฿${addon.price}</span></span>
                                </label>
                            `;
                        });
                    });
                    document.getElementById('addonCheckboxes').innerHTML = html || '<div class="col-span-full text-xs text-slate-400 p-2">ไม่มีโปรเสริมในระบบ</div>';
                }
            } catch (e) { }
        }

        async function loadServers(btn) {
            const icon = btn ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;
            const tbody = document.getElementById('serverTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">กำลังโหลด... ⏳</td></tr>';
            try {
                const res = await fetch('api/admin_servers.php?action=list', { cache: 'no-store' });
                const data = await res.json();
                if (data.status === 'success') {
                    allServers = data.data;
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">ยังไม่มีเซิร์ฟเวอร์ในระบบ</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.data.map(sv => {
                        const isVless = sv.type === 'vless';
                        const isSsh = sv.type === 'ssh_script' || sv.type === 'udp_custom';
                        let badgeType = '';
                        if (isSsh) badgeType = `<span class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">SSH SCRIPT</span>`;
                        else if (isVless) badgeType = `<span class="bg-purple-100 text-purple-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">VLESS</span>`;
                        else badgeType = `<span class="bg-pink-100 text-pink-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">VMESS</span>`;
                        
                        const catTh = serverThemeMap[(sv.category_color_theme || 'slate').toLowerCase()] || serverThemeMap['slate'];
                        const tierTh = serverThemeMap[(sv.price_tier_color_theme || 'indigo').toLowerCase()] || serverThemeMap['indigo'];

                        const badgeTier = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase mt-1 border ${tierTh.badge}" style="background-color: ${tierTh.bgHex}; color: ${tierTh.textHex}; border-color: ${tierTh.borderHex};">🏷️ โซน: ${sv.price_tier_name || sv.price_tier}</span>`;
                        const catBadge = sv.category_name ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold border ${catTh.badge}" style="background-color: ${catTh.bgHex}; color: ${catTh.textHex}; border-color: ${catTh.borderHex};">📂 ${sv.category_name}</span>` : `<span class="text-gray-400 text-[10px] italic">ไม่มีหมวดหมู่</span>`;

                        const statusBtn = sv.status === 'active'
                            ? `<button onclick="toggleStatus(${sv.id}, 'inactive')" class="bg-green-500 text-white px-3 py-1 rounded-full text-[10px] font-bold hover:bg-green-600 shadow-sm">🟢 เปิดขาย</button>`
                            : `<button onclick="toggleStatus(${sv.id}, 'active')" class="bg-gray-300 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold hover:bg-gray-400 shadow-sm">⚪ ปิดอยู่</button>`;

                        const detailsInfo = isSsh 
                            ? `<p class="text-[10px] text-gray-400 mt-0.5">Port: ${sv.port} · NPV ${Array.isArray(sv.ssh_templates) ? sv.ssh_templates.length : (sv.ssh_template ? 1 : 0)} · NetMod ${Array.isArray(sv.netmod_templates) ? sv.netmod_templates.length : (sv.netmod_template ? 1 : 0)}</p>`
                            : `<p class="text-[10px] text-gray-400 mt-0.5">ID: ${sv.inbound_id} | Port: ${sv.port}</p>`;

                        return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 md:px-6 py-3 font-bold text-slate-900">${sv.name}</td>
                            <td class="px-4 md:px-6 py-3">
                                <div class="flex flex-col gap-1 items-start">
                                    ${catBadge}
                                    <div class="flex gap-1">${badgeType} ${badgeTier}</div>
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3">
                                <p class="text-xs font-mono text-gray-600">${sv.domain}</p>
                                ${detailsInfo}
                            </td>
                            <td class="px-4 md:px-6 py-3 text-center">${statusBtn}</td>
                            <td class="px-4 md:px-6 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="testServer(${sv.id}, '${String(sv.name).replace(/'/g, "\\'")}')" class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-all">🔌 ทดสอบ</button>
                                    <button onclick="openModal('edit', ${sv.id})" class="bg-pink-50 text-pink-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-pink-100 transition-all">✏️ แก้ไข</button>
                                    <button onclick="deleteServer(${sv.id})" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-100 transition-all">🗑️ ลบ</button>
                                </div>
                            </td>
                        </tr>`;
                    }).join('');

                    if (btn) {
                        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชข้อมูลเซิร์ฟเวอร์แล้ว' });
                    }
                }
            } catch (e) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-500">การเชื่อมต่อขัดข้อง</td></tr>'; }
            finally {
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        async function testServer(id, name) {
            Swal.fire({ title: 'กำลังทดสอบการเชื่อมต่อ', text: name, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch('api/test_server.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ server_id: id }) });
                const data = await res.json();
                Swal.fire({ icon: data.status === 'success' ? 'success' : 'error', title: data.status === 'success' ? 'เชื่อมต่อสำเร็จ' : 'เชื่อมต่อไม่สำเร็จ', text: (data.message || '') + (data.latency_ms ? ` · ${data.latency_ms} ms` : '') });
            } catch (e) { Swal.fire('ผิดพลาด', 'ไม่สามารถเรียกใช้ระบบทดสอบได้', 'error'); }
        }

        function toggleFields() {
            const type = document.getElementById('frm_type').value;
            const vlessBox = document.getElementById('vless_box');
            const sshTemplateBox = document.getElementById('ssh_template_box');
            const netmodTemplateBox = document.getElementById('netmod_template_box');
            
            const urlField = document.getElementById('url_field');
            const inboundField = document.getElementById('inbound_field');
            const bugField = document.getElementById('bug_field');
            
            const userLabel = document.getElementById('user_label');
            const passLabel = document.getElementById('pass_label');
            const domainLabel = document.getElementById('domain_label');
            const portLabel = document.getElementById('port_label');

            const xuiTitle = document.getElementById('xui_config_title');
            const sshTitle = document.getElementById('ssh_config_title');
            const inboundTitle = document.getElementById('inbound_config_title');

            if (type === 'ssh_script' || type === 'udp_custom') {
                vlessBox.classList.add('hidden');
                sshTemplateBox.classList.remove('hidden'); 
                netmodTemplateBox.classList.remove('hidden'); 
                
                urlField.classList.add('hidden');
                inboundField.classList.add('hidden');
                bugField.classList.add('hidden');
                xuiTitle.classList.add('hidden');
                inboundTitle.classList.add('hidden');
                
                sshTitle.classList.remove('hidden');

                userLabel.innerText = "VPS Username (ปกติคือ root)";
                passLabel.innerText = "VPS Password";
                domainLabel.innerText = "VPS IP Address";
                portLabel.innerText = "SSH Port (ปกติคือ 22)";
                
                document.getElementById('frm_url').required = false;
                document.getElementById('frm_inbound').required = false;
                document.getElementById('frm_bug').required = false;

            } else {
                if (type === 'vless') vlessBox.classList.remove('hidden');
                else vlessBox.classList.add('hidden');
                
                sshTemplateBox.classList.add('hidden');
                netmodTemplateBox.classList.add('hidden'); 

                urlField.classList.remove('hidden');
                inboundField.classList.remove('hidden');
                bugField.classList.remove('hidden');
                xuiTitle.classList.remove('hidden');
                inboundTitle.classList.remove('hidden');
                
                sshTitle.classList.add('hidden');

                userLabel.innerText = "Username (Panel)";
                passLabel.innerText = "Password (Panel)";
                domainLabel.innerText = "Domain (Address)";
                portLabel.innerText = "Port (พอร์ตหลักเชื่อมต่อ)";
                
                document.getElementById('frm_url').required = true;
                document.getElementById('frm_inbound').required = true;
                document.getElementById('frm_bug').required = true;
            }

            toggleConnectionMode();
        }

        function toggleConnectionMode() {
            const type = document.getElementById('frm_type').value;
            const modeSelect = document.getElementById('frm_connection_mode');
            const modeWrap = document.getElementById('connection_mode_wrap');
            const legacyUser = document.getElementById('legacy_user_field');
            const legacyPass = document.getElementById('legacy_pass_field');
            const tokenField = document.getElementById('api_token_field');
            const user = document.getElementById('frm_user');
            const pass = document.getElementById('frm_pass');
            const token = document.getElementById('frm_api_token');
            const isXui = type !== 'ssh_script' && type !== 'udp_custom';
            const isApi = isXui && modeSelect.value === 'api';

            modeWrap.classList.toggle('hidden', !isXui);
            legacyUser.classList.toggle('hidden', isApi);
            legacyPass.classList.toggle('hidden', isApi);
            tokenField.classList.toggle('hidden', !isApi);

            user.required = !isApi;
            pass.required = !isApi;
            token.required = isApi;
        }

        function toggleDescField() {
            const mode = document.getElementById('frm_desc_mode').value;
            const customField = document.getElementById('frm_desc_custom');
            if (mode === 'custom') customField.classList.remove('hidden');
            else customField.classList.add('hidden');
        }

        function openModal(mode, id = null) {
            document.getElementById('svForm').reset();
            const modalTitle = document.getElementById('modalTitle');
            // รีเซ็ตการเลือก Addons
            document.querySelectorAll('input[name="server_addon_cb"]').forEach(cb => cb.checked = false);

            if (mode === 'add') {
                document.getElementById('frm_id').value = '';
                document.getElementById('frm_category').value = '';
                populateSshTemplateRows(null);
                modalTitle.innerText = '➕ เพิ่มเซิร์ฟเวอร์ใหม่';
                document.getElementById('frm_desc_mode').value = 'default_standard';
                const tierSelect = document.getElementById('frm_tier');
                if(tierSelect.options.length > 0) tierSelect.selectedIndex = 0;
            } else if (mode === 'edit') {
                const sv = allServers.find(s => s.id == id);
                if (!sv) return;

                document.getElementById('frm_id').value = sv.id;
                modalTitle.innerText = '✏️ แก้ไขเซิร์ฟเวอร์';

                document.getElementById('frm_name').value = sv.name;
                document.getElementById('frm_category').value = sv.category_id || ""; 
                
                // 🟢 กู้คืนการเลือก Addon (หลายตัว)
                const selectedAddons = sv.addon_id ? String(sv.addon_id).split(',').map(s => s.trim()) : [];
                document.querySelectorAll('input[name="server_addon_cb"]').forEach(cb => {
                    if (selectedAddons.includes(String(cb.value))) cb.checked = true;
                });

                document.getElementById('frm_type').value = sv.type;
                document.getElementById('frm_connection_mode').value = sv.connection_mode || 'legacy';
                document.getElementById('frm_ghost_cleanup_enabled').checked = Number(sv.ghost_cleanup_enabled) === 1;
                document.getElementById('frm_tier').value = sv.tier_id || sv.price_tier;
                document.getElementById('frm_url').value = sv.panel_url || "";
                document.getElementById('frm_user').value = sv.username;
                document.getElementById('frm_pass').value = sv.password;
                document.getElementById('frm_api_token').value = (sv.connection_mode === 'api') ? (sv.password || '') : '';
                document.getElementById('frm_inbound').value = sv.inbound_id || "";
                document.getElementById('frm_domain').value = sv.domain;
                document.getElementById('frm_bug').value = sv.bug_host || "";
                document.getElementById('frm_port').value = sv.port;
                populateSshTemplateRows(sv);

                if (sv.type === 'vless') {
                    document.getElementById('frm_vport').value = sv.vless_port;
                    document.getElementById('frm_pbk').value = sv.pbk;
                    document.getElementById('frm_sids').value = sv.sids;
                }

                let desc = sv.description;
                if (desc === "เซิร์ฟเวอร์มาตรฐาน ทะลุบล็อกใช้งานทั่วไป เล่นโซเชียล ดูหนังฟังเพลงลื่นไหล") document.getElementById('frm_desc_mode').value = 'default_standard';
                else if (desc === "เซิร์ฟเวอร์ VIP ปิงต่ำพิเศษ เหมาะสำหรับสายเกมเมอร์ ลดแลค เสถียรสุด") document.getElementById('frm_desc_mode').value = 'default_gaming';
                else if (!desc || desc.trim() === "") document.getElementById('frm_desc_mode').value = 'none';
                else {
                    document.getElementById('frm_desc_mode').value = 'custom';
                    document.getElementById('frm_desc_custom').value = desc;
                }
            }

            toggleDescField();
            toggleFields(); 

            const modal = document.getElementById('svModal');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => { modal.classList.remove('opacity-0'); document.getElementById('svModalContent').classList.remove('scale-95'); }, 10);
        }

        function closeModal() {
            document.getElementById('svModal').classList.add('opacity-0');
            document.getElementById('svModalContent').classList.add('scale-95');
            setTimeout(() => document.getElementById('svModal').classList.add('hidden'), 300);
        }

        async function saveServer() {
            const name = document.getElementById('frm_name').value;
            const type = document.getElementById('frm_type').value;
            const connectionMode = document.getElementById('frm_connection_mode').value;
            
            if (!name) return Swal.fire('ข้อมูลไม่ครบ', 'กรุณากรอกชื่อเซิร์ฟเวอร์', 'warning');
            if (type !== 'ssh_script') {
                const url = document.getElementById('frm_url').value;
                if (!url) return Swal.fire('ข้อมูลไม่ครบ', 'กรุณากรอก URL', 'warning');
            }
            if (type !== 'ssh_script' && type !== 'udp_custom' && connectionMode === 'api' && !document.getElementById('frm_api_token').value.trim()) {
                return Swal.fire('ข้อมูลไม่ครบ', 'กรุณากรอก API Token ของ 3x-ui', 'warning');
            }

            let descMode = document.getElementById('frm_desc_mode').value;
            let descFinal = "";
            if (descMode === 'default_standard') descFinal = "เซิร์ฟเวอร์มาตรฐาน ทะลุบล็อกใช้งานทั่วไป เล่นโซเชียล ดูหนังฟังเพลงลื่นไหล";
            else if (descMode === 'default_gaming') descFinal = "เซิร์ฟเวอร์ VIP ปิงต่ำพิเศษ เหมาะสำหรับสายเกมเมอร์ ลดแลค เสถียรสุด";
            else if (descMode === 'custom') descFinal = document.getElementById('frm_desc_custom').value;

            // 🟢 รวม Addon ID เป็น String คั่นด้วยลูกน้ำ
            const selectedAddons = Array.from(document.querySelectorAll('input[name="server_addon_cb"]:checked'))
                .map(cb => cb.value)
                .filter(val => val && val !== '0')
                .join(',');

            const serverId = document.getElementById('frm_id').value;
            const payload = {
                action: serverId ? 'edit' : 'add',
                id: serverId,
                category_id: document.getElementById('frm_category').value,
                addon_id: selectedAddons, // 🟢 ส่ง String ไปบันทึก
                type: type,
                connection_mode: connectionMode,
                ghost_cleanup_enabled: document.getElementById('frm_ghost_cleanup_enabled').checked ? 1 : 0,
                price_tier: document.getElementById('frm_tier').value,
                name: name,
                description: descFinal,
                panel_url: document.getElementById('frm_url').value,
                username: connectionMode === 'api' ? '' : document.getElementById('frm_user').value,
                password: connectionMode === 'api' ? document.getElementById('frm_api_token').value.trim() : document.getElementById('frm_pass').value,
                inbound_id: document.getElementById('frm_inbound').value,
                domain: document.getElementById('frm_domain').value,
                bug_host: document.getElementById('frm_bug').value,
                port: document.getElementById('frm_port').value,
                vless_port: document.getElementById('frm_vport').value,
                pbk: document.getElementById('frm_pbk').value,
                sids: document.getElementById('frm_sids').value,
                ssh_templates: collectSshTemplates('npv'),
                netmod_templates: collectSshTemplates('netmod')
            };

            Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const res = await fetch('api/admin_servers.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ!', data.message, 'success').then(() => { closeModal(); loadServers(); });
                } else { Swal.fire('ผิดพลาด', data.message, 'error'); }
            } catch (e) { Swal.fire('ขัดข้อง', 'เชื่อมต่อขัดข้อง', 'error'); }
        }

        async function deleteServer(id) {
            const confirm = await Swal.fire({ title: 'ลบเซิร์ฟเวอร์นี้?', text: 'หากลบแล้ว เซิร์ฟเวอร์นี้จะหายไปจากหน้า Store', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' });
            if (confirm.isConfirmed) {
                Swal.fire({ title: 'กำลังลบ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const res = await fetch('api/admin_servers.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete', id: id }) });
                const data = await res.json();
                if (data.status === 'success') { Swal.fire('ลบแล้ว!', '', 'success').then(() => loadServers()); }
            }
        }

        async function toggleStatus(id, newStatus) {
            const res = await fetch('api/admin_servers.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'toggle', id: id, status: newStatus }) });
            const data = await res.json();
            if (data.status === 'success') loadServers();
        }

        async function loadPriceTiers() {
            try {
                const res = await fetch('api/admin_pricing.php?action=get');
                const data = await res.json();
                if (data.status === 'success') {
                    const select = document.getElementById('frm_tier'); 
                    if (select) { 
                        select.innerHTML = data.data.map(t => {
                            const th = serverThemeMap[(t.color_theme || 'indigo').toLowerCase()] || serverThemeMap['indigo'];
                            return `<option value="${t.id}">🏷️ ${t.name} (เริ่ม ฿${t.price_1 !== undefined ? t.price_1 : (t.prices ? t.prices[0] : 0)}) [${th.name}]</option>`;
                        }).join(''); 
                    }
                }
            } catch (e) { }
        }

        document.addEventListener('DOMContentLoaded', () => { 
            loadCategoriesForDropdown(); 
            loadAddonsForDropdown(); 
            loadServers(); 
            loadPriceTiers(); 
        });
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
