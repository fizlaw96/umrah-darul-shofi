#!/usr/bin/env bash

set -Eeuo pipefail

project_path="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$project_path"

if ! php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then
    echo "PHP 8.3 atau lebih baharu diperlukan. Tukar versi PHP melalui Cloudways terlebih dahulu." >&2
    exit 1
fi

if [ -s "$HOME/.nvm/nvm.sh" ]; then
    # shellcheck disable=SC1091
    . "$HOME/.nvm/nvm.sh"
fi

if ! command -v nvm >/dev/null 2>&1; then
    echo "NVM tidak ditemui. Pasang atau aktifkan NVM, kemudian jalankan semula skrip ini." >&2
    exit 1
fi

nvm install 22
nvm use 22

branch="$(git branch --show-current)"

if [ -z "$branch" ]; then
    echo "Git berada dalam detached HEAD. Tukar ke branch deployment sebelum meneruskan." >&2
    exit 1
fi

git fetch origin "$branch"
git pull --ff-only origin "$branch"

php artisan down --render=errors.503 --retry=60 --refresh=60 --no-interaction

maintenance_mode_active=true
deployment_completed=false

finish_deployment() {
    if [ "$maintenance_mode_active" != true ]; then
        return
    fi

    if [ "$deployment_completed" = true ]; then
        php artisan up --no-interaction
        return
    fi

    echo "Deployment gagal. Website kekal dalam maintenance mode supaya pengunjung tidak melihat versi separa siap." >&2
    echo "Selepas isu diperbaiki, jalankan: php artisan up --no-interaction" >&2
}

trap finish_deployment EXIT

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci --include=dev
npm run build

php artisan migrate --force --no-interaction
php artisan optimize:clear --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

deployment_completed=true
