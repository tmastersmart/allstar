#!/usr/bin/env bash
###############################################################################
# DVSwitch Cockpit Uninstaller
#
# This script removes DVSwitch Cockpit and restores Apache/DVSwitch settings
# that the Cockpit installer may have modified. Use at your own risk this
# Atempts to remove and back up the changed files. Use dry run first.
#
# Created to fix problems or just so you can reinstall.
#
#
# USAGE:
#   sudo ./uninstall_dvswitch_cockpit.sh
#       Performs a full uninstall.
#
#   sudo ./uninstall_dvswitch_cockpit.sh --dry-run
#       Shows what WOULD be removed, but makes NO changes.
#
# DRY RUN MODE:
#   Use --dry-run to safely preview all actions. Nothing is deleted or changed.
#   This is ideal for verifying what will be removed before running the real
#   uninstall.
#
#  License No Copyright
#  https://creativecommons.org/publicdomain/zero/1.0/
#
#


set -euo pipefail

APP_NAME="DVSwitch Cockpit Uninstaller"
DEST_DIR="${DEST_DIR:-/var/www/html/dvswitch_cockpit}"
SUDOERS_FILE="${SUDOERS_FILE:-/etc/sudoers.d/dvswitch-cockpit-services}"
APACHE_CONF_FILE="${APACHE_CONF_FILE:-/etc/apache2/conf-available/dvswitch-cockpit-security.conf}"
APACHE_BACKUP_ROOT="/root/dvswitch-cockpit-backups"
LOG_DIR="/var/log/mmdvm"
BRIDGE_LOG="${LOG_DIR}/MMDVM_Bridge.log"

DRY_RUN=0
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=1
  echo "============================================================"
  echo "$APP_NAME (DRY RUN MODE)"
  echo "============================================================"
else
  echo "============================================================"
  echo "$APP_NAME"
  echo "============================================================"
fi

if [[ $EUID -ne 0 ]]; then
  echo "Please run as root: sudo ./uninstall_dvswitch_cockpit.sh" >&2
  exit 1
fi

run() {
  if [[ $DRY_RUN -eq 1 ]]; then
    echo "[DRY-RUN] $*"
  else
    eval "$@"
  fi
}

echo
echo "[1/6] Removing sudoers rule..."
if [[ -f "$SUDOERS_FILE" ]]; then
  run rm -f "$SUDOERS_FILE"
  echo "Would remove: $SUDOERS_FILE"
else
  echo "No sudoers file found at $SUDOERS_FILE"
fi

echo
echo "[2/6] Disabling and removing Apache Cockpit security config..."
if command -v a2disconf >/dev/null 2>&1; then
  run a2disconf dvswitch-cockpit-security >/dev/null 2>&1 || true
fi

if [[ -f "$APACHE_CONF_FILE" ]]; then
  run rm -f "$APACHE_CONF_FILE"
  echo "Would remove: $APACHE_CONF_FILE"
else
  echo "No Apache Cockpit conf found at $APACHE_CONF_FILE"
fi

echo
echo "[3/6] Restoring Apache CustomLog from Cockpit backups..."
LATEST_BACKUP=""
if [[ -d "$APACHE_BACKUP_ROOT" ]]; then
  LATEST_BACKUP="$(ls -d "$APACHE_BACKUP_ROOT"/apache-accesslog-filter-* 2>/dev/null | sort | tail -n 1 || true)"
fi

if [[ -n "$LATEST_BACKUP" && -d "$LATEST_BACKUP" ]]; then
  echo "Using backup: $LATEST_BACKUP"
  for f in "$LATEST_BACKUP"/*.conf; do
    [[ -f "$f" ]] || continue
    run cp -a "$f" /etc/apache2/sites-available/
    if [[ -d /etc/apache2/sites-enabled ]]; then
      run cp -a "$f" /etc/apache2/sites-enabled/ 2>/dev/null || true
    fi
  done

  if command -v apache2ctl >/dev/null 2>&1; then
    if ! apache2ctl configtest >/dev/null; then
      echo "Warning: apache2ctl configtest failed after restore." >&2
    else
      echo "Apache configtest OK after restore."
    fi
  fi
else
  echo "No Apache access-log backups found under $APACHE_BACKUP_ROOT"
fi

echo
echo "[4/6] Removing DVSwitch Cockpit web directory and backups..."
if [[ -d "$DEST_DIR" ]]; then
  run rm -rf "$DEST_DIR"
  echo "Would remove: $DEST_DIR"
else
  echo "No Cockpit install found at $DEST_DIR"
fi

for b in "${DEST_DIR}".backup.*; do
  [[ -e "$b" ]] || continue
  run rm -rf "$b"
  echo "Would remove backup: $b"
done

echo
echo "[5/6] Cleaning Cockpit cache remnants..."
run rm -rf /var/cache/dvswitch-cockpit 2>/dev/null || true
run rm -rf /tmp/dvswitch_cockpit_* /tmp/dvcockpit_* 2>/dev/null || true

echo
echo "[6/6] Reloading Apache and fixing MMDVM_Bridge log permissions..."
if systemctl list-unit-files apache2.service >/dev/null 2>&1; then
  run systemctl reload apache2 || systemctl restart apache2 || true
else
  echo "apache2.service not found; skipping reload."
fi

if [[ -f "$BRIDGE_LOG" ]]; then
  run chown root:adm "$BRIDGE_LOG" 2>/dev/null || true
  run chmod 0644 "$BRIDGE_LOG" 2>/dev/null || true
  echo "Would adjust permissions on: $BRIDGE_LOG"
else
  echo "MMDVM_Bridge log not found at $BRIDGE_LOG"
fi

echo
if [[ $DRY_RUN -eq 1 ]]; then
  echo "Dry-run complete. No changes were made."
else
  echo "Uninstall complete."
fi
