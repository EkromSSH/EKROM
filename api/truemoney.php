<?php
// api/truemoney.php - TrueMoney Gift Voucher Redeemer for EKROM Shop
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/slipok.php";

/**
 * สกัด Voucher Code จาก URL ซองของขวัญ หรือข้อความ
 */
function extract_truemoney_voucher_code(string $link): ?string {
    $link = trim($link);
    if (empty($link)) {
        return null;
    }
    if (preg_match("/[?&]v=([a-zA-Z0-9]+)/i", $link, $m)) {
        return $m[1];
    }
    if (preg_match("/campaign\/vouchers\/([a-zA-Z0-9]+)/i", $link, $m)) {
        return $m[1];
    }
    if (preg_match("/(?:gift\.truemoney\.com\/campaign\/(?:\?v=)?)([a-zA-Z0-9]+)/i", $link, $m)) {
        return $m[1];
    }
    if (preg_match("/\b([a-zA-Z0-9]{25,60})\b/", $link, $m)) {
        return $m[1];
    }
    if (preg_match("/^[a-zA-Z0-9]{10,60}$/", $link)) {
        return $link;
    }
    return null;
}

/**
 * แลกซองของขวัญ TrueMoney ผ่าน Gateway API (Autozy Gateway)
 * @param string $voucherCode รหัสซองของขวัญ
 * @param string $phoneNumber เบอร์ TrueMoney ของเจ้าของร้าน
 * @return array ["success" => bool, "amount" => float, "sender" => string, "message" => string]
 */
function redeem_truemoney_voucher(string $voucherCode, string $phoneNumber = ''): array {
    $voucherCode = trim($voucherCode);
    $phoneNumber = preg_replace("/[^0-9]/", "", trim($phoneNumber));

    if (empty($voucherCode)) {
        return ["success" => false, "message" => "รหัสซองของขวัญไม่ถูกต้อง"];
    }
    if (empty($phoneNumber) || strlen($phoneNumber) < 9) {
        $slipSettings = get_slip_settings();
        $phoneNumber = preg_replace("/[^0-9]/", "", trim($slipSettings['truemoney_phone'] ?? ($slipSettings['promptpay_number'] ?? '0639231350')));
    }
    if (empty($phoneNumber) || strlen($phoneNumber) < 9) {
        return ["success" => false, "message" => "ยังไม่ได้ตั้งค่าเบอร์ TrueMoney สำหรับรับเงินในระบบ"];
    }

    $url = "https://gateway.autozy.app/api/giftvoucher/" . urlencode($voucherCode) . "/" . urlencode($phoneNumber) . "/";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || !empty($curlErr)) {
        return [
            "success" => false,
            "message" => "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ TrueMoney ได้ (" . ($curlErr ?: "Timeout") . ") กรุณาลองใหม่อีกครั้ง"
        ];
    }

    $json = json_decode($response, true);
    if (!is_array($json)) {
        return [
            "success" => false,
            "message" => "ระบบ TrueMoney ตอบกลับไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง"
        ];
    }

    $status = strtolower((string)($json["status"] ?? ""));
    $code = (int)($json["code"] ?? 0);

    if ($status === "success" || $code === 200) {
        $amount = 0.0;
        if (isset($json["data"]["amount"])) {
            $amount = floatval($json["data"]["amount"]);
        } elseif (isset($json["amount"])) {
            $amount = floatval($json["amount"]);
        }

        if ($amount <= 0) {
            return [
                "success" => false,
                "message" => "ยอดเงินในซองของขวัญต้องมากกว่า 0 บาท"
            ];
        }
        return [
            "success" => true,
            "amount" => $amount,
            "sender" => $json["data"]["name"] ?? ($json["data"]["owner_full_name"] ?? ""),
            "message" => $json["message"] ?? ("เติมเงินสำเร็จ ฿" . number_format($amount, 2))
        ];
    }

    // แปลงข้อความข้อผิดพลาดที่พบบ่อย
    $msg = $json["message"] ?? "";
    if (empty($msg)) {
        switch ($code) {
            case 100: $msg = "ซองของขวัญนี้ถูกใช้งานไปแล้ว"; break;
            case 101: $msg = "ไม่พบซองของขวัญ หรือลิงก์ไม่ถูกต้อง"; break;
            case 102: $msg = "ไม่สามารถรับซองของตนเองได้ (กรุณาใช้คนละเบอร์กับผู้สร้างซอง)"; break;
            case 103: $msg = "ซองนี้มีผู้รับเต็มจำนวนแล้ว"; break;
            case 104: $msg = "ข้อมูลไม่ถูกต้อง"; break;
            case 105: $msg = "ซองของขวัญนี้หมดอายุแล้ว"; break;
            default:  $msg = "ไม่สามารถรับซองของขวัญได้"; break;
        }
    }

    return [
        "success" => false,
        "message" => $msg
    ];
}
