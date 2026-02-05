# Deployment Guide (Beget Specific)

Based on your hosting instructions, here is how to deploy your **existing** code instead of installing a fresh Laravel.

## Prerequisites
- **Local:** Project ready with `npm run build` executed.
- **Server:** SSH access, Composer installed (as per hosting guide).

---

## Step 1: Prepare Local Files
1.  Run `npm run build` locally to generate assets.
2.  Zip your project files, **excluding** `node_modules`, `vendor`, and `.git`.
    ```bash
    # Example command (Mac/Linux)
    zip -r site.zip . -x "node_modules/*" -x "vendor/*" -x ".git/*" -x ".env"
    ```
    *(Note: We exclude `.env` to avoid overwriting server configs, but if you don't have one on the server yet, you can include `.env.example` and rename it later).*

## Step 2: Server Setup (SSH)
Connect to your server via SSH.

1.  **Set PHP Version** (as per instructions):
    ```bash
    export PATH=/usr/local/php/cgi/8.3/bin/:$PATH
    php -v
    ```
    *(Should show PHP 8.3.x)*

2.  **Create Project Folder**:
    Instead of `composer create-project`, we create the folder manually.
    ```bash
    # Replace 'horolskis' with your desired folder/site name
    mkdir horolskis
    cd horolskis
    ```

3.  **Upload & Unzip**:
    - Upload `site.zip` to this `horolskis` folder (via File Manager or SCP).
    - Unzip it:
      ```bash
      unzip site.zip
      rm site.zip
      ```

## Step 3: Install Dependencies
Now we install the PHP libraries using the hosting's specific Composer command.

```bash
COMPOSER_MEMORY_LIMIT=-1 ~/.local/bin/composer install --no-dev --optimize-autoloader
```

## Step 4: Site Configuration (Symlink)
This is the critical step from your hosting guide.

1.  **Create Site in Panel**: If you haven't already, go to Beget Panel -> Sites and create a site pointing to this folder (e.g., named `horolskis`).
2.  **Create Symlink**:
    The hosting creates a `public_html` folder by default. We need to replace it with a link to Laravel's `public`.

    ```bash
    # Ensure you are inside the project folder
    cd ~/horolskis
    
    # Remove the default empty public_html
    rm -rf public_html/
    
    # Create the link
    ln -s public public_html
    ```

## Step 5: Final Setup
1.  **Environment File**:
    ```bash
    cp .env.example .env
    nano .env
    ```
    - Set `APP_URL=http://your-domain.com`
    - Set Database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) matching what you created in the Panel.

2.  **Generate Key & Migrate**:
    ```bash
    php artisan key:generate
    php artisan storage:link
    php artisan migrate
    ```

3.  **Optimize**:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

## Summary of Commands for Future Updates
When you upload new code (e.g., via git pull or unzip):
```bash
export PATH=/usr/local/php/cgi/8.3/bin/:$PATH
cd horolskis
COMPOSER_MEMORY_LIMIT=-1 ~/.local/bin/composer install --no-dev
php artisan migrate
php artisan config:cache
```
