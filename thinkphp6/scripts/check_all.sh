#!/usr/bin/env bash
set -euo pipefail

php scripts/route_check.php
php scripts/schema_check.php
for f in $(rg --files . -g '*.php'); do
  php -l "$f" >/dev/null
done

echo "all checks passed"
