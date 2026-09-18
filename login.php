<?php
require_once __DIR__ . '/api/db.php';
$turnstileSettings = get_turnstile_settings();
$turnstileEnabled = !empty($turnstileSettings['enabled']) && !empty($turnstileSettings['site_key']) && !empty($turnstileSettings['secret_key']);
$turnstileSiteKey = $turnstileSettings['site_key'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>เข้าสู่ระบบ / สมัครสมาชิก - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($turnstileEnabled): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoad" async defer></script>
    <?php endif; ?>
    <link rel="stylesheet" href="mobile-fix.css">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
    </style>
</head>
<body class="login-page bg-gradient-to-b from-slate-50 via-slate-100/50 to-slate-50 min-h-screen min-h-[100dvh] flex flex-col items-center justify-center p-4 sm:p-6 antialiased">

    <!-- Card Wrapper -->
    <div class="w-full max-w-[390px] bg-white rounded-2xl sm:rounded-3xl shadow-xl shadow-slate-200/70 border border-slate-100 p-5 sm:p-6 transition-all">
        
        <!-- Logo & Header -->
        <div class="text-center mb-5 cursor-pointer select-none" onclick="window.location.href='index.php'">
            <div class="inline-flex items-center justify-center w-11 h-11 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-2xl text-white font-bold text-lg shadow-md shadow-pink-200 mb-2">
                EK
            </div>
            <div class="font-bold text-xl tracking-tight text-slate-900 italic">
                EKROM <span class="text-pink-600">SHOP</span>
            </div>
            <p id="pageSubtitle" class="text-[11px] text-slate-400 font-medium mt-0.5">ยินดีต้อนรับ เข้าสู่ระบบเพื่อจัดการบริการ</p>
        </div>

        <!-- Tab Toggle -->
        <div class="flex bg-slate-100/90 p-1 rounded-xl mb-5 text-xs font-bold">
            <button type="button" onclick="toggleForm('login')" id="tabLogin" class="flex-1 py-2 rounded-lg bg-white shadow-sm text-pink-600 transition-all">
                เข้าสู่ระบบ
            </button>
            <button type="button" onclick="toggleForm('register')" id="tabRegister" class="flex-1 py-2 rounded-lg text-slate-500 hover:text-slate-800 transition-all">
                สมัครสมาชิก
            </button>
        </div>

        <!-- Login Form -->
        <form id="loginForm" class="space-y-3.5 block" onsubmit="handleAuth(event, 'login')">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">ชื่อผู้ใช้งาน (Username)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </span>
                    <input type="text" id="loginUser" name="username" autocomplete="username" required placeholder="กรอกชื่อผู้ใช้งาน" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-100 outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">รหัสผ่าน (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </span>
                    <input type="password" id="loginPass" name="password" autocomplete="current-password" required placeholder="กรอกรหัสผ่าน" class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-100 outline-none transition-all">
                    <button type="button" onclick="togglePassword('loginPass', 'iconLoginPass')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-pink-600 transition-colors">
                        <svg id="iconLoginPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-0.5">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" id="rememberMe" class="w-3.5 h-3.5 rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                    <span class="text-slate-600">จดจำการเข้าสู่ระบบ</span>
                </label>
            </div>

            <?php if ($turnstileEnabled): ?>
            <div class="flex flex-col items-center justify-center pt-1 min-h-[65px]">
                <div id="turnstile-login-container"></div>
                <button type="button" onclick="resetTurnstile('login')" class="text-[11px] text-slate-400 hover:text-pink-600 transition-colors flex items-center gap-1 mt-1.5 select-none" title="คลิกเพื่อรีเฟรชการตรวจสอบหุ่นยนต์">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    รีเฟรชการตรวจสอบ
                </button>
            </div>
            <?php endif; ?>

            <button type="submit" id="btnLogin" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-pink-200 text-sm transition-all active:scale-[0.99] mt-2">
                เข้าสู่ระบบ
            </button>
        </form>

        <!-- Register Form -->
        <form id="registerForm" class="space-y-3.5 hidden" onsubmit="handleAuth(event, 'register')">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">ตั้งชื่อผู้ใช้งาน (Username)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </span>
                    <input type="text" id="regUser" name="username" autocomplete="username" required placeholder="ภาษาอังกฤษหรือตัวเลข" class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">ตั้งรหัสผ่าน (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </span>
                    <input type="password" id="regPass" name="new-password" autocomplete="new-password" required placeholder="อย่างน้อย 6 ตัวอักษร" class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition-all">
                    <button type="button" onclick="togglePassword('regPass', 'iconRegPass')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg id="iconRegPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">ยืนยันรหัสผ่าน (Confirm Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </span>
                    <input type="password" id="regConfirmPass" name="new-password-confirm" autocomplete="new-password" required placeholder="กรอกรหัสผ่านอีกครั้ง" class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition-all">
                    <button type="button" onclick="togglePassword('regConfirmPass', 'iconRegConfirm')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg id="iconRegConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <?php if ($turnstileEnabled): ?>
            <div class="flex flex-col items-center justify-center pt-1 min-h-[65px]">
                <div id="turnstile-register-container"></div>
                <button type="button" onclick="resetTurnstile('register')" class="text-[11px] text-slate-400 hover:text-emerald-600 transition-colors flex items-center gap-1 mt-1.5 select-none" title="คลิกเพื่อรีเฟรชการตรวจสอบหุ่นยนต์">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    รีเฟรชการตรวจสอบ
                </button>
            </div>
            <?php endif; ?>

            <button type="submit" id="btnRegister" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl shadow-md shadow-emerald-200 text-sm transition-all active:scale-[0.99] mt-2">
                ยืนยันสมัครสมาชิก
            </button>
        </form>
    </div>

    <!-- Back to store footer link -->
    <a href="index.php" class="mt-4 text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1.5 transition-colors font-medium select-none">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        กลับหน้าหลักร้านค้า
    </a>

    <script>
        // กำหนดตัวแปรระบบและสถานะ Cloudflare Turnstile
        const turnstileEnabled = <?= json_encode($turnstileEnabled) ?>;
        const turnstileSiteKey = <?= json_encode($turnstileSiteKey) ?>;
        let activeAuthTab = 'login';
        let isTurnstileScriptLoaded = false;
        let isDomReady = false;
        let loginWidgetId = null;
        let registerWidgetId = null;
        let loginTurnstileToken = '';
        let registerTurnstileToken = '';

        // Callback เมื่อ Cloudflare Turnstile API โหลดเสร็จสิ้น
        window.onTurnstileLoad = function() {
            isTurnstileScriptLoaded = true;
            checkAndInitTurnstile();
        };

        function checkAndInitTurnstile() {
            if (!turnstileEnabled) return;
            if (isTurnstileScriptLoaded && isDomReady) {
                renderTurnstileWidget(activeAuthTab);
            }
        }

        function renderTurnstileWidget(type) {
            if (!turnstileEnabled || typeof turnstile === 'undefined' || !turnstileSiteKey) return;
            const isLogin = type === 'login';
            const containerId = isLogin ? 'turnstile-login-container' : 'turnstile-register-container';
            const container = document.getElementById(containerId);
            if (!container) return;

            // ตรวจสอบว่า Form ถูกซ่อนอยู่หรือไม่ (ถ้ายังถูกซ่อน ห้ามเรนเดอร์เพื่อป้องกัน iframe error 0x0)
            const formId = isLogin ? 'loginForm' : 'registerForm';
            const formEl = document.getElementById(formId);
            if (formEl && formEl.classList.contains('hidden')) return;

            const existingWidgetId = isLogin ? loginWidgetId : registerWidgetId;
            if (existingWidgetId !== null) {
                resetTurnstile(type);
                return;
            }

            try {
                const wId = turnstile.render('#' + containerId, {
                    sitekey: turnstileSiteKey,
                    theme: 'light',
                    'refresh-expired': 'auto',
                    callback: function(token) {
                        if (isLogin) {
                            loginTurnstileToken = token;
                        } else {
                            registerTurnstileToken = token;
                        }
                    },
                    'expired-callback': function() {
                        if (isLogin) {
                            loginTurnstileToken = '';
                        } else {
                            registerTurnstileToken = '';
                        }
                        resetTurnstile(type);
                    },
                    'error-callback': function() {
                        if (isLogin) {
                            loginTurnstileToken = '';
                        } else {
                            registerTurnstileToken = '';
                        }
                    }
                });

                if (isLogin) {
                    loginWidgetId = wId;
                } else {
                    registerWidgetId = wId;
                }
            } catch (e) {
                console.error('Error rendering Turnstile for ' + type + ':', e);
            }
        }

        function resetTurnstile(type) {
            if (!turnstileEnabled || typeof turnstile === 'undefined') return;
            const isLogin = type === 'login';
            const formId = isLogin ? 'loginForm' : 'registerForm';
            const wId = isLogin ? loginWidgetId : registerWidgetId;

            if (isLogin) {
                loginTurnstileToken = '';
            } else {
                registerTurnstileToken = '';
            }

            // ล้างค่า token เก่าออกจาก input เพื่อป้องกันการส่ง token เดิมซ้ำ
            const formEl = document.getElementById(formId);
            if (formEl) {
                const tokenInput = formEl.querySelector('[name="cf-turnstile-response"]');
                if (tokenInput) tokenInput.value = '';
            }

            if (wId !== null) {
                try {
                    turnstile.reset(wId);
                } catch (e) {
                    recreateTurnstileWidget(type);
                }
            } else {
                renderTurnstileWidget(type);
            }
        }

        function recreateTurnstileWidget(type) {
            if (!turnstileEnabled || typeof turnstile === 'undefined') return;
            const isLogin = type === 'login';
            const containerId = isLogin ? 'turnstile-login-container' : 'turnstile-register-container';
            const container = document.getElementById(containerId);
            if (!container) return;

            const wId = isLogin ? loginWidgetId : registerWidgetId;
            if (wId !== null) {
                try { turnstile.remove(wId); } catch(e) {}
            }
            if (isLogin) {
                loginWidgetId = null;
                loginTurnstileToken = '';
            } else {
                registerWidgetId = null;
                registerTurnstileToken = '';
            }
            container.innerHTML = '';
            renderTurnstileWidget(type);
        }

        // ตรวจสอบชื่อผู้ใช้ที่เคยบันทึกไว้ในเบราว์เซอร์ (Remember me)
        document.addEventListener('DOMContentLoaded', () => {
            isDomReady = true;
            try {
                // ตรวจสอบกรณีสคริปต์ Cloudflare โหลดเสร็จก่อน DOMContentLoaded
                if (typeof turnstile !== 'undefined' && typeof turnstile.render === 'function') {
                    isTurnstileScriptLoaded = true;
                }

                const savedUser = localStorage.getItem('ekrom_remember_user');
                if (savedUser) {
                    const userInput = document.getElementById('loginUser');
                    const rememberCheckbox = document.getElementById('rememberMe');
                    if (userInput) userInput.value = savedUser;
                    if (rememberCheckbox) rememberCheckbox.checked = true;
                    const passInput = document.getElementById('loginPass');
                    if (passInput) passInput.focus();
                }

                // สลับแท็บอัตโนมัติตาม URL Parameter หรือ Hash
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('tab') === 'register' || urlParams.get('mode') === 'register' || window.location.hash === '#register') {
                    toggleForm('register');
                } else {
                    checkAndInitTurnstile();
                }
            } catch (e) {}
        });

        // ถ้ามี Session หรือคุกกี้จดจำฉันอยู่แล้ว ให้เข้า Dashboard ได้ทันที
        fetch('api/check_auth.php', { cache: 'no-store' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'logged_in') {
                    const role = data.role || data.user?.role;
                    if (role === 'admin') {
                        window.location.replace('admin-dash.php');
                    } else {
                        window.location.replace('buyer-dash.php');
                    }
                }
            })
            .catch(() => {});

        function toggleForm(type) {
            activeAuthTab = type;
            const loginForm = document.getElementById('loginForm');
            const regForm = document.getElementById('registerForm');
            const tabLogin = document.getElementById('tabLogin');
            const tabRegister = document.getElementById('tabRegister');
            const pageSubtitle = document.getElementById('pageSubtitle');

            if (type === 'login') {
                loginForm.classList.remove('hidden');
                loginForm.classList.add('block');
                regForm.classList.remove('block');
                regForm.classList.add('hidden');
                tabLogin.className = "flex-1 py-2 rounded-lg bg-white shadow-sm text-pink-600 transition-all font-bold";
                tabRegister.className = "flex-1 py-2 rounded-lg text-slate-500 hover:text-slate-800 transition-all font-medium";
                if (pageSubtitle) pageSubtitle.innerText = 'ยินดีต้อนรับ เข้าสู่ระบบเพื่อจัดการบริการ';
            } else {
                loginForm.classList.remove('block');
                loginForm.classList.add('hidden');
                regForm.classList.remove('hidden');
                regForm.classList.add('block');
                tabRegister.className = "flex-1 py-2 rounded-lg bg-white shadow-sm text-emerald-600 transition-all font-bold";
                tabLogin.className = "flex-1 py-2 rounded-lg text-slate-500 hover:text-slate-800 transition-all font-medium";
                if (pageSubtitle) pageSubtitle.innerText = 'สร้างบัญชีใหม่เพื่อเริ่มใช้งาน VPN ได้ทันที';
            }

            // เรนเดอร์หรือรีเซ็ต Turnstile ของแท็บที่เปิดใช้งาน
            if (turnstileEnabled) {
                renderTurnstileWidget(type);
            }
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text'; 
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
            } else {
                input.type = 'password'; 
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }

        async function handleAuth(event, type) {
            event.preventDefault();
            
            const isLogin = type === 'login';
            const user = document.getElementById(isLogin ? 'loginUser' : 'regUser').value.trim();
            const pass = document.getElementById(isLogin ? 'loginPass' : 'regPass').value;
            
            // ตรวจสอบเฉพาะหน้า "สมัครสมาชิก"
            if (!isLogin) {
                const confirmPass = document.getElementById('regConfirmPass').value;
                
                // ตรวจสอบชื่อผู้ใช้งาน (ตัวอักษรภาษาอังกฤษและตัวเลขเท่านั้น ห้ามเว้นวรรค)
                const userRegex = /^[a-zA-Z0-9]+$/;
                if (!userRegex.test(user)) {
                    Swal.fire({ icon: 'error', title: 'ชื่อผู้ใช้งานไม่ถูกต้อง', text: 'ชื่อผู้ใช้งานต้องเป็นตัวอักษรภาษาอังกฤษ (A-Z, a-z) หรือตัวเลข (0-9) เท่านั้น' });
                    return;
                }

                // ตรวจสอบความยาวรหัสผ่าน (6 ตัวขึ้นไป)
                if (pass.length < 6) {
                    Swal.fire({ icon: 'warning', title: 'รหัสสั้นเกินไป', text: 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร' });
                    return;
                }

                // ตรวจสอบรหัสผ่าน (ภาษาอังกฤษ ตัวเลข และอักษรพิเศษบางตัวเท่านั้น)
                const passRegex = /^[a-zA-Z0-9!@#$%^&*_-]+$/;
                if (!passRegex.test(pass)) {
                    Swal.fire({ icon: 'error', title: 'รหัสผ่านไม่ถูกต้อง', text: 'รหัสผ่านต้องประกอบด้วยภาษาอังกฤษ, ตัวเลข หรืออักษรพิเศษ (!@#$%^&*_-) เท่านั้น' });
                    return;
                }

                // ตรวจสอบยืนยันรหัสผ่าน
                if (pass !== confirmPass) {
                    Swal.fire({ icon: 'error', title: 'รหัสไม่ตรงกัน', text: 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง' });
                    return;
                }
            }

            // ดึงข้อมูล Token ของ Turnstile ก่อนส่งไปหลังบ้าน
            let turnstileToken = '';
            if (turnstileEnabled) {
                const wId = isLogin ? loginWidgetId : registerWidgetId;
                if (typeof turnstile !== 'undefined' && wId !== null) {
                    turnstileToken = turnstile.getResponse(wId) || (isLogin ? loginTurnstileToken : registerTurnstileToken);
                } else {
                    turnstileToken = isLogin ? loginTurnstileToken : registerTurnstileToken;
                }

                if (!turnstileToken) {
                    const formId = isLogin ? 'loginForm' : 'registerForm';
                    const formElement = document.getElementById(formId);
                    turnstileToken = formElement?.querySelector('[name="cf-turnstile-response"]')?.value || '';
                }

                if (!turnstileToken) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณายืนยันตัวตน',
                        text: 'กรุณากดปุ่ม "รีเฟรชการตรวจสอบ" ด้านล่างกล่องยืนยันตัวตน และยืนยันว่าคุณไม่ใช่หุ่นยนต์ก่อนดำเนินการ'
                    });
                    return;
                }
            }

            const btn = document.getElementById(isLogin ? 'btnLogin' : 'btnRegister');
            const originalText = btn.innerText;
            btn.innerText = 'กำลังประมวลผล...';
            btn.disabled = true;

            try {
                const rememberMeChecked = isLogin && document.getElementById('rememberMe').checked;
                const response = await fetch(`api/${type}.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username: user,
                        password: pass,
                        turnstile_token: turnstileToken,
                        remember_me: rememberMeChecked
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    if (isLogin) {
                        try {
                            if (rememberMeChecked) {
                                localStorage.setItem('ekrom_remember_user', user);
                            } else {
                                localStorage.removeItem('ekrom_remember_user');
                            }
                        } catch (e) {}
                    }
                    const role = data.role || data.user?.role;
                    const targetUrl = (role === 'admin') ? 'admin-dash.php' : 'buyer-dash.php';

                    if (isLogin && data.must_change_password) {
                        promptChangeDefaultPassword(user, pass, targetUrl);
                        return;
                    }

                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, timer: 1500, showConfirmButton: false }).then(() => {
                        if (isLogin) {
                            window.location.href = targetUrl;
                        } else {
                            document.getElementById('registerForm').reset();
                            resetTurnstile('register');
                            toggleForm('login'); 
                            document.getElementById('loginUser').value = user;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: isLogin ? 'เข้าสู่ระบบไม่สำเร็จ' : 'สมัครสมาชิกไม่สำเร็จ',
                        text: data.message
                    });
                    
                    // ล้าง token เดิมที่ถูกใช้งานไปแล้ว เพื่อป้องกันการส่งซ้ำ แต่ไม่ต้อง Reset อัตโนมัติ (ให้ผู้ใช้กดปุ่มรีเฟรชด้วยตนเอง)
                    if (isLogin) {
                        loginTurnstileToken = '';
                        const passInput = document.getElementById('loginPass');
                        if (passInput) {
                            passInput.select();
                        }
                    } else {
                        registerTurnstileToken = '';
                    }
                    const formElement = document.getElementById(isLogin ? 'loginForm' : 'registerForm');
                    if (formElement) {
                        const tokenInput = formElement.querySelector('[name="cf-turnstile-response"]');
                        if (tokenInput) tokenInput.value = '';
                    }
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        async function promptChangeDefaultPassword(username, currentPass, redirectUrl) {
            const { value: formValues } = await Swal.fire({
                title: '🔐 กรุณาเปลี่ยนรหัสผ่านใหม่',
                html: `
                    <div class="text-left text-sm space-y-3 pt-2">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start gap-2 leading-relaxed">
                            <span class="text-base leading-none">⚠️</span>
                            <div>คุณกำลังเข้าสู่ระบบด้วยรหัสผ่านเริ่มต้น <strong>(${escapeHtml(currentPass || 'admin123')})</strong> เพื่อความปลอดภัยของระบบหลังบ้าน กรุณาตั้งรหัสผ่านใหม่ก่อนเริ่มใช้งาน</div>
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
                cancelButtonText: 'ข้ามไปก่อน (ไม่แนะนำ)',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                allowOutsideClick: false,
                preConfirm: async () => {
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
                                old_password: currentPass,
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
                    title: 'เปลี่ยนรหัสผ่านสำเร็จ!',
                    text: 'รหัสผ่านของคุณได้รับการอัปเดตเรียบร้อยแล้ว กำลังเข้าสู่แดชบอร์ด...',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = redirectUrl;
                });
            } else {
                window.location.href = redirectUrl;
            }
        }
    </script>
</body>
</html>
