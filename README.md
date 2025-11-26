# 📢 Kosonsoy Telegram Reklama Tizimi

Telegram kanallarida reklama boshqarish uchun professional web platforma.

## 🌟 Asosiy Xususiyatlar

### 🎯 Ommaviy Sahifa (Landing Page)
- ✅ Zamonaviy va responsive dizayn
- ✅ Reklama paketlari va narxlar
- ✅ Statistika va xususiyatlar
- ✅ Telegram bot integratsiyasi
- ✅ SEO optimallashtirilgan

### 🔐 Admin Panel
- ✅ Mijozlarni boshqarish (CRUD)
- ✅ Paketlarni boshqarish
- ✅ Reklama bronlash tizimi
- ✅ Kalendar va vaqt slotlari
- ✅ To'lov va statistika
- ✅ Dashboard va hisobotlar

### 🤖 Telegram Bot
- ✅ Webhook integratsiyasi
- ✅ Avtomatik xabarlar
- ✅ Eslatmalar (reminders)
- ✅ Buyurtma holati
- ✅ Komandalar: /start, /help, /my_ads

## 📁 Loyiha Strukturasi

```
kosonsoy-reklama/
│
├── index.php                 # Ommaviy bosh sahifa (landing page)
├── .htaccess                # Apache konfiguratsiyasi
├── 404.html                 # Xato sahifasi
│
├── assets/                  # Frontend resurslar
│   ├── css/
│   │   └── style.css       # Asosiy stillar
│   └── js/
│       ├── main.js         # Asosiy JavaScript
│       ├── calendar.js     # Kalendar funksiyalari
│       └── packages.js     # Paket boshqarish
│
├── config/                  # Konfiguratsiya fayllar
│   ├── database.php        # Database ulanish
│   └── settings.php        # Umumiy sozlamalar
│
├── frontend/               # Admin panel UI
│   ├── login.php          # Login sahifasi
│   ├── components/        # UI komponentlar
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   └── admin/             # Admin sahifalari
│       ├── index.php      # Dashboard
│       ├── customers.php  # Mijozlar
│       ├── packages.php   # Paketlar
│       ├── calendar.php   # Kalendar
│       └── payments.php   # To'lovlar
│
├── backend/               # Backend API
│   ├── auth/             # Autentifikatsiya
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── check_session.php
│   ├── customers/        # Mijozlar CRUD
│   │   ├── create.php
│   │   ├── read.php
│   │   ├── update.php
│   │   ├── delete.php
│   │   └── get_by_id.php
│   ├── packages/         # Paketlar CRUD
│   │   ├── create.php
│   │   ├── read.php
│   │   ├── update.php
│   │   ├── delete.php
│   │   └── assign_to_customer.php
│   ├── bookings/         # Bronlash tizimi
│   │   ├── create.php
│   │   ├── read.php
│   │   ├── update.php
│   │   ├── delete.php
│   │   └── get_by_date.php
│   ├── calendar/         # Kalendar API
│   │   ├── get_slots.php
│   │   ├── get_slots_by_date.php
│   │   ├── generate_slots.php
│   │   └── check_availability.php
│   ├── payments/         # To'lovlar
│   │   ├── create.php
│   │   ├── read.php
│   │   └── get_by_customer.php
│   └── statistics/       # Statistika va hisobotlar
│       ├── dashboard.php
│       ├── revenue.php
│       ├── customers_stats.php
│       └── ads_stats.php
│
└── telegram/             # Telegram bot
    ├── bot.php          # Asosiy bot logikasi
    ├── webhook.php      # Webhook handler
    ├── test.php         # Test skript
    ├── commands/        # Bot komandalar
    │   ├── start.php
    │   ├── help.php
    │   └── my_ads.php
    └── notifications/   # Xabarnomalar
        ├── send_confirmation.php
        └── send_reminder.php
```

## 🗄️ Database Strukturasi

### Jadvallar:
- `users` - Adminlar va foydalanuvchilar
- `customers` - Mijozlar
- `packages` - Reklama paketlari
- `customer_packages` - Mijozlarga biriktirilgan paketlar
- `bookings` - Reklama bronlash
- `time_slots` - Vaqt slotlari
- `payments` - To'lovlar

## 🚀 O'rnatish

### 1. Talablar
- PHP 7.4+
- MySQL 5.7+ yoki MariaDB 10.3+
- Apache web server
- mod_rewrite enabled

### 2. Database yaratish
```bash
mysql -u root -p
CREATE DATABASE kosonsoy_hisobot CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

### 3. SQL import qilish
```bash
mysql -u kosonsoy_hisobot -p kosonsoy_hisobot < kosonsoy_hisobot.sql
```

### 4. Fayllarni serverga yuklash
```bash
# FTP yoki cPanel orqali barcha fayllarni yuklang
# public_html papkasiga
```

### 5. Konfiguratsiya
`config/database.php` faylini tahrirlang:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'kosonsoy_hisobot');
define('DB_PASS', 'sizning_parolingiz');
define('DB_NAME', 'kosonsoy_hisobot');
```

### 6. Telegram Bot sozlash
`telegram/bot.php` faylida:
```php
define('BOT_TOKEN', 'sizning_bot_tokeningiz');
define('WEBHOOK_URL', 'https://reklama.kosonsoyliklar.uz/telegram/webhook.php');
```

Webhook o'rnatish:
```bash
curl -F "url=https://reklama.kosonsoyliklar.uz/telegram/webhook.php" \
     https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook
```

## 👤 Kirish Ma'lumotlari

### Admin Panel
- URL: `https://reklama.kosonsoyliklar.uz/frontend/login.php`
- Username: `superadmin`
- Password: `kosonsoy2025` (o'zgartirishni unutmang!)

## 📱 Telegram Bot

### Komandalar:
- `/start` - Botni boshlash
- `/help` - Yordam
- `/my_ads` - Mening reklamalarim

## 🔒 Xavfsizlik

### ✅ Amalga oshirilgan:
- SQL injection himoyasi
- XSS himoyasi
- CSRF token
- Session boshqaruvi
- Password hashing (bcrypt)
- Input validation
- Secure headers

### ⚠️ Tavsiyalar:
1. Default admin parolini o'zgartiring
2. Database parolini murakkab qiling
3. HTTPS faqat ishlating
4. Regular backup oling
5. Error logging yoqing

## 📊 Xususiyatlar

### Mijozlar Boshqaruvi
- ✅ Mijoz qo'shish/tahrirlash/o'chirish
- ✅ Telefon raqam va ism saqlash
- ✅ Active/inactive holat
- ✅ Paket tayinlash

### Paketlar
- ✅ Turli xil reklama paketlari
- ✅ Narx va post soni
- ✅ Active/inactive paketlar
- ✅ Paket statistikasi

### Kalendar va Bronlash
- ✅ Vaqt slotlari
- ✅ Available/booked/past holat
- ✅ Avtomatik slot yaratish
- ✅ Konflikt tekshirish
- ✅ Reklama tavsifi va izohlar

### To'lovlar
- ✅ Naqd/karta/nasiya/bepul
- ✅ To'lov tarixi
- ✅ Daromad statistikasi
- ✅ Mijoz bo'yicha hisobot

### Statistika
- ✅ Dashboard
- ✅ Daromad grafiklar
- ✅ Mijozlar statistikasi
- ✅ Reklama statistikasi
- ✅ Eng faol mijozlar

## 🎨 Dizayn

- **Frontend**: Bootstrap 5.3, Font Awesome 6.4
- **Colors**: Gradient (Telegram style)
- **Responsive**: Mobile-first approach
- **Icons**: Font Awesome
- **Charts**: Chart.js (dashboard uchun)

## 🐛 Xatolarni Tuzatish

### Database ulanish xatosi
```bash
# config/database.php faylni tekshiring
# MySQL service ishlab turganini tekshiring
sudo systemctl status mysql
```

### Telegram webhook xatosi
```bash
# Bot tokenni tekshiring
# Webhook URL to'g'riligini tekshiring
# SSL sertifikat mavjudligini tekshiring
```

## 📝 To Do List

### V2.0 Rejalar:
- [ ] SMS xabarnomalar
- [ ] Email xabarnomalar
- [ ] Multi-admin qo'llab-quvvatlash
- [ ] Reklama shablonlar
- [ ] Avtomatik post yuborish
- [ ] Analytics dashboard
- [ ] Mobile app (React Native)
- [ ] API documentation
- [ ] Unit tests

## 🤝 Hissa Qo'shish

Loyihani yaxshilash uchun:
1. Fork qiling
2. Feature branch yarating
3. Commit qiling
4. Push qiling
5. Pull Request oching

## 📞 Aloqa

- **Developer**: Kosonsoy IT Team
- **Telegram**: [@kosonsoy_admin](https://t.me/kosonsoy_admin)
- **Channel**: [@kosonsoy](https://t.me/kosonsoy)
- **Email**: info@kosonsoyliklar.uz
- **Website**: https://reklama.kosonsoyliklar.uz

## 📄 Litsenziya

© 2025 Kosonsoy Reklama. Barcha huquqlar himoyalangan.

---

**Eslatma**: Bu tizim Kosonsoy telegram kanaliga maxsus ishlab chiqilgan. Kod ochiq emas, faqat mijoz uchun.

## 🎯 Versiya Tarixi

### v1.0.0 (2025-11-18)
- ✅ Asosiy funksiyalar
- ✅ Admin panel
- ✅ Telegram bot
- ✅ Landing page
- ✅ Database tuzilmasi

---

**🚀 Omad! Kosonsoy kanaliga xush kelibsiz!**
