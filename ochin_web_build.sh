echo "setup hello messages"
echo 'echo -e "\e[1;31m\n   _   _      __    _\n  (_)_(_)____/ /_  (_)___\n / __ \/ ___/ __ \/ / __ \ \n/ /_/ / /__/ / / / / / / /\n\____/\___/_/ /_/_/_/ /_/ \n\e[0m"' | sudo tee -a /etc/bash.bashrc

echo "change the hostname to ochin"
echo ochin | tee /etc/hostname
echo "127.0.0.1       localhost" | tee  /etc/hosts
echo "::1             localhost ip6-localhost ip6-loopback" | tee -a /etc/hosts
echo "ff02::1         ip6-allnodes" | tee -a /etc/hosts
echo "ff02::2         ip6-allrouters" | tee -a /etc/hosts
echo "127.0.1.1       ochin" | tee -a /etc/hosts

#buster
echo "increase upload size"
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' /etc/php/7.4/apache2/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/g' /etc/php/7.4/apache2/php.ini
sed -i 's/max_input_time = 60/max_input_time = 180/g' /etc/php/7.4/apache2/php.ini
echo "enable php extensions: sqlite3 and pdo_sqlite"
sed -i 's/;extension=sqlite3/extension=sqlite3/g' /etc/php/7.4/apache2/php.ini
sed -i 's/;extension=pdo_sqlite/extension=pdo_sqlite/g' /etc/php/7.4/apache2/php.ini

#move ochin_web to the www folder
mv  ../ochin_web /var/www/html
cp favicon.ico /var/www/html
#www-data owns the folder
chown -R www-data:www-data /var/www/html/ochin_web
echo "secure the backgroundworker"
chown -R root:root /var/www/html/ochin_web/backgroundWorker
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/logs
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/logs
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/files2append
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/files2append
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/files2remove
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/files2remove
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/files2update
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/files2update
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/modules
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/modules
mkdir /var/www/html/ochin_web/backgroundWorker/exchange/services
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/services
chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/services_whitelist.txt
#give read permission for wpa_supplicant.conf  to all
chmod 604 /etc/wpa_supplicant/wpa_supplicant.conf	

#redirect to ochin_web
echo "rewrite engine on and point to /ochin"
sed -i '/^\tDocumentRoot.*/a \\n\tRewriteEngine on \n\t\tRewriteCond %{REQUEST_URI} ^\\\/$ \n\t\tRewriteRule (.*) \/ochin_web\/ [R=301]' /etc/apache2/sites-enabled/000-default.conf
a2enmod rewrite

#setup the background service to run at boot and log to file
echo "create background_worker.service"
servicefile="/lib/systemd/system/background_worker.service"
logLevel="INFO"
rootpath="/var/www/html/ochin_web/"
echo "[Unit]">$servicefile
echo "Description=background_worker">>$servicefile
echo "After=multi-user.target">>$servicefile
echo "">>$servicefile
echo "[Service]">>$servicefile
echo "ExecStart=sudo python "$rootpath"backgroundWorker/main.py -source "$rootpath"backgroundWorker/exchange/ -logging "$logLevel" -logsPath "$rootpath"backgroundWorker/exchange/logs/">>$servicefile
echo "Restart=always">>$servicefile
echo "">>$servicefile
echo "[Install]">>$servicefile
echo "WantedBy=multi-user.target">>$servicefile
systemctl enable background_worker.service 
systemctl start background_worker.service