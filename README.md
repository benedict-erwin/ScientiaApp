# ScientiaApp

## Tentang
**ScientiaApp** merupakan sebuah skeleton / kerangka dasar untuk mengawali sebuah proyek baru. Core dari Skeleton ini dibangun menggunakan [Slim PHP micro framework](https://www.slimframework.com/), database framework menggunakan [Medoo](https://medoo.in/), dan templating engine menggunakan [Twig](https://twig.symfony.com/).

Dibangun dengan tujuan untuk mempermudah para developer dalam memulai proyeknya, tanpa perlu lagi memikirkan hal-hal mendasar karena dalam skeleton ini sudah dilengkapi dengan fitur dasar seperti user menejemen, pengaturan role/level user, hak akses, menu, auditlog/log aktifitas user. Ditambah lagi dengan tersedianya CRUD generator akan mengurangi developer melakukan rutinitas copy-paste berulang-ulang :p

Menggunakan pendekatan pemisahan antara data (BackEnd) dan tampilan (FrontEnd), **ScientiaApp** diharapkan dapat mempercepat proses development karena lebih mempermudah untuk pembagian pengerjaan dalam tim.
BackEnd menggunakan model RESTful Web API dengan JWT sebagai otorisasinya sehingga memudahkan integrasi dengan platform lain (mobile/desktop).
FrontEnd menggunakan jquery dan bootstrap yang sudah familiar dikalangan developer.

## Fitur
* Pengaturan Role / User group
* Pengaturan User
* Pengaturan Group menu
* Pengaturan Menu
* Pengaturan Hak akses
* CRUD Generator
* Auditlog / Log aktivitas user
* Pencarian
* RESTful API (GET, POST, PUT, DELETE)
* Data Cache

## Kebutuhan Sistem
* PHP versi 7.0 atau lebih
* Database Mysql/MariaDB
* Redis Server (opsional)
* Composer (opsional)

## Instalasi
* Download atau clone repositori ini pada webserver Anda
```
git clone git@github.com:benedict-erwin/ScientiaApp.git
```
* Buka browser dan arahkan ke http://ip-server-anda/lokasi-folder-scientiaapp/public/install/
* Ikuti panduan pada wizard instalasi
* Untuk panduan lengkap, lihat wiki [instalasi](https://github.com/benedict-erwin/ScientiaApp/wiki/Instalasi)