# Live concurrency regression test

This suite creates and deletes temporary codes and accounts through real Piwigo registration/database APIs. Run only against an explicitly authorized test installation. It uses unique `rctest_` prefixes, disables core registration email notifications, and cleans up accounts through Piwigo's delete_user helper in a finally block. Normal audit records may remain. Review active third-party hooks for external side effects before running.

Set REGISTER_CODES_TEST_BOOTSTRAP to an absolute PHP script that initializes Piwigo include/common.inc.php with CLI server variables. The active Register Codes plugin must contain the candidate fix. When running as a deployment user, configure temporary writable Piwigo cache/template paths in the bootstrap; do not loosen live permissions. The bootstrap must not print output.

```sh
REGISTER_CODES_TEST_BOOTSTRAP=/path/to/bootstrap.php python3 tests/integration/run.py
```

Cases: 12 attempts on a single-use code, 12 on a three-use code, 8 on an unlimited code, expired codes, prior validation failure, and late validation failure after reservation. The last two cases also verify the same code can subsequently register successfully. Each attempt has an independent PHP process and DB connection. These tests exercise core register_user and actual plugin hooks; they do not test browser form submission.

The lifecycle/asset-loader test uses disposable tables with a random `rclifecycle_` prefix, then drops only those tables. It also checks actual Piwigo URL generation with a simulated `/photos/` installation prefix:

```sh
REGISTER_CODES_TEST_BOOTSTRAP=/path/to/bootstrap.php php tests/integration/guide-test.php
```
