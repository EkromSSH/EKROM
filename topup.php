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
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .sidebar-link.active { background-color: #2563eb; color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .upload-area.dragover { border-color: #3b82f6; background-color: #1e293b; }
    
    </style>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-4 sm:px-6 py-3.5 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM <span class="text-blue-600">TOPUP</span></span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.location.href='buyer-dash.php'" class="text-slate-600 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition-all">กลับ</button>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-blue-600 focus:outline-none p-1">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-blue-600">TOPUP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-blue-600">TOPUP</span></span>
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
        <header class="mb-8 mt-2 md:mt-0 text-center md:text-left">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">เติมเงินอัตโนมัติ ⚡</h1>
            <p class="text-gray-500 mt-1 text-xs md:text-sm">สแกนจ่าย หรือกรอกซองอังเปา ยอดเงินเข้าทันทีใน 3 วินาที</p>
        </header>

        <div class="max-w-4xl mx-auto mb-6">
            <div class="flex bg-slate-200/70 p-1 rounded-xl border border-slate-200">
                <button onclick="switchTab('slip')" id="tabSlip" class="flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-blue-600 transition-all">💸 โอนเงิน (สลิป)</button>
                <button onclick="switchTab('angpao')" id="tabAngpao" class="flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all">🧧 ซองอังเปา (TrueMoney)</button>
            </div>
        </div>

        <div id="sectionSlip" class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white rounded-[32px] p-8 border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-full text-xs font-bold mb-6">1. สแกนจ่ายเงิน (PromptPay)</div>
                <img id="promptpayQrImg" src="https://i.ibb.co/0RKRMV1h/004999192800471-20260721-195438-2.jpg" alt="PromptPay QR" class="w-48 h-48 md:w-56 md:h-56 border-4 border-slate-100 rounded-2xl shadow-sm mb-4 p-2 bg-white object-contain">
                <h3 class="font-bold text-slate-900 text-lg">สแกนจ่ายด้วยแอปธนาคารเท่านั้น</h3>
                <p class="text-blue-600 font-bold text-sm mt-1" id="receiverName">ชื่อบัญชี: นูรียะห์ ตาเละ</p>
                <p class="text-slate-500 font-semibold text-xs mt-1" id="receiverAccount">พร้อมเพย์ / บัญชี: 081-096-8889</p>
            </div>

            <div class="bg-slate-900 rounded-[32px] p-8 shadow-xl flex flex-col justify-center">
                <div class="flex justify-center mb-6">
                    <div class="bg-white/10 text-emerald-400 px-4 py-1.5 rounded-full text-xs font-bold border border-white/10">2. อัปโหลดสลิปเพื่อยืนยัน</div>
                </div>

                <div class="bg-white/10 border border-white/10 rounded-2xl p-4 mb-5">
                    <label for="slipAmount" class="block text-sm font-bold text-white mb-2">จำนวนเงินที่โอนจริง (บาท)</label>
                    <input type="number" id="slipAmount" min="0.01" step="0.01" inputmode="decimal" placeholder="เช่น 100" oninput="updateSlipSubmitState()" class="w-full bg-white text-slate-900 border-0 rounded-xl px-4 py-3 text-lg font-bold text-center outline-none focus:ring-4 focus:ring-blue-400/30">
                    <p class="text-slate-400 text-[11px] mt-2 text-center">กรอกให้ตรงกับยอดเงินในสลิป ระบบจะส่งยอดนี้ไปตรวจสอบก่อนเติมเงิน</p>
                </div>

                <div id="drop-zone" class="upload-area border-2 border-dashed border-slate-600 rounded-2xl p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-slate-800 transition-all relative overflow-hidden group">
                    <img id="preview-image" class="hidden w-full h-48 object-contain mb-4 rounded-xl relative z-10" />
                    <div id="upload-text" class="relative z-10">
                        <div class="w-12 h-12 bg-slate-800 text-white rounded-full flex items-center justify-center text-xl mx-auto mb-3 group-hover:bg-blue-600 transition-colors">📤</div>
                        <p class="text-white font-bold mb-1">คลิกที่นี่ หรือ ลากสลิปมาวาง</p>
                        <p class="text-slate-400 text-xs">รองรับไฟล์ JPG, PNG</p>
                    </div>
                    <p id="qr-status" class="hidden text-xs font-bold mt-3 relative z-10"></p>
                    <input type="file" id="slip-input" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="previewSlip(this)">
                </div>

                <button id="btn-submit" onclick="uploadSlip()" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl mt-6 hover:bg-blue-500 transition-all shadow-lg shadow-blue-500/30 disabled:bg-slate-600 disabled:shadow-none hidden" disabled>
                    ยืนยันการทำรายการ
                </button>
            </div>
        </div>

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
        // --- 🟢 ฟังก์ชันสลับหน้า (Tabs) ---
        function switchTab(type) {
            const tabSlip = document.getElementById('tabSlip');
            const tabAngpao = document.getElementById('tabAngpao');
            const sectionSlip = document.getElementById('sectionSlip');
            const sectionAngpao = document.getElementById('sectionAngpao');

            if (type === 'slip') {
                sectionSlip.classList.remove('hidden');
                sectionSlip.classList.add('grid');
                sectionAngpao.classList.add('hidden');
                
                tabSlip.className = "flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-blue-600 transition-all";
                tabAngpao.className = "flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            } else {
                sectionSlip.classList.add('hidden');
                sectionSlip.classList.remove('grid');
                sectionAngpao.classList.remove('hidden');
                
                tabAngpao.className = "flex-1 py-3 rounded-lg font-bold text-sm bg-white shadow text-orange-600 transition-all";
                tabSlip.className = "flex-1 py-3 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            }
        }

        // --- 🟢 ระบบอ่าน QR และตรวจสลิปด้วย Zelthr API รุ่นใหม่ ---
        let decodedSlipQr = '';
        let qrDecodeToken = 0;

        function updateSlipSubmitState() {
            const file = document.getElementById('slip-input').files[0];
            const amount = Number.parseFloat(document.getElementById('slipAmount').value);
            const btnSubmit = document.getElementById('btn-submit');
            const readyForSubmit = Boolean(file && Number.isFinite(amount) && amount > 0);
            btnSubmit.classList.toggle('hidden', !readyForSubmit);
            btnSubmit.disabled = !readyForSubmit;
        }

        function setQrStatus(message, className) {
            const status = document.getElementById('qr-status');
            status.textContent = message;
            status.className = `text-xs font-bold mt-3 relative z-10 ${className || ''}`;
        }

        function readImageFromDataUrl(dataUrl) {
            return new Promise((resolve, reject) => {
                const image = new Image();
                image.onload = () => resolve(image);
                image.onerror = reject;
                image.src = dataUrl;
            });
        }

        async function decodeSlipQr(dataUrl, token) {
            decodedSlipQr = '';
            setQrStatus('กำลังอ่าน QR Code จากสลิป... ⏳', 'text-amber-300');
            if (typeof jsQR !== 'function') {
                setQrStatus('โหลดระบบอ่าน QR ไม่สำเร็จ กรุณารีเฟรชหน้าเว็บแล้วลองใหม่', 'text-red-300');
                return;
            }

            try {
                const image = await readImageFromDataUrl(dataUrl);
                if (token !== qrDecodeToken) return;

                const maxSize = 2400;
                const scale = Math.min(1, maxSize / Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round((image.naturalWidth || image.width) * scale));
                canvas.height = Math.max(1, Math.round((image.naturalHeight || image.height) * scale));
                const context = canvas.getContext('2d', { willReadFrequently: true });
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const qr = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });

                if (token !== qrDecodeToken) return;
                if (qr && qr.data) {
                    decodedSlipQr = qr.data.trim();
                    setQrStatus('อ่าน QR Code สำเร็จ พร้อมตรวจสอบสลิป ✅', 'text-emerald-300');
                } else {
                    setQrStatus('อ่าน QR Code ไม่สำเร็จ กรุณาใช้รูปสลิปที่เห็น QR ชัดเจน', 'text-red-300');
                }
            } catch (error) {
                if (token === qrDecodeToken) setQrStatus('ไม่สามารถอ่าน QR Code จากรูปนี้ได้', 'text-red-300');
            }
        }

        function previewSlip(input) {
            const previewImage = document.getElementById('preview-image');
            const uploadText = document.getElementById('upload-text');
            const file = input.files && input.files[0];
            qrDecodeToken += 1;
            decodedSlipQr = '';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('hidden');
                    uploadText.classList.add('hidden');
                    decodeSlipQr(e.target.result, qrDecodeToken);
                    updateSlipSubmitState();
                };
                reader.readAsDataURL(file);
            } else {
                previewImage.removeAttribute('src');
                previewImage.classList.add('hidden');
                uploadText.classList.remove('hidden');
                setQrStatus('', 'hidden');
                updateSlipSubmitState();
            }
        }

        async function uploadSlip() {
            const slipInput = document.getElementById('slip-input');
            const file = slipInput.files[0];
            const amount = Number.parseFloat(document.getElementById('slipAmount').value);
            
            if (!file) {
                return Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกรูปสลิปก่อนครับ!' });
            }
            if (!Number.isFinite(amount) || amount <= 0) {
                return Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณากรอกจำนวนเงินที่ต้องการเติมก่อนตรวจสลิป' });
            }
            if (!decodedSlipQr) {
                return Swal.fire({ icon: 'warning', title: 'ยังอ่าน QR ไม่ได้', text: 'กรุณาใช้รูปสลิปที่มี QR Code ชัดเจน แล้วรอให้ระบบอ่าน QR สำเร็จ' });
            }

            const btnSubmit = document.getElementById('btn-submit');
            const originalText = btnSubmit.innerText;
            btnSubmit.innerText = "กำลังตรวจสอบสลิป... ⏳";
            btnSubmit.disabled = true;

            Swal.fire({
                title: 'กำลังตรวจสอบ...',
                text: 'กรุณารอสักครู่ ระบบกำลังเช็คข้อมูลสลิป',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('api/topup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ qrcode: decodedSlipQr, amount: Number(amount.toFixed(2)) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'เติมเงินสำเร็จ!', text: data.message })
                    .then(() => window.location.href = 'store.php');
                } else {
                    Swal.fire({ icon: 'error', title: 'สลิปไม่ถูกต้อง', text: data.message });
                    resetSlipUI(originalText);
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                resetSlipUI(originalText);
            });
        }

        function resetSlipUI(originalText) {
            const btnSubmit = document.getElementById('btn-submit');
            const slipInput = document.getElementById('slip-input');
            btnSubmit.innerText = originalText;
            btnSubmit.disabled = false;
            slipInput.value = "";
            document.getElementById('slipAmount').value = "";
            document.getElementById('preview-image').classList.add('hidden');
            document.getElementById('preview-image').removeAttribute('src');
            document.getElementById('upload-text').classList.remove('hidden');
            decodedSlipQr = '';
            qrDecodeToken += 1;
            setQrStatus('', 'hidden');
            btnSubmit.classList.add('hidden');
            btnSubmit.disabled = true;
        }

        // --- 🟢 ระบบซองอังเปา ---
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

        // --- เช็คแอดมินสำหรับปุ่มหลังบ้าน ---
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

        async function loadTopupSettings() {
            try {
                const res = await fetch('api/topup.php');
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    const s = data.data;
                    const name = s.slip_receiver_th || s.promptpay_name || s.slip_receiver_en || 'แอดมิน';
                    const acc = s.slip_receiver_account || s.promptpay_number || '';
                    const rName = document.getElementById('receiverName');
                    const rAcc = document.getElementById('receiverAccount');
                    const qrImg = document.getElementById('promptpayQrImg');
                    if (rName) rName.innerText = 'ชื่อบัญชี: ' + name;
                    if (rAcc && acc) {
                        rAcc.innerText = 'พร้อมเพย์ / บัญชี: ' + acc;
                        if (qrImg && acc) {
                            qrImg.src = 'https://promptpay.io/' + encodeURIComponent(acc.replace(/[^0-9]/g, '')) + '.png';
                        }
                    }
                }
            } catch(e) {}
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkAdminRoleAndInjectButton();
            loadTopupSettings();
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
