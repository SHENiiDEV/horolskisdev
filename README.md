# HorolskisDev / DevOps Helper

Typically used for freelance developers or small agencies to manage clients, projects, tasks, time tracking, and invoicing.

## 🚀 Features

### 👥 Client & Project Management
- **Clients**: Manage client profiles with detailed information.
- **Projects**: Organize work by projects, linked to specific clients.
- **Vault**: Securely store credentials (SSH keys, passwords, API tokens) for each project.

### 📋 Task Management & Kanban
- **Global Kanban**: View all tasks across all projects in a single board.
- **Project Kanban**: Filter tasks by specific project.
- **Client Kanban**: dedicated board for clients to view their project status.
- **Task Details**: Assignments, functional and non-functional requirements, priorities.

### ⏱️ Time Tracking
- **Real-time Timer**: Start and stop timers on specific tasks.
- **Manual Entry**: Manually add time entries for past work.
- **Editing**: Update descriptions, start/end times, or duration of existing entries.
- **History**: View time logs by project or globally.

### 💰 Invoicing
- **Generate Invoices**: Create invoices directly from completed tasks or manually.
- **Multi-language**: Support for English (EN) and Latvian (LV) languages.
- **PDF Generation**: Download invoices as PDF (uses DomPDF).
- **Status Tracking**: Draft, Sent, Paid statuses.

### 🔔 Integrations
- **Telegram**: Notification system for task updates (configured via `.env`).

---

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Blade, Alpine.js, Tailwind CSS
- **Database**: MySQL / SQLite
- **Assets**: Vite
- **PDF**: barryvdh/laravel-dompdf

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/SHENiiDEV/horolskisdev.git
   cd devops-helper
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your database settings in `.env`*

4. **Database Migration**
   ```bash
   php artisan migrate
   ```

5. **Build Assets**
   ```bash
   npm run build
   ```

6. **Run Server**
   ```bash
   php artisan serve
   ```

## 📦 Deployment

A deployment archive `devops-helper-deploy.zip` is generated in the root directory, containing pre-built assets and excluding development files.

To deploy manually:
1. Upload `devops-helper-deploy.zip`.
2. Unzip on server.
3. Run `composer install --no-dev`.
4. Run `php artisan migrate --force`.
5. Ensure `storage` and `bootstrap/cache` are writable.
