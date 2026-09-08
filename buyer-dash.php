<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Dashboard - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="skeleton.css">
    <link rel="stylesheet" href="announcement.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .sidebar-link.active { background-color: #2563eb; color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .vpn-card:hover { border-color: #93c5fd; transform: translateY(-4px); cursor: pointer; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1); }
        .modal-active { display: flex !important; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .vpn-card { min-width: 0; }
        @media (max-width: 767px) {
            .vpn-card:hover { transform: none; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); }
        }
    
    </style>
    <script>
        let current_opened_id = null;
        let deleteCountdownInterval = null;
        let isUserReseller = false; 
        let vpnItems = [];
        const vpnItemsById = new Map();
        const vpnTrafficStates = Object.create(null);
        const vpnTrafficQueue = [];
        const vpnTrafficQueued = new Set();
        let vpnTrafficActive = 0;
        let vpnFilterTimer = null;

        const authReady = fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') {
                window.location.href = 'login.php';
                throw new Error('auth_required');
            }
            return data;
        }).catch(() => {
            window.location.href = 'login.php';
            throw new Error('auth_required');
        });

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    </script>
    <link rel=stylesheet href=mobile-fix.css>
</head>

<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Mobile Header & Drawer -->
    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM <span class="text-blue-600">DASHBOARD</span></span>
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
                    <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-blue-600">DASHBOARD</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-blue-600">DASHBOARD</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0 gap-4">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 truncate">Dashboard 👋</h1>
                <p class="text-gray-500 text-xs md:text-sm truncate">จัดการและต่ออายุไฟล์ VPN/SSH ของคุณได้ที่นี่</p>
            </div>

            <div class="flex items-center gap-3 md:gap-4 shrink-0">
                <div onclick="window.location.href='topup.php'" class="hidden md:flex bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl items-center gap-3 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-lg">💰</div>
                    <div class="flex flex-col">
                        <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">ยอดเงินคงเหลือ</span>
                        <span class="font-bold text-emerald-700 leading-none text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                    </div>
                    <span class="ml-2 bg-emerald-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-sm group-hover:bg-emerald-600 transition-all">+ เติมเงิน</span>
                </div>
                <div onclick="openProfile()" class="flex items-center gap-2 md:gap-3 cursor-pointer bg-white border border-gray-200 pl-3 md:pl-4 pr-1 md:pr-1.5 py-1 md:py-1.5 rounded-full hover:bg-gray-50 transition-all shadow-sm max-w-[140px] sm:max-w-[200px] md:max-w-xs">
                    <span id="userNameDisplay" class="font-bold text-slate-700 text-xs md:text-sm truncate block">กำลังโหลด...</span>
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full border-2 border-white shadow-sm flex items-center justify-center font-bold text-blue-600 shrink-0">👤</div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">
            <div class="bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-[10px] md:text-xs font-bold uppercase mb-1">สถานะเซิร์ฟเวอร์หลัก</p>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-lg shadow-green-500/50"></span>
                        <h3 class="text-base md:text-lg font-bold text-slate-900">Online</h3>
                    </div>
                </div>
            </div>
            <div class="col-span-1 md:col-span-2 bg-slate-900 p-5 md:p-6 rounded-3xl shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between group gap-4 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500 rounded-full blur-3xl opacity-20 pointer-events-none"></div>
                <div class="relative z-10">
                    <h3 class="text-white font-bold text-base md:text-lg mb-1">ต้องการเพิ่มไฟล์ใหม่?</h3>
                    <p class="text-blue-300 text-xs md:text-sm font-bold">ราคาเริ่มต้นเพียง 5 บาทเท่านั้น</p>
                </div>
                <button onclick="window.location.href='store.php'" class="w-full md:w-auto bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-500 transition-all shadow-lg shadow-blue-500/30 relative z-10">ไปที่ร้านค้า 🛒</button>
            </div>
        </div>

        <div id="announcementArea" class="hidden mb-6 space-y-3"></div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 md:mb-4">
            <h2 class="text-lg md:text-xl font-bold text-slate-900 flex items-center gap-2"><span class="text-blue-600">📁</span> รายการเซิร์ฟเวอร์ของคุณ</h2>
            <span id="vpnResultCount" class="text-[11px] md:text-xs font-bold text-slate-400">กำลังโหลดรายการ...</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl md:rounded-3xl p-3 md:p-4 mb-4 md:mb-6 shadow-sm">
            <div class="flex flex-col md:flex-row gap-2 md:gap-3">
                <label class="relative flex-1 min-w-0">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base" aria-hidden="true">⌕</span>
                    <input id="vpnSearchInput" type="search" autocomplete="off" placeholder="ค้นหาชื่อไฟล์ / เซิร์ฟเวอร์ / แพ็กเกจ / UUID" oninput="scheduleVpnFilter()" class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                </label>
                <label class="md:w-48 shrink-0">
                    <span class="sr-only">กรองตามสถานะ</span>
                    <select id="vpnStatusFilter" onchange="applyVpnFilters()" class="w-full h-11 bg-slate-50 border border-slate-200 rounded-xl px-3 text-sm text-slate-700 font-bold outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="all">แสดงทั้งหมด</option>
                        <option value="active">ยังใช้งานอยู่</option>
                        <option value="expired">หมดอายุแล้ว</option>
                    </select>
                </label>
            </div>
            <p class="text-[10px] md:text-xs text-slate-400 mt-2 px-1">ค้นหาได้ทันทีโดยไม่ต้องเลื่อนหาไฟล์ทีละรายการ</p>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 md:gap-6" id="vpn-list">
            <div class="skeleton-card"><div class="skeleton skeleton-line short"></div><div class="skeleton skeleton-line mid"></div><div class="skeleton skeleton-line"></div></div><div class="skeleton-card"><div class="skeleton skeleton-line short"></div><div class="skeleton skeleton-line mid"></div><div class="skeleton skeleton-line"></div></div>
        </div>
    </main>

    <!-- Modal รายละเอียด -->
    <div id="detailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl animate-in fade-in zoom-in duration-300 flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-6 md:p-8 bg-slate-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                <div class="min-w-0 pr-4">
                    <h2 id="modalTitle" class="text-xl md:text-2xl font-bold text-slate-900 truncate">รายละเอียดไฟล์</h2>
                    <p id="modalPkg" class="text-blue-600 font-bold text-xs md:text-sm mt-1 truncate">--</p>
                </div>
                <button onclick="closeDetail()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            <div class="p-6 md:p-8 overflow-y-auto hide-scroll flex-grow">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">สถานะ</p>
                        <p id="modalStatusText" class="font-bold text-xs md:text-sm truncate">--</p>
                    </div>
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">ดาวน์โหลด</p>
                        <p id="modalDownload" class="font-bold text-slate-900 text-xs md:text-sm truncate">0 MB</p>
                    </div>
                    <div class="bg-slate-50 p-3 md:p-4 rounded-2xl border border-gray-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">อัปโหลด</p>
                        <p id="modalUpload" class="font-bold text-slate-900 text-xs md:text-sm truncate">0 MB</p>
                    </div>
                    <div class="bg-orange-50 p-3 md:p-4 rounded-2xl border border-orange-100 flex flex-col justify-center overflow-hidden">
                        <p class="text-[10px] text-orange-500 font-bold uppercase mb-1">เวลาคงเหลือ</p>
                        <p id="modalDaysLeft" class="font-bold text-orange-600 text-[11px] md:text-[13px] whitespace-nowrap tracking-tight">--</p>
                    </div>
                </div>

                <div id="expiredWarningContainer" class="hidden mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 flex-col md:flex-row items-start md:items-center justify-between gap-3 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 text-6xl opacity-10">⚠️</div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-bold text-red-600 flex items-center gap-2"><span class="animate-pulse w-2 h-2 bg-red-600 rounded-full"></span> ไฟล์นี้หมดอายุแล้ว!</h3>
                        <p class="text-[11px] md:text-xs text-red-500 mt-1" id="warningSubText">ระบบจะลบไฟล์นี้ออกจากระบบอัตโนมัติ</p>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-xl border border-red-100 shadow-sm relative z-10 shrink-0 w-full md:w-auto text-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase mb-0.5">สถานะการลบ</p>
                        <p id="deleteCountdown" class="font-bold text-red-600 text-sm font-mono tracking-widest">--:--:--</p>
                    </div>
                </div>

                <div id="qrContainer" class="hidden mb-6 flex flex-col items-center p-6 bg-white border border-dashed border-gray-200 rounded-3xl">
                    <img id="qrImage" src="" alt="QR Code" class="w-32 h-32 md:w-44 md:h-44 mb-3 rounded-lg">
                    <p class="text-[10px] text-gray-400 font-bold uppercase text-center bg-slate-100 px-3 py-1 rounded-full">Scan to connect</p>
                </div>

                <div class="mb-8" id="configSection">
                    <label class="block text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">🔗 ข้อมูลการเชื่อมต่อ / Config</label>
                    
                    <!-- 🟢 ตัวเลือกแอป (แสดงเฉพาะระบบ SSH) -->
                    <div id="sshAppSelector" class="hidden grid grid-cols-2 gap-3 mb-4">
                        <button onclick="showConfigFormat('npv')" id="btnAppNpv" class="bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700">
                            <span class="text-xl">🛡️</span> NPV Tunnel
                        </button>
                        <button onclick="showConfigFormat('netmod')" id="btnAppNetmod" class="bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700">
                            <span class="text-xl">🔥</span> NetMod
                        </button>
                    </div>

                    <div id="sshConfigVariants" class="hidden mb-4 space-y-3"></div>

                    <textarea id="modalConfig" readonly class="w-full bg-slate-900 text-emerald-400 text-[10px] md:text-xs p-4 rounded-2xl h-32 border-none resize-none font-mono focus:outline-none leading-relaxed"></textarea>
                    
                    <div id="genericConfigActions" class="grid grid-cols-2 gap-3 mt-3">
                        <button onclick="copyConfig()" class="bg-slate-800 text-white py-3.5 rounded-xl text-xs font-bold hover:bg-slate-700 transition-all shadow-md">📋 คัดลอกข้อมูล</button>
                        <button id="btnQrCode" onclick="toggleQRCode()" class="bg-blue-600 text-white py-3.5 rounded-xl text-xs font-bold hover:bg-blue-500 transition-all shadow-md shadow-blue-500/30">📱 เปิด QR Code</button>
                    </div>
                </div>

                <div id="renewContainer" class="border-t border-gray-100 pt-6">
                    <p class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2"><span class="w-2 h-2 bg-emerald-500 rounded-full"></span> ต่ออายุการใช้งาน (เพิ่มวัน)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <button onclick="renewVPN(1)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-3 rounded-xl text-[10px] md:text-xs font-bold transition-all shadow-sm">1 วัน</button>
                        <button onclick="renewVPN(7)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-3 rounded-xl text-[10px] md:text-xs font-bold transition-all shadow-sm">7 วัน</button>
                        <button onclick="renewVPN(15)" class="bg-white border border-gray-200 hover:border-emerald-500 hover:text-emerald-600 py-3 rounded-xl text-[10px] md:text-xs font-bold transition-all shadow-sm">15 วัน</button>
                        <button onclick="renewVPN(30)" class="bg-emerald-50 border border-emerald-200 text-emerald-600 py-3 rounded-xl text-[10px] md:text-xs font-bold transition-all shadow-sm">30 วัน</button>
                    </div>
                </div>

                <div id="deleteContainer" class="hidden border-t border-red-100 mt-6 pt-4 pb-2 space-y-2">
                    <button onclick="switchServer()" id="btnSwitchServer" class="hidden w-full bg-purple-50 text-purple-600 hover:bg-purple-500 hover:text-white py-3 rounded-xl text-xs font-bold transition-all shadow-sm">🔄 ย้ายเซิร์ฟเวอร์ (สำหรับตัวแทน)</button>
                    <button onclick="deleteVPN()" id="btnDeleteVPN" class="w-full bg-red-50 text-red-600 hover:bg-red-500 hover:text-white py-3 rounded-xl text-xs font-bold transition-all shadow-sm">🗑️ ลบไฟล์นี้ออกจากระบบถาวร</button>
                    <p id="refundNotice" class="hidden text-center text-[10px] text-emerald-600 font-bold mt-2">💡 ลบภายใน 10 นาที ได้รับเงินคืนเต็มจำนวน</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal โปรไฟล์ -->
    <div id="profileModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl animate-in fade-in zoom-in duration-300 flex flex-col overflow-hidden">
            <div class="p-6 md:p-8 bg-slate-50 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl md:text-2xl font-bold text-slate-900">โปรไฟล์ของคุณ</h2>
                <button onclick="closeProfile()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all shrink-0">✕</button>
            </div>
            <div class="p-6 md:p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl font-bold text-blue-600 border-4 border-blue-50 shadow-sm shrink-0">👤</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-400 font-bold uppercase">ชื่อผู้ใช้ (Username)</p>
                        <p id="profileUsername" class="font-bold text-lg md:text-xl text-slate-900 truncate">--</p>
                    </div>
                </div>
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2"><span class="w-2 h-2 bg-slate-900 rounded-full"></span> เปลี่ยนรหัสผ่าน</h3>
                    <div class="space-y-3">
                        <input type="password" id="oldPwd" placeholder="รหัสผ่านเดิม" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 transition-all">
                        <input type="password" id="newPwd" placeholder="รหัสผ่านใหม่" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 transition-all">
                        <input type="password" id="confirmPwd" placeholder="ยืนยันรหัสผ่านใหม่" class="w-full bg-slate-50 border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 transition-all">
                        <button onclick="changePassword()" id="btnChangePwd" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-blue-600 transition-all mt-2 shadow-lg">บันทึกรหัสผ่านใหม่</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const drawer = document.getElementById('mobileDrawer');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden'); setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0'); drawer.classList.add('-translate-x-full'); setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        function openProfile() { document.getElementById('profileModal').classList.add('modal-active'); }
        function closeProfile() { document.getElementById('profileModal').classList.remove('modal-active'); }

        function parseShopDate(dateStr) {
            if (!dateStr) return new Date(NaN);
            // Database DATETIME is stored in Asia/Bangkok. Add the offset
            // explicitly so a customer's device timezone cannot expire it early.
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(dateStr)) {
                return new Date(dateStr.replace(' ', 'T') + '+07:00');
            }
            return new Date(dateStr);
        }

        function formatThaiDateTime(dateStr) {
            if (!dateStr) return '--';
            const d = parseShopDate(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear() + 543} เวลา ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} น.`;
        }

        function getDetailedTimeLeft(expireStr) {
            if (!expireStr) return '--';
            const diff = parseShopDate(expireStr) - new Date();
            if (diff <= 0) return 'หมดอายุแล้ว';
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const mins = Math.floor((diff / 1000 / 60) % 60);
            let res = [];
            if (days > 0) res.push(`${days} วัน`);
            if (hours > 0) res.push(`${hours} ชม.`);
            if (mins > 0) res.push(`${mins} นาที`);
            return res.length === 0 ? 'น้อยกว่า 1 นาที' : res.join(' ');
        }

        function startDeleteCountdown(expireStr, isSSH) {
            clearInterval(deleteCountdownInterval);
            const warningBox = document.getElementById('expiredWarningContainer');
            const countdownEl = document.getElementById('deleteCountdown');
            const subText = document.getElementById('warningSubText');
            countdownEl.innerText = "กำลังลบบัญชี...";
            subText.innerText = "ระบบจะลบบัญชีออกจากเซิร์ฟเวอร์และฐานข้อมูลโดยอัตโนมัติหลังหมดอายุ";
            warningBox.classList.remove('hidden'); warningBox.classList.add('flex');
        }

        function stopDeleteCountdown() {
            clearInterval(deleteCountdownInterval);
            document.getElementById('expiredWarningContainer').classList.replace('flex', 'hidden');
        }

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('userBalanceDesk').innerText = data.balance;
                    document.getElementById('userBalanceMob').innerText = data.balance;
                    if (document.getElementById('userNameDisplay')) document.getElementById('userNameDisplay').innerText = data.username;
                    if (document.getElementById('profileUsername')) document.getElementById('profileUsername').innerText = data.username;
                    if (data.role === 'reseller') isUserReseller = true;
                }
            } catch (e) {}
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        }

        function getVpnBadgeId(uuid) {
            return `vpn-badge-${String(uuid ?? '').replace(/[^a-zA-Z0-9_-]/g, '_')}`;
        }

        function getVpnBadgeMeta(data) {
            if (!data) return { className: 'px-2 py-1 bg-slate-100 text-slate-500 border-slate-200 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: '⏳ โหลด...' };
            if (data.status === 'error') return { className: 'px-2 py-1 bg-red-50 text-red-600 border-red-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'เชื่อมต่อล้มเหลว' };
            if (data.real_status === 'active') return { className: 'px-2 py-1 bg-green-50 text-green-600 border-green-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shadow-sm shadow-green-500/20 shrink-0', text: '🟢 ใช้งานได้' };
            if (data.real_status === 'expired') return { className: 'px-2 py-1 bg-orange-50 text-orange-600 border-orange-100 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: '🔴 หมดอายุแล้ว' };
            if (data.real_status === 'unknown') return { className: 'px-2 py-1 bg-amber-50 text-amber-700 border-amber-200 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'ตรวจสอบวันหมดอายุ' };
            return { className: 'px-2 py-1 bg-slate-100 text-slate-500 border-slate-300 text-[9px] md:text-[10px] font-bold rounded-full border uppercase shrink-0', text: 'ไม่พบในระบบ' };
        }

        function isVpnExpired(item) {
            const expiry = parseShopDate(item?.expiry_time);
            return !isNaN(expiry.getTime()) && expiry.getTime() <= Date.now();
        }

        function renderVpnCard(item) {
            const badge = getVpnBadgeMeta(vpnTrafficStates[String(item.uuid)]);
            const badgeId = getVpnBadgeId(item.uuid);
            const serverName = escapeHtml(item.server_name || 'ไม่ระบุชื่อเซิร์ฟเวอร์');
            const packageName = escapeHtml(item.package_name || 'ไม่ระบุแพ็กเกจ');
            const expiry = escapeHtml(formatThaiDateTime(item.expiry_time));
            const id = escapeHtml(item.id);
            return `<article class="bg-white p-3 md:p-6 rounded-2xl md:rounded-[24px] shadow-sm border border-gray-200 hover:border-blue-300 vpn-card transition-all flex flex-col cursor-pointer" data-vpn-id="${id}" role="button" tabindex="0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm md:text-xl font-bold text-slate-900 truncate">${serverName}</h3>
                        <p class="text-[10px] md:text-xs text-blue-600 font-bold truncate mt-0.5">📦 ${packageName}</p>
                    </div>
                    <span id="${badgeId}" class="${badge.className}">${badge.text}</span>
                </div>

                <div class="mt-2 md:mt-4 pt-2 md:pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase truncate"><span class="md:hidden">หมดอายุ</span><span class="hidden md:inline">⏱️ หมดอายุเวลา</span></p>
                        <p class="text-[11px] md:text-sm font-bold text-slate-800 truncate">${expiry}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase">เหลือ</p>
                        <p class="text-[10px] md:text-sm font-bold text-orange-500 whitespace-nowrap">${escapeHtml(getDetailedTimeLeft(item.expiry_time))}</p>
                    </div>
                </div>

                <button type="button" class="w-full mt-3 md:mt-5 bg-blue-50 text-blue-600 py-2.5 md:py-3 rounded-xl font-bold text-xs md:text-sm hover:bg-blue-600 hover:text-white transition-all shadow-sm"><span class="sm:hidden">ดู Config</span><span class="hidden sm:inline">ดูรายละเอียดและ Config</span><span class="ml-1" aria-hidden="true">→</span></button>
            </article>`;
        }

        function renderVpnCards(items) {
            const vpnContainer = document.getElementById('vpn-list');
            vpnContainer.innerHTML = items.map(renderVpnCard).join('');
            vpnContainer.querySelectorAll('[data-vpn-id]').forEach(card => {
                const open = () => openDetailById(card.dataset.vpnId);
                card.addEventListener('click', open);
                card.addEventListener('keydown', event => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        open();
                    }
                });
            });
            enqueueVpnTraffic(items);
        }

        function applyVpnFilters() {
            const vpnContainer = document.getElementById('vpn-list');
            const searchInput = document.getElementById('vpnSearchInput');
            const statusFilter = document.getElementById('vpnStatusFilter');
            const query = String(searchInput?.value || '').trim().toLowerCase();
            const status = statusFilter?.value || 'all';
            const visibleItems = vpnItems.filter(item => {
                const searchable = [item.server_name, item.package_name, item.uuid, item.username, item.id, item.config_name, item.name].filter(Boolean).join(' ').toLowerCase();
                if (query && !searchable.includes(query)) return false;
                if (status === 'expired' && !isVpnExpired(item)) return false;
                if (status === 'active' && isVpnExpired(item)) return false;
                return true;
            });

            const count = document.getElementById('vpnResultCount');
            if (count) count.innerText = `แสดง ${visibleItems.length} จาก ${vpnItems.length} ไฟล์`;

            if (!vpnItems.length) {
                vpnContainer.innerHTML = '<p class="col-span-full text-center text-gray-400 py-8 md:py-10 bg-white rounded-2xl md:rounded-[24px] border border-gray-100">คุณยังไม่มีไฟล์เซิร์ฟเวอร์ในขณะนี้</p>';
            } else if (!visibleItems.length) {
                vpnContainer.innerHTML = '<div class="col-span-full text-center py-8 md:py-10 px-4 bg-white rounded-2xl md:rounded-[24px] border border-dashed border-slate-200"><div class="text-3xl mb-2">🔎</div><p class="font-bold text-slate-700">ไม่พบไฟล์ที่ตรงกับการค้นหา</p><p class="text-xs text-slate-400 mt-1">ลองเปลี่ยนคำค้นหาหรือเลือก “แสดงทั้งหมด”</p></div>';
            } else {
                renderVpnCards(visibleItems);
            }
        }

        function scheduleVpnFilter() {
            clearTimeout(vpnFilterTimer);
            vpnFilterTimer = setTimeout(applyVpnFilters, 120);
        }

        function updateVpnBadge(uuid) {
            const badge = document.getElementById(getVpnBadgeId(uuid));
            if (!badge) return;
            const meta = getVpnBadgeMeta(vpnTrafficStates[String(uuid)]);
            badge.className = meta.className;
            badge.innerText = meta.text;
        }

        function loadVpnTraffic(item) {
            return fetch(`api/get_traffic.php?uuid=${encodeURIComponent(item.uuid)}&server=${encodeURIComponent(item.server_name)}`)
                .then(response => response.json())
                .then(data => {
                    vpnTrafficStates[String(item.uuid)] = data;
                    updateVpnBadge(item.uuid);
                })
                .catch(() => {
                    vpnTrafficStates[String(item.uuid)] = { status: 'error' };
                    updateVpnBadge(item.uuid);
                });
        }

        function enqueueVpnTraffic(items) {
            items.forEach(item => {
                const key = String(item.uuid);
                if (Object.prototype.hasOwnProperty.call(vpnTrafficStates, key) || vpnTrafficQueued.has(key)) return;
                vpnTrafficQueued.add(key);
                vpnTrafficQueue.push(item);
            });
            processVpnTrafficQueue();
        }

        function processVpnTrafficQueue() {
            while (vpnTrafficActive < 4 && vpnTrafficQueue.length) {
                const item = vpnTrafficQueue.shift();
                const key = String(item.uuid);
                vpnTrafficQueued.delete(key);
                if (Object.prototype.hasOwnProperty.call(vpnTrafficStates, key)) {
                    updateVpnBadge(item.uuid);
                    continue;
                }
                vpnTrafficActive++;
                loadVpnTraffic(item).finally(() => {
                    vpnTrafficActive--;
                    processVpnTrafficQueue();
                });
            }
        }

        function openDetailById(id) {
            const item = vpnItemsById.get(String(id));
            if (!item) return;
            openDetail(item.id, item.uuid, item.server_name, item.package_name, item.expiry_time, encodeURIComponent(item.config_link || ''));
        }

        async function loadVPNList() {
            const vpnContainer = document.getElementById('vpn-list');
            try {
                const res = await fetch('api/get_vpn_list.php');
                const result = await res.json();
                if (result.status !== 'success') throw new Error(result.message || 'load_failed');

                vpnItems = Array.isArray(result.data) ? result.data : [];
                vpnItemsById.clear();
                vpnItems.forEach(item => vpnItemsById.set(String(item.id), item));
                applyVpnFilters();
            } catch (e) {
                vpnItems = [];
                vpnItemsById.clear();
                const count = document.getElementById('vpnResultCount');
                if (count) count.innerText = 'โหลดรายการไม่สำเร็จ';
                vpnContainer.innerHTML = '<p class="col-span-full text-center text-red-500 py-8 md:py-10 bg-white rounded-2xl md:rounded-3xl border border-gray-100">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>';
            }
        }

        function openDetail(id, uuid, title, pkg, expire, encodedConfig) {
            current_opened_id = id;
            stopDeleteCountdown();

            const configStr = decodeURIComponent(encodedConfig);
            let parsedConfig = {};
            let isSSH = false;

            // ตรวจสอบว่าเป็น JSON (ระบบ SSH ใหม่) หรือ Text ธรรมดา (V2Ray / SSH เก่า)
            try {
                parsedConfig = JSON.parse(configStr);
                isSSH = true;
            } catch(e) {
                parsedConfig = { raw: configStr };
                isSSH = configStr.startsWith('npvt-ssh://') || configStr.startsWith('IP:');
            }

            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalPkg').innerHTML = `<span class="text-blue-600">${pkg}</span> <span class="mx-1.5 text-gray-300 font-normal">|</span> <span class="text-[10px] md:text-xs text-gray-500 font-normal">หมดอายุ: ${formatThaiDateTime(expire)}</span>`;

            const npvVariants = isSSH ? normalizeSshConfigVariants(parsedConfig.npv, 'NPV Tunnel') : [];
            const netmodVariants = isSSH ? normalizeSshConfigVariants(parsedConfig.netmod, 'NetMod') : [];
            const hasSeparateSshConfigs = npvVariants.length || netmodVariants.length;
            document.getElementById('sshAppSelector').classList.add('hidden');
            document.getElementById('sshConfigVariants').classList.toggle('hidden', !hasSeparateSshConfigs);
            document.getElementById('modalConfig').classList.toggle('hidden', hasSeparateSshConfigs);
            document.getElementById('genericConfigActions').classList.toggle('hidden', hasSeparateSshConfigs);
            document.getElementById('btnQrCode').classList.toggle('hidden', isSSH);
            window.currentSshConfig = parsedConfig;
            if (hasSeparateSshConfigs) {
                renderSshConfigVariants(npvVariants, netmodVariants);
            } else {
                document.getElementById('modalConfig').value = isSSH ? String(parsedConfig.raw || configStr) : configStr;
            }

            const statusEl = document.getElementById('modalStatusText');
            statusEl.innerText = '⏳ โหลดสถานะ...';
            statusEl.className = 'font-bold text-gray-500 text-xs md:text-sm truncate';
            document.getElementById('modalUpload').innerText = '⏳';
            document.getElementById('modalDownload').innerText = '⏳';
            document.getElementById('modalDaysLeft').innerText = getDetailedTimeLeft(expire);

            document.getElementById('deleteContainer').classList.add('hidden');
            document.getElementById('renewContainer').classList.add('hidden');
            
            if (isUserReseller) {
                document.getElementById('btnSwitchServer').classList.remove('hidden');
                document.getElementById('refundNotice').classList.remove('hidden');
            } else {
                document.getElementById('btnSwitchServer').classList.add('hidden');
                document.getElementById('refundNotice').classList.add('hidden');
            }

            fetch(`api/get_traffic.php?uuid=${uuid}&server=${encodeURIComponent(title)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('modalUpload').innerText = data.up;
                        document.getElementById('modalDownload').innerText = data.down;

                        if (data.real_status === 'active') {
                            statusEl.innerText = 'ใช้งานได้';
                            statusEl.className = 'font-bold text-green-600 text-xs md:text-sm truncate';
                            document.getElementById('renewContainer').classList.remove('hidden');
                            document.getElementById('deleteContainer').classList.remove('hidden');
                        } else if (data.real_status === 'expired') {
                            statusEl.innerText = 'หมดอายุ';
                            statusEl.className = 'font-bold text-orange-600 text-xs md:text-sm truncate';
                            document.getElementById('modalDaysLeft').innerText = 'หมดอายุแล้ว';
                            
                            startDeleteCountdown(expire, isSSH);
                            
                            document.getElementById('deleteContainer').classList.remove('hidden');
                            document.getElementById('renewContainer').classList.remove('hidden');
                        } else if (data.real_status === 'not_found') {
                            statusEl.innerText = 'ไม่พบในเซิร์ฟเวอร์';
                            statusEl.className = 'font-bold text-red-600 text-xs md:text-sm truncate';
                            document.getElementById('modalDaysLeft').innerText = 'ถูกลบแล้ว';
                            document.getElementById('deleteContainer').classList.remove('hidden');
                        } else if (data.real_status === 'unknown') {
                            statusEl.innerText = 'ข้อมูลวันหมดอายุไม่ถูกต้อง';
                            statusEl.className = 'font-bold text-amber-600 text-xs md:text-sm truncate';
                            document.getElementById('modalDaysLeft').innerText = 'กรุณาติดต่อแอดมิน';
                        }
                    } else {
                        statusEl.innerText = 'เชื่อมต่อล้มเหลว';
                        statusEl.className = 'font-bold text-red-600 text-xs md:text-sm truncate';
                        document.getElementById('deleteContainer').classList.remove('hidden'); 
                    }
                }).catch(() => { 
                    statusEl.innerText = 'เชื่อมต่อล้มเหลว'; 
                    statusEl.className = 'font-bold text-red-600 text-xs md:text-sm truncate'; 
                    document.getElementById('deleteContainer').classList.remove('hidden');
                });

            document.getElementById('qrContainer').classList.add('hidden');
            document.getElementById('detailModal').classList.add('modal-active');
        }

        async function switchServer() {
            if (!isUserReseller || !current_opened_id) {
                return Swal.fire('ไม่สามารถใช้งานได้', 'ระบบย้ายเซิร์ฟเวอร์นี้สำหรับตัวแทนจำหน่ายเท่านั้น', 'warning');
            }

            Swal.fire({ title: 'กำลังโหลดเซิร์ฟเวอร์...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch('api/switch_server.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_options', config_id: current_opened_id })
                });
                const result = await response.json();
                Swal.close();
                if (result.status !== 'success') throw new Error(result.message || 'โหลดข้อมูลย้ายเซิร์ฟเวอร์ไม่สำเร็จ');

                const source = result.data?.source || {};
                const servers = Array.isArray(result.data?.servers) ? result.data.servers : [];
                const movable = servers.filter(server => server.can_move);
                if (!movable.length) {
                    return Swal.fire('ยังย้ายไม่ได้', servers[0]?.reason || 'ยังไม่มีเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน', 'warning');
                }

                const firstId = movable[0].id;
                const optionsHtml = servers.map(server => {
                    const price = server.target_customer_price == null ? '-' : `฿${Number(server.target_customer_price).toFixed(2)}`;
                    const suffix = server.can_move ? ` · ราคาขาย ${price}` : ` · ${server.reason || 'ไม่พร้อมใช้งาน'}`;
                    const selected = server.can_move && server.id === firstId ? ' selected' : '';
                    const disabled = server.can_move ? '' : ' disabled';
                    return `<option value="${server.id}"${selected}${disabled}>${escapeHtml(server.name)} · ${server.is_ssh ? 'SSH' : 'VPN'}${escapeHtml(suffix)}</option>`;
                }).join('');

                const html = `<div class="text-left space-y-3">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-[10px] font-bold uppercase text-slate-400">ไฟล์ปัจจุบัน</p>
                        <p class="mt-1 text-sm font-bold text-slate-900 truncate">${escapeHtml(source.server_name || '-')}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">แพ็กเกจ</span><b class="mt-0.5 block text-slate-800">${escapeHtml(source.package_name || '-')}</b></div>
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">เหลือเวลา</span><b class="mt-0.5 block text-slate-800">${Number(source.remaining_days || 0).toFixed(2)} วัน</b></div>
                        </div>
                    </div>
                    ${source.is_paid ? `<div><label class="block text-xs font-bold text-slate-700 mb-1.5">ราคาที่ขายให้ลูกค้า (บาท)</label><input id="switch-sale-price" type="number" min="0.01" max="1000000" step="0.01" inputmode="decimal" class="swal2-input !m-0 !w-full !text-sm" placeholder="เช่น 50" autofocus><p class="mt-1 text-[10px] text-slate-400">กรอกราคาขายจริงของไฟล์นี้ ไม่ใช่ราคาทุนตัวแทน</p></div>` : `<div class="rounded-xl bg-blue-50 px-3 py-2 text-[11px] font-bold text-blue-700">ไฟล์ฟรี/ทดลอง ระบบจะคงเวลาที่เหลือเดิมโดยไม่ต้องกรอกราคา</div>`}
                    <label class="block text-xs font-bold text-slate-700">เลือกเซิร์ฟเวอร์ปลายทาง</label>
                    <select id="switch-server" class="swal2-input !m-0 !w-full !text-sm">${optionsHtml}</select>
                    <div id="switch-preview" class="rounded-2xl border border-purple-100 bg-purple-50 p-3 text-xs font-bold text-purple-700"></div>
                    <div id="switch-ssh-credentials" class="hidden space-y-2 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                        <p id="switch-ssh-help" class="text-[11px] font-bold text-amber-700"></p>
                        <input id="switch-ssh-user" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH username ใหม่ (เว้นว่างเพื่อใช้ชื่อเดิม)">
                        <input id="switch-ssh-pass" type="password" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH password ใหม่ (เว้นว่างเพื่อใช้รหัสเดิม)">
                    </div>
                    <p class="rounded-xl bg-emerald-50 px-3 py-2 text-[10px] font-bold text-emerald-700">✅ ระบบจะสร้างไฟล์ใหม่ก่อน แล้วลบไฟล์เดิม · ไม่หักเงินเพิ่ม</p>
                </div>`;

                const dialog = await Swal.fire({
                    title: '🔄 ย้ายเซิร์ฟเวอร์',
                    html,
                    width: 560,
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันการย้าย',
                    cancelButtonText: 'ยกเลิก',
                    focusConfirm: false,
                    didOpen: () => {
                        const serverSelect = document.getElementById('switch-server');
                        const priceInput = document.getElementById('switch-sale-price');
                        const previewEl = document.getElementById('switch-preview');
                        const credentialBox = document.getElementById('switch-ssh-credentials');
                        const helpEl = document.getElementById('switch-ssh-help');
                        const sync = () => {
                            const selected = servers.find(server => String(server.id) === String(serverSelect.value));
                            if (!selected) return;
                            const salePrice = Number(priceInput?.value || 0);
                            const targetPrice = Number(selected.target_customer_price || 0);
                            if (!selected.can_move) {
                                previewEl.innerText = `⚠️ ${selected.reason || 'เซิร์ฟเวอร์นี้ยังไม่พร้อมย้าย'}`;
                            } else if (source.is_paid && salePrice <= 0) {
                                previewEl.innerText = 'กรุณากรอกราคาที่ขายให้ลูกค้า เพื่อคำนวณวันปลายทาง';
                            } else if (source.is_paid) {
                                const convertedDays = Number(source.remaining_days || 0) * salePrice / targetPrice;
                                previewEl.innerHTML = `📊 ราคาขายลูกค้า ฿${salePrice.toFixed(2)} → ราคาปลายทาง ฿${targetPrice.toFixed(2)}<br><span class="font-normal">${Number(source.remaining_days || 0).toFixed(2)} วัน → ${convertedDays.toFixed(2)} วัน</span>`;
                            } else {
                                previewEl.innerHTML = '📊 ไฟล์ฟรี/ทดลอง<br><span class="font-normal">คงเวลาที่เหลือเดิม</span>';
                            }
                            credentialBox.classList.toggle('hidden', !selected.is_ssh);
                            helpEl.innerText = selected.requires_ssh_credentials
                                ? 'ปลายทางเป็น SSH กรุณากรอก Username และ Password ใหม่'
                                : 'ปลายทางเป็น SSH · เว้นว่างได้ ระบบจะใช้บัญชีเดิมถ้าใช้ได้';
                        };
                        serverSelect.addEventListener('change', sync);
                        priceInput?.addEventListener('input', sync);
                        sync();
                    },
                    preConfirm: () => {
                        const selected = servers.find(server => String(server.id) === String(document.getElementById('switch-server').value));
                        if (!selected || !selected.can_move) {
                            Swal.showValidationMessage('กรุณาเลือกเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน');
                            return false;
                        }
                        const salePrice = Number(document.getElementById('switch-sale-price')?.value || 0);
                        if (source.is_paid && (!Number.isFinite(salePrice) || salePrice <= 0)) {
                            Swal.showValidationMessage('กรุณากรอกราคาที่ขายให้ลูกค้าให้ถูกต้อง');
                            return false;
                        }
                        const sshUser = document.getElementById('switch-ssh-user')?.value.trim() || '';
                        const sshPass = document.getElementById('switch-ssh-pass')?.value.trim() || '';
                        if (selected.requires_ssh_credentials && (sshUser === '' || sshPass === '')) {
                            Swal.showValidationMessage('กรุณากรอก SSH username และ password ของปลายทาง');
                            return false;
                        }
                        return { new_server_id: String(selected.key || `sv${selected.id}`), sale_price: salePrice, new_ssh_user: sshUser, new_ssh_pass: sshPass };
                    }
                });
                if (!dialog.isConfirmed) return;

                Swal.fire({ title: 'กำลังย้ายเซิร์ฟเวอร์...', text: 'ระบบกำลังสร้างไฟล์ใหม่และตรวจสอบไฟล์เดิม', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const moveResponse = await fetch('api/switch_server.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch', config_id: current_opened_id, ...dialog.value })
                });
                const moveResult = await moveResponse.json();
                if (moveResult.status !== 'success') throw new Error(moveResult.message || 'ย้ายเซิร์ฟเวอร์ไม่สำเร็จ');
                await Swal.fire({ icon: 'success', title: 'ย้ายสำเร็จ', text: moveResult.message || 'ย้ายเซิร์ฟเวอร์เรียบร้อยแล้ว' });
                location.reload();
            } catch (e) {
                Swal.close();
                Swal.fire('ย้ายไม่สำเร็จ', e.message || 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
            }
        }

        async function deleteVPN() {
            const confirmDelete = await Swal.fire({ title: 'ยืนยันการลบไฟล์', text: "หากลบแล้วจะไม่สามารถกู้คืนได้!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: 'ใช่, ลบเลย!', cancelButtonText: 'ยกเลิก' });
            if (!confirmDelete.isConfirmed) return;

            const btn = document.getElementById('btnDeleteVPN');
            const originalText = btn.innerText; btn.innerText = "กำลังลบข้อมูล... ⏳"; btn.disabled = true;

            try {
                const response = await fetch('api/delete_vpn.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ config_id: current_opened_id }) });
                const result = await response.json();
                if (result.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.message });
                }
            } catch (e) { Swal.fire({ icon: 'error', title: 'การเชื่อมต่อขัดข้อง', text: 'เกิดข้อผิดพลาด' }); } finally { btn.innerText = originalText; btn.disabled = false; }
        }

        async function renewVPN(days) {
            const confirmRenew = await Swal.fire({ title: 'ยืนยันการต่ออายุ', text: `ต้องการต่ออายุเพิ่มอีก ${days} วัน ใช่หรือไม่? ระบบจะหักเงินจากยอดคงเหลือของคุณ`, icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#64748b', confirmButtonText: 'ยืนยัน', cancelButtonText: 'ยกเลิก' });
            if (!confirmRenew.isConfirmed) return;

            Swal.fire({ title: 'กำลังดำเนินการ...', text: 'กรุณารอสักครู่ ระบบกำลังต่ออายุเซิร์ฟเวอร์', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            try {
                const response = await fetch('api/renew_vpn.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ config_id: current_opened_id, days: days }) });
                const result = await response.json();
                if (result.status === 'success') { Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message }).then(() => location.reload()); }
                else { Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: result.message }); }
            } catch (e) { Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้' }); }
        }

        function toggleQRCode() {
            const container = document.getElementById('qrContainer');
            const img = document.getElementById('qrImage');
            const config = document.getElementById('modalConfig').value;
            if (container.classList.contains('hidden')) {
                img.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(config)}`;
                container.classList.remove('hidden');
            } else { container.classList.add('hidden'); }
        }

        function copyConfig() {
            const c = document.getElementById("modalConfig"); c.select(); navigator.clipboard.writeText(c.value);
            Toast.fire({ icon: 'success', title: 'คัดลอกข้อมูลสำเร็จ!' });
        }

        function closeDetail() { stopDeleteCountdown(); document.getElementById('detailModal').classList.remove('modal-active'); }

        function normalizeSshConfigVariants(value, label) {
            if (Array.isArray(value)) return value.map((item, index) => ({
                name: String(item?.name || item?.template_name || `${label} ${index + 1}`),
                config: String(item?.config || item?.value || '')
            })).filter(item => item.config);
            if (typeof value === 'string' && value.trim()) return [{ name: `${label} 1`, config: value.trim() }];
            return [];
        }

        function renderSshConfigVariants(npvVariants, netmodVariants) {
            const container = document.getElementById('sshConfigVariants');
            if (!container) return;
            const renderGroup = (label, items, theme) => {
                const palette = theme === 'orange'
                    ? { box: 'border-orange-100 bg-orange-50', title: 'text-orange-800', button: 'bg-orange-600 hover:bg-orange-700' }
                    : { box: 'border-emerald-100 bg-emerald-50', title: 'text-emerald-800', button: 'bg-emerald-600 hover:bg-emerald-700' };
                if (!items.length) return '';
                return `<section class="rounded-2xl border ${palette.box} p-3"><div class="mb-2 flex items-center justify-between gap-2"><h3 class="text-xs font-extrabold ${palette.title}">${theme === 'orange' ? '🔥' : '🛡️'} ${label}</h3><span class="text-[10px] font-bold ${palette.title}">${items.length} แบบ</span></div><div class="space-y-2">${items.map((item, index) => { const fieldId = `ssh-variant-${theme}-${index}`; return `<div class="rounded-xl border border-white/80 bg-white p-2.5 shadow-sm"><div class="mb-1.5 flex items-center justify-between gap-2"><span class="min-w-0 truncate text-[11px] font-bold text-slate-700">${escapeHtml(item.name || `${label} ${index + 1}`)}</span><button type="button" onclick="copyConfigField('${fieldId}')" class="shrink-0 rounded-lg ${palette.button} px-2.5 py-1.5 text-[10px] font-bold text-white">📋 คัดลอก</button></div><textarea id="${fieldId}" readonly rows="3" class="w-full rounded-lg bg-slate-50 p-2 text-[10px] text-slate-700 font-mono outline-none">${escapeHtml(item.config)}</textarea></div>`; }).join('')}</div></section>`;
            };
            container.innerHTML = renderGroup('NPV Tunnel', npvVariants, 'emerald') + renderGroup('NetMod', netmodVariants, 'orange');
        }

        function copyConfigField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            field.focus();
            field.select();
            const clipboardWrite = navigator.clipboard?.writeText(field.value);
            if (clipboardWrite?.catch) clipboardWrite.catch(() => document.execCommand('copy'));
            else document.execCommand('copy');
            Toast.fire({ icon: 'success', title: 'คัดลอกข้อมูลสำเร็จ!' });
        }

        window.showConfigFormat = function(app) {
            const btnNpv = document.getElementById('btnAppNpv');
            const btnNetmod = document.getElementById('btnAppNetmod');
            
            // รีเซ็ตปุ่ม
            btnNpv.className = "bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700";
            btnNetmod.className = "bg-slate-50 border border-slate-200 text-slate-500 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex flex-col items-center justify-center gap-1.5 hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700";

            if (app === 'npv') {
                btnNpv.className = "bg-emerald-50 border-2 border-emerald-500 text-emerald-700 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md flex flex-col items-center justify-center gap-1.5";
                const variants = normalizeSshConfigVariants(window.currentSshConfig.npv, 'NPV Tunnel');
                document.getElementById('modalConfig').value = variants[0]?.config || window.currentSshConfig.raw;
            } else if (app === 'netmod') {
                btnNetmod.className = "bg-orange-50 border-2 border-orange-500 text-orange-700 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md flex flex-col items-center justify-center gap-1.5";
                const variants = normalizeSshConfigVariants(window.currentSshConfig.netmod, 'NetMod');
                document.getElementById('modalConfig').value = variants[0]?.config || window.currentSshConfig.raw;
            } else {
                document.getElementById('modalConfig').value = window.currentSshConfig.raw;
            }
        }

        async function loadAnnouncements() {
            try {
                const r = await fetch('api/announcements.php?action=list');
                const d = await r.json();
                const announcements = Array.isArray(d.data) ? d.data : [];
                if (!announcements.length) return;

                const now = Date.now();
                const visibleAnnouncements = announcements.filter(a => {
                    const key = `nexa_ann_dismissed_${a.id}`;
                    const dismissedUntil = Number(localStorage.getItem(key) || 0);
                    if (dismissedUntil > now) return false;
                    if (dismissedUntil) localStorage.removeItem(key);
                    return true;
                });

                // ล้างคีย์แบบเก่าที่เคยบังคับให้ประกาศเด้งซ้ำหลังสมัครสมาชิก
                localStorage.removeItem('nexa_ann_force');
                localStorage.removeItem('nexa_ann_closed_at');
                if (!visibleAnnouncements.length) return;

                const meta = {
                    info: {icon: 'i', color: '#2563eb', bg: '#dbeafe'},
                    success: {icon: '✓', color: '#059669', bg: '#d1fae5'},
                    warning: {icon: '!', color: '#d97706', bg: '#fef3c7'},
                    danger: {icon: '!', color: '#dc2626', bg: '#fee2e2'}
                };
                const esc = s => {
                    const x = document.createElement('div');
                    x.textContent = s;
                    return x.innerHTML;
                };
                const items = visibleAnnouncements.map(a => {
                    const m = meta[a.type] || meta.info;
                    return `<article class="nexa-ann-item"><span class="nexa-ann-item-icon" style="color:${m.color};background:${m.bg}">${m.icon}</span><div><div class="nexa-ann-item-title">${esc(a.title)}</div><div class="nexa-ann-item-message">${esc(a.message || a.content || '')}</div></div></article>`;
                }).join('');
                const html = `<div class="nexa-ann-head"><div class="nexa-ann-kicker"><span class="nexa-ann-logo"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><span><b>EKROM</b> NEWS</span></div><h2 class="nexa-ann-title">ประกาศข่าวสาร</h2><p class="nexa-ann-subtitle">รายละเอียดและอัปเดตล่าสุดสำหรับคุณ</p></div><div class="nexa-ann-list">${items}</div><label class="nexa-ann-snooze"><input id="nexaAnnSnooze" type="checkbox"><span>ไม่ต้องแสดงซ้ำภายใน 1 ชั่วโมง</span></label>`;

                const saveAnnouncementDismissal = () => {
                    if (document.getElementById('nexaAnnSnooze')?.checked) {
                        const dismissedUntil = Date.now() + 3600000;
                        visibleAnnouncements.forEach(a => {
                            localStorage.setItem(`nexa_ann_dismissed_${a.id}`, String(dismissedUntil));
                        });
                    }
                };

                Swal.fire({
                    html,
                    showConfirmButton: true,
                    confirmButtonText: 'รับทราบแล้ว',
                    customClass: {container: 'nexa-ann-backdrop', popup: 'nexa-ann-popup', confirmButton: 'nexa-ann-confirm'},
                    showClass: {popup: 'nexa-ann-enter'},
                    hideClass: {popup: 'nexa-ann-leave'},
                    buttonsStyling: false,
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    willClose: saveAnnouncementDismissal
                });
            } catch (e) {}
        }
        async function changePassword() {
            const oldPwd = document.getElementById('oldPwd').value;
            const newPwd = document.getElementById('newPwd').value;
            const confirmPwd = document.getElementById('confirmPwd').value;

            if (!oldPwd || !newPwd || !confirmPwd) {
                return Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบทุกช่อง', 'warning');
            }
            if (newPwd !== confirmPwd) {
                return Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน', 'warning');
            }
            if (newPwd.length < 4) {
                return Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 4 ตัวอักษร', 'warning');
            }

            const btn = document.getElementById('btnChangePwd');
            const orig = btn.innerText;
            btn.innerText = 'กำลังบันทึก...';
            btn.disabled = true;

            try {
                const res = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ old_password: oldPwd, new_password: newPwd })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ', data.message || 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว', 'success');
                    document.getElementById('oldPwd').value = '';
                    document.getElementById('newPwd').value = '';
                    document.getElementById('confirmPwd').value = '';
                } else {
                    Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                }
            } catch (e) {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            } finally {
                btn.innerText = orig;
                btn.disabled = false;
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
            } catch (e) { }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                await authReady;
                loadUserInfo();
                loadVPNList();
                loadAnnouncements();
                checkAdminRoleAndInjectButton();
            } catch (e) {}
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
