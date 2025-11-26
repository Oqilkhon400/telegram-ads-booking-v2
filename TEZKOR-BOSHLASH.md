# ⚡ TEZKOR BOSHLASH - KOSONSOY REKLAMA TIZIMI

## 🎯 5 Daqiqada Ishga Tushirish

### 1️⃣ FAYLLARNI YUKLASH (2 daqiqa)
```bash
1. kosonsoy-reklama-FINAL.zip ni yuklab oling
2. cPanel → File Manager → public_html
3. Upload → Extract
```

### 2️⃣ DATABASE YARATISH (2 daqiqa)
```bash
1. cPanel → MySQL Databases
2. Database yarating: kosonsoy_hisobot
3. User yarating: kosonsoy_hisobot
4. User ni database ga biriktiring
5. phpMyAdmin → Import → kosonsoy_hisobot.sql
```

### 3️⃣ KONFIGURATSIYA (1 daqiqa)
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'sizning_user');
define('DB_PASS', 'sizning_parol');
define('DB_NAME', 'sizning_database');
```

### 4️⃣ TEKSHIRISH
```
1. Oching: https://reklama.kosonsoyliklar.uz
2. Oching: https://reklama.kosonsoyliklar.uz/check-system.php
3. Login: https://reklama.kosonsoyliklar.uz/frontend/login.php
   Username: superadmin
   Password: kosonsoy2025
```

### 5️⃣ TELEGRAM BOT
```php
// telegram/bot.php
define('BOT_TOKEN', 'sizning_bot_token');

// Webhook o'rnatish:
curl -F "url=https://reklama.kosonsoyliklar.uz/telegram/webhook.php" \
     https://api.telegram.org/botSIZNING_TOKEN/setWebhook
```

---

## 📁 Fayllar Joylashuvi

```
public_html/
├── index.php              ← BOSH SAHIFA (landing page)
├── check-system.php       ← Tizim tekshirish
├── .htaccess             
├── frontend/
│   └── login.php         ← ADMIN LOGIN
└── config/
    └── database.php      ← DATABASE SOZLAMALARI
```

---

## 🔑 Kirish Ma'lumotlari

**Admin Panel:**
- URL: `/frontend/login.php`
- User: `superadmin`
- Pass: `kosonsoy2025`

⚠️ **Darhol parolni o'zgartiring!**

---

## ✅ Muhim Tekshiruvlar

1. ✅ Landing page ochiladi?
2. ✅ Admin panelga kirish mumkin?
3. ✅ Database ulanmoqda?
4. ✅ Telegram bot javob beradi?
5. ✅ SSL (HTTPS) ishlayapti?

---

## 🆘 Tezkor Xatoliklarni Tuzatish

### ❌ Database ulanmaydi
→ `config/database.php` da ma'lumotlarni to'g'rilang

### ❌ 404 Not Found
→ `.htaccess` fayl borligini tekshiring

### ❌ Permission denied
→ `chmod 755` papkalarga

### ❌ Bot javob bermaydi
→ Bot token va webhook URL tekshiring

---

## 📞 Yordam

**To'liq qo'llanma:** `ORNATISH-QOLLANMA.md`
**Telegram:** @kosonsoy_admin

---

**🚀 Omad! 5 daqiqada tayyor!**
