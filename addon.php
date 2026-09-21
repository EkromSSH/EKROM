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
    <link rel="stylesheet" href="mobile-fix.css">
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

    <!-- Mobile Drawer -->
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
                <a href="addon.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-500 transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
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
        <div class="max-w-3xl mx-auto">
            <header class="flex justify-between items-center mb-6 mt-2 md:mt-0">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">โปรเสริม 📦</h1>
                    <p class="text-gray-500 mt-1 text-xs md:text-sm">คู่มือสมัครโปรเสริมของแต่ละค่าย</p>
                </div>
                
                <div class="hidden md:flex items-center gap-4">
                    <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl flex items-center gap-2.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm group">
                        <div class="w-7 h-7 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-sm">💰</div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider leading-tight">ยอดเงินคงเหลือ</span>
                            <span class="font-bold text-emerald-700 leading-tight text-xs md:text-sm">฿<span id="userBalanceDesk">0.00</span></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- กล่องคำอธิบายสำคัญเหมือนเว็บ BANG -->
            <div class="mt-3 p-3.5 rounded-2xl bg-pink-50 border border-pink-200 text-pink-700 text-xs md:text-sm shadow-sm leading-relaxed mb-6">
                📌 <b>สมัครโปรเสริมก่อนสั่งซื้อเซิร์ฟเวอร์</b> — ถึงจะเชื่อมต่อไฟล์ในแอป V2rayNG, V2BOX, NPV Tunnel และอื่นๆ ได้<br>
                📱 รองรับทั้ง <b>Android</b> และ <b>iOS</b>
            </div>

            <!-- เมนูเลือกค่ายด้านบน (สร้างอัตโนมัติเฉพาะค่ายที่มีโปรในระบบ) -->
            <div id="carrierTabsContainer" class="mt-4 mb-6"></div>

            <div id="addonsContainer" class="pb-10">
                <div class="text-center py-10 text-gray-400">กำลังโหลดแพ็กเกจเสริม... ⏳</div>
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

        const netConfig = {
            'ais': {
                name: 'AIS',
                title: '📶 AIS',
                logo: 'images/carriers/ais.svg',
                tabId: 'tab-ais',
                grad: 'from-emerald-500 to-emerald-400',
                text: 'text-emerald-50',
                activeTabClass: 'bg-emerald-50/70 border-2 border-emerald-500 shadow-md shadow-emerald-500/20 ring-2 ring-emerald-500/20 scale-[1.02] opacity-100',
                inactiveTabClass: 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'
            },
            'true': {
                name: 'True',
                title: '📶 True',
                logo: 'images/carriers/true.svg',
                tabId: 'tab-true',
                grad: 'from-amber-500 to-amber-400',
                text: 'text-amber-50',
                activeTabClass: 'bg-rose-50/70 border-2 border-red-500 shadow-md shadow-red-500/20 ring-2 ring-red-500/20 scale-[1.02] opacity-100',
                inactiveTabClass: 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'
            },
            'dtac': {
                name: 'Dtac',
                title: '📶 Dtac',
                logo: 'images/carriers/dtac.svg',
                tabId: 'tab-dtac',
                grad: 'from-fuchsia-500 to-fuchsia-400',
                text: 'text-fuchsia-50',
                activeTabClass: 'bg-sky-50/70 border-2 border-sky-500 shadow-md shadow-sky-500/20 ring-2 ring-sky-500/20 scale-[1.02] opacity-100',
                inactiveTabClass: 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'
            },
            'nt': {
                name: 'NT Mobile',
                title: '📶 NT Mobile',
                logo: 'images/carriers/nt.svg',
                tabId: 'tab-nt',
                grad: 'from-yellow-500 to-amber-400',
                text: 'text-amber-950 font-bold',
                activeTabClass: 'bg-amber-50/70 border-2 border-yellow-500 shadow-md shadow-yellow-500/20 ring-2 ring-yellow-500/20 scale-[1.02] opacity-100',
                inactiveTabClass: 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'
            }
        };

        function getNetMeta(netKey, rawName) {
            if (netConfig[netKey]) return netConfig[netKey];
            const name = rawName || netKey.toUpperCase();
            return {
                name: name,
                title: `📶 ${name}`,
                logo: null,
                tabId: 'tab-' + netKey,
                grad: 'from-slate-700 to-slate-600',
                text: 'text-slate-100',
                activeTabClass: 'bg-slate-50 border-2 border-slate-700 shadow-md shadow-slate-700/20 ring-2 ring-slate-700/20 scale-[1.02] opacity-100',
                inactiveTabClass: 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'
            };
        }

        // แผนผังสีธีมสำหรับการ์ดโปรเสริม (Theme Color Map)
        const themeColorMap = {
            'green':   { grad: 'from-emerald-500 to-emerald-400', hexGrad: 'linear-gradient(to right, #10b981, #34d399)', text: 'text-emerald-50', textHex: '#ecfdf5' },
            'emerald': { grad: 'from-emerald-500 to-emerald-400', hexGrad: 'linear-gradient(to right, #10b981, #34d399)', text: 'text-emerald-50', textHex: '#ecfdf5' },
            'orange':  { grad: 'from-amber-500 to-amber-400',     hexGrad: 'linear-gradient(to right, #f59e0b, #fbbf24)', text: 'text-amber-50', textHex: '#fffbeb' },
            'amber':   { grad: 'from-amber-500 to-amber-400',     hexGrad: 'linear-gradient(to right, #f59e0b, #fbbf24)', text: 'text-amber-50', textHex: '#fffbeb' },
            'purple':  { grad: 'from-fuchsia-500 to-fuchsia-400', hexGrad: 'linear-gradient(to right, #d946ef, #e879f9)', text: 'text-fuchsia-50', textHex: '#fdf4ff' },
            'violet':  { grad: 'from-purple-600 to-purple-400',   hexGrad: 'linear-gradient(to right, #9333ea, #c084fc)', text: 'text-purple-50', textHex: '#faf5ff' },
            'yellow':  { grad: 'from-yellow-500 to-amber-400',    hexGrad: 'linear-gradient(to right, #eab308, #fbbf24)', text: 'text-amber-950 font-bold', textHex: '#451a03' },
            'blue':    { grad: 'from-blue-600 to-sky-400',        hexGrad: 'linear-gradient(to right, #2563eb, #38bdf8)', text: 'text-blue-50', textHex: '#eff6ff' },
            'sky':     { grad: 'from-sky-500 to-cyan-400',        hexGrad: 'linear-gradient(to right, #0284c7, #22d3ee)', text: 'text-sky-50', textHex: '#f0f9ff' },
            'indigo':  { grad: 'from-indigo-600 to-indigo-400',   hexGrad: 'linear-gradient(to right, #4f46e5, #818cf8)', text: 'text-indigo-50', textHex: '#eef2ff' },
            'red':     { grad: 'from-rose-600 to-red-400',        hexGrad: 'linear-gradient(to right, #e11d48, #f87171)', text: 'text-rose-50', textHex: '#fff1f2' },
            'rose':    { grad: 'from-rose-600 to-red-400',        hexGrad: 'linear-gradient(to right, #e11d48, #f87171)', text: 'text-rose-50', textHex: '#fff1f2' },
            'pink':    { grad: 'from-pink-500 to-rose-400',       hexGrad: 'linear-gradient(to right, #ec4899, #fb7185)', text: 'text-pink-50', textHex: '#fdf2f8' },
            'cyan':    { grad: 'from-teal-500 to-cyan-400',       hexGrad: 'linear-gradient(to right, #14b8a6, #22d3ee)', text: 'text-teal-50', textHex: '#f0fdfa' },
            'teal':    { grad: 'from-teal-500 to-cyan-400',       hexGrad: 'linear-gradient(to right, #14b8a6, #22d3ee)', text: 'text-teal-50', textHex: '#f0fdfa' },
            'slate':   { grad: 'from-slate-700 to-slate-600',     hexGrad: 'linear-gradient(to right, #334155, #475569)', text: 'text-slate-100', textHex: '#f8fafc' }
        };

        // แผนผังสีพื้นหลังกล่องแจ้งเตือน (Warning Box Background Colors)
        function getWarningBgClass(colorKey) {
            const key = (colorKey || 'pink').toLowerCase();
            switch (key) {
                case 'red':
                    return 'bg-red-50 border-red-200 text-red-700';
                case 'orange':
                    return 'bg-orange-50 border-orange-200 text-orange-800';
                case 'yellow':
                case 'amber':
                    return 'bg-amber-50 border-amber-200 text-amber-800';
                case 'green':
                case 'emerald':
                    return 'bg-emerald-50 border-emerald-200 text-emerald-800';
                case 'blue':
                    return 'bg-blue-50 border-blue-200 text-blue-800';
                case 'purple':
                    return 'bg-purple-50 border-purple-200 text-purple-800';
                case 'cyan':
                case 'teal':
                    return 'bg-cyan-50 border-cyan-200 text-cyan-800';
                case 'gray':
                case 'slate':
                    return 'bg-slate-100 border-slate-200 text-slate-700';
                case 'pink':
                default:
                    return 'bg-pink-50 border-pink-200 text-pink-700';
            }
        }

        let currentActiveNet = '';
        let currentActiveNetsList = [];

        function showNet(net) {
            currentActiveNet = net;
            currentActiveNetsList.forEach(x => {
                const el = document.getElementById('net-' + x);
                if (el) {
                    if (x === net) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                }
                const tab = document.getElementById('tab-' + x);
                if (tab) {
                    const conf = getNetMeta(x);
                    const baseClass = 'h-12 sm:h-14 px-2 sm:px-3 py-2 rounded-2xl flex items-center justify-center transition-all duration-200 relative group cursor-pointer';
                    if (x === net) {
                        tab.className = `${baseClass} ${conf.activeTabClass || 'bg-white border-2 border-pink-500 shadow-md shadow-pink-500/20 ring-2 ring-pink-500/20 scale-[1.02] opacity-100'}`;
                    } else {
                        tab.className = `${baseClass} ${conf.inactiveTabClass || 'bg-white border-2 border-slate-200/80 opacity-60 hover:opacity-100 hover:border-slate-300 hover:bg-slate-50/50 shadow-sm'}`;
                    }
                }
            });
        }

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

        // ฟังก์ชันแปลง extra_html (ข้อความดิบวิธีที่ 2) ให้เป็นกล่องสวยอัตโนมัติเหมือนเว็บ BANG
        function autoExtra(txt) {
            if (!txt || !txt.trim()) return '';
            if (txt.trim().startsWith('<')) return txt;
            const lines = txt.trim().split(/\r?\n/);
            let out = '<div class="mt-4 p-4 bg-orange-50 rounded-2xl border border-orange-200 shadow-sm text-left">';
            let inList = false;

            const formatLineWithLinks = (s) => {
                let clean = escapeAddonHtml(s);
                // อนุญาตแท็กจัดรูปแบบข้อความที่ปลอดภัย เช่น <b>, <strong>, <i>, <em>, <u>, <mark>, <br>, <span>
                clean = clean.replace(/&lt;(\/?(?:b|strong|i|em|u|mark|br))&gt;/gi, '<$1>');
                clean = clean.replace(/&lt;span\s+class=&quot;([^&]*)&quot;&gt;/gi, '<span class="$1">');
                clean = clean.replace(/&lt;\/span&gt;/gi, '</span>');
                // รองรับรูปแบบ Markdown **ข้อความ**
                clean = clean.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                clean = clean.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener" class="text-pink-600 font-bold underline hover:text-pink-700">$1</a>');
                clean = clean.replace(/(^|[^"'>])(topping\.truemoney\.com[^\s<]*)/g, '$1<a href="https://$2" target="_blank" rel="noopener" class="text-pink-600 font-bold underline hover:text-pink-700">$2</a>');
                return clean;
            };

            for (let ln of lines) {
                ln = ln.trim();
                if (!ln) continue;
                if (/^[━─=_-]{3,}$/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    out += '<hr class="my-2.5 border-orange-200/80">';
                    continue;
                }
                if (/^(🧧|📌|📲|🔥|🛡|▶)/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    out += `<p class="text-orange-700 font-bold text-sm mb-1">${formatLineWithLinks(ln)}</p>`;
                    continue;
                }
                if (/^(💡|⚠️)/.test(ln)) {
                    if (inList) { out += '</ol>'; inList = false; }
                    const icon = ln.startsWith('💡') ? '💡' : '⚠️';
                    const clean = ln.replace(/^(💡|⚠️)\s*/, '');
                    out += `<div class="mt-2.5 p-2.5 bg-orange-100/80 rounded-xl text-orange-800 text-xs font-semibold leading-relaxed border border-orange-200/60 flex items-start gap-1.5"><span>${icon}</span><span>${formatLineWithLinks(clean)}</span></div>`;
                    continue;
                }
                if (/^\d+[\.\)]\s*/.test(ln)) {
                    if (!inList) {
                        out += '<ol class="text-gray-600 text-xs my-2 space-y-1.5 list-decimal list-inside font-medium leading-relaxed">';
                        inList = true;
                    }
                    const clean = ln.replace(/^\d+[\.\)]\s*/, '');
                    out += `<li>${formatLineWithLinks(clean)}</li>`;
                    continue;
                }
                if (inList) { out += '</ol>'; inList = false; }
                out += `<p class="text-gray-600 text-xs my-1 leading-relaxed">${formatLineWithLinks(ln)}</p>`;
            }
            if (inList) out += '</ol>';
            out += '</div>';
            return out;
        }

        function getNormalizedCodes(item) {
            const rows = Array.isArray(item && item.subscription_codes) ? item.subscription_codes : [];
            const normalized = rows.map((row, index) => ({
                name: String(row.name || row.label || row.code_name || `รหัสสมัคร ${index + 1}`),
                price: row.price ? String(row.price) : '',
                code: String(row.code || row.ussd_code || row.value || '').trim()
            })).filter(row => row.code);
            if (normalized.length) return normalized;

            const legacy = String(item && item.ussd_code || '').trim();
            return legacy ? [{ name: 'รหัสสมัครเดิม', price: '', code: legacy }] : [];
        }

        function renderAddonCard(item, meta) {
            const codes = getNormalizedCodes(item);
            const warnBgClass = getWarningBgClass(item.warning_bg);
            const themeKey = (item.theme_color || '').toLowerCase();
            const theme = themeColorMap[themeKey] || meta || themeColorMap['green'];

            // ส่วนรหัสสมัคร USSD พร้อมปุ่มโทรออกและคัดลอก (ปุ่มโทรไม่ต้องเอาออกมีไว้เหมือนเดิม)
            let ussdSectionHtml = '';
            if (codes.length > 0) {
                ussdSectionHtml = `
                <div class="mt-4">
                    <p class="text-gray-500 text-sm font-semibold mb-2">📲 รหัสสมัครโปรเสริม</p>
                    <div class="space-y-2">
                        ${codes.map((code, codeIndex) => {
                            const codeId = `addon_code_${item.id}_${codeIndex}`;
                            return `
                            <div class="flex flex-col bg-gray-50 rounded-xl p-3 border border-gray-100">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">${escapeAddonHtml(code.name)}</p>
                                    ${code.price ? `<p class="text-gray-400 text-xs mt-0.5">${escapeAddonHtml(code.price)}</p>` : ''}
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <input type="text" readonly value="${escapeAddonHtml(code.code)}" id="${codeId}" class="w-full min-w-0 bg-white border border-gray-200 rounded-lg px-2.5 py-2 text-sm font-bold text-slate-700 text-center outline-none focus:border-pink-400 transition-all">
                                    <button type="button" onclick="copyUssd(document.getElementById('${codeId}').value)" class="inline-flex items-center justify-center bg-white border border-gray-200 text-slate-700 hover:text-slate-900 px-3 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 active:scale-95 transition-all shrink-0 cursor-pointer" title="คัดลอกรหัส">📋</button>
                                    <a href="tel:${encodeURIComponent(code.code)}" class="inline-flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm hover:opacity-95 transition-all shrink-0" title="กดโทรออกเพื่อสมัคร">📞</a>
                                </div>
                            </div>`;
                        }).join('')}
                    </div>
                </div>`;
            }

            return `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5 transition-all">
                <!-- แถบหัวการ์ด Gradient (รองรับสีที่ปรับแต่งได้ตาม theme_color) -->
                <div class="bg-gradient-to-r ${theme.grad} px-5 py-3.5" style="background: ${theme.hexGrad || ''};">
                    <h2 class="font-bold text-white text-lg">${escapeAddonHtml(item.title)}</h2>
                    ${item.subtitle ? `<p class="${theme.text} text-xs mt-0.5" style="color: ${theme.textHex || ''};">${escapeAddonHtml(item.subtitle)}</p>` : ''}
                </div>

                <div class="p-5">
                    <!-- แถวราคาและ Badge เหมือนเว็บ BANG -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            ${item.price_label ? `<p class="text-gray-500 text-xs font-medium">${escapeAddonHtml(item.price_label)}</p>` : ''}
                            <p class="text-2xl font-black text-pink-600">฿${parseFloat(item.price)}<span class="text-xs font-semibold text-gray-400"> ${escapeAddonHtml(item.price_per || '/ 30 วัน')}</span></p>
                        </div>
                        ${item.badge ? `<span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-200">${escapeAddonHtml(item.badge)}</span>` : ''}
                    </div>

                    <!-- ค่าบริการหักจากซิมของเว็บ EKROM (คงไว้ตามคำขอ) -->
                    <div class="bg-slate-50 rounded-2xl p-3 md:p-4 mb-4 border border-gray-100 flex items-center justify-between gap-3 relative z-10">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">ค่าบริการ</p>
                            <p class="text-[10px] text-gray-500 font-semibold">หักจากซิม</p>
                        </div>
                        <p class="text-2xl md:text-3xl font-bold text-slate-900 whitespace-nowrap">${parseFloat(item.price)} <span class="text-sm md:text-base text-gray-500 font-normal">บาท</span></p>
                    </div>

                    <!-- ข้อความอธิบายรายละเอียดแพ็กเกจเหมือนเว็บ BANG -->
                    ${item.desc_html ? `<div class="mt-4 bg-gray-50 rounded-xl p-4 text-sm space-y-1.5 text-gray-700 leading-relaxed border border-gray-100">${item.desc_html}</div>` : (item.description ? `<p class="text-xs md:text-sm text-gray-500 mt-3 mb-4 leading-relaxed">${escapeAddonHtml(item.description)}</p>` : '')}

                    <!-- กล่องข้อความแจ้งเตือนเหมือนเว็บ BANG (รองรับหลากสี) -->
                    ${item.warning ? `<div class="mt-3 p-3 rounded-xl ${warnBgClass} border text-xs font-medium leading-relaxed">${item.warning}</div>` : ''}

                    <!-- กล่องข้อมูลพิเศษเพิ่มเติม (เช่น วิธีที่ 2 สมัครผ่าน TrueMoney) เหมือนเว็บ BANG -->
                    ${item.extra_html ? autoExtra(item.extra_html) : ''}

                    <!-- รายการรหัส USSD + ปุ่มโทรออก -->
                    ${ussdSectionHtml}
                </div>
            </div>`;
        }

        async function loadAddons() {
            const tabsContainer = document.getElementById('carrierTabsContainer');
            const container = document.getElementById('addonsContainer');
            try {
                const res = await fetch('api/addons.php?action=get');
                const result = await res.json();
                
                if (result.status === 'success' && result.data && result.data.length > 0) {
                    const byNet = {};

                    result.data.forEach(cat => {
                        const rawCatName = (cat.name || '').trim();
                        const lowerCat = rawCatName.toLowerCase();
                        let defaultNetKey = 'other';
                        if (lowerCat.includes('ais')) defaultNetKey = 'ais';
                        else if (lowerCat.includes('true')) defaultNetKey = 'true';
                        else if (lowerCat.includes('dtac')) defaultNetKey = 'dtac';
                        else if (lowerCat.includes('nt')) defaultNetKey = 'nt';

                        (cat.items || []).forEach(item => {
                            const itemCarrier = (item.carrier || rawCatName).trim();
                            const lowerItem = itemCarrier.toLowerCase();
                            let netKey = defaultNetKey;
                            if (lowerItem.includes('ais')) netKey = 'ais';
                            else if (lowerItem.includes('true')) netKey = 'true';
                            else if (lowerItem.includes('dtac')) netKey = 'dtac';
                            else if (lowerItem.includes('nt')) netKey = 'nt';
                            else if (netKey === 'other') {
                                netKey = lowerItem.replace(/[^a-z0-9]/g, '') || 'other';
                            }

                            if (!byNet[netKey]) {
                                byNet[netKey] = {
                                    key: netKey,
                                    name: itemCarrier || (netConfig[netKey] ? netConfig[netKey].name : netKey),
                                    items: []
                                };
                            }
                            byNet[netKey].items.push(item);
                        });
                    });

                    // เรียงลำดับค่ายมาตรฐาน และคัดกรองเฉพาะค่ายที่มีโปรโมชั่นจริงในระบบ
                    const canonicalOrder = ['ais', 'true', 'dtac', 'nt'];
                    const activeNets = canonicalOrder.filter(k => byNet[k] && byNet[k].items.length > 0);
                    Object.keys(byNet).forEach(k => {
                        if (!canonicalOrder.includes(k) && byNet[k] && byNet[k].items.length > 0) {
                            activeNets.push(k);
                        }
                    });

                    // ถ้าไม่มีโปรของค่ายใดเลยในระบบ
                    if (activeNets.length === 0) {
                        if (tabsContainer) tabsContainer.innerHTML = '';
                        container.innerHTML = '<div class="text-center text-gray-400 py-10 font-bold bg-white rounded-3xl border border-gray-100">ยังไม่มีแพ็กเกจเสริมในระบบ</div>';
                        return;
                    }

                    currentActiveNetsList = activeNets;
                    if (!activeNets.includes(currentActiveNet)) {
                        currentActiveNet = activeNets[0];
                    }

                    // ปรับขนาด Grid ปุ่มเลือกค่ายตามจำนวนค่ายที่มีโปรจริง
                    let gridCols = 'grid-cols-2 sm:grid-cols-4';
                    if (activeNets.length === 1) gridCols = 'grid-cols-1 max-w-xs mx-auto';
                    else if (activeNets.length === 2) gridCols = 'grid-cols-2';
                    else if (activeNets.length === 3) gridCols = 'grid-cols-3';
                    else if (activeNets.length === 4) gridCols = 'grid-cols-2 sm:grid-cols-4';
                    else gridCols = 'grid-cols-2 sm:grid-cols-3 md:grid-cols-' + Math.min(activeNets.length, 5);

                    // สร้างแท็บเลือกค่ายเฉพาะค่ายที่มีโปร (แสดงโลโก้ค่ายทางการ ขนาดช่องเท่าเดิม สวยงามระดับมืออาชีพ)
                    if (tabsContainer) {
                        let tabsHtml = `<div class="grid ${gridCols} gap-2 sm:gap-3">`;
                        activeNets.forEach(netKey => {
                            const meta = getNetMeta(netKey, byNet[netKey].name);
                            const logoHtml = meta.logo
                                ? `<img src="${meta.logo}" alt="${escapeAddonHtml(meta.name)}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';" class="h-6 sm:h-7 max-h-6 sm:max-h-7 w-auto max-w-[80%] object-contain pointer-events-none transition-transform duration-200 group-hover:scale-105"><span class="font-bold text-xs sm:text-sm text-slate-700 hidden">${escapeAddonHtml(meta.title || meta.name)}</span>`
                                : `<span class="font-bold text-xs sm:text-sm truncate text-slate-700">${escapeAddonHtml(meta.title || meta.name)}</span>`;
                            const baseClass = 'h-12 sm:h-14 px-2 sm:px-3 py-2 rounded-2xl flex items-center justify-center transition-all duration-200 relative group cursor-pointer';
                            const initialClass = netKey === currentActiveNet ? meta.activeTabClass : meta.inactiveTabClass;
                            tabsHtml += `<button type="button" onclick="showNet('${netKey}')" id="tab-${netKey}" class="${baseClass} ${initialClass}" title="${escapeAddonHtml(meta.name)}">${logoHtml}</button>`;
                        });
                        tabsHtml += `</div>`;
                        tabsContainer.innerHTML = tabsHtml;
                    }

                    // สร้างกล่องเนื้อหาเฉพาะค่ายที่มีโปร
                    let fullHtml = '';
                    activeNets.forEach(netKey => {
                        const meta = getNetMeta(netKey, byNet[netKey].name);
                        const items = byNet[netKey].items;
                        const isHidden = netKey !== currentActiveNet ? 'hidden' : '';

                        const itemsHtml = items.map(item => renderAddonCard(item, meta)).join('');
                        fullHtml += `
                        <div id="net-${netKey}" class="${isHidden}">
                            ${itemsHtml}
                        </div>`;
                    });

                    container.innerHTML = fullHtml;
                    showNet(currentActiveNet);
                } else {
                    if (tabsContainer) tabsContainer.innerHTML = '';
                    container.innerHTML = '<div class="text-center text-gray-400 py-10 font-bold bg-white rounded-3xl border border-gray-100">ยังไม่มีแพ็กเกจเสริมในระบบ</div>';
                }
            } catch (e) {
                console.error(e);
                if (tabsContainer) tabsContainer.innerHTML = '';
                container.innerHTML = '<div class="text-center text-red-500 py-10">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</div>';
            }
        }

        function copyUssd(code) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => {
                    Toast.fire({ icon: 'success', title: 'คัดลอกรหัสสำเร็จ!' });
                }).catch(() => fallbackCopy(code));
            } else {
                fallbackCopy(code);
            }
        }

        function fallbackCopy(code) {
            const ta = document.createElement('textarea');
            ta.value = code;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0, 99999);
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
            Toast.fire({ icon: 'success', title: 'คัดลอกรหัสสำเร็จ!' });
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
