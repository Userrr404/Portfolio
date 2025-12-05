# 🚀 Personal Portfolio Website (PHP 8 + MVC + TailwindCSS + MySQL)

A modern, scalable, and production-ready **Portfolio Website** built with  
**PHP 8**, **Modern MVC structure**, **TailwindCSS**, **JavaScript**, and **MySQL**, following a clean, zero-crash, Fully structured Enterprise-Architecture, Fail-safe Models, Cache Engine, Dynamic Layouts, Auto URL Paths.


# This project is built with a zero-crash, self-healing backend architecture to always stay online:
  - If tables are empty → JSON defaults load  
  - If JSON missing → hard-coded fallbacks load  
  - Cache boosts performance automatically  
  - No controller or model can crash the page 
  - Auto-logs errors.
  - Environment-independent paths.
  - Clean MVC-ready folder structure.

---  

# 🔥 Key Concepts

### 🧠 **Zero-Crash Enterprise Backend Architecture & Unified 4-Layer Data Architecture**
Every page & model follows:

**A → B → C → D data fallback pipeline**
  **LEVEL**    **SOURCE**                                      **PURPOSE**
1.  **A.       Cache (storage/cache/*.json)                 Fastest response**
2.  **B.       MySQL Database                               Real data**
3.  **C.       JSON Defaults (app/resources/defaults/*)     Safe content if DB empty**
4.  **D.       Hard-coded fallbacks                         Last layer, prevents UI break**

This guarantees **no empty UI**, **zero fatal errors**, and **production reliability**.

---

## 🛠 Requirements
- PHP 8.x or newer
- MySQL (XAMPP recommended)
- Git (for version control)
- VS code (recommended)

---

The system includes:

- Dynamic asset loading  
- Auto-detected BASE_URL (no hardcoded paths)  
- Modular layouts (`layout_head.php`, `layout_foot.php`)  
- Clean folder architecture (MVC-ready)  
- Secure configuration handling  
- Logging system  
- Reusable components  
- SEO-friendly structure  

---

# 📌 Features

### 🧩 **Architecture**
- Fully modular MVC structure
- Clean folder structure (`config/`, `public/`, `rotues/`, `app/`)
- Dynamic header/footer with asset injection
- Auto-path resolver (no hardcoded URLs)
- Enterprise folder organization
- Centralized path resolver: `paths.php`
- Centralized configuration: `config.php`
- Environment-independent (localhost / domain / subfolder)
- Dynamic layout system (layout_head.php, layout_foot.php)
- Enterprise error-handling (ErrorHandler.php)
- Reusable model architecture
- Smart controllers with clean output
- Centralized helpers for clean code

### 💾 **Backend**
- PDO database connection wrapper & Singleton with auto-reconnect(`db_connection.php`)
- Query health-check (SELECT 1)
- Auto-fallback content (DB-failure proof)
- Centralized logger & Error logging (`logger.php`)
- Automatic caching engine (cache/*.json)
- Helpers for safe queries + sanitization
- Zero fatal errors (try/catch everywhere)
- Auto-resolving URLs & paths based on server environment

### 🎨 **Frontend**
- TailwindCSS + custom CSS + (CDN or local config supported)
- Smooth animations
- Fully responsive layout
- Modular JS (header.js, footer.js, scroll-progress.js)
- Structured assets folder
- SEO-optimized HTML structure

### 🔒 **Security**
- `.gitignore` prevents leaking secrets (`config/config.php`, `logs/`, `uploads/`, `storage/catch/`, `vendor`, `node_modules/`)
- `config_example.php` for safe public template
- Logs + uploads excluded from Git
- No credentials leak
- Sanitized output using helpers
- Secure mail system

### ✔ **Unified Model Architecture**
Every Model follows:

| Stage | Source | Description |
|-------|---------|-------------|
| A | Cache | Fastest response |
| B | DB | Fetch real data |
| C | JSON | User-editable defaults |
| D | Hard Fallback | Never-break guarantee |

### ✔ **Safe View Rendering**
All pages use:

```php
["from_db" => bool, "data" => []]

### Dynamic Content System
- All webpages load via Models --> cache --> DB --> .JSON --> Fallback
  - Home Section → HomeModel.php
  - About Section → AboutModel.php
  - Skills Section → SkillModel.php
  - Projects Section → ProjectModel.php
  - Contact Section → ContactModel.php

- Even If:
  - MySQL stops
  - A table is missing
  - A query fails
  - Hosting disconnects

---

# 📁 **Project Folder Structure**
Portfolio/
|
|── app/ # This folder contains all backend application logic & It is the “brain” of your portfolio
|    ├── Controllers/            # 🎯 Page Controllers
│    │   ├── HomeController.php       # Loads Homepage sections using unified flow`
│    │   ├── AboutController.php      # Loads About page (DB → JSON → fallback`)
│    │   ├── ProjectController.php    # Project pages with pagination + filters`
│    │   ├── NotesController.php      # Notes, categories, tags, pinned notes`
│    │   └── ContactController.php    # Developer contact info 
│    │
|    ├── Helpers/ # These files contain reusable PHP helper functions used across all pages.`)
|    |     ├── helpers.php # Keep code DRY and avoid repeating logic everywhere.`)
|    |     ├── sanitizer.php # Security layer for all user-facing output.`)
│    │     ├── view_helpers.php # Keep view logic clean without mixing PHP logic in templates.`)
│    │     ├── logger.php # Logging utility
|    |
|    ├── Models/ # Models are responsible for data fetching, fallback values, and business logic. Views NEVER touch SQL; all SQL is inside Models. (DB → Cache → JSON → Fallback)
│    │    ├── AboutModel.php # Loads about sections using DB → JSON → fallback`)
│    │    ├── ContactModel.php # Contact section loader`)
│    │    ├── HomeModel.php    # Homepage model with unified architecture`)
│    │    ├── NoteModel.php     # Notes, categories, tags, pinned notes`)
│    │    ├── ProjectModel.php  # Project list, filters, pagination, tech relations`)
│    │    ├── SkillModel.php # Skill icons + categories with fallback`)
│    │
|    ├── Servicess/ # These are services — reusable backend components.`)
│    |    ├── CacheService.php # JSON caching (fast responses)`)
│    |    ├── MailService.php # Email handler (contact form)`)
│    │    ├── HeaderData.php  # Dynamic header data provider
│    │    └── FooterData.php  # Dynamic footer data provider
│    ├── core/ # Core contains the foundation of your backend system.`)
│    │    ├── Controller.php
│    │    ├── db_connection.php # PDO connection wrapper
│    │    ├── ErrorHandler.php
│    │    ├── HeaderData.php # Dynamic header data provider
│    │    ├── FooterData.php # Dynamic footer data provider
│    │
│    ├── views/
│    │    ├── components/         # components
│    │    ├── layouts/            # 🖼 Layout System
│    │    │     ├── layout_head.php     # <head> section + CSS/JS inject
│    │    │     ├── layout_foot.php     # Footer scripts
│    │    │     ├── header.php          # Navigation bar
│    │    │     ├── footer.php          # Footer UI
│    │    │
│    │    ├── home/
│    │    │     ├── index.php
│    │    │
│    │    ├── pages/              # 📄 Page Views
│    │          ├── about.php
│    │          ├── projects.php
│    │          ├── notes.php
│    │          ├── contact.php
│    │
│    │      
│    ├── resources/
│       └── defaults/              # JSON fallback files when DB is empty)
│           │
│           ├── about/                 # JSON defaults for About page)
│           │   ├── content.json
│           │   ├── education.json
│           │   ├── experience.json
│           │   ├── hero.json
│           │   ├── skills.json
│           │   ├── stats.json
│           │
│           ├── contact/                 # JSON defaults for About contact)
│           │   ├── content_hero.json
│           │   ├── contact_info.json
│           │   ├── contact_map.json
│           │   ├── contact_socials.json
│           │   ├── conatct_toast.json
│           │
│           ├── home/                  # Defaults for Home page sections)
│           │   ├── about.json
│           │   ├── contact.json
│           │   ├── home.json
│           │   ├── projects.json
│           │   ├── skills.json
│           │
│           ├── notes/                 # Notes system defaults)
│           │   ├── categories.json
│           │   ├── notes.json
│           │   ├── pinned.json
│           │   ├── tags.json
│           │
│           └── projects/              # Project page fallback data)
│               ├── featured.json
│               ├── projects.json
│               ├── tech_list.json
│
│── config/ # Configuration files that initialize everything.`)
│    ├── config.php # Private config (ignored by Git)`)
│    ├── config_example.php # Public-safe template`)
│    ├── env.php # Loads hosting provider environment variables`)
│    ├── paths.php # Auto URL + PATH generator`)
│
│── logs/ # Debugging and monitoring.`)
│     ├── app.log # Runtime logs (ignored by Git)
│
│── uploads/ # User uploads (ignored by Git)
│
│── public/
│     ├── assets/ # Contains all public-facing files (CSS, JS, images). This folder loads directly in the browser.`)
│     │      ├── css/                        # All stylesheet files`)
│     │      │    ├── about.css
│     │      │    ├── animations.css
│     │      │    ├── footer.css
│     │      │    ├── global.css
│     │      │    ├── header.css
│     │      │    ├── index.css
│     │      │    ├── notes.css
│     │      │
│     │      ├── js/                         # All dynamic client-side JS logic`)
│     │      │   ├── about.js
│     │      │   ├── footer.js
│     │      │   ├── header.js
│     │      │   ├── index.js
│     │      │   ├── notes.js
│     │      │   ├── projects.js
│     │      │   ├── scroll-progress.js
│     │      │   ├── tailwind-config-global.js
│     │      │   ├── tailwind-config.js
│     │      │
│     │      ├── projects/                   # All projects images
│     │      └── images/                     # All website images, icons, thumbnails`)
│     │
│     ├── downloads/                # Resume
│     │       └── .pdf
│     ├── bootstrap.php # GLOBAL BOOTSTRAP — loads everything required files for each page
│     ├── index.php # Homepage
│     ├── about.php # About section
│     ├── projects.php # Portfolio projects
│     ├── notes.php # Notes / blogs
│     ├── contact.php # Contact page
│     ├── downloadcv.php # download the CV
│
├── routes/
│    └── web.php
├── storage/
│     ├── cache/                  # ⚡ Cached JSON files (ignored from Git)
│         ├── *.json  # Improves performance dramatically`)
│
├── vendor/
├── .env
├── .gitignore
├── .htaccess
├── README.md


---

## 🔐 Sensitive Files (NOT uploaded to GitHub)

  - `.gitignore` protects these:
    ── config/config.php # Project sensitive data (ignored by Git)
    ── logs/ # Logs file (ignored by Git)
    ── uploads/ # User uploads (ignored by Git)

---


### 1️⃣ Clone the repository
```bash
git clone https://github.com/Userrr404/Portfolio.git
cd Portfolio

### 2️⃣ Create your secure config
cp config/config_example.php config/config.php

    - Then open config.php and set:
      ── DB_HOST=localhost
      ── DB_NAME=portfolio
      ── DB_USER=root
      ── DB_PASS=

### 3️⃣ Import the MySQL database
    - CREATE DATABASE portfolio;
    - Then import SQL file
      ── phpMyAdmin → Import → <your_database>.sql

### 4️⃣ Move project to XAMPP + Run
    - Move the project to:
      ── C:\xampp\htdocs\Portfolio\

    - Start Apache + MySQL
      ── Then open:
        -- http://localhost/Portfolio/

---

🎯 Final Notes

### This architecture is built for:
  - Real production hosting
  - Zero downtime
  - Easy editing (JSON defaults)
  - Speed (automatic caching)
  - Expandability (clean Controllers/Models)