<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ร้านค้า VPN - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; scroll-behavior: smooth; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .store-hero { isolation: isolate; box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16); }
        .store-hero::after { content: ''; position: absolute; inset: auto -12% -70% 35%; height: 260px; background: rgba(236, 72, 153, 0.2); filter: blur(55px); border-radius: 999px; pointer-events: none; }
        .server-card { min-height: 100%; transform: translateZ(0); }
        .server-card:hover { transform: translateY(-7px); }
        .server-card .card-arrow { transition: transform 0.3s ease, background-color 0.3s ease, color 0.3s ease; }
        .server-card:hover .card-arrow { transform: translateX(3px); }
        .cpu-bar { transition: width 700ms cubic-bezier(0.22, 1, 0.36, 1), background-color 220ms ease; will-change: width; }
        .category-section { scroll-margin-top: 92px; }
        .category-heading-line { flex: 1; height: 1px; background: linear-gradient(90deg, rgba(226,232,240,0.95), rgba(226,232,240,0)); }
        .filter-button { position: relative; overflow: hidden; }
        .filter-button::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 20%, rgba(255,255,255,0.35), transparent 70%); transform: translateX(-120%); transition: transform 0.55s ease; }
        .filter-button:hover::after { transform: translateX(120%); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .fade-in-up { animation: fadeInUp 0.45s ease-out both; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 767px) {
            .server-card:hover { transform: none; }
            .store-hero { box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14); }
        }
    
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel=stylesheet href=mobile-fix.css>
</head>

<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-4 sm:px-6 py-3.5 flex justify-between items-center gap-3 z-40 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs shrink-0">EK</div>
            <span class="font-bold text-lg tracking-tight italic truncate">EKROM <span class="text-pink-600">STORE</span></span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-2 sm:px-3 py-1.5 rounded-lg flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-sm max-w-[145px] sm:max-w-none">
                <span class="text-[10px] text-emerald-600 font-bold uppercase hidden sm:inline">ยอดเงิน</span>
                <span class="text-emerald-600 font-bold sm:hidden">💰</span>
                <span class="font-bold text-emerald-700 text-[11px] sm:text-sm truncate">฿<span id="userBalanceMob">0.00</span></span>
            </div>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-pink-600 focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="drawer-header flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="drawer-title font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">STORE</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2 mt-4">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
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
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">STORE</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-10 xl:p-12 overflow-y-auto relative">
        <div class="max-w-7xl mx-auto relative">
            <header class="store-hero relative overflow-hidden rounded-[26px] md:rounded-[32px] bg-gradient-to-br from-slate-950 via-slate-900 to-pink-950 px-5 py-6 md:px-8 md:py-9 lg:px-10 lg:py-10 mb-5 md:mb-7">
                <div class="absolute -right-10 -top-16 w-64 h-64 rounded-full bg-pink-500/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-16 -bottom-28 w-64 h-64 rounded-full bg-pink-400/10 blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3.5 py-1.5 text-xs sm:text-sm font-bold tracking-wider text-pink-200 uppercase backdrop-blur-sm whitespace-nowrap">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,0.12)] shrink-0"></span>
                            <span>EKROM STORE · VPN SERVICE</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-bold tracking-tight text-white md:text-4xl">ร้านค้า (Store) <span class="text-pink-300">🛒</span></h1>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-300 md:text-base">เลือกเซิร์ฟเวอร์ที่เหมาะกับคุณ แล้วเริ่มใช้งานได้ทันที</p>
                        <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-300 md:text-xs">
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">⚡ เริ่มต้นใช้งานง่าย</span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5">🛡️ เลือกแพ็กเกจได้ตามต้องการ</span>
                        </div>
                    </div>
                    <div onclick="window.location.href='topup.php'" class="group hidden cursor-pointer items-center gap-2.5 rounded-xl border border-emerald-300/40 bg-emerald-400/10 px-3 py-2 backdrop-blur-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300/70 hover:bg-emerald-400/15 md:flex">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-300/20 text-base shadow-inner">💰</div>
                        <div>
                            <span class="block text-[9px] font-bold uppercase tracking-wider text-emerald-200 leading-tight">ยอดเงินคงเหลือ</span>
                            <span class="mt-0.5 block text-sm md:text-base font-bold leading-tight text-emerald-100">฿<span id="userBalanceDesk">0.00</span></span>
                            <span class="block text-[9px] font-semibold text-emerald-200/75 transition-colors group-hover:text-emerald-100">แตะเพื่อเติมเงิน →</span>
                        </div>
                    </div>
                </div>
            </header>

            <div id="stickyFilterBar" class="hidden sticky top-0 z-30 -mx-4 mb-6 bg-slate-50/95 px-4 py-3 backdrop-blur-md transition-all md:-mx-8 md:mb-8 md:px-8 lg:-mx-10 lg:px-10 xl:-mx-12 xl:px-12">
                <div class="mx-auto flex max-w-7xl items-center gap-2 overflow-x-auto rounded-2xl border border-slate-200/80 bg-white/90 p-1.5 shadow-sm hide-scroll" id="categoryFilter"></div>
            </div>

            <div id="storeContent" class="pb-10 min-h-[50vh]">
                <div class="text-center py-10 text-gray-400">กำลังโหลดเซิร์ฟเวอร์... ⏳</div>
            </div>
        </div>
    </main>

    <div id="buyModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div id="buyModalContent" class="bg-white w-full max-w-lg rounded-[32px] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6 md:p-8 border-b border-gray-100 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <div id="modalIcon" class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl">🇹🇭</div>
                    <div>
                        <h2 id="modalServerName" class="text-xl md:text-2xl font-bold text-slate-900">Ais Server 1</h2>
                        <p id="modalServerType" class="text-xs font-bold mt-1">Standard VPN</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            
            <div class="p-6 md:p-8 overflow-y-auto hide-scroll flex-grow bg-slate-50/50">
                
                <!-- 🟢 กล่องแสดงโปรเสริมที่จะแทรกอัตโนมัติ (รองรับหลายโปร) -->
                <div id="requiredAddonBox" class="hidden"></div>
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-900 mb-2">🏷️ ตั้งชื่อไฟล์กำกับ (ไม่บังคับ)</label>
                    <input type="text" id="customNameInput" placeholder="เช่น มือถือเครื่องหลัก, ไอแพด, PC" maxlength="30" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-all shadow-sm">
                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium">💡 ระบบจะใส่วันที่และเวลาหมดอายุต่อท้ายชื่อไฟล์ให้อัตโนมัติ เช่น (หมดอายุ 12/10/2026 23:17)</p>
                </div>
                
                <div id="serverWarningBox" class="hidden mb-6 p-4 rounded-xl border shadow-sm">
                    <h4 id="serverWarningTitle" class="font-bold text-sm mb-2 flex items-center gap-2">⚠️ คำแนะนำก่อนสั่งซื้อ</h4>
                    <ul id="serverWarningList" class="text-xs space-y-2 list-disc list-inside"></ul>
                </div>
                
                <div id="sshAccountConfig" class="hidden mb-6 p-4 bg-slate-100 rounded-xl border border-slate-200 shadow-inner">
                    <label class="block text-sm font-bold text-slate-900 mb-3">🔐 ตั้งค่าบัญชีผู้ใช้งาน (SSH Account)</label>
                    <input type="text" id="sshUserInput" placeholder="Username (ตัวอักษรภาษาอังกฤษเท่านั้น)" pattern="[a-zA-Z]+" title="ตัวอักษรภาษาอังกฤษเท่านั้น" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 mb-3 transition-all">
                    <input type="text" id="sshPassInput" placeholder="Password (ตัวอักษรภาษาอังกฤษเท่านั้น)" pattern="[a-zA-Z]+" title="ตัวอักษรภาษาอังกฤษเท่านั้น" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-pink-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-900 mb-3">⏱️ เลือกแพ็กเกจที่ต้องการ</label>
                    <div id="resellerTrialSettings" class="hidden mb-3 p-4 bg-purple-50 border border-purple-100 rounded-xl relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-4xl opacity-10">⏱️</div>
                        <label class="block text-xs font-bold text-purple-700 mb-1 relative z-10">กำหนดเวลาทดลอง (ตัวแทน)</label>
                        <div class="flex items-center gap-2 relative z-10">
                            <input type="number" id="trialDurationInput" min="1" max="60" value="60" class="w-full bg-white border border-purple-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                            <span class="text-xs font-bold text-purple-600 shrink-0">นาที</span>
                        </div>
                        <p class="text-[9px] text-purple-500 mt-1 font-bold relative z-10">สร้างฟรีไม่จำกัดครั้ง สูงสุด 60 นาที</p>
                    </div>
                    <div id="packageGrid" class="grid grid-cols-2 gap-3"></div>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-100 bg-white shrink-0">
                <button id="btnConfirmBuy" onclick="confirmPurchase()" class="w-full bg-pink-600 text-white font-bold py-4 rounded-xl hover:bg-pink-700 transition-all shadow-lg shadow-pink-500/30">ยืนยันสั่งซื้อ</button>
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

        let currentUserRole = 'user'; 
        let globalWarnings = { ssh: '', v2ray: '' };

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if (data.status === 'success') {
                    if (document.getElementById('userBalanceDesk')) document.getElementById('userBalanceDesk').innerText = data.balance;
                    if (document.getElementById('userBalanceMob')) document.getElementById('userBalanceMob').innerText = data.balance;
                    currentUserRole = data.role; 
                }
            } catch (e) { }
        }

        async function loadWarnings() {
            try {
                const res = await fetch('api/store_warnings.php?action=get');
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    globalWarnings.ssh = data.data.warning_ssh || data.data.ssh || '';
                    globalWarnings.v2ray = data.data.warning_v2ray || data.data.v2ray || '';
                }
            } catch (e) {}
        }

        let serverData = {};
        let selectedServerId = null;
        let statsTimeout = null;
        let globalPriceTiers = [];

        const themeMapper = {
            green: { text: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100', dot: 'bg-emerald-500', btn: 'bg-emerald-500 hover:bg-emerald-600' },
            red: { text: 'text-red-600', bg: 'bg-red-50', border: 'border-red-100', dot: 'bg-red-500', btn: 'bg-red-500 hover:bg-red-600' },
            pink: { text: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100', dot: 'bg-pink-500', btn: 'bg-pink-600 hover:bg-pink-700' },
            blue: { text: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100', dot: 'bg-pink-500', btn: 'bg-pink-600 hover:bg-pink-700' },
            purple: { text: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100', dot: 'bg-purple-500', btn: 'bg-purple-600 hover:bg-purple-700' },
            orange: { text: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-100', dot: 'bg-orange-500', btn: 'bg-orange-500 hover:bg-orange-600' }
        };

        function renderCard(svId, sv) {
            const tier = globalPriceTiers.find(t => t.id === (sv.price_tier || sv.tier_id)) || globalPriceTiers[0] || {};
            const theme = sv.theme || tier.theme || tier.color_theme || 'pink';
            const icon = sv.icon || tier.icon || '🇹🇭';
            const isReseller = currentUserRole === 'reseller';
            
            const pArr = Array.isArray(tier.prices) ? tier.prices : [5, 25, 45, 80];
            const p1 = tier.price_1 !== undefined ? tier.price_1 : (pArr[0] ?? 5);
            const p7 = tier.price_7 !== undefined ? tier.price_7 : (pArr[1] ?? 25);
            const p15 = tier.price_15 !== undefined ? tier.price_15 : (pArr[2] ?? 45);
            const p30 = tier.price_30 !== undefined ? tier.price_30 : (pArr[3] ?? 80);

            const prices = [
                parseFloat(isReseller && tier.reseller_price_1 ? tier.reseller_price_1 : p1),
                parseFloat(isReseller && tier.reseller_price_7 ? tier.reseller_price_7 : p7),
                parseFloat(isReseller && tier.reseller_price_15 ? tier.reseller_price_15 : p15),
                parseFloat(isReseller && tier.reseller_price_30 ? tier.reseller_price_30 : p30)
            ];
            
            const isGaming = theme === 'purple' || theme === 'orange' || theme === 'red';
            const cardStyle = isGaming ? `bg-slate-900 border-slate-800 hover:border-${theme}-500` : `bg-white border-slate-200/80 hover:border-${theme}-300`;
            const textStyle = isGaming ? 'text-white' : 'text-slate-900';
            const pStyle = isGaming ? 'text-slate-400' : 'text-gray-500';

            const userCount = Number(sv.user_count) > 0 ? `${sv.user_count} คน` : 'กำลังโหลด...';
            const parsedCpu = Number(sv.cpu);
            const cpuLoad = sv.cpu === null || sv.cpu === undefined || sv.cpu === '' || !Number.isFinite(parsedCpu)
                ? null
                : Math.min(100, Math.max(0, parsedCpu));
            let cpuColorClass = cpuLoad === null ? 'bg-slate-300' : 'bg-emerald-500';
            if (cpuLoad !== null && cpuLoad >= 80) cpuColorClass = 'bg-red-500'; else if (cpuLoad !== null && cpuLoad >= 50) cpuColorClass = 'bg-orange-500';

            const descText = (sv.description !== null && sv.description !== "") ? sv.description : 'เซิร์ฟเวอร์ความเร็วสูง ทะลุบล็อกลื่นไหล';
            const validPrices = prices.filter(price => Number.isFinite(price) && price >= 0);
            const startingPrice = validPrices.length ? Math.min(...validPrices) : 0;

            serverData[svId] = {
                name: sv.name, type: tier.name, real_type: sv.type, icon: icon, theme: theme,
                addons: sv.addons, // 🟢 รองรับโปรเสริมหลายตัว
                pkgs: [
                    { val: 'trial', name: 'ทดลองใช้งาน', price: 0, tag: isReseller ? 'สร้างฟรีไม่จำกัด' : 'ฟรี 1 สิทธิ์' },
                    { val: '1', name: '1 วัน', price: prices[0] }, { val: '7', name: '7 วัน', price: prices[1] },
                    { val: '15', name: '15 วัน', price: prices[2] }, { val: '30', name: '30 วัน', price: prices[3], tag: 'คุ้มสุด' }
                ]
            };

            return `
            <div onclick="openModal('${svId}')" class="${cardStyle} rounded-[24px] md:rounded-[28px] p-4 md:p-5 border shadow-[0_8px_24px_rgba(15,23,42,0.05)] hover:shadow-[0_18px_38px_rgba(15,23,42,0.13)] transition-all duration-300 server-card cursor-pointer group flex flex-col h-full relative overflow-hidden fade-in-up">
                ${isGaming ? `<div class="absolute -right-16 -top-16 w-48 h-48 bg-${theme}-500 rounded-full blur-3xl opacity-20 pointer-events-none"></div>` : `<div class="absolute -right-20 -top-20 w-44 h-44 bg-${theme}-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>`}
                <div class="flex items-start gap-3 relative z-10 min-w-0">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-${theme}-${isGaming ? '500/20' : '50'} text-${theme}-${isGaming ? '400' : '600'} rounded-2xl flex items-center justify-center text-2xl md:text-3xl border border-${theme}-${isGaming ? '500/30' : '100'} shadow-sm group-hover:scale-105 group-hover:rotate-2 transition-transform shrink-0">${icon}</div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex max-w-[68%] bg-${isGaming ? `${theme}-500/20` : 'slate-100'} text-${isGaming ? `${theme}-300` : 'slate-500'} px-2.5 py-1 text-[9px] md:text-[10px] font-bold rounded-full uppercase border border-${isGaming ? `${theme}-500/30` : 'transparent'} truncate">${tier.name}</span>
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold ${isGaming ? 'text-emerald-300' : 'text-emerald-600'} shrink-0"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.12)]"></span>ออนไลน์</span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold ${textStyle} mt-2 line-clamp-2 leading-tight tracking-tight">${sv.name}</h3>
                    </div>
                </div>
                <p class="text-xs md:text-sm leading-relaxed ${pStyle} mt-4 break-words whitespace-normal relative z-10">${descText}</p>
                <div class="grid grid-cols-2 gap-2.5 mt-4 relative z-10">
                    <div class="bg-${isGaming ? 'slate-800/80' : 'slate-50/90'} border border-${isGaming ? 'slate-700/60' : 'slate-100'} px-3 py-2.5 rounded-2xl min-w-0">
                        <div class="flex items-center gap-1.5 text-[10px] ${isGaming ? 'text-slate-400' : 'text-slate-500'} font-semibold"><span class="text-emerald-500">●</span> ผู้ใช้งาน</div>
                        <div id="userCount-${svId}" class="mt-1 text-sm font-bold text-${theme}-${isGaming ? '400' : '600'} truncate">${userCount}</div>
                    </div>
                    <div class="bg-${isGaming ? 'slate-800/80' : 'slate-50/90'} border border-${isGaming ? 'slate-700/60' : 'slate-100'} px-3 py-2.5 rounded-2xl min-w-0">
                        <div class="flex items-center justify-between gap-1.5 text-[10px] ${isGaming ? 'text-slate-400' : 'text-slate-500'} font-semibold"><span>⚙️ CPU</span><span id="cpuText-${svId}" class="text-${theme}-${isGaming ? '300' : '600'} font-bold">${cpuLoad === null ? '--' : `${cpuLoad}%`}</span></div>
                        <div class="mt-2 bg-${isGaming ? 'slate-700' : 'slate-200'} rounded-full h-1.5 overflow-hidden"><div id="cpuBar-${svId}" class="cpu-bar ${cpuColorClass} h-1.5 rounded-full" style="width: ${cpuLoad ?? 0}%"></div></div>
                    </div>
                </div>
                <div class="flex items-end justify-between gap-3 border-t border-${isGaming ? 'slate-800' : 'slate-100'} mt-5 pt-4 relative z-10">
                    <div class="min-w-0">
                        <span class="block text-[10px] ${isGaming ? 'text-slate-400' : 'text-slate-400'} font-semibold">เริ่มต้นเพียง</span>
                        <span class="mt-0.5 block font-bold text-${theme}-${isGaming ? '400' : '600'} text-base md:text-lg truncate">฿${startingPrice} <span class="text-[10px] font-semibold ${isGaming ? 'text-slate-500' : 'text-slate-400'}">/ 1 วัน</span></span>
                        <span class="mt-1 block text-[10px] font-bold ${isGaming ? 'text-emerald-300' : 'text-emerald-600'}">🎁 มีแพ็กเกจทดลอง</span>
                    </div>
                    <div class="flex items-center gap-2 text-${theme}-${isGaming ? '300' : '600'} font-bold text-xs md:text-sm shrink-0"><span class="hidden sm:inline">เลือกแพ็กเกจ</span><span class="card-arrow flex h-9 w-9 items-center justify-center rounded-full bg-${theme}-${isGaming ? '500/20' : '50'} text-base shadow-sm group-hover:bg-${theme}-${isGaming ? '500' : '600'} group-hover:text-white">➜</span></div>
                </div>
            </div>`;
        }

        function filterCategory(targetId) {
            document.querySelectorAll('.cat-btn').forEach(btn => {
                btn.className = 'cat-btn filter-button shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-transparent text-slate-500 hover:bg-slate-100 border border-transparent transition-all';
            });
            const activeBtn = document.getElementById('btn-cat-' + targetId);
            if (activeBtn) {
                activeBtn.className = 'cat-btn filter-button active shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-slate-900 text-white shadow-md transition-all border border-slate-800 shadow-slate-900/20';
            }

            const sections = document.querySelectorAll('.category-section');
            sections.forEach(sec => {
                if (targetId === 'all' || sec.getAttribute('data-cat-id') === String(targetId)) {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = 'none';
                }
            });
        }

        async function loadServers() {
            const container = document.getElementById('storeContent');
            try {
                const res = await fetch('api/servers.php?action=get_store');
                const result = await res.json();

                if (result.status !== 'success' || (!result.data.categories.length && Object.keys(result.data.uncategorized).length === 0)) {
                    container.innerHTML = '<div class="text-center text-red-400 py-10 font-bold bg-white rounded-3xl border border-gray-100 shadow-sm">ขณะนี้ยังไม่มีเซิร์ฟเวอร์เปิดให้บริการ 🛠️</div>';
                    return;
                }

                globalPriceTiers = result.data.price_tiers || [];
                let fullHtml = '';
                let filterHtml = `<button onclick="filterCategory('all')" id="btn-cat-all" class="cat-btn filter-button active shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-slate-900 text-white shadow-md transition-all border border-slate-800 shadow-slate-900/20">รวมทั้งหมด</button>`;
                serverData = {};

                result.data.categories.forEach(cat => {
                    const svKeys = Object.keys(cat.servers);
                    if (svKeys.length > 0) {
                        const style = themeMapper[cat.color_theme] || themeMapper['pink'] || themeMapper['blue'];
                        filterHtml += `<button onclick="filterCategory('${cat.id}')" id="btn-cat-${cat.id}" class="cat-btn filter-button shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-transparent text-slate-500 hover:bg-slate-100 border border-transparent transition-all">${cat.name}</button>`;
                        fullHtml += `
                    <div class="category-section mb-8 md:mb-12" data-cat-id="${cat.id}">
                            <div class="mb-4 flex items-center gap-3 md:mb-5">
                                <div class="h-8 w-1.5 ${style.dot} rounded-full shadow-sm"></div>
                                <h2 class="text-xl font-bold tracking-tight ${style.text} md:text-2xl">${cat.name}<span class="ml-2 text-[11px] font-semibold text-slate-400 md:text-xs">${svKeys.length} รายการ</span></h2>
                                <div class="category-heading-line"></div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-3">
                        `;
                        svKeys.forEach(svId => { fullHtml += renderCard(svId, cat.servers[svId]); });
                        fullHtml += `</div></div>`;
                    }
                });

                const uncatKeys = Object.keys(result.data.uncategorized);
                if (uncatKeys.length > 0) {
                    filterHtml += `<button onclick="filterCategory('uncat')" id="btn-cat-uncat" class="cat-btn filter-button shrink-0 px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold bg-transparent text-slate-500 hover:bg-slate-100 border border-transparent transition-all">ทั่วไป</button>`;
                    fullHtml += `
                    <div class="category-section mb-8 md:mb-12" data-cat-id="uncat">
                        <div class="mb-4 flex items-center gap-3 md:mb-5">
                            <div class="h-8 w-1.5 bg-slate-400 rounded-full shadow-sm"></div>
                            <h2 class="text-xl font-bold tracking-tight text-slate-700 md:text-2xl">เซิร์ฟเวอร์ทั่วไป<span class="ml-2 text-[11px] font-semibold text-slate-400 md:text-xs">${uncatKeys.length} รายการ</span></h2>
                            <div class="category-heading-line"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-3">
                    `;
                    uncatKeys.forEach(svId => { fullHtml += renderCard(svId, result.data.uncategorized[svId]); });
                    fullHtml += `</div></div>`;
                }

                document.getElementById('categoryFilter').innerHTML = filterHtml;
                document.getElementById('stickyFilterBar').classList.remove('hidden');
                document.getElementById('stickyFilterBar').classList.add('block');
                container.innerHTML = fullHtml;

                if (statsTimeout) clearTimeout(statsTimeout);
                startRealtimeUpdates();
            } catch (e) { container.innerHTML = '<div class="text-center text-red-500 py-10 bg-white rounded-3xl border border-gray-100 shadow-sm">ไม่สามารถเชื่อมต่อกับฐานข้อมูลเซิร์ฟเวอร์ได้ ⚠️</div>'; }
        }

        async function startRealtimeUpdates() {
            statsTimeout = setTimeout(async () => {
                try {
                    const res = await fetch('api/servers.php?action=get_stats&t=' + Date.now(), { cache: 'no-store' });
                    const data = await res.json();
                    if (data.status === 'success') {
                        for (const [svId, stats] of Object.entries(data.data)) {
                            const countEl = document.getElementById(`userCount-${svId}`);
                            const cpuBar = document.getElementById(`cpuBar-${svId}`);
                            const cpuText = document.getElementById(`cpuText-${svId}`);
                            // Do not replace a valid displayed value with a transient
                            // zero while 3x-ui is refreshing its client statistics.
                            if (countEl && Number(stats.user_count) > 0) {
                                countEl.innerText = `${stats.user_count} คน`;
                            }
                            if (cpuBar && cpuText) {
                                const parsedCpu = Number(stats.cpu);
                                const hasCpu = stats.cpu !== null && stats.cpu !== undefined && stats.cpu !== '' && Number.isFinite(parsedCpu);
                                const cpu = hasCpu ? Math.min(100, Math.max(0, parsedCpu)) : null;
                                cpuBar.style.width = `${cpu ?? 0}%`;
                                cpuText.innerText = cpu === null ? '--' : `${cpu}%`;
                                let colorClass = cpu === null ? 'bg-slate-300' : 'bg-emerald-500';
                                if (cpu !== null && cpu >= 80) colorClass = 'bg-red-500'; else if (cpu !== null && cpu >= 50) colorClass = 'bg-orange-500';
                                cpuBar.classList.remove('bg-slate-300', 'bg-emerald-500', 'bg-orange-500', 'bg-red-500');
                                cpuBar.classList.add('cpu-bar', colorClass);
                            }
                        }
                    }
                } catch (e) { }
                startRealtimeUpdates();
            }, 10000);
        }

        function toggleResellerTrial() {
            const pkgInput = document.querySelector('input[name="selectedPkg"]:checked');
            const trialSettings = document.getElementById('resellerTrialSettings');
            if (pkgInput && pkgInput.value === 'trial' && currentUserRole === 'reseller') {
                trialSettings.classList.remove('hidden'); trialSettings.classList.add('block');
            } else if(trialSettings) {
                trialSettings.classList.remove('block'); trialSettings.classList.add('hidden');
            }
        }

        function parseWarningList(text) {
            if (!text) return '';
            const lines = text.split('\n').filter(l => l.trim() !== '');
            return lines.map(l => `<li>${l}</li>`).join('');
        }

        function escapeAddonValue(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function getStoreAddonCodes(addon) {
            const rows = Array.isArray(addon && addon.subscription_codes) ? addon.subscription_codes : [];
            const normalized = rows.map((row, index) => ({
                name: String(row.code_name || row.name || `รหัสสมัคร ${index + 1}`),
                code: String(row.ussd_code || row.code || row.value || '').trim()
            })).filter(row => row.code);
            if (normalized.length) return normalized;

            const legacy = String(addon && addon.ussd_code || '').trim();
            return legacy ? [{ name: 'รหัสสมัครเดิม', code: legacy }] : [];
        }

        function copyAddonUssd(val) {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            const copied = navigator.clipboard && navigator.clipboard.writeText
                ? navigator.clipboard.writeText(val)
                : Promise.reject(new Error('clipboard unavailable'));
            copied.then(() => Toast.fire({ icon: 'success', title: 'คัดลอกเบอร์/รหัสสมัครแล้ว!' }))
                .catch(() => {
                    const helper = document.createElement('textarea');
                    helper.value = val;
                    helper.style.position = 'fixed';
                    helper.style.opacity = '0';
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    helper.remove();
                    Toast.fire({ icon: 'success', title: 'คัดลอกเบอร์/รหัสสมัครแล้ว!' });
                });
        }

        function openModal(svId) {
            selectedServerId = svId;
            const sv = serverData[svId];
            document.getElementById('modalServerName').innerText = sv.name;
            document.getElementById('modalServerType').innerText = sv.type;
            const iconEl = document.getElementById('modalIcon');
            iconEl.innerText = sv.icon || '🇹🇭';

            if (sv.theme === 'purple') {
                document.getElementById('modalServerType').className = 'text-xs font-bold mt-1 text-purple-600';
                iconEl.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-2xl bg-purple-100 text-purple-600';
                document.getElementById('btnConfirmBuy').className = 'w-full bg-purple-600 text-white font-bold py-4 rounded-xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/30';
            } else {
                document.getElementById('modalServerType').className = 'text-xs font-bold mt-1 text-pink-600';
                iconEl.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-2xl bg-pink-50 text-pink-600';
                document.getElementById('btnConfirmBuy').className = 'w-full bg-pink-600 text-white font-bold py-4 rounded-xl hover:bg-pink-700 transition-all shadow-lg shadow-pink-500/30';
            }

            document.getElementById('customNameInput').value = "";
            
            const warningBox = document.getElementById('serverWarningBox');
            const warningTitle = document.getElementById('serverWarningTitle');
            const warningList = document.getElementById('serverWarningList');

            if (sv.real_type === 'ssh_script') {
                const warnHtml = parseWarningList(globalWarnings.ssh);
                if (!warnHtml) {
                    warningBox.classList.add('hidden');
                    warningBox.classList.remove('block');
                } else {
                    warningBox.className = "mb-6 p-4 rounded-xl border border-pink-200 bg-pink-50 shadow-sm block";
                    warningTitle.className = "text-pink-700 font-bold text-sm mb-2 flex items-center gap-2";
                    warningList.className = "text-xs text-pink-600 space-y-2 list-disc list-inside";
                    warningList.innerHTML = warnHtml;
                }

                document.getElementById('sshAccountConfig').classList.remove('hidden');
                document.getElementById('sshUserInput').value = '';
                document.getElementById('sshPassInput').value = '';
            } else {
                const warnHtml = parseWarningList(globalWarnings.v2ray);
                if (!warnHtml) {
                    warningBox.classList.add('hidden');
                    warningBox.classList.remove('block');
                } else {
                    warningBox.className = "mb-6 p-4 rounded-xl border border-orange-200 bg-orange-50 shadow-sm block";
                    warningTitle.className = "text-orange-700 font-bold text-sm mb-2 flex items-center gap-2";
                    warningList.className = "text-xs text-orange-600 space-y-2 list-disc list-inside";
                    warningList.innerHTML = warnHtml;
                }

                document.getElementById('sshAccountConfig').classList.add('hidden');
            }

            // 🟢 วนลูปแสดงการ์ดโปรเสริมทั้งหมดที่ต้องใช้
            const addonBox = document.getElementById('requiredAddonBox');
            if (sv.addons && sv.addons.length > 0) {
                let addonsHtml = '';
                sv.addons.forEach(addon => {
                    const theme = themeMapper[addon.theme_color] || themeMapper['green'];
                    const shortLabel = addon.title.match(/(\d+[A-Za-z]*)/) ? addon.title.match(/(\d+[A-Za-z]*)/)[0] : 'โปร';
                    const codes = getStoreAddonCodes(addon);
                    const codeRowsHtml = codes.length ? codes.map((code, codeIndex) => {
                        const codeId = `required_addon_code_${addon.id}_${codeIndex}`;
                        return `
                            <div class="rounded-xl border border-gray-200 bg-white p-2.5">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold text-gray-500 truncate">${escapeAddonValue(code.name)}</span>
                                    <span class="text-[10px] text-gray-400 shrink-0">เลือกใช้รายการนี้</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="${escapeAddonValue(code.code)}" id="${codeId}" class="w-full min-w-0 bg-slate-50 border border-gray-200 rounded-lg px-2.5 py-2 text-sm font-bold text-slate-700 text-center outline-none focus:border-pink-400 transition-all">
                                    <button onclick="copyAddonUssd(document.getElementById('${codeId}').value)" class="bg-white border border-gray-200 text-slate-600 px-2.5 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 transition-all shrink-0" title="คัดลอก">📋</button>
                                    <a href="tel:${encodeURIComponent(code.code)}" class="${theme.btn} text-white px-2.5 py-2 rounded-lg text-xs font-bold transition-all shadow-sm shrink-0" title="กดสมัคร">📞</a>
                                </div>
                            </div>`;
                    }).join('') : '<div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">ยังไม่ได้ตั้งค่าเบอร์/รหัสสมัคร</div>';
                    
                    addonsHtml += `
                        <div class="rounded-2xl border ${theme.border} ${theme.bg} p-4 relative overflow-hidden">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-white/50 text-${theme.text.split('-')[1]}-600 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 uppercase shadow-sm">${shortLabel}</div>
                                <div class="min-w-0 flex-grow">
                                    <p class="text-[9px] md:text-[10px] font-bold uppercase ${theme.text} opacity-80 mb-0.5">⚠️ โปรเสริมที่ต้องใช้</p>
                                    <h4 class="font-bold text-slate-900 text-sm truncate">${addon.title}</h4>
                                </div>
                            </div>
                                <div class="bg-white rounded-xl p-2.5 mb-3 border border-white/60 shadow-sm flex justify-between items-center">
                                <div>
                                    <p class="text-[9px] text-gray-500 font-bold uppercase">ราคา</p>
                                    <p class="font-bold text-slate-900 text-sm">฿${parseFloat(addon.price)} <span class="text-[9px] text-gray-400 font-normal">/ ${addon.duration_text}</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-gray-500 font-bold uppercase">เบอร์/รหัสสมัคร</p>
                                    <p class="font-bold ${theme.text} text-sm ${theme.bg} px-1.5 py-0.5 rounded border ${theme.border}">${codes.length} รายการ</p>
                                </div>
                            </div>
                            <div class="space-y-2">${codeRowsHtml}</div>
                        </div>
                    `;
                });
                addonBox.className = 'mb-6 space-y-4 block';
                addonBox.innerHTML = addonsHtml;
            } else {
                addonBox.className = 'hidden';
                addonBox.innerHTML = '';
            }

            let pkgsHtml = '';
            sv.pkgs.forEach((pkg, idx) => {
                const isTrial = pkg.val === 'trial';
                const colSpan = isTrial ? 'col-span-2' : '';
                const isChecked = idx === sv.pkgs.length - 1 ? 'checked' : '';
                let styleClass = isTrial ? (sv.theme === 'purple' ? 'border-purple-200 bg-purple-50/50 peer-checked:border-purple-500 peer-checked:bg-purple-100 peer-checked:text-purple-700' : 'border-emerald-200 bg-emerald-50/50 peer-checked:border-emerald-500 peer-checked:bg-emerald-100 peer-checked:text-emerald-700') : (sv.theme === 'purple' ? 'border-gray-200 bg-white peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700' : 'border-gray-200 bg-white peer-checked:border-pink-600 peer-checked:bg-pink-50 peer-checked:text-pink-600');
                let tagHtml = '';
                if (pkg.tag) {
                    const tagColor = isTrial ? (sv.theme === 'purple' ? 'bg-purple-500' : 'bg-emerald-500') : (sv.theme === 'purple' ? 'bg-purple-500' : 'bg-pink-600');
                    const tagPos = isTrial ? 'top-0 right-0 rounded-bl-lg' : '-top-2 left-1/2 -translate-x-1/2 rounded-full whitespace-nowrap';
                    tagHtml = `<span class="absolute ${tagPos} ${tagColor} text-white text-[9px] px-2 py-0.5 font-bold z-10">${pkg.tag}</span>`;
                }
                const priceDisplay = isTrial ? `<div class="text-sm font-bold mt-1 ${sv.theme === 'purple' ? 'text-purple-600' : 'text-emerald-600'}">✨ ${pkg.name}</div>` : `<div class="text-xs md:text-sm font-bold">${pkg.name}</div><div class="text-lg md:text-xl font-bold mt-0.5">฿${pkg.price}</div>`;

                pkgsHtml += `<label class="cursor-pointer group ${colSpan} relative"><input type="radio" name="selectedPkg" value="${pkg.val}" class="peer sr-only" onchange="toggleResellerTrial()" ${isChecked}>${tagHtml}<div class="p-3 md:p-4 rounded-xl border-2 ${styleClass} text-center transition-all relative overflow-hidden h-full flex flex-col justify-center items-center">${priceDisplay}</div></label>`;
            });
            document.getElementById('packageGrid').innerHTML = pkgsHtml;

            toggleResellerTrial();
            const modal = document.getElementById('buyModal');
            const content = document.getElementById('buyModalContent');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
        }

        function closeModal() {
            document.getElementById('buyModal').classList.add('opacity-0');
            document.getElementById('buyModalContent').classList.add('scale-95');
            setTimeout(() => { document.getElementById('buyModal').classList.remove('flex'); document.getElementById('buyModal').classList.add('hidden'); }, 300);
        }

        async function confirmPurchase() {
            const pkgInput = document.querySelector('input[name="selectedPkg"]:checked');
            if (!pkgInput) return Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกแพ็กเกจที่ต้องการ' });

            const pkgVal = pkgInput.value;
            const customName = document.getElementById('customNameInput').value.trim();
            const sv = serverData[selectedServerId];
            
            const sshUser = document.getElementById('sshUserInput').value.trim();
            const sshPass = document.getElementById('sshPassInput').value.trim();
            
            if (sv.real_type === 'ssh_script') {
                if (!sshUser || !sshPass) {
                    return Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณาตั้ง Username และ Password สำหรับผู้ใช้งานด้วยครับ' });
                }
                const englishLetterRegex = /^[a-zA-Z]+$/;
                if (!englishLetterRegex.test(sshUser)) {
                    return Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ถูกต้อง', text: 'Username ต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น' });
                }
                if (!englishLetterRegex.test(sshPass)) {
                    return Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ถูกต้อง', text: 'Password ต้องเป็นตัวอักษรภาษาอังกฤษเท่านั้น' });
                }
            }

            let trialDuration = 60;
            if (pkgVal === 'trial' && currentUserRole === 'reseller') {
                trialDuration = parseInt(document.getElementById('trialDurationInput').value);
                if (isNaN(trialDuration) || trialDuration < 1 || trialDuration > 60) return Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'เวลาทดลองต้องอยู่ระหว่าง 1 ถึง 60 นาที' });
            }

            const isTrial = pkgVal === 'trial';
            const confirmBuy = await Swal.fire({
                title: isTrial ? 'ยืนยันสร้างไฟล์ฟรี' : 'ยืนยันการสั่งซื้อ?',
                text: isTrial ? 'ระบบจะสร้างไฟล์ทดลองให้คุณ' : 'ระบบจะทำการหักเงินจากยอดคงเหลือของคุณ',
                icon: 'question', showCancelButton: true,
                confirmButtonColor: isTrial ? (sv.theme === 'purple' ? '#a855f7' : '#10b981') : (sv.theme === 'purple' ? '#9333ea' : '#db2777'),
                confirmButtonText: isTrial ? 'สร้างไฟล์เลย' : 'ตกลงสั่งซื้อ', cancelButtonText: 'ยกเลิก'
            });

            if (!confirmBuy.isConfirmed) return;
            closeModal();
            Swal.fire({ title: 'กำลังสร้างไฟล์...', text: 'กรุณารอสักครู่ ระบบกำลังติดต่อเซิร์ฟเวอร์', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            try {
                const response = await fetch('api/create_vpn.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ 
                        package: pkgVal, server_id: selectedServerId, custom_name: customName, trial_duration: trialDuration,
                        ssh_user: sshUser, ssh_pass: sshPass
                    }) 
                });
                const data = await response.json();
                if (data.status === 'success') { Swal.fire({ icon: 'success', title: 'สำเร็จ! 🎉', text: data.message }).then(() => { window.location.href = 'buyer-dash.php'; }); }
                else { Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message }).then(() => openModal(selectedServerId)); }
            } catch (e) { Swal.fire({ icon: 'error', title: 'ขัดข้อง', text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้' }); }
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
            } catch (e) { }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadUserInfo(); 
            await loadWarnings(); 
            await loadServers();  
            checkAdminRoleAndInjectButton();
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
