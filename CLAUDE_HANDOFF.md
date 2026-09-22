# Smart Regional Consultants WordPress Theme

## Claude Code handoff and deployment brief

This document is the implementation and deployment handoff for the Smart Regional Consultants (SRC) WordPress site. Use it as the source of truth when preparing the production WordPress environment, deploying the theme, configuring content, and creating CI/CD.

## Current project

- Local WordPress root: `/Users/muchiriandrew/Sites/smartregionalconsultants`
- Theme root: `/Users/muchiriandrew/Sites/smartregionalconsultants/wp-content/themes/smartregionalconsultants`
- Local Valet URL: `http://smartregionalconsultants.test/`
- Published visual reference: `https://tool-riff-26498280.figma.site/`
- Figma-generated source reference: `/Users/muchiriandrew/Downloads/src_figma_code`
- GitHub repository: `https://github.com/MuchiriAndrew/src_wp_theme`
- Repository purpose: deployable SRC theme source and project documentation, not WordPress core or production secrets
- Active theme stylesheet slug: `smartregionalconsultants`
- Theme asset version: `1.2.4`
- Local WordPress version at handoff: `7.1.1`
- Local database name: `smartregionalconsultants`
- Local database host: `localhost`
- Local table prefix: `src_`

The GitHub repository is currently empty. The recommended repository root is the theme directory, not the full local WordPress installation. WordPress core, `wp-config.php`, database contents, uploads, cache, and server credentials must remain outside Git.

## Non-negotiable production requirements

1. Install a current WordPress release compatible with the production PHP and database versions.
2. Deploy this theme to `wp-content/themes/smartregionalconsultants`.
3. Do not replace production `wp-config.php` with the local file.
4. Do not commit database credentials, salts, SSH keys, uploads, or server-specific configuration.
5. Do not add a plugin for the custom post types, taxonomies, homepage data, footprint map, project modal, or team carousel. These are registered and rendered programmatically by the theme.
6. Back up the production database and uploads before the first deployment.
7. Run `wp rewrite flush` after activating the theme or changing permalink/CPT rewrite settings.
8. Verify the site visually at desktop, tablet, and mobile widths after deployment.

## Theme architecture

### Templates

- `front-page.php` is the WordPress front-page entry point. It immediately requires `front-page-replica.php`; the older markup below the `return` is unreachable legacy code and should not be treated as the active homepage implementation.
- `front-page-replica.php` renders the Figma-parity homepage. It assembles content from options, custom post types, post meta, taxonomy terms, and `assets/africa.svg`.
- `header.php` renders the brand, desktop navigation, mobile offcanvas navigation, scrim, and Partner With Us CTA.
- `footer.php` renders the newsletter/footer content and footer navigation.
- `page.php` renders standard pages such as About, What We Do, Contact, Careers, and Get Involved using page-level SRC fields.
- `archive.php` renders archives for Projects, Programmes, Team, and Careers.
- `single.php` renders single Project and Programme pages plus the shared fallback single layout.
- `index.php` is the generic fallback template.
- `style.css` contains the design system, responsive layouts, visual parity styles, map state styles, modal styles, and responsive breakpoints.
- `assets/js/site.js` contains all client-side interactions.
- `assets/africa.svg` is the inline SVG map. Its country path IDs are used as the country-code integration point.
- `functions.php` contains theme setup, asset loading, CPT/taxonomy registration, admin fields, settings, seed data, map data assembly, and helper functions.

### Main data flow

1. WordPress loads `front-page.php`.
2. `front-page.php` requires `front-page-replica.php`.
3. `front-page-replica.php` calls helpers such as `src_settings_defaults()`, `src_ordered_posts()`, `src_field()`, `src_get_footprint_countries()`, and `src_image_url()`.
4. PHP renders the page and embeds structured JSON for the sector switcher, country map, and project modal.
5. `assets/js/site.js` hydrates the interactive components from those JSON blocks.
6. `style.css` provides the visual state changes and responsive layouts.

## Admin content model

All custom post types and taxonomies are registered in `functions.php` inside `src_register_content()`. All custom post meta is stored with an underscore prefix, for example the admin field `programme_status` is stored as `_programme_status`.

### Public custom post types

| Admin name | Post type | Public URL/archive | Purpose |
| --- | --- | --- | --- |
| Projects | `project` | `/our-work/` | Case studies used by the archive, homepage project mosaic, and modal dossier |
| Programmes | `programme` | `/programmes/` | Programme cards, single programme pages, and country footprint programme links |
| Team | `team_member` | `/leadership/` | Leadership/team archive and homepage carousel |
| Careers | `job` | `/careers/` | Job openings shown on the Careers page |

### Internal homepage content post types

These are intentionally not public-facing URLs. They appear as editable admin menus and are used to manage repeatable homepage content without a page builder.

| Admin name | Post type | Homepage use |
| --- | --- | --- |
| Homepage Sectors | `src_sector_item` | Sector list and sector image/caption switcher |
| Homepage Pillars | `src_pillar_item` | Three dark pillar cards |
| Homepage Audiences | `src_audience_item` | Who We Serve tiles |
| Homepage Partners | `src_partner_item` | Leading Donors/Partners grid |
| Homepage Stats | `src_stat_item` | Homepage metrics band |

### Taxonomies

| Admin name | Taxonomy | Attached to | Purpose |
| --- | --- | --- | --- |
| Sectors | `sector` | `project` | Categorise project case studies |
| Programme Types | `programme_type` | `programme` | Categorise programmes |
| Programme Countries | `programme_country` | `programme` | Drives the interactive Africa footprint map and programme lists |
| Departments | `job_department` | `job` | Categorise job openings |

All taxonomies are public, REST-enabled, and hierarchical. `programme_country` also has a visible admin column.

## Custom fields by content type

### Projects

Edit under `Projects` in WordPress admin. The featured image is preferred for the project image. `project_image_url` is only a fallback.

| Field | Type/use |
| --- | --- |
| `project_partner` | Partner/funder label shown on the tile and modal |
| `project_funder` | Separate funder label shown in the modal kicker |
| `project_location` | Geography shown in the modal |
| `project_year` | Year used by legacy/archive layouts |
| `project_impact` | Impact statement used by single/archive layouts |
| `project_span` | `1` or `2`; controls homepage mosaic tile span |
| `project_home_order` | Numeric homepage ordering; lower first |
| `project_home_featured` | Checkbox; must be enabled for homepage mosaic inclusion |
| `project_external_url` | Optional external project website; used by the modal Website tab |
| `project_modal_description` | Modal Overview description; falls back to excerpt/content |
| `project_image_url` | Fallback image URL when no featured image exists |
| `project_stats` | One `Label | Value` pair per line |
| `project_impacts` | One `Heading | Description` pair per line |

When a project tile is clicked, the homepage opens a native dialog modal. The modal has these tabs: Overview, Impact & Results, Visit Website, and Get Involved. The modal is populated from the project fields above; it does not navigate to a full page for the tile interaction.

### Programmes

Edit under `Programmes` in WordPress admin. The featured image is preferred; `programme_image_url` is the fallback.

| Field | Type/use |
| --- | --- |
| `programme_status` | Use a value containing `enroll`, `upcoming`, or `closed`; homepage normalises this to Enrolling Now, Upcoming, or Closed |
| `programme_partner` | Partner/funder displayed in country footprint programme lists |
| `programme_duration` | Duration shown on cards and single views |
| `programme_start` | Next start date shown on cards |
| `programme_fee` | Fee shown as a separate image badge on homepage cards; the exact value `Free` is normalised to `Free` |
| `programme_location` | Location used as a fallback partner/detail label |
| `programme_spots` | Available spots shown in homepage metadata |
| `programme_home_order` | Numeric homepage ordering; lower first |
| `programme_home_featured` | Checkbox; must be enabled for homepage programme card inclusion |
| `programme_image_url` | Fallback image URL |

The programme title, excerpt, and content are standard WordPress fields. The homepage prefers the excerpt and falls back to content. Programme country links are generated from the programme permalink, so adding a programme to a country automatically creates the correct link in the map detail panel.

### Team members

Edit under `Team` in WordPress admin. Use a featured image for the portrait; `team_image_url` is the fallback.

| Field | Type/use |
| --- | --- |
| `team_role` | Role shown over the portrait |
| `team_quote` | Short biography/quote shown on the card |
| `team_category` | Free-text category such as leadership, board, or staff |
| `team_panel_color` | `red`, `charcoal`, or `orange` |
| `team_home_order` | Numeric carousel ordering |
| `team_home_featured` | Checkbox; controls homepage inclusion |
| `team_image_url` | Fallback image URL |

The homepage team component supports hover activation, keyboard activation, arrows, dots, and a mobile-friendly carousel layout.

### Careers/jobs

Edit under `Careers` in WordPress admin.

| Field | Type/use |
| --- | --- |
| `job_location` | Location |
| `job_type` | Full-time, contract, consultancy, etc. |
| `job_department` | Department label |
| `job_deadline` | Application deadline |

The page uses the excerpt/content for the role description and the configured application email for the Apply button.

### Homepage sectors

Edit under `Homepage Sectors`.

| Field | Type/use |
| --- | --- |
| Title/content | Sector title and description |
| Featured image | Sector image |
| `sector_number` | Display number, for example `01` |
| `sector_home_order` | Numeric display order |
| `sector_image_url` | Fallback image URL |
| `sector_link` | Explore link |

### Homepage pillars

Edit under `Homepage Pillars`.

| Field | Type/use |
| --- | --- |
| Title/content | Pillar title and description |
| `pillar_number` | Display label, for example `Pillar 01` |
| `pillar_home_order` | Numeric display order |
| `pillar_link` | Learn more link |

### Homepage audiences

Edit under `Homepage Audiences`.

| Field | Type/use |
| --- | --- |
| Title | Audience label |
| `audience_home_order` | Numeric display order |
| `audience_link` | Destination URL |

### Homepage partners/donors

Edit under `Homepage Partners`. This is the data source for the Leading Donors, NGOs and Enterprises section.

| Field | Type/use |
| --- | --- |
| Title | Partner name / tile label |
| Featured image | Used by image tiles |
| `partner_type` | `logo`, `image`, or `cta` |
| `partner_abbreviation` | Display mark; may contain a newline for stacked text |
| `partner_name` | Full name shown below an abbreviation |
| `partner_color` | `red`, `charcoal`, or `orange` |
| `partner_image_url` | Fallback/source URL for image tiles |
| `partner_span` | `1` or `2`; controls tile width |
| `partner_home_order` | Numeric display order |
| `partner_link` | CTA/partner destination URL |

### Homepage stats

Edit under `Homepage Stats`.

| Field | Type/use |
| --- | --- |
| `stat_value` | Large number/value |
| `stat_label` | Caption |
| `stat_home_order` | Numeric display order |
| `stat_home_enabled` | Checkbox controlling visibility |

## Homepage settings

Go to `Appearance → SRC Content Settings`. This is registered by `src_add_settings_page()` and stores values in the `src_settings` WordPress option.

### Hero and header

- `hero_meta`
- `hero_title` — one line per line break
- `hero_accent_line`
- `hero_copy`
- `hero_image_url`
- `header_cta_kicker`
- `header_cta_label`

### Homepage section headings

- `sector_eyebrow`, `sector_heading`, `sector_intro`
- `audience_eyebrow`, `audience_heading`
- `footprint_eyebrow`, `footprint_heading`
- `partners_eyebrow`, `partners_heading`
- `projects_eyebrow`, `projects_heading`
- `team_eyebrow`, `team_heading`
- `programmes_eyebrow`, `programmes_heading`

### Quote section

- `quote_eyebrow`
- `quote_text`
- `quote_attribution`
- `quote_summary`

### Newsletter/footer/contact

- `newsletter_eyebrow`
- `newsletter_heading`
- `site_description`
- `county_footprint`
- `contact_city`
- `contact_email`
- `contact_phone`
- `social_linkedin`
- `social_x`
- `social_facebook`
- `social_youtube`

## Standard page content fields

Standard Pages have an `SRC Page Content` meta box. The page slug determines where the values are used.

### Shared page fields

- `page_hero_title`
- `page_hero_eyebrow`
- `page_hero_intro`
- `page_hero_image_url`

### About page

- `page_story_eyebrow`
- `page_story_heading`
- `page_story_body` — paragraphs separated by blank lines
- `page_story_image_url`
- `page_story_stat`
- `page_story_stat_label`
- `page_pillars` — one `Label | Description` row per line

### What We Do page

- `page_capabilities_eyebrow`
- `page_capabilities_heading`
- `page_capabilities_intro`
- `page_capabilities_cards` — one `Title | Description | Image URL` row per line
- `page_capabilities_steps` — one `Title | Description` row per line

### Contact page

- `page_contact_eyebrow`
- `page_contact_heading`
- `page_contact_intro`
- `page_contact_email`
- `page_contact_city`
- `page_contact_coverage`

### Careers page

- `page_careers_eyebrow`
- `page_careers_heading`
- `page_application_email`

## Africa footprint map

The map is fully data-driven from the `Programme Countries` taxonomy.

### Add or edit a country

1. Go to `Programmes → Programme Countries`.
2. Create or edit the country term.
3. Set `Map code` to the two-letter uppercase SVG path code, for example `KE`, `TZ`, `MW`, `ZM`, `ZA`, or `SN`.
4. Add the country summary, display order, selected scale, selected X offset, selected Y offset, and HQ checkbox.
5. Assign one or more Programme posts to that country term.
6. Confirm the country code exists as a path ID in `assets/africa.svg`.
7. Test hover, click, keyboard activation, map zoom, programme links, Back to map, and mobile layout.

### Country term fields

- `src_country_code` — two-letter code matching an SVG path ID
- `src_country_summary` — detail-panel summary
- `src_country_order` — legend order
- `src_country_scale` — selected zoom scale, normally around `1.55`
- `src_country_x` — selected X offset, including the `%`, for example `-12%`
- `src_country_y` — selected Y offset, including the `%`, for example `7%`
- `src_country_hq` — whether to show the HQ marker

`src_get_footprint_countries()` builds the JSON object sent to the browser. Each country includes its name, programme count, summary, map transform values, HQ state, and programme rows. The programme rows contain title, partner/location label, and the real WordPress permalink.

### Map behaviour

- Initial state shows the full Africa map zoomed out.
- Hovering a supported SVG country highlights the country path, corresponding legend item, and country label.
- Clicking a map country or legend item selects it and animates the configured map transform.
- The detail panel lists the assigned programmes as real links.
- Programme links have pointer cursor, red hover text, red accent bar, and arrow movement.
- Escape and Back to map restore the full-map state.
- Keyboard users can focus and activate supported countries with Enter or Space.
- A ResizeObserver keeps country pins aligned as the map responds to viewport changes.

## JavaScript interactions

`assets/js/site.js` contains the following behaviour:

- Mobile offcanvas navigation slides from right to left at widths up to 900px.
- Mobile navigation uses a full-height panel, scrim, Escape-to-close, focus management, focus trapping, and body scroll locking.
- Header scroll state toggles after 40px.
- `[data-reveal]` elements animate into view through `IntersectionObserver`.
- Homepage sectors rotate automatically and pause on hover/focus; clicking a sector selects it.
- Africa map hover/click/focus/keyboard behaviour is described above.
- Project tiles open the native `<dialog>` modal rather than navigating directly.
- Project modal tabs support click and arrow-key navigation.
- Team carousel supports previous/next arrows, dots, hover activation, click activation, keyboard activation, and responsive layout.
- Reduced-motion preferences are respected in the footprint mobile scroll and CSS transitions.

## Initial seed behaviour

`functions.php` contains guarded seed functions:

- `src_seed_content()` runs after theme activation once, guarded by the `src_seeded` option.
- `src_seed_footprint_content()` runs on `init` with version guard `src_footprint_seed_version`.
- `src_seed_home_content()` runs on `init` with version guard `src_home_content_seed_version`.
- `src_seed_page_content()` runs on `init` with version guard `src_page_content_seed_version`.

The seed functions create starter pages, navigation, projects, programmes, team members, homepage content items, country terms, and default settings. They intentionally do not overwrite existing posts once they have been created. On production, review the generated content before exposing the site publicly.

If a future schema change requires a migration, add an explicit versioned migration rather than deleting the seed option or blindly rerunning seed code. Always back up the production database first.

## Local development

The local site is already served through Valet.

```bash
cd /Users/muchiriandrew/Sites/smartregionalconsultants
wp core version
wp theme activate smartregionalconsultants
wp rewrite flush --hard
wp option get siteurl
```

Useful content inspection commands:

```bash
wp post list --post_type=project,programme,team_member,job --fields=ID,post_type,post_title,post_name,post_status
wp term list programme_country --fields=term_id,name,slug,count
wp option get src_settings --format=json
wp db export /safe/path/src-backup.sql
```

Validation commands:

```bash
php -l wp-content/themes/smartregionalconsultants/functions.php
php -l wp-content/themes/smartregionalconsultants/front-page.php
php -l wp-content/themes/smartregionalconsultants/front-page-replica.php
node --check wp-content/themes/smartregionalconsultants/assets/js/site.js
```

Do not commit the local `wp-config.php`, database dump, `.env` files, cache, or uploads.

## GitHub repository and branching

The GitHub repository is empty and is intended to contain the theme source:

`https://github.com/MuchiriAndrew/src_wp_theme`

Recommended local repository root:

`/Users/muchiriandrew/Sites/smartregionalconsultants/wp-content/themes/smartregionalconsultants`

Initialisation commands:

```bash
cd /Users/muchiriandrew/Sites/smartregionalconsultants/wp-content/themes/smartregionalconsultants
git init -b main
git remote add origin https://github.com/MuchiriAndrew/src_wp_theme.git
git add .
git commit -m "Initial Smart Regional Consultants WordPress theme"
git push -u origin main
```

The repository should include the theme PHP, CSS, JavaScript, SVG, and this handoff document. It should not include WordPress core, `wp-config.php`, database exports, media uploads, or production secrets.

For subsequent changes:

```bash
git status
git add .
git commit -m "Describe the change"
git push origin main
```

Use feature branches and pull requests if the production deployment process requires review.

## CI/CD requirements for Claude Code

Create a GitHub Actions workflow at `.github/workflows/deploy-theme.yml` after confirming the production hosting method. It should:

1. Run on pushes to `main` and optionally on manual dispatch.
2. Check out the repository.
3. Run PHP syntax checks on all theme PHP files.
4. Run `node --check` on `assets/js/site.js`.
5. Deploy only the theme directory to the production theme path.
6. Never deploy `wp-config.php`, database dumps, `.env` files, or uploads from GitHub.
7. Use an SSH key or the hosting provider's deployment mechanism stored in GitHub Actions secrets.
8. Fail the workflow if linting or deployment fails.
9. Provide a post-deploy smoke check for the production homepage and key archives.

Preferred SSH/rsync secret names:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_SSH_KEY`
- `DEPLOY_PATH` — absolute path to the production `wp-content/themes/smartregionalconsultants` directory

If production has WP-CLI over SSH, the workflow may additionally run:

```bash
wp theme activate smartregionalconsultants
wp rewrite flush
wp cache flush
```

Only run activation or cache commands after confirming the remote path and WordPress installation. Do not use `--delete` with rsync until the destination is verified to be the dedicated theme directory.

## Production deployment checklist

### Before deployment

- Confirm domain, DNS, SSL, hosting, PHP, database, and SSH details.
- Provision a fresh WordPress installation.
- Create a production database and least-privilege database user.
- Configure production `wp-config.php` with unique salts and environment-specific credentials.
- Confirm filesystem permissions and PHP upload limits.
- Install/activate this theme from the GitHub revision intended for release.

### After theme deployment

- Activate `smartregionalconsultants`.
- Set the site title, timezone, permalink structure, and static homepage.
- Confirm the Home page is assigned as the front page.
- Confirm the Primary Navigation menu is assigned.
- Run `wp rewrite flush`.
- Review `Appearance → SRC Content Settings`.
- Review all homepage repeatable content entries and ordering/featured flags.
- Upload production-quality featured images to the Media Library where possible.
- Configure Programme Countries and assign programmes.
- Confirm all CTA, archive, programme, project, partner, team, contact, and career URLs.
- Test the project dialog and all four tabs.
- Test the map at desktop, tablet, and mobile widths.
- Test mobile navigation, keyboard navigation, Escape handling, and reduced-motion behaviour.

### Smoke-test URLs

- `/`
- `/our-work/`
- `/programmes/`
- `/leadership/`
- `/careers/`
- `/about/`
- `/what-we-do/`
- `/partners/`
- `/get-involved/`
- `/contact/`

## Visual QA checklist

Use the published Figma site as the visual reference, not assumptions from the source code alone.

For each viewport size:

1. Open the Figma reference and production/local implementation side by side.
2. Compare header height, navigation spacing, hero image treatment, SRC watermark, typography, section spacing, and responsive breakpoints.
3. Interact with the mobile offcanvas navigation.
4. Hover and click supported countries on the Africa map.
5. Verify that programme links point to the correct programme posts.
6. Open every project tile and test every modal tab.
7. Test project modal close, Escape, focus return, and external link behaviour.
8. Test the team carousel arrows, dots, hover state, click state, and keyboard state.
9. Compare programme card badges, card heights, CTA states, and image hover effects.
10. Check the page at desktop, tablet, and mobile widths and with reduced motion enabled.

Suggested viewport set: 1440×900, 1280×800, 1024×768, 768×1024, 390×844, and 375×812.

## Known implementation notes

- The homepage uses the replica implementation in `front-page-replica.php`.
- The older unreachable homepage markup in `front-page.php` should be removed only as a deliberate cleanup after a production backup and regression test.
- Content is intentionally managed through native WordPress post types, taxonomies, post meta, term meta, and one theme settings page; there is no ACF or page-builder dependency.
- Image URLs in seed/default content use Unsplash as fallbacks. For production reliability, use WordPress Media Library featured images or a controlled CDN and update the relevant post content.
- The production database is the source of truth for content. Git is the source of truth for theme code.
- Changes to content should normally be made in WordPress admin. Changes to layout, logic, fields, CPTs, taxonomies, or interactions should be made in the theme repository and deployed through CI/CD.

## First tasks for Claude Code

1. Verify the production repository checkout path and confirm it matches `MuchiriAndrew/src_wp_theme`.
2. Create the initial commit from this theme directory and push it to `main`.
3. Provision or verify production WordPress, database, PHP, SSL, DNS, and filesystem access.
4. Create the deployment workflow only after confirming the production deployment transport and destination path.
5. Deploy the theme to a staging or production-safe target.
6. Run the deployment and visual QA checklists above.
7. Document the final production domain, host, deploy path, secrets, rollback process, and database backup process in the production runbook. Never put secret values in this file.
