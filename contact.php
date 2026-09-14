<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ติดต่อผู้ดูแลระบบ - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .contact-card:hover { transform: translateY(-5px); }
        .faq-answer { display: none; }
        .faq-active .faq-answer { display: block; }
        .faq-active .faq-icon { transform: rotate(180deg); }
    
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
            <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
        </div>
        
        <div class="flex items-center gap-3">
            <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm">
                <span class="text-emerald-700 text-xs font-bold">฿<span id="userBalanceMob">0.00</span></span>
                <span class="bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold">+</span>
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
                    <span class="drawer-title font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ติดต่อผู้ดูแลระบบ 💬</h1>
                    <p class="text-gray-500 mt-1 text-xs md:text-sm">มีปัญหาการใช้งาน หรือต้องการสอบถามเพิ่มเติม ติดต่อเราได้เลย</p>
                </div>
                
                <div class="hidden md:flex items-center gap-4">
                    <div class="bg-pink-50 border border-pink-200 px-4 py-2 rounded-xl flex items-center gap-3 shadow-sm">
                        <div class="w-8 h-8 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center text-lg">🕒</div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-pink-600 font-bold uppercase tracking-wider mb-0.5">เวลาทำการ</span>
                            <span class="font-bold text-pink-700 leading-none text-sm">09:00 - 21:00 น.</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto mb-12">
                
                <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm transition-all duration-300 contact-card flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-[#1877F2]/10 text-[#1877F2] rounded-full flex items-center justify-center text-4xl mb-4">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Facebook Page</h3>
                    <p class="text-sm text-gray-500 mb-6 flex-grow">แจ้งปัญหา เติมเงิน หรือปรึกษาแพ็กเกจ ติดต่อแบบส่วนตัวแอดมินตอบไวที่สุด!</p>
                    <a href="https://www.facebook.com/share/14Zq7rmBjpS/" target="_blank" class="w-full bg-[#1877F2] text-white py-3 rounded-xl font-bold hover:bg-[#166FE5] transition-colors shadow-lg shadow-[#1877F2]/30">ทักแชทเพจเลย</a>
                </div>

                <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm transition-all duration-300 contact-card flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-[#00B2FF]/10 text-[#00B2FF] rounded-full flex items-center justify-center text-4xl mb-4">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26 6.559-6.963 3.13 3.259 5.889-3.259-6.56 6.963z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">กลุ่มพูดคุย & แจ้งปัญหา</h3>
                    <p class="text-sm text-gray-500 mb-6 flex-grow">เข้าร่วมกลุ่มแชท Messenger เพื่อพูดคุย แจ้งปัญหา และรับข่าวสารอัปเดตต่างๆ</p>
                    <a href="https://m.me/j/AbbNfkaIlLvGwfKr/?send_source=gc%3Acopy_invite_link_c" target="_blank" class="w-full bg-[#00B2FF] text-white py-3 rounded-xl font-bold hover:bg-[#0099E5] transition-colors shadow-lg shadow-[#00B2FF]/30">เข้าร่วมกลุ่มแชท</a>
                </div>

            </div>

            <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2"><span class="text-pink-600 text-2xl">💡</span> คำถามที่พบบ่อย (FAQ)</h2>
                
                <div class="space-y-4">
                    <div class="border border-gray-100 rounded-2xl p-2 bg-slate-50 transition-all cursor-pointer faq-item" onclick="this.classList.toggle('faq-active')">
                        <div class="flex justify-between items-center p-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base">Q: เติมเงินสลิปไม่ผ่าน ต้องทำอย่างไร?</h3>
                            <span class="faq-icon text-gray-400 transition-transform">▼</span>
                        </div>
                        <div class="faq-answer px-3 pb-3 text-sm text-gray-600 border-t border-gray-200 mt-2 pt-3">
                            A: กรุณาตรวจสอบว่าชื่อบัญชีผู้โอนตรงกับบัญชีที่ระบบกำหนดไว้หรือไม่ หากสแกนจ่ายถูกต้องแล้วยอดไม่เข้า รบกวนส่งสลิปให้แอดมินทาง Facebook เพจ เพื่อดำเนินการตรวจสอบและปรับยอดเงินให้ครับ
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-2xl p-2 bg-slate-50 transition-all cursor-pointer faq-item" onclick="this.classList.toggle('faq-active')">
                        <div class="flex justify-between items-center p-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base">Q: ลืมรหัสผ่าน ไม่สามารถเข้าสู่ระบบได้?</h3>
                            <span class="faq-icon text-gray-400 transition-transform">▼</span>
                        </div>
                        <div class="faq-answer px-3 pb-3 text-sm text-gray-600 border-t border-gray-200 mt-2 pt-3">
                            A: สามารถแคปหน้าจอ Username ของคุณ แล้วทักมาหาแอดมินทาง Facebook เพจ แอดมินจะทำการรีเซ็ตรหัสผ่านใหม่ให้ภายในเวลาทำการครับ
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-2xl p-2 bg-slate-50 transition-all cursor-pointer faq-item" onclick="this.classList.toggle('faq-active')">
                        <div class="flex justify-between items-center p-3">
                            <h3 class="font-bold text-slate-900 text-sm md:text-base">Q: VPN ไม่สามารถเชื่อมต่อได้?</h3>
                            <span class="faq-icon text-gray-400 transition-transform">▼</span>
                        </div>
                        <div class="faq-answer px-3 pb-3 text-sm text-gray-600 border-t border-gray-200 mt-2 pt-3">
                            A: เบื้องต้นให้ลองกดยกเลิกการเชื่อมต่อแล้วกดเชื่อมต่อใหม่อีก 2-3 ครั้ง หากยังไม่ได้ผลให้ลบไฟล์เดิมทิ้งและนำลิงก์จากระบบไปนำเข้าแอปใหม่อีกครั้ง หากยังพบปัญหาสามารถทักหาแอดมินได้ตลอดเวลาครับ
                        </div>
                    </div>
                </div>
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
                    if(document.getElementById('userBalanceDesk')) document.getElementById('userBalanceDesk').innerText = data.balance;
                    if(document.getElementById('userBalanceMob')) document.getElementById('userBalanceMob').innerText = data.balance;
                }
            } catch(e) { console.error('Failed to load user info'); }
        }

        loadUserInfo();

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
