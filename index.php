<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>EKROM Shop - บริการ V2Ray VPN คุณภาพสูง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Anuphan:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Anuphan', 'Inter', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        .gradient-text { background: linear-gradient(90deg, #2563eb, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    </style>
    <link rel=stylesheet href=mobile-fix.css>
</head>
<body class="landing-page bg-slate-50 text-gray-800 antialiased">

    <header class="landing-header bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href='index.php'">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-200">EK</div>
                    <span class="font-bold text-xl md:text-2xl tracking-tight text-slate-900 italic">EKROM <span class="text-blue-600">SHOP</span></span>
                </div>
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-slate-600 hover:text-blue-600 text-sm font-bold transition-colors">หน้าแรก</a>
                    <a href="#vpn-specs" class="text-slate-600 hover:text-blue-600 text-sm font-bold transition-colors">คุณสมบัติ</a>
                    <a href="#pricing" class="text-slate-600 hover:text-blue-600 text-sm font-bold transition-colors">ราคา</a>
                </nav>
                <div class="flex items-center gap-2">
                    <a href="#pricing" class="md:hidden text-xs font-bold text-blue-600 px-3 py-2 rounded-lg bg-blue-50">ราคา</a>
                    <button onclick="window.location.href='login.php'" class="bg-slate-900 hover:bg-slate-800 text-white text-xs md:text-sm px-4 md:px-6 py-2.5 md:py-3 rounded-xl font-bold transition-all hover:shadow-lg shadow-slate-200">เข้าสู่ระบบ</button>
                </div>
            </div>
        </div>
    </header>

    <section class="relative overflow-hidden pt-12 pb-20 md:pt-20 md:pb-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-full text-xs font-bold mb-6 border border-blue-100 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    V2Ray WebSocket (WS) ระบบใหม่ล่าสุด
                </div>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 leading-[1.2] mb-6">
                    VPN ปลอดภัย <span class="gradient-text">เร็ว แรง ระดับ 1Gbps</span>
                </h1>
                <p class="text-sm md:text-base text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                    ยกระดับการใช้งานด้วยเทคโนโลยี V2Ray WS บนเซิร์ฟเวอร์ไทยแท้ 100% เชื่อมต่อเสถียร ทะลุทุกขีดจำกัด
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="#pricing" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-1">เริ่มใช้งานเลย</a>
            </div>
        </div>
    </section>

    <section id="vpn-specs" class="py-16 md:py-20 bg-white relative border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="p-8 rounded-[32px] bg-slate-50 border border-gray-100 hover:shadow-xl hover:border-blue-100 transition-all group">
                    <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-blue-200">🇹🇭</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Thailand Servers</h3>
                    <p class="text-gray-500 text-xs md:text-sm leading-relaxed">เซิร์ฟเวอร์ไทยแท้ ตั้งอยู่ที่ Data Center มาตรฐานสากล เพื่อความหน่วง (Latency) ที่ต่ำที่สุด</p>
                </div>
                <div class="p-8 rounded-[32px] bg-slate-50 border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all group">
                    <div class="w-14 h-14 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-emerald-200">⚡</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">1000 Mbps Speed</h3>
                    <p class="text-gray-500 text-xs md:text-sm leading-relaxed">อินเทอร์เน็ต Port 1Gbps ต่อเครื่อง วิ่งเต็มสปีด รองรับการดูหนัง 4K สตรีมมิ่ง และเล่นเกมได้อย่างไร้รอยต่อ</p>
                </div>
                <div class="p-8 rounded-[32px] bg-slate-50 border border-gray-100 hover:shadow-xl hover:border-slate-300 transition-all group">
                    <div class="w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-slate-200">🛡️</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">V2Ray WS System</h3>
                    <p class="text-gray-500 text-xs md:text-sm leading-relaxed">ใช้โปรโตคอล V2Ray ผ่าน WebSocket ปลอดภัยสูง ทะลุบล็อกได้อย่างมีประสิทธิภาพ</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-16 md:py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-3">เริ่มต้นใช้งานง่ายๆ</h2>
                <p class="text-sm text-gray-500">จัดการทุกอย่างผ่านระบบอัตโนมัติ 100% สะดวก รวดเร็ว</p>
            </div>
            
            <div class="max-w-md mx-auto bg-white rounded-[32px] shadow-lg border-2 border-blue-500 p-8 transform hover:-translate-y-2 transition-all relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-4 py-1 rounded-full text-xs font-bold tracking-wide shadow-md">แพ็กเกจสุดคุ้ม</div>
                <h3 class="text-lg font-bold text-center text-slate-900 mt-2 mb-1">Premium VPN</h3>
                <div class="text-center mb-6">
                    <span class="text-4xl font-bold text-slate-900">฿50</span>
                    <span class="text-gray-500 text-sm font-medium">/ 30 วัน</span>
                </div>
                
                <ul class="space-y-3 mb-8 text-sm text-slate-600">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><strong class="text-slate-900">ระบบอัตโนมัติทั้งหมด</strong> ชำระเงินปุ๊บ รับข้อมูลใช้งานทันที ไม่ต้องรอแอดมิน</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><strong class="text-slate-900">จัดการด้วยตัวเอง</strong> ดูวันหมดอายุและสถานะเซิร์ฟเวอร์ผ่าน Dashboard</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>ใช้งานเซิร์ฟเวอร์ไทย V2Ray WS ความเร็ว 1000 Mbps</span>
                    </li>
                </ul>
                
                <button onclick="window.location.href='login.php'" class="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-slate-200">
                    สมัครสมาชิก / เข้าสู่ระบบ
                </button>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md mb-3">EK</div>
            <p class="text-gray-400 text-xs font-medium">&copy; 2026 EKROM Shop. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>