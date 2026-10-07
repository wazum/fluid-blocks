#!/usr/bin/env bash
set -euo pipefail

INSTANCE_DIR="${E2E_INSTANCE_DIR:-/tmp/fluid-blocks-e2e}"
PORT="${E2E_PORT:-8080}"
FPM_PORT="${E2E_FPM_PORT:-9081}"
PHP_FPM="${PHP_FPM:-php-fpm}"
SERVER_DIR="$INSTANCE_DIR/.server"

stop() {
    for pid_file in "$SERVER_DIR/nginx.pid" "$SERVER_DIR/php-fpm.pid"; do
        if [ -f "$pid_file" ]; then
            kill "$(cat "$pid_file")" 2>/dev/null || true
            rm -f "$pid_file"
        fi
    done
}

if [ "${1:-start}" = "stop" ]; then
    stop
    exit 0
fi

stop
mkdir -p "$SERVER_DIR/temp"

cat > "$SERVER_DIR/php-fpm.conf" <<CONF
[global]
pid = $SERVER_DIR/php-fpm.pid
error_log = $SERVER_DIR/php-fpm.log
daemonize = yes

[e2e]
listen = 127.0.0.1:$FPM_PORT
pm = static
pm.max_children = 4
clear_env = no
catch_workers_output = yes
CONF

cat > "$SERVER_DIR/nginx.conf" <<CONF
pid $SERVER_DIR/nginx.pid;
error_log $SERVER_DIR/nginx-error.log warn;

events {
    worker_connections 64;
}

http {
    include /etc/nginx/mime.types;
    access_log $SERVER_DIR/nginx-access.log;
    client_body_temp_path $SERVER_DIR/temp/body;
    fastcgi_temp_path $SERVER_DIR/temp/fastcgi;
    proxy_temp_path $SERVER_DIR/temp/proxy;
    uwsgi_temp_path $SERVER_DIR/temp/uwsgi;
    scgi_temp_path $SERVER_DIR/temp/scgi;

    server {
        listen 127.0.0.1:$PORT;
        root $INSTANCE_DIR/public;
        index index.php;

        location / {
            try_files \$uri \$uri/ /index.php\$is_args\$args;
        }

        location ~ \.php\$ {
            try_files \$uri =404;
            include /etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            fastcgi_pass 127.0.0.1:$FPM_PORT;
        }
    }
}
CONF

"$PHP_FPM" --fpm-config "$SERVER_DIR/php-fpm.conf"
nginx -e "$SERVER_DIR/nginx-error.log" -c "$SERVER_DIR/nginx.conf"

for _ in $(seq 1 30); do
    if curl -fs -o /dev/null "http://127.0.0.1:$PORT/"; then
        echo "[serve] http://127.0.0.1:$PORT/ is up"
        exit 0
    fi
    sleep 1
done

echo "[serve] the page did not come up"
tail -n 30 "$SERVER_DIR/nginx-error.log" "$SERVER_DIR/php-fpm.log" || true
curl -s "http://127.0.0.1:$PORT/" | head -n 40 || true
exit 1
