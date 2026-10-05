<?php
require_once __DIR__ . '/api/db.php';
$siteSettings = get_site_settings();
$siteName = htmlspecialchars($siteSettings['site_name'] ?: 'EKROM');
$siteLogo = htmlspecialchars($siteSettings['site_logo'] ?? '');
$siteInitial = htmlspecialchars(mb_substr($siteSettings['site_name'] ?: 'EKROM', 0, 2));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>จัดการการเติมเงิน - <?= $siteName ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="admin-mobile.css?v=20260926_5">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        th, td { white-space: nowrap; } 
    
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
</head>
<body class="admin-shell bg-slate-50 text-gray-800 antialiased flex flex-col md:flex-row h-screen overflow-hidden">

    <div class="admin-mobile-nav md:hidden bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs overflow-hidden">
                <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
            </div>
            <span class="font-bold text-lg tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
        </div>
        <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 max-w-[85vw] h-full max-h-[100dvh] flex flex-col p-5 sm:p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl overflow-y-auto overscroll-contain">
            <div class="drawer-header flex justify-between items-center mb-6 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden">
                        <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
                    </div>
                    <span class="drawer-title font-bold text-xl tracking-tight text-white italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
            </div>
            <nav class="flex-1 min-h-0 overflow-y-auto space-y-1.5 pr-1 overscroll-contain">
                <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
                <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
                <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
                <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
                <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
                <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
                <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🧾 ประวัติการเติมเงิน</a>
                <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
                <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-2 sm:mt-4">🏠 กลับหน้าลูกค้า</a>
            </nav>
            <div class="drawer-footer shrink-0 mt-auto pt-4 border-t border-slate-700 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))]">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-2.5 sm:py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg overflow-hidden">
                <?php if (!empty($siteLogo)): ?><img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="w-full h-full object-cover"><?php else: ?><?= $siteInitial ?><?php endif; ?>
            </div>
            <span class="font-bold text-xl tracking-tight italic"><?= $siteName ?> <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-2">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าระบบ & ความปลอดภัย</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">ประวัติการเติมเงิน 🧾</h1>
            <p class="text-gray-500 mt-1 text-sm">ตรวจสอบและลบประวัติการเติมเงินของลูกค้าในระบบ</p>
        </header>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 bg-slate-50">
                <h2 class="text-base md:text-lg font-bold text-slate-900 flex items-center gap-2"><span class="text-emerald-600">💰</span> รายการเติมเงินทั้งหมด</h2>
                
                <div class="flex gap-2">
                    <div class="relative flex-grow md:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">🔍</span>
                        <input type="text" id="searchInput" onkeyup="filterTopups()" placeholder="ค้นหาชื่อ LINE, Username หรือ วันที่..." class="w-full bg-white border border-gray-200 pl-9 pr-4 py-2.5 rounded-xl text-xs md:text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition-all shadow-sm">
                    </div>
                    <button onclick="loadTopups(this)" class="text-emerald-600 font-bold text-xs md:text-sm bg-emerald-50 border border-emerald-100 px-4 py-2.5 rounded-xl hover:bg-emerald-600 hover:text-white transition-all whitespace-nowrap shadow-sm shrink-0 flex items-center justify-center gap-1.5">
                        <span class="refresh-icon inline-block">🔄</span> รีเฟรช
                    </button>
                </div>
            
            <div class="overflow-x-auto hide-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 text-[10px] md:text-xs uppercase tracking-wider border-b border-gray-200">
                            <th class="px-4 md:px-6 py-4 font-bold">#ID</th>
                            <th class="px-4 md:px-6 py-4 font-bold">วันที่ - เวลา</th>
                            <th class="px-4 md:px-6 py-4 font-bold">ลูกค้า / ชื่อ LINE / Username</th>
                            <th class="px-4 md:px-6 py-4 font-bold text-right">จำนวนเงิน</th>
                            <th class="px-4 md:px-6 py-4 font-bold text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="topupTableBody" class="text-xs md:text-sm divide-y divide-gray-100">
                        <tr><td colspan="5" class="text-center py-10 text-gray-400 font-bold">กำลังโหลดข้อมูล...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
        }

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

        async function loadTopups(btn) {
            const icon = btn ? btn.querySelector('.refresh-icon') : null;
            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;
            const tbody = document.getElementById('topupTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">กำลังโหลด... ⏳</td></tr>';
            try {
                const res = await fetch('api/admin_manage.php?action=get_topups', { cache: 'no-store' });
                const data = await res.json();
                
                if (data.status === 'success') {
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">ไม่มีประวัติการเติมเงิน</td></tr>';
                        return;
                    }
                    tbody.innerHTML = data.data.map(log => {
                        const isLine = Boolean(log.line_display_name || log.line_user_id || (log.username && log.username.startsWith('line_')));
                        const lineName = log.line_display_name ? escapeHtml(log.line_display_name) : '';
                        const uname = log.username ? escapeHtml(log.username) : '';
                        const picUrl = log.line_picture_url ? escapeHtml(log.line_picture_url) : '';

                        let userDisplay = '';
                        if (!log.username) {
                            userDisplay = '<span class="text-red-400 italic">(ผู้ใช้ถูกลบ)</span>';
                        } else if (isLine) {
                            userDisplay = `
                                <div class="flex items-center gap-2.5">
                                    <div class="relative shrink-0">
                                        ${picUrl ? `
                                            <img src="${picUrl}" alt="${lineName || uname}" class="w-8 h-8 rounded-full object-cover border border-emerald-400 shadow-xs" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('${lineName || 'LINE'}') + '&background=06c755&color=fff';">
                                        ` : `
                                            <div class="w-8 h-8 rounded-full bg-[#06c755] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                                💬
                                            </div>
                                        `}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5 flex-wrap">
                                            <span>${lineName || uname}</span>
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#06c755]/10 text-[#059b43] border border-[#06c755]/25">
                                                <svg class="w-2.5 h-2.5 fill-[#06c755]" viewBox="0 0 24 24"><path d="M24 10.304c0-5.369-5.383-9.738-12-9.738-6.616 0-12 4.369-12 9.738 0 4.814 4.269 8.846 10.019 9.589.391.084.922.258 1.057.592.122.303.079.778.039 1.085l-.171 1.027c-.053.303-.242 1.186 1.039.647 1.281-.54 6.911-4.069 9.428-6.967 1.739-1.907 2.589-3.844 2.589-5.971z"/></svg>
                                                LINE
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">Username: <span class="text-slate-600">${uname}</span></div>
                                    </div>
                                </div>
                            `;
                        } else {
                            userDisplay = `
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0 border border-slate-200">
                                        ${uname.charAt(0).toUpperCase()}
                                    </div>
                                    <span class="font-bold text-slate-900">${uname}</span>
                                </div>
                            `;
                        }

                        return `
                        <tr class="hover:bg-slate-50 transition-colors topup-row">
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-400">#${log.id}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-slate-600">${log.created_at}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4">${userDisplay}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-right font-bold text-emerald-600">+ ฿${parseFloat(log.amount).toFixed(2)}</td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <button onclick="deleteTopup(${log.id})" class="bg-red-50 text-red-600 p-1.5 md:p-2 rounded-lg hover:bg-red-100 text-[10px] md:text-xs font-bold transition-all shadow-sm">🗑️ ลบ</button>
                            </td>
                        </tr>`;
                    }).join('');
                    filterTopups();
                    if (btn) {
                        Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 }).fire({ icon: 'success', title: 'รีเฟรชประวัติการเติมเงินแล้ว' });
                    }
                } else {
                    Swal.fire('ผิดพลาด', data.message, 'error');
                }
            } catch(e) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-500">การเชื่อมต่อขัดข้อง</td></tr>';
            } finally {
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        function filterTopups() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.topup-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        async function deleteTopup(id) {
            const confirm = await Swal.fire({
                title: 'แน่ใจหรือไม่?',
                text: 'การลบประวัติจะทำให้ยอดสถิติรายได้ของแอดมินลดลง แต่จะไม่ดึงเงินออกจากกระเป๋าลูกค้านะครับ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                Swal.fire({ title: 'กำลังลบ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                try {
                    const res = await fetch('api/admin_manage.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_topup', log_id: id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ!', data.message, 'success').then(() => loadTopups());
                    } else {
                        Swal.fire('ผิดพลาด!', data.message, 'error');
                    }
                } catch(e) {
                    Swal.fire('ขัดข้อง', 'ระบบเชื่อมต่อมีปัญหา', 'error');
                }
            }
        }

        loadTopups();
    </script>

</body>
</html>
