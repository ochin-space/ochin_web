echo "setup hello messages"
#echo 'echo -e "\e[1;31m   _   _      __    _\n  (_)_(_)____/ /_  (_)___\n / __ \/ ___/ __ \/ / __ \ \n/ /_/ / /__/ / / / / / / /\n\____/\___/_/ /_/_/_/ /_/ \n\e[0m"' | sudo tee -a /etc/issue
echo 'echo -e "\e[1;31m   _   _      __    _\n  (_)_(_)____/ /_  (_)___\n / __ \/ ___/ __ \/ / __ \ \n/ /_/ / /__/ / / / / / / /\n\____/\___/_/ /_/_/_/ /_/ \n\e[0m"' | sudo tee -a /home/${USER}/.bashrc
echo "change the hostname to ochin"
echo ochin | sudo tee /etc/hostname
echo "install Apache"
sudo apt update
sudo apt upgrade -y
sudo apt install apache2 -y

#buster
echo "Setting up PHP7.4 libs and extensions for Apache"
#curl https://packages.sury.org/php/apt.gpg | sudo tee /usr/share/keyrings/suryphp-archive-keyring.gpg >/dev/null
#echo "deb [signed-by=/usr/share/keyrings/suryphp-archive-keyring.gpg] https://packages.sury.org/php/ $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
#sudo apt update -y
#sudo apt-get install php7.4 libapache2-mod-php7.4 php7.4-mbstring php7.4-mysql php7.4-curl php7.4-gd php7.4-zip -y
#bullseye
sudo apt install php7.4 php7.4-zip php7.4-xml php7.4-sqlite3 -y
echo "increase upload size"
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' /etc/php/7.4/apache2/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 100M/g' /etc/php/7.4/apache2/php.ini
sudo sed -i 's/max_input_time = 60/max_input_time = 180/g' /etc/php/7.4/apache2/php.ini
echo "enable php extensions: sqlite3 and pdo_sqlite"
sudo sed -i 's/;extension=sqlite3/extension=sqlite3/g' /etc/php/7.4/apache2/php.ini
sudo sed -i 's/;extension=pdo_sqlite/extension=pdo_sqlite/g' /etc/php/7.4/apache2/php.ini

echo "rewrite engine on and point to /ochin"
sudo sed -i '/^\tDocumentRoot.*/a \\n\tRewriteEngine on \n\t\tRewriteCond %{REQUEST_URI} ^\\\/$ \n\t\tRewriteRule (.*) \/ochin_web\/ [R=301]' /etc/apache2/sites-enabled/000-default.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
sudo mv  ../ochin_web /var/www/html
#www-data own the folder
sudo chown -R www-data:www-data /var/www/html/ochin_web
#secure the whitelists
sudo chown -R root:root /var/www/html/ochin_web/backgroundWorker
sudo chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/files2remove
sudo chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/files2update
sudo chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/modules
sudo chown www-data:www-data /var/www/html/ochin_web/backgroundWorker/exchange/services
sudo cp favicon.ico ../
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
sudo systemctl enable background_worker.service 
sudo systemctl start background_worker.service 