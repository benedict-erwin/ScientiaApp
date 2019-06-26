### 1. Router
  - [ ] Router dibuat dinamis untuk semua method (POST, GET, DELETE, PUT)
    - [ ] GET : /{id} - Get record by id (PrimaryKey)
    - [ ] PUT : /{id} - Update record by id (PrimaryKey)
    - [ ] POST
      - [ ] Create : /create - Create new record, return new id [id => {new_id}]
      - [ ] Read   : /read - Read records with or without filter
    - [ ] DELETE
      - [ ] Single : /{id} - Delete record by id (PrimaryKey)
      - [ ] Batch  : /batch - Delete multiple records
  - [ ] Dibedakan path untuk router api dengan front
    - [ ] REST API : /api/nama_api
    - [ ] FRONT:
      - [x] CMS: /scientia/nama_halaman
      - [ ] Web: /nama_halaman

### 2. Controller
  - [x] Dipisahkan berdasarkan controller private dan public
    - [x] Private: Hanya bisa diakses dengan JWT
    - [x] Public: Bisa diakses untuk umum, tanpa perlu JWT
  - [ ] Filter variabel dengan GUMP dibuat untuk masing-masing methode (tidak global di __construct)

### 3. Template
  - [ ] Untuk semua template dengan ekstensi .html diganti dengan ekstensi .twig

### 4. Plugin
  - [ ] DataTables:
    - [ ] Dibuat independen tanpa perlu extend dengan BaseController, sehingga bisa digunakan untuk semua controller (public, private)
    - [ ] Menggunakan library Medoo
    - [ ] Contructor disiapkan untuk inisiasi object database

### 5. ERROR CODE
  - [ ] Definisikan error code:
    - [ ] Buat definer untuk list erro code
