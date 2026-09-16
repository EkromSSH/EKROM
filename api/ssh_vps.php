<?php
// api/ssh_vps.php - Automated SSH VPS Direct Integration for EKROM Shop
// Handles remote Linux user creation, password management, expiry & quota on VPS

function ssh_vps_exec($server, $cmd, $timeout = 10) {
    $host = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '');
    $port = (int)($server['port'] ?: 22);
    $user = !empty($server['username']) ? trim($server['username']) : 'root';
    $pass = $server['password'] ?? '';

    if (empty($host)) {
        return ['success' => false, 'message' => 'ไม่ได้ระบุ IP หรือโดเมนของเซิร์ฟเวอร์ VPS'];
    }
    if (empty($pass)) {
        return ['success' => false, 'message' => 'ไม่ได้ระบุรหัสผ่าน VPS (Password) ในระบบหลังบ้าน'];
    }

    $escapedPass = escapeshellarg($pass);
    $escapedUser = escapeshellarg($user);
    $escapedHost = escapeshellarg($host);
    $escapedPort = (int)$port;
    $escapedTimeout = max(3, (int)$timeout);

    // Ensure sshpass is available
    static $sshpassReady = null;
    if ($sshpassReady === null) {
        $sshpassReady = (bool)shell_exec("command -v sshpass 2>/dev/null");
        if (!$sshpassReady) {
            @exec("DEBIAN_FRONTEND=noninteractive apt-get update -y >/dev/null 2>&1 && DEBIAN_FRONTEND=noninteractive apt-get install -y sshpass >/dev/null 2>&1");
            $sshpassReady = (bool)shell_exec("command -v sshpass 2>/dev/null");
        }
    }

    if (!$sshpassReady) {
        return ['success' => false, 'message' => 'เซิร์ฟเวอร์ยังไม่ได้ติดตั้งโปรแกรม sshpass กรุณารันคำสั่ง: apt install -y sshpass'];
    }

    // Use sshpass to connect to remote VPS
    $fullCmd = "sshpass -p {$escapedPass} ssh -p {$escapedPort} "
             . "-o StrictHostKeyChecking=no "
             . "-o UserKnownHostsFile=/dev/null "
             . "-o LogLevel=ERROR "
             . "-o ConnectTimeout={$escapedTimeout} "
             . "{$escapedUser}@{$escapedHost} "
             . escapeshellarg($cmd);

    $output = [];
    $returnVar = 0;
    exec($fullCmd . " 2>&1", $output, $returnVar);
    $outText = trim(implode("\n", $output));

    return [
        'success' => ($returnVar === 0),
        'output' => $outText,
        'code' => $returnVar
    ];
}

function ssh_vps_add_user($server, $username, $password, $days = 30, $limitGb = 200, $limitIp = 2) {
    $username = trim($username);
    $password = trim($password);
    if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username)) {
        return ['success' => false, 'message' => 'ชื่อผู้ใช้ไม่ถูกต้อง (อนุญาตเฉพาะตัวอักษรและตัวเลข 3-32 ตัว)'];
    }
    if ($password === '') {
        return ['success' => false, 'message' => 'รหัสผ่านต้องไม่ว่างเปล่า'];
    }

    $days = max(1, (int)$days);
    $limitGb = max(0, (int)$limitGb);
    $limitIp = max(1, (int)$limitIp);
    
    // สร้างผู้ใช้ Linux SSH พร้อมตั้งวันหมดอายุ และกำหนดโควต้า 200 GB และจำกัด 2 IP (บันทึกลงใน .ssh.db และตั้งค่า iptables quota)
    $cmd = "
if [ -x /usr/local/bin/ssh-admin ]; then
    /usr/local/bin/ssh-admin create '{$username}' '{$password}' {$days} {$limitGb} {$limitIp}
elif [ -x /usr/bin/ssh-admin ]; then
    /usr/bin/ssh-admin create '{$username}' '{$password}' {$days} {$limitGb} {$limitIp}
else
    /usr/sbin/useradd -e \$(date -d '+{$days} days' +'%Y-%m-%d') -s /bin/false -M '{$username}' 2>/dev/null || /usr/sbin/usermod -e \$(date -d '+{$days} days' +'%Y-%m-%d') -s /bin/false '{$username}' 2>/dev/null
    echo '{$username}:{$password}' | /usr/sbin/chpasswd 2>/dev/null
    mkdir -p /etc/ssh
    touch /etc/ssh/.ssh.db
    sed -i '/\\b{$username}\\b/d' /etc/ssh/.ssh.db 2>/dev/null || true
    echo '### {$username} '\$(date -d '+{$days} days' +%s)' {$limitGb} {$limitIp}' >> /etc/ssh/.ssh.db
    if [ {$limitGb} -gt 0 ]; then
        uid_num=\$(id -u '{$username}' 2>/dev/null)
        if [ -n \"\$uid_num\" ]; then
            for n in \$(iptables -L OUTPUT -n --line-numbers 2>/dev/null | grep \"owner UID match \$uid_num\" | awk '{print \$1}' | sort -rn); do
                iptables -D OUTPUT \"\$n\" 2>/dev/null
            done
            bytes=\$(({$limitGb} * 1024 * 1024 * 1024))
            iptables -I OUTPUT -m owner --uid-owner \"\$uid_num\" -m quota --quota \"\$bytes\" -j ACCEPT 2>/dev/null || true
            iptables -I OUTPUT -m owner --uid-owner \"\$uid_num\" -j REJECT 2>/dev/null || true
        fi
    fi
fi
";

    $res = ssh_vps_exec($server, $cmd, 12);
    if (!$res['success']) {
        return [
            'success' => false,
            'message' => 'เชื่อมต่อ VPS เพื่อสร้างบัญชีไม่สำเร็จ: ' . ($res['output'] ?: 'Connection timed out or refused')
        ];
    }

    return ['success' => true];
}

function ssh_vps_delete_user($server, $username) {
    $username = trim($username);
    if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username)) {
        return ['success' => false, 'message' => 'ชื่อผู้ใช้ไม่ถูกต้อง'];
    }

    $cmd = "
if [ -x /usr/local/bin/ssh-admin ]; then
    /usr/local/bin/ssh-admin delete '{$username}'
elif [ -x /usr/bin/ssh-admin ]; then
    /usr/bin/ssh-admin delete '{$username}'
else
    uid_num=\$(id -u '{$username}' 2>/dev/null)
    if [ -n \"\$uid_num\" ]; then
        for n in \$(iptables -L OUTPUT -n --line-numbers 2>/dev/null | grep \"owner UID match \$uid_num\" | awk '{print \$1}' | sort -rn); do
            iptables -D OUTPUT \"\$n\" 2>/dev/null
        done
    fi
    pkill -u '{$username}' 2>/dev/null || true
    userdel -f '{$username}' 2>/dev/null || true
    sed -i '/\\b{$username}\\b/d' /etc/ssh/.ssh.db 2>/dev/null || true
fi
";

    return ssh_vps_exec($server, $cmd, 8);
}

function ssh_vps_renew_user($server, $username, $days) {
    $username = trim($username);
    if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username)) {
        return ['success' => false, 'message' => 'ชื่อผู้ใช้ไม่ถูกต้อง'];
    }
    $days = max(1, (int)$days);

    // ขยายวันหมดอายุของ Linux User และอัปเดต timestamp ใน .ssh.db (โดยคงค่าลิมิตเดิมของสคริปต์ไว้ หรือถ้าไม่มีให้ใช้ 200 GB / 2 IP)
    $cmd = "
if [ -x /usr/local/bin/ssh-admin ]; then
    /usr/local/bin/ssh-admin renew '{$username}' {$days}
elif [ -x /usr/bin/ssh-admin ]; then
    /usr/bin/ssh-admin renew '{$username}' {$days}
else
    /usr/sbin/usermod -e \$(date -d '+{$days} days' +'%Y-%m-%d') '{$username}' 2>/dev/null || true
    OLD_GB=\$(grep \"^### {$username} \" /etc/ssh/.ssh.db 2>/dev/null | awk '{print \$4}')
    OLD_IP=\$(grep \"^### {$username} \" /etc/ssh/.ssh.db 2>/dev/null | awk '{print \$5}')
    [ -z \"\$OLD_GB\" ] && OLD_GB=200
    [ -z \"\$OLD_IP\" ] && OLD_IP=2
    EXP_TS=\$(date -d '+{$days} days' +%s)
    sed -i '/\\b{$username}\\b/d' /etc/ssh/.ssh.db 2>/dev/null || true
    echo \"### {$username} \$EXP_TS \$OLD_GB \$OLD_IP\" >> /etc/ssh/.ssh.db
    uid_num=\$(id -u '{$username}' 2>/dev/null)
    if [ -n \"\$uid_num\" ]; then
        for n in \$(iptables -L OUTPUT -n --line-numbers 2>/dev/null | grep \"owner UID match \$uid_num\" | awk '{print \$1}' | sort -rn); do
            iptables -D OUTPUT \"\$n\" 2>/dev/null
        done
        if [ \"\$OLD_GB\" -gt 0 ] 2>/dev/null; then
            bytes=\$((\$OLD_GB * 1024 * 1024 * 1024))
            iptables -I OUTPUT -m owner --uid-owner \"\$uid_num\" -m quota --quota \"\$bytes\" -j ACCEPT 2>/dev/null || true
            iptables -I OUTPUT -m owner --uid-owner \"\$uid_num\" -j REJECT 2>/dev/null || true
        fi
    fi
fi
";

    return ssh_vps_exec($server, $cmd, 10);
}

function ssh_vps_test_connection($server) {
    $start = microtime(true);
    $res = ssh_vps_exec($server, "uptime; whoami", 6);
    $latency = (int)round((microtime(true) - $start) * 1000);

    if ($res['success']) {
        return [
            'success' => true,
            'message' => "เชื่อมต่อ SSH VPS สำเร็จ! (Ping: {$latency}ms)",
            'latency_ms' => $latency
        ];
    } else {
        return [
            'success' => false,
            'message' => "ไม่สามารถเข้าสู่ระบบ VPS ผ่าน SSH ได้: " . ($res['output'] ?: 'ตรวจสอบ IP / Port / Password'),
            'latency_ms' => $latency
        ];
    }
}
