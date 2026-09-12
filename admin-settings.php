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
    <style>body { font-family: 'Anuphan', 'Inter', sans-serif; }
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

    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
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
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
            <div class="mt-auto pt-6 border-t border-slate-700">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
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
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ตั้งค่าระบบ (Settings) ⚙️</h1>
            <p class="text-gray-500 mt-1 text-sm">จัดการลิงก์ Webhook และตั้งค่าคำแนะนำก่อนสั่งซื้อหน้าร้านค้า</p>
        </header>

        <!-- 🟢 1. ส่วนตั้งค่าระบบตรวจสอบสลิป -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                <span class="text-pink-600 text-2xl drop-shadow-sm">🧾</span>
                <h2 class="text-lg font-bold text-slate-900">ตั้งค่าระบบตรวจสลิปโอนเงิน (SlipOK API)</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">🔑 ข้อมูลเชื่อมต่อ SlipOK API</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SlipOK Branch ID <span class="text-red-500">*</span></label>
                            <input type="text" id="slipok_branch_id" placeholder="เช่น 1234 หรือ branch_id" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">Branch ID ที่ได้จากแดชบอร์ด SlipOK (api.slipok.com/api/line/apikey/{branch_id})</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SlipOK API Key (x-authorization) <span class="text-red-500">*</span></label>
                            <input type="password" id="slipok_api_key" placeholder="วาง API Key สำหรับยืนยันตัวตน" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้ส่งใน Header: x-authorization</p>
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

                    <!-- 🟢 การตั้งค่าความปลอดภัย ชื่อ และบัญชี -->
                    <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2">🔒 ความปลอดภัย & บัญชีรับเงิน</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">เลขบัญชีธนาคาร / พร้อมเพย์ <span class="text-red-500">*</span></label>
                            <input type="text" id="slip_receiver_account" placeholder="เช่น 0810968889" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                            <p class="text-[10px] text-gray-500 mt-1">ใช้สร้าง Dynamic QR และตรวจสอบบัญชีผู้รับในสลิป</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อบัญชี (ภาษาไทย)</label>
                            <input type="text" id="slip_receiver_th" placeholder="เช่น นูรียะห์ ตาเละ" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อบัญชี (ภาษาอังกฤษ)</label>
                            <input type="text" id="slip_receiver_en" placeholder="เช่น NURIYAH TALEK (เว้นว่างได้)" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-pink-500">
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
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8">
            <div class="p-6 bg-slate-50 border-b border-gray-200 flex items-center gap-3">
                <span class="text-[#5865F2] text-2xl drop-shadow-sm">👾</span>
                <h2 class="text-lg font-bold text-slate-900">Discord Webhooks</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">🛒 1. แจ้งเตือน ซื้อสินค้า</label>
                        <input type="url" id="wb_buy" placeholder="วางลิงก์ Webhook สำหรับแจ้งลูกค้าซื้อไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">💰 2. แจ้งเตือน เติมเงิน</label>
                        <input type="url" id="wb_topup" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนเติมเงิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">♻️ 3. แจ้งเตือน ต่ออายุ</label>
                        <input type="url" id="wb_renew" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนต่ออายุไฟล์" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">✨ 4. แจ้งเตือน สมัครสมาชิกใหม่</label>
                        <input type="url" id="wb_register" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนสมัคร" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">🔑 5. แจ้งเตือน เข้าสู่ระบบ</label>
                        <input type="url" id="wb_login" placeholder="วางลิงก์ Webhook สำหรับแจ้งคนล็อกอิน" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#5865F2] focus:ring-2 focus:ring-[#5865F2]/20 transition-all">
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWebhooks()" class="bg-[#5865F2] text-white font-bold px-8 py-3.5 rounded-xl hover:bg-[#4752C4] transition-all shadow-lg shadow-[#5865F2]/30 w-full md:w-auto">💾 บันทึก Webhooks</button>
                </div>
            </div>
        </div>

        <!-- 🟢 3. ส่วนตั้งค่าคำแนะนำก่อนสั่งซื้อ -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8">
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
                        <textarea id="warningSsh" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-pink-500 transition-all h-56 resize-none"></textarea>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-gray-200">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center font-bold">⚡</div>
                            <h3 class="font-bold text-slate-900">คำแนะนำระบบ V2Ray</h3>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3">พิมพ์ 1 บรรทัด = 1 ข้อย่อย (ใช้แท็ก <b>&lt;b&gt;ข้อความ&lt;/b&gt;</b> ทำตัวหนาได้)</p>
                        <textarea id="warningV2ray" class="w-full bg-white border border-gray-200 rounded-xl p-4 text-sm outline-none focus:border-orange-500 transition-all h-56 resize-none"></textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end">
                    <button onclick="saveWarnings()" id="btnSaveWarnings" class="bg-orange-500 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 w-full md:w-auto">💾 บันทึกคำแนะนำ</button>
                </div>
            </div>
        </div>
        <section class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden max-w-5xl mb-8">
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
    </main>

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

        window.onload = () => {
            loadAnnouncements();
            loadSlipSettings();
            loadWebhooks();
            loadWarnings();
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
