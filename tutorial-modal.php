<!-- Modal คู่มือสอนเช่าไฟล์ & วิธีใช้งาน (Step-by-Step Interactive Guide) -->
<div id="tutorialModal" onclick="if(event.target === this) closeTutorialModal()" class="fixed inset-0 bg-slate-900/65 backdrop-blur-sm z-[110] hidden items-center justify-center p-3 sm:p-4 opacity-0 transition-opacity duration-300">
    <div id="tutorialModalContent" class="bg-white w-full max-w-2xl rounded-[32px] shadow-2xl flex flex-col max-h-[92vh] max-h-[92dvh] overflow-hidden transform scale-95 transition-transform duration-300 border border-slate-100">
        
        <!-- Header -->
        <div class="p-5 sm:p-6 bg-gradient-to-r from-pink-500/10 via-rose-500/10 to-indigo-500/10 border-b border-gray-100 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3 min-w-0 pr-2">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white flex items-center justify-center text-xl shadow-md shadow-pink-500/25 shrink-0">
                    📖
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-xl font-black text-slate-900 truncate">วิธีเช่าไฟล์และใช้งาน VPN</h2>
                        <span id="tutStepBadge" class="text-[10px] font-extrabold bg-pink-100 text-pink-700 border border-pink-200 px-2 py-0.5 rounded-full shrink-0">ขั้นตอนที่ 1 / 4</span>
                    </div>
                    <p class="text-xs text-slate-500 truncate mt-0.5 font-medium">ทำตามขั้นตอนง่ายๆ รับไฟล์พร้อมเชื่อมต่อเน็ตได้ทันที</p>
                </div>
            </div>
            <button type="button" onclick="closeTutorialModal()" class="w-10 h-10 bg-slate-50 hover:bg-slate-100 rounded-full flex items-center justify-center text-gray-400 hover:text-slate-900 transition-all shrink-0 cursor-pointer text-lg font-bold">✕</button>
        </div>

        <!-- Stepper Navigation Bar -->
        <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-100 shrink-0">
            <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                <!-- Step Tab 1 -->
                <button type="button" onclick="setTutorialStep(1)" id="tutTab1" class="tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-white text-pink-600 font-bold border border-pink-200 shadow-xs">
                    <span class="w-5 h-5 rounded-full bg-pink-600 text-white text-[11px] font-black flex items-center justify-center">1</span>
                    <span class="text-[10px] sm:text-xs truncate max-w-full">1. เติมเงิน</span>
                </button>
                <!-- Step Tab 2 -->
                <button type="button" onclick="setTutorialStep(2)" id="tutTab2" class="tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-transparent text-slate-500 font-semibold hover:bg-white/80">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 text-[11px] font-black flex items-center justify-center">2</span>
                    <span class="text-[10px] sm:text-xs truncate max-w-full">2. เลือกโปร</span>
                </button>
                <!-- Step Tab 3 -->
                <button type="button" onclick="setTutorialStep(3)" id="tutTab3" class="tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-transparent text-slate-500 font-semibold hover:bg-white/80">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 text-[11px] font-black flex items-center justify-center">3</span>
                    <span class="text-[10px] sm:text-xs truncate max-w-full">3. กดสั่งซื้อ</span>
                </button>
                <!-- Step Tab 4 -->
                <button type="button" onclick="setTutorialStep(4)" id="tutTab4" class="tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-transparent text-slate-500 font-semibold hover:bg-white/80">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 text-[11px] font-black flex items-center justify-center">4</span>
                    <span class="text-[10px] sm:text-xs truncate max-w-full">4. ใส่ในแอป</span>
                </button>
            </div>
        </div>

        <!-- Modal Body / Step Content (Scrollable) -->
        <div class="p-5 sm:p-7 overflow-y-auto flex-grow hide-scroll space-y-6">

            <!-- ================= STEP 1: เติมเงินเข้ากระเป๋าเว็บ ================= -->
            <div id="tutStep1" class="tut-step-content space-y-5 animate-in fade-in duration-200">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 font-black text-sm flex items-center justify-center shrink-0">1</span>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">ขั้นตอนที่ 1: เติมเงินเข้ากระเป๋าเว็บ (ขั้นต่ำ 1 บาท)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                            ระบบทำงานแบบอัตโนมัติ 24 ชั่วโมง เติมเงินเสร็จยอดเงินจะเข้าสู่บัญชีของคุณทันที
                        </p>
                    </div>
                </div>

                <!-- Visual Mockup Card for Step 1 -->
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white rounded-3xl p-5 sm:p-6 shadow-lg border border-slate-700 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-emerald-500/10 rounded-full blur-2xl"></div>
                    <div class="flex flex-col sm:flex-row items-center gap-5 relative z-10">
                        <div class="w-32 h-32 sm:w-36 sm:h-36 bg-white p-2.5 rounded-2xl shadow-md shrink-0 flex flex-col items-center justify-center border border-slate-200">
                            <div class="w-full text-center pb-1 border-b border-gray-100 mb-1">
                                <span class="text-[9px] font-black text-blue-800 tracking-wider">PROMPTPAY QR</span>
                            </div>
                            <div class="w-20 h-20 bg-slate-900 rounded-lg flex items-center justify-center text-white text-xs font-mono">
                                <span>[ QR Code ]</span>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-600 mt-1">สแกนจ่ายได้ทุกธนาคาร</span>
                        </div>
                        <div class="space-y-2 text-center sm:text-left flex-grow">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                                <span>⚡</span> ยอดเงินเข้าทันทีใน 1-3 วินาที
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-white">รองรับทั้ง พร้อมเพย์ และ ซอง TrueMoney</h4>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                1. กดเมนู <b>"💰 เติมเงินเข้าเว็บ"</b><br>
                                2. พิมพ์จำนวนเงินที่ต้องการ ➔ กดยืนยันเพื่อรับ QR Code<br>
                                3. เปิดแอปธนาคารสแกนจ่าย ยอดเงินจะเข้าทันทีอัตโนมัติ ไม่ต้องส่งสลิป!
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs text-emerald-800 flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2">
                        <span>💡</span>
                        <span><b>คำแนะนำ:</b> เติมเงินตามราคาแพ็กเกจที่คุณต้องการ เช่น 50฿, 100฿ หรือ 150฿ ได้เลยครับ</span>
                    </span>
                    <a href="topup.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-xl transition-all shadow-xs shrink-0 whitespace-nowrap">
                        ไปหน้าเติมเงิน →
                    </a>
                </div>
            </div>

            <!-- ================= STEP 2: เลือกแพ็กเกจ VPN ================= -->
            <div id="tutStep2" class="tut-step-content space-y-5 hidden animate-in fade-in duration-200">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 font-black text-sm flex items-center justify-center shrink-0">2</span>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">ขั้นตอนที่ 2: เลือกค่ายซิมและแพ็กเกจที่ต้องการ</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                            เลือกเซิร์ฟเวอร์ที่ตรงกับค่ายซิมและโปรโมชันที่คุณใช้งานอยู่
                        </p>
                    </div>
                </div>

                <!-- Visual Mockup Card for Step 2 -->
                <div class="bg-white rounded-3xl p-5 border-2 border-slate-100 shadow-md space-y-4">
                    <!-- Carrier Filter Mockup -->
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">1. เลือกค่ายซิมของคุณ</div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="p-3 rounded-2xl border-2 border-emerald-500 bg-emerald-50/70 text-center font-bold text-xs text-emerald-800 flex items-center justify-center gap-2 shadow-xs">
                                <span>🟢</span> <span>AIS (เอไอเอส)</span>
                            </div>
                            <div class="p-3 rounded-2xl border-2 border-rose-500 bg-rose-50/70 text-center font-bold text-xs text-rose-800 flex items-center justify-center gap-2 shadow-xs">
                                <span>🔴</span> <span>TRUE (ทรูมูฟ)</span>
                            </div>
                            <div class="p-3 rounded-2xl border-2 border-sky-500 bg-sky-50/70 text-center font-bold text-xs text-sky-800 flex items-center justify-center gap-2 shadow-xs">
                                <span>🔵</span> <span>DTAC (ดีแทค)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Server Plan Card Mockup -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/80 space-y-3">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">⚡</span>
                                <div>
                                    <div class="font-extrabold text-slate-900 text-sm">AIS PLAY High-Speed 1Gbps</div>
                                    <div class="text-[11px] text-slate-500">โปรโตคอล VLESS WebSocket พอร์ตตรง</div>
                                </div>
                            </div>
                            <span class="text-xs font-black bg-pink-50 text-pink-700 border border-pink-200 px-2 py-0.5 rounded-md">ยอดนิยม 🔥</span>
                        </div>
                        <div class="text-[11px] font-bold text-slate-600">เลือกระยะเวลาการใช้งาน:</div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="p-2 rounded-xl bg-white border border-slate-200 text-center text-xs font-bold text-slate-700">7 วัน (30฿)</div>
                            <div class="p-2 rounded-xl bg-white border border-slate-200 text-center text-xs font-bold text-slate-700">15 วัน (60฿)</div>
                            <div class="p-2 rounded-xl bg-pink-600 text-white font-black text-center text-xs shadow-xs">30 วัน (100฿) ✓</div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-center gap-2.5">
                    <span class="text-base shrink-0">💡</span>
                    <span><b>แนะนำ:</b> หากไม่แน่ใจว่าซิมของคุณใช้โปรอะไร สามารถทักสอบถามแอดมินที่ปุ่มแชทมุมขวาล่างได้เลยครับ</span>
                </div>
            </div>

            <!-- ================= STEP 3: กดยืนยันการสั่งซื้อ ================= -->
            <div id="tutStep3" class="tut-step-content space-y-5 hidden animate-in fade-in duration-200">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 font-black text-sm flex items-center justify-center shrink-0">3</span>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">ขั้นตอนที่ 3: สั่งซื้อและรับไฟล์อัตโนมัติทันที</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                            ระบบจะตัดเงินจากยอดคงเหลือในกระเป๋า และสร้างบัญชี VPN ให้อัตโนมัติทันที
                        </p>
                    </div>
                </div>

                <!-- Visual Mockup Card for Step 3 -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-3xl p-5 sm:p-6 border border-slate-200 space-y-4 shadow-sm">
                    <div class="text-center pb-3 border-b border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center text-2xl mx-auto mb-2 shadow-xs">
                            🛒
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-base">ยืนยันการเช่าไฟล์ VPN</h4>
                        <p class="text-xs text-slate-500">ตรวจสอบรายละเอียดก่อนกดยืนยัน</p>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200 space-y-2.5 text-xs">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>เซิร์ฟเวอร์ที่เลือก:</span>
                            <span class="font-bold text-slate-900">AIS PLAY VIP 1Gbps</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>ระยะเวลาใช้งาน:</span>
                            <span class="font-bold text-slate-900">30 วัน (1 เดือน)</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>ความเร็ว & โควต้า:</span>
                            <span class="font-bold text-emerald-600">1Gbps / ไม่จำกัด GB</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-100 text-sm font-black text-slate-900">
                            <span>ยอดที่ต้องชำระ:</span>
                            <span class="text-pink-600 text-base font-mono">฿100.00</span>
                        </div>
                    </div>

                    <div class="w-full bg-gradient-to-r from-pink-600 to-rose-600 text-white py-3.5 rounded-2xl font-black text-sm text-center shadow-md shadow-pink-600/25 flex items-center justify-center gap-2">
                        <span>⚡</span>
                        <span>กดยืนยันคำสั่งซื้อ (รับไฟล์ทันทีใน 1 วิ)</span>
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 text-xs text-emerald-900 flex items-center gap-2.5">
                    <span class="text-base shrink-0">✅</span>
                    <span><b>การันตี:</b> เมื่อกดยืนยันแล้ว ระบบจะพาคุณไปที่หน้าดูไฟล์ทันที พร้อมคัดลอกโค้ดไปใช้งานได้เลยครับ</span>
                </div>
            </div>

            <!-- ================= STEP 4: นำเข้าโค้ด & เชื่อมต่อ ================= -->
            <div id="tutStep4" class="tut-step-content space-y-5 hidden animate-in fade-in duration-200">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 font-black text-sm flex items-center justify-center shrink-0">4</span>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">ขั้นตอนที่ 4: คัดลอกโค้ดไปใส่ในแอป VPN</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-relaxed">
                            คัดลอกโค้ดคอนฟิกจากหน้าเว็บ แล้วนำไปวางในแอปเพื่อเชื่อมต่อเล่นเน็ต
                        </p>
                    </div>
                </div>

                <!-- 2-Platform Guide Cards: Android vs iOS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Android Card -->
                    <div class="bg-white rounded-2xl p-4 border-2 border-emerald-200 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🤖</span>
                                <h4 class="font-extrabold text-slate-900 text-sm">สำหรับ Android</h4>
                            </div>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">v2rayNG / NPV</span>
                        </div>
                        <ol class="text-xs text-slate-600 space-y-1.5 list-decimal list-inside leading-relaxed">
                            <li>กดปุ่ม <b>"คัดลอกโค้ด"</b> จากหน้าเว็บ</li>
                            <li>เปิดแอป <b>v2rayNG</b> หรือ <b>NPV Tunnel</b></li>
                            <li>กดเครื่องหมาย <b>+</b> มุมบนขวา</li>
                            <li>เลือก <b>"นำเข้าจากคลิปบอร์ด" (Import from clipboard)</b></li>
                            <li>กดปุ่มวงกลมด้านล่างเพื่อเชื่อมต่อ (ติดสีเขียว 🟢)</li>
                        </ol>
                        <button type="button" onclick="showGraphicGuide('npv')" class="w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold py-2 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🖼️</span> ดูภาพตัวอย่างวิธีใส่ NPV Tunnel
                        </button>
                    </div>

                    <!-- iOS Card -->
                    <div class="bg-white rounded-2xl p-4 border-2 border-indigo-200 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🍏</span>
                                <h4 class="font-extrabold text-slate-900 text-sm">สำหรับ iOS (iPhone/iPad)</h4>
                            </div>
                            <span class="text-[10px] bg-indigo-100 text-indigo-800 font-bold px-2 py-0.5 rounded-md">Shadowrocket / V2Box</span>
                        </div>
                        <ol class="text-xs text-slate-600 space-y-1.5 list-decimal list-inside leading-relaxed">
                            <li>กดปุ่ม <b>"คัดลอกโค้ด"</b> จากหน้าเว็บ</li>
                            <li>เปิดแอป <b>V2Box</b> หรือ <b>Shadowrocket</b></li>
                            <li>แอปจะตรวจพบโค้ดและถามให้นำเข้าทันที (กดตกลง)</li>
                            <li>หากไม่ขึ้น ให้กดปุ่ม <b>+</b> แล้วเลือก Import</li>
                            <li>กดสวิตช์เปิดเชื่อมต่อด้านบน เพื่อเริ่มใช้งาน</li>
                        </ol>
                        <button type="button" onclick="showGraphicGuide('v2box')" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 text-xs font-bold py-2 rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>🖼️</span> ดูภาพตัวอย่างวิธีใส่ V2Box
                        </button>
                    </div>
                </div>

                <!-- Graphic Guide Preview Container (Collapsible) -->
                <div id="graphicGuideBox" class="hidden bg-slate-900 rounded-3xl p-4 sm:p-5 text-white space-y-3 transition-all duration-300">
                    <div class="flex justify-between items-center">
                        <h4 id="graphicGuideTitle" class="font-bold text-sm text-pink-400 flex items-center gap-2">
                            <span>🖼️</span> ภาพประกอบวิธีใส่ไฟล์ในแอป
                        </h4>
                        <button type="button" onclick="document.getElementById('graphicGuideBox').classList.add('hidden')" class="text-xs text-slate-400 hover:text-white bg-slate-800 px-2.5 py-1 rounded-lg">ปิดรูปภาพ ✕</button>
                    </div>
                    <div class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 flex justify-center p-2">
                        <img id="graphicGuideImg" src="images/guide_v2box.png" alt="วิธีใช้งาน" class="max-h-[350px] w-auto object-contain rounded-xl shadow-lg">
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Actions & Controls -->
        <div class="p-4 sm:p-5 bg-slate-50 border-t border-gray-100 flex items-center justify-between shrink-0 gap-3">
            <button type="button" onclick="prevTutorialStep()" id="btnTutPrev" class="px-4 sm:px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 text-xs sm:text-sm font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-1.5">
                <span>←</span> <span>ย้อนกลับ</span>
            </button>

            <!-- Dots Indicator -->
            <div class="flex items-center gap-1.5">
                <span id="tutDot1" class="w-2.5 h-2.5 rounded-full bg-pink-600 transition-all"></span>
                <span id="tutDot2" class="w-2 h-2 rounded-full bg-slate-300 transition-all"></span>
                <span id="tutDot3" class="w-2 h-2 rounded-full bg-slate-300 transition-all"></span>
                <span id="tutDot4" class="w-2 h-2 rounded-full bg-slate-300 transition-all"></span>
            </div>

            <button type="button" onclick="nextTutorialStep()" id="btnTutNext" class="px-5 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white text-xs sm:text-sm font-bold transition-all shadow-md shadow-pink-600/25 flex items-center gap-1.5 cursor-pointer">
                <span id="btnTutNextText">ถัดไป</span> <span>→</span>
            </button>
        </div>

    </div>
</div>

<script>
let currentTutStep = 1;
const totalTutSteps = 4;

function openTutorialModal(step = 1) {
    const modal = document.getElementById('tutorialModal');
    const content = document.getElementById('tutorialModalContent');
    if (!modal) return;
    
    document.body.classList.add('modal-open');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        if (content) content.classList.remove('scale-95');
    }, 10);
    setTutorialStep(step);
}

function closeTutorialModal() {
    const modal = document.getElementById('tutorialModal');
    const content = document.getElementById('tutorialModalContent');
    if (!modal) return;
    
    modal.classList.add('opacity-0');
    if (content) content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        if (!document.getElementById('buyModal')?.classList.contains('flex') && !document.getElementById('backupModal')?.classList.contains('modal-active')) {
            document.body.classList.remove('modal-open');
        }
    }, 280);
}

function setTutorialStep(step) {
    currentTutStep = Math.max(1, Math.min(totalTutSteps, step));
    
    // Update contents
    for (let i = 1; i <= totalTutSteps; i++) {
        const stepEl = document.getElementById(`tutStep${i}`);
        const tabEl = document.getElementById(`tutTab${i}`);
        const dotEl = document.getElementById(`tutDot${i}`);
        
        if (stepEl) {
            if (i === currentTutStep) {
                stepEl.classList.remove('hidden');
            } else {
                stepEl.classList.add('hidden');
            }
        }
        
        if (tabEl) {
            const badgeSpan = tabEl.querySelector('span:first-child');
            if (i === currentTutStep) {
                tabEl.className = 'tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-white text-pink-600 font-bold border border-pink-200 shadow-xs';
                if (badgeSpan) badgeSpan.className = 'w-5 h-5 rounded-full bg-pink-600 text-white text-[11px] font-black flex items-center justify-center';
            } else {
                tabEl.className = 'tut-tab-btn py-2 px-1 sm:px-2 rounded-xl text-center transition-all flex flex-col items-center justify-center gap-1 cursor-pointer bg-transparent text-slate-500 font-semibold hover:bg-white/80';
                if (badgeSpan) badgeSpan.className = 'w-5 h-5 rounded-full bg-slate-200 text-slate-600 text-[11px] font-black flex items-center justify-center';
            }
        }
        
        if (dotEl) {
            if (i === currentTutStep) {
                dotEl.className = 'w-2.5 h-2.5 rounded-full bg-pink-600 transition-all scale-125';
            } else {
                dotEl.className = 'w-2 h-2 rounded-full bg-slate-300 transition-all';
            }
        }
    }
    
    // Update badge & buttons
    const badge = document.getElementById('tutStepBadge');
    if (badge) badge.innerText = `ขั้นตอนที่ ${currentTutStep} / ${totalTutSteps}`;
    
    const prevBtn = document.getElementById('btnTutPrev');
    if (prevBtn) prevBtn.disabled = (currentTutStep === 1);
    
    const nextBtnText = document.getElementById('btnTutNextText');
    if (nextBtnText) {
        if (currentTutStep === totalTutSteps) {
            nextBtnText.innerText = '🛒 ไปที่ร้านค้า';
        } else {
            nextBtnText.innerText = 'ถัดไป';
        }
    }
}

function nextTutorialStep() {
    if (currentTutStep < totalTutSteps) {
        setTutorialStep(currentTutStep + 1);
    } else {
        closeTutorialModal();
        if (window.location.pathname.includes('store.php')) {
            window.scrollTo({ top: 300, behavior: 'smooth' });
        } else {
            window.location.href = 'store.php';
        }
    }
}

function prevTutorialStep() {
    if (currentTutStep > 1) {
        setTutorialStep(currentTutStep - 1);
    }
}

function showGraphicGuide(app) {
    const box = document.getElementById('graphicGuideBox');
    const img = document.getElementById('graphicGuideImg');
    const title = document.getElementById('graphicGuideTitle');
    if (!box || !img) return;
    
    if (app === 'v2box') {
        img.src = 'images/guide_v2box.png';
        if (title) title.innerHTML = '<span>🍏</span> วิธีใส่ไฟล์ในแอป V2Box (สำหรับ iOS)';
    } else {
        img.src = 'images/guide_npv.png';
        if (title) title.innerHTML = '<span>🤖</span> วิธีใส่ไฟล์ในแอป NPV Tunnel (สำหรับ Android)';
    }
    box.classList.remove('hidden');
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('tutorialModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeTutorialModal();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (new URLSearchParams(window.location.search).get('tutorial') === '1' || window.location.hash === '#tutorial') {
        setTimeout(() => {
            openTutorialModal();
        }, 300);
    }
});
</script>
