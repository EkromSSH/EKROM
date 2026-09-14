<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>เติมเงิน - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .upload-area.dragover { border-color: #db2777; background-color: #1e293b; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel="stylesheet" href="mobile-fix.css">
</head>
<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-4 sm:px-6 py-3.5 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM <span class="text-pink-600">TOPUP</span></span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.location.href='buyer-dash.php'" class="text-slate-600 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition-all">กลับ</button>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-pink-600 focus:outline-none p-1">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="drawer-header flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="drawer-title font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">TOPUP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
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
            <div class="w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">TOPUP</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <header class="mb-8 mt-2 md:mt-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">เติมเงินอัตโนมัติ ⚡</h1>
                <p class="text-gray-500 mt-1 text-xs md:text-sm">สแกนจ่ายพร้อมเพย์ หรือกรอกซองอังเปา ยอดเงินเข้าทันทีอัตโนมัติ 24 ชม.</p>
            </div>
            <div id="userBalanceBadge" class="hidden md:flex items-center gap-2.5 bg-white px-3.5 py-2 rounded-xl border border-slate-100 shadow-sm self-start md:self-auto">
                <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center text-pink-600 text-base font-bold">💳</div>
                <div>
                    <div class="text-[11px] text-slate-400 font-semibold leading-tight">ยอดเงินคงเหลือ</div>
                    <div class="text-base font-bold text-slate-800 leading-tight" id="currentBalanceDisplay">0.00 ฿</div>
                </div>
            </div>
        </header>

        <!-- Method Switcher Tabs -->
        <div class="max-w-4xl mx-auto mb-6">
            <div class="flex bg-slate-200/70 p-1 rounded-xl border border-slate-200">
                <button onclick="switchTab('slip')" id="tabSlip" class="flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-pink-600 transition-all">💸 โอนเงิน (สลิป พร้อมเพย์)</button>
                <button onclick="switchTab('angpao')" id="tabAngpao" class="flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all">🧧 ซองอังเปา (TrueMoney)</button>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 1: PROMPTPAY & SLIPOK SECTION                         -->
        <!-- ============================================================ -->
        <div id="sectionSlip" class="max-w-4xl mx-auto">
            
            <!-- STEP 1: SPECIFY AMOUNT (>= 30 THB) -->
            <div id="slipStep1" class="max-w-2xl mx-auto bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-pink-50 text-pink-600 rounded-2xl text-2xl mb-3">💵</div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900">ระบุจำนวนเงินที่ต้องการเติม</h2>
                    <p class="text-slate-500 text-xs md:text-sm mt-1">ระบบจะสร้าง QR Code พร้อมเพย์ตามยอดเงินที่ระบุพอดีเป๊ะ</p>
                </div>

                <!-- Quick Amount Pills -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-6">
                    <button type="button" onclick="selectQuickAmount(30)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">30 ฿</button>
                    <button type="button" onclick="selectQuickAmount(50)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">50 ฿</button>
                    <button type="button" onclick="selectQuickAmount(100)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">100 ฿</button>
                    <button type="button" onclick="selectQuickAmount(300)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">300 ฿</button>
                    <button type="button" onclick="selectQuickAmount(500)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">500 ฿</button>
                    <button type="button" onclick="selectQuickAmount(1000)" class="quick-btn py-2.5 px-3 rounded-xl border border-slate-200 hover:border-pink-500 hover:bg-pink-50 hover:text-pink-600 font-bold text-sm text-slate-700 transition-all text-center">1,000 ฿</button>
                </div>

                <!-- Amount Input -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 mb-6">
                    <label for="topupAmountInput" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">จำนวนเงิน (บาท)</label>
                    <div class="relative">
                        <input type="number" id="topupAmountInput" min="30" step="1" inputmode="decimal" placeholder="30.00" class="w-full bg-white text-slate-900 border border-slate-200 rounded-xl px-4 py-3.5 text-2xl font-bold text-center outline-none focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 transition-all">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">บาท</span>
                    </div>
                    <div class="flex items-center justify-between mt-2.5 text-xs">
                        <span class="text-pink-600 font-semibold">⚠️ ขั้นต่ำ 30.00 บาท</span>
                        <span class="text-slate-400">ไม่มีค่าธรรมเนียม</span>
                    </div>
                </div>

                <button type="button" id="btnCreateOrder" onclick="submitCreateOrder()" class="w-full bg-pink-600 hover:bg-pink-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-pink-500/30 transition-all text-base flex items-center justify-center gap-2">
                    <span>สร้าง QR Code ชำระเงิน</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>

            <!-- STEP 2: DYNAMIC QR & SLIP UPLOAD -->
            <div id="slipStep2" class="hidden">
                <!-- Top Header Bar -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200 mb-6 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-slate-500">รหัสรายการ:</span>
                        <span id="displayOrderId" class="font-mono font-bold text-slate-800 text-sm bg-slate-100 px-2.5 py-1 rounded-lg">-</span>
                    </div>
                    <button type="button" onclick="cancelCurrentOrder()" class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>เปลี่ยนยอดเงิน / ยกเลิก</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Dynamic QR Code -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 shadow-sm flex flex-col items-center text-center">
                        <div class="inline-flex items-center gap-1.5 bg-pink-50 text-pink-600 px-3.5 py-1 rounded-full text-xs font-bold mb-4">
                            <span>1.</span> สแกนชำระเงิน (PromptPay)
                        </div>

                        <!-- Amount Highlight -->
                        <div class="mb-4">
                            <div class="text-xs text-slate-400 font-semibold mb-1">ยอดเงินที่ต้องโอนพอดี</div>
                            <div class="text-3xl font-extrabold text-pink-600 tracking-tight">
                                <span id="displayOrderAmount">0.00</span> <span class="text-xl">฿</span>
                            </div>
                        </div>

                        <!-- QR Code Image -->
                        <div class="relative p-3 bg-white rounded-2xl border-2 border-slate-100 shadow-inner mb-4">
                            <img id="displayQrImg" src="" alt="PromptPay QR" class="w-52 h-52 md:w-56 md:h-56 object-contain rounded-lg">
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] font-bold px-3 py-0.5 rounded-full shadow">
                                Dynamic PromptPay QR
                            </div>
                        </div>

                        <!-- Receiver Info -->
                        <div class="w-full bg-slate-50 rounded-2xl p-3.5 mb-4 text-xs space-y-1.5 border border-slate-100">
                            <div class="flex justify-between">
                                <span class="text-slate-400">ชื่อบัญชี:</span>
                                <span id="displayReceiverName" class="font-bold text-slate-700">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">พร้อมเพย์ / บัญชี:</span>
                                <span id="displayReceiverAcc" class="font-bold text-slate-700 font-mono">-</span>
                            </div>
                        </div>

                        <!-- Countdown Timer -->
                        <div class="w-full bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-center justify-center gap-2 text-amber-800 text-xs font-bold">
                            <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>ชำระเงินภายใน: <span id="countdownTimer" class="font-mono text-sm font-black text-rose-600">15:00</span> นาที</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">ห้ามโอนต่ำกว่าหรือมากกว่ายอดที่ระบุ</p>
                    </div>

                    <!-- Right: Upload Slip -->
                    <div class="bg-slate-900 rounded-3xl p-6 md:p-8 shadow-xl flex flex-col justify-between text-white">
                        <div>
                            <div class="flex justify-center mb-6">
                                <div class="bg-white/10 text-emerald-400 px-4 py-1.5 rounded-full text-xs font-bold border border-white/10 flex items-center gap-1.5">
                                    <span>2.</span> แนบสลิปเพื่อตรวจสอบอัตโนมัติ
                                </div>
                            </div>

                            <!-- Dropzone / Upload Box -->
                            <div id="drop-zone" class="upload-area border-2 border-dashed border-slate-700 rounded-2xl p-6 text-center cursor-pointer hover:border-pink-500 hover:bg-slate-800/80 transition-all relative overflow-hidden group">
                                <img id="preview-image" class="hidden w-full h-48 object-contain mb-3 rounded-xl relative z-10" />
                                <div id="upload-text" class="relative z-10 py-4">
                                    <div class="w-14 h-14 bg-slate-800 text-white rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3 group-hover:bg-pink-600 group-hover:scale-110 transition-all">
                                        📷
                                    </div>
                                    <p class="text-white font-bold text-sm mb-1">คลิกที่นี่ หรือลากรูปสลิปมาวาง</p>
                                    <p class="text-slate-400 text-xs">รองรับไฟล์ JPG, PNG, WEBP (สูงสุด 10MB)</p>
                                </div>
                                <input type="file" id="slipFileInput" accept="image/jpeg,image/png,image/webp,image/jpg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="handleSlipFileChange(this)">
                            </div>

                            <!-- Selected File Info -->
                            <div id="selectedFileInfo" class="hidden mt-3 p-3 bg-white/5 border border-white/10 rounded-xl flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-pink-400 text-base">📎</span>
                                    <span id="selectedFileName" class="text-slate-300 truncate max-w-[200px]">-</span>
                                </div>
                                <button type="button" onclick="clearSelectedSlip()" class="text-slate-400 hover:text-rose-400 p-1">✕ ลบ</button>
                            </div>

                            <!-- Feature Note -->
                            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-300 bg-white/5 py-2.5 px-3 rounded-xl border border-white/5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>ระบบปรับยอดเงินอัตโนมัติหลังจากอัพโหลดสลิป</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="button" id="btnSubmitSlip" onclick="submitSlipVerification()" disabled class="w-full bg-slate-700 text-slate-400 font-bold py-4 rounded-xl shadow-lg transition-all text-base cursor-not-allowed">
                                กรุณาเลือกรูปสลิปก่อน
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 2: ANGPAO SECTION (TRUEMONEY)                         -->
        <!-- ============================================================ -->
        <div id="sectionAngpao" class="max-w-4xl mx-auto hidden">
            <div class="bg-white rounded-[32px] p-8 border border-gray-100 shadow-sm max-w-2xl mx-auto">
                <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 mb-6 text-sm text-orange-800">
                    <div class="flex items-center gap-2 font-bold mb-3 text-base">
                        <span class="text-xl">⚠️</span> เงื่อนไขการสร้างซองของขวัญ
                    </div>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>กรอกจำนวนเงินที่ต้องการเติม</li>
                        <li>เลือกประเภทการใส่ซองเป็น <strong class="text-orange-600 bg-orange-100 px-2 py-0.5 rounded">"แบ่งจำนวนเงินเท่ากัน"</strong></li>
                        <li>กรอกจำนวนคนที่รับซองเป็น <strong class="text-orange-600 bg-orange-100 px-2 py-0.5 rounded">"1 คน"</strong> เท่านั้น</li>
                        <li>ระบบจะหักค่าธรรมเนียมการรับซอง 2.9% อัตโนมัติ</li>
                        <li>นำลิงก์ที่ได้มาวางในช่องด้านล่างนี้ (ไม่ต้องกรอกเบอร์ ระบบใช้เบอร์แอดมินที่ตั้งไว้)</li>
                    </ul>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">ลิงก์ซองอังเปา (URL)</label>
                    <input type="text" id="angpaoLink" placeholder="วางลิงก์ซอง หรือรหัสหลัง v= ที่นี่" class="w-full px-4 py-4 rounded-xl bg-slate-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all font-bold text-slate-700 text-center">
                </div>

                <button onclick="submitAngpao()" id="btnAngpao" class="w-full bg-orange-500 text-white font-bold py-4 rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 text-lg">🧧 ยืนยันการรับซองอังเปา</button>
            </div>
        </div>
    </main>

    <script>
        // --- 🟢 Tab Switcher ---
        function switchTab(type) {
            const tabSlip = document.getElementById('tabSlip');
            const tabAngpao = document.getElementById('tabAngpao');
            const sectionSlip = document.getElementById('sectionSlip');
            const sectionAngpao = document.getElementById('sectionAngpao');

            if (type === 'slip') {
                sectionSlip.classList.remove('hidden');
                sectionAngpao.classList.add('hidden');
                tabSlip.className = "flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-pink-600 transition-all";
                tabAngpao.className = "flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            } else {
                sectionSlip.classList.add('hidden');
                sectionAngpao.classList.remove('hidden');
                tabAngpao.className = "flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-orange-600 transition-all";
                tabSlip.className = "flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            }
        }

        // --- 🟢 PromptPay & SlipOK State ---
        let currentOrder = null;
        let countdownInterval = null;
        let selectedSlipFile = null;

        function selectQuickAmount(amt) {
            const input = document.getElementById('topupAmountInput');
            input.value = amt;
            document.querySelectorAll('.quick-btn').forEach(btn => {
                if (parseFloat(btn.textContent) === amt) {
                    btn.classList.add('border-pink-500', 'bg-pink-50', 'text-pink-600');
                } else {
                    btn.classList.remove('border-pink-500', 'bg-pink-50', 'text-pink-600');
                }
            });
        }

        function resetToStep1() {
            if (countdownInterval) clearInterval(countdownInterval);
            currentOrder = null;
            clearSelectedSlip();
            document.getElementById('slipStep1').classList.remove('hidden');
            document.getElementById('slipStep2').classList.add('hidden');
        }

        function showStep2(order) {
            currentOrder = order;
            document.getElementById('displayOrderId').textContent = order.order_id;
            document.getElementById('displayOrderAmount').textContent = Number(order.amount).toFixed(2);
            document.getElementById('displayReceiverName').textContent = order.promptpay_name || 'ร้านค้า';
            document.getElementById('displayReceiverAcc').textContent = order.promptpay_number || '-';
            document.getElementById('displayQrImg').src = order.qr_image_url;

            document.getElementById('slipStep1').classList.add('hidden');
            document.getElementById('slipStep2').classList.remove('hidden');

            startCountdown(order.expires_in_seconds || 900);
        }

        function startCountdown(seconds) {
            if (countdownInterval) clearInterval(countdownInterval);
            let remaining = Math.max(0, Math.floor(seconds));

            function updateDisplay() {
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('countdownTimer').textContent = '00:00';
                    Swal.fire({
                        icon: 'warning',
                        title: 'รายการหมดอายุแล้ว',
                        text: 'รายการเติมเงินนี้หมดเวลา 15 นาทีแล้ว กรุณาสร้างรายการใหม่ครับ'
                    }).then(() => {
                        resetToStep1();
                    });
                    return;
                }
                const m = Math.floor(remaining / 60).toString().padStart(2, '0');
                const s = (remaining % 60).toString().padStart(2, '0');
                document.getElementById('countdownTimer').textContent = `${m}:${s}`;
                remaining -= 1;
            }

            updateDisplay();
            countdownInterval = setInterval(updateDisplay, 1000);
        }

        async function submitCreateOrder() {
            const input = document.getElementById('topupAmountInput');
            const amount = parseFloat(input.value);

            if (isNaN(amount) || amount <= 0) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกจำนวนเงิน',
                    text: 'กรุณาระบุจำนวนเงินที่ต้องการเติม'
                });
            }

            if (amount < 30) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'ยอดเงินต่ำกว่ากำหนด',
                    text: 'ระบบรองรับการเติมเงินขั้นต่ำ 30 บาทขึ้นไปครับ'
                });
            }

            const btn = document.getElementById('btnCreateOrder');
            btn.disabled = true;
            btn.innerHTML = `<span>กำลังสร้าง QR Code...</span>`;

            try {
                const res = await fetch('api/topup.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ action: 'create_order', amount: amount })
                });
                const data = await res.json();

                if (data.status === 'success' && data.data) {
                    showStep2(data.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สามารถสร้างรายการได้',
                        text: data.message || 'เกิดข้อผิดพลาดในการสร้างรายการ'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'เชื่อมต่อล้มเหลว',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<span>สร้าง QR Code ชำระเงิน</span><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>`;
            }
        }

        async function cancelCurrentOrder() {
            if (!currentOrder) {
                resetToStep1();
                return;
            }

            const confirm = await Swal.fire({
                title: 'ยกเลิกรายการ?',
                text: 'คุณต้องการยกเลิกรายการนี้และเปลี่ยนยอดเงินหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#db2777',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ใช่, ยกเลิก',
                cancelButtonText: 'กลับไปชำระเงิน'
            });

            if (!confirm.isConfirmed) return;

            try {
                await fetch('api/topup.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'cancel_order', order_id: currentOrder.order_id })
                });
            } catch (e) {}

            resetToStep1();
        }

        // --- 🟢 Slip Upload & Verification ---
        function handleSlipFileChange(input) {
            const file = input.files && input.files[0];
            if (!file) return;

            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ขนาดไฟล์เกินกำหนด',
                    text: 'รูปภาพต้องมีขนาดไม่เกิน 10MB ครับ'
                });
                input.value = '';
                return;
            }

            selectedSlipFile = file;
            document.getElementById('selectedFileName').textContent = file.name;
            document.getElementById('selectedFileInfo').classList.remove('hidden');

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview-image');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                document.getElementById('upload-text').classList.add('hidden');
            };
            reader.readAsDataURL(file);

            const btn = document.getElementById('btnSubmitSlip');
            btn.disabled = false;
            btn.className = "w-full bg-pink-600 hover:bg-pink-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-pink-500/30 transition-all text-base cursor-pointer flex items-center justify-center gap-2";
            btn.innerHTML = `<span>ตรวจสอบสลิปและเติมเงิน ⚡</span>`;
        }

        function clearSelectedSlip() {
            selectedSlipFile = null;
            const input = document.getElementById('slipFileInput');
            if (input) input.value = '';
            const preview = document.getElementById('preview-image');
            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }
            const uploadText = document.getElementById('upload-text');
            if (uploadText) uploadText.classList.remove('hidden');
            const info = document.getElementById('selectedFileInfo');
            if (info) info.classList.add('hidden');

            const btn = document.getElementById('btnSubmitSlip');
            if (btn) {
                btn.disabled = true;
                btn.className = "w-full bg-slate-700 text-slate-400 font-bold py-4 rounded-xl shadow-lg transition-all text-base cursor-not-allowed";
                btn.textContent = "กรุณาเลือกรูปสลิปก่อน";
            }
        }

        async function submitSlipVerification() {
            if (!currentOrder || !currentOrder.order_id) {
                return Swal.fire({ icon: 'error', title: 'ไม่พบรายการ', text: 'ไม่พบข้อมูลรายการเติมเงิน กรุณาสร้างรายการใหม่' });
            }
            if (!selectedSlipFile) {
                return Swal.fire({ icon: 'warning', title: 'ยังไม่ได้เลือกสลิป', text: 'กรุณาอัปโหลดรูปสลิปการโอนเงินก่อนครับ' });
            }

            const btn = document.getElementById('btnSubmitSlip');
            btn.disabled = true;
            btn.innerHTML = `<span>กำลังส่งตรวจสอบกับ SlipOK... ⏳</span>`;

            Swal.fire({
                title: 'กำลังตรวจสอบสลิป...',
                html: `<div class="text-sm text-slate-600 mt-2">
                    <p>ระบบกำลังส่งสลิปไปตรวจสอบกับ SlipOK API</p>
                    <p class="text-xs text-slate-400 mt-1">กรุณารอสักครู่...</p>
                </div>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('action', 'check_slip');
            formData.append('order_id', currentOrder.order_id);
            formData.append('slip', selectedSlipFile);

            try {
                const res = await fetch('api/topup.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    if (countdownInterval) clearInterval(countdownInterval);

                    Swal.fire({
                        icon: 'success',
                        title: 'เติมเงินสำเร็จ! 🎉',
                        html: `<div class="text-slate-600 text-sm mt-3 space-y-2">
                            <div class="text-2xl font-black text-emerald-600">+${Number(data.data.amount).toFixed(2)} บาท</div>
                            <p class="text-slate-500">ยอดเงินคงเหลือใหม่: <strong class="text-slate-800">${Number(data.data.new_balance).toFixed(2)} ฿</strong></p>
                            <div class="text-xs text-slate-400 bg-slate-50 p-2 rounded-lg border border-slate-100 font-mono">
                                Ref: ${data.data.trans_ref || '-'}
                            </div>
                        </div>`,
                        confirmButtonColor: '#db2777',
                        confirmButtonText: 'ไปหน้าร้านค้า'
                    }).then(() => {
                        window.location.href = 'store.php';
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = `<span>ตรวจสอบสลิปและเติมเงิน ⚡</span>`;

                    Swal.fire({
                        icon: 'error',
                        title: 'การตรวจสอบไม่ผ่าน',
                        text: data.message || 'สลิปไม่ถูกต้อง หรือไม่สามารถยืนยันยอดเงินได้',
                        confirmButtonColor: '#db2777',
                        confirmButtonText: 'ตกลง'
                    });
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = `<span>ตรวจสอบสลิปและเติมเงิน ⚡</span>`;
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#db2777'
                });
            }
        }

        // Setup Drag & Drop
        function setupDropzone() {
            const dropZone = document.getElementById('drop-zone');
            if (!dropZone) return;
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('border-pink-500', 'bg-slate-800');
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('border-pink-500', 'bg-slate-800');
                }, false);
            });
            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    const input = document.getElementById('slipFileInput');
                    input.files = files;
                    handleSlipFileChange(input);
                }
            });
        }

        // --- 🟢 TrueMoney Angpao ---
        async function submitAngpao() {
            const link = document.getElementById('angpaoLink').value.trim();
            if (!link) return Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาวางลิงก์ซองอังเปา' });

            const btn = document.getElementById('btnAngpao');
            btn.innerText = 'กำลังตรวจสอบซอง... ⏳';
            btn.disabled = true;
            Swal.fire({ title: 'กำลังเช็คซองทรูมันนี่...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch('api/topup_angpao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ link: link })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'ได้รับเงินแล้ว! 🎉', text: data.message }).then(() => { window.location.href = 'store.php'; });
                } else {
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
            } finally {
                btn.innerText = '🧧 ยืนยันการรับซองอังเปา';
                btn.disabled = false;
            }
        }

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
            } catch (e) {}
        }

        async function loadTopupSettings() {
            try {
                const res = await fetch('api/topup.php');
                const data = await res.json();
                if (data.status === 'success' && data.active_order) {
                    showStep2(data.active_order);
                } else {
                    resetToStep1();
                }
            } catch (e) {
                resetToStep1();
            }

            try {
                const rAuth = await fetch('api/check_auth.php');
                const aData = await rAuth.json();
                if (aData.status === 'logged_in') {
                    const bal = document.getElementById('currentBalanceDisplay');
                    if (bal) bal.textContent = Number(aData.balance || 0).toFixed(2) + ' ฿';
                    const badge = document.getElementById('userBalanceBadge');
                    if (badge) badge.classList.remove('hidden');
                }
            } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', () => {
            setupDropzone();
            checkAdminRoleAndInjectButton();
            loadTopupSettings();
        });
    </script>

    <script>
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
