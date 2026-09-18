# Opsora Development — Local Environment Setup Guide

> **Status:** IMPLEMENTED  
> **Prerequisites:** PHP 8.2+, Composer 2.7+, Node.js 18+, MySQL 8.0+, Flutter 3.24+

---

## 1. Step-by-Step Local Setup Sequence

```bash
# 1. Clone repository & check out SaaS branch
git clone https://github.com/mhiskall282/opsora-sre.git
cd opsora-sre
git checkout feat/opsora-saas

# 2. Install PHP backend dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database migrations & personas seeding
php artisan migrate:fresh --seed

# 5. Compile frontend asset pipeline
npm install
npm run build

# 6. Run automated test suites to verify local integrity
php artisan test
vendor/bin/pint --test

# 7. Start local development server
php artisan serve
```

---

## 2. Flutter Mobile Setup

```bash
cd npontu_sre_mobile
flutter pub get
flutter test
flutter run -d windows
```
