# Digital Harbor — Admin Dashboard

PHP + MySQL admin for managing contact-form messages with multi-user roles.

## Stack
- PHP 8.1+ with PDO
- MySQL 5.7+ / MariaDB 10.3+
- No frameworks. Single-file pages.

## Files
```
api/submit.php       Public endpoint — contact form posts here (JSON)
admin/index.php      Inbox view (login required)
admin/message.php    Single-message view + status actions
admin/users.php      User management (admin role only)
admin/login.php      Sign in
admin/logout.php     Sign out
admin/setup.php      First-run admin seeding (locks after first user)
admin/admin.css      Dashboard styling
includes/config.php  DB + mail + site config (reads env vars)
includes/db.php      PDO singleton
includes/auth.php    Sessions, login, CSRF, escaping
includes/layout.php  Shared header/footer
includes/schema.sql  DB schema
```

## Setup (any PHP host)

1. Upload all files to your web root, preserving the directory structure.
2. Create a MySQL database — note the host, name, user, password.
3. Set environment variables (or edit `includes/config.php` directly):
   ```
   DB_HOST=localhost
   DB_NAME=digital_harbor
   DB_USER=...
   DB_PASS=...
   ADMIN_EMAIL=you@yourdomain.com
   MAIL_FROM=noreply@yourdomain.com
   ```
4. Visit `https://yourdomain.com/admin/setup.php` once.
   - Creates the schema automatically.
   - Prompts you to create the first admin user.
   - Refuses to run again after one user exists.
5. **Delete `admin/setup.php`** after first use (or leave it — it's locked).
6. Sign in at `/admin/login.php`.

## Local dev (XAMPP / MAMP)

1. Drop the project into `htdocs/` (XAMPP) or your equivalent web root.
2. Open phpMyAdmin → create DB `digital_harbor`.
3. Default config reads `localhost` / `root` / empty password (XAMPP defaults).
4. Visit `http://localhost/admin/setup.php`.

## Free hosting that supports PHP + MySQL

- **InfinityFree** — fully free, persistent PHP/MySQL.
- **000webhost** — free tier with daily refresh.
- **Render.com** — free Docker tier, app sleeps after inactivity.
- **Railway** — free credits, then pay-as-you-go.

For Vercel/Netlify, you'd need to switch to Node.js — they don't run PHP.

## How it connects to the public site

The `/contact.html` form posts JSON to `/api/submit.php`. The endpoint:
1. Stores the message in the `messages` table.
2. Forwards a copy to `ADMIN_EMAIL` via PHP's `mail()`.
3. Returns `{ok:true, id:N}` on success.

Most free hosts disable `mail()` — for reliable delivery, swap in PHPMailer + an SMTP provider (Mailgun, Sendgrid, Postmark all have free tiers).

## Roles

- `admin` — can manage users, delete messages, do everything
- `viewer` — can read inbox + change message status (read/replied/archived) only

## Security notes

- Passwords hashed with `password_hash()` (bcrypt).
- CSRF tokens on every state-changing form.
- HTTP-only, SameSite=Lax session cookies; secure flag set under HTTPS.
- All user output escaped via `e()` (`htmlspecialchars`).
- Honeypot field on contact form to deflect basic bots.
- PDO prepared statements throughout — no string-interpolated SQL.

## What's NOT included (yet)

You picked option (a) for features = "receive contact messages" only.
If you later want any of:
- Edit hero/services/team copy from the dashboard
- Upload service images
- File-attachment uploads on contact form
- Reply-from-dashboard (instead of mailto:)
- Analytics

…let me know and I'll add them.
