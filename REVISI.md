1. Router.php
  - Router dibuat dinamis untuk semua method (POST, GET, DELETE, PUT)
  - Dibedakan path untuk router api dengan front
    - API : /api/nama_api
    - FRONT: 
      - CMS: /scientia/nama_halaman
      - Web: /nama_halaman
      
2. Controller
  - Dipisahkan berdasarkan controller private dan public
    - Private: Hanya bisa diakses dengan JWT
    - Public: Bisa diakses untuk umum, tanpa perlu JWT
  - Filter variabel dengan GUMP dibuat untuk masing-masing methode (tidak global di __construct)
    
3. Template HTML
  - Untuk semua template dengan ekstensi .html diganti dengan ekstensi .twig
  
4. Plugin
  - DataTables: 
    - Dibuat independen tanpa perlu extend dengan BaseController, sehingga bisa digunakan untuk semua controller (public, private)
    - Menggunakan library Medoo
    - Contructor disiapkan untuk inisiasi object database
    
  
