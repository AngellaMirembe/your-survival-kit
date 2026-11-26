#!/usr/bin/env bash
# One-time LAMP setup for Your Survival Kit (Fedora / Apache / PHP-FPM / MariaDB)
# Run from a terminal:  bash setup-lamp.sh
# It asks for your sudo password once, then does everything else.

set -euo pipefail

SRC="$(cd "$(dirname "$0")" && pwd)"
DEST="/var/www/html/your-survival-kit"
DB="your_survival_kit"
DBUSER="ysk_user"
DBPASS="ysk_local_2026"
CONF="/etc/httpd/conf.d/your-survival-kit.conf"

echo "==> 1/6 Copying site into Apache document root"
sudo rm -rf "$DEST"
sudo cp -r "$SRC" "$DEST"

echo "==> 2/6 Setting permissions and SELinux context"
sudo chown -R apache:apache "$DEST"
sudo find "$DEST" -type d -exec chmod 755 {} \;
sudo find "$DEST" -type f -exec chmod 644 {} \;
sudo chmod -R 775 "$DEST/uploads"
command -v restorecon >/dev/null && sudo restorecon -R "$DEST" || true
sudo chcon -R -t httpd_sys_rw_content_t "$DEST/uploads" 2>/dev/null || true

echo "==> 3/6 Enabling .htaccess overrides for this site (pretty URLs)"
sudo tee "$CONF" >/dev/null <<EOF
<Directory "$DEST">
    AllowOverride All
    Require all granted
</Directory>
EOF

echo "==> 4/6 Importing database"
if sudo mysql -Nse "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$DB'" | grep -qx "$DB"; then
    echo "    Database already exists — keeping existing data."
else
    sudo mysql < "$SRC/database/your_survival_kit.sql"
fi

echo "==> 5/6 Creating PHP database user and updating config.php"
sudo mysql <<SQL
CREATE USER IF NOT EXISTS '${DBUSER}'@'localhost' IDENTIFIED BY '${DBPASS}';
GRANT ALL PRIVILEGES ON ${DB}.* TO '${DBUSER}'@'localhost';
FLUSH PRIVILEGES;
SQL
sudo sed -i \
    -e "s/define('DB_USER', 'root');/define('DB_USER', '${DBUSER}');/" \
    -e "s/define('DB_PASS', '');/define('DB_PASS', '${DBPASS}');/" \
    "$DEST/includes/config.php"

echo "==> 6/6 Restarting services"
sudo systemctl restart httpd mariadb php-fpm

echo
echo "Done! Open:  http://localhost/your-survival-kit/"
echo "Admin:       http://localhost/your-survival-kit/admin/login.php"
echo "Login:       admin@your-survival-kit.org / ChangeMe123!"
echo "DB user:     ${DBUSER} / ${DBPASS}  (already written into config.php)"
echo
echo "Remember to change the admin password after your first login."
