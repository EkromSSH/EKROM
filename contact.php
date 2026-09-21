<?php
require_once __DIR__ . '/api/db.php';
$contact = get_contact_settings();

// Parse work hours for automatic status
$currentHour = (int)date('G'); // 0-23 in Asia/Bangkok
$isWorkingHours = ($currentHour >= 9 && $currentHour < 21);
if (($contact['work_status'] ?? '') === 'offline') {
    $isOnline = false;
} elseif (($contact['work_status'] ?? '') === 'online') {
    $isOnline = true;
} else {
    $isOnline = $isWorkingHours;
}

function safe_external_url($url, $default = '#') {
    $url = trim((string)$url);
    if (empty($url) || $url === '#') return $default;
    // ดึงเฉพาะ URL ออกมาหากมีการคัดลอกข้อความเชิญชวนของ LINE OpenChat ติดมาด้วย
    if (preg_match('/(https?:\/\/[^\s"\'<>]+)/i', $url, $m)) {
        return $m[1];
    }
    if (!preg_match('#^[a-z]+://#i', $url)) {
        return 'https://' . ltrim($url, '/');
    }
    return $url;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>ติดต่อผู้ดูแลระบบ - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Anuphan:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .sidebar-link:hover { background-color: rgba(219, 39, 119, 0.1); color: #db2777; }
        .sidebar-link.active { background-color: #db2777; color: white; box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2); }
        .contact-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .contact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.25s ease-out, padding 0.25s ease-out;
            opacity: 0;
        }
        .faq-active .faq-answer {
            max-height: 500px;
            opacity: 1;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        .faq-active .faq-icon {
            transform: rotate(180deg);
        }
        .badge-pulse {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 0.4; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
    </style>
    <script>
        fetch('api/check_auth.php').then(r => r.json()).then(data => {
            if (data.status !== 'logged_in') window.location.href = 'login.php';
        }).catch(() => window.location.href = 'login.php');
    </script>
    <link rel="stylesheet" href="mobile-fix.css">
</head>
<body class="app-shell bg-slate-50 text-slate-800 antialiased flex flex-col lg:flex-row h-screen overflow-hidden">

    <!-- Mobile Top Navigation Bar -->
    <div class="app-mobile-nav lg:hidden bg-white border-b border-gray-100 px-5 py-3.5 flex justify-between items-center z-40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md text-xs">EK</div>
            <span class="font-bold text-lg tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
        </div>
        
        <div class="flex items-center gap-2.5">
            <div onclick="window.location.href='topup.php'" class="bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 rounded-xl flex items-center gap-1.5 cursor-pointer hover:bg-emerald-100 transition-all shadow-sm">
                <span class="text-emerald-700 text-xs font-bold">฿<span id="userBalanceMob">0.00</span></span>
                <span class="bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded-md font-bold">+</span>
            </div>
            <button onclick="toggleMobileMenu()" class="text-slate-600 hover:text-pink-600 focus:outline-none p-1.5 rounded-lg hover:bg-slate-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobileMenu" onclick="if(event.target === this) toggleMobileMenu()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden opacity-0 transition-opacity duration-300">
        <div id="mobileDrawer" class="bg-white w-72 h-full flex flex-col p-6 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="drawer-header flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="drawer-logo w-10 h-10 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">EK</div>
                    <span class="drawer-title font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
                </div>
                <button onclick="toggleMobileMenu()" class="drawer-close-btn w-9 h-9 bg-slate-100 rounded-full flex items-center justify-center text-gray-500 hover:text-slate-900 transition-all">✕</button>
            </div>
            <nav class="flex-grow space-y-1.5">
                <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📊</span> Dashboard</a>
                <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>🛒</span> บริการ VPN</a>
                <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>💰</span> เติมเงิน</a>
                <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
                <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📦</span> โปรเสริม</a>
                <a href="contact.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💬</span> ติดต่อแอดมิน</a>
            </nav>
            <div class="drawer-footer mt-auto pt-6 border-t border-gray-100">
                <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-72 bg-white h-screen border-r border-gray-100 p-6 shrink-0 z-40">
        <div class="flex items-center gap-3 mb-10 cursor-pointer" onclick="window.location.href='buyer-dash.php'">
            <div class="w-10 h-10 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-pink-500/20">EK</div>
            <div class="flex flex-col">
                <span class="font-bold text-xl tracking-tight italic">EKROM <span class="text-pink-600">SHOP</span></span>
                <span class="text-[11px] text-gray-400 font-medium">VPN & Network Service</span>
            </div>
        </div>
        <nav class="flex-grow space-y-1.5">
            <a href="buyer-dash.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📊</span> Dashboard</a>
            <a href="store.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>🛒</span> บริการ VPN</a>
            <a href="topup.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>💰</span> เติมเงิน</a>
            <a href="history.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📜</span> ประวัติการทำรายการ</a>
            <a href="addon.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-600 transition-all"><span>📦</span> โปรเสริม</a>
            <a href="contact.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all"><span>💬</span> ติดต่อแอดมิน</a>
        </nav>
        <div class="mt-auto pt-6 border-t border-gray-100">
            <button onclick="window.location.href='api/logout.php'" class="flex items-center gap-3 px-4 py-3 w-full text-red-500 font-semibold hover:bg-red-50 rounded-xl transition-all"><span>🚪</span> ออกจากระบบ</button>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-grow p-4 md:p-8 lg:p-10 overflow-y-auto">
        <div class="max-w-5xl mx-auto space-y-8">
            
            <!-- Hero Header Section -->
            <div class="bg-gradient-to-r from-pink-600 via-rose-600 to-pink-500 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-pink-600/15 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-12 top-4 text-8xl opacity-10 select-none pointer-events-none">💬</div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold mb-3 border border-white/20">
                            <span>✨</span> ศูนย์ช่วยเหลือและบริการลูกค้า
                        </div>
                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight">
                            ติดต่อผู้ดูแลระบบ 💬
                        </h1>
                        <p class="text-pink-100 text-sm md:text-base mt-2 max-w-xl font-normal leading-relaxed">
                            ยินดีให้ความช่วยเหลือตลอดเวลา มีปัญหาการใช้งาน สอบถามการติดตั้ง หรือแจ้งปัญหาเติมเงิน เลือกติดต่อผ่านช่องทางด้านล่างได้ทันที
                        </p>
                    </div>

                    <!-- Status & Work Hours Card -->
                    <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl p-4 md:p-5 shrink-0 min-w-[240px] shadow-lg">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="text-xs text-pink-100 font-medium">สถานะแอดมิน</span>
                            <?php if ($isOnline): ?>
                                <span class="inline-flex items-center gap-1.5 bg-emerald-500/90 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> ออนไลน์
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 bg-amber-500/90 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-white"></span> พักผ่อน (ทักทิ้งไว้ได้)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">🕒</div>
                            <div>
                                <div class="text-[11px] text-pink-100 font-medium uppercase tracking-wider">เวลาทำการ</div>
                                <div class="text-sm md:text-base font-bold text-white"><?= htmlspecialchars($contact['work_hours'] ?? '09:00 - 21:00 น.') ?></div>
                            </div>
                        </div>
                        <p class="text-[11px] text-pink-100 mt-2 border-t border-white/15 pt-2">
                            <?= htmlspecialchars($contact['work_days'] ?? 'เปิดบริการทุกวัน (จันทร์ - อาทิตย์)') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Tip Card (ข้อแนะนำเพื่อความรวดเร็ว) -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-4 md:p-5 flex items-start gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl shrink-0">
                    💡
                </div>
                <div class="text-xs md:text-sm text-slate-700 leading-relaxed flex-grow">
                    <span class="font-bold text-amber-900 block text-sm mb-1">💡 เพื่อให้ได้รับการแก้ไขและบริการอย่างรวดเร็วที่สุด:</span>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-600 text-xs md:text-sm">
                        <li>โปรดแจ้ง <b>Username</b> ที่ใช้งานในระบบ</li>
                        <li>หากเป็นปัญหาเติมเงิน แนบภาพถ่ายสลิปที่มี QR Code ชัดเจน</li>
                        <li>หาก VPN เชื่อมต่อไม่ได้ แคปหน้าจอ Log ข้อผิดพลาดจากในแอปมาด้วยครับ</li>
                    </ul>
                </div>
            </div>

            <!-- 🟢 SECTION 1: ช่องทาง LINE (LINE Official Account, LINE แอดมิน, LINE กลุ่ม/OpenChat) -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-3 h-3 rounded-full bg-[#06C755]"></div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-900">ช่องทาง LINE (แนะนำ • สะดวกและรวดเร็ว)</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    <!-- 1. LINE Official Account (LINE @) -->
                    <div class="bg-white rounded-3xl p-6 border border-emerald-100 shadow-sm hover:border-[#06C755]/40 contact-card flex flex-col justify-between relative overflow-hidden group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-[#06C755]/10 text-[#06C755] rounded-2xl flex items-center justify-center shadow-inner">
                                    <!-- Official LINE SVG Icon -->
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.477.254l2.486 3.376V8.108c0-.345.282-.63.63-.63.345 0 .624.285.624.63v4.771zm-7.009-4.771c.347 0 .629.285.629.63v4.771c0 .344-.282.629-.629.629-.348 0-.63-.285-.63-.629V8.108c0-.345.282-.63.63-.63zm-2.433 4.771h-1.63V8.108c0-.345-.282-.63-.63-.63s-.63.285-.63.63v5.401c0 .344.282.629.63.629h2.26c.347 0 .629-.285.629-.629 0-.349-.282-.631-.629-.631zM24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                                    </svg>
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm shadow-emerald-600/25">
                                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> แนะนำ • ตอบไว
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($contact['line_oa_name'] ?? 'LINE Official Account') ?></h3>
                            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                สอบถามปัญหาการเชื่อมต่อ แจ้งเติมเงิน หรือปรึกษาแพ็กเกจ มีแอดมินคอยตอบผ่านระบบ LINE OA
                            </p>

                            <?php if (!empty($contact['line_oa_id'])): ?>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3 py-2 flex items-center justify-between mb-4">
                                <div class="text-xs">
                                    <span class="text-slate-400">LINE ID:</span>
                                    <span class="font-bold text-slate-800 ml-1 select-all"><?= htmlspecialchars($contact['line_oa_id']) ?></span>
                                </div>
                                <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($contact['line_oa_id']) ?>', this)" data-copied-text="<?= htmlspecialchars($contact['line_oa_id']) ?>" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 bg-white border border-emerald-200 px-2 py-1 rounded-lg transition-all active:scale-95 shadow-2xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10a2 2 0 00-2 2v3a2 2 0 002 2h8a2 2 0 002-2v-3a2 2 0 00-2-2z"></path></svg>
                                    <span>คัดลอก ID</span>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2 pt-2">
                            <a href="<?= htmlspecialchars(safe_external_url($contact['line_oa_url'] ?? '', 'https://line.me/R/ti/p/@ekromshop')) ?>" target="_blank" class="w-full bg-[#06C755] hover:bg-[#05b34c] text-white py-2.5 px-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-[#06C755]/20 active:scale-95">
                                <span>📲</span> เพิ่มเพื่อนใน LINE
                            </a>
                        </div>
                    </div>

                    <!-- 2. LINE ส่วนตัวแอดมิน (Personal LINE) -->
                    <div class="bg-white rounded-3xl p-6 border border-teal-100 shadow-sm hover:border-teal-400/40 contact-card flex flex-col justify-between relative overflow-hidden group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                                    👤
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-teal-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm shadow-teal-600/25">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-200"></span> ติดต่อตรง
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($contact['line_personal_name'] ?? 'LINE ส่วนตัวแอดมิน') ?></h3>
                            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                ติดต่อพูดคุยกับแอดมินโดยตรง เหมาะสำหรับกรณีเร่งด่วน หรือต้องการสอบถามเรื่องระบบเป็นพิเศษ
                            </p>

                            <?php if (!empty($contact['line_personal_id'])): ?>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3 py-2 flex items-center justify-between mb-4">
                                <div class="text-xs">
                                    <span class="text-slate-400">LINE ID:</span>
                                    <span class="font-bold text-slate-800 ml-1 select-all"><?= htmlspecialchars($contact['line_personal_id']) ?></span>
                                </div>
                                <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($contact['line_personal_id']) ?>', this)" data-copied-text="<?= htmlspecialchars($contact['line_personal_id']) ?>" class="text-[11px] font-semibold text-teal-600 hover:text-teal-700 bg-white border border-teal-200 px-2 py-1 rounded-lg transition-all active:scale-95 shadow-2xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10a2 2 0 00-2 2v3a2 2 0 002 2h8a2 2 0 002-2v-3a2 2 0 00-2-2z"></path></svg>
                                    <span>คัดลอก ID</span>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2 pt-2">
                            <a href="<?= htmlspecialchars(safe_external_url($contact['line_personal_url'] ?? '', 'https://line.me/ti/p/~' . ($contact['line_personal_id'] ?? ''))) ?>" target="_blank" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2.5 px-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-teal-600/20 active:scale-95">
                                <span>💬</span> ทักแชท LINE แอดมิน
                            </a>
                        </div>
                    </div>

                    <!-- 3. LINE กลุ่ม / OpenChat (Community) -->
                    <div class="bg-white rounded-3xl p-6 border border-emerald-100 shadow-sm hover:border-[#06C755]/40 contact-card flex flex-col justify-between relative overflow-hidden group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-emerald-100/70 text-[#06C755] rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                                    👥
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-emerald-700 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm shadow-emerald-700/25">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-200"></span> ชุมชนสมาชิก
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($contact['line_group_name'] ?? 'กลุ่ม LINE OpenChat') ?></h3>
                            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                <?= htmlspecialchars($contact['line_group_desc'] ?? 'พูดคุยแลกเปลี่ยนเทคนิคการใช้งาน รับแจ้งอัปเดตไฟล์ VPN และพูดคุยกับเพื่อนสมาชิก') ?>
                            </p>

                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-4 text-xs text-slate-600 flex items-center gap-2">
                                <span class="text-emerald-500 text-base shrink-0">📢</span>
                                <span>อัปเดตไฟล์ใหม่และข่าวสารสำคัญแบบเรียลไทม์</span>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <a href="<?= htmlspecialchars(safe_external_url($contact['line_group_url'] ?? '', '#')) ?>" target="_blank" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-2.5 px-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-emerald-600/20 active:scale-95">
                                <span>🚀</span> เข้าร่วม LINE OpenChat
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 🔵 SECTION 2: ช่องทาง Facebook & Messenger -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-3 h-3 rounded-full bg-[#1877F2]"></div>
                    <h2 class="text-lg md:text-xl font-bold text-slate-900">ช่องทาง Facebook & Messenger</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- 4. Facebook Fanpage -->
                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-blue-100 shadow-sm hover:border-blue-300 contact-card flex flex-col justify-between relative overflow-hidden group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-[#1877F2]/10 text-[#1877F2] rounded-2xl flex items-center justify-center shadow-inner">
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-[#1877F2] text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm shadow-blue-500/25">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-200"></span> เพจหลักทางการ
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 mb-1"><?= htmlspecialchars($contact['facebook_page_name'] ?? 'Facebook Page') ?></h3>
                            <p class="text-xs md:text-sm text-slate-500 mb-6 leading-relaxed">
                                แจ้งปัญหาการใช้งาน เติมเงิน สอบถามโปรเสริม ติดต่อทางกล่องข้อความ Inbox เพจอย่างเป็นทางการ
                            </p>
                        </div>

                        <div>
                            <a href="<?= htmlspecialchars(safe_external_url($contact['facebook_page_url'] ?? '', 'https://www.facebook.com/share/14Zq7rmBjpS/')) ?>" target="_blank" class="w-full bg-[#1877F2] hover:bg-[#166FE5] text-white py-3 px-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-[#1877F2]/25 active:scale-95">
                                <span>💬</span> ทักแชท Inbox Facebook เลย
                            </a>
                        </div>
                    </div>

                    <!-- 5. Facebook Messenger Group -->
                    <div class="bg-white rounded-3xl p-6 md:p-7 border border-sky-100 shadow-sm hover:border-sky-300 contact-card flex flex-col justify-between relative overflow-hidden group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-[#00B2FF]/10 text-[#00B2FF] rounded-2xl flex items-center justify-center shadow-inner">
                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26 6.559-6.963 3.13 3.259 5.889-3.259-6.56 6.963z"/>
                                    </svg>
                                </div>
                                <span class="inline-flex items-center gap-1.5 bg-[#0084FF] text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm shadow-sky-500/25">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-200"></span> กลุ่มแชทพูดคุย
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 mb-1"><?= htmlspecialchars($contact['messenger_group_name'] ?? 'กลุ่มพูดคุย & แจ้งปัญหา') ?></h3>
                            <p class="text-xs md:text-sm text-slate-500 mb-6 leading-relaxed">
                                กลุ่มแชท Messenger สำหรับพูดคุย ปรึกษา แจ้งปัญหา หรือสอบถามวิธีการใช้งานระหว่างเพื่อนสมาชิก
                            </p>
                        </div>

                        <div>
                            <a href="<?= htmlspecialchars(safe_external_url($contact['messenger_group_url'] ?? '', 'https://m.me/j/AbbNfkaIlLvGwfKr/?send_source=gc%3Acopy_invite_link_c')) ?>" target="_blank" class="w-full bg-[#00B2FF] hover:bg-[#009ee3] text-white py-3 px-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-[#00B2FF]/25 active:scale-95">
                                <span>⚡</span> เข้าร่วมกลุ่มแชท Messenger
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 💡 SECTION 3: FAQ คำถามที่พบบ่อย (Accordion สไตล์พรีเมียม) -->
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">💡</span>
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-slate-900">คำถามที่พบบ่อย (FAQ)</h2>
                            <p class="text-xs text-slate-500 mt-0.5">รวมคำตอบและวิธีแก้ไขปัญหาเบื้องต้นที่พบบ่อยที่สุด</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3" id="faqAccordion">
                    
                    <div class="border border-slate-200/70 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer faq-item" onclick="toggleFaq(this)">
                        <div class="flex justify-between items-center gap-3">
                            <h3 class="font-bold text-slate-800 text-sm md:text-base flex items-center gap-2">
                                <span class="text-pink-600 font-extrabold text-sm">Q:</span> เติมเงินสแกนสลิปแล้ว ยอดเงินไม่ปรับเข้าบัญชีต้องทำอย่างไร?
                            </h3>
                            <span class="faq-icon text-slate-400 transition-transform duration-300 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                        <div class="faq-answer text-xs md:text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 mt-3">
                            <b>ตอบ:</b> กรุณาตรวจสอบว่าชื่อบัญชีและธนาคารตรงกับที่ระบบกำหนด และสลิปไม่มีการอัปโหลดซ้ำ หากสแกนถูกต้องแล้วแต่ยอดไม่เข้าภายใน 1-2 นาที สามารถส่งภาพสลิปการโอนเงินพร้อมแจ้ง Username ให้แอดมินทาง LINE หรือ Facebook ได้ทันที แอดมินจะตรวจสอบและปรับยอดให้โดยเร็วครับ
                        </div>
                    </div>

                    <div class="border border-slate-200/70 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer faq-item" onclick="toggleFaq(this)">
                        <div class="flex justify-between items-center gap-3">
                            <h3 class="font-bold text-slate-800 text-sm md:text-base flex items-center gap-2">
                                <span class="text-pink-600 font-extrabold text-sm">Q:</span> VPN ไม่เชื่อมต่อ หรือขึ้น Connection Timeout แก้ไขอย่างไร?
                            </h3>
                            <span class="faq-icon text-slate-400 transition-transform duration-300 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                        <div class="faq-answer text-xs md:text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 mt-3">
                            <b>ตอบ:</b> แนะนำขั้นตอนการตรวจสอบเบื้องต้น:
                            <ol class="list-decimal list-inside mt-1.5 space-y-1">
                                <li>ปิด-เปิด โหมดเครื่องบิน (Airplane Mode) บนโทรศัพท์ 5 วินาที เพื่อรีเซ็ต IP ซิมการ์ด</li>
                                <li>ตรวจสอบว่าแพ็กเกจอินเทอร์เน็ต/โปรเสริมตรงกับหมวดหมู่เซิร์ฟเวอร์ที่เลือกใช้งาน</li>
                                <li>กดคัดลอกลิงก์หรือดาวน์โหลดไฟล์คอนฟิกจากหน้าเว็บใหม่อีกครั้ง</li>
                                <li>หากยังไม่สามารถเชื่อมต่อได้ แคปหน้าจอ Log ส่งให้แอดมินในแชทเพื่อตรวจสอบสถานะเซิร์ฟเวอร์ครับ</li>
                            </ol>
                        </div>
                    </div>

                    <div class="border border-slate-200/70 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer faq-item" onclick="toggleFaq(this)">
                        <div class="flex justify-between items-center gap-3">
                            <h3 class="font-bold text-slate-800 text-sm md:text-base flex items-center gap-2">
                                <span class="text-pink-600 font-extrabold text-sm">Q:</span> ย้ายเครื่อง หรือนำไฟล์ VPN ไปใช้ในคอมพิวเตอร์ได้ไหม?
                            </h3>
                            <span class="faq-icon text-slate-400 transition-transform duration-300 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                        <div class="faq-answer text-xs md:text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 mt-3">
                            <b>ตอบ:</b> สามารถนำไปใช้ได้ครับ แต่ต้องอยู่ภายใต้เงื่อนไขจำกัดจำนวนอุปกรณ์ (Limit IP) ตามแพ็กเกจ เช่น หากแพ็กเกจจำกัด 1-2 เครื่อง ไม่ควรเชื่อมต่อพร้อมกันเกินกว่าที่กำหนด เพราะระบบจะบล็อกการเชื่อมต่ออัตโนมัติครับ
                        </div>
                    </div>

                    <div class="border border-slate-200/70 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer faq-item" onclick="toggleFaq(this)">
                        <div class="flex justify-between items-center gap-3">
                            <h3 class="font-bold text-slate-800 text-sm md:text-base flex items-center gap-2">
                                <span class="text-pink-600 font-extrabold text-sm">Q:</span> ลืมรหัสผ่าน ไม่สามารถเข้าสู่ระบบได้ ต้องทำอย่างไร?
                            </h3>
                            <span class="faq-icon text-slate-400 transition-transform duration-300 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                        <div class="faq-answer text-xs md:text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 mt-3">
                            <b>ตอบ:</b> สามารถแจ้งชื่อผู้ใช้งาน (Username) หรืออีเมลที่ลงทะเบียนไว้ แล้วส่งให้แอดมินทาง LINE หรือ Facebook แอดมินจะตรวจสอบความเป็นเจ้าของและทำการรีเซ็ตรหัสผ่านใหม่ให้ทันทีครับ
                        </div>
                    </div>

                    <div class="border border-slate-200/70 rounded-2xl p-4 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer faq-item" onclick="toggleFaq(this)">
                        <div class="flex justify-between items-center gap-3">
                            <h3 class="font-bold text-slate-800 text-sm md:text-base flex items-center gap-2">
                                <span class="text-pink-600 font-extrabold text-sm">Q:</span> สามารถต่ออายุไฟล์ VPN เดิมก่อนหมดอายุได้หรือไม่?
                            </h3>
                            <span class="faq-icon text-slate-400 transition-transform duration-300 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </div>
                        <div class="faq-answer text-xs md:text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 mt-3">
                            <b>ตอบ:</b> ได้ครับ สามารถเข้าสู่หน้า Dashboard แล้วกดที่ปุ่ม <b>"ต่ออายุ"</b> ในการ์ด VPN นั้นๆ ได้ทันที วันหมดอายุจะถูกบวกเพิ่มจากวันหมดอายุเดิมอัตโนมัติ โดยไม่ต้องเปลี่ยนไฟล์คอนฟิกใหม่
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Note -->
            <div class="text-center text-xs text-slate-400 pb-6">
                <?= htmlspecialchars($contact['contact_note'] ?? 'หากติดต่อหลังเวลาทำการ ทีมงานจะรีบตอบกลับในเช้าวันถัดไปครับ') ?>
            </div>

        </div>
    </main>

    <!-- Toast Notification for Copy -->
    <div id="copyToast" class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-2xl transition-all duration-300 opacity-0 pointer-events-none transform translate-y-4 flex items-center gap-2">
        <span>📋</span> <span id="copyToastText">คัดลอกเรียบร้อยแล้ว</span>
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

        function toggleFaq(item) {
            item.classList.toggle('faq-active');
        }

        function copyToClipboard(text, btnElement) {
            if (!text) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopiedFeedback(btnElement, text);
                }).catch(() => fallbackCopy(text, btnElement));
            } else {
                fallbackCopy(text, btnElement);
            }
        }

        function fallbackCopy(text, btnElement) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                showCopiedFeedback(btnElement, text);
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(textarea);
        }

        function showCopiedFeedback(btnElement, text) {
            if (btnElement) {
                const originalHtml = btnElement.innerHTML;
                btnElement.innerHTML = `<span class="text-emerald-600 font-bold">✓ คัดลอกแล้ว</span>`;
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                }, 2000);
            }
            showToast('คัดลอก ID แล้ว: ' + text);
        }

        function showToast(msg) {
            const toast = document.getElementById('copyToast');
            const toastText = document.getElementById('copyToastText');
            if (!toast) return;
            if (toastText) toastText.innerText = msg;
            toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
            }, 2400);
        }

        async function loadUserInfo() {
            try {
                const res = await fetch('api/get_user_info.php');
                const data = await res.json();
                if(data.status === 'success') {
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
