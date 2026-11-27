# Leanpay payment plugin

The Magento 2 module `Leanpay_Payment` (Composer package `leanpay/payment`) that adds Leanpay installment payments, the installment badge and Leanpay promotions to a merchant's shop. The repository is the module only: no shop, no containers, no tests. It is developed through the submodule of the [website repository](https://github.com/magebitcom/leanpay-magento-website), see [local setup](docs/operations/local-setup.md).

## Branches and pull requests

Read from the merged pull requests and the commit log of this repository.

- `master` holds the released code and `staging` is what the Luma test shop runs. The Hyva test shop runs `master`.
- Cut a feature branch `feature/LMM-<n>` from `master`, and open the pull request into `staging`.
- A feature branch that conflicts with `staging` stays clean: cut `feature/LMM-<n>-PR_STAGING` from `staging`, merge the feature branch into it, and open the pull request from there.
- A release is a branch `release/<dd.mm.yyyy>` from `master` that collects the approved feature branches, with a pull request into `master`. A feature branch has also gone into `master` directly with its own version bump.
- Release steps: [runbook](docs/operations/runbook.md), Release the plugin. They are the version bump in `composer.json`, the release notes in `docs/releases/<version>.md` plus a summary at the top of `docs/releases/README.md`, the tag (no `v` prefix), the GitHub release and the Packagist check.
- Commit subjects start with the ticket key: `LMM-<n> <what changed>`. A docs-only commit is `docs(LMM-<n>): <what changed>`.

## Rules

- Keep checkout, the product-page badge and the listing badges on one promotion resolution; the badge must never advertise terms checkout does not apply ([promotions](docs/functionality/promotions.md)).
- Listing and widget code must not run a query per product card: promotion data is preloaded once per page.
- The notification route has no form key check; keep its signature check intact ([Paying with Leanpay](docs/functionality/paying-with-leanpay.md)).
- What the Hyva add-on reads (settings, promotion and plan lookups, the listing preload) changes only compatibly, or the add-on is released together with it ([system map](docs/architecture/README.md)).
- Never put an API key, secret word or sandbox login in the code, a doc or a ticket comment: they live in the shop's admin configuration.
- Leave the files in `docs/releases/` as they are, except to add the notes of a new release.

## Checks

No hook manager and no test suite exist. The docs check runs on pull requests into `staging` and `master`, and by hand: `docs/.health-checker/run`. What to test by hand is in [testing](docs/testing/README.md).

## Documentation

Docs under `docs/` must stay up to date with the code, and this is enforced: the
PR gate fails when changed code is covered by a doc that
was not updated with it (`doc.untouched`), when a covered file changed after
the doc was last checked (`review.stale`), or when a module belongs to no doc
(`covers.unclaimed`). Update the docs on the same branch as the code.

Every change to `docs/`, a one-line fix included, goes through the
`write-project-docs` skill (magebit-skills), which picks its mode from the
request, or, without the skill, through `docs/MAINTAINING.md`. Do either
once the code change is done, before you commit; until then, leave `docs/`
alone. Never edit `docs/` any other way, and add `Docs-Reviewed` lines only
once the doc has been checked against the change.
Check with `docs/.health-checker/run`.

Where a fact goes, before you save it anywhere:

| A fact that is... | Goes to |
|---|---|
| A business rule, or what the client can change | The area doc under `docs/functionality/` |
| What an outside system sends, receives or owns | Its doc under `docs/integrations/` |
| How the module is built, what it stores, how the Hyva add-on depends on it | `docs/architecture/README.md` |
| A fact about a test shop | The website repository's docs (the test shops live there) |
| A development setup trap | `docs/operations/local-setup.md` |
| A procedure, a release step | `docs/operations/runbook.md` |
| A symptom, its cause and its fix | `docs/operations/troubleshooting.md` |
| How a change is tested, and which suite to run | `docs/testing/README.md` |
| A fact true for every Magebit project | The company skill that owns it, or the Magebit Brain |
| A personal preference | Your own memory |
| The state of a task | The ticket |
