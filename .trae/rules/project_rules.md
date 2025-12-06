Email Notification Standards

- Use `MailMessage` with `line()` and `action()` for body and CTA
- Avoid custom `markdown('mail.email')` unless necessary
- When using `resources/views/mail/email.blade.php`, render content as plain text and use simple links
- Set sender: use `smtp_settings.mail_from_email` when verified; otherwise use `smtp_settings.mail_username`
- Ensure links are public: derive URLs via `getDomainSpecificUrl()` and configure `APP_URL`
- Keep messages minimal HTML to avoid raw markup in plain‑text clients
- Always run with `queue.default=sync` in local dev to validate delivery
