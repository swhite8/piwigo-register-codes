# Register Codes for Piwigo

Requires a registration code when signing up through Piwigo's registration page. Codes support expiry dates, usage limits (0 means unlimited), batch generation, copying, and registration history.

## 1.5.1 security release candidate

- Require session-bound CSRF tokens for administrative submissions.
- Escape stored code, comment, and username output; keep code values out of inline JavaScript.
- Validate identifiers, counts, expiry dates, and field lengths on the server. Batch generation is limited to 100 codes per request.
- Escape SQL values using the active Piwigo database connection.
- Generate codes using cryptographically secure randomness.
- Clear stale registration-code session state.
- Atomically reserve uses before account creation to enforce usage limits under concurrent registration.
- Preserve codes/history on deactivation. **Uninstall deletes both plugin tables.** Back up the database before uninstalling.
- Render administration after processing mutations and assign default expiry dates in PHP for Smarty compatibility.
- Avoid exposing SQL queries in public registration errors.
- Prefix all PHP callbacks and lazily load event handlers.
- Load CSS/JavaScript through Piwigo for subdirectory installations.
- Insert the registration field only once, with a submit-button fallback.
- Support theme overrides at `plugins/piwigo-register-codes/register_codes_register.tpl` inside a theme template directory, plus Piwigo set_extent overrides for the `register_codes_register` handle.
- Keep schema initialization idempotent across install, activation and update.

Existing code values are retained; no schema migration is required. This remains a release candidate pending final review. Authenticated admin form tests and live concurrent registration tests have passed on the local installation. Local automated checks use PHP 8.5 and Piwigo 16.4's Smarty library; broader compatibility has not been established.

## Release review

See [CHANGELOG.md](CHANGELOG.md) for release-candidate notes. CI runs isolated checks on PHP 8.3, 8.4 and 8.5 using the pinned Piwigo 16.4.0 template fixture. CI does not run tests against a real database or establish complete compatibility with those environments.

Create a release archive after approval with `git archive --format=zip --prefix=piwigo-register-codes/ HEAD -o piwigo-register-codes-1.5.1.zip`. Tests and CI files are excluded through `.gitattributes`.

## Development checks

From the repository root:

```sh
python3 tests/admin-test.py
php tests/registration-test.php
PIWIGO_ROOT=/path/to/piwigo php tests/render-test.php
PIWIGO_ROOT=/path/to/piwigo php tests/template-test.php
php tests/maintenance-test.php
PIWIGO_ROOT=/path/to/piwigo python3 tests/translations-test.py
```

The tests use mocked database calls and do not write to a Piwigo database. The rendering test loads the real Smarty library from the specified Piwigo installation.

Before release, test create/copy/delete, batch creation, expiry and usage limits, registration, deactivation/reactivation, and simultaneous registrations against a single-use code in an isolated installation. Usage is reserved with a conditional atomic UPDATE before account creation. Rejected/aborted requests release their reservation at shutdown unless the account already exists. An uncatchable process termination may leave a reservation charged; this conservatively prevents over-redemption. Existing MyISAM tables are supported without migration.

## Languages

The plugin includes complete catalogs for English (`en_UK`), Simplified Chinese (`zh_CN`), Hindi (`hi_IN`), Spanish (`es_ES`), Modern Standard Arabic (`ar_SA`), French (`fr_FR`), Bengali (`bn_IN`), Brazilian Portuguese (`pt_BR`), Indonesian (`id_ID`), and Urdu (`ur_PK`). Selection uses total speakers (native plus second-language speakers) in the 2026 Ethnologue ranking as reproduced at https://en.wikipedia.org/wiki/List_of_languages_by_total_number_of_speakers .

Only plugin language files are supplied. Users select their language through Piwigo; a corresponding core locale must be installed for it to appear in Piwigo's language selector. In particular, the test installation currently lacks Hindi and Urdu core packs. No core language files are installed or modified by this plugin update. Arabic/Urdu direction is scoped to plugin administration. Native-speaker review of the new translations is welcome.

## Credits

- foundation-datepicker: https://github.com/najlepsiwebdesigner/foundation-datepicker
- Bliwi contributed generation/copy buttons, batch generation, visual improvements, translations, and testing.
- Contributions, translations, and feedback are welcome.
