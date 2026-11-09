# SRC WordPress CMS and Checkout Audit

**Audit date:** 6 November 2026  
**Production site:** <https://smartregionalconsultants.mkbuilds.live/>  
**Scope:** WordPress theme content model, editor controls, WooCommerce programme registration, M-Pesa gateway readiness, and gaps between editable content and hard-coded presentation/relationships.

## Executive summary

The theme is connected to WordPress and uses programmatically registered custom post types, taxonomies, post meta, term meta, and one theme settings page. Editors can manage most programme records, team profiles, jobs, partners, impact metrics, testimonials, publications, portfolio records, and several page content fields from wp-admin. WooCommerce is the checkout engine; open programme records are synchronized to hidden virtual WooCommerce products.

This is **not yet a completely CMS-driven site**. Layout, styling, animations and interaction logic belong to the theme, as expected, but several editorial details and relationships that a content editor may reasonably expect to manage are also fixed in code. The largest content-model gaps are:

1. Africa map marker placement is not fully controlled by country records. Country terms expose map code, order, summary, scale, offsets and HQ state, but the marker coordinate/selection configuration is partly fixed in the map implementation.
2. Kenya county marker list and the Nairobi programme list are hard-coded. Adding a county term does not automatically add its map pointer or define its displayed programme set.
3. Homepage headings and labels are mixed: some are settings fields, while others are literal template copy.
4. A number of image fields accept raw URLs or theme asset paths rather than offering a WordPress Media Library picker.
5. Some programme relationships and ordering use hard-coded slug lists/default maps.
6. The v3 taxonomy registration currently sets `publicly_queryable` to `false` for all v3 content types after first setting it from the news flag. As written, the later value wins; editor screens exist, but public single/archive routing for `src_news_item` may not behave as its archive settings suggest.

The live checkout issue shown in the screenshots is fixed at the WooCommerce configuration level. The store-only “coming soon” settings were disabled. The programme registration AJAX now creates the cart and redirects to WooCommerce checkout; a fresh browser flow reached checkout and the checkout Store API exposed M-Pesa as an available method. **A real M-Pesa payment has not been made or validated.** The gateway has no production Daraja credentials yet, which the site owner intends to enter in wp-admin.

## Checkout repair and current status

### What was broken

- WooCommerce had `woocommerce_coming_soon=yes` and `woocommerce_store_pages_only=yes`. That caused `/checkout/` to display WooCommerce's store launch placeholder rather than the real checkout.
- In the registration modal, the payment step appeared to stall at “Preparing checkout…” because the checkout handoff was blocked by the store-only state.

### What changed and what was verified

- Set `woocommerce_coming_soon=no` and `woocommerce_store_pages_only=no` in the production WordPress options, then flushed the WordPress object cache.
- Verified that `/programmes/` and the homepage return HTTP 200.
- Verified that a fresh registration flow for an open programme accepts valid test details, creates the WooCommerce cart, and redirects to `https://smartregionalconsultants.mkbuilds.live/checkout/`.
- Verified checkout loads with the selected programme at **KES 100** and lists the `mpesa` payment method. The empty checkout URL redirects to `/cart/`, which is normal WooCommerce behavior when no cart item is present; it no longer shows the coming-soon screen.
- No order was placed and no real payment was attempted.

### M-Pesa readiness

| Item | Current status |
|---|---|
| WooCommerce installed and active | Yes |
| M-Pesa gateway plugin installed and active | Yes (`mpesa-gateway` 2.1.2, per production inspection) |
| M-Pesa method shown at checkout | Yes, verified in checkout data |
| Gateway mode | Sandbox at time of inspection |
| Daraja credentials | Not configured; intentionally left for site owner |
| Live STK Push acceptance | **Not ready until credentials and live mode are configured and a controlled payment is tested** |
| Programme fee source | Programme CMS field, in KES; current open programmes use temporary KES 100 pricing |

After entering the Daraja credentials in the M-Pesa plugin settings, set the correct live environment and callback configuration, then complete a small controlled end-to-end payment. Keep credentials out of source control and this document.

## Where to manage content in wp-admin

### Main content areas

| Admin area | What it manages |
|---|---|
| **Pages → Edit** | Page title/content and the theme's `SRC Page Content` meta box. Templates read the custom fields for their branded page sections. |
| **Programmes → Edit** | Programme content, v3 programme fields, status, enrolment, funders/partners, imagery, dates, counties/countries, and fee. The CPT is registered by the theme. |
| **Team → Edit** | Team profile, role, bio, image, LinkedIn, display options, county role and ordering. Department and county assignments use Team taxonomies. |
| **Careers → Edit** | Job opening details and requirements. The Careers page itself has page fields for its labels, empty state and application copy. |
| **Projects → Edit** | Legacy project records and project-specific details. Homepage project mosaic is now primarily assembled from programme records and programme themes. |
| **Homepage Sectors / Homepage Pillars / Homepage Audiences** | Homepage feature cards, descriptions, ordering and links. |
| **Homepage Partners** | Homepage partner logo/name/link, marquee inclusion and order. Also includes legacy homepage tile fields. |
| **Homepage Stats** | Homepage stat items and visibility/order. The v3 impact grid uses the separate Impact Metrics CPT. |
| **Testimonials** | Homepage story slides: person, organization/location, headline, body paragraphs, image/video URL and featured/order state. |
| **Pool of Experts** | Expert profiles, specialty, summary, request link, category and order. |
| **Project Folders** | Programme/project-folder summary, funders, order and CTA/results URL. |
| **Publications** | Publication title/body, year, PDF URL, flipbook URL and featured status. |
| **Latest News** | News title/body/excerpt, display date/category, image URL, order, homepage feature and destination/related programme. |
| **Portfolio Companies** | Portfolio card name, sector, location, website, abbreviation, order and inclusion. |
| **Impact Metrics** | Metric value/label/context/facts, image key or URL, layout area, related link and order. |
| **Appearance → SRC Content Settings** | Homepage hero and CTA, editable homepage headings/intro, homepage quote, newsletter/footer copy, contact details, social links and Leadership group labels/settings. |
| **Appearance → Menus** | Header/footer navigation when a WordPress menu is assigned. The theme also has a fallback menu if no menu is configured. |
| **WooCommerce → Products** | Standard WooCommerce products. Programme-linked products are hidden virtual products generated/updated from programme fields; use the programme editor for programme pricing and availability. |
| **WooCommerce → Settings → General** | Store currency and general store settings. Production currency was verified as KES. |
| **WooCommerce → Settings → Payments → M-Pesa** | Gateway credentials, mode and provider-specific settings. Values are site secrets and should only be entered by an authorized administrator. |

## Programmatic content model

All listed custom post types and taxonomies are registered in theme PHP; they do not depend on a CPT registration plugin. The main implementations are `functions.php`, `inc/v3-content-model.php`, `inc/v3-leadership.php`, `inc/v3-careers.php`, `inc/v3-pool-experts.php` and `inc/commerce/programme-products.php`.

### Custom post types

| CPT key | Admin label | Public-facing role | Main editable fields / controls |
|---|---|---|---|
| `programme` | Programmes | Programme archive/single, homepage enrollment cards, footprint relationships, project mosaic and checkout | Core title/editor/excerpt/featured image; short and hero names; status/current-upcoming-past; enrolment/open-partner-managed-closed; type/pillar; tags; funders/partners/logos; reach/audience/outcomes; duration/dates/delivery/journey; fee label and KES amount; cohort date; code/note; detail page toggle; homepage/project order; Woo product ID; country/county/theme/pillar/type terms. |
| `team_member` | Team | Leadership/team archive, homepage team selection | Core title/editor/featured image; role, quote, short bio, LinkedIn, county role level, per-department order, category, homepage panel tone/order/featured, image URL/path; team department/county assignments. Leadership-specific settings live in SRC Content Settings and department term fields. |
| `job` | Careers | Job listings and application modal on Careers page | Title/editor/excerpt; location, employment type, department, deadline, order, requirements. `job_department` taxonomy. |
| `project` | Projects | Legacy public project archive under `/our-work/` | Title/editor/excerpt/featured image; partner/funder, location/year/impact, homepage span/order/featured, external URL, modal description, fallback image URL, overview stats and impact rows; `sector` taxonomy. |
| `src_sector_item` | Homepage Sectors | Homepage sector cards | Title/editor/thumbnail/order plus number, fallback image URL and destination link. |
| `src_pillar_item` | Homepage Pillars | Homepage pillar cards | Title/editor/order plus number and link. |
| `src_audience_item` | Homepage Audiences | Homepage audience cards | Title/editor/order and audience link. |
| `src_partner_item` | Homepage Partners | Homepage partner tile and partner marquee | Title/thumbnail; name, abbreviation, type, color, image URL, span, link, homepage order; v3 logo URL, marquee order and marquee inclusion; `partner_category`. |
| `src_stat_item` | Homepage Stats | Legacy homepage statistics | Title plus value, label, homepage order and enabled state. |
| `src_testimonial` | Testimonials | Homepage testimonial carousel | Person, business/location, headline, paragraphs, image/video URL, order and homepage featured toggle; `testimonial_category`. |
| `src_expert` | Pool of Experts | Expert directory and enquiry links | Role/specialty, summary, order, featured toggle, request label/URL; `expert_category`. |
| `src_project_folder` | Project Folders | Project-folder cards/details | Summary, funders, order and results/stories CTA URL; `project_theme`. |
| `src_publication` | Publications | Featured homepage publication/download/embed | Year, PDF URL, flipbook URL and featured toggle. |
| `src_news_item` | Latest News | Homepage news cards and intended news archive/single | Display date, category, image URL, order, featured toggle, related programme slug, external/internal article URL. **Routing caveat:** v3 registration assigns `publicly_queryable` twice and the second `false` overrides the news flag; public routing needs verification/fix before treating `/news/` as a reliable archive. |
| `portfolio_company` | Portfolio Companies | Portfolio page cards | Sector, location, website, card abbreviation, order and featured toggle. |
| `impact_metric` | Impact Metrics | Homepage impact tiles and details | Value/prefix/suffix/label/detail/context/facts/image key or URL/layout area/link/order. |

### Taxonomies

| Taxonomy | Attached to | Editor use |
|---|---|---|
| `programme_type` | Programme | Signature/partner classification (legacy taxonomy). |
| `programme_country` | Programme | Country relationships and Africa footprint terms; country term editor also has map metadata described below. |
| `programme_theme` | Programme | Programme archive filters and homepage project-theme mosaic. |
| `programme_pillar` | Programme | Programme pillar grouping/filtering. |
| `programme_county` | Programme | Kenya county programme relationships. |
| `team_department` | Team Member | Department/group sections; custom order/label/appearance fields are implemented by Leadership code. |
| `team_county` | Team Member | Team county/coverage grouping. |
| `job_department` | Job | Careers department classification. |
| `sector` | Project | Legacy project-sector classification. |
| `expert_category` | Pool of Experts | Expertise filters. |
| `testimonial_category` | Testimonials | Story classification. |
| `partner_category` | Homepage Partners | Partner classification. |
| `project_theme` | Project Folders | Project-folder theme grouping. |

Terms for several taxonomies are seeded by the theme. Editors can add/edit terms through the corresponding taxonomy menu or editor sidebar. Taxonomy terms provide classification; they do not necessarily control every visual map point or page-level relationship (see known gaps).

## Page-level content controls

`Pages → Edit → SRC Page Content` is a custom meta box defined in `src_page_field_schema()`. The normal WordPress page editor remains available, but the branded page templates primarily render these named fields.

| Page/template area | Current custom fields |
|---|---|
| Shared page hero | Hero title, title emphasis, eyebrow, intro and hero image URL. |
| About / story | Story eyebrow/heading/body/image/stat/stat label; pillars as `Label | Description`; values as `Title | Description`; Theory of Change eyebrow/heading/steps as `Number | Title | Description`; impact stats as `Value | Label`; team CTA eyebrow/heading/button. |
| What We Do | Service pillars as `taxonomy key | number | title | tagline | description | audience | gradient start hex | gradient end hex | accent hex`; delivery eyebrow/heading/methods; partnership CTA eyebrow/heading/button. |
| Contact | Eyebrow/heading/intro/email/city/coverage; form and offices eyebrow; offices as `Name | Location | Email | Accent`; social links as setting key/label/handle; enquiry options as `value | label`; submit/success/error text. |
| Careers | Eyebrow/heading/application email; positions/requirements/apply/close/form labels; name/email/phone/cover placeholders; CV prompt; submit/success/error/empty-state text; equal-opportunity eyebrow/heading/copy; workforce stats as `Value | Label`. Job records are managed separately in Careers. |
| Pool of Experts | All-filter label; CTA eyebrow/heading/copy/button/URL. Expert records and category filters are managed separately. |
| Get Involved | Paths as `ID | Label | Subtitle | Description | Background hex`; capabilities as `path ID | capability`; form eyebrow/heading/intro; placeholders; submit/success/error text and enquiry recipient email. |

The page fields are plain text/textarea/URL inputs, not a visual page builder. Structured row fields require the documented delimiter format. Images are URL text fields in this meta box; use stable Media Library URLs if an editor needs to replace them.

## Global and homepage settings

**Appearance → SRC Content Settings** is backed by the `src_settings` option. Current groups and fields:

- **Homepage hero and header:** hero meta line; multiline title; accent line; description; hero image URL; header CTA kicker and label.
- **Homepage section headings:** sectors eyebrow/heading/intro; audience eyebrow/heading; footprint eyebrow/heading; partners eyebrow/heading; projects eyebrow/heading; team eyebrow/heading; programmes eyebrow/heading.
- **Homepage quote:** eyebrow, quote text, attribution and summary.
- **Newsletter and footer:** newsletter eyebrow/heading/email label/placeholder/button; site description; county footprint; contact city/email/phone; LinkedIn/X/Facebook/YouTube URLs.
- **Leadership settings:** department/section labels, taglines and related presentation fields supplied by `src_leadership_settings_schema()`; team cards themselves are Team posts.

Homepage data records such as sectors, audiences, impact metrics, partner marquee entries, open programmes, testimonials, news and publication are queried from their respective content types. Homepage text is only partially settings-driven: some headings/eyebrows and fixed call-to-action labels remain literal template copy.

## Africa and Kenya footprint CMS behavior

### Africa countries

Country records use the `programme_country` taxonomy. Each term editor includes:

- Two-letter SVG/map code
- Summary line
- Display order
- Selected map scale
- Selected map X and Y offsets
- Headquarters-country toggle

The map’s displayed programme links are assembled from published v3 Programme records. Kenya currently displays all v3 programmes; other country terms use programme-country term assignments. A few regional country codes have a fallback to programmes whose reach starts with “Africa”. The displayed programme card links resolve to the corresponding programme permalink.

### Kenya counties

Programme County terms can be assigned to programmes. The map code also has a fixed active-county slug list, and Nairobi has a fixed list of programme slugs. This means **country terms and their programme assignments are substantially manageable in wp-admin, while map pointer coordinates and some grouping behavior are not fully editor-managed**. A new map country/county cannot yet be added end-to-end without checking or changing theme code.

## Commerce data flow

1. Editor creates/updates a Programme in wp-admin.
2. Programme fields define current/upcoming/past status, enrolment state, fee label and numeric KES amount.
3. For an imported v3 programme published as `current`, marked `open`, and given a valid whole-number fee (or explicitly marked Free), `inc/commerce/programme-products.php` creates or synchronizes a hidden, virtual, individually sold WooCommerce product.
4. Product price and publish/draft availability are synchronized from the programme fields. Do not independently edit the linked product price; the programme record is the intended source of truth.
5. Registration modal submits programme ID, Kenyan phone, email, consent and nonce to `src_v3_prepare_programme_checkout` over WordPress AJAX.
6. Server validates the programme state and input, adds its product to the WooCommerce cart, stores billing phone/email in the WC customer session, calculates totals and returns the checkout URL.
7. WooCommerce renders checkout and its enabled payment methods, including M-Pesa when active/configured.
8. WooCommerce order and payment processing are handled by WooCommerce and the gateway plugin.

The price field is CMS-editable, but payment availability is conditional: a programme must be published, v3-imported, current, open, and have an allowed valid fee. Closed, upcoming or unpriced programmes should not be purchasable. Temporary prices currently set on the open catalogue are **KES 100** and must be replaced with approved fees before promoting real registration.

## What is CMS-driven, partly CMS-driven, and code-owned

| Area | Classification | Notes |
|---|---|---|
| Programme titles, copy, status, fees, partners, audience, dates and media | CMS-driven | Programme CPT and custom fields. Product price/availability derive from these fields. |
| Staff profiles and department/county assignment | CMS-driven | Team CPT, taxonomies and profile meta; section presentation still comes from theme. |
| Open jobs and job details | CMS-driven | Job CPT and department terms. Application form behavior/template is code-owned. |
| Homepage partner records/logos | CMS-driven with asset caveat | Partner CPT; logo fields are URL/thumbnail. The theme's logo presentation and carousel behavior are code. |
| Footprint country labels, country codes, order, summary, HQ and zoom offsets | CMS-driven | Country taxonomy term fields. |
| Footprint map point geometry / some Kenya mappings | Partly CMS-driven | Map asset and marker-coordinate mapping are in code; Kenya list and Nairobi programme slugs include hard-coded values. |
| Homepage impact data | CMS-driven | Impact Metrics records; section shell/title and card layout are code. |
| News, testimonials, publications and portfolio records | CMS-driven | Dedicated CPTs and custom fields; routes and section visibility depend on theme queries/templates. |
| Branded page copy | Partly CMS-driven | Custom page fields cover major sections; some page copy and headings remain embedded in PHP. |
| Header/footer links | Partly CMS-driven | WordPress menu may control navigation; theme fallback and select CTA labels/paths are code/settings. Footer links/layout include fixed labels. |
| Visual system, responsive layout, animation, modal and carousel behavior | Code-owned | CSS/JS/templates; this is implementation behavior rather than normal editorial data. |
| Images | Partly CMS-driven | Featured images work in supported CPTs; multiple legacy/custom fields expect pasted URLs or theme-relative asset paths. |
| Woo store settings, checkout page and payment gateway | WooCommerce/plugin-managed | Currency, pages, payment enablement and credentials are controlled in WooCommerce/plugin settings. |

## CMS gaps and recommended fixes

### P1 — Make footprint additions truly no-code

- Store marker X/Y coordinates (and map view/zoom if needed) on each country/county term, render marker positions from those fields, and provide clear validation/help text.
- Remove the hard-coded country/county slug allowlists and Nairobi programme slug list; derive points from taxonomy terms and programme assignments.
- Define a clear policy for country programme fallback instead of implicitly showing all Africa-reach programmes for selected regional countries.
- Add preview/validation for missing country code, invalid SVG code, missing marker coordinates, and terms with no linked programmes.

### P1 — Remove hard-coded editorial copy from templates

- Inventory the headings, eyebrows, CTA labels, map intro, enrollment badges, homepage section labels and empty states in `front-page-replica.php`, page templates, archives and footer.
- Add settings/page fields for copy content editors are expected to change; leave design tokens, component structure and animation logic in code.
- In particular, wire currently-present but unused settings such as footprint intro or section copy if editorial control is expected.

### P1 — Fix and verify news public routing

- Correct the duplicate `publicly_queryable` assignment in `inc/v3-content-model.php` so `src_news_item` receives the intended public single/archive behavior.
- Confirm archive rewrite, singular route and internal news links after rewrite flush. Until then, treat the news listing/link behavior as a functional gap.

### P2 — Replace URL-only image editors with Media Library controls

- Provide attachment selectors/previews for hero images, page story images, partner logos, map-related artwork, programme fallback images, expert portraits and news/testimonial images.
- Preserve current URL/path fields for compatibility during migration; use attachment IDs as the preferred stable reference.

### P2 — Consolidate duplicate/legacy fields and ordering

- Programme fields exist both in the legacy `Programme Details` meta box and the newer `SRC v3 Content` box (for example status, duration, dates/fee, partner and homepage order). Clarify which field is authoritative and hide/deprecate duplicates.
- Partner records similarly have legacy homepage tile fields and newer marquee fields. Add one clearly named editor workflow or split the admin labels by usage.
- The content model currently has legacy `project` records alongside programme-based project-folder/mosaic data. Document the relationship and remove unused controls only after checking existing production records.
- Replace hard-coded default slug-order maps with editable numeric order fields wherever editors should choose sequence.

### P2 — Improve structured content editing

- Several fields accept pipe-delimited rows or newline-separated lists. These are manageable but error-prone, difficult to validate and not accessible for non-technical editors.
- Replace repeated rows (offices, service pillars, impact facts, partner/funder lists, programme journey) with repeatable admin controls and validation.
- Add field descriptions, examples and safe bounds to settings and custom meta fields; use a color picker for hex-color fields and media selectors for images.

### P2 — Make homepage section visibility and empty states explicit

- Several sections render only if matching featured posts exist; there are no consistent section-level visibility controls or editorial preview status.
- Add explicit section toggles and clear editor notices explaining why a section is hidden (no featured item, missing image, missing link, etc.).
- Add an editor preview workflow for page fields and custom records, and verify draft preview does not expose unpublished records publicly.

### P3 — Add content governance and operational documentation

- Add a “content owner / update cadence” note for programmes, partners, jobs, impact metrics and payment settings.
- Document which menu assigns header/footer navigation and the exact required page slugs for branded templates.
- Add an admin dashboard checklist for missing programme fee/product link, missing country map code, broken URLs, absent featured images and M-Pesa configuration status without exposing secret values.

## Operational editor instructions

### Update a programme fee

1. Go to **Programmes → All Programmes → Edit** the programme.
2. In **SRC v3 Content**, set status, enrolment, fee label and **WooCommerce price in KES**.
3. Ensure programme is published, status is Current, enrolment is Open, and amount is a valid whole KES amount (or set fee label to Free for a zero-price enrolment).
4. Save/update. The linked hidden virtual product is synchronized by the theme.
5. Check the programme's registration flow and WooCommerce checkout. Do not manually expose the hidden linked product in the catalogue.

### Update a country and assign programmes

1. Go to **Programmes → Programme Countries** and edit/add the country term.
2. Set the two-letter code matching the SVG/map feature, summary, display order, HQ toggle and selected-view scale/offsets.
3. Assign the country term to each appropriate Programme.
4. Confirm the homepage footprint map. Current code still has coordinate and fallback limitations; adding a new geography may require a theme change until the P1 gap is addressed.

### Configure real M-Pesa

1. Go to **WooCommerce → Settings → Payments → M-Pesa** (exact tab wording may vary by gateway version).
2. Enter production Daraja details directly in wp-admin; never paste them into chat, documentation, source files or Git.
3. Select the production environment and verify the gateway callback/return configuration required by the plugin.
4. Run a low-value controlled payment, confirm the provider result and WooCommerce order status, and confirm failure/cancellation behavior.

## Verification limits

- The production registration-to-checkout redirect and M-Pesa method presence were tested using a fresh browser session with test contact details. No payment/order submission was performed.
- HTTP smoke checks returned 200 for the homepage and programme archive. An empty `/checkout/` redirects to `/cart/`, as expected when WooCommerce has no cart item.
- The plugin’s live STK Push behavior remains unverified until the owner configures production credentials and authorizes a controlled transaction.
- This audit describes the current theme source and the production checks completed on 6 November 2026. It does not assert that every field has been changed through wp-admin in a production edit/publish test.

## Source files reviewed

- `wp-content/themes/smartregionalconsultants/functions.php` — legacy CPTs/taxonomies, page meta, settings, country term fields, footprint queries and seed routines.
- `wp-content/themes/smartregionalconsultants/inc/v3-content-model.php` — v3 CPT/taxonomy registration, custom meta schema, save handlers and programme archive filtering.
- `wp-content/themes/smartregionalconsultants/inc/v3-leadership.php` — leadership settings, team grouping, department fields and team rendering.
- `wp-content/themes/smartregionalconsultants/inc/v3-careers.php` — Careers page/jobs and application handler.
- `wp-content/themes/smartregionalconsultants/inc/v3-pool-experts.php` — expert categories/seeding.
- `wp-content/themes/smartregionalconsultants/inc/commerce/programme-products.php` — programme product synchronization and checkout AJAX endpoint.
- `wp-content/themes/smartregionalconsultants/assets/js/registration.js` — frontend registration modal and checkout redirect behavior.
- `wp-content/themes/smartregionalconsultants/front-page-replica.php` — homepage data queries, section rendering and fixed copy.
- Theme page/archive/single templates and `woocommerce.php`, `page-cart.php`, `page-checkout.php` — page routing and WooCommerce presentation.

