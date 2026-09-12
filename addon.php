<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>โปรเสริม - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .addon-card:hover { transform: translateY(-5px); }
        .addon-card { min-width: 0; }
        @media (max-width: 767px) {
            .addon-card:hover { transform: none; }
        }
    
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');

        const Toast = Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 3000, timerProgressBar: true
        });
    </script>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="app-shell bg-slate-50 text-gray-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Navbar Mobile -->
    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-4 sm:px-6 py-3.5 flex justify-between items-center gap-3 z-40 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs shrink-0">EK</div>
            <span class="font-bold text-lg tracking-tight italic truncate">EKROM <span class="text-pink-600">SHOP</span></span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-2 sm:px-3 py-1.5 rounded-lg flex items-center gap-1.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm max-w-[145px] sm:max-w-none">
                <span class="text-emerald-700 text-xs font-bold">฿<span id="userBalanceMob">0.00</span></span>
                <span class="bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold">+</span>
            </div>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-pink-600 focus:outline-none">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- 🟢 เพิ่ม HTML ของ Mobile Menu กลับเข้ามา -->
    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-2">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Sidebar Desktop -->
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
            <a href="addon.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8 lg:p-12 overflow-y-auto">
        <div class="max-w-5xl mx-auto">
            <header class="flex justify-between items-center mb-8 md:mb-10 mt-2 md:mt-0">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">โปรเสริม (Add-ons) 📦</h1>
                    <p class="text-gray-500 mt-1 text-xs md:text-sm">สมัครแพ็กเกจเหล่านี้เพื่อใช้งานเป็นฐานในการเชื่อมต่อ VPN</p>
                </div>
                
                <div class="hidden md:flex items-center gap-4">
                    <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl flex items-center gap-3 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                        <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-lg">💰</div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">ยอดเงินคงเหลือ</span>
                            <span class="font-bold text-emerald-700 leading-none text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="bg-red-50 border border-red-200 p-4 md:p-6 rounded-2xl md:rounded-3xl mb-6 md:mb-10 flex items-start gap-3 md:gap-4 shadow-sm relative overflow-hidden">
                <div class="text-2xl md:text-4xl relative z-10 animate-bounce shrink-0">🚨</div>
                <div class="relative z-10">
                    <h3 class="font-bold text-red-900 text-sm md:text-base">ข้อบังคับสำคัญ</h3>
                    <p class="text-xs md:text-sm text-red-700 mt-1 leading-relaxed">
                        โปรเสริมด้านล่างนี้ <strong>จำเป็นต้องสมัคร</strong> เพื่อใช้เป็นฐานสำหรับการเชื่อมต่อเข้ากับระบบ VPN <br>
                        ❌ หากไม่สมัคร จะ<strong>ไม่สามารถเชื่อมต่อ VPN</strong> และไม่สามารถใช้งานอินเทอร์เน็ตได้ครับ!
                    </p>
                </div>
            </div>

            <div id="addonsContainer" class="pb-10">
                <div class="text-center py-10 text-gray-400">กำลังโหลดแพ็กเกจเสริม... ⏳</div>
            </div>

        </div>
    </main>

    <script>
        // 🟢 เพิ่มฟังก์ชันเมนูมือถือที่หายไปกลับเข้ามา
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

        const themeMap = {
            'green': { text: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100', dot: 'bg-emerald-500', btn: 'bg-emerald-500 hover:bg-emerald-600' },
            'red': { text: 'text-red-600', bg: 'bg-red-50', border: 'border-red-100', dot: 'bg-red-500', btn: 'bg-red-600 hover:bg-red-700' },
            'pink': { text: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100', dot: 'bg-pink-500', btn: 'bg-pink-600 hover:bg-pink-700' },
            'blue': { text: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100', dot: 'bg-pink-500', btn: 'bg-pink-600 hover:bg-pink-700' },
            'purple': { text: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100', dot: 'bg-purple-500', btn: 'bg-purple-600 hover:bg-purple-700' },
            'orange': { text: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-100', dot: 'bg-orange-500', btn: 'bg-orange-500 hover:bg-orange-600' }
        };

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if(data.status === 'success') {
                    if(document.getElementById('userBalanceDesk')) document.getElementById('userBalanceDesk').innerText = data.balance;
                    if(document.getElementById('userBalanceMob')) document.getElementById('userBalanceMob').innerText = data.balance;
                }
            } catch(e) {}
        }

        function escapeAddonHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function getAddonCodes(item) {
            const rows = Array.isArray(item && item.subscription_codes) ? item.subscription_codes : [];
            const normalized = rows.map((row, index) => ({
                name: String(row.code_name || row.name || `รหัสสมัคร ${index + 1}`),
                code: String(row.ussd_code || row.code || row.value || '').trim()
            })).filter(row => row.code);
            if (normalized.length) return normalized;

            const legacy = String(item && item.ussd_code || '').trim();
            return legacy ? [{ name: 'รหัสสมัครเดิม', code: legacy }] : [];
        }

        async function loadAddons() {
            const container = document.getElementById('addonsContainer');
            try {
                const res = await fetch('api/addons.php?action=get');
                const result = await res.json();
                
                if (result.status === 'success' && result.data.length > 0) {
                    let html = '';
                    result.data.forEach((cat, index) => {
                        const style = themeMap[cat.items[0].theme_color] || themeMap['green'];
                        const borderTop = index > 0 ? 'border-t border-gray-100 pt-6 md:pt-10' : '';
                        
                        html += `
                        <div class="mb-6 md:mb-10 ${borderTop}">
                            <h2 class="text-lg md:text-xl font-bold text-slate-900 mb-3 md:mb-5 flex items-center gap-2 md:gap-3">
                                <div class="w-2 h-6 ${style.dot} rounded-full shrink-0"></div> สำหรับผู้ใช้เครือข่าย ${cat.name}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
                        `;
                        
                        cat.items.forEach(item => {
                            const c = themeMap[item.theme_color] || themeMap['green'];
                            const shortLabel = item.title.match(/(\d+[A-Za-z]*)/) ? item.title.match(/(\d+[A-Za-z]*)/)[0] : 'โปร';
                            const codes = getAddonCodes(item);
                            const codeRowsHtml = codes.length ? codes.map((code, codeIndex) => {
                                const codeId = `addon_code_${item.id}_${codeIndex}`;
                                return `
                                    <div class="rounded-xl border border-gray-200 bg-white p-2.5">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <span class="text-[10px] font-bold text-gray-500 truncate">${escapeAddonHtml(code.name)}</span>
                                            <span class="text-[10px] text-gray-400 shrink-0">เลือกใช้รายการนี้</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="text" readonly value="${escapeAddonHtml(code.code)}" id="${codeId}" class="w-full min-w-0 bg-slate-50 border border-gray-200 rounded-lg px-2.5 py-2 text-sm font-bold text-slate-700 text-center outline-none focus:border-pink-400 transition-all">
                                            <button onclick="copyCode('${codeId}')" class="bg-slate-100 text-slate-600 p-2 rounded-lg hover:${c.bg} hover:${c.text} transition-all font-bold shrink-0" title="คัดลอกรหัส">📋</button>
                                            <a href="tel:${encodeURIComponent(code.code)}" class="${c.btn} text-white p-2 rounded-lg transition-all shadow-sm shrink-0" title="กดสมัคร">📞</a>
                                        </div>
                                    </div>`;
                            }).join('') : '<div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">ยังไม่ได้ตั้งค่าเบอร์/รหัสสมัคร</div>';

                            html += `
                            <div class="bg-white rounded-2xl md:rounded-[28px] p-4 md:p-5 border border-gray-100 shadow-sm hover:shadow-xl hover:border-${item.theme_color}-200 transition-all duration-300 addon-card flex flex-col h-full relative overflow-hidden">
                                ${item.theme_color === 'red' || item.theme_color === 'purple' ? `<div class="absolute -right-10 -top-10 w-32 h-32 ${c.dot} rounded-full blur-3xl opacity-10 pointer-events-none"></div>` : ''}
                                
                                <div class="flex items-start gap-3 relative z-10 min-w-0">
                                    <div class="w-12 h-12 md:w-14 md:h-14 ${c.bg} ${c.text} rounded-2xl flex items-center justify-center text-sm md:text-xl font-bold shadow-inner uppercase shrink-0">${shortLabel}</div>
                                    <div class="min-w-0 flex-1">
                                        <span class="inline-flex max-w-full ${c.bg} ${c.text} px-2.5 py-1 text-[9px] md:text-[10px] font-bold rounded-full uppercase border ${c.border} truncate">${item.duration_text}</span>
                                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mt-1 leading-snug break-words">${item.title}</h3>
                                    </div>
                                </div>
                                <p class="text-xs md:text-sm text-gray-500 mt-3 mb-4 leading-relaxed flex-grow relative z-10 break-words">${item.description}</p>
                                
                                <div class="bg-slate-50 rounded-2xl p-3 md:p-4 mb-4 border border-gray-100 flex items-center justify-between gap-3 relative z-10">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">ค่าบริการ</p>
                                        <p class="text-[10px] text-gray-500">หักจากซิม</p>
                                    </div>
                                    <p class="text-2xl md:text-3xl font-bold text-slate-900 whitespace-nowrap">${parseFloat(item.price)} <span class="text-sm md:text-base text-gray-500 font-normal">บาท</span></p>
                                </div>

                                <div class="mb-4 relative z-10">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <p class="text-xs font-bold text-slate-900">เบอร์/รหัสสมัคร</p>
                                        <span class="text-[10px] text-gray-400">มี ${codes.length} รายการ</span>
                                    </div>
                                    <div class="space-y-2">${codeRowsHtml}</div>
                                </div>
                            </div>`;
                        });
                        
                        html += `</div></div>`;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="text-center text-gray-400 py-10 font-bold bg-white rounded-3xl border border-gray-100">ยังไม่มีแพ็กเกจเสริมในระบบ</div>';
                }
            } catch (e) {
                container.innerHTML = '<div class="text-center text-red-500 py-10">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</div>';
            }
        }

        function copyCode(elementId) {
            const input = document.getElementById(elementId);
            if (!input) return;
            input.select();
            const copied = navigator.clipboard && navigator.clipboard.writeText
                ? navigator.clipboard.writeText(input.value)
                : Promise.reject(new Error('clipboard unavailable'));
            copied.then(() => Toast.fire({ icon: 'success', title: 'คัดลอกรหัสสำเร็จ!' }))
                .catch(() => {
                    document.execCommand('copy');
                    Toast.fire({ icon: 'success', title: 'คัดลอกรหัสสำเร็จ!' });
                });
        }

        loadUserInfo();
        loadAddons();

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
