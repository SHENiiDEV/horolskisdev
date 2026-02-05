# Deploying to Beget.com (Shared Hosting)

This guide outlines the steps to deploy the **DevOps Helper** application to a Beget.com shared hosting environment.

## Prerequisites

1.  **Beget Account**: Access to your hosting control panel.
2.  **SSH Access**: Enabled in the Beget control panel (crucial for running commands).
3.  **Domain**: A domain added to your Beget account.

---

## Step 1: Prepare the Application Locally

Since shared hosting environments often have limited resources or lack Node.js, it is best to build your frontend assets (CSS/JS) locally.

1.  **Build Assets**:
    Open your terminal in the project folder and run:
    ```bash
    npm install
    npm run build
    ```
    This will generate the compiled assets in `public/build`.

2.  **Prepare for Upload**:
    You can zip your entire project folder, **excluding** `node_modules`. You can choose to include `vendor` (if you ran `composer install`) or run composer on the server (recommended).

    *Recommended*: Zip the project excluding `node_modules` and `.git`.
    ```bash
    zip -r website.zip . -x "node_modules/*" -x ".git/*"
    ```

---

## Step 2: Upload Files to Server

1.  **Log in via SFTP/FTP** or use the **Beget File Manager**.
2.  Create a folder for your project. A good practice is to keep it *outside* the public web root if possible, or organize it cleanly.
    *   Example: `/home/u/user/devops-helper`
3.  Upload `website.zip` to this folder.
4.  Unzip the archive.
    *   Via SSH: `unzip website.zip`
    *   Via File Manager: Right-click -> Unzip.

---

## Step 3: Database Setup

1.  Go to the **Beget Control Panel** -> **MySQL**.
2.  Create a **New Database**.
    *   Note the `Database Name`.
    *   Note the `User Name` and `Password`.
3.  (Optional) If you have a local database dump, you can import it via phpMyAdmin.

---

## Step 4: Environment Configuration

1.  In your project folder on the server, rename `.env.example` to `.env`.
    ```bash
    cp .env.example .env
    ```
2.  Edit `.env` (via File Manager or `nano .env` in SSH).
3.  Update the following settings:

    ```ini
    APP_NAME="DevOps Helper"
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://your-domain.com

    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=your_beget_db_name
    DB_USERNAME=your_beget_db_user
    DB_PASSWORD=your_beget_db_password
    ```

---

## Step 5: Install Dependencies & Setup (via SSH)

Connect to your server via SSH (e.g., `ssh user@host`) and navigate to your project folder:
```bash
cd /home/u/user/devops-helper
```

1.  **Install PHP Dependencies**:
    ```bash
    composer install --optimize-autoloader --no-dev
    ```
    *(Note: If `composer` is not found, Beget usually has it installed. Try `/usr/local/bin/composer` or check their docs).*

2.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

3.  **Run Migrations**:
    ```bash
    php artisan migrate
    ```
    *(If you skipped importing a SQL dump, this will create the tables).*

4.  **Create Storage Link**:
    ```bash
    php artisan storage:link
    ```

---

## Step 6: Configure Web Server (Document Root)

To serve the application correctly, the web server needs to point to the `public` folder of your Laravel application.

1.  Go to **Beget Control Panel** -> **Sites**.
2.  Find your site/domain.
3.  Click on the **Gear icon** (Settings) or the folder path to change the **Document Root**.
4.  Change the path to point to your project's `public` folder.
    *   From: `/home/u/user/site.com/public_html`
    *   To: `/home/u/user/devops-helper/public`
5.  Save changes. Beget will restart the web server configuration automatically.

**Alternative (if you can't change Site Root):**
If you must use `public_html`, you can delete the default `public_html` folder and create a symbolic link:
```bash
cd /home/u/user/site.com
rm -rf public_html
ln -s /home/u/user/devops-helper/public public_html
```

---

## Step 7: Scheduler (Cron Job)

Laravel requires a cron job to run every minute to handle scheduled tasks.

1.  Go to **Beget Control Panel** -> **Crontab (Cron)**.
2.  Add a new task:
    *   **Time**: `* * * * *` (Every minute)
    *   **Command**:
        ```bash
        /usr/bin/php /home/u/user/devops-helper/artisan schedule:run >> /dev/null 2>&1
        ```
    *(Make sure to check the PHP path. In Beget, you might want to use a specific PHP version like `/usr/bin/php8.2`)*.

---

## Troubleshooting

*   **500 Error**: Check `storage/logs/laravel.log` or the web server error logs in Beget Control Panel.
*   **Permissions**: Ensure `storage` and `bootstrap/cache` directories are writable.
    ```bash
    chmod -R 775 storage bootstrap/cache
    ```
*   **Vite Assets 404**: Ensure you ran `npm run build` locally and uploaded the `public/build` folder successfully.
