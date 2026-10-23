# Releasing

Composer gets versions from git tags through [Packagist](https://packagist.org/packages/claviska/simpleimage). There's no version field in `composer.json`, so don't add one.

1. Make sure `master` is up to date and clean:

   ```sh
   git checkout master && git pull
   ```

2. Choose the next [semver](https://semver.org/) number. Tags have no `v` prefix (e.g. `4.4.0`, not `v4.4.0`).

3. Create the GitHub release. This also creates and pushes the tag:

   ```sh
   gh release create 4.4.1 --target master --generate-notes
   ```

4. Packagist should pick up the tag automatically via the GitHub webhook. If the new version doesn't appear on [Packagist](https://packagist.org/packages/claviska/simpleimage) after a few minutes, click **Update** there.
