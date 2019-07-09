### 1. Router
  - [x] Router dibuat dinamis untuk semua method (POST, GET, DELETE, PUT)
    - [x] GET : /{id} - Get record by id (PrimaryKey)
    - [x] PUT : /{id} - Update record by id (PrimaryKey)
    - [x] POST
      - [x] Create : /create - Create new record, return new id [id => {new_id}]
      - [x] Read   : /read - Read records with or without filter
    - [x] DELETE
      - [x] Single : /{id} - Delete record by id (PrimaryKey)
      - [x] Batch  : /batch - Delete multiple records
  - [x] Dibedakan path untuk router api dengan front
    - [x] REST API : /api/nama_api
    - [x] FRONT:
      - [x] CMS: /scientia/nama_halaman

### 2. Controller
  - [x] Dipisahkan berdasarkan controller private dan public
    - [x] Private: Hanya bisa diakses dengan JWT
    - [x] Public: Bisa diakses untuk umum, tanpa perlu JWT
  - [x] Filter variabel dengan GUMP dibuat untuk masing-masing methode (tidak global di __construct)

### 3. Template
  - [x] Untuk semua template dengan ekstensi .html diganti dengan ekstensi .twig

### 4. Plugin
  - [x] DataTables:
    - [x] Dibuat independen tanpa perlu extend dengan BaseController, sehingga bisa digunakan untuk semua controller (public, private)
    - [x] Menggunakan library Medoo
    - [x] Contructor disiapkan untuk inisiasi object database

### 5. ERROR CODE
  - [ ] Format error log :
    ```php
        $errMsg = [
            'code' => 'ErrCode',
            'error' => 'Error Definition',
            'message' => 'Error Message',
        ];
    ```
  - [ ] Definisikan error code:
    - [ ] Buat definer untuk list erro code
    - [ ] SC400 : AUTOLOAD REGISTER CANNOT LOAD SPECIFIED CLASS
    - [ ] SC401 : REQUESTED URL NOT FOUND IN ROUTER
    - [ ] SC500 : INTERNAL SERVER ERROR HANDLER
    - [ ] SC501 : CACHE ERROR
    - [ ] SC502 : REST-API ROUTER ERROR
    - [ ] SC503 : HTTP METHOD NOT ALLOWED HANDLER
