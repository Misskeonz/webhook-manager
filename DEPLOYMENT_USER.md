# 👤 Deployment User Configuration

## 📖 Overview

The **Deploy User** feature allows you to specify the user that will execute deployment commands. This is important when the deployment path is owned by a different user than the Laravel app user (typically `www-data`).

## 🎯 Use Case

### Scenario 1: Path Owned by Different User
```bash
# Path owned by: deployer
/var/www/myproject -> owner: deployer:deployer

# Laravel app runs as: www-data
# Result: Permission denied when git pull
```

**Solution:** Set `deploy_user` to `deployer`

### Scenario 2: Multiple Users Need Access
```bash
# Development server
Path: /home/ubuntu/projects/site
Owner: ubuntu

# Laravel runs as: www-data
```

**Solution:** Set `deploy_user` to `ubuntu`

## ⚙️ Configuration

### 1. Webhook Form

When creating/editing a webhook, fill in the **Deploy User** field:

```
Deploy User: [deployer]

Common users:
- www-data (default web server user)
- deployer (dedicated deployment user)
- ubuntu (AWS/Ubuntu servers)
- nginx (Nginx user)
- forge (Laravel Forge)
```

### 2. Sudo Configuration (IMPORTANT!)

The Laravel app needs permission to run commands as other users. Configure `sudoers`:

```bash
# Edit sudoers file
sudo visudo -f /etc/sudoers.d/laravel-webhook
```

Add these lines:

```bash
# Allow www-data to run git commands as any user
www-data ALL=(ALL) NOPASSWD: /usr/bin/git

# Allow www-data to run bash scripts as any user
www-data ALL=(ALL) NOPASSWD: /bin/bash

# Optional: composer, npm (if used in deploy scripts)
www-data ALL=(ALL) NOPASSWD: /usr/bin/composer
www-data ALL=(ALL) NOPASSWD: /usr/bin/npm
```

**Save and test:**
```bash
# Test as www-data user
sudo -u www-data sudo -u deployer git --version
# Should work without password prompt
```

### 3. File Permissions

Ensure the deploy user has proper permissions:

```bash
# Option 1: Change ownership to deploy user
sudo chown -R deployer:deployer /var/www/myproject

# Option 2: Use ACL for multiple users
sudo setfacl -R -m u:www-data:rwx /var/www/myproject
sudo setfacl -R -d -m u:www-data:rwx /var/www/myproject
```

## 🔧 How It Works

### Backend Process

When deployment runs:

```php
// DeploymentService prepares command with sudo
$deployUser = $webhook->deploy_user; // e.g., 'deployer'

// Original command:
['git', 'pull', 'origin', 'main']

// Becomes:
['sudo', '-u', 'deployer', 'git', 'pull', 'origin', 'main']
```

### Commands Affected

All deployment commands will run as the specified user:

1. **Git Operations**
   - `git clone`
   - `git fetch`
   - `git reset --hard`

2. **Deploy Scripts**
   - Pre-deploy script
   - Post-deploy script

### Deployment Output

The log will show the user being used:

```
Running deployment as user: deployer

Cloning repository...
Cloning into '/var/www/myproject'...
Done.

✓ Deployment completed successfully!
```

## 🛡️ Security Considerations

### ⚠️ Important Rules:

1. **Be Specific in Sudoers**
   ```bash
   # ❌ DANGEROUS - Too broad
   www-data ALL=(ALL) NOPASSWD: ALL
   
   # ✅ SAFE - Specific commands only
   www-data ALL=(ALL) NOPASSWD: /usr/bin/git
   ```

2. **Limit User Access**
   - Only allow deployment to run as trusted users
   - Don't use `root` as deploy user

3. **Path Validation**
   - Validate `local_path` in controller
   - Don't allow paths outside designated deployment directories

4. **User Validation**
   - Regex validation: `/^[a-z_][a-z0-9_-]*$/`
   - Only alphanumeric, underscore, hyphen
   - Prevents command injection

## 📋 Examples

### Example 1: Laravel Forge Setup

```
Website: myapp.com
Path: /home/forge/myapp.com
Deploy User: forge
```

### Example 2: Ubuntu Server

```
Website: api.example.com
Path: /home/ubuntu/apps/api
Deploy User: ubuntu
```

### Example 3: Shared Hosting

```
Website: blog.site.com
Path: /var/www/html/blog
Deploy User: www-data
```

### Example 4: Docker Environment

```
Website: app.docker.local
Path: /var/www/app
Deploy User: www-data
# (No sudo needed if already running as correct user)
```

## 🔍 Troubleshooting

### Issue 1: "sudo: a password is required"

**Cause:** Sudoers not configured correctly

**Solution:**
```bash
# Check sudoers file exists
ls -la /etc/sudoers.d/laravel-webhook

# Verify NOPASSWD is set
sudo cat /etc/sudoers.d/laravel-webhook
```

### Issue 2: "Permission denied"

**Cause:** Deploy user doesn't have access to path

**Solution:**
```bash
# Check path ownership
ls -la /var/www/

# Fix ownership
sudo chown -R deployer:deployer /var/www/myproject

# Or use ACL
sudo setfacl -R -m u:deployer:rwx /var/www/myproject
```

### Issue 3: "sudo: unknown user: deployer"

**Cause:** User doesn't exist on system

**Solution:**
```bash
# Check user exists
id deployer

# Create user if needed
sudo useradd -m -s /bin/bash deployer
```

### Issue 4: Commands not running as expected

**Debug:**
```bash
# Check what user Laravel runs as
ps aux | grep php-fpm

# Test sudo manually
sudo -u www-data sudo -u deployer whoami
# Should output: deployer
```

## ✅ Best Practices

### 1. Use Dedicated Deploy User

Create a specific user for deployments:

```bash
# Create deploy user
sudo useradd -m -s /bin/bash deployer

# Add SSH key for Git
sudo -u deployer ssh-keygen -t ed25519 -C "deployer@server"
```

### 2. Minimum Permissions

Only grant what's needed:

```bash
# Deployment directory only
/var/www/myproject -> deployer:deployer (755)

# Laravel storage/cache -> www-data (writable)
/var/www/myproject/storage -> www-data:www-data (775)
```

### 3. Test Before Production

```bash
# Test deployment manually first
sudo -u www-data sudo -u deployer git -C /var/www/myproject pull
```

### 4. Monitor Deployments

Check deployment logs to verify the user being used:

```
Dashboard → Deployments → View Output

Look for: "Running deployment as user: deployer"
```

## 📝 Quick Setup Checklist

- [ ] Create deploy user (if not exists)
- [ ] Set path ownership/permissions
- [ ] Configure sudoers file
- [ ] Test sudo access manually
- [ ] Set `deploy_user` in webhook form
- [ ] Run test deployment
- [ ] Verify output shows correct user
- [ ] Check file permissions after deploy

## 🎓 Additional Resources

- [Linux File Permissions](https://www.linux.com/training-tutorials/understanding-linux-file-permissions/)
- [Sudoers Configuration](https://www.sudo.ws/docs/man/sudoers.man/)
- [Laravel Forge Documentation](https://forge.laravel.com/docs)

---

**Need Help?** Check deployment logs for detailed error messages. Most issues are permission-related and can be solved with proper ownership/ACL configuration.
