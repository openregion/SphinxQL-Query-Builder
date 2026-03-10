SphinxQL Query Builder Unit Tests
=================================

##### How to run

There's a sphinx.conf file in this directory. It uses a single RT index. Check the necessary directories, I ran Sphinx in `/usr/local/sphinx`

The udf must be compiled: `gcc -shared -o data/test_udf.so test_udf.c`

The test should then just work: `phpunit -c phpunit.xml`

Make sure there's a `data` directory under the `tests` directory.

##### Docker-only local run (no host PHP/Composer install)

This repository includes a Docker-based runner that executes the Manticore matrix used in CI (`mysqli` and `pdo` test configs).

From the repository root:

```bash
./scripts/run-tests-docker.sh
```

What this command does:

1. Builds `Dockerfile.test` (PHP CLI + required extensions + Composer + gcc/wget).
2. Runs `tests/install.sh` with `SEARCH_BUILD=MANTICORE`.
3. Starts searchd with `tests/run.sh` (and compiles test UDF).
4. Executes:
   - `vendor/bin/phpunit --configuration tests/travis/mysqli.phpunit.xml`
   - `vendor/bin/phpunit --configuration tests/travis/pdo.phpunit.xml`

Notes:

- The script copies the repository into a temporary in-container workspace before running tests, so your local working tree is not modified by test artifacts.
- If Docker image build fails due to a broken local credential helper, the script retries build with an isolated anonymous Docker config for public image pulls.
