#!/usr/bin/php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'src/ScientiaAPP/App/Lib/Encrypter.php';

// DB parameters
$file = 'install/database.sql';
$db_hostname = $argv[1];
$my_root = $argv[2];
$my_pass = $argv[3];
$database = $argv[4];
$db_username = $argv[5];
$db_password = $argv[6];
$site_url = $argv[12];

echo "Membuat User dan Database..." . PHP_EOL;
sleep(1);

// Create connection
$conn = mysqli_connect($db_hostname, $my_root, $my_pass);

// Check connection
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Create database and user
$sql = "CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
$sql .= "CREATE USER '{$db_username}'@'{$db_hostname}' IDENTIFIED BY '{$db_password}';";
$sql .= "GRANT SELECT, INSERT, DELETE, UPDATE, EXECUTE ON {$database}.* TO '{$db_username}'@'{$db_hostname}';";

if (mysqli_multi_query($conn, $sql)) {
    echo "User dan Database sukses dibuat" . PHP_EOL;
} else {
    echo "Error, Database gagal dibuat: " . mysqli_error($conn) . PHP_EOL;
    exit();
}

// Close connection
mysqli_close($conn);
$conn = null;

// Generate database data
echo "Mengisi data default..." . PHP_EOL;
$status = execute_sql($file, $database, $db_hostname, $my_root, $my_pass);
sleep(1);

if ($status) {
    echo "[v] - Data telah sukses terisi" . PHP_EOL;
} else {
    echo "[x] - Error, data gagal terisi" . PHP_EOL;
    die("Silahkan cek koneksi database Anda");
}

// Variable for Env.php
echo "Membuat token signature untuk JWT" . PHP_EOL;
$token = hash('sha256', '_ScientiaApp_' . rand(100000, 999999));
$jsToken = hash('md5', '_ScientiaApp_' . rand(100000, 999999));
sleep(1);
echo "[v] - JWT Token Signature : {$token}" . PHP_EOL;
$php = "<?php\n";
$php .= "\$conf=[\n";
$php .= "\t'APPVER'=>'v1.0', //application version\n";
$php .= "\t'APPNAME'=>'ScientiaAPP', //application name\n";
$php .= "\t'MODE'=>'develop', //develop or production\n";
$php .= "\t'BASE_URL' => '{$site_url}', //URL with trailing slash: https://local.host/\n";
$php .= "\t'DB_HOST' => '{$db_hostname}',\n";
$php .= "\t'DB_USER' => '{$db_username}',\n";
$php .= "\t'DB_PASS' => '{$db_password}',\n";
$php .= "\t'DB_NAME' => '{$database}',\n";
$php .= "\t// 'REDIS_HOST' => '127.0.0.1',\n";
$php .= "\t// 'REDIS_PORT' => 6379,\n";
$php .= "\t// 'REDIS_PASS' => null,\n";
$php .= "\t// 'REDIS_DB' => null,\n";
$php .= "\t'API_TOKEN' => '{$jsToken}',\n";
$php .= "\t'API_PATH' => 'api',\n";
$php .= "\t'SIGNATURE' => '{$token}'\n";
$php .= "];\n";

/* Generate env.php */
echo "Membuat file environment..." . PHP_EOL;
$envFile = 'env.php';
$handle = fopen($envFile, 'w') or die('Tidak dapat membuka file:  ' . $envFile);
if (!fwrite($handle, $php)) {
    echo "[x] - env.php gagal dibuat!" . PHP_EOL;
    die(error_get_last()['message']);
}
sleep(1);
echo "[v] - env.php berhasil dibuat" . PHP_EOL;

// Create connection
$conn = mysqli_connect($db_hostname, $my_root, $my_pass, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// User variable
// ScientiaAPP Super User
echo "Setup super user..." . PHP_EOL;
$nama = $argv[7];
$email = $argv[8];
$telp = $argv[9];
$uname = $argv[10];
$paswd = $argv[11];
$idjab = 1;
$kripto = new App\Lib\Encrypter($token);
$sec_pwd = $kripto->secure_passwd($uname, $paswd, true);

$sql = "INSERT INTO `m_user` (`nama`, `email`, `idjabatan`, `telpon`, `username`, `password`)
VALUES ('{$nama}', '{$email}', '{$idjab}', '{$telp}', '{$uname}', '{$sec_pwd}')";

if (mysqli_query($conn, $sql)) {
    sleep(1);
    echo "User Super untuk SimAset telah dibuat" . PHP_EOL;
    echo "Silahkan login dengan user berikut:" . PHP_EOL;
    echo PHP_EOL;
    echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL;
    echo "Username : {$uname}" . PHP_EOL;
    echo "Password : {$paswd}" . PHP_EOL;
    echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . PHP_EOL;
    echo PHP_EOL;
} else {
    echo "Error: " . $sql . PHP_EOL . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
$conn = null;

// Function to Execute SQL File
function execute_sql($file, $database, $db_hostname, $db_username, $db_password)
{
    // MySQL cli direct
    $mysql_paths = array();

    // Use mysql location from `which` command.
    $mysql = trim(`which mysql`);
    if (is_executable($mysql)) {
        array_unshift($mysql_paths, $mysql);
    }

    // Default paths
    $mysql_paths[] = '/Applications/MAMP/Library/bin/mysql'; //Mac Mamp
    $mysql_paths[] = 'c:\xampp\mysql\bin\mysql.exe'; //XAMPP
    $mysql_paths[] = '/usr/bin/mysql'; //Linux
    $mysql_paths[] = '/usr/local/mysql/bin/mysql'; //Mac
    $mysql_paths[] = '/usr/local/bin/mysql'; //Linux
    $mysql_paths[] = '/usr/mysql/bin/mysql'; //Linux

    foreach ($mysql_paths as $mysql) {
        if (is_executable($mysql)) {
            $execute_command = "\"$mysql\" --host=$db_hostname --user=$db_username --password=$db_password $database < $file";
            $status = false;
            system($execute_command, $status);
            return $status == 0;
        }
    }
}
