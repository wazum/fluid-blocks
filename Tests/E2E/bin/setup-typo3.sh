#!/usr/bin/env bash
set -euo pipefail

TYPO3_VERSION="${TYPO3_VERSION:-^14.3}"
INSTANCE_DIR="${E2E_INSTANCE_DIR:-/tmp/fluid-blocks-e2e}"
PORT="${E2E_PORT:-8080}"

E2E_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
EXTENSION_DIR="$(cd "$E2E_DIR/../.." && pwd)"

echo "[setup] TYPO3 ${TYPO3_VERSION} in ${INSTANCE_DIR}"

rm -rf "$INSTANCE_DIR"
mkdir -p "$INSTANCE_DIR"
cd "$INSTANCE_DIR"

cat > composer.json <<JSON
{
    "name": "wazum/fluid-blocks-e2e-instance",
    "type": "project",
    "repositories": [
        { "type": "path", "url": "${EXTENSION_DIR}", "options": { "symlink": true } },
        { "type": "path", "url": "${E2E_DIR}/Fixtures/Extensions/fluid_blocks_e2e", "options": { "symlink": true } }
    ],
    "require": {
        "typo3/cms-backend": "${TYPO3_VERSION}",
        "typo3/cms-core": "${TYPO3_VERSION}",
        "typo3/cms-fluid": "${TYPO3_VERSION}",
        "typo3/cms-frontend": "${TYPO3_VERSION}",
        "typo3/cms-install": "${TYPO3_VERSION}",
        "wazum/fluid-blocks": "@dev",
        "wazum/fluid-blocks-e2e": "@dev"
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "extra": {
        "typo3/cms": {
            "web-dir": "public"
        }
    },
    "config": {
        "allow-plugins": {
            "typo3/class-alias-loader": true,
            "typo3/cms-composer-installers": true
        }
    }
}
JSON

composer install --no-interaction --no-progress

vendor/bin/typo3 setup \
    --driver=sqlite \
    --admin-username=admin \
    --admin-user-password='Password123!' \
    --admin-email=admin@example.com \
    --project-name='fluid-blocks E2E' \
    --create-site="http://127.0.0.1:${PORT}/" \
    --server-type=other \
    --no-interaction
vendor/bin/typo3 extension:setup

php -r '
$file = "config/system/settings.php";
$settings = require $file;
$settings["SYS"]["trustedHostsPattern"] = ".*";
file_put_contents($file, "<?php" . PHP_EOL . "return " . var_export($settings, true) . ";" . PHP_EOL);
'

php -r '
require "vendor/autoload.php";
$file = "config/sites/main/config.yaml";
$site = Symfony\Component\Yaml\Yaml::parseFile($file);
$site["errorHandling"] = [["errorCode" => 404, "errorHandler" => "Page", "errorContentSource" => "t3://page?uid=6"]];
file_put_contents($file, Symfony\Component\Yaml\Yaml::dump($site, 4));
'

DATABASE_PATH="$(php -r '$settings = require "config/system/settings.php"; echo $settings["DB"]["Connections"]["Default"]["path"];')"
sqlite3 "$DATABASE_PATH" < "$E2E_DIR/seed.sql"
vendor/bin/typo3 cache:flush

echo "[setup] done"
