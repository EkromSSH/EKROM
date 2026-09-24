# 🚀 EKROM SHOP — คู่มือการติดตั้งและใช้งานระบบฉบับสมบูรณ์ (Official Manual)

**EKROM SHOP** คือระบบร้านค้าเว็บแอปพลิเคชันสำหรับจำหน่ายและจัดการไฟล์เชื่อมต่อ VPN (V2Ray / VLESS Reality / VMess / Trojan / Shadowsocks) และ SSH VPS อัตโนมัติ 24 ชั่วโมง พร้อมระบบจัดการหลังบ้านระดับพรีเมียม, ระบบตรวจสอบสลิปอัตโนมัติ (SlipOK), ซองของขวัญ TrueMoney และ **LINE Official Account Messaging API Bot 24 ชม.**

---

## 📑 สารบัญ (Table of Contents)
1. [ความต้องการของระบบ (System Requirements)](#1-ความต้องการของระบบ-system-requirements)
2. [วิธีติดตั้งระบบแบบ One-Click Auto Installer](#2-วิธีติดตั้งระบบแบบ-one-click-auto-installer)
3. [การตั้งค่า Cloudflare DNS & เปิดใช้งาน HTTPS (SSL)](#3-การตั้งค่า-cloudflare-dns--เปิดใช้งาน-https-ssl)
4. [ข้อมูลการเข้าสู่ระบบและบัญชีเริ่มต้น](#4-ข้อมูลการเข้าสู่ระบบและบัญชีเริ่มต้น)
5. [คู่มือการเชื่อมต่อ LINE Official Account & Messaging API Bot](#5-คู่มือการเชื่อมต่อ-line-official-account--messaging-api-bot)
6. [การตั้งค่าระบบเติมเงินอัตโนมัติ (SlipOK & TrueMoney)](#6-การตั้งค่าระบบเติมเงินอัตโนมัติ-slipok--truemoney)
7. [การเชื่อมต่อเซิร์ฟเวอร์ VPN (3X-UI) และ SSH VPS](#7-การเชื่อมต่อเซิร์ฟเวอร์-vpn-3x-ui-และ-ssh-vps)
8. [ระบบสำรองข้อมูลอัตโนมัติ & การแจ้งเตือน Discord](#8-ระบบสำรองข้อมูลอัตโนมัติ--การแจ้งเตือน-discord)
9. [คำสั่งควบคุมและดูแลรักษาเซิร์ฟเวอร์ (Maintenance Commands)](#9-คำสั่งควบคุมและดูแลรักษาเซิร์ฟเวอร์-maintenance-commands)

---

## 1. ความต้องการของระบบ (System Requirements)
- **ระบบปฏิบัติการ:** Ubuntu 20.04 / 22.04 / 24.04 LTS หรือ Debian 11 / 12 (แนะนำ Ubuntu 22.04 LTS)
- **สิทธิ์การใช้งาน:** Root (`sudo -i`)
- **CPU / RAM:** ขั้นต่ำ 1 vCPU / 1 GB RAM (แนะนำ 2 GB RAM ขึ้นไป)
- **พื้นที่ดิสก์:** ขั้นต่ำ 10 GB
- **พอร์ตที่ต้องเปิด:** 80 (HTTP) และ 443 (HTTPS)

---

## 2. วิธีติดตั้งระบบแบบ One-Click Auto Installer

รันคำสั่งติดตั้งอัตโนมัติเพียงคำสั่งเดียวผ่าน Terminal (SSH):

```bash
bash <(curl -fsSL https://raw.githubusercontent.com/EkromSSH/EKROM/main/install.sh)
```

หรือใช้คำสั่งสำรอง:
```bash
wget -O install.sh https://raw.githubusercontent.com/EkromSSH/EKROM/main/install.sh && chmod +x install.sh && ./install.sh
```

### สิ่งที่ระบบติดตั้งให้อัตโนมัติ:
- ติดตั้ง PHP 8.1 / 8.2 พร้อม Extensions จำเป็น (`curl`, `sqlite3`, `mbstring`, `zip`, `openssl`, `json`)
- ติดตั้งและตั้งค่า Nginx Reverse Proxy พร้อมการรักษาความปลอดภัย
- สร้างฐานข้อมูล SQLite พร้อมข้อมูล Seed เริ่มต้นและตาราง LINE Bot
- สร้าง Systemd Service (`ekrom-shop.service`) รันเบื้องหลังอัตโนมัติ พร้อมระบบรีสตาร์ทตัวเองหากเซิร์ฟเวอร์ดับ
- ตั้งค่าระบบ Backup ฐานข้อมูลอัตโนมัติทุกวันเวลา 03:00 น.

---

## 3. การตั้งค่า Cloudflare DNS & เปิดใช้งาน HTTPS (SSL)

เพื่อให้ระบบใช้งาน HTTPS ได้อย่างสมบูรณ์แบบ (จำเป็นสำหรับการเชื่อมต่อ LINE Webhook):

1. เข้าสู่ระบบ [Cloudflare Dashboard](https://dash.cloudflare.com/) > เลือกโดเมนของคุณ
2. ไปที่เมนู **DNS > Records** > กด **Add record**:
   - **Type:** `A`
   - **Name:** `@` (หรือชื่อซับโดเมน เช่น `shop`, `vpn`)
   - **IPv4 address:** ใส่ IP ของเครื่องเซิร์ฟเวอร์ VPS ของคุณ
   - **Proxy status:** **Proxied (เปิดเมฆสีส้ม ☁️)**
   - กด **Save**
3. ไปที่เมนู **SSL/TLS**:
   - เลือกโหมดการเข้ารหัสเป็น: **`Flexible`** หรือ **`Full`**
   - ไปที่ **Edge Certificates** > เปิด **Always Use HTTPS** เป็น **ON**

---

## 4. ข้อมูลการเข้าสู่ระบบและบัญชีเริ่มต้น

เมื่อติดตั้งเสร็จ สามารถเข้าสู่ระบบได้ที่: `https://โดเมนของคุณ.com/login.php`

| บทบาท | ชื่อผู้ใช้ (Username) | รหัสผ่าน (Password) | PIN ยืนยัน | สิทธิ์การใช้งาน |
|---|---|---|---|---|
| **ผู้ดูแลระบบ (Admin)** | `admin` | `admin123` | `123456` | เข้าถึงหน้าจัดการหลังบ้านทั้งหมด |
| **ตัวแทนจำหน่าย (Reseller)** | `reseller` | `reseller123` | `123456` | ได้รับส่วนลดสั่งซื้อ 30% อัตโนมัติ |
| **ลูกค้าทดสอบ (Buyer)** | `buyer` | `buyer123` | - | บัญชีทดลองสั่งซื้อหน้าร้าน |

> ⚠️ **คำแนะนำความปลอดภัย:** เมื่อเข้าสู่ระบบครั้งแรก กรุณาเข้าไปเปลี่ยนรหัสผ่านและรหัส PIN แอดมินทันทีที่หน้า [Admin Settings](https://your-domain.com/admin-settings.php)

---

## 5. คู่มือการเชื่อมต่อ LINE Official Account & Messaging API Bot

ระบบมาพร้อมกับ **LINE Bot อัจฉริยะ 24 ชั่วโมง** รองรับการส่งเมนู Flex Message, ให้ลูกค้าเลือกซื้อ VPN, ขอทดลองใช้ฟรี, เติมเงินผ่านสลิปในแชท และส่งลิงก์/QR Code ให้ลูกค้าติดตั้งในแอปได้ทันที

```
[1. ตั้งค่าใน LINE Developers] ➔ [2. ตั้งค่าใน LINE OA Manager] ➔ [3. บันทึก Token ในเว็บหลังบ้าน]
```

### 🔹 ขั้นตอนที่ 1: ตั้งค่าใน LINE Developers Console
1. เข้าไปที่ [LINE Developers Console](https://developers.line.biz/)
2. สร้าง Provider และสร้าง Channel ประเภท **`Messaging API`**
3. ไปที่แท็บ **Messaging API**:
   - ในช่อง **Webhook URL** ให้ใส่:
     ```text
     https://โดเมนของคุณ.com/api/line_webhook.php
     ```
   - สวิตช์ **Use webhook**: กดเปิดเป็น **`ON (สีเขียว)`**
   - เลื่อนลงไปที่หัวข้อ **Channel access token** > กดปุ่ม **`Issue`** แล้วคัดลอก Token เก็บไว้
4. ไปที่แท็บ **Basic settings**:
   - คัดลอกค่า **Channel secret** เก็บไว้

### 🔹 ขั้นตอนที่ 2: ตั้งค่าใน LINE Official Account Manager
1. เข้าไปที่ [LINE OA Manager](https://manager.line.biz/) > เลือกบัญชีของคุณ
2. ไปที่ **ตั้งค่า (มุมขวาบน) > ตั้งค่าการตอบกลับ (Response settings)**:
   - **โหมดการตอบกลับ (Response mode):** เลือกเป็น **`บอท (Bot)`**
   - **ข้อความตอบกลับอัตโนมัติ (Auto-response messages):** เลือกเป็น **`ปิด (Disabled)`**
   - **Webhook:** เลือกเป็น **`เปิดใช้งาน (Enabled)`**

### 🔹 ขั้นตอนที่ 3: บันทึกข้อมูลลงในระบบหลังบ้านเว็บ
1. เข้าสู่ระบบหลังบ้าน: `https://โดเมนของคุณ.com/admin-settings.php`
2. เลื่อนลงไปที่หัวข้อ **🤖 จัดการ LINE Bot & การแจ้งเตือน**
3. กรอกข้อมูล:
   - **เปิดใช้งาน LINE Bot:** กดเปิดสวิตช์ให้เป็น **สีเขียว**
   - **LINE Basic ID:** ใส่ ID บอท (เช่น `@ekromvpn`)
   - **Channel Secret:** วาง Secret จากขั้นตอนที่ 1
   - **Channel Access Token:** วาง Token จากขั้นตอนที่ 1
4. กด **💾 บันทึกการตั้งค่า LINE Bot**
5. กด **⚡ ทดสอบการเชื่อมต่อ LINE Bot** — ถ้าระบบขึ้นข้อความสำเร็จ บอทจะเริ่มทำงานทันที!

---

## 6. การตั้งค่าระบบเติมเงินอัตโนมัติ (SlipOK & TrueMoney)

เข้าสู่ระบบหลังบ้านไปที่เมนู **ตั้งค่าระบบ > ระบบเติมเงิน (Topup Settings)**:

### 1. การตรวจสลิปโอนเงิน (PromptPay & Bank Slip):
- เลือกโหมด: **`SlipOK API (แนะนำ - ตรวจสอบยอดเงินอัตโนมัติ 100%)`**
- สมัครและรับ API Key ได้ที่ [SlipOK.com](https://slipok.com/)
- กรอก **Branch ID** และ **API Key**
- ใส่ชื่อบัญชีผู้รับเงิน และเบอร์พร้อมเพย์/เลขบัญชีร้านค้า
- กำหนดยอดเงินขั้นต่ำในการเติมเงิน (เช่น 30 บาท)

### 2. ซองของขวัญ TrueMoney Voucher:
- ระบบรองรับการวางลิงก์ซองของขวัญทรูมันนี่ (`gift.truemoney.com`) เติมเงินเข้ากระเป๋าอัตโนมัติทั้งหน้าเว็บและในแชท LINE Bot

---

## 7. การเชื่อมต่อเซิร์ฟเวอร์ VPN (3X-UI) และ SSH VPS

เข้าสู่ระบบหลังบ้านไปที่เมนู **จัดการเซิร์ฟเวอร์ (Admin Servers)**:

### 1. เชื่อมต่อเซิร์ฟเวอร์ X-UI / 3X-UI (VLESS Reality / VMess / Trojan):
- **ประเภท:** เลือก `X-UI (3x-ui Panel)`
- **Panel URL:** `http://IP_เครื่อง_VPN:2053/xui/` (หรือพอร์ตที่ตั้งไว้)
- **Username / Password:** ใส่บัญชีเข้าหน้าเว็บ 3x-ui
- **Inbound ID:** ใส่ ID ของ Inbound ที่ต้องการให้ระบบสร้าง Client ให้ลูกค้า
- **Bug Host (SNI):** ใส่ Bug Host สำหรับโปรเน็ต (เช่น `speedtest.net`, `true.th`)

### 2. เชื่อมต่อเซิร์ฟเวอร์ SSH VPS (NPV Tunnel / NetMod / HTTP Custom):
- **ประเภท:** เลือก `SSH VPS (Script / UDP Custom)`
- **Host / IP:** ใส่ IP ของเซิร์ฟเวอร์ VPS
- **SSH Port:** พอร์ต SSH (ค่าเริ่มต้น `22`)
- **Root Password:** รหัสผ่าน root ของ VPS เพื่อให้ระบบสั่งสร้าง User/Pass อัตโนมัติ

---

## 8. ระบบสำรองข้อมูลอัตโนมัติ & การแจ้งเตือน Discord

### การสำรองข้อมูล (Database Backup):
- ระบบมีสคริปต์ `/root/ekrom-shop/backup_db.sh` สำรองไฟล์ฐานข้อมูล SQLite แบบ Online Snapshot ทุกวัน
- บีบอัดไฟล์เป็น `.sqlite.gz` เก็บไว้ในโฟลเดอร์ `/root/ekrom-shop/backups/`
- ระบบจะลบไฟล์สำรองที่เก่าเกิน 14 วันทิ้งให้อัตโนมัติ เพื่อประหยัดพื้นที่ดิสก์

### การแจ้งเตือน Discord Webhook:
- เข้าหน้า **Admin Settings > Discord Webhooks**
- นำ Webhook URL จาก Discord มาใส่ในช่อง:
  - แจ้งเตือนเมื่อมีการซื้อ VPN (`buy`)
  - แจ้งเตือนเมื่อมีการเติมเงิน (`topup`)
  - แจ้งเตือนข้อผิดพลาดระบบ (`error`)

---

## 9. คำสั่งควบคุมและดูแลรักษาเซิร์ฟเวอร์ (Maintenance Commands)

| คำสั่ง | การทำงาน |
|---|---|
| `systemctl restart ekrom-shop` | รีสตาร์ทบริการเว็บร้านค้า |
| `systemctl status ekrom-shop` | ตรวจสอบสถานะการทำงานของเว็บ |
| `systemctl restart nginx` | รีสตาร์ทเว็บเซิร์ฟเวอร์ Nginx |
| `tail -f /root/ekrom-shop/line_bot.log` | ดู Log การทำงานของ LINE Bot แบบเรียลไทม์ |
| `/root/ekrom-shop/backup_db.sh` | สำรองข้อมูลฐานข้อมูลทันทีด้วยตนเอง |
| `cd /root/ekrom-shop && git pull` | อัปเดตระบบร้านค้าเป็นเวอร์ชันล่าสุดจาก GitHub |

---

**© 2026 EKROM SHOP — พัฒนาเพื่อการจัดการระบบ VPN & SSH อัตโนมัติอย่างมืออาชีพ**
