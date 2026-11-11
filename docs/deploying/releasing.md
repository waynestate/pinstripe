---
title: Releasing
description: How Pinstripe is released.
---

# Releasing

This page sets out the steps to release Pinstripe, from the CHANGELOG to the npm publish.

1. Move the lines under `## Unreleased` in `CHANGELOG.md` to a new `## X.Y.Z (YYYY-MM-DD)` heading.
2. Set the same version in `package.json`, in the same release commit: `tests/PackageVersionTest.php` fails otherwise. In the same commit, rewrite the front page's "Just off the cutting table" note (`site/index.html`, then `composer site:agent-ready`) to lead with the new version and what it brought: `tests/FrontPageTest.php` fails while it names an older one. And rename the `## Unreleased` draft in `docs/upgrading.md` to the same `## X.Y.Z (YYYY-MM-DD)` heading (write the section if there is no draft: what changed for an app, the steps, how to verify): `tests/UpgradingTest.php` fails while a release has no section there.
3. `composer ci` and `npm test` green locally, then both pipelines green for that commit.
4. Run a secrets scan of the history and check that nothing internal is in the tree.
5. Tag `X.Y.Z` (no `v`) on git.wayne.edu. The push mirror carries the tag to GitHub, and Packagist picks it up from there.
6. **npm is published by hand,** after the tag, from a clean checkout of the tagged commit, by an owner of the `@waynestate` npm scope with two-factor auth:

   ```
   npm ci --ignore-scripts
   npm test
   npm pack --dry-run
   npm publish --access public
   ```

   `npm pack --dry-run` should list only `eslint/`, `prettier/`, `LICENSE`, `README.md` and `package.json`. Publishing cannot be undone, so check the version first with `npm view @waynestate/pinstripe version`.
7. A release needs a maintainer's approval. Never move or delete a published tag.

> [!WARNING]
> A release needs a maintainer's approval. Never move or delete a published tag.

## Related

- [Changelog](../changelog.md)
- [Best practices](../reference/best-practices.md)
- [Tested with and tool floors](../reference/tested-with.md)
