# Changelog

## 1.5.1 — release candidate

### Security and correctness

- Require session-bound CSRF tokens for administrative mutations.
- Escape stored output in HTML and JavaScript contexts; avoid interpolating codes into JavaScript.
- Validate administrative fields, enforce the batch-size limit, and generate unpredictable codes.
- Escape SQL values using the active database connection.
- Reserve code usage atomically before account creation, enforcing limits under concurrent registration. Release failed reservations unless an account was created.
- Remove SQL details from registration errors and discard legacy session state.

### Compatibility and administration

- Preserve codes/history on deactivation. Explicit uninstall removes both plugin tables.
- Initialize tables idempotently through install, activation and update; no schema migration is needed.
- Prefix callbacks and load event handlers through Piwigo's lazy loader.
- Use Piwigo's asset loaders for installations below the domain root.
- Insert the registration field once and support theme template overrides.
- Stack entry forms on narrower screens and contain scrolling within data tables.
- Provide complete plugin catalogs for ten languages, context-safe translated strings, and plugin-scoped Arabic/Urdu direction.

### Validation and release notes

- Tested on a local Piwigo 16.4.0/PHP 8.5 installation, including authenticated administrative submissions and concurrent real registrations.
- Add isolated regression, template, lifecycle and translation tests, plus a PHP 8.3–8.5 CI matrix using Piwigo 16.4.0 templates. Broader live compatibility is not yet established.
- No core Piwigo files or core language packs are included. Hindi/Urdu need corresponding core locale packs to appear in Piwigo's language selector.
- A forcibly terminated PHP process can leave a reserved use charged; normal failed registrations release reservations. This favors preventing over-redemption.
- Exclude test and CI files from `git archive` release packages. Review new translations with native speakers when available.
