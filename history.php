<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ประวัติการทำรายการ - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .sidebar-link.active { background-color: #2563eb; color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .history-card:hover { border-color: #bfdbfe; background-color: #f8fafc; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM</span>
        </div>
        
        <div class="flex items-center gap-3">
            <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm">
                <span class="text-emerald-700 text-xs font-bold">฿<span id="userBalanceMob">0.00</span></span>
                <span class="bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold">+</span>
            </div>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-blue-600 focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight italic">EKROM</span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📜</span> ประวัติการทำรายการ</a>
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
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-blue-600">SHOP</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ประวัติการทำรายการ 📜</h1>
                <p class="text-gray-500 mt-1 text-xs md:text-sm">ตรวจสอบประวัติการสั่งซื้อและประวัติการเติมเงินของคุณ</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div onclick="window.location.href='topup.php'" class="hidden md:flex bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl items-center gap-3 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-lg">💰</div>
                    <div class="flex flex-col">
                        <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">ยอดเงินคงเหลือ</span>
                        <span class="font-bold text-emerald-700 leading-none text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                    </div>
                    <span class="ml-2 bg-emerald-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-sm group-hover:bg-emerald-600 transition-all">+ เติมเงิน</span>
                </div>
            </div>
        </header>

        <div class="max-w-5xl mx-auto bg-white rounded-[32px] shadow-sm border border-gray-100 p-4 md:p-8">
            
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 md:gap-4 mb-6 border-b border-gray-100 pb-6">
                <div class="bg-slate-100/80 p-1.5 rounded-2xl flex w-full md:w-auto border border-slate-200/60 shadow-inner">
                    <button id="tab-vpn" onclick="switchTab('vpn')" class="flex-1 md:flex-none md:px-8 py-2.5 rounded-xl font-bold text-xs md:text-sm bg-white text-blue-600 shadow-sm transition-all">🛒 สั่งซื้อ VPN</button>
                    <button id="tab-topup" onclick="switchTab('topup')" class="flex-1 md:flex-none md:px-8 py-2.5 rounded-xl font-bold text-xs md:text-sm text-slate-500 hover:text-slate-800 transition-all">💰 เติมเงิน</button>
                </div>
                
                <button onclick="refreshCurrentTab()" class="text-blue-600 text-xs md:text-sm font-bold hover:bg-blue-100 transition-all bg-blue-50 px-5 py-2.5 rounded-2xl border border-blue-100 flex items-center justify-center gap-2">🔄 รีเฟรช</button>
            </div>

            <div id="history-list" class="space-y-4">
                <div class="text-center py-10 text-gray-400">กำลังโหลดข้อมูล...</div>
            </div>
        </div>
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

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if(data.status === 'success') {
                    document.getElementById('userBalanceDesk').innerText = data.balance;
                    document.getElementById('userBalanceMob').innerText = data.balance;
                }
            } catch(e) { console.error('Failed to load user info'); }
        }

        let currentTab = 'vpn';

        function switchTab(tab) {
            currentTab = tab;
            const btnVpn = document.getElementById('tab-vpn');
            const btnTopup = document.getElementById('tab-topup');

            const activeClass = "flex-1 md:flex-none md:px-8 py-2.5 rounded-xl font-bold text-xs md:text-sm bg-white text-blue-600 shadow-sm transition-all";
            const inactiveClass = "flex-1 md:flex-none md:px-8 py-2.5 rounded-xl font-bold text-xs md:text-sm text-slate-500 hover:text-slate-800 transition-all";

            if (tab === 'vpn') {
                btnVpn.className = activeClass;
                btnTopup.className = inactiveClass;
                loadVPNHistory();
            } else {
                btnTopup.className = activeClass;
                btnVpn.className = inactiveClass;
                loadTopupHistory();
            }
        }

        function refreshCurrentTab() {
            if(currentTab === 'vpn') loadVPNHistory();
            else loadTopupHistory();
        }

        // โหลดข้อมูล VPN
        async function loadVPNHistory() {
            const historyContainer = document.getElementById('history-list');
            historyContainer.innerHTML = '<div class="text-center py-10 text-gray-400">กำลังโหลดประวัติการสั่งซื้อ... ⏳</div>';
            
            try {
                const res = await fetch('api/get_vpn_list.php');
                const result = await res.json();
                
                if (result.status === 'success') {
                    if (result.data.length === 0) {
                        historyContainer.innerHTML = `
                            <div class="text-center py-16 flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">📭</div>
                                <p class="text-gray-500 font-bold">ยังไม่มีประวัติการสั่งซื้อ VPN</p>
                                <p class="text-gray-400 text-sm mt-1">กดซื้อแพ็กเกจ VPN เพื่อเริ่มใช้งานได้เลย</p>
                                <button onclick="window.location.href='store.php'" class="mt-6 bg-blue-600 text-white px-6 py-2 rounded-xl font-bold text-sm hover:bg-blue-500 transition-all shadow-md">ไปที่ร้านค้า</button>
                            </div>
                        `;
                    } else {
                        historyContainer.innerHTML = result.data.map(item => {
                            const isActive = item.status_real === 'active';
                            const statusColor = isActive ? 'text-green-600 bg-green-50 border-green-100' : 'text-red-600 bg-red-50 border-red-100';
                            const statusText = isActive ? 'พร้อมใช้งาน' : 'หมดอายุแล้ว';
                            const iconStr = item.server_name.includes('Gaming') ? '🎮' : '🇹🇭';

                            return `
                            <div class="p-5 rounded-2xl border border-gray-100 transition-all history-card flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl shrink-0">${iconStr}</div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm md:text-base">${item.server_name}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">แพ็กเกจ: <span class="text-blue-600 font-bold">${item.package_name}</span></p>
                                    </div>
                                </div>
                                <div class="flex flex-col md:items-end w-full md:w-auto mt-2 md:mt-0 border-t md:border-none border-gray-50 pt-3 md:pt-0">
                                    <p class="text-[10px] md:text-xs text-gray-400 font-bold mb-1.5 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> หมดอายุ: ${item.expiry_time}</p>
                                    <div class="flex justify-between w-full md:w-auto items-center">
                                        <span class="px-3 py-1 ${statusColor} text-[10px] font-bold rounded-full border uppercase">${statusText}</span>
                                    </div>
                                </div>
                            </div>
                        `}).join('');
                    }
                }
            } catch (e) { 
                historyContainer.innerHTML = '<p class="text-center text-red-500 py-10 font-bold">⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>'; 
            }
        }

        async function loadTopupHistory() {
            const historyContainer = document.getElementById('history-list');
            historyContainer.innerHTML = '<div class="text-center py-10 text-gray-400">กำลังโหลดประวัติการเติมเงิน... ⏳</div>';
            
            try {
                const res = await fetch('api/get_topup_history.php');
                const result = await res.json();
                
                if (result.status === 'success') {
                    if (result.data.length === 0) {
                        historyContainer.innerHTML = `
                            <div class="text-center py-16 flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-4">💳</div>
                                <p class="text-gray-500 font-bold">ยังไม่มีประวัติการเติมเงิน</p>
                                <button onclick="window.location.href='topup.php'" class="mt-6 bg-emerald-500 text-white px-6 py-2 rounded-xl font-bold text-sm hover:bg-emerald-600 transition-all shadow-md">เติมเงินเข้าระบบ</button>
                            </div>
                        `;
                    } else {
                        historyContainer.innerHTML = result.data.map(item => {
                            return `
                            <div class="p-5 rounded-2xl border border-gray-100 transition-all history-card flex justify-between items-center gap-4 hover:border-emerald-200">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">💵</div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm md:text-base">เติมเงินสำเร็จ</h3>
                                        <p class="text-[10px] md:text-xs text-gray-400 mt-0.5">วันที่ทำรายการ: ${item.created_at}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-emerald-600 text-base md:text-lg">+ ฿${parseFloat(item.amount).toFixed(2)}</p>
                                    <span class="text-[9px] text-emerald-500 font-bold bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded uppercase mt-1 inline-block">Success</span>
                                </div>
                            </div>
                        `}).join('');
                    }
                }
            } catch (e) { 
                historyContainer.innerHTML = '<p class="text-center text-red-500 py-10 font-bold">⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>'; 
            }
        }

        loadUserInfo();
        switchTab('vpn');

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

        document.addEventListener('DOMContentLoaded', checkAdminRoleAndInjectButton);
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
