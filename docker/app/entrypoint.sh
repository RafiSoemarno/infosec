#!/usr/bin/env bash
set -euo pipefail

# Simple signal handler to stop tailscaled gracefully
shutdown() {
  echo "Entrypoint: received shutdown signal, stopping services..."
  # Attempt to down tailscale (best-effort)
  if command -v tailscale >/dev/null 2>&1; then
    if tailscale status >/dev/null 2>&1; then
      echo "Entrypoint: running 'tailscale down'..."
      tailscale down || true
    fi
  fi
  # Stop tailscaled process if running
  if [ -n "${TAILSCALED_PID:-}" ]; then
    echo "Entrypoint: killing tailscaled (pid ${TAILSCALED_PID})..."
    kill "${TAILSCALED_PID}" || true
    wait "${TAILSCALED_PID}" 2>/dev/null || true
  fi

  # Stop sshd if present
  service ssh stop >/dev/null 2>&1 || true

  exit 0
}

trap 'shutdown' SIGTERM SIGINT

# Start sshd (optional)
if [ -f /usr/sbin/sshd ]; then
  echo "Entrypoint: starting sshd..."
  service ssh start >/dev/null 2>&1 || true
fi

# Ensure tailscale state dir exists and has correct perms
mkdir -p /var/lib/tailscale
chown -R root:root /var/lib/tailscale || true

# Start tailscaled in userspace-networking (no NET_ADMIN capability required)
echo "Entrypoint: starting tailscaled (userspace-networking)..."
tailscaled --state=/var/lib/tailscale/tailscaled.state --socket=/var/run/tailscale/tailscaled.sock --tun=userspace-networking >/var/log/tailscaled.log 2>&1 &
TAILSCALED_PID=$!

# Wait a short moment for tailscaled to initialize
sleep 1

# If TS_AUTHKEY provided, attempt automatic 'tailscale up' with retries
if [ -n "${TS_AUTHKEY:-}" ]; then
  echo "Entrypoint: TS_AUTHKEY provided, attempting 'tailscale up'..."
  MAX_RETRIES=6
  RETRY_SLEEP=2
  i=0
  until tailscale up --authkey="${TS_AUTHKEY}" --hostname="${TS_HOSTNAME:-laravel-dev}" --ssh --accept-dns=true --accept-routes="${TS_ACCEPT_ROUTES:-false}" >/dev/null 2>&1; do
    i=$((i+1))
    echo "Entrypoint: tailscale up failed (attempt ${i}/${MAX_RETRIES}), retrying in ${RETRY_SLEEP}s..."
    sleep "${RETRY_SLEEP}"
    if [ "${i}" -ge "${MAX_RETRIES}" ]; then
      echo "Entrypoint: tailscale up failed after ${MAX_RETRIES} attempts. Continuing without bringing up tailscale."
      break
    fi
  done
else
  echo "Entrypoint: TS_AUTHKEY not set. tailscaled started but not 'up'. Run 'tailscale up' manually inside the container or set TS_AUTHKEY."
fi

# Print tailscale status to logs for easier debugging
if command -v tailscale >/dev/null 2>&1; then
  echo "Entrypoint: tailscale status:"
  tailscale status || true
  echo "Entrypoint: tailscale ip addresses:"
  tailscale ip -4 || true
fi

# Ensure Laravel storage dirs exist and permissions are OK
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Hand off to php-fpm as the foreground process (so container keeps running)
echo "Entrypoint: starting php-fpm (foreground)..."
exec php-fpm -F