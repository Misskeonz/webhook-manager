# 🚀 Git Webhook Manager

A comprehensive Laravel-based webhook management system for automated Git deployments from GitHub and GitLab. This application allows you to configure webhooks, manage SSH keys, and automate your deployment workflow with a beautiful Bootstrap 5 interface.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)

## ✨ Features

- 🎯 **Multi-Provider Support** - Works with GitHub and GitLab
- 🔐 **Auto SSH Key Generation** - Unique SSH key pairs for each webhook
- 👤 **Deploy User Control** - Execute deployments as specific system users
- 📊 **Beautiful Dashboard** - Modern Bootstrap 5 UI with statistics
- 🔄 **Automated Deployments** - Trigger deployments via webhooks or manually
- 📝 **Deployment History** - Track all deployments with detailed logs
- 🔒 **Webhook Verification** - Secure webhook signatures validation
- ⚙️ **Pre/Post Deploy Scripts** - Run custom commands before and after deployment
- 🚦 **Queue System** - Asynchronous deployment processing
- 📱 **Responsive Design** - Works on all devices
- 🎨 **PSR-Compliant Code** - Clean, maintainable codebase

## 📋 Requirements

- PHP >= 8.2
- Composer
- Laravel 12.x
- Database (MySQL, PostgreSQL, SQLite, etc.)
- Git
- SSH (ssh-keygen command)
- Queue worker (for background processing)

## 🔧 Installation

### 1. Clone or Setup Project

```bash
# If cloning
git clone <your-repo-url>
cd git-webhook

# Install dependencies
composer install
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=git_webhook
DB_USERNAME=root
DB_PASSWORD=

# Configure queue connection (database recommended)
QUEUE_CONNECTION=database
```

### 3. Database Migration

```bash
# Run migrations
php artisan migrate

# Or run with fresh installation
php artisan migrate:fresh
```

### 4. Build Assets

```bash
# Build frontend assets
npm run build

# Or for development
npm run dev
```

### 5. Start Queue Worker

**Important:** The queue worker must be running for deployments to work!

```bash
# Start queue worker
php artisan queue:work

# Or use queue:listen for development
php artisan queue:listen
```

### 6. Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Access the application at
# http://localhost:8000
```

## 📖 Usage Guide

### Creating a Webhook

1. **Navigate to Webhooks** → Click "Create Webhook"
2. **Fill in Basic Information:**
   - **Name:** Descriptive name for your webhook
   - **Domain:** Optional website reference
   - **Status:** Active/Inactive

3. **Configure Repository:**
   - **Git Provider:** GitHub or GitLab
   - **Repository URL:** SSH or HTTPS URL (e.g., `git@github.com:user/repo.git`)
   - **Branch:** Branch to deploy (e.g., `main`, `develop`)
   - **Local Path:** Absolute path for deployment (e.g., `/var/www/html/myproject`)
   - **Deploy User:** User to execute deployment commands (e.g., `www-data`, `deployer`, `nginx`)

4. **SSH Key Configuration:**
   - Check "Auto-generate SSH Key Pair" to create unique SSH keys
   - Public key will be shown after creation

5. **Deploy Scripts (Optional):**
   - **Pre-Deploy Script:** Commands to run before deployment
   - **Post-Deploy Script:** Commands to run after deployment

### Setting Up Git Provider Webhook

#### For GitHub:

1. Go to your repository → **Settings** → **Webhooks** → **Add webhook**
2. **Payload URL:** Copy from webhook details page
3. **Content type:** `application/json`
4. **Secret:** Copy the secret token from webhook details
5. **Which events?** Just the push event
6. **Active:** ✓ Checked

#### For GitLab:

1. Go to your repository → **Settings** → **Webhooks** → **Add webhook**
2. **URL:** Copy from webhook details page
3. **Secret Token:** Copy from webhook details
4. **Trigger:** Push events
5. **SSL verification:** Enable SSL verification

### Adding SSH Deploy Key

#### For GitHub:

1. Go to repository → **Settings** → **Deploy keys** → **Add deploy key**
2. **Title:** Webhook Deploy Key
3. **Key:** Paste the public SSH key from webhook details
4. **Allow write access:** Not required (read-only is fine)

#### For GitLab:

1. Go to repository → **Settings** → **Repository** → **Deploy Keys**
2. **Title:** Webhook Deploy Key
3. **Key:** Paste the public SSH key
4. Click **Add key**

### Manual Deployment

1. Navigate to **Webhooks** → Select your webhook
2. Click **Deploy Now** button
3. Deployment will be queued and processed by queue worker
4. View deployment status in real-time

### Viewing Deployment Logs

1. Navigate to **Deployments** or click on a deployment
2. View detailed logs including:
   - Deployment status
   - Commit information
   - Terminal output
   - Error messages (if failed)
   - Execution time

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php      # Dashboard & statistics
│   ├── WebhookController.php        # Webhook CRUD operations
│   ├── DeploymentController.php     # Deployment management
│   └── WebhookHandlerController.php # Webhook API handler
├── Jobs/
│   └── ProcessDeployment.php        # Async deployment job
├── Models/
│   ├── Webhook.php                  # Webhook model
│   ├── SshKey.php                   # SSH key model
│   └── Deployment.php               # Deployment model
└── Services/
    ├── SshKeyService.php            # SSH key generation
    └── DeploymentService.php        # Deployment logic

resources/views/
├── layouts/
│   └── app.blade.php                # Main Bootstrap 5 layout
├── dashboard.blade.php              # Dashboard view
├── webhooks/                        # Webhook views
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
└── deployments/                     # Deployment views
    ├── index.blade.php
    └── show.blade.php

database/migrations/
├── 2024_01_01_000001_create_webhooks_table.php
├── 2024_01_01_000002_create_ssh_keys_table.php
└── 2024_01_01_000003_create_deployments_table.php
```

## 🎯 Example Post-Deploy Scripts

### Laravel Application:
```bash
#!/bin/bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm install
npm run build
```

### Node.js Application:
```bash
#!/bin/bash
npm install --production
npm run build
pm2 restart app
```

### Static Website:
```bash
#!/bin/bash
npm install
npm run build
rsync -avz dist/ /var/www/html/
```

## 🔒 Security Best Practices

1. **Never commit `.env` file** - Contains sensitive credentials
2. **Use unique secret tokens** - Auto-generated per webhook
3. **Enable webhook signature verification** - Always verify signatures
4. **Restrict file permissions** - Ensure proper permissions on deployment directories
5. **Use read-only deploy keys** - Don't give write access unless necessary
6. **Run queue worker as limited user** - Don't run as root
7. **Validate deploy scripts** - Review scripts before saving

## 🐛 Troubleshooting

### Deployments Not Processing

**Problem:** Deployments stuck in "pending" status

**Solution:**
- Ensure queue worker is running: `php artisan queue:work`
- Check queue table: `SELECT * FROM jobs;`
- Review logs: `tail -f storage/logs/laravel.log`

### SSH Key Permission Denied

**Problem:** Git clone/pull fails with permission denied

**Solution:**
- Verify SSH key is added to Git provider
- Check key permissions: `chmod 600 storage/app/temp/temp_key_*`
- Test SSH connection: `ssh -T git@github.com`

### Webhook Not Triggering

**Problem:** Git provider webhook not triggering deployments

**Solution:**
- Verify webhook URL is correct and accessible
- Check webhook secret token matches
- Review Git provider webhook delivery logs
- Ensure webhook is active

### Permission Issues

**Problem:** Cannot write to deployment directory

**Solution:**
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/myproject

# Set proper permissions
sudo chmod -R 755 /var/www/html/myproject
```

### Deploy User Configuration

**Feature:** Execute deployment commands as specific system user

**Use Case:**
- When deployment path is owned by a different user
- For better security and permission management
- To isolate deployment processes

**Setup:**
1. Configure sudo permissions (see `DEPLOYMENT_USER.md` for details)
2. Set deploy user in webhook configuration
3. Ensure user has proper path permissions

**Example:**
```bash
# Configure sudoers
sudo visudo -f /etc/sudoers.d/laravel-webhook

# Add:
www-data ALL=(ALL) NOPASSWD: /usr/bin/git
www-data ALL=(ALL) NOPASSWD: /bin/bash
```

📖 **Full Documentation:** See [DEPLOYMENT_USER.md](DEPLOYMENT_USER.md) for comprehensive guide

## 🚀 Production Deployment

### 1. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 2. Setup Supervisor for Queue Worker

Create `/etc/supervisor/conf.d/git-webhook-worker.conf`:

```ini
[program:git-webhook-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasflimit=3600
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start git-webhook-worker:*
```

### 3. Setup Nginx (Example)

```nginx
server {
    listen 80;
    server_name webhook.example.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📝 Code Standards

This project follows **PSR-12** coding standards:

- ✅ PSR-4 autoloading
- ✅ Type declarations
- ✅ Proper docblocks
- ✅ Meaningful variable names
- ✅ Single responsibility principle

## 🤝 Contributing

Contributions are welcome! Please ensure your code:

1. Follows PSR-12 standards
2. Includes proper documentation
3. Has meaningful commit messages
4. Is tested before submission

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 💬 Support

For issues, questions, or suggestions:
- Create an issue in the repository
- Check existing documentation
- Review troubleshooting section

---

**Built with ❤️ using Laravel 12 & Bootstrap 5**

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
