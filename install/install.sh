#!/bin/bash
# Program ini dapat digunakan untuk personal ataupun komersial.
# Banner Function
print_banner () {
    clear
    echo "====================================================================";
    echo " ScientiaAPP - Web Application Skeleton (Based on Slim)             ";
    echo " Developer : Benedict E. Pranata                                    ";
    echo " Email : benedict.erwin@gmail.com                                   ";
    echo " Version 1.0 - 27/07/2018                                           ";
    echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-";
    echo
    echo
}

print_banner

# Must be root
if [[ $EUID > 0 ]]; then # we can compare directly with this syntax.
  echo "Silahkan jalankan dengan hak akses root/sudo";
  exit 1
fi

echo "Mengecek kebutuhan sistem...";

# Dependencies Variable
php_v=$(php --version | head -n 1 | cut -d " " -f 2 | cut -c 1,3);
composer_loc=$(which composer);
redis_loc=$(which redis-cli);

# Check PHP Version
echo "Mengecek Versi PHP (minimal versi 7.0)...";
sleep 1
if [ $php_v -lt 70 ]; then
    echo "[x] - Versi PHP tidak didukung, versi minimal: PHP 7.0";
    exit 1
fi

echo "Mengecek apakah Redis terinstall...";
sleep 1
if [[ -z $redis_loc ]]; then
    echo "[x] - Silahkan install redis terlebih dahulu";
    exit 1
fi
echo "[v] - Redis terinstall";
sleep 1

# Check composer and install if not exists
echo "[v] - Versi PHP : $php_v - didukung";
echo "Mengecek apakah Composer terinstall...";
sleep 1
if [[ -z $composer_loc ]]; then
    echo "[!] - Composer tidak ditemukan!";
    echo "Menginstal Composer...";
    sleep 1
    c_hash=$(php -r "readfile('https://composer.github.io/installer.sig');");
    php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
    php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === '$c_hash') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

    if [ -f composer-setup.php ]; then
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        mv composer.phar /usr/local/bin/composer
    fi
fi

echo "[v] - Composer terinstall";
sleep 1

echo "Menjalankan composer update...";
sleep 1

# Update dependencies
composer update

sleep 1
print_banner

# Database variable
echo "[Setup Koneksi Database] - Silahkan ikuti langkah-langkah berikut";
read -p "Masukkan web base url (dengan akhiran '/') : " site_root;
read -p "Masukkan database hostname : " db_hostname;
read -p "Masukkan mysql root user : " my_root;
read -p "Masukkan mysql root password : " my_pass;
read -p "Masukkan nama database yang akan dibuat : " database;
read -p "Masukkan username untuk database yang akan dibuat : " db_username;
read -p "Masukkan password untuk database yang akan dibuat : " db_password;

sleep 1
print_banner

# Web application variable
echo "[Setup Super User] - Silahkan ikuti langkah-langkah berikut";
read -p "Masukkan nama lengkap super user untuk aplikasi web : " app_nama;
read -p "Masukkan email super user untuk aplikasi web : " app_mail;
read -p "Masukkan telepon super user untuk aplikasi web : " app_telp;
read -p "Masukkan username super user untuk aplikasi web : " app_user;
read -p "Masukkan password super user untuk aplikasi web : " app_pass;

# Execute PHP Installer Script
sleep 1
print_banner
echo "[Setup Data] - Konfigurasi database...";
php install/install.php "$db_hostname" "$my_root" "$my_pass" "$database" "$db_username" "$db_password" "$app_nama" "$app_mail" "$app_telp" "$app_user" "$app_pass" "$site_root"

# Permission
sleep 1
print_banner
echo "[Setup Up Permission] - Untuk direktori Log dan Cache...";
chmod 775 -Rf ../ScientiaAPP
mkdir -p src/ScientiaAPP/App/Log
mkdir -p src/ScientiaAPP/App/Cache
chmod 777 src/ScientiaAPP/App/Log
chmod 777 src/ScientiaAPP/App/Cache

# Self remove
sleep 1
echo "Menghapus file installer...";
sleep 1
echo "Instalasi selesai.";
rm -rf "install"
rm -rf $0
