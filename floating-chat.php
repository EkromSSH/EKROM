<?php
// Floating Facebook Group Chat Component
$floatingFbUrl = function_exists('get_facebook_chat_group_url') ? get_facebook_chat_group_url() : '';
$floatingContact = function_exists('get_contact_settings') ? get_contact_settings() : [];
$floatingFbName = !empty($floatingContact['messenger_group_name']) 
    ? $floatingContact['messenger_group_name'] 
    : (!empty($floatingContact['facebook_page_name']) ? $floatingContact['facebook_page_name'] : 'แชทกลุ่ม Facebook');
?>
<!-- Floating Facebook / Messenger Chat Button -->
<div id="floatingFbChatContainer" class="fixed z-40 flex items-center group pointer-events-auto" style="bottom: max(20px, calc(16px + env(safe-area-inset-bottom, 0px))); right: 20px;">
    <!-- Tooltip Label for Desktop -->
    <div class="hidden sm:flex items-center gap-2 bg-slate-900/90 text-white text-xs font-bold py-2 px-3.5 rounded-full mr-3 shadow-xl opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 pointer-events-none whitespace-nowrap border border-white/10 backdrop-blur-md">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span><?= htmlspecialchars($floatingFbName) ?></span>
    </div>

    <!-- Floating Chat Button -->
    <a href="<?= !empty($floatingFbUrl) ? htmlspecialchars($floatingFbUrl) : 'javascript:void(0)' ?>" 
       <?= !empty($floatingFbUrl) ? 'target="_blank" rel="noopener noreferrer"' : 'onclick="openFloatingFbChatPrompt()"' ?>
       class="relative w-14 h-14 sm:w-15 sm:h-15 rounded-full flex items-center justify-center text-white shadow-xl shadow-blue-500/35 border-2 border-white/90 transition-all duration-300 hover:scale-110 active:scale-95 hover:shadow-2xl hover:shadow-blue-500/50 hover:-translate-y-1 cursor-pointer focus:outline-none"
       style="background: linear-gradient(135deg, #00B2FF 0%, #006AFF 50%, #7B3FE4 100%);"
       title="<?= htmlspecialchars($floatingFbName) ?>"
       aria-label="<?= htmlspecialchars($floatingFbName) ?>">
       
        <!-- Pulse Glow Ring -->
        <span class="absolute -inset-1 rounded-full bg-blue-500/30 animate-pulse pointer-events-none"></span>

        <!-- Online Green Status Dot -->
        <span class="absolute top-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full flex items-center justify-center shadow-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
        </span>

        <!-- Messenger Chat Bubble SVG Icon -->
        <svg class="w-7 h-7 sm:w-8 sm:h-8 drop-shadow-md text-white transition-transform duration-300 group-hover:scale-105" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.91 1.455 5.512 3.736 7.175V22l3.415-1.874c.91.252 1.873.389 2.849.389 5.523 0 10-4.145 10-9.257C22 6.145 17.523 2 12 2zm1.066 12.443l-2.613-2.787-5.099 2.787 5.61-5.957 2.679 2.787 5.034-2.787-5.611 5.957z"/>
        </svg>
    </a>
</div>

<script>
if (typeof openFloatingFbChatPrompt === 'undefined') {
    function openFloatingFbChatPrompt() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'แชทกลุ่ม Facebook',
                text: 'ขณะนี้ทางร้านยังไม่ได้ระบุลิงก์แชทกลุ่ม Facebook ในระบบหลังบ้าน สามารถติดต่อแอดมินผ่านเมนูติดต่อเราได้ครับ',
                confirmButtonText: 'ไปหน้าติดต่อเรา',
                showCancelButton: true,
                cancelButtonText: 'ปิด',
                confirmButtonColor: '#0084FF'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'contact.php';
                }
            });
        } else {
            alert('ขณะนี้ทางร้านยังไม่ได้ระบุลิงก์แชทกลุ่ม Facebook ครับ สามารถติดต่อผ่านหน้าติดต่อเรา');
        }
    }
}
</script>
