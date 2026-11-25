#!/bin/sh
# headersHelper for the workation-prod MCP server in .mcp.json.
#
# Reads the WordPress application password from 1Password and prints the
# Authorization header as JSON. Claude Code runs it on each connect, so the
# password is never stored in a file or an environment variable. Needs the
# 1Password CLI (`op`) with desktop-app integration; the first call may ask
# for Touch ID.
#
# Rotate: make a new application password in wp-admin (Users → Profile →
# Application Passwords) and replace the password field of the item below.
set -eu

ITEM='op://prod-bergbytes/workation-prod-mcp'
user=$(op read "$ITEM/username")
pass=$(op read "$ITEM/password")
token=$(printf '%s:%s' "$user" "$pass" | base64 | tr -d '\n')
printf '{"Authorization":"Basic %s"}\n' "$token"
