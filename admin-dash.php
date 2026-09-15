<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ระบบแอดมิน - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        th, td { white-space: nowrap; } 
    
    </style>
    <script>
        const authReady = fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') {
                window.location.href = 'login.php';
                throw new Error('auth_required');
            }
            if (data.must_change_password) {
                setTimeout(() => {
                    const c = document.getElementById('defaultPassWarningContainer');
                    if (c) {
                        c.innerHTML = `
                            <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-amber-500/10 border border-amber-300 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl shrink-0">⚠️</span>
                                    <div>
                                        <div class="font-bold text-sm text-slate-900">คำเตือนความปลอดภัย: คุณกำลังใช้งานด้วยรหัสผ่านเริ่มต้น (admin123)</div>
                                        <div class="text-xs text-slate-600 mt-0.5">กรุณาตั้งรหัสผ่านใหม่เพื่อป้องกันการเข้าถึงระบบหลังบ้านโดยไม่ได้รับอนุญาต</div>
                                    </div>
                                </div>
                                <button onclick="promptChangeAdminPassword()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all shrink-0">
                                    🔐 เปลี่ยนรหัสผ่านทันที
                                </button>
                            </div>
                        `;
                    }
                }, 100);
            }
            return data;
        }).catch(() => {
            window.location.href = 'login.php';
            throw new Error('auth_required');
        });

        const Toast = Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 3000, timerProgressBar: true
        });

        // ฟังก์ชันคำนวณเวลาแบบละเอียด
        function parseShopDate(expireStr) {
            if (!expireStr) return new Date(NaN);
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(expireStr)) {
                return new Date(expireStr.replace(' ', 'T') + '+07:00');
            }
            return new Date(expireStr);
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
                <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">👥 จัดการผู้ใช้งาน & สถิติ</a>
                <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
                <button type="button" onclick="promptChangeAdminPassword(); toggleMobileMenu();" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-rose-300 hover:text-white hover:bg-rose-900/30 transition-all w-full text-left">🔐 เปลี่ยนรหัสผ่าน / PIN</button>
                <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-2 sm:mt-4">🏠 กลับหน้าลูกค้า</a>
            </nav>
            <div class="drawer-footer shrink-0 mt-auto pt-4 border-t border-slate-700 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))]">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex">
        <div class="flex items-center gap-3 mb-8 cursor-pointer" onclick="window.location.href='admin-dash.php'">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-1.5 overflow-y-auto pr-1">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
            <button type="button" onclick="promptChangeAdminPassword()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-rose-300 hover:text-white hover:bg-rose-900/30 transition-all w-full text-left">🔐 เปลี่ยนรหัสผ่าน / PIN</button>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-4 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-2.5 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <div id="defaultPassWarningContainer"></div>
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการระบบ 🛠️</h1>
            <p class="text-gray-500 mt-1 text-sm">สรุปรายได้ ค้นหาผู้ใช้งาน และจัดการไฟล์ VPN</p>
        </header>

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-4 md:p-6 rounded-[24px] md:rounded-3xl border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl md:text-2xl font-bold shrink-0">💰</div>
                <div>
                    <p class="text-[10px] md:text-xs text-gray-400 font-bold uppercase">วันนี้</p>
                    <h3 class="text-lg md:text-2xl font-bold text-slate-900 truncate" id="statToday">฿0.00</h3>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-[24px] md:rounded-3xl border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-pink-50 text-pink-500 flex items-center justify-center text-xl md:text-2xl font-bold shrink-0">📅</div>
                <div>
                    <p class="text-[10px] md:text-xs text-gray-400 font-bold uppercase">สัปดาห์นี้</p>
                    <h3 class="text-lg md:text-2xl font-bold text-slate-900 truncate" id="statWeek">฿0.00</h3>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-[24px] md:rounded-3xl border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center text-xl md:text-2xl font-bold shrink-0">📊</div>
                <div>
                    <p class="text-[10px] md:text-xs text-gray-400 font-bold uppercase">เดือนนี้</p>
                    <h3 class="text-lg md:text-2xl font-bold text-slate-900 truncate" id="statMonth">฿0.00</h3>
                </div>
            </div>
            <div class="bg-white p-4 md:p-6 rounded-[24px] md:rounded-3xl border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center gap-3 md:gap-4 opacity-75">
                <div class="w-10 h-10 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center text-xl md:text-2xl font-bold shrink-0">⏳</div>
                <div>
                    <p class="text-[10px] md:text-xs text-gray-400 font-bold uppercase">เดือนที่แล้ว</p>
                    <h3 class="text-lg md:text-2xl font-bold text-slate-900 truncate" id="statLastMonth">฿0.00</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 bg-slate-50">
                <h2 class="text-base md:text-lg font-bold text-slate-900 flex items-center gap-2"><span class="text-pink-600">👥</span> รายชื่อผู้ใช้งาน</h2>
                
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                    <button onclick="cleanupExpired()" class="w-full md:w-auto text-red-600 font-bold text-xs md:text-sm bg-red-50 border border-red-100 px-4 py-2.5 rounded-xl hover:bg-red-500 hover:text-white transition-all shadow-sm">🧹 ล้างไฟล์ขยะ (>7 วัน)</button>
                    
                    <div class="flex gap-2">
                        <div class="relative flex-grow md:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">🔍</span>
                            <input type="text" id="searchInput" onkeyup="handleSearchInput()" placeholder="ค้นหาชื่อ..." class="w-full bg-white border border-gray-200 pl-9 pr-4 py-2.5 rounded-xl text-xs md:text-sm outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-100 transition-all shadow-sm">
                        </div>
                        <button onclick="refreshData(this)" class="text-pink-600 font-bold text-xs md:text-sm bg-pink-50 border border-pink-100 px-4 py-2.5 rounded-xl hover:bg-pink-600 hover:text-white transition-all whitespace-nowrap shadow-sm shrink-0 flex items-center justify-center gap-1.5">
                            <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto hide-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 text-[10px] md:text-xs uppercase tracking-wider border-b border-gray-200">
                            <th class="px-3 md:px-6 py-4 font-bold">ID</th>
                            <th class="px-3 md:px-6 py-4 font-bold">ชื่อผู้ใช้ (Username)</th>
                            <th class="px-3 md:px-6 py-4 font-bold">สิทธิ์ (Role)</th>
                            <th class="px-3 md:px-6 py-4 font-bold text-right">ยอดเงิน (Balance)</th>
                            <th class="px-3 md:px-6 py-4 font-bold text-center">จัดการ (Actions)</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody" class="text-xs md:text-sm divide-y divide-gray-100">
                        <tr><td colspan="5" class="text-center py-10 text-gray-400 font-bold">กำลังโหลดข้อมูล...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- 🟢 ส่วนที่เพิ่มเข้ามา: ปุ่มแบ่งหน้า (Pagination) -->
            <div id="paginationControls" class="p-4 border-t border-gray-100 flex justify-between items-center bg-white hidden">
                <span class="text-[10px] md:text-xs text-gray-500 font-bold" id="pageInfo">แสดง 0-0 จาก 0 คน</span>
                <div class="flex gap-2">
                    <button onclick="changePage(-1)" id="btnPrev" class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 transition-all">⬅️ ก่อนหน้า</button>
                    <button onclick="changePage(1)" id="btnNext" class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 disabled:opacity-50 transition-all">ถัดไป ➡️</button>
                </div>
            </div>
        </div>
    </main>

    <div id="vpnListModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-2 md:p-4 opacity-0 transition-opacity duration-300">
        <div id="vpnListContent" class="bg-white w-full max-w-4xl rounded-[24px] md:rounded-[32px] shadow-2xl flex flex-col max-h-[95vh] md:max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-4 md:p-6 border-b border-gray-100 flex justify-between items-start md:items-center bg-slate-50 shrink-0">
                <div>
                    <h2 class="text-lg md:text-2xl font-bold text-slate-900">ไฟล์ VPN ของลูกค้า</h2>
                    <p id="vpnListOwner" class="text-pink-600 font-bold text-xs md:text-sm mt-1">Username: ---</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button id="adminCreateVpnBtn" onclick="openAdminCreateVpn()" class="bg-pink-600 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-lg shadow-pink-200 hover:bg-pink-700 transition-all">➕ สร้างไฟล์</button>
                    <button onclick="closeVpnListModal()" class="w-8 h-8 md:w-10 md:h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-slate-900 transition-all">✕</button>
                </div>
            </div>
            <div class="p-4 md:p-6 overflow-y-auto hide-scroll flex-grow bg-white">
                <div class="overflow-x-auto border border-gray-200 rounded-2xl hide-scroll">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 text-gray-500 text-[10px] md:text-xs uppercase tracking-wider border-b border-gray-200">
                                <th class="px-4 py-3 font-bold">ชื่อเซิร์ฟเวอร์</th>
                                <th class="px-4 py-3 font-bold">สถานะ</th>
                                <th class="px-4 py-3 font-bold">การใช้งาน</th>
                                <th class="px-4 py-3 font-bold text-center">จัดการไฟล์</th>
                            </tr>
                        </thead>
                        <tbody id="vpnListBody" class="text-xs divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="vpnDetailModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[110] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div id="vpnDetailContent" class="bg-white w-full max-w-lg rounded-[24px] md:rounded-[32px] shadow-2xl flex flex-col transform scale-95 transition-transform duration-300">
            <div class="p-4 md:p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-base md:text-lg font-bold text-slate-900">รายละเอียด Config</h2>
                <button onclick="closeVpnDetailModal()" class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-gray-500 hover:text-slate-900 transition-all">✕</button>
            </div>
            <div class="p-4 md:p-6">
                <p class="text-xs md:text-sm font-bold text-gray-500 mb-1">เซิร์ฟเวอร์: <span id="detailServerName" class="text-pink-600">---</span></p>
                <p class="text-[10px] md:text-xs text-gray-400 mb-4 truncate">UUID: <span id="detailUuid" class="font-mono text-slate-700">---</span></p>
                <div id="detailVpnPanel">
                    <label id="detailConfigLabel" class="block text-xs md:text-sm font-bold text-slate-900 mb-2">ลิงก์ VPN สำหรับลูกค้า</label>
                    <textarea id="detailConfig" readonly class="w-full bg-slate-900 text-emerald-400 text-[10px] md:text-xs p-4 rounded-xl h-24 md:h-32 border-none resize-none font-mono focus:outline-none"></textarea>
                    <button onclick="copyAdminConfig()" class="w-full mt-4 bg-pink-600 text-white font-bold py-3 rounded-xl hover:bg-pink-700 transition-all shadow-lg shadow-pink-200 text-sm">📋 คัดลอกลิงก์ VPN</button>
                </div>
                <div id="detailSshPanel" class="hidden space-y-3">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                        <div class="mb-2 flex items-center justify-between gap-2"><label class="text-xs font-bold text-emerald-800">NPV Tunnel <span id="detailNpvCount" class="font-normal text-emerald-600"></span></label></div>
                        <div id="detailNpvList" class="space-y-2"></div>
                    </div>
                    <div class="rounded-xl border border-orange-100 bg-orange-50 p-3">
                        <div class="mb-2 flex items-center justify-between gap-2"><label class="text-xs font-bold text-orange-800">NetMod <span id="detailNetmodCount" class="font-normal text-orange-600"></span></label></div>
                        <div id="detailNetmodList" class="space-y-2"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><div class="mb-2 flex items-center justify-between gap-2"><label class="text-xs font-bold text-slate-700">Username</label><button onclick="copyAdminField('detailSshUser', 'Username')" class="rounded-lg bg-white px-2 py-1 text-[10px] font-bold text-pink-600 shadow-sm">คัดลอก</button></div><input id="detailSshUser" readonly class="w-full rounded-lg bg-white px-3 py-2 text-xs font-mono text-slate-800 outline-none"></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><div class="mb-2 flex items-center justify-between gap-2"><label class="text-xs font-bold text-slate-700">Password</label><button onclick="copyAdminField('detailSshPass', 'Password')" class="rounded-lg bg-white px-2 py-1 text-[10px] font-bold text-pink-600 shadow-sm">คัดลอก</button></div><input id="detailSshPass" readonly class="w-full rounded-lg bg-white px-3 py-2 text-xs font-mono text-slate-800 outline-none"></div>
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
                menu.classList.remove('hidden');
                setTimeout(() => { menu.classList.remove('opacity-0'); drawer.classList.remove('-translate-x-full'); }, 10);
            } else {
                menu.classList.add('opacity-0'); drawer.classList.add('-translate-x-full');
                setTimeout(() => { menu.classList.add('hidden'); }, 300);
            }
        }

        async function loadStats() {
            try {
                const res = await fetch('api/admin_manage.php?action=get_revenue_stats', { cache: 'no-store' });
                const data = await res.json();
                
                if (data.status === 'success' && data.data) {
                    const today = parseFloat(data.data.today) || 0;
                    const week = parseFloat(data.data.week) || 0;
                    const month = parseFloat(data.data.month) || 0;
                    const lastMonth = parseFloat(data.data.last_month) || 0;

                    document.getElementById('statToday').innerText = '฿' + today.toFixed(2);
                    document.getElementById('statWeek').innerText = '฿' + week.toFixed(2);
                    document.getElementById('statMonth').innerText = '฿' + month.toFixed(2);
                    document.getElementById('statLastMonth').innerText = '฿' + lastMonth.toFixed(2);
                }
            } catch(e) { 
                console.error('Stats load failed'); 
            }
        }

        // 🟢 เพิ่มตัวแปรสำหรับจัดการการโหลดแบ่งหน้าและแก้ปัญหาพิมพ์ค้าง
        let allUsersList = [];
        let filteredUsers = [];
        let currentPage = 1;
        const rowsPerPage = 50; // โหลดทีละ 50 คนป้องกันมือถือค้าง
        let searchTimeout = null;

        // 🟢 1. ฟังก์ชันหน่วงเวลาค้นหา (Debounce) แก้มือถือค้างเวลาพิมพ์
        function handleSearchInput() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterUsers();
            }, 300); // รอผู้ใช้หยุดพิมพ์ 300ms แล้วค่อยค้นหา
        }

        async function loadUsers() {
            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">กำลังโหลด... ⏳</td></tr>';
            try {
                const res = await fetch('api/admin_manage.php?action=get_users', { cache: 'no-store' });
                const data = await res.json();
                if (data.status === 'success') {
                    allUsersList = data.data; // เก็บข้อมูลทั้งหมดไว้ในตัวแปร
                    filteredUsers = [...allUsersList];
                    currentPage = 1;
                    renderUsers(); // สั่งวาด UI ใหม่
                } else {
                    Swal.fire('Error', data.message, 'error').then(() => window.location.href = 'buyer-dash.php');
                }
            } catch(e) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-500">การเชื่อมต่อขัดข้อง</td></tr>'; }
        }

        // 🟢 2. ฟังก์ชันวาด UI แบบแบ่งหน้า (Pagination)
        function renderUsers() {
            const tbody = document.getElementById('userTableBody');
            if (filteredUsers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">ไม่มีผู้ใช้งานที่ค้นหา</td></tr>';
                document.getElementById('paginationControls').classList.add('hidden');
                return;
            }

            document.getElementById('paginationControls').classList.remove('hidden');
            
            // คำนวณช่วงข้อมูลที่จะแสดงในหน้านี้
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const currentData = filteredUsers.slice(startIndex, endIndex);

            tbody.innerHTML = currentData.map((user) => {
                const isAd = user.role === 'admin';
                let roleBadge = '';
                if (user.role === 'admin') {
                    roleBadge = `<span class="bg-rose-100 text-rose-600 px-2 py-1 rounded text-[9px] md:text-[10px] font-bold uppercase">Admin</span>`;
                } else if (user.role === 'reseller') {
                    roleBadge = `<span class="bg-purple-100 text-purple-600 px-2 py-1 rounded text-[9px] md:text-[10px] font-bold uppercase">Reseller</span>`;
                } else {
                    roleBadge = `<span class="bg-pink-50 text-pink-600 px-2 py-1 rounded text-[9px] md:text-[10px] font-bold uppercase">User</span>`;
                }

                let roleActionBtn = '';
                if (user.role === 'admin') {
                    roleActionBtn = `<button onclick="toggleRole(${user.id}, '${user.username}', 'user')" class="bg-slate-100 text-slate-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-slate-200 transition-all flex items-center justify-center shrink-0" title="ปลดสิทธิ์"><span class="text-[15px]">🔽</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">ปลด</span></button>`;
                } else if (user.role === 'reseller') {
                    roleActionBtn = `
                        <button onclick="toggleRole(${user.id}, '${user.username}', 'user')" class="bg-slate-100 text-slate-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-slate-200 transition-all flex items-center justify-center shrink-0" title="ปลดตัวแทน"><span class="text-[15px]">🔽</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">ปลด</span></button>
                        <button onclick="toggleRole(${user.id}, '${user.username}', 'admin')" class="bg-amber-50 text-amber-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-amber-100 transition-all flex items-center justify-center shrink-0" title="ตั้งแอดมิน"><span class="text-[15px]">👑</span></button>
                    `;
                } else {
                    roleActionBtn = `
                        <button onclick="toggleRole(${user.id}, '${user.username}', 'reseller')" class="bg-indigo-50 text-indigo-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-indigo-100 transition-all flex items-center justify-center shrink-0" title="ตั้งตัวแทน"><span class="text-[15px]">💼</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">ตัวแทน</span></button>
                        <button onclick="toggleRole(${user.id}, '${user.username}', 'admin')" class="bg-amber-50 text-amber-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-amber-100 transition-all flex items-center justify-center shrink-0" title="ตั้งแอดมิน"><span class="text-[15px]">👑</span></button>
                    `;
                }

                return `
                <tr class="hover:bg-slate-50 transition-colors user-row">
                    <td class="px-3 md:px-6 py-2.5 md:py-4 text-gray-500 font-bold">#${user.id}</td>
                    <td class="px-3 md:px-6 py-2.5 md:py-4 font-bold text-slate-900 username-cell">${user.username}</td>
                    <td class="px-3 md:px-6 py-2.5 md:py-4">${roleBadge}</td>
                    <td class="px-3 md:px-6 py-2.5 md:py-4 text-right font-bold text-emerald-600">฿${parseFloat(user.balance).toFixed(2)}</td>
                    <td class="px-2 md:px-6 py-2.5 md:py-4 text-center">
                        <div class="flex items-center justify-center gap-1.5 flex-nowrap overflow-x-auto hide-scroll">
                            <button onclick="viewUserVPNs(${user.id}, '${user.username}')" class="bg-purple-50 text-purple-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-purple-100 transition-all flex items-center justify-center shrink-0" title="ดูไฟล์ VPN"><span class="text-[15px]">📁</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">ดูไฟล์</span></button>
                            <button onclick="editBalance(${user.id}, '${user.username}', ${user.balance})" class="bg-emerald-50 text-emerald-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-emerald-100 transition-all flex items-center justify-center shrink-0" title="แก้ไขยอดเงิน"><span class="text-[15px]">💰</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">เติมเงิน</span></button>
                            <button onclick="changePassword(${user.id}, '${user.username}')" class="bg-orange-50 text-orange-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-orange-100 transition-all flex items-center justify-center shrink-0" title="รีเซ็ตรหัสผ่าน"><span class="text-[15px]">🔑</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">รหัส</span></button>
                            ${roleActionBtn}
                            <button onclick="deleteUser(${user.id}, '${user.username}', ${isAd})" class="bg-red-50 text-red-600 w-8 h-8 lg:w-auto lg:h-auto lg:px-2.5 lg:py-1.5 rounded-lg hover:bg-red-100 transition-all flex items-center justify-center shrink-0" title="ลบบัญชี"><span class="text-[15px]">🗑️</span><span class="hidden lg:inline ml-1.5 text-[11px] font-bold">ลบ</span></button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
            
            updatePaginationControls();
        }

        // 🟢 3. อัปเดตข้อมูลการเปลี่ยนหน้า
        function updatePaginationControls() {
            const totalPages = Math.ceil(filteredUsers.length / rowsPerPage);
            document.getElementById('btnPrev').disabled = (currentPage === 1);
            document.getElementById('btnNext').disabled = (currentPage >= totalPages);
            
            const start = (currentPage - 1) * rowsPerPage + 1;
            const end = Math.min(currentPage * rowsPerPage, filteredUsers.length);
            const total = filteredUsers.length;
            
            document.getElementById('pageInfo').innerText = total > 0 ? `แสดง ${start} - ${end} จาก ${total} คน` : `ไม่มีข้อมูล`;
        }

        function changePage(direction) {
            currentPage += direction;
            renderUsers();
        }

        // 🟢 4. ระบบกรองชื่อแบบใช้ Array แทนการซ่อน DOM ตรงๆ (เร็วกว่ามากๆ)
        function filterUsers() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            if (!input) {
                filteredUsers = [...allUsersList];
            } else {
                filteredUsers = allUsersList.filter(user => user.username.toLowerCase().includes(input));
            }
            currentPage = 1; // กลับไปหน้าแรกเสมอเมื่อค้นหา
            renderUsers();
        }

        async function toggleRole(userId, username, newRole) {
            let actionText = '';
            if (newRole === 'admin') actionText = 'ตั้งเป็นแอดมิน';
            else if (newRole === 'reseller') actionText = 'ตั้งเป็นตัวแทน';
            else actionText = 'ปลดสิทธิ์';

            const confirm = await Swal.fire({
                title: `เปลี่ยนสิทธิ์ ${username}?`,
                text: `คุณต้องการ${actionText}ใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newRole === 'admin' ? '#d97706' : (newRole === 'reseller' ? '#4f46e5' : '#64748b')
            });
            if (confirm.isConfirmed) {
                processAdminAction('change_role', { target_id: userId, role: newRole });
            }
        }

        async function deleteUser(userId, username, isAdmin) {
            if (isAdmin) {
                const { value: pin } = await Swal.fire({
                    title: `⚠️ ลบแอดมิน ${username}`,
                    text: 'การลบแอดมินต้องใช้รหัส PIN 6 หลักของคุณ:',
                    input: 'password',
                    inputPlaceholder: 'ใส่รหัส PIN 6 หลัก',
                    inputAttributes: { maxlength: 6, inputmode: 'numeric' },
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonText: 'ตรวจสอบ & ลบ'
                });
                if (pin) {
                    processAdminAction('delete_user', { target_id: userId, admin_pin: pin });
                }
            } else {
                const confirm = await Swal.fire({ 
                    title: `ลบยูสเซอร์ ${username}?`, 
                    text: 'ไฟล์ VPN ทั้งหมดจะถูกลบด้วย', 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#ef4444' 
                });
                if (confirm.isConfirmed) processAdminAction('delete_user', { target_id: userId });
            }
        }

        async function viewUserVPNs(userId, username) {
            Swal.fire({ title: 'กำลังโหลด...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch('api/admin_manage.php', { 
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ action: 'get_user_vpns', target_id: userId }) 
                });
                const data = await res.json();
                Swal.close();

                if (data.status !== 'success') throw new Error(data.message || 'ไม่สามารถโหลดไฟล์ได้');

                window.adminCreateTarget = { id: userId, username: username };
                window.adminVpnConfigs = Object.create(null);
                document.getElementById('vpnListOwner').innerText = 'Username: ' + username;
                const tbody = document.getElementById('vpnListBody');
                const configs = Array.isArray(data.data) ? data.data : [];
                configs.forEach(vpn => { window.adminVpnConfigs[String(vpn.id)] = vpn; });
                if (configs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400 font-bold">ไม่มีไฟล์ VPN</td></tr>';
                } else {
                    tbody.innerHTML = configs.map(vpn => {
                        const configId = Number(vpn.id);
                        const isSsh = vpn.is_ssh === true;
                        return `
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-bold text-slate-900">${escapeAdminHtml(vpn.server_name)}</td>
                                <td class="px-4 py-3"><span id="admin-status-${configId}" class="text-[10px] font-bold text-gray-400">⏳ โหลด...</span></td>
                                <td class="px-4 py-3 text-[9px] md:text-[10px] text-gray-500">${isSsh ? 'SSH' : 'VPN'} · 📥 <span id="admin-down-${configId}">--</span> | 📤 <span id="admin-up-${configId}">--</span></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-nowrap gap-1 justify-center overflow-x-auto hide-scroll">
                                        <button onclick="showVpnDetailById(${configId})" class="bg-pink-50 text-pink-600 px-2 py-1 rounded text-[9px] font-bold shrink-0">📋 Config</button>
                                        <button onclick="adminRenewVpn(${configId})" class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded text-[9px] font-bold shrink-0">➕ ต่ออายุ</button>
                                        <button onclick="adminMoveVpn(${configId})" class="bg-purple-50 text-purple-600 px-2 py-1 rounded text-[9px] font-bold shrink-0 hover:bg-purple-100">🔄 ย้าย</button>
                                        <button onclick="adminRefundVpn(${configId})" class="bg-amber-50 text-amber-700 px-2 py-1 rounded text-[9px] font-bold shrink-0 hover:bg-amber-100">💸 คืนยอด</button>
                                        <button onclick="adminDeleteVpn(${configId})" class="bg-red-50 text-red-600 px-2 py-1 rounded text-[9px] font-bold shrink-0">🗑️ ลบ</button>
                                    </div>
                                </td>
                            </tr>`;
                    }).join('');

                    configs.forEach(vpn => {
                        const configId = Number(vpn.id);
                        fetch(`api/get_traffic.php?uuid=${encodeURIComponent(vpn.uuid)}&server=${encodeURIComponent(vpn.server_name)}`)
                            .then(r => r.json()).then(t => {
                                const statusEl = document.getElementById(`admin-status-${configId}`);
                                const downEl = document.getElementById(`admin-down-${configId}`);
                                const upEl = document.getElementById(`admin-up-${configId}`);
                                if (!statusEl || !downEl || !upEl) return;
                                if (t.status === 'success') {
                                    downEl.innerText = t.down || 'N/A';
                                    upEl.innerText = t.up || 'N/A';
                                    if (t.real_status === 'active') {
                                        const timeLeft = getDetailedTimeLeft(vpn.expiry_time);
                                        statusEl.innerText = `🟢 เหลือ ${timeLeft}`;
                                        statusEl.className = 'text-[10px] font-bold text-green-600';
                                    } else if (t.real_status === 'expired') {
                                        statusEl.innerText = '🔴 หมดอายุ';
                                        statusEl.className = 'text-[10px] font-bold text-red-600';
                                    } else if (t.real_status === 'not_found') {
                                        statusEl.innerText = vpn.is_ssh ? '⚪ ไม่พบบัญชี SSH' : '⚪ ไม่พบในระบบ';
                                        statusEl.className = 'text-[10px] font-bold text-gray-500';
                                    } else {
                                        statusEl.innerText = '⚠️ ตรวจสอบไม่ได้';
                                        statusEl.className = 'text-[10px] font-bold text-amber-600';
                                    }
                                } else {
                                    statusEl.innerText = '⚠️ เชื่อมต่อไม่ได้';
                                    statusEl.className = 'text-[10px] font-bold text-amber-600';
                                }
                            }).catch(() => {
                                const statusEl = document.getElementById(`admin-status-${configId}`);
                                if (statusEl) statusEl.innerText = '⚠️ เชื่อมต่อไม่ได้';
                            });
                        });
                }
                const modal = document.getElementById('vpnListModal');
                modal.classList.remove('hidden'); modal.classList.add('flex');
                setTimeout(() => { modal.classList.remove('opacity-0'); document.getElementById('vpnListContent').classList.remove('scale-95'); }, 10);
            } catch(e) { Swal.fire('Error', 'ไม่สามารถโหลดไฟล์ได้', 'error'); }
        }

        async function openAdminCreateVpn() {
            const target = window.adminCreateTarget;
            if (!target) return Swal.fire('ผิดพลาด', 'ไม่พบลูกค้าที่เลือก', 'error');
            const load = await fetch('api/admin_servers.php?action=list');
            const serverData = await load.json();
            if (serverData.status !== 'success') return Swal.fire('ผิดพลาด', serverData.message || 'โหลดเซิร์ฟเวอร์ไม่สำเร็จ', 'error');
            const servers = (serverData.data || []).filter(s => s.status === 'active');
            if (!servers.length) return Swal.fire('แจ้งเตือน', 'ยังไม่มีเซิร์ฟเวอร์ที่เปิดใช้งาน', 'warning');
            const options = servers.map(s => `<option value="sv${s.id}" data-type="${s.type || ''}">${s.name} · ${s.type === 'ssh_script' || s.type === 'udp_custom' ? 'SSH' : '3x-ui'}</option>`).join('');
            const html = `<div class="text-left space-y-3">
                <div class="rounded-xl bg-pink-50 p-3 text-xs font-bold text-pink-700">👤 ลูกค้า: ${escapeAdminHtml(target.username)}<br><span class="font-normal">ไฟล์นี้สร้างโดยแอดมินและไม่หักยอดลูกค้า</span></div>
                <label class="block text-xs font-bold text-slate-700">เซิร์ฟเวอร์</label><select id="ac-server" class="swal2-input !m-0 !w-full !text-sm">${options}</select>
                <label class="block text-xs font-bold text-slate-700">อายุไฟล์</label><select id="ac-package" class="swal2-input !m-0 !w-full !text-sm"><option value="1">1 วัน</option><option value="7">7 วัน</option><option value="15">15 วัน</option><option value="30" selected>30 วัน</option></select>
                <label class="block text-xs font-bold text-slate-700">ชื่อไฟล์ (ไม่บังคับ)</label><input id="ac-name" class="swal2-input !m-0 !w-full !text-sm" placeholder="เช่น มือถือคุณลูกค้า"><p class="text-[10px] text-slate-400 mt-0.5 text-left">💡 ระบบจะใส่วันที่และเวลาหมดอายุต่อท้ายชื่อไฟล์ให้อัตโนมัติ</p>
                <div id="ac-ssh" class="hidden space-y-2"><input id="ac-user" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH username"><input id="ac-pass" type="password" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH password"></div>
            </div>`;
            const result = await Swal.fire({ title: 'สร้างไฟล์ให้ลูกค้า', html, confirmButtonText: 'สร้างไฟล์', cancelButtonText: 'ยกเลิก', showCancelButton: true, focusConfirm: false, width: 520,
                didOpen: () => { const s = document.getElementById('ac-server'); const sync = () => document.getElementById('ac-ssh').classList.toggle('hidden', !['ssh_script','udp_custom'].includes(s.selectedOptions[0].dataset.type)); s.addEventListener('change', sync); sync(); },
                preConfirm: () => { const s = document.getElementById('ac-server'); const ssh = !['ssh_script','udp_custom'].includes(s.selectedOptions[0].dataset.type) ? {} : { ssh_user: document.getElementById('ac-user').value.trim(), ssh_pass: document.getElementById('ac-pass').value.trim() }; if (ssh.ssh_user === '' || ssh.ssh_pass === '') { if (ssh.ssh_user !== undefined) { Swal.showValidationMessage('กรุณากรอก SSH username และ password'); return false; } } return { server_id: s.value, package: document.getElementById('ac-package').value, custom_name: document.getElementById('ac-name').value.trim(), ...ssh }; }
            });
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'กำลังสร้างไฟล์...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try { const res = await fetch('api/admin_create_vpn.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ target_user_id: target.id, ...result.value }) }); const data = await res.json();
                if (data.status !== 'success') throw new Error(data.message || 'สร้างไฟล์ไม่สำเร็จ');
                await Swal.fire('สำเร็จ', data.message || 'สร้างไฟล์ให้ลูกค้าเรียบร้อยแล้ว', 'success'); viewUserVPNs(target.id, target.username);
            } catch (e) { Swal.fire('สร้างไฟล์ไม่สำเร็จ', e.message, 'error'); }
        }

        async function adminMoveVpn(id) {
            const vpn = window.adminVpnConfigs?.[String(id)];
            if (!vpn) return Swal.fire('ผิดพลาด', 'ไม่พบข้อมูลไฟล์นี้', 'error');

            Swal.fire({ title: 'กำลังตรวจสอบเซิร์ฟเวอร์ปลายทาง...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_move_options', config_id: id })
                });
                const data = await res.json();
                Swal.close();
                if (data.status !== 'success') throw new Error(data.message || 'ไม่สามารถเตรียมข้อมูลการย้ายได้');

                const source = data.data?.source || {};
                const serverOptions = Array.isArray(data.data?.servers) ? data.data.servers : [];
                const movableOptions = serverOptions.filter(server => server.can_move);
                if (!movableOptions.length) {
                    return Swal.fire('ยังย้ายไม่ได้', serverOptions[0]?.reason || 'ยังไม่มีเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน', 'warning');
                }

                const firstMovableId = movableOptions[0].id;
                const optionsHtml = serverOptions.map(server => {
                    const preview = server.preview || {};
                    const suffix = server.can_move
                        ? ` · เหลือ ${Number(preview.converted_days || 0).toFixed(2)} วัน`
                        : ` · ${server.reason || 'ไม่พร้อมใช้งาน'}`;
                    const selected = server.can_move && server.id === firstMovableId ? ' selected' : '';
                    const disabled = server.can_move ? '' : ' disabled';
                    return `<option value="${server.id}"${selected}${disabled}>${escapeAdminHtml(server.name)} · ${server.is_ssh ? 'SSH' : '3x-ui'}${escapeAdminHtml(suffix)}</option>`;
                }).join('');

                const html = `<div class="text-left space-y-3">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0"><p class="text-[10px] font-bold uppercase text-slate-400">ไฟล์ปัจจุบัน</p><p class="mt-1 truncate text-sm font-bold text-slate-900">${escapeAdminHtml(source.server_name || vpn.server_name || '-')}</p></div>
                            <span class="shrink-0 rounded-full bg-pink-50 px-2 py-1 text-[10px] font-bold text-pink-600">${escapeAdminHtml(source.package_name || vpn.package_name || '-')}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">ราคาขายลูกค้า</span><b class="mt-0.5 block text-slate-800">฿${Number(source.customer_price ?? 0).toFixed(2)}</b></div>
                            <div class="rounded-xl bg-white p-2.5"><span class="block text-slate-400">เหลือเวลา</span><b class="mt-0.5 block text-slate-800">${Number(source.remaining_days || 0).toFixed(2)} วัน</b></div>
                        </div>
                    </div>
                    <label class="block text-xs font-bold text-slate-700">เลือกเซิร์ฟเวอร์ปลายทาง</label>
                    <select id="move-server" class="swal2-input !m-0 !w-full !text-sm">${optionsHtml}</select>
                    <div id="move-preview" class="rounded-2xl border border-purple-100 bg-purple-50 p-3 text-xs font-bold text-purple-700"></div>
                    <div id="move-ssh-credentials" class="hidden space-y-2 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                        <p id="move-ssh-help" class="text-[11px] font-bold text-amber-700"></p>
                        <input id="move-ssh-user" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH username ใหม่ (เว้นว่างเพื่อใช้ชื่อเดิม)">
                        <input id="move-ssh-pass" type="password" class="swal2-input !m-0 !w-full !text-sm" placeholder="SSH password ใหม่ (เว้นว่างเพื่อใช้รหัสเดิม)">
                    </div>
                    <p class="rounded-xl bg-emerald-50 px-3 py-2 text-[10px] font-bold text-emerald-700">✅ ระบบจะสร้างไฟล์ใหม่ก่อน แล้วลบไฟล์เดิม · ไม่หักเงินลูกค้า</p>
                </div>`;

                const result = await Swal.fire({
                    title: '🔄 ย้ายเซิร์ฟเวอร์', html, width: 560,
                    confirmButtonText: 'ยืนยันการย้าย', cancelButtonText: 'ยกเลิก',
                    showCancelButton: true, focusConfirm: false,
                    didOpen: () => {
                    const serverSelect = document.getElementById('move-server');
                    const previewEl = document.getElementById('move-preview');
                    const credentialBox = document.getElementById('move-ssh-credentials');
                    const helpEl = document.getElementById('move-ssh-help');
                    const sync = () => {
                        const selected = serverOptions.find(server => String(server.id) === String(serverSelect.value));
                        if (!selected) return;
                        const preview = selected.preview || {};
                        previewEl.innerHTML = selected.can_move
                            ? `📊 ${escapeAdminHtml(preview.calculation || 'คงเวลาที่เหลือเดิม')}<br><span class="font-normal">${Number(preview.remaining_days || 0).toFixed(2)} วัน → ${Number(preview.converted_days || 0).toFixed(2)} วัน · หมดอายุ ${escapeAdminHtml(preview.new_expiry_time || '-')}</span>`
                            : `⚠️ ${escapeAdminHtml(selected.reason || 'เซิร์ฟเวอร์นี้ยังไม่พร้อมย้าย')}`;
                        credentialBox.classList.toggle('hidden', !selected.is_ssh);
                        helpEl.innerText = selected.requires_ssh_credentials
                            ? 'ปลายทางเป็น SSH กรุณากรอก Username และ Password ใหม่'
                            : 'ปลายทางเป็น SSH · เว้นว่างได้ ระบบจะใช้บัญชีเดิมถ้าใช้ได้';
                    };
                    serverSelect.addEventListener('change', sync);
                    sync();
                },
                    preConfirm: () => {
                        const selected = serverOptions.find(server => String(server.id) === String(document.getElementById('move-server').value));
                        if (!selected || !selected.can_move) { Swal.showValidationMessage('กรุณาเลือกเซิร์ฟเวอร์ปลายทางที่พร้อมใช้งาน'); return false; }
                        const sshUser = document.getElementById('move-ssh-user')?.value.trim() || '';
                        const sshPass = document.getElementById('move-ssh-pass')?.value.trim() || '';
                        if (selected.requires_ssh_credentials && (sshUser === '' || sshPass === '')) {
                            Swal.showValidationMessage('กรุณากรอก SSH username และ password ของปลายทาง'); return false;
                        }
                        return { target_server_id: Number(selected.id), new_ssh_user: sshUser, new_ssh_pass: sshPass };
                    }
                });
                if (!result.isConfirmed) return;
                processAdminAction('admin_move_vpn', { config_id: id, ...result.value });
            } catch (e) {
                Swal.close();
                Swal.fire('ย้ายไม่สำเร็จ', e.message || 'ไม่สามารถเตรียมข้อมูลการย้ายได้', 'error');
            }
        }

        async function adminRenewVpn(id) {
            const username = window.adminCreateTarget?.username || '';
            const { value: days } = await Swal.fire({
                title: 'ต่ออายุฟรี', text: `เพิ่มวันใช้งานให้คุณ ${username}`,
                input: 'number', inputValue: 30, inputAttributes: { min: 1, max: 3650, step: 1 },
                inputValidator: value => (!value || Number(value) < 1 || Number(value) > 3650) ? 'กรุณาระบุ 1–3650 วัน' : undefined,
                showCancelButton: true
            });
            if (days) {
                processAdminAction('admin_renew_vpn', { config_id: id, days: days });
            }
        }

        async function adminRefundVpn(id) {
            Swal.fire({ title: 'กำลังคำนวณยอดคืน...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch('api/admin_manage.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_refund_preview', config_id: id })
                });
                const result = await response.json();
                Swal.close();
                if (result.status !== 'success') throw new Error(result.message || 'คำนวณยอดคืนไม่สำเร็จ');

                const data = result.data || {};
                const preview = data.preview || {};
                const amount = Number(preview.refundable_amount || 0);
                if (amount < 0.01) return Swal.fire('ไม่มีมูลค่าเงินคืน', preview.calculation || 'ไฟล์นี้ไม่สามารถคืนยอดได้', 'info');

                const confirm = await Swal.fire({
                    title: '💸 เปลี่ยนไฟล์เป็นยอดเงิน?',
                    html: `<div class="text-left rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm">
                        <p class="font-bold text-slate-900">${escapeAdminHtml(data.owner_username || '-')}</p>
                        <p class="mt-1 text-xs text-slate-500">${escapeAdminHtml(data.server_name || '-')} · ${escapeAdminHtml(data.package_name || '-')}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl bg-white p-3"><span class="block text-slate-400">เหลือเวลา</span><b class="mt-1 block text-slate-800">${Number(preview.remaining_days || 0).toFixed(2)} วัน</b></div>
                            <div class="rounded-xl bg-white p-3"><span class="block text-slate-400">ยอดที่จะคืน</span><b class="mt-1 block text-amber-700">฿${amount.toFixed(2)}</b></div>
                        </div>
                        <p class="mt-3 text-[10px] font-bold text-amber-700">${escapeAdminHtml(preview.calculation || '')}</p>
                    </div><p class="mt-3 text-[11px] text-slate-500">ระบบจะลบไฟล์จากเซิร์ฟเวอร์ก่อน แล้วจึงคืนยอดเข้ากระเป๋า</p>`,
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันคืนยอด',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#d97706',
                    focusConfirm: false
                });
                if (confirm.isConfirmed) processAdminAction('admin_refund_vpn', { config_id: id });
            } catch (e) {
                Swal.close();
                Swal.fire('คืนยอดไม่สำเร็จ', e.message || 'ไม่สามารถติดต่อระบบได้', 'error');
            }
        }

        async function adminDeleteVpn(id) {
            const confirm = await Swal.fire({ title: 'ลบไฟล์นี้?', text: 'ลบจากทั้งระบบและฐานข้อมูล', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' });
            if (confirm.isConfirmed) processAdminAction('admin_delete_vpn', { config_id: id });
        }

        async function cleanupExpired() {
            const confirm = await Swal.fire({ title: 'ล้างไฟล์ขยะ?', text: 'ลบไฟล์ที่หมดอายุเกิน 7 วันทั้งหมด', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' });
            if (confirm.isConfirmed) processAdminAction('cleanup_expired', {});
        }

        async function editBalance(userId, username, current) {
            const { value: balance } = await Swal.fire({ title: `ยอดเงิน ${username}`, input: 'number', inputValue: current, showCancelButton: true });
            if (balance !== undefined) processAdminAction('update_balance', { target_id: userId, new_balance: balance });
        }

        async function changePassword(userId, username) {
            const { value: pass } = await Swal.fire({ title: `รหัสผ่านใหม่ ${username}`, input: 'password', showCancelButton: true });
            if (pass) processAdminAction('change_password', { target_id: userId, new_password: pass });
        }

        async function processAdminAction(action, payload) {
            Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            payload.action = action;
            try {
                const res = await fetch('api/admin_manage.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    Swal.fire('สำเร็จ!', data.message, 'success').then(() => refreshData());
                    closeVpnListModal();
                } else { Swal.fire('ผิดพลาด!', data.message, 'error'); }
            } catch(e) { Swal.fire('Error', 'การเชื่อมต่อมีปัญหา', 'error'); }
        }

        function escapeAdminHtml(value) {
            const node = document.createElement('div');
            node.textContent = String(value ?? '');
            return node.innerHTML;
        }

        function formatAdminConfig(vpn) {
            const configLink = String(vpn.config_link ?? '');
            if (vpn.is_ssh !== true) return configLink;

            try {
                const saved = JSON.parse(configLink);
                if (saved && typeof saved === 'object') {
                    const blocks = [];
                    if (saved.raw) blocks.push(String(saved.raw));
                    if (saved.npv) blocks.push('NPV:\n' + String(saved.npv));
                    if (saved.netmod) blocks.push('NetMod:\n' + String(saved.netmod));
                    if (blocks.length) return blocks.join('\n\n');
                }
            } catch (e) {}

            return configLink;
        }

        function getAdminSshParts(vpn) {
            const configLink = String(vpn.config_link ?? '');
            let saved = {};
            try {
                const parsed = JSON.parse(configLink);
                if (parsed && typeof parsed === 'object') saved = parsed;
            } catch (e) {}
            const raw = String(saved.raw || configLink);
            const readLine = label => {
                const match = raw.match(new RegExp('(?:^|\\n)' + label + ':\\s*([^\\r\\n]+)', 'i'));
                return match ? match[1].trim() : '';
            };
            const variants = (value, label) => {
                if (Array.isArray(value)) return value.map((item, index) => ({
                    name: String(item?.name || item?.template_name || `${label} ${index + 1}`),
                    config: String(item?.config || item?.value || '')
                })).filter(item => item.config);
                if (typeof value === 'string' && value.trim()) return [{ name: `${label} 1`, config: value.trim() }];
                return [];
            };
            return {
                npv: variants(saved.npv, 'NPV Tunnel'),
                netmod: variants(saved.netmod, 'NetMod'),
                username: readLine('Username') || String(vpn.uuid || ''),
                password: readLine('Password')
            };
        }

        function renderAdminConfigVariants(listId, countId, variants, label, color) {
            const list = document.getElementById(listId);
            const count = document.getElementById(countId);
            if (!list) return;
            const copyClass = color === 'orange' ? 'bg-orange-50 text-orange-700' : 'bg-emerald-50 text-emerald-700';
            if (count) count.innerText = variants.length ? `(${variants.length} แบบ)` : '';
            list.innerHTML = variants.length
                ? variants.map((item, index) => {
                    const fieldId = `${listId}-${index}`;
                    return `<div class="rounded-lg border border-white/80 bg-white p-2.5 shadow-sm"><div class="mb-1.5 flex items-center justify-between gap-2"><span class="min-w-0 truncate text-[10px] font-bold text-slate-700">${escapeAdminHtml(item.name || `${label} ${index + 1}`)}</span><button onclick="copyAdminField('${fieldId}', '${label}')" class="shrink-0 rounded-md ${copyClass} px-2 py-1 text-[10px] font-bold">📋 คัดลอก</button></div><textarea id="${fieldId}" readonly rows="3" class="w-full rounded-lg bg-slate-50 p-2 text-[10px] text-slate-700 font-mono outline-none">${escapeAdminHtml(item.config)}</textarea></div>`;
                }).join('')
                : `<p class="rounded-lg bg-white/70 p-3 text-[10px] font-semibold text-slate-400">ไม่มีแม่แบบ ${label} สำหรับไฟล์นี้</p>`;
        }

        function showVpnDetailById(configId) {
            const vpn = window.adminVpnConfigs?.[String(configId)];
            if (!vpn) return Swal.fire('ผิดพลาด', 'ไม่พบข้อมูล Config นี้', 'error');

            const isSsh = vpn.is_ssh === true;
            document.getElementById('detailServerName').innerText = vpn.server_name || 'ไม่ระบุ';
            document.getElementById('detailUuid').innerText = vpn.uuid || 'ไม่ระบุ';
            document.getElementById('detailVpnPanel').classList.toggle('hidden', isSsh);
            document.getElementById('detailSshPanel').classList.toggle('hidden', !isSsh);
            if (isSsh) {
                const parts = getAdminSshParts(vpn);
                renderAdminConfigVariants('detailNpvList', 'detailNpvCount', parts.npv, 'NPV Tunnel', 'emerald');
                renderAdminConfigVariants('detailNetmodList', 'detailNetmodCount', parts.netmod, 'NetMod', 'orange');
                document.getElementById('detailSshUser').value = parts.username || 'ไม่พบ Username';
                document.getElementById('detailSshPass').value = parts.password || 'ไม่พบ Password';
            } else {
                document.getElementById('detailConfigLabel').innerText = 'ลิงก์ VPN สำหรับลูกค้า';
                document.getElementById('detailConfig').value = formatAdminConfig(vpn);
            }
            document.getElementById('vpnDetailModal').classList.remove('hidden');
            document.getElementById('vpnDetailModal').classList.add('flex');
            setTimeout(() => { document.getElementById('vpnDetailModal').classList.remove('opacity-0'); document.getElementById('vpnDetailContent').classList.remove('scale-95'); }, 10);
        }

        function closeVpnListModal() {
            document.getElementById('vpnListModal').classList.add('opacity-0');
            document.getElementById('vpnListContent').classList.add('scale-95');
            setTimeout(() => document.getElementById('vpnListModal').classList.add('hidden'), 300);
        }

        function closeVpnDetailModal() {
            document.getElementById('vpnDetailModal').classList.add('opacity-0');
            document.getElementById('vpnDetailContent').classList.add('scale-95');
            setTimeout(() => document.getElementById('vpnDetailModal').classList.add('hidden'), 300);
        }

        async function copyAdminConfig() {
            const c = document.getElementById('detailConfig');
            try {
                await navigator.clipboard.writeText(c.value);
            } catch (e) {
                c.focus();
                c.select();
                document.execCommand('copy');
            }
            Toast.fire({ icon: 'success', title: 'คัดลอกแล้ว!' });
        }

        async function copyAdminField(elementId, label) {
            const field = document.getElementById(elementId);
            const value = String(field?.value || '').trim();
            if (!value || value.startsWith('ไม่มี ') || value.startsWith('ไม่พบ ')) {
                return Swal.fire({ icon: 'info', title: 'ไม่มีข้อมูล', text: `${label} ของไฟล์นี้ยังไม่มีข้อมูล` });
            }
            try {
                await navigator.clipboard.writeText(value);
            } catch (e) {
                field.focus();
                field.select();
                document.execCommand('copy');
            }
            Toast.fire({ icon: 'success', title: `คัดลอก ${label} แล้ว!` });
        }

        async function promptChangeAdminPassword() {
            const { value: formValues } = await Swal.fire({
                title: '🔐 เปลี่ยนรหัสผ่านผู้ดูแลระบบ',
                html: `
                    <div class="text-left text-sm space-y-3 pt-2">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start gap-2 leading-relaxed">
                            <span class="text-base leading-none">⚠️</span>
                            <div>กำหนดรหัสผ่านใหม่และรหัส PIN ของผู้ดูแลระบบเพื่อความปลอดภัยสูงสุด</div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">รหัสผ่านเดิม (Current Password)</label>
                            <input id="swalOldPass" type="password" placeholder="เช่น admin123 (เว้นว่างได้ถ้าใช้รหัสเริ่มต้น)" class="swal2-input !m-0 !w-full !text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">รหัสผ่านใหม่ (New Password) <span class="text-rose-500">*</span></label>
                            <input id="swalNewPass" type="password" placeholder="อย่างน้อย 6 ตัวอักษร" class="swal2-input !m-0 !w-full !text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ยืนยันรหัสผ่านใหม่ (Confirm Password) <span class="text-rose-500">*</span></label>
                            <input id="swalConfirmPass" type="password" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" class="swal2-input !m-0 !w-full !text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">รหัส PIN แอดมินใหม่ (PIN Code 6 หลัก)</label>
                            <input id="swalNewPin" type="text" maxlength="6" placeholder="เช่น 123456 (เว้นว่างได้หากไม่เปลี่ยน)" class="swal2-input !m-0 !w-full !text-sm">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึกรหัสผ่านใหม่ 🚀',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                preConfirm: async () => {
                    const oldPass = document.getElementById('swalOldPass').value;
                    const newPass = document.getElementById('swalNewPass').value;
                    const confirmPass = document.getElementById('swalConfirmPass').value;
                    const newPin = document.getElementById('swalNewPin').value.trim();

                    if (!newPass) {
                        Swal.showValidationMessage('กรุณากรอกรหัสผ่านใหม่');
                        return false;
                    }
                    if (newPass.length < 6) {
                        Swal.showValidationMessage('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                        return false;
                    }
                    if (newPass === 'admin123' || newPass === 'reseller123') {
                        Swal.showValidationMessage('กรุณาตั้งรหัสผ่านใหม่ที่ไม่ใช่รหัสเริ่มต้น');
                        return false;
                    }
                    if (newPass !== confirmPass) {
                        Swal.showValidationMessage('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน');
                        return false;
                    }
                    if (newPin && !/^\d{4,6}$/.test(newPin)) {
                        Swal.showValidationMessage('รหัส PIN ต้องเป็นตัวเลข 4 - 6 หลัก');
                        return false;
                    }

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
                        const resData = await res.json();
                        if (resData.status !== 'success') {
                            Swal.showValidationMessage(resData.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้');
                            return false;
                        }
                        return resData;
                    } catch (err) {
                        Swal.showValidationMessage('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                        return false;
                    }
                }
            });

            if (formValues && formValues.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: formValues.message || 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
                    timer: 2000,
                    showConfirmButton: false
                });
                const container = document.getElementById('defaultPassWarningContainer');
                if (container) container.innerHTML = '';
            }
        }

        async function refreshData(btn) {
            const icon = btn ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;
            try {
                await authReady;
                await Promise.all([loadStats(), loadUsers()]);
                if (btn) {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชข้อมูลผู้ใช้และสถิติแล้ว' });
                }
            } catch (e) {
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        refreshData();
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
