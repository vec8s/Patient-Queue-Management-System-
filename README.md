# Patient Queue Management System (PQS)

نظام حديث ومتكامل لإدارة طوابير وحجوزات المرضى في المنشآت الصحية، مبني بمعمارية احترافية مدعومة بـ **Composer** ومعيار **PSR-4 Autoloading** مع حماية أمنية خادمة متقدمة.

---

## 📁 هيكلية المشروع المعتمدة (Composer PSR-4 Architecture)

```text
pqs/
├── composer.json               # ملف Composer المركزي وإعدادات PSR-4
├── .env.example                # نموذج المتغيرات السرية وبيانات الاتصال
├── .env                        # ملف البيئة المحلي الفعلي (مستبعد من Git)
├── .gitignore                  # استبعاد الحزم وملفات النظام والبيئة
├── .htaccess                   # قواعد حماية ملفات السيرفر
│
├── src/                        # كلاسات النظام المنظمة ("PQS\\": "src/")
│   ├── Auth/                   # كلاسات الأمان (JWTService, AuthGuard)
│   ├── Database/               # كلاس الاتصال وإدارة المعاملات (Database)
│   ├── Services/               # خدمات العمل (QueueService, PatientService, AppointmentService)
│   └── Utils/                  # الأدوات المساعدة (Config, Response)
│
├── api/                        # واجهات برمجية موحدة (v1/)
│   └── v1/
│       ├── auth.php            # تسجيل الدخول والتحقق وإنشاء الحسابات
│       ├── patients.php        # تسجيل المرضى والبحث الآمن
│       ├── appointments.php    # حجز ومراجعة المواعيد
│       └── queue.php           # شاشة الطابور واستدعاء الطبيب
│
├── database/                   # قاعدة البيانات النظيفة والمطهرة
│   ├── schema.sql              # هيكل الجداول (DDL نقي بدون بيانات مسربة)
│   ├── seed.sql                # بيانات تجريبية وهمية نظيفة
│   └── migration.php           # سكربت التهيئة التلقائي
│
├── includes/                   # القوالب المشتركة (header, navbar, footer)
├── css/                        # التنسيقات (style.css)
├── js/                         # الجافاسكربت (script.js)
├── images/                     # الصور والأيقونات
│
├── index.php                   # البوابة الرئيسية
├── visit.php                   # مدخل استقبال المراجع
├── checkin.php                 # تسجيل الحضور الفوري والمسبق
├── appointments.php            # حجز المواعيد المسبقة
├── patient-search.php          # البحث الآمن عن ملفات المرضى
├── registration.php            # تسجيل مريض جديد
├── queue.php                   # شاشة صالة الانتظار الحية (Live TV)
├── dashboard.php               # لوحة تحكم الأطباء (محمية بالخادم)
├── admin.php                   # لوحة الإدارة العامة (محمية للمدير فقط)
├── login.php                   # بوابة دخول الكادر الطبي
└── vendor/                     # الحزم المحملة تلقائياً عبر Composer
```

---

## 🚀 التثبيت والتشغيل السريع

### 1. تثبيت الحزم وتوليد الـ Autoloader
من داخل مجلد `pqs`:
```bash
composer dump-autoload -o
# أو لتثبيت أي حزم جديدة مستقبلاً:
composer install
```

### 2. ضبط ملف البيئة `.env`
انسخ ملف `.env.example` إلى `.env` وعدل بيانات قاعدة البيانات إذا لزم الأمر:
```env
DB_HOST="localhost"
DB_PORT="3306"
DB_USER="root"
DB_PASS=""
DB_NAME="db_pqs"
JWT_SECRET="pqs_app_secret_key_98372645109847162534"
```

### 3. تهيئة وبذر قاعدة البيانات النظيفة
شغّل أمر التهيئة عبر أداة Artisan:
```bash
php artisan migrate
```
أو مباشرة:
```bash
php database/migration.php
```

### 4. تشغيل خادم التطوير المحلي (Local Dev Server)
شغّل خادم التطوير فوراً باستخدام أمر Artisan:
```bash
php artisan serve
# أو
php artisan server
```
كما يمكنك تحديد منفذ مخصص عند الحاجة:
```bash
php artisan server --port=8080
```
وسيكون النظام متاحاً مباشرة على المتصفح عبر:
```
http://127.0.0.1:8000
```

---

## 🔑 الحسابات التجريبية الافتراضية النظيفة

| الدور الوظيفي | البريد الإلكتروني | كلمة المرور |
| :--- | :--- | :--- |
| **مدير النظام (Admin)** | `admin@clinic.local` | `Admin@123` |
| **طبيب معالج (Doctor)** | `doctor@clinic.local` | `Doctor@123` |

---

## 🛡️ التحسينات الأمنية المنفذة

1. **تطهير البيانات (Data Sanitization)**: حذف جميع أسماء المرضى وسجلاتهم الحقيقية من ملفات الـ SQL واستبدالها ببيانات وهمية.
2. **منع إنشاء حسابات إدارية دون توثيق**: تم تقييد إنشاء الموظفين برتبة `admin` في `api/v1/auth.php` ليتطلب توكن إداري حصراً.
3. **حماية الصفحات الحساسة من الخادم (Server-Side RBAC)**: ملفات `dashboard.php` و `admin.php` أصبحت تفحص الصلاحيات من الخادم فوراً عبر `AuthGuard::enforcePage()`.
4. **تأمين التوكن**: دعم تخزين التوكن في كوكيز بخصائص `HttpOnly` و `SameSite` لمنع سرقته عبر ثغرات XSS.
5. **معالجة تضارب أرقام التذاكر (Race Condition)**: إنشاء آلية قفل المعاملات `FOR UPDATE` في `QueueService` لضمان عدم تكرار أرقام التذاكر عند التزامن.
