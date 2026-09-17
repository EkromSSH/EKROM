<!DOCTYPE html>
<html lang="th" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>EKROM Shop - บริการ V2Ray VPN ความเร็วสูงระดับ 1Gbps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Anuphan:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
    </style>
    <link rel="stylesheet" href="mobile-fix.css">
</head>
<body class="landing-page bg-slate-50 text-gray-800 antialiased selection:bg-pink-500 selection:text-white">

    <!-- Header with Centered Logo -->
    <header class="landing-header bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 items-center h-16 md:h-20">
                <!-- Left: Nav Links -->
                <div class="flex items-center justify-start">
                    <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold text-slate-600">
                        <a href="#" class="hover:text-pink-600 transition-colors">หน้าแรก</a>
                        <a href="#vpn-specs" class="hover:text-pink-600 transition-colors">คุณสมบัติ</a>
                        <a href="#pricing" class="hover:text-pink-600 transition-colors">ราคา</a>
                    </nav>
                    <a href="#vpn-specs" class="md:hidden text-xs font-bold text-slate-600 hover:text-pink-600 px-3 py-1.5 rounded-lg bg-slate-100 transition-colors">
                        คุณสมบัติ
                    </a>
                </div>

                <!-- Center: Logo (Centered on both Mobile & Desktop) -->
                <div class="flex items-center justify-center">
                    <a href="index.php" class="flex items-center gap-2 sm:gap-2.5 group cursor-pointer select-none">
                        <div class="w-9 h-9 md:w-10 md:h-10 bg-gradient-to-tr from-pink-600 to-rose-500 rounded-xl flex items-center justify-center text-white font-bold text-base md:text-lg shadow-md shadow-pink-200 group-hover:scale-105 transition-transform">
                            EK
                        </div>
                        <span class="font-bold text-lg md:text-xl tracking-tight text-slate-900 italic group-hover:text-pink-600 transition-colors whitespace-nowrap">
                            EKROM <span class="text-pink-600">SHOP</span>
                        </span>
                    </a>
                </div>

                <!-- Right: Header Spacer (Keeps Logo Centered) -->
                <div class="flex items-center justify-end"></div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-16 md:pt-20 md:pb-24">
        <!-- Ambient Glow Circles -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] sm:w-[550px] h-[350px] sm:h-[550px] bg-gradient-to-tr from-pink-200/40 via-rose-100/30 to-transparent rounded-full blur-3xl -z-10 pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            
            <!-- Hero Centered Logo Emblem -->
            <div class="flex flex-col items-center justify-center mb-6">
                <div class="relative group cursor-pointer" onclick="window.location.href='index.php'">
                    <div class="absolute -inset-1.5 bg-gradient-to-r from-pink-600 to-rose-500 rounded-3xl blur-md opacity-35 group-hover:opacity-60 transition duration-500"></div>
                    <div class="relative w-20 h-20 md:w-24 md:h-24 bg-gradient-to-tr from-pink-600 via-rose-500 to-pink-500 rounded-3xl flex items-center justify-center text-white font-black text-3xl md:text-4xl shadow-2xl shadow-pink-300 ring-4 ring-white">
                        EK
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-center">
                    <span class="inline-flex items-center gap-2 bg-pink-50 text-pink-700 px-3.5 py-1.5 rounded-full text-xs font-bold border border-pink-200/60 shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-pink-500"></span>
                        </span>
                        V2Ray WebSocket (WS) ระบบใหม่อัตโนมัติ 100%
                    </span>
                </div>
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.2] mb-5">
                VPN ปลอดภัย คุณภาพสูง<br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-pink-600 via-rose-500 to-pink-500 bg-clip-text text-transparent">เร็ว แรง ระดับ 1Gbps</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-sm sm:text-base text-slate-500 mb-8 max-w-2xl mx-auto leading-relaxed">
                ยกระดับความเร็วและความเป็นส่วนตัว ด้วยเทคโนโลยี V2Ray & Reality บนเซิร์ฟเวอร์ไทยแท้ 100% เชื่อมต่อง่าย เสถียร ไม่สะดุด ทะลุทุกขีดจำกัด
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center max-w-md mx-auto">
                <button onclick="window.location.href='login.php?tab=register'" class="w-full sm:w-auto bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-lg shadow-pink-200 transition-all transform hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center gap-2">
                    <span>🚀 เริ่มต้นใช้งานเลย</span>
                </button>
                <a href="#pricing" class="w-full sm:w-auto bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-7 py-3 rounded-xl font-bold text-sm shadow-xs transition-all flex items-center justify-center gap-2">
                    <span>🏷️ ดูแพ็กเกจราคา</span>
                </a>
            </div>

            <!-- Trust Highlights Grid -->
            <div class="mt-12 pt-8 border-t border-slate-200/60 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 max-w-3xl mx-auto">
                <div class="flex flex-col items-center text-center p-3 rounded-2xl bg-white/70 border border-slate-100 shadow-xs">
                    <span class="text-xl mb-1">⚡</span>
                    <span class="font-bold text-xs sm:text-sm text-slate-800">1000 Mbps</span>
                    <span class="text-[11px] text-slate-400">พอร์ตความเร็วสูง</span>
                </div>
                <div class="flex flex-col items-center text-center p-3 rounded-2xl bg-white/70 border border-slate-100 shadow-xs">
                    <span class="text-xl mb-1">🇹🇭</span>
                    <span class="font-bold text-xs sm:text-sm text-slate-800">Thai Servers</span>
                    <span class="text-[11px] text-slate-400">เซิร์ฟเวอร์ไทยแท้</span>
                </div>
                <div class="flex flex-col items-center text-center p-3 rounded-2xl bg-white/70 border border-slate-100 shadow-xs">
                    <span class="text-xl mb-1">🤖</span>
                    <span class="font-bold text-xs sm:text-sm text-slate-800">Auto 100%</span>
                    <span class="text-[11px] text-slate-400">รับข้อมูลทันที 24 ชม.</span>
                </div>
                <div class="flex flex-col items-center text-center p-3 rounded-2xl bg-white/70 border border-slate-100 shadow-xs">
                    <span class="text-xl mb-1">🛡️</span>
                    <span class="font-bold text-xs sm:text-sm text-slate-800">Security</span>
                    <span class="text-[11px] text-slate-400">V2Ray WS ปลอดภัย</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="vpn-specs" class="py-16 md:py-24 bg-white relative border-y border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-3 py-1 rounded-full border border-pink-100">ฟีเจอร์เด่น</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-3 mb-2">ทำไมผู้ใช้จึงไว้วางใจ EKROM VPN</h2>
                <p class="text-xs sm:text-sm text-slate-500">โครงสร้างพื้นฐานระดับพรีเมียม เพื่อประสบการณ์อินเทอร์เน็ตที่ไร้ขีดจำกัด</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1: Thailand Data Center -->
                <div class="p-7 sm:p-8 rounded-3xl bg-slate-50/80 border border-slate-200/70 hover:shadow-xl hover:border-pink-200 transition-all duration-300 group text-center flex flex-col items-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-tr from-pink-600 via-rose-500 to-pink-500 text-white rounded-2xl sm:rounded-3xl flex items-center justify-center text-3xl sm:text-4xl mb-5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg shadow-pink-200/70 ring-4 ring-pink-50">
                        🇹🇭
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2.5">Thailand Data Center</h3>
                    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-xs">
                        เซิร์ฟเวอร์ตั้งอยู่ในศูนย์ข้อมูลมาตรฐานสากลในประเทศไทย ค่าความหน่วง (Latency) ต่ำ เหมาะสำหรับการเล่นเกมและสตรีมมิ่ง
                    </p>
                </div>

                <!-- Feature 2: 1000 Mbps Port Speed -->
                <div class="p-7 sm:p-8 rounded-3xl bg-slate-50/80 border border-slate-200/70 hover:shadow-xl hover:border-emerald-200 transition-all duration-300 group text-center flex flex-col items-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-tr from-emerald-500 via-teal-500 to-emerald-600 text-white rounded-2xl sm:rounded-3xl flex items-center justify-center text-3xl sm:text-4xl mb-5 group-hover:scale-110 group-hover:-rotate-3 transition-all duration-300 shadow-lg shadow-emerald-200/70 ring-4 ring-emerald-50">
                        ⚡
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2.5">1000 Mbps Port Speed</h3>
                    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-xs">
                        พอร์ตเชื่อมต่อ 1 Gbps ต่อเครื่อง สปีดวิ่งเต็มที่ ไม่บีบความเร็ว ดูวิดีโอ 4K โหลดไฟล์ และดาวน์โหลดได้ลื่นไหลไม่มีสะดุด
                    </p>
                </div>

                <!-- Feature 3: V2Ray WebSocket & Reality -->
                <div class="p-7 sm:p-8 rounded-3xl bg-slate-50/80 border border-slate-200/70 hover:shadow-xl hover:border-indigo-200 transition-all duration-300 group text-center flex flex-col items-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-tr from-indigo-600 via-blue-600 to-indigo-700 text-white rounded-2xl sm:rounded-3xl flex items-center justify-center text-3xl sm:text-4xl mb-5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg shadow-indigo-200/70 ring-4 ring-indigo-50">
                        🛡️
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-2.5">V2Ray WebSocket & Reality</h3>
                    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-xs">
                        โปรโตคอลความปลอดภัยขั้นสูง เลียนแบบทราฟฟิก HTTPS ทะลุการบล็อกเครือข่าย ปลอดภัยจากการดักจับข้อมูล 100%
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-16 md:py-24 bg-gradient-to-b from-slate-50 to-white relative">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-3 py-1 rounded-full border border-pink-100">แพ็กเกจสุดคุ้ม</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-3 mb-2">เลือกแพ็กเกจที่เหมาะกับคุณ</h2>
                <p class="text-xs sm:text-sm text-slate-500">สมัครสมาชิก เติมเงิน และเปิดใช้งานได้เองอัตโนมัติภายใน 1 นาที</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <!-- Tier 1: 7 วัน -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase">ทดลองใช้งาน</div>
                        <h3 class="text-lg font-bold text-slate-900 mt-1 mb-3">Starter 7 วัน</h3>
                        <div class="flex items-baseline gap-1 mb-4">
                            <span class="text-3xl font-extrabold text-slate-900">฿15</span>
                            <span class="text-xs text-slate-400">/ 7 วัน</span>
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-600 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> ความเร็วสูงสุด 1000 Mbps
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> เซิร์ฟเวอร์ไทย V2Ray WS
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> จัดการผ่าน Dashboard
                            </li>
                        </ul>
                    </div>
                    <button onclick="window.location.href='login.php'" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold py-2.5 rounded-xl transition-all">
                        เลือกแพ็กเกจนี้
                    </button>
                </div>

                <!-- Tier 2: 30 วัน (Recommended) -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border-2 border-pink-500 shadow-xl shadow-pink-100 relative flex flex-col justify-between transform sm:-translate-y-2">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-pink-600 to-rose-600 text-white text-[11px] font-bold px-3.5 py-1 rounded-full shadow-md uppercase tracking-wider">
                        ★ ยอดนิยมสูงสุด
                    </div>
                    <div>
                        <div class="text-xs font-bold text-pink-600 uppercase mt-1">ใช้งานรายเดือน</div>
                        <h3 class="text-lg font-bold text-slate-900 mt-1 mb-3">Premium 30 วัน</h3>
                        <div class="flex items-baseline gap-1 mb-4">
                            <span class="text-4xl font-black text-slate-900">฿50</span>
                            <span class="text-xs text-slate-400">/ 30 วัน</span>
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-600 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> ความเร็วไม่จำกัด 1000 Mbps
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> ทะลุบล็อก ปลอดภัย 100%
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> ระบบอัตโนมัติ รับ Config ทันที
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> รองรับทุกอุปกรณ์ (iOS, Android, PC)
                            </li>
                        </ul>
                    </div>
                    <button onclick="window.location.href='login.php'" class="w-full bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white text-xs sm:text-sm font-bold py-3 rounded-xl shadow-md shadow-pink-200 transition-all active:scale-[0.98]">
                        สมัครและเริ่มใช้งาน
                    </button>
                </div>

                <!-- Tier 3: Daily Pass -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase">ใช้งานระยะสั้น</div>
                        <h3 class="text-lg font-bold text-slate-900 mt-1 mb-3">Daily Pass 1 วัน</h3>
                        <div class="flex items-baseline gap-1 mb-4">
                            <span class="text-3xl font-extrabold text-slate-900">฿5</span>
                            <span class="text-xs text-slate-400">/ 1 วัน</span>
                        </div>
                        <ul class="space-y-2.5 text-xs text-slate-600 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> เหมาะสำหรับทดสอบความเร็ว
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> เชื่อมต่อได้ทันที
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span> เซิร์ฟเวอร์ไทย Port 1Gbps
                            </li>
                        </ul>
                    </div>
                    <button onclick="window.location.href='login.php'" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold py-2.5 rounded-xl transition-all">
                        เลือกแพ็กเกจนี้
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer with Centered Logo -->
    <footer class="bg-white border-t border-slate-100 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
            <a href="index.php" class="flex items-center gap-2.5 mb-3 select-none group">
                <div class="w-8 h-8 bg-pink-600 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-md shadow-pink-200 group-hover:scale-105 transition-transform">
                    EK
                </div>
                <span class="font-bold text-lg tracking-tight text-slate-900 italic">
                    EKROM <span class="text-pink-600">SHOP</span>
                </span>
            </a>
            <p class="text-xs text-slate-400 max-w-sm mb-4">
                ระบบจำหน่ายและบริหารจัดการ VPN คุณภาพสูง รวดเร็ว เสถียร ปลอดภัย ด้วยระบบอัตโนมัติตลอด 24 ชั่วโมง
            </p>
            <div class="flex items-center gap-4 text-xs font-semibold text-slate-500 mb-4">
                <a href="#" class="hover:text-pink-600 transition-colors">หน้าแรก</a>
                <span>•</span>
                <a href="#vpn-specs" class="hover:text-pink-600 transition-colors">คุณสมบัติ</a>
                <span>•</span>
                <a href="#pricing" class="hover:text-pink-600 transition-colors">แพ็กเกจราคา</a>
                <span>•</span>
                <a href="login.php" class="hover:text-pink-600 transition-colors">เข้าสู่ระบบ</a>
            </div>
            <p class="text-slate-400 text-[11px]">&copy; 2026 EKROM Shop. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>