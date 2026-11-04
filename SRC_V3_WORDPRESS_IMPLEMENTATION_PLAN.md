# SRC v3 → WordPress and WooCommerce implementation plan

Date: 4 November 2026

## Goal and source of truth

Rebuild the current Smart Regional Consultants WordPress site so its public pages match the [published v3 Figma site](https://morse-fling-58551420.figma.site/) in layout, content, responsive behavior, motion, and interactions. Use the React/Vite export in `/Users/muchiriandrew/Documents/SRC v3` for component markup, CSS, assets, data, and behavior. Use the published site to settle visual disagreements. The WordPress admin must own site content; WooCommerce must own programme orders and payments.

The export is React 19 + Vite + Tailwind v4, not Next.js. It runs locally with `pnpm dev` and builds with `pnpm build`. Its registration payment step is a visual prototype with a placeholder paybill, random browser-generated references, and no transaction. The real payment flow will preserve the design language and replace those placeholder actions with WooCommerce and M-Pesa states.

**Known source/publication difference:** The exported Leadership page has a different member order, round-table grid, and county-filter treatment from the published site. Reconcile that page against the published site before marking it complete. The other audited page content matched: home; programme listing; current/upcoming/past programme details; About, What We Do, Pool of Experts, Careers, Portfolio, Get Involved, and Contact. Published and local export compiled bundles have different hashes because the published JavaScript includes development JSX instrumentation, so matching source cannot be inferred from bundle hashes alone.

## Current baseline

- WordPress root: `/Users/muchiriandrew/Sites/smartregionalconsultants` (Valet URL `http://smartregionalconsultants.test/`).
- Theme: `/Users/muchiriandrew/Sites/smartregionalconsultants/wp-content/themes/smartregionalconsultants`; Git remote `MuchiriAndrew/src_wp_theme`.
- Production: `https://smartregionalconsultants.mkbuilds.live/`; deployment details in the theme's `PRODUCTION_RUNBOOK.md`. There is no staging site or automated theme deployment yet.
- Existing theme files are chiefly `functions.php`, `front-page-replica.php`, `header.php`, `footer.php`, `archive.php`, `single.php`, `page.php`, `style.css`, and `assets/js/site.js`.
- Existing programme, project, team member, and job CPTs, several homepage-only CPTs, and programme country/type taxonomies can be migrated or extended. Existing meta boxes are mainly plain text fields. Do not delete existing posts or change slugs without a migration map.
- WooCommerce 11.2.1 is installed and active locally, with KES currency. The supplied gateway source is `/Users/muchiriandrew/Sites/enkatawatches/wp-content/plugins/mpesa-gateway` (plugin version 2.1.2); its local SRC copy is inactive and unconfigured.

## Implementation progress

Implementation began locally on branch `codex/src-v3-implementation`. A database backup was saved to `/Users/muchiriandrew/Sites/_backups/smartregionalconsultants-before-v3-2026-11-04.sql`. No production files or database have been changed.

The first implementation slice is in place: 21 v3 programmes are imported without deleting the six legacy programme records; v3 programme fields/taxonomies and additional content CPTs are registered; programme archive/detail templates, filters, county map, related cards, and WooCommerce page wrappers/styles are added. A hidden product is synchronized only for a published, current, open programme with an explicit whole-shilling KES price or an explicit free label. The four currently open source records still have `[TBC]` fees and remain non-purchasable. The archive and detail flow, filter/empty state, and detail map were inspected manually in the local browser.

The next implementation slice is underway: the v3 homepage hero now uses the exported livestock video/poster and SRC watermark assets, its four enrolment cards are queried from published current/open programme records and link to their respective programme pages, and the impact tiles are backed by the `impact_metric` CPT. `wp src import-impact` seeds the six exported metric records; their copy, values, images, order, facts and links are editable in WordPress. The hero and impact section were compared visually in Chrome against the published reference at desktop and mobile widths; hover/tap expansion, counter animation and programme links are implemented. The homepage sections after Impact still need migration from the older SRC layout.

The M-Pesa plugin copy has been hardened locally by removing credential defaults, disabling it by default, defaulting to sandbox, reducing sensitive logging, and adding callback token/request-ID/amount checks. It remains inactive. Provider-side callback authenticity/reconciliation and sandbox acceptance still require a separate review; live payment is not ready.

The foundation milestone is committed and pushed to `codex/src-v3-implementation`; this homepage follow-up is in progress. The previously staged `PRODUCTION_RUNBOOK.md` user change must remain isolated from implementation commits. Broad parity work on the homepage and remaining v3 routes is still outstanding; this is an implementation checkpoint, not a completion claim.

## Architectural decisions

1. Keep WordPress as the canonical router and server renderer. Use real permalinked pages, archives, and programme detail URLs, so direct links, refresh, history, SEO, and accessibility work. The Figma export's in-memory routing is a behavior reference, not the production routing layer.
2. Port the exported DOM hierarchy, design tokens, Tailwind utilities, custom CSS, and assets into theme templates. Build assets with Vite/Tailwind and enqueue versioned production files. Extract reusable PHP parts for header, footer, page hero, cards, section headings, and CTA bands. Use small React islands or equivalent focused JavaScript for the map, project folder, programme filtering, modal, carousel, and other stateful widgets where that preserves parity. Render meaningful content in PHP first.
3. Put reusable business content in CPTs/taxonomies and page-specific copy in registered page meta or site settings. Register every field in code with sanitization, capability checks, REST exposure where needed, and suitable admin controls. Avoid hard-coded arrays in the rendered theme except design constants and SVG geography.
4. Use one WooCommerce virtual product per open programme that needs enrolment. Link product and programme by ID; keep the programme as the editorial source and the product/order as the pricing and transaction source. Configure zero-price products for free enrolment. Upcoming and past programmes must never be purchasable unless explicitly opened in admin.
5. Use native WooCommerce checkout, order/pay, and account lifecycle. The Figma registration modal supplies programme choice and applicant details; payment moves into a WooCommerce checkout view styled to continue the modal/progress design. The placeholder “I have paid” action becomes a real payment status. The M-Pesa STK callback and verified WooCommerce order status, not a button click, determine confirmation.
6. Keep the gateway as a separate versioned plugin from the theme. A theme deployment must not overwrite payment code or secrets. Configure credentials only in the target environment.

## Route and template inventory

| Published/export surface | WordPress route and template | Main data owner | Critical behavior |
| --- | --- | --- | --- |
| Home | `/`, `front-page.php` and section parts | Home page fields, programmes, impact metrics, project folders, partners, testimonials, posts, publication | Hero media, enrolling cards/timers, impact accordion, footprint, project folder, partner strip, carousel, news |
| About SRC | `/about/`, page template | Page fields, values, impact text | Section navigation/CTA |
| What We Do | `/what-we-do/`, page template | Capability/pillar records and page fields | Pillar accordion and programme links |
| Pool of Experts | `/pool-of-experts/`, page template | Expert records, category taxonomy | Category filters and expandable items |
| Portfolio | `/portfolio/`, archive/page template | Portfolio company CPT | Card states and company detail/link |
| Partners | `/partners/`, page template | Partner CPT and partner category | Logo mosaic/category grouping and hover labels |
| Leadership | `/leadership/`, page template | Team member CPT, team group/county | Sticky section nav, county filter, card reveal, biography modal |
| Get Involved | `/get-involved/`, page template | Page fields and enquiry submissions | Donor/partner/other tracks and forms |
| Careers | `/careers/`, job archive/page template | Job CPT and application submissions | Job filter/expand and application form |
| Contact | `/contact/`, page template | Contact settings and enquiries | Form validation/confirmation |
| Programmes | `/programmes/`, `archive-programme.php` | Programme CPT/taxonomies | Status/theme/signature filters; empty state; sticky filter bar |
| Programme detail | `/programmes/{slug}/`, `single-programme.php` | Programme CPT and linked product | Status-specific CTA, journey, county map, related cards |
| Commerce | `/shop/`, `/product/{slug}/`, `/cart/`, `/checkout/`, `/checkout/order-pay/...`, `/checkout/order-received/...`, `/my-account/` | WooCommerce products/orders/customers | Responsive product/cart/checkout/order/payment/account states |

The export contains a Partners page component, but its main navigation currently points “Partners” to the home anchor. The WordPress site should provide a real Partners URL while retaining the home anchor from the menu where the published design uses it. Similar in-memory navigation in the export must become stable WordPress URLs.

## Content model and admin controls

### Programmes

Extend the existing `programme` CPT, preserving its 21 v3 published slugs and imported posts. Create an idempotent WP-CLI importer from `src/data/programmes.ts` plus `src/data/programmes-update.json`, with an explicit mapping table and dry-run mode. Do not overwrite manually edited WordPress content on later imports without a deliberate flag.

Register these fields in code and expose appropriate admin controls:

- Identity: full name, short card name, hero title/subtitle, slug, programme code, published/featured flags, card/home order, source ID.
- Classification: status (`current`, `upcoming`, `past`), enrolment (`open`, `partner`, `closed`), type (`signature`, `partner`), one of five themes, pillar (`bms`, `irf`, `ml`), tags.
- Geography: country terms, Kenyan county terms, reach summary, delivery geography, map visibility/marker priority. Attach programme relationships to terms; derive map lists and counts from these relationships.
- Detail: audience, outcomes, duration, dates, delivery method, ordered journey stages, note, lead image, logo gallery, related funders/partners.
- Cohort: next date, public date label, registration open/close dates, capacity, availability and CTA override. Countdowns use real dates; undated cohorts show the design's “opening soon” state without a fake countdown.
- Commerce: linked WooCommerce product ID, price display, free/paid status, purchase eligibility, optional registration questions, terms/consent version. Price and tax authority stays with the product; editorial fee labels must not conflict with it.
- Admin list columns/filters for status, theme, enrolment, featured, product link and price readiness; clear validation when an open paid programme lacks a valid product/price.

Reuse `programme_type` and `programme_country` where feasible. Add `programme_theme`, `programme_pillar`, `programme_county`, and a tag taxonomy if the existing model cannot represent those relationships cleanly. The map's Africa/county SVG path keys are design constants; term metadata maps each term to its existing SVG ID, marker coordinates, display label, and optional zoom. Adding a country/county and attaching programmes should activate its pointer without changing theme code, provided that geography exists in the SVG dataset.

### Other content

| Content | Proposed admin source | Required editable fields |
| --- | --- | --- |
| Hero and page sections | Page meta / SRC site settings | Eyebrows, headlines, emphasis ranges, copy, CTA label/target, images/video/poster, ordering, visibility |
| Impact metrics | Extend `src_stat_item` or migrate to `impact_metric` | Number, prefix/suffix, label, explanation, bullet rows, CTA, order |
| Project folders | Project-folder CPT or programme-theme term metadata | Folder title, image, summary, funders, results, order, linked programmes and modal copy |
| Case studies | Existing `project` CPT | Article/detail content, images, statistics and relationships |
| Partners | Extend `src_partner_item` or migrate to public partner CPT | Name, logo, category, URL, tile style/span, tooltip, order, funder relationship |
| Testimonials/stories | New `testimonial` CPT | Quote, story paragraphs, person, enterprise, location, image/video, order, visibility |
| News | Native WordPress posts and categories | Date, title, excerpt, body, featured image, related programme, publish state |
| Impact publication | Publication CPT or dedicated page field | Year, cover, PDF media, flipbook URL/embed, title, summary |
| Leadership | Extend `team_member` | Role, group, county, order, bio, portrait, external profile, card tone and featured state |
| Experts | New `expert` CPT + category taxonomy | Name, specialty, blurb, photo, category, expanded detail, order |
| Jobs | Existing `job` CPT | Department, type, location, deadline, description, application target/state |
| Global content | Settings + native menus/pages | Brand assets, footer/legal links, socials, contact info, newsletter settings, privacy/terms/cookies |

Forms need actual handlers and admin visibility: contact and partner enquiries; job applications, including safe private storage for CVs if uploads are enabled; newsletter opt-in and provider integration; upcoming-programme notifications. Use nonce/CSRF protection, server-side validation, spam controls, email delivery, consent records and a retention policy. Replace prototype-only success messages and dead buttons.

## Work packages

### 0. Freeze references and establish a safe workspace

- Archive the v3 export at its current revision and record checksums for source, assets and generated build. Keep the published URL and screenshots as visual references.
- Create a v3 feature branch/worktree in the theme repository. Back up local and production databases/uploads before schema or import work.
- Provision staging on the existing host or another equivalent environment with its own database, domain, secrets and sandbox gateway. Use staging for payment and deployment QA. Add rollback instructions.
- Record the known Leadership difference as an explicit parity ticket. Use published Leadership order/layout as the acceptance target unless the owner supplies a newer publication.
- Produce route/section screenshots and interaction recordings at 1440×900, 768×1024 and 390×844, plus narrow 375px overflow checks. Include hover/focus and active states.

### 1. Theme foundation and design system

- Add a repeatable asset build to the theme (`package.json`, Vite/Tailwind config, source CSS/JS, manifest-based WordPress enqueue). Commit built assets required by the PHP server or produce them in CI.
- Port font imports/assets, colors, spacing, radii, shadows, typography scales, breakpoints, button states, motion durations, and the responsive container system from `src/index.css`.
- Copy/replace exported SVGs, logos, team imagery and video poster with local media or WordPress Media Library records. Audit external Unsplash URLs and replace editorial assets where final media exists. Keep alt text editable.
- Rebuild header, dropdowns, mobile navigation, sticky subnav, footer, cookie/legal links and CTA treatment from `Nav.tsx`/`Footer.tsx`. Use semantic links and correct current-page state; preserve keyboard, touch and reduced-motion behavior.
- Build reusable PHP components with matching class names and DOM hierarchy to reduce visual drift: page hero/header, section heading, card variants, badges, image overlays, map panel, modal shell, form fields and CTA bands.

### 2. Content schema and migration

- Register all CPTs, taxonomies, meta fields, media controls, and settings in code. Prefer organized `inc/content/`, `inc/admin/`, `inc/commerce/` modules over adding more to the current large `functions.php`.
- Migrate old posts/terms/meta to new field names with one-way, logged WP-CLI migrations; preserve IDs/permalinks wherever possible. Add a repeatable v3 seed importer for the 21 programme records and other approved demo content.
- Import team, partner, impact, portfolio, story, publication and news data; distinguish confirmed production content from prototype placeholders. Do not publish fake contacts, placeholder social links, `[TBC]` fees, sample job openings or draft metrics as confirmed facts.
- Build admin screens that make ordering, linking, visibility and checkout readiness obvious to editors. Document the exact edit path for every frontend section.

### 3. Public pages and interactions

- **Home:** rebuild full-screen hero/video and watermark, now-enrolling strip, impact accordion, Africa/Kenya footprint, project folders, partner mosaic, featured programmes, upcoming chips, testimonial/video carousel, latest news, publication/flipbook, newsletter and footer. Match responsive stacking, image crops, motion and section spacing.
- **Footprint:** preserve default full-map fit, country/county hover and click selection, zoom/back transitions, selected marker state, programme list links and mobile details. Derive programmes from taxonomy relations; verify each link resolves to the matching detail page.
- **Projects:** implement theme folders and nested programme files with published overlay behavior, tabs/panels, back/close, keyboard focus, scroll lock and proper programme links.
- **Programmes:** render 7 current, 2 upcoming and 12 past seed records; status/theme/signature filters, query-state persistence, empty results, featured card layouts, past card treatment, notification CTA and modal entry. Keep counts dynamic.
- **Programme detail:** reproduce hero, at-a-glance, journey, delivery/county map, CTA and related cards for current, upcoming and past states. Paid/open, free/open, partner-only, upcoming and closed CTA decisions come from admin metadata.
- **Other pages:** rebuild About, What We Do, Pool of Experts, Portfolio, Partners, Leadership, Get Involved, Careers and Contact. Implement all filter, accordion, modal and form states in those pages. Reconcile Leadership member order and county filter/grid with the published site.
- Implement scroll reveal/slide animations only where the export uses them, with `prefers-reduced-motion` support and no content hidden when JavaScript fails.

### 4. WooCommerce enrolment

- Install the latest compatible WooCommerce release locally and on staging, enable HPOS if the gateway and integration behave correctly, and configure KES currency, taxes, guest checkout, emails, account settings and legal pages. Keep shipping disabled for virtual programme products.
- Create one linked virtual product per open enrolment. Use hidden catalogue visibility unless the design calls for a public product card. Set appropriate stock/seat limits and sold-individually rules. Prevent cart mixing if a registration should contain only one programme.
- Add an admin sync/validation layer: programme status and enrolment govern purchase availability; product price governs payable amount; changing a programme must not silently publish a new purchasable product.
- Match the modal's programme selection and details steps. Validate name/contact/consent server side, save structured order metadata, and place the linked product into the WooCommerce cart/session. Do not put applicant details in query strings or browser-generated references.
- Move paid users into native WooCommerce checkout styled as the payment stage of the v3 flow. Prefill billing data from the secure session. Free products bypass M-Pesa and complete with a zero-total order. Confirmation displays a real order number/status and, after successful M-Pesa payment, the real receipt.
- Style native Shop, product, Cart, Checkout, Order Pay, Order Received, My Account (login/register/orders/order detail), notices/validation errors, empty cart, coupons if enabled, and transaction emails in the v3 design system. Keep responsive layouts at desktop/tablet/mobile. Do not add a public Shop link if it would diverge from the published navigation.
- Map statuses visibly: awaiting STK approval, pending callback, paid/enrolled, failed/cancelled, timeout/retry, refunded and manually reconciled. Send the correct email only after the status is authoritative; never mark an enrolment paid based on a click.

### 5. M-Pesa plugin integration and hardening

- Bring `mpesa-gateway` into SRC as a separately versioned plugin release. Confirm WooCommerce/WordPress/PHP compatibility and decide classic Checkout versus Checkout Block by testing the plugin's actual integration; the plugin declares Blocks support, but checkout behavior must be verified.
- Remove embedded credential defaults, rotate exposed credentials if they are live, and configure sandbox/live keys, shortcode, passkey and callback URL outside Git. Check environment separation and callback reachability.
- Review STK request/response handling, webhook authenticity and order lookup, amount/currency matching, duplicate callbacks, status query reconciliation, retries, failure and cancellation paths. Ensure idempotent `payment_complete` behavior. The current code casts totals to integer, so use whole KES prices or correct amount handling.
- Remove or redact raw callback, phone and credential-bearing debug logs. Keep safe order-level audit events and error context without exposing payment data.
- Test sandbox success, decline, cancel, timeout, lost callback, duplicate callback, retry from Order Pay, browser refresh/back and admin manual reconciliation before enabling live mode.

### 6. QA and acceptance

- Maintain a comparison matrix for every route/section/state at **1440×900, 768×1024, 390×844 and 375px**. Capture reference and WordPress screenshots at identical viewport, scroll and state. Use image diffs to find layout drift, then human-review typography, crop, motion and hover/focus treatment. Review full-page as well as viewport captures.
- Exercise all interactions in both implementations: desktop dropdown and mobile menu; map hover/click/zoom/back; project folder tabs; leadership biography; filters and empty state; testimonial carousel arrows/dots/swipe; form validation; modal selection/back/close; programme links; past/upcoming states.
- Test actual WordPress content management: edit each section's text, image, order and visibility in admin, publish, confirm frontend update, and restore the baseline. Add a new country/county programme relationship and verify the map/list changes without editing code.
- Verify no horizontal overflow, clipped controls, broken images, stuck scroll lock or focus traps. Check keyboard, screen reader labels, reduced motion, contrast, form errors, SEO metadata, sitemap and route refresh/history.
- Check performance budgets and image optimization, especially the hero media, map SVG, long programme archive and large JavaScript bundle. Load interaction JS only where needed.
- Run WooCommerce sandbox end-to-end scenarios and verify every payment/order state in admin and customer email. Validate no registration appears as paid before the gateway confirms it.
- Acceptance is a page/state checklist with screenshot evidence and explicit sign-off of any intentional payment-flow difference. “Approximately similar” is not a pass criterion.

### 7. Release and operations

- Add GitHub Actions for build/lint/security checks and staged theme deployment from the theme repository. Keep production deploy gated and environment secrets in GitHub/environment configuration; deploy the separately versioned gateway through its own reviewed release path.
- Back up database and uploads, deploy to staging, run migrations/imports, flush rewrites, clear caches, check routes/forms/checkout, and record the exact deployed commit and plugin versions.
- Promote to production in a low-traffic window. Install/configure WooCommerce and gateway, migrate content, confirm public callback and HTTPS, run a controlled live transaction, verify order/receipt/email, and retain a tested rollback package.
- Update `PRODUCTION_RUNBOOK.md` and admin documentation with content edit paths, product/programme sync rules, payment configuration, callback/reconciliation procedure, backup/rollback and CI/CD workflow.

## Definition of done

1. Every page and interactive state in the route matrix has approved visual comparison evidence at desktop, tablet and mobile; the known Leadership revision is resolved against the published target.
2. All editorial content visible on the site can be changed in WordPress admin, including project/map/programme relationships and homepage content, without editing theme code.
3. All 21 v3 programmes are represented with correct slugs/statuses and dynamic filters; no placeholder fee can be purchased accidentally.
4. Paid enrolment creates a WooCommerce order, triggers a real M-Pesa STK request, and confirms only after authoritative payment; free enrolment creates a valid zero-total order.
5. Native WooCommerce customer pages, notices, order states and emails are styled and usable at all target sizes.
6. Staging and production deployments are reproducible, secrets are isolated, and rollback plus payment reconciliation are documented.

## Business inputs required before live payment

Confirmed prices and whether each open programme is paid or free; KES/tax treatment; cohort dates and capacities; refund/cancellation policy; required applicant fields and consent wording; M-Pesa sandbox/live credentials and shortcode; sender email/domain configuration; newsletter destination; final media/annual report PDF; verified social and legal links. Implementation can proceed with clearly marked draft values while those inputs are collected. No `[TBC]` value becomes a charge.
