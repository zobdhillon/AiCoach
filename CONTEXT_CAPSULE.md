# 📋 Ai-Coach Project - Context Capsule

**Last Updated:** June 3, 2026  
**Project Status:** ✅ **SUBSTANTIALLY COMPLETE** (Core features implemented & functional)

---

## 🎯 Project Overview

**Ai-Coach** is a Laravel-based AI-powered conversation simulation platform where users practice real-world communication skills by engaging in realistic scenarios with an AI coach powered by Groq's LLM.

**Problem Solved:** Professional communication is hard to practice without stakes. Ai-Coach provides safe, AI-driven simulations for negotiation, customer service, interviews, and interpersonal challenges.

**Tech Stack:** Laravel 11 + Vue 3 + Inertia.js + Tailwind CSS + Groq API

---

## ✅ What's Been Done

### **1. Core Architecture**

- ✅ Laravel 11 application fully configured
- ✅ Inertia.js + Vue 3 frontend integration (SPA-like experience)
- ✅ Tailwind CSS with custom theme (purple/pink/indigo gradients)
- ✅ Database schema with proper relationships (Users → Conversations → Messages → Scenarios)

### **2. Database & Models**

- ✅ **Users:** Authentication, profiles, account management
- ✅ **Scenarios:** 3 pre-seeded scenarios (Job Interview, Salary Negotiation, Angry Customer)
- ✅ **Conversations:** Tracks user practice sessions with scores
- ✅ **Messages:** Full chat history with role tracking (user/assistant/system)

**Relationships:**

```
User (1) ──→ (Many) Conversations
         ↓
      Scenario ──→ (Many) Conversations
                        ↓
                    (Many) Messages
```

### **3. Authentication** ✅

- Registration with email validation
- Login/logout
- Password reset workflow
- Profile management (update name/email, change password, delete account)
- Session-based auth protecting all practice routes

**Controllers:** `AuthenticatedSessionController`, `RegisteredUserController`, `PasswordController`, `VerifyEmailController`

### **4. Scenario Management** ✅

**Pre-seeded Scenarios:**

1. **Job Interview** (Software Engineer)
    - Objectives: Articulate experience, demonstrate problem-solving, ask insightful questions

2. **Salary Negotiation** (HR Manager)
    - Objectives: State target salary, counter pushback, negotiate beyond base salary

3. **Angry Customer** (Frustrated Customer)
    - Objectives: Acknowledge frustration, take ownership, offer specific solution

**Features:**

- Scenario preview modals with role context
- Objectives displayed before starting session
- System prompts guide AI behavior
- User/AI role definitions

### **5. Real-Time Conversation Engine** ✅

- Users create new conversation by selecting scenario
- AI opens with initial message (Groq API)
- Bidirectional messaging with persistent history
- Auto-complete after 10 user turns
- Message roles tracked (user/assistant/system)

**Key Features:**

- Real-time UI updates via Inertia.js
- Voice input support (Web Speech API)
- Typing indicators
- Auto-scrolling chat feed
- Session status tracking (active → completed)

### **6. AI Integration (Groq API)** ✅

**Configuration:**

```env
GROQ_API_KEY=<your_api_key>
GROQ_MODEL=mixtral-8x7b-32768
```

**Conversation Flow:**

1. **Opening:** Scenario system prompt + opening instruction → Groq → AI greeting
2. **Chat:** Full message history + system instruction → Groq → AI response (max 150 tokens)
3. **Scoring:** Transcript formatted → Groq evaluation prompt → Returns 4 scores + feedback

**Key Parameters:**

- Temperature: 0.4 (deterministic but natural)
- Max tokens: 150 per response (forces conciseness)
- System instructions enforce natural dialogue (no emails, stay in character)

### **7. Performance Scoring System** ✅

After session completion, AI evaluates on 4 dimensions:

- **Clarity** (0-100): Communication clarity
- **Confidence** (0-100): Assertiveness and confidence
- **Objective** (0-100): Goal achievement
- **Adaptability** (0-100): Responsiveness to challenges
- **Final Score:** Average of 4 dimensions

Scores stored as JSON in `conversations.scores` with AI-generated feedback.

### **8. Frontend Pages** ✅

| Page               | Purpose                                       | File                                         |
| ------------------ | --------------------------------------------- | -------------------------------------------- |
| Dashboard          | User stats, recent sessions, quick-start      | `resources/js/Pages/Dashboard.vue`           |
| Scenarios Index    | Grid of all scenarios with preview modal      | `resources/js/Pages/Scenarios/Index.vue`     |
| Conversation Show  | Live chat interface with AI                   | `resources/js/Pages/Conversations/Show.vue`  |
| Conversation Index | User's full practice history                  | `resources/js/Pages/Conversations/Index.vue` |
| Profile Edit       | Update name/email/password, delete account    | `resources/js/Pages/Profile/Edit.vue`        |
| Auth Pages         | Login, Register, Password Reset, Email Verify | `resources/js/Pages/Auth/*`                  |

### **9. Responsive UI & UX** ✅

- Mobile-first design with Tailwind CSS
- Dark mode support (CSS custom properties)
- Desktop table layout → mobile card layout fallback
- Sidebar collapses on small screens
- Gradient-colored scenario cards (purple/pink/indigo)
- Consistent typography (Plus Jakarta Sans font)

### **10. Controllers & Routes** ✅

```
GET    /dashboard                        Dashboard display
GET    /scenarios                        Browse all scenarios
POST   /conversations                    Start new session
GET    /conversations                    View practice history
GET    /conversations/{id}               View specific session
POST   /conversations/{id}/messages      Send user message
POST   /conversations/{id}/complete      End session & score
```

**Controllers:**

- `DashboardController` → aggregates user stats
- `ScenarioController` → fetches scenarios
- `ConversationController` → manages sessions
- `MessageController` → handles chat messages
- `ProfileController` → user account management

### **11. Database Migrations** ✅

```
✅ 0001_01_01_000000 - Create users table
✅ 0001_01_01_000001 - Create cache table (jobs)
✅ 0001_01_01_000002 - Create jobs table
✅ 2026_05_21_095224 - Create scenarios table
✅ 2026_05_22_095224 - Create conversations table
✅ 2026_05_23_095224 - Create messages table (with role enum)
✅ 2026_05_24_200200 - Add user_id FK to conversations
✅ 2026_06_02_130500 - Add user_role, ai_role, objectives to scenarios
```

### **12. Testing Scaffold** ✅

- Basic auth tests in `tests/Feature/Auth/`
- Profile tests in `tests/Feature/ProfileTest.php`
- PHPUnit configured

---

## ❌ What Remains

| Feature                            | Status        | Priority | Notes                                                          |
| ---------------------------------- | ------------- | -------- | -------------------------------------------------------------- |
| **Voice Input Refinement**         | ⚠️ Partial    | Medium   | Web Speech API integrated; needs browser compatibility testing |
| **Email Verification Enforcement** | ⚠️ Scaffolded | Medium   | Currently optional; not enforced in practice flow              |
| **Admin Scenario Editor**          | ❌ Missing    | Low      | Scenarios seeded only; no UI to create/edit/delete scenarios   |
| **Advanced Analytics**             | ❌ Missing    | Low      | No performance trends, no detailed reports                     |
| **Multi-Language Support**         | ❌ Missing    | Low      | Currently English-only                                         |
| **API Documentation**              | ❌ Missing    | Low      | No OpenAPI/Swagger specs                                       |
| **Comprehensive Tests**            | ⚠️ Minimal    | Medium   | Need expanded coverage for conversations & scoring             |
| **Rate Limiting**                  | ❌ Missing    | Medium   | No API rate limits; should protect Groq API calls              |
| **Caching Layer**                  | ❌ Missing    | Low      | No Redis/caching for scenario queries                          |
| **Deployment Configuration**       | ⚠️ Partial    | Medium   | No `.env.production`, no CI/CD pipeline                        |
| **Error Handling**                 | ⚠️ Basic      | Medium   | Need better Groq API error handling                            |
| **Session Timeout**                | ⚠️ Basic      | Low      | Could add warning before session expiration                    |

---

## 📊 Database Schema Quick Reference

### **Users Table**

```sql
id, name, email, email_verified_at, password, remember_token, created_at, updated_at
```

### **Scenarios Table**

```sql
id, title, description, system_prompt, user_role, ai_role,
objectives (JSON array), created_at, updated_at
```

### **Conversations Table**

```sql
id, user_id (FK → users), scenario_id (FK → scenarios),
scores (JSON: {clarity, confidence, objective, adaptability, final, feedback}),
status (enum: 'active', 'completed'), created_at, updated_at
```

### **Messages Table**

```sql
id, conversation_id (FK → conversations), role (enum: 'user', 'assistant', 'system'),
content (text), created_at, updated_at
```

---

## 🏗️ Project Structure

```
Ai-Coach/
├── app/
│   ├── Models/
│   │   ├── User.php                    # Authenticatable
│   │   ├── Scenario.php                # HasMany Conversations
│   │   ├── Conversation.php            # BelongsTo User/Scenario, HasMany Messages
│   │   └── Message.php                 # BelongsTo Conversation
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── ScenarioController.php
│   │   │   ├── ConversationController.php
│   │   │   ├── MessageController.php
│   │   │   ├── ProfileController.php
│   │   │   └── Auth/
│   │   │       ├── RegisteredUserController.php
│   │   │       ├── AuthenticatedSessionController.php
│   │   │       └── [other auth controllers]
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/                   # Form validation
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/                     # All 8 migrations
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── ScenarioSeeder.php          # Seeds 3 scenarios
│   └── factories/
│       └── UserFactory.php
├── routes/
│   ├── web.php                         # All authenticated routes
│   ├── auth.php                        # Scaffolded auth routes
│   └── console.php
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Scenarios/
│   │   │   │   └── Index.vue
│   │   │   ├── Conversations/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Show.vue
│   │   │   ├── Auth/                   # 6 auth pages
│   │   │   ├── Profile/
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Partials/           # Form components
│   │   │   └── Welcome.vue             # Landing page
│   │   ├── Layouts/
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   ├── Components/                 # Shared Vue components
│   │   ├── app.js                      # Vue + Inertia entry
│   │   └── bootstrap.js
│   ├── css/
│   │   └── app.css                     # Tailwind + custom vars
│   └── views/
│       └── app.blade.php               # Inertia root
├── config/
│   ├── app.php, auth.php, database.php
│   ├── services.php                    # Groq config
│   └── [10+ other configs]
├── tests/
│   ├── Feature/
│   │   ├── Auth/                       # Auth tests
│   │   └── ProfileTest.php
│   └── Unit/
├── public/
│   ├── index.php                       # Entry point
│   └── build/                          # Compiled assets
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── .env                                # Environment config
├── .env.example
├── composer.json                       # PHP dependencies
├── package.json                        # JS/Node dependencies
├── vite.config.js
├── tailwind.config.js
├── phpunit.xml
├── README.md
└── CONTEXT_CAPSULE.md                 # ← THIS FILE
```

---

## 🚀 Quick Start

### **Installation**

```bash
cd /var/www/html/Ai-Coach

# Install dependencies
composer install
npm install

# Create .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Set up Groq API key
# Edit .env: GROQ_API_KEY=your_groq_api_key
```

### **Database Setup**

```bash
# Create database
php artisan migrate --seed

# This will:
# - Create all tables
# - Seed 3 pre-configured scenarios
# - Set up authentication tables
```

### **Development**

```bash
# Terminal 1: Vite watch
npm run dev

# Terminal 2: Laravel dev server
php artisan serve

# Access: http://localhost:8000
```

### **Testing**

```bash
php artisan test
./vendor/bin/phpunit
```

---

## 🔑 Key Environment Variables

```env
# App
APP_NAME=Ai-Coach
APP_ENV=local|production
APP_DEBUG=true|false
APP_KEY=                        # Generated via php artisan key:generate

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_coach
DB_USERNAME=root
DB_PASSWORD=

# Groq API (REQUIRED for AI functionality)
GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
GROQ_MODEL=mixtral-8x7b-32768  # or other Groq model

# Mail (optional)
MAIL_MAILER=smtp|log|sendmail
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

# Session
SESSION_DRIVER=database
CACHE_DRIVER=database
```

---

## 🎨 Design System

### **Colors**

- **Primary:** Purple (`#7c3aed`)
- **Secondary:** Pink (`#ec4899`)
- **Tertiary:** Indigo (`#6366f1`)
- **Background:** Light purple (`#f0ebff`)
- **Text:** Dark purple (`#1e0a4a`)
- **Text 2:** Medium purple (`#6b5b9a`)

### **Typography**

- **Font:** Plus Jakarta Sans (Google Fonts)
- **Weights:** 300, 400, 500, 600, 700, 800

### **Spacing & Sizing**

- Responsive design via Tailwind (xs, sm, md, lg, xl, 2xl)
- Mobile-first approach
- Grid layouts with auto-fit

---

## 🔄 User Flow

```
1. User visits http://localhost:8000
   ↓
2. Redirects to login (if not authenticated)
   ↓
3. Register/Login
   ↓
4. Dashboard (stats, recent sessions)
   ↓
5. Browse Scenarios → Select scenario → Preview modal
   ↓
6. Click "Start Session" → Create Conversation record
   ↓
7. AI sends opening message
   ↓
8. User types response → saves as message, calls Groq, gets AI response
   ↓
9. Repeat 8-10 user turns
   ↓
10. Auto-complete → Backend scores conversation
    ↓
11. Groq returns: clarity, confidence, objective, adaptability, feedback
    ↓
12. Display scores + feedback on Show page
    ↓
13. View all sessions in Conversations/Index
```

---

## 🔐 Security Considerations

- ✅ CSRF protection (Laravel built-in)
- ✅ Password hashing (bcrypt)
- ✅ Authentication middleware on all protected routes
- ✅ Form validation on all requests
- ⚠️ **TODO:** Rate limiting on Groq API calls
- ⚠️ **TODO:** API key rotation mechanism
- ⚠️ **TODO:** Input sanitization for chat messages

---

## 📈 Performance Notes

- **Conversations:** Stored with full message history (no pagination yet)
- **Groq API:** Each message = 1 API call; should implement caching/queue
- **Database:** Simple structure; can scale with proper indexing
- **Frontend:** Vue 3 reactivity; Inertia lazy loads components

---

## 🐛 Known Issues & Caveats

1. **Voice Input:** Web Speech API only works in Chrome/Edge; fallback needed for Safari
2. **Email Verification:** Scaffolded but not enforced; users can practice without verifying
3. **Session Timeout:** No warning; sessions timeout silently
4. **Groq API Errors:** Limited error handling; should retry or queue failed messages
5. **Mobile UX:** Avatar display needs adjustment on small screens
6. **Dark Mode:** CSS custom properties work, but theme toggle not fully implemented

---

## 🚀 Next Steps (Recommended Priority)

1. **High:** Add comprehensive test coverage for conversations & scoring
2. **High:** Implement Groq API rate limiting & error handling
3. **Medium:** Add email verification enforcement
4. **Medium:** Create admin scenario editor UI
5. **Medium:** Add voice input browser compatibility fixes
6. **Low:** Implement caching layer for scenarios
7. **Low:** Add analytics dashboard
8. **Low:** Multi-language support (i18n)

---

## 📚 Useful Commands

```bash
# Migrations
php artisan migrate                # Run all migrations
php artisan migrate:rollback       # Rollback last batch
php artisan migrate:reset          # Rollback all & re-run
php artisan migrate:refresh --seed # Rollback + re-run + seed

# Tinker (REPL)
php artisan tinker
>>> User::count()                  # Count users
>>> Scenario::all()                # List all scenarios

# Build & Deploy
npm run build                      # Build assets for production
php artisan cache:clear            # Clear app cache

# Code Quality
./vendor/bin/phpstan analyse       # Static analysis (if installed)
./vendor/bin/pint                  # PHP code formatting
```

---

## 📞 Support & Documentation

- **Laravel Docs:** https://laravel.com/docs
- **Vue 3 Docs:** https://vuejs.org
- **Inertia.js:** https://inertiajs.com
- **Tailwind CSS:** https://tailwindcss.com
- **Groq API:** https://console.groq.com/docs

---

## ✨ Summary

**Ai-Coach** is a well-structured, feature-complete platform for practicing communication skills with AI. The core functionality is solid and production-ready. The main work remaining is quality-of-life improvements (better error handling, admin tools, analytics) and testing. The codebase follows Laravel and Vue.js best practices with clean architecture, proper relationships, and responsive UI design.

**Status:** ✅ Core complete | ⚠️ Minor refinements needed | 🚀 Ready for deployment (with additional testing)
