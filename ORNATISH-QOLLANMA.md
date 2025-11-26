# 🚀 KOSONSOY REKLAMA TIZIMINI O'RNATISH QO'LLANMASI

## 📋 Talab qilinadigan narsalar

- **Hosting**: cPanel yoki DirectAdmin
- **PHP**: 7.4 yoki yuqori
- **MySQL**: 5.7 yoki yuqori (MariaDB 10.3+)
- **Domain**: reklama.kosonsoyliklar.uz (SSL bilan)
- **Telegram Bot Token**: @BotFather dan

---

## 📦 BOSQICH 1: FAYLLARNI YUKLASH

### 1.1 ZIP faylni yuklab oling
✅ `kosonsoy-reklama-complete.zip` faylini kompyuteringizga saqlang

### 1.2 cPanel/Hosting ga kiring
1. Hostingizga kiring (cPanel)
2. **File Manager** ga o'ting
3. `public_html` papkasini oching

### 1.3 ZIP ni yuklash
1. **Upload** tugmasini bosing
2. `kosonsoy-reklama-complete.zip` faylni tanlang
3. Yuklangandan keyin **Extract** qiling
4. Barcha fayllar `public_html` da bo'lishi kerak

### 1.4 Fayllar joylashuvi
```
public_html/
├── index.php              ← Bosh sahifa (landing)
├── .htaccess             ← Apache sozlamalari
├── 404.html              ← Xato sahifasi
├── README.md
├── assets/               ← CSS, JS fayllar
├── backend/              ← API fayllar
├── config/               ← Konfiguratsiya
├── frontend/             ← Admin panel
└── telegram/             ← Bot fayllar
```

---

## 🗄️ BOSQICH 2: DATABASE YARATISH

### 2.1 MySQL Database yaratish
1. cPanel → **MySQL Databases**
2. **Create New Database** bo'limida:
   - Database nomi: `kosonsoy_hisobot`
   - Charset: `utf8mb4_general_ci`
   - **Create Database** ni bosing

### 2.2 Database foydalanuvchi yaratish
1. **MySQL Users** bo'limida:
   - Username: `kosonsoy_hisobot`
   - Password: **Kuchli parol yarating** (masalan: `K0s0ns0y@2025!`)
   - **Create User** ni bosing

### 2.3 Foydalanuvchini databasega biriktirish
1. **Add User to Database** bo'limida:
   - User: `kosonsoy_hisobot`
   - Database: `kosonsoy_hisobot`
   - **ALL PRIVILEGES** tanlang
   - **Make Changes** ni bosing

### 2.4 SQL faylni import qilish
1. cPanel → **phpMyAdmin**
2. Chap menuda `kosonsoy_hisobot` ni tanlang
3. Yuqorida **Import** tabni oching
4. **Choose File** → `kosonsoy_hisobot.sql` ni tanlang
5. **Go** tugmasini bosing
6. ✅ "Import has been successfully finished" xabarini ko'rishingiz kerak

---

## ⚙️ BOSQICH 3: KONFIGURATSIYA

### 3.1 Database sozlamalari
1. File Manager orqali `config/database.php` faylni oching
2. Quyidagi qatorlarni o'zgartiring:

```php
// ESKi (standart):
define('DB_HOST', 'localhost');
define('DB_USER', 'kosonsoy_hisobot');
define('DB_PASS', 'Jd5NE3RhhwNv2YzwvNM9');
define('DB_NAME', 'kosonsoy_hisobot');

// YANGI (sizning ma'lumotlaringiz):
define('DB_HOST', 'localhost');           // Odatda localhost
define('DB_USER', 'sizning_db_useringiz'); // 2.2 dagi username
define('DB_PASS', 'sizning_parolingiz');   // 2.2 dagi password
define('DB_NAME', 'sizning_db_nomingiz');  // 2.1 dagi database nomi
```

3. **Save Changes** ni bosing

### 3.2 File permissions (ruxsatlar)
Quyidagi papkalarga write permission bering:
```bash
chmod 755 assets/
chmod 755 backend/
chmod 755 config/
chmod 755 frontend/
chmod 755 telegram/
```

---

## 🔐 BOSQICH 4: ADMIN PAROLNI O'ZGARTIRISH

### 4.1 Tizimga kirish
1. Brauzerda oching: `https://reklama.kosonsoyliklar.uz/frontend/login.php`
2. Kirish ma'lumotlari:
   - **Username**: `superadmin`
   - **Password**: `kosonsoy2025`

### 4.2 Yangi parol yaratish
⚠️ **MUHIM**: Default parolni darhol o'zgartiring!

1. Terminal orqali yangi hash yarating:
```php
<?php
echo password_hash('yangi_parol_123', PASSWORD_BCRYPT);
?>
```

2. phpMyAdmin → `users` jadvalidagi `password` ustunini yangilang

---

## 🤖 BOSQICH 5: TELEGRAM BOT SOZLASH

### 5.1 Bot yaratish
1. Telegram da [@BotFather](https://t.me/BotFather) ga yozing
2. `/newbot` komandasi yuboring
3. Bot nomini kiriting: `Kosonsoy Reklama Bot`
4. Username kiriting: `kosonsoy_reklama_bot`
5. **Bot Token** ni saqlang (masalan: `6547321890:AAHxxxxxxxxxxxxxxxxxxxxx`)

### 5.2 Bot sozlamalari
File Manager → `telegram/bot.php` faylni oching:

```php
// Eski:
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');

// Yangi (5.1 dagi token):
define('BOT_TOKEN', '6547321890:AAHxxxxxxxxxxxxxxxxxxxxx');
```

### 5.3 Webhook o'rnatish
Terminal/SSH orqali (yoki online curl service ishlatib):

```bash
curl -F "url=https://reklama.kosonsoyliklar.uz/telegram/webhook.php" \
     https://api.telegram.org/bot6547321890:AAHxxxxxxxxxxxxxxxxxxxxx/setWebhook
```

✅ Javob: `{"ok":true,"result":true,"description":"Webhook was set"}`

### 5.4 Webhook tekshirish
```bash
curl https://api.telegram.org/bot6547321890:AAHxxxxxxxxxxxxxxxxxxxxx/getWebhookInfo
```

---

## 🌐 BOSQICH 6: DOMAIN VA SSL

### 6.1 Domain sozlamalari
1. Domain DNS sozlamalariga kiring
2. **A Record** qo'shing:
   - Host: `reklama`
   - Points to: `Sizning server IP`
   - TTL: `3600`

### 6.2 SSL Sertifikat (HTTPS)
cPanel → **SSL/TLS** → **Let's Encrypt**:
1. Domain: `reklama.kosonsoyliklar.uz`
2. **Issue** ni bosing
3. 2-3 daqiqa kuting
4. ✅ HTTPS ishlaydi!

---

## ✅ BOSQICH 7: TEKSHIRISH

### 7.1 Bosh sahifa
Oching: `https://reklama.kosonsoyliklar.uz`
- ✅ Landing page ko'rinishi kerak
- ✅ Responsive bo'lishi kerak
- ✅ Barcha rasmlar yuklangan

### 7.2 Admin panel
Oching: `https://reklama.kosonsoyliklar.uz/frontend/login.php`
- ✅ Login sahifa ochilishi kerak
- ✅ Kirish mumkin bo'lishi kerak
- ✅ Dashboard ko'rinishi kerak

### 7.3 Database ulanish
- ✅ Admin panel ma'lumotlarni ko'rsatsa - database ishlaydi
- ❌ Xatolik bo'lsa - `config/database.php` ni tekshiring

### 7.4 Telegram bot
1. Telegram da botingizga `/start` yuboring
2. ✅ Javob kelishi kerak
3. ✅ Tugmalar ishlashi kerak

---

## 🎨 BOSQICH 8: O'ZGARTIRISH (ixtiyoriy)

### 8.1 Logo qo'shish
- `assets/images/` papka yarating
- Logo faylini yuklang
- `index.php` da qo'shing

### 8.2 Ranglarni o'zgartirish
`index.php` → CSS qismida:
```css
:root {
    --primary: #0088cc;    /* Asosiy rang */
    --secondary: #00a6db;  /* Ikkilamchi rang */
}
```

### 8.3 Telefon raqam va kontaktlar
`index.php` → Footer qismida:
```html
<li><i class="fas fa-phone"></i> +998 XX XXX XX XX</li>
<li><i class="fas fa-envelope"></i> info@kosonsoyliklar.uz</li>
```

---

## 🐛 MUAMMOLAR VA YECHIMLAR

### ❌ "Database connection error"
**Sabab**: Database sozlamalari noto'g'ri
**Yechim**:
1. `config/database.php` ni tekshiring
2. phpMyAdmin da user/password tekshiring
3. Database nomi to'g'riligini tekshiring

### ❌ "404 Not Found" (admin panel)
**Sabab**: .htaccess ishlamayapti
**Yechim**:
1. cPanel → `.htaccess` faylni tekshiring
2. Apache `mod_rewrite` yoqilganini tekshiring

### ❌ "Permission denied" (file upload)
**Sabab**: Folder ruxsatlari yo'q
**Yechim**:
```bash
chmod 755 assets/
chmod 755 uploads/
```

### ❌ Telegram bot javob bermaydi
**Sabab**: Webhook xato
**Yechim**:
1. Bot token to'g'riligini tekshiring
2. Webhook URL to'g'riligini tekshiring
3. SSL sertifikat borligini tekshiring
4. `getWebhookInfo` orqali holatni ko'ring

---

## 📊 STATISTIKA VA MONITORING

### Error Logging
`config/settings.php` da:
```php
ini_set('display_errors', 0);  // Production da 0
ini_set('log_errors', 1);
ini_set('error_log', '/home/user/error_log.txt');
```

### Traffic monitoring
- Google Analytics qo'shing
- cPanel → Awstats orqali ko'ring

---

## 🔒 XAVFSIZLIK CHORALARI

### ✅ ALBATTA QILING:
1. ✅ Admin parolni o'zgartiring
2. ✅ Database parolni murakkab qiling
3. ✅ HTTPS dan foydalaning
4. ✅ Har hafta backup oling
5. ✅ `.git` papkasini o'chiring
6. ✅ Error logging yoqing

### ❌ HECH QACHON QILMANG:
1. ❌ Parollarni file'da saqlamang
2. ❌ HTTP dan foydalanmang
3. ❌ Root user bilan ishlamang
4. ❌ Error'larni ochiq ko'rsatmang

---

## 📞 YORDAM

### Muammo yuzaga kelsa:
1. **README.md** faylni o'qing
2. **Error log** fayllarni tekshiring
3. **Google** da qidiring
4. **Telegram**: @kosonsoy_admin ga yozing

---

## 🎉 TAYYOR!

Endi sizning professional reklama tizimingiz ishga tushdi!

### Keyingi qadamlar:
1. ✅ Mijozlar qo'shish
2. ✅ Paketlar yaratish
3. ✅ Reklamalarni boshlash
4. ✅ Statistikani kuzatish

---

**🚀 Omad! Kosonsoy kanaliga xush kelibsiz!**

**© 2025 Kosonsoy Reklama Tizimi**
