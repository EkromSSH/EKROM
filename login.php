<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>เข้าสู่ระบบ - EKROM Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    
    <style>body { font-family: 'Anuphan', 'Inter', sans-serif; }
    </style>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="login-page bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white max-w-md w-full rounded-[32px] shadow-2xl p-8 border border-gray-100">
        <div class="flex justify-center items-center gap-3 mb-8 cursor-pointer" onclick="window.location.href='index.php'">
            <div class="w-12 h-12 bg-pink-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">EK</div>
            <span class="font-bold text-2xl tracking-tight text-slate-900 italic">EKROM <span class="text-pink-600">SHOP</span></span>
        </div>

        <div class="flex bg-slate-100 p-1 rounded-xl mb-8">
            <button onclick="toggleForm('login')" id="tabLogin" class="flex-1 py-2 rounded-lg font-bold text-sm bg-white shadow text-pink-600 transition-all">เข้าสู่ระบบ</button>
            <button onclick="toggleForm('register')" id="tabRegister" class="flex-1 py-2 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all">สมัครสมาชิก</button>
        </div>

        <form id="loginForm" class="space-y-5 block" onsubmit="handleAuth(event, 'login')">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" id="loginUser" name="username" autocomplete="username" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">รหัสผ่าน (Password)</label>
            <div class="relative">
                    <input type="password" id="loginPass" name="password" autocomplete="current-password" required class="w-full px-4 py-3 pr-12 rounded-xl bg-slate-50 border border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none transition-all">
                    <button type="button" onclick="togglePassword('loginPass', 'iconLoginPass')" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-pink-600 transition-colors">
                        <svg id="iconLoginPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                <input type="checkbox" id="rememberMe" class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                <span>Remember me</span>
            </label>

            <div class="flex justify-center pt-2">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAEGT6ptkwY3fLerb"></div>
            </div>

            <button type="submit" id="btnLogin" class="w-full bg-pink-600 text-white font-bold py-4 rounded-xl hover:bg-pink-700 transition-all shadow-lg shadow-pink-200 mt-4">เข้าสู่ระบบ</button>
        </form>

        <form id="registerForm" class="space-y-5 hidden" onsubmit="handleAuth(event, 'register')">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">ตั้งชื่อผู้ใช้งาน (Username)</label>
                <input type="text" id="regUser" name="username" autocomplete="username" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">ตั้งรหัสผ่าน (Password)</label>
                <div class="relative">
                    <input type="password" id="regPass" name="new-password" autocomplete="new-password" required class="w-full px-4 py-3 pr-12 rounded-xl bg-slate-50 border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all">
                    <button type="button" onclick="togglePassword('regPass', 'iconRegPass')" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-emerald-600 transition-colors">
                        <svg id="iconRegPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">ยืนยันรหัสผ่าน (Confirm Password)</label>
                <div class="relative">
                    <input type="password" id="regConfirmPass" name="new-password-confirm" autocomplete="new-password" required class="w-full px-4 py-3 pr-12 rounded-xl bg-slate-50 border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all">
                    <button type="button" onclick="togglePassword('regConfirmPass', 'iconRegConfirm')" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-emerald-600 transition-colors">
                        <svg id="iconRegConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-center pt-2">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAEGT6ptkwY3fLerb"></div>
            </div>

            <button type="submit" id="btnRegister" class="w-full bg-emerald-500 text-white font-bold py-4 rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200 mt-4">ยืนยันสมัครสมาชิก</button>
        </form>

    </div>

    <script>
        // ตรวจสอบชื่อผู้ใช้ที่เคยบันทึกไว้ในเบราว์เซอร์ (Remember me)
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const savedUser = localStorage.getItem('ekrom_remember_user');
                if (savedUser) {
                    const userInput = document.getElementById('loginUser');
                    const rememberCheckbox = document.getElementById('rememberMe');
                    if (userInput) userInput.value = savedUser;
                    if (rememberCheckbox) rememberCheckbox.checked = true;
                    const passInput = document.getElementById('loginPass');
                    if (passInput) passInput.focus();
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
            const loginForm = document.getElementById('loginForm');
            const regForm = document.getElementById('registerForm');
            const tabLogin = document.getElementById('tabLogin');
            const tabRegister = document.getElementById('tabRegister');

            if (type === 'login') {
                loginForm.classList.replace('hidden', 'block');
                regForm.classList.replace('block', 'hidden');
                tabLogin.className = "flex-1 py-2 rounded-lg font-bold text-sm bg-white shadow text-pink-600 transition-all";
                tabRegister.className = "flex-1 py-2 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            } else {
                loginForm.classList.replace('block', 'hidden');
                regForm.classList.replace('hidden', 'block');
                tabRegister.className = "flex-1 py-2 rounded-lg font-bold text-sm bg-white shadow text-emerald-600 transition-all";
                tabLogin.className = "flex-1 py-2 rounded-lg font-bold text-sm text-gray-500 hover:text-gray-700 transition-all";
            }

            // 🟢 รีเซ็ต Turnstile เมื่อเปลี่ยนแท็บเพื่อป้องกันบั๊ก
            if (typeof turnstile !== 'undefined') turnstile.reset();
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
            const user = document.getElementById(isLogin ? 'loginUser' : 'regUser').value;
            const pass = document.getElementById(isLogin ? 'loginPass' : 'regPass').value;
            
            // 🟢 ตรวจสอบเฉพาะหน้า "สมัครสมาชิก" เท่านั้น
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

            // 🟢 ดึงข้อมูล Token ของ Turnstile ก่อนส่งไปหลังบ้าน
            const formId = isLogin ? 'loginForm' : 'registerForm';
            const formElement = document.getElementById(formId);
            const turnstileToken = formElement.querySelector('[name="cf-turnstile-response"]')?.value;
            if (!turnstileToken) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณายืนยันตัวตน',
                    text: 'กรุณาติ๊กช่องยืนยันว่าคุณไม่ใช่หุ่นยนต์ก่อนดำเนินการ'
                });
                return;
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
                    // 🟢 แนบ Token ส่งไปพร้อมรหัสผ่าน
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
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message }).then(() => {
                        if (isLogin) {
                            const role = data.role || data.user?.role;
                            if (role === 'admin') {
                                window.location.href = 'admin-dash.php';
                            } else {
                                window.location.href = 'buyer-dash.php';
                            }
                        } else {
                            document.getElementById('registerForm').reset();
                            if (typeof turnstile !== 'undefined') turnstile.reset(); // 🟢 รีเซ็ตกล่องเช็คบอทเมื่อสมัครสำเร็จ
                            toggleForm('login'); 
                            document.getElementById('loginUser').value = user;
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: isLogin ? 'เข้าสู่ระบบไม่สำเร็จ' : 'สมัครสมาชิกไม่สำเร็จ', text: data.message });
                    if (typeof turnstile !== 'undefined') turnstile.reset(); // 🟢 รีเซ็ตกล่องเวลากรอกผิด
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ระบบขัดข้อง', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                if (typeof turnstile !== 'undefined') turnstile.reset(); // 🟢 รีเซ็ตกล่องถ้ามี Error
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
