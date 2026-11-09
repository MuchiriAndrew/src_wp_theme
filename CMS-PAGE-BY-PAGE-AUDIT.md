# SRC website CMS control audit

**Audit date:** 9 November 2026<br>
**Environment:** local `smartregionalconsultants.test` WordPress install<br>
**Theme branch:** `codex/src-v3-implementation`<br>
**Target:** at least 95% of meaningful page content can be changed in WordPress without editing code.

## Result

The active SRC page templates are estimated at **95–99% CMS control of meaningful editorial content**. Scores are estimates from the rendered pages and their WordPress data wiring, not a literal percentage of DOM text. Copy, media, item data, destinations and ordering count as editorial content. Layout, styling, responsive behavior, accessibility semantics, animation and transaction logic are implementation behavior. Short UI labels are CMS controlled where they are editorially meaningful; small technical labels and behavior-only text remain code or plugin defaults.

This is a local theme and database audit. It does not certify that the same data, routes, settings or media have been promoted to staging or production. The public stock `/sample-page/` remains published and should be removed or repurposed. Empty `/cart/`, direct `/checkout/` and `/my-account/` routes redirect by design; their route behavior is not a CMS content page and is recorded separately.

| Page type / route | Estimated CMS control | Where editors control it | Remaining code or service ownership |
|---|---:|---|---|
| Homepage `/` | 97% | SRC Content Settings, home record types and linked programme/news/publication records | Layout, map artwork/geometry, motion, carousel behavior and a few short interface labels |
| About `/about/` | 98% | Page edit form: hero, story, pillars, values, theory, stats and CTA | Layout, section order and a few fixed accessibility/breadcrumb strings |
| Leadership `/leadership/` | 97% | SRC Content Settings for hero, section names/taglines, board placeholders, filters and CTA; Team records for profiles/order/groups | Profile-card design, filters and a small number of utility labels |
| What We Do `/what-we-do/` | 97% | Page edit form: hero, service pillars, delivery methods and partnership CTA | Visual system and layout; data rows are plain text rows rather than structured repeaters |
| Programmes archive `/programmes/` | 98% | SRC Content Settings for archive, filters and section copy; Programme records and Programme Themes for cards and categories | Filter mechanics, layout and small metric/metadata labels |
| Programme detail `/programmes/{slug}/` (27 records) | 96% | Programme title/body, programme metadata, taxonomies, image, status, dates, fee, audience, outcomes, delivery, geography and relationships in Programme editor | Layout, derived metadata, registration/payment behavior and a few fixed section labels |
| Get Involved `/get-involved/` | 97% | Page edit form: hero, pathways, capabilities, form copy, recipient and response messages | Form fields, validation, delivery and interaction behavior |
| Contact `/contact/` | 97% | Page edit form: hero, office rows, contact information, enquiry choices, labels, recipient, social links and response messages | Form field schema, validation, mail handling and layout |
| Partners `/partners/` | 98% | Page edit form for hero/directory copy; Partner records for name, logo, link and order | Directory layout and partner logo presentation |
| Portfolio `/portfolio/` | 97% | Page edit form for hero and card link; Programme records supply each listing item and ordering | Portfolio remains programme-backed; card structure is code-owned |
| Pool of Experts `/pool-of-experts/` | 97% | Page edit form for hero, category filter and CTA; Expert records and Expert Categories for directory | Filter and card behavior |
| Careers `/careers/` | 96% | Page edit form for hero, empty state, application copy, recipient, EOE copy and stats; Job records for roles and requirements | Form schema, file limits, validation, uploads, mail handling and interaction |
| News archive `/news/` | 98% | SRC Content Settings for archive copy and labels; Latest News records for stories, images, category/date and order | Card/grid layout and navigation mechanics |
| News story `/news/{slug}/` (3 records) | 98% | Latest News editor: title, body, excerpt, image, date/category and linked programme | Reading layout, breadcrumbs and a few fixed fallback labels |
| Our Work archive `/our-work/` | 98% | SRC Content Settings for archive eyebrow, heading and introduction; Project records supply cards, images, summaries, partner, place and order | Grid layout, card behavior and visual treatment |
| Project detail `/our-work/{slug}/` (8 records) | 98% | Project record title, excerpt/body, featured image, partner, location, year and impact; SRC Content Settings for detail eyebrow and section heading | Detail layout and fallback values when fields are empty |
| Team profile `/leadership/{slug}/` (37 records) | 98% | Team record title, role, short/full bio, portrait, LinkedIn, county/level and department; SRC Content Settings for profile eyebrow and back link | Profile layout, breadcrumb labels and LinkedIn link label |
| Shop `/shop/` | 97% | SRC Content Settings for catalogue copy; WooCommerce Products and programme records for names, images, fees and availability | Store layout, registration flow, payment methods and WooCommerce behavior |
| Product detail `/product/{slug}/` (4 records) | 97% | WooCommerce Product title, description, image, price, stock/status, categories and purchase settings | Product layout, WooCommerce behavior and some default taxonomy/UI labels |
| Cart `/cart/` | N/A | No public cart screen in this registration flow | Empty/direct cart intentionally redirects to Programmes; populated registration bypasses a general cart page |
| Checkout `/checkout/` | N/A for direct URL; 95%+ for editorial checkout content | Checkout helper/confirmation copy in SRC Content Settings; order and payment fields in WooCommerce and checkout/payment plugins | Direct empty checkout redirects to Programmes; populated transaction is session/order/plugin managed |
| My Account `/my-account/` | N/A | Not part of the current no-account registration journey | Route intentionally redirects to Programmes |
| Sample Page `/sample-page/` | 100% editable, but not site content | Standard WordPress page title and body | Stock WordPress sample text remains published; remove or repurpose before release |

The average across the 19 active editorial page types (excluding transaction routes whose UI requires a valid cart/account context) is approximately **97.3%**. This aggregate is a planning estimate; it must not be read as a precise count of characters or pixels. Every active editorial family in the local route inventory is estimated at or above the 95% target. This does not include stale stock WordPress content, which remains a cleanup item.

## Scope and verification

- Reviewed all 14 published Page records, all 27 published Programme detail routes, all 37 Team profile routes, all 8 Project detail routes, all 4 Product detail routes, all 3 News story routes, plus the Homepage and public archives/Shop/utility routes. A published default `Hello world!` post and `/sample-page/` are stock content, not active branded page families, and should be removed or repurposed.
- Browser QA swept 98 published Page/record/post URLs plus the Leadership, Programmes, News and Our Work archives at 390px CSS width, checking final URL, page title/H1, image loads and horizontal overflow. No route had horizontal overflow or a missing H1. The homepage has one intentionally empty image `src` in the hidden footprint dialog; the dialog image is assigned when a country is selected. Representative Our Work archive, Project detail, Team profile and Product detail pages were also checked at 1280px desktop. Product detail uses WooCommerce's standard product summary and tabs. Empty Cart and direct Checkout resolve to Programmes; My Account resolves to Programmes as configured by the registration journey.
- Cross-checked page forms and front-end output against `src_page_field_schema()`, `src_settings_fields()`, post-type fields, taxonomies, theme templates, WooCommerce templates and plugin-managed checkout configuration.
- Current theme code exposes 123 page-field definitions and 215 content-setting definitions. Theme-managed post types use standard WordPress form controls with the block editor disabled; image fields can open the Media Library. WooCommerce utility pages keep their normal WooCommerce function and page body rather than opening in the block/classic editor.
- Ran PHP syntax checks for all theme PHP files: all passed.
- Ran JavaScript syntax checks for `assets/js/registration.js` and `assets/js/home-v3.js`, plus `git diff --check`; all passed.
- Admin UI data was not edited and saved in a browser during this run. Consequently, the audit verifies the CMS controls are registered and wired into the rendered templates, but not a live editor save/publish round-trip or the logged-in role permissions.
- The site database contains records with sparse or generic descriptive text. Their fields are editable, so they meet CMS control; their editorial completeness and accuracy are separate content-quality work.

## Shared site chrome

### Header and navigation

**CMS controlled:** WordPress Appearance → Menus controls primary menu labels, URLs and nesting when a Primary menu is assigned. Appearance → Customize controls the Custom Logo. SRC Content Settings controls header CTA kicker, label and destination.

**Code controlled:** responsive navigation, menu depth, mobile drawer, icons, markup and styling. If no Primary menu is assigned, the theme displays a fallback menu; the fallback is code-owned.

### Footer

**CMS controlled:** footer menus; email, phone, city and county footprint; social URLs; newsletter heading, eyebrow, explanatory copy and link; footer column headings and company name.

**Code controlled:** layout, responsive behavior, newsletter destination behavior, copyright year and menu fallback markup.

### Page and record editing experience

Theme-managed Pages, programmes, people, jobs, partners, experts, news, publications and home content records use ordinary WordPress admin forms. The theme disables Gutenberg/editor canvas for these types and provides a regular content textarea plus labelled meta fields. Branded page fields appear in page-specific groups. Image fields can use the Media Library.

The shop/cart/checkout/account Pages retain WooCommerce’s functional handling. Their edit form preserves the page body and explains where the store behavior is configured. Editors should manage products, checkout fields, gateways and orders through the standard WooCommerce/plugin screens.

Some repeatable page data is stored as newline-delimited text rows (for example service pillars, offices and involvement pathways). This is editable without code but is less user-friendly and validates less safely than a dedicated repeater UI; it is a usability improvement, not a content-control gap.

## Page-by-page content inventory

### 1. Homepage — `/`

**Visible content:** hero and CTAs; enrolment carousel; impact metrics; footprint/map; programme and project cards; partner strip; testimonials; news; featured publication; newsletter/news link; shared footer.

**CMS controlled:** hero metadata, title, accent, intro, image and CTA; all hero/enrolment labels; programme cards from Programme records; impact labels, values, copy and links from Impact Metrics; map introduction and map labels plus country data; project/programme data; partner names/logos/order; testimonial story/copy/media; latest News and Publication records; section headings, links, empty states and footer copy.

**Not CMS controlled:** layout, section order, SVG map artwork, geographic marker positioning and map interaction, animation, carousel/modal behavior and responsive display. The logo uses the WordPress Custom Logo; confirm a SRC logo is selected in the target environment.

### 2. About — `/about/`

**Visible content:** hero; founding story and stat; vision/mission/purpose; values; theory of change; statistics; leadership CTA.

**CMS controlled:** all headings, paragraphs, image URL and alt text, stat data, value rows, theory steps and CTA text/destination in the page edit form.

**Not CMS controlled:** section sequence, visual design and breadcrumb. Rows use text delimiters rather than repeatable cards.

### 3. Leadership — `/leadership/`

**Visible content:** hero and actions; team section navigation; board placeholders; department headings/taglines; profile cards; programme-team county filter; Careers CTA.

**CMS controlled:** Team post names, roles, biographies, portraits, LinkedIn URLs, category, display inclusion/order and county roles; SRC Content Settings controls hero, board placeholders and copy, section labels/taglines, filter labels, CTA text and hero image.

**Not CMS controlled:** card and section layout, filter behavior, iconography and profile interaction. The current data renders several “Profile forthcoming” board slots; the count and placeholder copy are editable, but replacing the placeholder with actual board profiles is a content task.

### 4. What We Do — `/what-we-do/`

**Visible content:** hero; service pillars; delivery approach/methods; partnership CTA.

**CMS controlled:** hero copy/image; pillar titles, taglines, descriptions, audiences and colors; delivery heading/methods; CTA text and destination.

**Not CMS controlled:** visual design, section order and motion. Pillars are stored as one delimited row per pillar rather than structured repeaters.

### 5. Programmes archive — `/programmes/`

**Visible content:** archive hero; status/theme/signature filters and counts; current/upcoming/past sections; programme cards; no-results message.

**CMS controlled:** all archive/section/filter copy and hero image in SRC Content Settings; filters and card content derive from Programme records, status, taxonomy, ordering, fee, date, audience, reach, duration, tags, imagery, availability and product links.

**Not CMS controlled:** card/filter implementation, sort rules, responsive filter scrolling and layout. Counts are calculated from record data.

### 6. Programme detail — `/programmes/{slug}/`

**Visible content:** programme hero and status; summary; audience/outcomes; duration/delivery/journey; location/reach; registration/notification action; related programmes.

**CMS controlled:** Programme title/body and metadata, images, status/enrolment, fee/dates, partner/funder, audience, outcomes, duration, learning journey, delivery, geography, theme and related content.

**Not CMS controlled:** field presentation, derived values/relationships, registration behavior and a few behavior-only labels. Registration dialog labels, progress steps, input prompts, consent copy, validation text, payment states and return actions are in SRC Content Settings. All 27 published local routes resolved to their own detail page during browser QA.

### 7. Get Involved — `/get-involved/`

**Visible content:** hero; partnership pathways and capability rows; enquiry form; response/error copy.

**CMS controlled:** page form fields for hero, pathways, capability descriptions, field labels/placeholders, form headings, recipient, button and response messages.

**Not CMS controlled:** field schema, validation, delivery and interaction behavior. Pathways/capabilities use delimited rows.

### 8. Contact — `/contact/`

**Visible content:** hero; contact copy and details; offices; enquiry form/options; social links; response/error copy.

**CMS controlled:** page fields and SRC Content Settings cover copy, contact details, offices, enquiry choices, field labels, recipient, social handles and messages.

**Not CMS controlled:** form schema, validation/mail processing, icons and layout. Office/options/social rows use delimited text.

### 9. Partners — `/partners/`

**Visible content:** hero; directory heading/introduction; partner logos/names/links; shared footer.

**CMS controlled:** page edit form controls hero and directory text; Partner records control display name, logo, external URL, order and inclusion. Partner image fields support Media Library selection.

**Not CMS controlled:** responsive grid and decorative presentation. Confirm each environment has the intended partner records and logo attachments.

### 10. Portfolio — `/portfolio/`

**Visible content:** hero; programme/case-study cards; shared footer.

**CMS controlled:** hero and card action via the Portfolio page fields; cards derive their title, copy, image, audience and destination from Programme records; item ordering comes from programme home order.

**Not CMS controlled:** portfolio currently uses Programme records instead of a separate case-study content type; card layout and initials are generated in code.

### 11. Pool of Experts — `/pool-of-experts/`

**Visible content:** hero; category filters; expert cards; engagement CTA.

**CMS controlled:** page hero, all-categories label and CTA copy/destination; Expert records and Expert Categories control profiles, specialties, images, copy, category and ordering.

**Not CMS controlled:** filter/card interaction and layout.

### 12. Careers — `/careers/`

**Visible content:** hero; current roles and requirements; application form/upload; equal-opportunity copy and metrics; empty/success/error states.

**CMS controlled:** Jobs records control roles, descriptions, requirements, location, type, department, deadline and order. Page fields control hero, application recipient, labels, instructions, upload guidance, EOE content, stats and response messages.

**Not CMS controlled:** form fields, required validation, upload limit/type enforcement, mail processing and layout.

### 13. News archive — `/news/`

**Visible content:** hero, archive copy, featured/listed stories and empty state.

**CMS controlled:** archive text/action labels in SRC Content Settings; story title/body/excerpt/date/category/image/order in Latest News records.

**Not CMS controlled:** archive/card layout and pagination/display behavior. Local route renders successfully.

### 14. News story — `/news/{slug}/`

**Visible content:** breadcrumb/category/title/date/featured image/article body/footer.

**CMS controlled:** Latest News title, standard page body, excerpt, date/category, featured image and related programme fields.

**Not CMS controlled:** reading layout, breadcrumb format and fallback category wording. All three published local story routes resolved successfully.

### 15. Shop — `/shop/`

**Visible content:** commerce hero; catalogue heading/intro; programme registration cards; empty state; footer.

**CMS controlled:** catalogue/empty copy in SRC Content Settings; product titles/prices/status/images and linked Programme metadata in WooCommerce and Programme records.

**Not CMS controlled:** store/catalogue layout and purchase/registration behavior.

### 16. Our Work archive — `/our-work/`

**Visible content:** archive eyebrow/title/intro; all project cards with title, summary, image, partner and location; shared site chrome.

**CMS controlled:** the three archive text fields are in SRC Content Settings. Project records control each card's title, excerpt, image, partner, location and ordering.

**Not CMS controlled:** grid/card layout and decorative styling. All eight published cards appeared in the rendered archive.

### 17. Project detail — `/our-work/{slug}/` (8 routes)

**Visible content:** project eyebrow; title and excerpt; partner/location; detail heading and body; image; year and impact; shared site chrome.

**CMS controlled:** Project record title, excerpt/body, featured image, partner, location, year and impact; detail eyebrow and section heading are in SRC Content Settings. Empty fields use code fallbacks, so editors should complete the record fields for accurate display.

**Not CMS controlled:** layout, image crop and fallback wording. The representative SME Business Strategy Advisory route rendered its own title/body and loaded images at desktop and mobile sizes.

### 18. Team profile — `/leadership/{slug}/` (37 routes)

**Visible content:** breadcrumb; profile eyebrow and department; name, role, county/level, summary, portrait, LinkedIn link; full bio and back link when content exists; shared site chrome.

**CMS controlled:** Team record title, role, short/full bio, portrait, LinkedIn URL, county and county level; Team Department taxonomy; global profile eyebrow and back-link label in SRC Content Settings.

**Not CMS controlled:** profile layout, breadcrumb labels and the short “LinkedIn” link label. Representative Catherine Gitonga profile rendered its fields and loaded imagery at desktop and mobile sizes.

### 19. Product detail — `/product/{slug}/` (4 routes)

**Visible content:** WooCommerce product title, price, image, quantity/add-to-cart controls, category, description/tabs; shared site chrome.

**CMS controlled:** product title, long/short description, featured/gallery images, price, sale price, stock/status, SKU, categories/tags and purchase settings through the ordinary WooCommerce Product form.

**Not CMS controlled:** WooCommerce layout and behavior, quantity/cart mechanics, and WooCommerce's default strings/taxonomy values. Product detail is rendered by `woocommerce_content()` in the theme template. The representative product route rendered product data and all page images loaded at desktop/mobile widths.

### 20. Cart — `/cart/`

This registration flow does not expose a general cart screen. Empty/direct Cart requests intentionally redirect to Programmes; registrations start a session-backed checkout from the Programme card. This is functional route behavior rather than editable page content.

### 21. Checkout — `/checkout/`

Checkout is a session-backed transaction screen. Checkout step and payment confirmation copy are in SRC Content Settings. The programme registration dialog also uses ordinary settings fields for its steps, prompts, validation, payment status and button copy. Order items, prices, payment methods and configurable checkout fields are managed by WooCommerce and installed checkout/payment plugins. Direct checkout without a selected programme redirects to Programmes. A populated payment flow was not submitted during this audit.

### 22. My Account — `/my-account/`

Customer accounts are not used in the current registration flow. The route intentionally redirects to Programmes. If accounts become part of the product journey, that route and its login/registration content will need a separate scope and QA.

### 23. WordPress Sample Page — `/sample-page/`

The local site still publishes the default WordPress example page. Its title/body are editable through the normal Page form, but the generic example content should not be part of the branded site. Remove it or replace it with intended site content before release.

### 24. WordPress sample post — `/hello-world/`

The default “Hello world!” post is also published. Its title/body are editable in the ordinary post form, but it is not an active branded content type and should be removed or repurposed before release.

## Published record route inventory

In addition to the page families above, these are the published detail-route slugs included in the local database inventory. Every record in each family uses the same fields described above.

- **Programmes (27):** `/programmes/graca-machel-investor-readiness/`, `/programmes/herventure/`, `/programmes/kcb-mifugo-ni-mali/`, `/programmes/investor-readiness/`, `/programmes/export-readiness/`, `/programmes/webinar-series/`, `/programmes/road-to-growth/`, `/programmes/road-to-markets/`, `/programmes/casha/`, `/programmes/ntf-iii-it-ites/`, `/programmes/usaid-acdi-voca/`, `/programmes/village-capital-bds/`, `/programmes/giz-business-scouts/`, `/programmes/giz-sme-loop/`, `/programmes/palladium-usaid/`, `/programmes/road-to-leadership/`, `/programmes/kuza-jamii/`, `/programmes/ande-circularity/`, `/programmes/seed/`, `/programmes/lift-nk/`, `/programmes/sheconnects/`, `/programmes/regional-enterprise-support/`, `/programmes/women-creating-wealth/`, `/programmes/laikipia-livestock-spv/`, `/programmes/livestock-market-systems/`, `/programmes/investor-readiness-bootcamp/`, `/programmes/expert-consultation-session/`.
- **News (3):** `/news/road-to-growth-returning-soon/`, `/news/cooperatives-stronger-markets/`, `/news/what-investment-readiness-means/`.
- **Team profiles (37):** `/leadership/abdirizack-mohammed/`, `/leadership/moses-odipo/`, `/leadership/abdikarim-ado/`, `/leadership/starnsilaus-auren/`, `/leadership/rahma-adan-jillo/`, `/leadership/mulki-abdi-hassan/`, `/leadership/meshack-nyerere/`, `/leadership/lomosingo-kokiro/`, `/leadership/lenairerei-ndeca/`, `/leadership/kennedy-oduor-omondi/`, `/leadership/jaffer-ayub/`, `/leadership/hannah-choke/`, `/leadership/gerald-mahali/`, `/leadership/esther-wahome/`, `/leadership/enock-toroitich-kochulem/`, `/leadership/diana-ajore/`, `/leadership/daniel-naro-ngichwae/`, `/leadership/barile-korme/`, `/leadership/ali-alio-mohamed/`, `/leadership/abdifatah-yussuf-konyow/`, `/leadership/eddy-innocent-ochieng/`, `/leadership/malone-bruno-oriedi/`, `/leadership/david-njoroge/`, `/leadership/risper-wanja/`, `/leadership/keisla-adhiambo-otieno/`, `/leadership/micah-kiangi/`, `/leadership/jane-malo/`, `/leadership/mary-wanjiku/`, `/leadership/judy-waruguru/`, `/leadership/john-kangata-ndinguri/`, `/leadership/cynthia-wanja-theuri/`, `/leadership/caroline-nyagah/`, `/leadership/emmaculate-mulinge/`, `/leadership/bonano-badia/`, `/leadership/florence-ndwiga/`, `/leadership/kennedy-njue/`, `/leadership/catherine-gitonga/`.
- **Projects (8):** `/our-work/sme-business-strategy-advisory/`, `/our-work/livestock-market-systems/`, `/our-work/wash-enterprise-development/`, `/our-work/agrovet-network-scale-up/`, `/our-work/dairy-cooperative-strengthening/`, `/our-work/her-venture/`, `/our-work/hustle-economy-accelerator/`, `/our-work/kijani-co-operative-growth-programme/`.
- **Products (4):** `/product/investor-readiness-enrolment/`, `/product/export-readiness-enrolment/`, `/product/webinar-series-enrolment/`, `/product/herventure-enrolment/`.

## Remaining actions

1. Confirm the WordPress Custom Logo setting points to the approved SRC logo in each target environment.
2. Remove or repurpose the public Sample Page.
3. Review generic/sparse Programme record descriptions for editorial quality; their displayed fields are editable.
4. If editors need safer, simpler editing for repeatable service, office, pathway and stat rows, replace delimiter-based textareas with structured repeatable fields.
5. Before promoting, sign in as an editor and save a draft update on one Page, one Programme, one Partner and one News item, then verify the rendered preview. No staging or production changes were made as part of this audit.
