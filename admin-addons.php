<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>จัดการโปรเสริม - EKROM Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="mobile-fix.css">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        th, td { white-space: nowrap; }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            const role = data.role || data.user?.role;
            if (data.status !== 'logged_in' || role !== 'admin') {
                window.location.href = 'login.php';
            }
        }).catch(() => { window.location.href = 'login.php'; });

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    </script>
</head>
<body class="admin-shell bg-slate-50 text-gray-800 antialiased flex flex-col md:flex-row h-screen overflow-hidden">

    <!-- Mobile Header -->
    <div class="admin-mobile-nav md:hidden bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-slate-900 w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="font-bold text-xl tracking-tight text-white italic">EKROM <span class="text-rose-500">ADMIN</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-gray-400 hover:text-white transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-1.5 overflow-y-auto">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
            <div class="mt-auto pt-4 border-t border-slate-700">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="w-72 bg-slate-900 text-white h-screen flex flex-col p-6 shrink-0 z-40 hidden md:flex">
        <div class="flex items-center gap-3 mb-8 cursor-pointer" onclick="window.location.href='admin-dash.php'">
            <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
            <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-rose-500">ADMIN</span></span>
        </div>
        <nav class="flex-grow space-y-1.5 overflow-y-auto">
            <a href="admin-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">👥 จัดการผู้ใช้งาน & สถิติ</a>
            <a href="admin-resellers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🤝 ยอดขายตัวแทน</a>
            <a href="admin-shops.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏢 จัดการร้านค้าเช่า (SaaS)</a>
            <a href="admin-servers.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">⚙️ ตั้งค่าเซิร์ฟเวอร์</a>
            <a href="admin-pricing.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🏷️ จัดการโซนราคา</a>
            <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">📑 จัดการหมวดหมู่</a>
            <a href="admin-addons.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold bg-slate-800 text-white transition-all border border-slate-700">📦 โปรเสริม</a>
            <a href="admin-topups.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🧾 ประวัติการเติมเงิน</a>
            <a href="admin-settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all">🔔 ตั้งค่าการแจ้งเตือน</a>
            <a href="buyer-dash.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-all mt-4">🏠 กลับหน้าลูกค้า</a>
        </nav>
        <div class="mt-auto pt-4 border-t border-slate-700">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-400 font-semibold hover:bg-slate-800 rounded-xl transition-all">🚪 ออกจากระบบ</button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-6 lg:p-10 overflow-y-auto">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 md:mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">จัดการโปรเน็ตเสริม (Addons) 📦</h1>
                <p class="text-gray-500 mt-1 text-sm">เพิ่มและแก้ไขแพ็กเกจโปรเน็ตแนะนำสำหรับค่าย AIS, True, DTAC พร้อมรหัส USSD กดสมัคร</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openCreateAddonModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all flex items-center gap-2">
                    <span>➕</span> เพิ่มโปรเสริมใหม่
                </button>
                <button onclick="loadAddons()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                    <span>🔄</span> รีเฟรช
                </button>
            </div>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 mb-8">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">📦</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">แพ็กเกจโปรเสริมทั้งหมด</p>
                    <h3 class="text-xl md:text-2xl font-bold text-slate-900" id="statTotalAddons">0</h3>
                </div>
            </div>
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-gray-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold shrink-0">📶</div>
                <div>
                    <p class="text-[11px] md:text-xs text-gray-400 font-bold uppercase">ค่ายเครือข่ายที่มีโปร</p>
                    <h3 class="text-xl md:text-2xl font-bold text-blue-600" id="statCarriers">0</h3>
                </div>
            </div>
        </div>

        <!-- Addons Table -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                <span class="text-emerald-600">📑</span> รายการโปรเสริมทั้งหมดในระบบ
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold text-xs uppercase">
                            <th class="py-3 px-4">ค่ายเครือข่าย</th>
                            <th class="py-3 px-4">ชื่อแพ็กเกจ</th>
                            <th class="py-3 px-4">ราคา (฿)</th>
                            <th class="py-3 px-4">ระยะเวลา</th>
                            <th class="py-3 px-4">รหัส USSD สมัคร</th>
                            <th class="py-3 px-4 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="addonsTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">กำลังโหลดโปรเสริม...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let addonsData = [];

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

        async function loadAddons() {
            try {
                const res = await fetch('api/admin_addons.php?action=list');
                const json = await res.json();
                if (json.status === 'success') {
                    addonsData = json.data || [];
                    document.getElementById('statTotalAddons').innerText = addonsData.length;
                    
                    const carrierSet = new Set(addonsData.map(a => a.carrier));
                    document.getElementById('statCarriers').innerText = carrierSet.size;

                    renderAddons(addonsData);
                }
            } catch (e) {
                console.error(e);
            }
        }

        function renderAddons(list) {
            const tbody = document.getElementById('addonsTableBody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-gray-400">ยังไม่มีแพ็กเกจโปรเสริม</td></tr>`;
                return;
            }

            tbody.innerHTML = list.map(a => {
                let badgeColor = 'bg-slate-100 text-slate-700';
                if (a.carrier.includes('AIS')) badgeColor = 'bg-green-100 text-green-700';
                else if (a.carrier.includes('True')) badgeColor = 'bg-red-100 text-red-700';
                else if (a.carrier.includes('DTAC')) badgeColor = 'bg-blue-100 text-blue-700';

                const codesList = (a.codes || []).map(c => `<span class="inline-block px-2 py-0.5 bg-slate-100 rounded text-[11px] font-mono mr-1 mb-1">${escapeHtml(c.name || 'สมัคร')}: <strong>${escapeHtml(c.code)}</strong></span>`).join('');

                return `
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="py-3.5 px-4 font-bold">
                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold ${badgeColor}">
                                ${escapeHtml(a.carrier)}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-800">${escapeHtml(a.title)}</p>
                            <p class="text-xs text-gray-400">${escapeHtml(a.description || '')}</p>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-emerald-600">฿${parseFloat(a.price).toFixed(2)}</td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-slate-600">${escapeHtml(a.duration_text || '30 วัน')}</td>
                        <td class="py-3.5 px-4">${codesList || '<span class="text-gray-400 text-xs">ไม่มีรหัส</span>'}</td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <button onclick="openEditAddon(${a.id})" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-bold text-xs transition-all">✏️ แก้ไข</button>
                            <button onclick="deleteAddon(${a.id}, '${escapeHtml(a.title)}')" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-bold text-xs transition-all">🗑️ ลบ</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function openCreateAddonModal() {
            const { value: formValues } = await Swal.fire({
                title: '➕ เพิ่มโปรเสริมใหม่',
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1">ค่ายเครือข่าย</label>
                                <select id="swalCarrier" class="swal2-select !m-0 !w-full">
                                    <option value="AIS 5G">AIS 5G</option>
                                    <option value="True 5G">True 5G</option>
                                    <option value="DTAC">DTAC</option>
                                    <option value="NT Mobile">NT Mobile</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">ระยะเวลา</label>
                                <input id="swalDuration" type="text" value="30 วัน" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ชื่อแพ็กเกจ</label>
                            <input id="swalTitle" type="text" placeholder="เช่น เน็ตไม่อั้น 15Mbps" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ราคา (บาท)</label>
                            <input id="swalPrice" type="number" step="0.01" value="200" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">คำอธิบายสั้น</label>
                            <input id="swalDesc" type="text" placeholder="เช่น เหมาะสำหรับเล่นเกม ดูหนัง 4K" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">รหัสกดสมัคร USSD (เช่น *777*7153#)</label>
                            <input id="swalCode" type="text" placeholder="*900*8888#" class="swal2-input !m-0 !w-full font-mono">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const carrier = document.getElementById('swalCarrier').value;
                    const duration = document.getElementById('swalDuration').value.trim();
                    const title = document.getElementById('swalTitle').value.trim();
                    const price = parseFloat(document.getElementById('swalPrice').value) || 0;
                    const desc = document.getElementById('swalDesc').value.trim();
                    const code = document.getElementById('swalCode').value.trim();

                    if (!title) {
                        Swal.showValidationMessage('กรุณาระบุชื่อแพ็กเกจ');
                        return false;
                    }

                    const codes = code ? [{ name: 'สมัครแพ็กเกจ', code: code }] : [];
                    return { carrier, duration_text: duration, title, price, description: desc, codes };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function openEditAddon(id) {
            const a = addonsData.find(x => x.id === id);
            if (!a) return;
            const primaryCode = (a.codes && a.codes[0]) ? a.codes[0].code : '';

            const { value: formValues } = await Swal.fire({
                title: `✏️ แก้ไขโปรเสริม`,
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold mb-1">ค่ายเครือข่าย</label>
                                <select id="swalEditCarrier" class="swal2-select !m-0 !w-full">
                                    <option value="AIS 5G" ${a.carrier === 'AIS 5G' ? 'selected' : ''}>AIS 5G</option>
                                    <option value="True 5G" ${a.carrier === 'True 5G' ? 'selected' : ''}>True 5G</option>
                                    <option value="DTAC" ${a.carrier === 'DTAC' ? 'selected' : ''}>DTAC</option>
                                    <option value="NT Mobile" ${a.carrier === 'NT Mobile' ? 'selected' : ''}>NT Mobile</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">ระยะเวลา</label>
                                <input id="swalEditDuration" type="text" value="${escapeHtml(a.duration_text || '30 วัน')}" class="swal2-input !m-0 !w-full">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ชื่อแพ็กเกจ</label>
                            <input id="swalEditTitle" type="text" value="${escapeHtml(a.title)}" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">ราคา (บาท)</label>
                            <input id="swalEditPrice" type="number" step="0.01" value="${a.price}" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">คำอธิบายสั้น</label>
                            <input id="swalEditDesc" type="text" value="${escapeHtml(a.description || '')}" class="swal2-input !m-0 !w-full">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">รหัสกดสมัคร USSD</label>
                            <input id="swalEditCode" type="text" value="${escapeHtml(primaryCode)}" class="swal2-input !m-0 !w-full font-mono">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'บันทึกการแก้ไข',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    const carrier = document.getElementById('swalEditCarrier').value;
                    const duration = document.getElementById('swalEditDuration').value.trim();
                    const title = document.getElementById('swalEditTitle').value.trim();
                    const price = parseFloat(document.getElementById('swalEditPrice').value) || 0;
                    const desc = document.getElementById('swalEditDesc').value.trim();
                    const code = document.getElementById('swalEditCode').value.trim();

                    if (!title) {
                        Swal.showValidationMessage('กรุณาระบุชื่อแพ็กเกจ');
                        return false;
                    }

                    const codes = code ? [{ name: 'สมัครแพ็กเกจ', code: code }] : [];
                    return { id, carrier, duration_text: duration, title, price, description: desc, codes };
                }
            });

            if (formValues) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', ...formValues })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        async function deleteAddon(id, title) {
            const confirm = await Swal.fire({
                title: `ลบโปรเสริม "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันลบ',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch('api/admin_addons.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        Toast.fire({ icon: 'success', title: data.message });
                        loadAddons();
                    } else {
                        Swal.fire('ผิดพลาด', data.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                }
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        document.addEventListener('DOMContentLoaded', loadAddons);
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
