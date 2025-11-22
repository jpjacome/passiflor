# Therapies Feature — Implementation Plan

Last updated: 2025-10-31

This document summarizes the design and implementation plan for converting the existing static `resources/views/therapy.blade.php` into a dynamic, editable therapy template system. It reflects the team's decisions so far and the constraints provided:

- Editable by: admins and therapists
- Visibility: logged-in admins, therapists, and patients assigned to a therapy (patients optional/nullable on assignment)
- Content format: structured plain-text fields (no WYSIWYG). Image upload allowed (max 3 MB).
- Language: single-language initially (Spanish / `es`).
- Scale: small (max ~10 therapy templates initially).

## Goals

- Make the therapy content editable (the content that exists in `therapy.blade.php`), keeping the same page structures.
- Allow admins/therapists to create a new therapy template, add/remove pages, and edit page content.
- Support publishing and assignment to a patient (assignment is nullable).
- Keep implementation small and safe: plain text fields, server-side validation, and images stored under `storage/app/public/therapies`.

## Non-goals (initial)

- Public listing or public render changes (we will not modify the public `therapy.blade.php` yet).
- Complex WYSIWYG editing or media galleries.
- Progress tracking, versioning, scheduling, or sessions (these can be added later).

## Content modeling: page types found in the existing blade

After examining `resources/views/therapy.blade.php`, there are repeating page structures we will treat as page types. Grouping these yields a compact modeling approach:

- Hero (cover) page: the top `card-wrapper card-wrapper-1` block with large SVG, subtitle, main title and contact/metadata lines.
- Step / numbered page: repeated `card-wrapper card-wrapper-2` blocks. These have: a numeric indicator, a title, body (either paragraph or list), optional note, and an optional border image/icon.
- (Potentially) small info pages that reuse the step structure but without numbers.

We will support at least these page types initially: `hero`, `step`, and `info` (alias of `step` without number). Each page will be stored as a row with typed fields rather than raw HTML.

## Database design (recommended normalized approach)

Rationale: Since we must support adding pages, ordering, and simple editing, a normalized design keeps pages editable and makes queries easier to write.

1) `therapies` table (metadata)

- id (bigIncrements)
- slug (string, unique)
- title (string)
- short_description (text, nullable)
- cover_image (string, nullable) — path to uploaded image
- duration_minutes (integer, nullable)
- age_from (tinyInteger, nullable)
- age_to (tinyInteger, nullable)
- assigned_patient_id (nullable unsignedBigInteger) — FK to users (nullable)
- author_id (unsignedBigInteger, FK users)
- published (boolean, default false)
- created_at, updated_at, deleted_at (soft deletes optional)

2) `therapy_pages` table (pages linked directly to therapies)

- id
- therapy_id (foreignId -> therapies)
- position (unsignedSmallInteger) — order on the therapy
- type (string / enum) — one of `hero`, `step`, `info`
- number (nullable smallint) — for `step` pages (the numeric indicator shown in the blade)
- title (string, nullable)
- subtitle (string, nullable)
- body (text) — plain text, can contain newline-separated content; admin UI will provide a textarea and optional structured fields for lists
- list_items (json, nullable) — optional array of strings for repeatable bullet items (store as JSON)
- note (string, nullable)
- image (string, nullable) — path to any image/icon used on the page
- created_at, updated_at

Notes on fields

- `body` will be plain text. For sections that are lists in the blade, UI can either accept a textarea with each line being an item or an explicit list input saved to `list_items` JSON. Rendering will prefer `list_items` if present; otherwise the `body` will be rendered as paragraph(s) splitting on newlines.

## Eloquent model design (brief)

- App\\Models\\Therapy: hasMany(TherapyPage), belongsTo(Author/User), belongsTo(AssignedPatient optional)
- App\\Models\\TherapyPage: belongsTo(Therapy). $casts = ['list_items' => 'array']

## Routes & Controllers (planned)

Admin area (authenticated, roles: admin|therapist):

- GET /admin/therapies -> Admin\\TherapyController@index (list)
- GET /admin/therapies/create -> Admin\\TherapyController@create
- POST /admin/therapies -> Admin\\TherapyController@store
- GET /admin/therapies/{therapy}/edit -> Admin\\TherapyController@edit
- PATCH /admin/therapies/{therapy} -> Admin\\TherapyController@update
- DELETE /admin/therapies/{therapy} -> Admin\\TherapyController@destroy

Name routes: admin.therapies.*

Public/patient facing (later / separate PR):

- GET /therapies/{slug} -> TherapyPublicController@show (only for assigned patient or if published)

Access control: use policies or gates. Admin and Therapist roles can manage therapies. Patients can view only records where they are assigned (or where `published` is true if making public later).

## Storage / Images

- Upload path: `storage/app/public/therapies/{therapy_id}/` (use Laravel Storage facade and `public` disk)
- Max file size: 3MB (as requested)
- Allowed types: jpg, jpeg, png, svg (and possibly webp)
- Validation: `max:3072|mimes:jpg,jpeg,png,svg,webp` (apply server-side)

## Language

This implementation targets a single language initially (Spanish / `es`). That means:

- No translation table is required; all textual content lives directly on `therapies` and `therapy_pages`.
- The admin UI will present fields for the single language. If later multi-language is desired, we can add a `therapy_translations` table and migrate contents.

## Validation and shape contract (server-side)

When creating/updating a therapy and its pages validate:

- therapy.title required, string, max:200
- pages[*].type in [`hero`,`step`,`info`]
- pages[*].position integer >= 0
- pages[*].number integer nullable when type is `step`
- pages[*].title string nullable max:200
- pages[*].list_items array of strings (each max 200)
- image file validating max: 3MB and allowed mimes

On the client side (admin) we'll present a form with therapy metadata and a repeatable pages area where pages can be added/removed and reordered.

## Migration tasks (files to create)

Planned migrations (filenames approximate):

- 2025_11_01_000000_create_therapies_table.php
- 2025_11_01_000100_create_therapy_pages_table.php

Each migration will add the fields described above and proper foreign keys with cascade deletes where appropriate.

Example migration notes (create_therapy_pages):

- `$table->json('list_items')->nullable();`
- `$table->string('image')->nullable();`

## Admin UI (sketch)

- Admin list view: table of therapies with author, published flag, assigned patient
- Admin create/edit view: metadata form + repeatable pages UI (title, subtitle, body textarea and optional list editor, image upload, number for step pages)
- No WYSIWYG — plain text areas only. For lists allow either textarea (newline per item) or a small multi-input UI.

Render contract for public Blade (later work)

- For each page: controller will provide `$therapy->pages()->orderBy('position')->get()`
- `hero` page renders the SVG area and metadata fields; `step` pages render the number, title and list or paragraph body exactly like the static blade.

## Seed data

- Add a seeder to create the initial therapy template (e.g., `Poopy training`) with pages matching the existing `therapy.blade.php` content. This helps QA and preview.

## Tests

- Unit/feature tests to add:
  - Migration and model sanity tests (casts and relations)
  - Admin CRUD feature test: create therapy with pages, edit, delete
  - Access control tests: ensure only admin/therapist can manage; patient can view assigned therapy

## Implementation timeline & steps (concrete)

1. Create migrations for `therapies` and `therapy_pages`.
2. Create Eloquent models with relations and casts.
3. Create Admin\\TherapyController and resource views for admin CRUD (index/create/edit forms). Add validation logic and storage handling for images.
4. Seed initial therapy content (ES) using a seeder file.
5. Convert `resources/views/therapy.blade.php` to a dynamic template that accepts a `$therapy` and renders pages. (Do not modify public routing yet — keep this as a preview under admin or a preview route.)
6. Add policy and route protections (middleware auth + role gate). Wire `/admin/therapies` routes.
7. Add tests and run `php artisan migrate --seed` in dev.

## Security and privacy notes

- Validate uploads and sanitize file names. Use Storage::putFile to avoid path traversal.
- Enforce authorization in controllers and policies.
- Patient assignment is optional; if assigned, ensure only that patient (and staff) can view their therapy.

## Next immediate steps (what I can do for you next)

1. Provide the exact migration files (ready to apply) and model stubs.
2. Create the admin controller scaffolding and admin views for create/edit with repeatable pages UI.
3. Write the seeder to populate the initial therapy template with the ES content from the current static `therapy.blade.php`.

Tell me which of steps 1–3 you want me to implement first.
# Therapies Feature — Implementation Plan

Last updated: 2025-10-31

This document summarizes the design and implementation plan for converting the existing static `resources/views/therapy.blade.php` into a dynamic, editable therapy template system. It reflects the team's decisions so far and the constraints provided:

- Editable by: admins and therapists
- Visibility: logged-in admins, therapists, and patients assigned to a therapy (patients optional/nullable on assignment)
- Content format: structured plain-text fields (no WYSIWYG). Image upload allowed (max 3 MB).
- Multilanguage support: required from the start.
- Scale: small (max ~10 therapy templates initially).

## Goals

- Make the therapy content editable (the content that exists in `therapy.blade.php`), keeping the same page structures.
- Allow admins/therapists to create a new therapy template, add/remove pages, and edit page content.
- Support multiple locales for each therapy.
- Allow assigning a therapy to a patient (nullable on create).
- Keep implementation small and safe: plain text fields, server-side validation, and images stored under `storage/app/public/therapies`.

## Non-goals (initial)

- Public listing or public render changes (we will not modify the public `therapy.blade.php` yet).
- Complex WYSIWYG editing or media galleries.
- Progress tracking, versioning, scheduling, or sessions (these can be added later).

## Content modeling: page types found in the existing blade

- Support publishing and assignment to a patient (assignment is nullable).
- (Potentially) small info pages that reuse the step structure but without numbers.

We will support at least these page types initially: `hero`, `step`, and `info` (alias of `step` without number). Each page will be stored as a row with typed fields rather than raw HTML.

## Database design (recommended normalized approach)

Rationale: Since we must support adding pages, ordering, and multi-language from the start, a normalized design keeps pages editable without large JSON patches and makes queries easier to write.
- created_at, updated_at, deleted_at (soft deletes optional)

2) `therapy_translations` table (localized content wrapper)
- position (unsignedSmallInteger) — order on the therapy
- type (string / enum) — one of `hero`, `step`, `info`
- title (string, nullable)
- subtitle (string, nullable)
- body (text) — plain text, can contain newline-separated content; admin UI will provide a textarea and optional structured fields for lists
- image (string, nullable) — path to any image/icon used on the page
- created_at, updated_at

Notes on fields
- `body` will be plain text. For sections that are lists in the blade, UI can either accept a textarea with each line being an item or an explicit list input saved to `list_items` JSON. Rendering will prefer `list_items` if present; otherwise the `body` will be rendered as paragraph(s) splitting on newlines.

## Routes & Controllers (planned)

Admin area (authenticated, roles: admin|therapist):

- GET /admin/therapies/create -> Admin\\TherapyController@create
- POST /admin/therapies -> Admin\\TherapyController@store
- GET /admin/therapies/{therapy}/edit -> Admin\\TherapyController@edit
- PATCH /admin/therapies/{therapy} -> Admin\\TherapyController@update
- DELETE /admin/therapies/{therapy} -> Admin\\TherapyController@destroy

- GET /therapies/{slug}/{locale?} -> TherapyPublicController@show (only for assigned patient or if published)


- Upload path: `storage/app/public/therapies/{therapy_id}/` (use Laravel Storage facade and `public` disk)
- Max file size: 3MB (as requested)
- Allowed types: jpg, jpeg, png, svg (and possibly webp)
- Validation: `max:3072|mimes:jpg,jpeg,png,svg,webp` (apply server-side)
- UI will need to allow selecting locale while creating/editing and adding translations per therapy.
- Slugs: either store per translation (e.g., `slug` on translation) or use a single canonical slug and append locale to URL. Recommended: keep slug on `therapies` (canonical) and prefer path `/therapies/{therapy}/{locale}` or store locale-specific slugs on `therapy_translations` if you want SEO-friendly language slugs. For now, store slug on `therapies` and pass locale param.

Tradeoff: `therapy_translations` is slightly more complex but clean; alternative is storing `content` and `title` as JSON keyed by locale on `therapies`. We chose translations table for clarity and easier queries.

## Validation and shape contract (server-side)

- therapy.title (per-translation) required, string, max:255
- pages[*].type in [`hero`,`step`,`info`]
- pages[*].position integer >= 0
- pages[*].number integer nullable when type is `step`
- pages[*].title string nullable max:255

On the client side (admin) we'll present a form with one section for translation (locale), therapy metadata, and a repeatable pages area where pages can be added/removed and reordered.


- 2025_11_01_000000_create_therapies_table.php
- 2025_11_01_000100_create_therapy_translations_table.php
Each migration will add the fields described above and proper foreign keys with cascade deletes where appropriate.

Example migration notes (create_therapy_pages):
- `$table->json('list_items')->nullable();`
- `$table->string('image')->nullable();`


- Admin list view: table of therapies with locale(s), author, published flag, assigned patient
- Admin create/edit view: metadata form + translation chooser + repeatable pages UI (title, subtitle, body textarea and optional list editor, image upload, number for step pages)
- No WYSIWYG — plain text areas only. For lists allow either textarea (newline per item) or a small multi-input UI.

Render contract for public Blade (later work)

- For each page: controller will provide `$therapyTranslation->pages()->orderBy('position')->get()`
- `hero` page renders the SVG area and metadata fields; `step` pages render the number, title and list or paragraph body exactly like the static blade.

## Seed data

- Add a seeder to create the initial therapy template (e.g., `Poopy training`) with ES translation and pages matching the existing `therapy.blade.php` content. This helps QA and preview.

## Tests

  - Access control tests: ensure only admin/therapist can manage; patient can view assigned therapy

## Implementation timeline & steps (concrete)

1. Create migrations for `therapies`, `therapy_translations`, `therapy_pages`.
2. Create Eloquent models with relations and casts.
3. Create Admin\\TherapyController and resource views for admin CRUD (index/create/edit forms). Add validation logic and storage handling for images.
4. Seed initial therapy content (ES) using a seeder file.
5. Convert `resources/views/therapy.blade.php` to a dynamic template that accepts a `$therapyTranslation` and renders pages. (Do not modify public routing yet — keep this as a preview under admin or a preview route.)
7. Add tests and run `php artisan migrate --seed` in dev.

## Security and privacy notes

- Validate uploads and sanitize file names. Use Storage::putFile to avoid path traversal.
- Enforce authorization in controllers and policies.
- Patient assignment is optional; if assigned, ensure only that patient (and staff) can view their therapy.

## Next immediate steps (what I can do for you next)

1. Provide the exact migration files (ready to apply) and model stubs.
2. Create the admin controller scaffolding and admin views for create/edit with repeatable pages UI.
3. Write the seeder to populate the initial therapy template with the ES content from the current static `therapy.blade.php`.

Tell me which of steps 1–3 you want me to implement first. If you prefer a slightly different DB layout (for example, storing pages as JSON on `therapy_translations`), say so and I'll adjust the migration plan.

---

If you want, I can also produce an ER diagram and the exact migration PHP code next. Which of those would you like me to create now?
