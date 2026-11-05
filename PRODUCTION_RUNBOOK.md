# Smart Regional Consultants — Production Runbook

## Live site

- URL: https://smartregionalconsultants.mkbuilds.live/
- Host: `167.86.112.85` (shared mkbuilds.live application server)
- SSH: `root@167.86.112.85`
- Web root: `/var/www/smartregionalconsultants`
- Theme path: `/var/www/smartregionalconsultants/wp-content/themes/smartregionalconsultants`
- PHP: 8.3 via `php8.3-fpm.sock`
- Internal nginx site: `/etc/nginx/sites-available/smartregionalconsultants`, listening on `127.0.0.1:8098`
- Reverse proxy: nginx-proxy-manager (NPM), proxy host id 15, forwards `smartregionalconsultants.mkbuilds.live` → `172.18.0.1:8098`
- TLS: Let's Encrypt via NPM, certificate id 21, auto-renews, expires 2026-12-21 (NPM handles renewal)
- Firewall: UFW rule allowing `172.16.0.0/12` (Docker bridge) → port `8098/tcp`, comment "Docker->smartregionalconsultants"

## Database

- Engine: host-native MySQL (`mysql.service`), not the `supportshelf-mysql` Docker container
- DB name: `smartregionalconsultants`
- DB user: `deployer` (shared low-privilege user, scoped per-database grants; credentials in production `wp-config.php` only)
- Table prefix: `src_`

### Backup

```bash
ssh root@167.86.112.85 "mysqldump -u deployer -p'<password from wp-config.php>' smartregionalconsultants > /root/backups/smartregionalconsultants-$(date +%F).sql"
```

Also back up uploads before any risky change:

```bash
ssh root@167.86.112.85 "tar czf /root/backups/smartregionalconsultants-uploads-$(date +%F).tar.gz -C /var/www/smartregionalconsultants/wp-content uploads"
```

### Restore

```bash
ssh root@167.86.112.85 "mysql -u deployer -p'<password>' smartregionalconsultants < /root/backups/<file>.sql"
```

## Deploying theme updates

Theme code lives in GitHub (`MuchiriAndrew/src_wp_theme`, branch `main`) and is deployed by rsync, not by cloning git on the server (matches how the other mkbuilds.live WordPress sites are deployed):

```bash
rsync -avz --delete \
  --exclude='.git' --exclude='.gitignore' \
  /Users/muchiriandrew/Sites/smartregionalconsultants/wp-content/themes/smartregionalconsultants/ \
  root@167.86.112.85:/var/www/smartregionalconsultants/wp-content/themes/smartregionalconsultants/

ssh root@167.86.112.85 "chown -R www-data:www-data /var/www/smartregionalconsultants/wp-content/themes/smartregionalconsultants"
```

If any CPT/taxonomy/rewrite-affecting code changed:

```bash
ssh root@167.86.112.85 "cd /var/www/smartregionalconsultants && wp --allow-root rewrite flush --hard"
```

A GitHub Actions workflow (`.github/workflows/deploy-theme.yml`) automating this over SSH has **not** been created yet — this deploy was done manually. Set that up before relying on push-to-deploy.

## Rollback

Theme code: redeploy a previous commit with the same rsync command, pointed at a checked-out earlier `git` revision locally, or `git archive` a tag straight to a tarball and rsync that.

Database: restore the most recent pre-change backup (see Restore above). WordPress content changes are otherwise only reversible through Trash/Revisions in wp-admin or a DB restore — there is no automatic content versioning beyond that.

## Admin access

- wp-admin: https://smartregionalconsultants.mkbuilds.live/wp-admin/
- Admin username: `src_admin`
- Password: generated at install time, given to the site owner directly in chat — not stored in this file. Rotate it in wp-admin (Users → your profile) if it needs to change.

## Known open items (not yet done)

- No GitHub Actions CI/CD workflow yet (theme deploy is manual rsync).
- No Careers (`job`) posts seeded — Careers page will show no current openings until added.
- Mobile viewport (375px) overflows horizontally on the homepage, traced to `.footprint-ref` (the Africa map section) — needs a responsive-CSS fix in the theme, not a deploy issue.
- Featured images/media are mostly seed defaults (per `CLAUDE_HANDOFF.md`, some fall back to Unsplash URLs) — swap in real Media Library assets before treating content as final.
- No staging environment separate from this production site; changes go live immediately on deploy.
