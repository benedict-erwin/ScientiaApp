<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
session_start();

require_once '../../src/ScientiaAPP/App/Lib/Encrypter.php';

switch ($_REQUEST['step_name']) {
    case 'svrcheck':
        reqCheck();
        break;
    case 'savedbconf':
        saveDbConf($_REQUEST['new_db'], $_REQUEST['new_user'], $_REQUEST['new_pass']);
        break;
    case 'savewebconf':
        saveWebConf($_REQUEST['tx_baseurl'], $_REQUEST['tx_fullname'], $_REQUEST['tx_email'], $_REQUEST['tx_phone'], $_REQUEST['tx_user'], $_REQUEST['tx_pass']);
        break;
    case 'testdb':
        connetionTest($_REQUEST['dbhost'], $_REQUEST['dbuser'], $_REQUEST['dbpass']);
        break;
    case 'composer_down':
        downloadComposer();
        break;
    case 'composer':
        composerInstall();
        sleep(1);
        break;
    case 'preparedir':
        createDir();
        sleep(2);
        break;
    case 'createnewuserdb':
        createNewUserDB();
        sleep(1);
        break;
    case 'importdatabase':
        importDatabase();
        sleep(2);
        break;
    case 'createsuperuser':
        createSuperUser();
        sleep(2);
        break;
    case 'createenv':
        createENV();
        sleep(2);
        break;
    case 'wedonehere':
        @unlink('composer.phar');
        @unlink('installer.php');
        @unlink('keys.dev.pub');
        @unlink('keys.tags.pub');
        rrmdir('cache');
        rrmdir('extracted');
        file_put_contents('.htaccess', 'Deny from all');
        session_destroy();
        break;

    default:
        var_dump(dirname(__DIR__));
        var_dump(getcwd());
        var_dump($_SESSION);
        die();
        break;
}

# PrintJSON
function printJson(array $data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

# Dependencies check
function reqCheck()
{
    # php version
    $phpver = version_compare(phpversion(), "7.0.0", ">=") ? true : false;

    # result
    $ver = phpversion();
    $ver = explode('-', $ver);
    $data = [
        'php_70' => ['check' => $phpver, 'version' => $ver[0]]
    ];

    printJson($data);
}

# Dowloading composer
function downloadComposer()
{
    putenv('COMPOSER_HOME=' . __DIR__);
    try {
        $installerURL = 'https://getcomposer.org/installer';
        $installerFile = 'installer.php';
        if (!file_exists($installerFile)) {
            echo 'Downloading ' . $installerURL . PHP_EOL;
            flush();
            $ch = curl_init($installerURL);
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_FILE, fopen($installerFile, 'w+'));
            if (curl_exec($ch))
                echo 'Success downloading ' . $installerURL . PHP_EOL;
            else {
                echo 'Error downloading ' . $installerURL . PHP_EOL;
                die();
            }
            flush();
        }
        echo 'Installer found : ' . $installerFile . PHP_EOL;
        echo 'Starting installation...' . PHP_EOL;
        flush();
        $argv = array();
        include $installerFile;
        flush();
    } catch (\Exception $e) {
        printJson(['success' => false, 'error' => $e->getMessage()]);
    }
}

# Extract composher.phar
function extractComposer()
{
    if (file_exists('composer.phar')) {
        echo 'Extracting composer.phar ...' . PHP_EOL;
        flush();
        $composer = new Phar('composer.phar');
        $composer->extractTo('extracted');
        echo 'Extraction complete.' . PHP_EOL;
    } else
        echo 'composer.phar does not exist';
}

# Execute composer install
function composerInstall()
{
    putenv('COMPOSER_HOME=' . __DIR__);
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $path = str_replace('\\', '\\\\', $path);
    }

    if (!file_exists('extracted')) {
        extractComposer();
    }

    try {
        require_once(__DIR__ . '/extracted/vendor/autoload.php');
        $input = new Symfony\Component\Console\Input\StringInput('install' . ' -vvv -d ' . $path);
        $output = new Symfony\Component\Console\Output\StreamOutput(fopen('php://output', 'w'));
        $app = new Composer\Console\Application();
        $app->run($input, $output);
    } catch (\Exception $e) {
        printJson(['success' => false, 'error' => $e->getMessage()]);
    }
}

# Crate SUper User
function createSuperUser()
{
    $data = [];
    $_SESSION['TOKEN'] = hash('sha256', '_ScientiaApp_' . rand(100000, 999999));
    $_SESSION['JS_TOKEN'] = hash('md5', '_ScientiaApp_' . rand(100000, 999999));
    $nama = $_SESSION['APP_FNAME'];
    $email = $_SESSION['APP_EMAIL'];
    $telp = $_SESSION['APP_PHONE'];
    $uname = $_SESSION['APP_USER'];
    $conn = mysqli_connect($_SESSION['DBHOST'], $_SESSION['DBUSER'], $_SESSION['DBPASS'], $_SESSION['NEWDB']);
    $kripto = new App\Lib\Encrypter($_SESSION['TOKEN']);
    $sec_pwd = $kripto->secure_passwd($_SESSION['APP_USER'], $_SESSION['APP_PASS'], true);
    $sql = "INSERT INTO `m_user` (`nama`, `email`, `idrole`, `telpon`, `username`, `password`) VALUES ('{$nama}', '{$email}', '1', '{$telp}', '{$uname}', '{$sec_pwd}')";

    if (mysqli_query($conn, $sql)) {
        $data['success'] = true;
        $data['error'] = null;
    } else {
        $data['success'] = true;
        $data['error'] = mysqli_error($conn);
    }

    printJson($data);
}

# Create ENV
function createENV()
{
    $site_url = $_SESSION['APP_BASEURL'];
    $db_hostname = $_SESSION['DBHOST'];
    $db_username = $_SESSION['NEWUSER'];
    $db_password = $_SESSION['NEWPASS'];
    $database = $_SESSION['NEWDB'];
    $jsToken = $_SESSION['JS_TOKEN'];
    $token = $_SESSION['TOKEN'];
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
    $php .= "\t'CMS_TEMPLATE' => 'notika',\n";
    $php .= "\t'SIGNATURE' => '{$token}'\n";
    $php .= "];\n";

    /* Generate env.php */
    $path =  dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
    $envFile = $path . 'env.php';
    $handle = fopen($envFile, 'w') or die(json_encode(['success' => false, 'error' => 'cannot open file ' . $envFile]));
    if (!fwrite($handle, $php)) {
        die(json_encode(['success' => false, 'error' => error_get_last()['message']]));
    }

    $data = [
        'username' => $_SESSION['APP_USER'],
        'password' => $_SESSION['APP_PASS'],
        'base_url' => $_SESSION['APP_BASEURL']
    ];
    printJson(['success' => true, 'error' => null, 'data' => $data]);
}

# Create New DB User
function createNewUserDB(Type $var = null)
{
    $data = [];
    $sql = "CREATE DATABASE `" . $_SESSION['NEWDB'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $sql .= "CREATE USER '" . $_SESSION['NEWUSER'] . "'@'" . $_SESSION['DBHOST'] . "' IDENTIFIED BY '" . $_SESSION['NEWPASS'] . "';";
    $sql .= "GRANT SELECT, INSERT, DELETE, UPDATE, EXECUTE ON " . $_SESSION['NEWDB'] . ".* TO '" . $_SESSION['NEWUSER'] . "'@'" . $_SESSION['DBHOST'] . "';";
    $mysqli = new mysqli($_SESSION['DBHOST'], $_SESSION['DBUSER'], $_SESSION['DBPASS']);
    if ($mysqli->connect_error) { /* check connection */
        $data['success'] = false;
        $data['error'] = $mysqli->connect_error;
    }

    /* execute multi query */
    if ($mysqli->multi_query($sql)) {
        $data['success'] = true;
        $data['error'] = null;
    } else {
        $data['success'] = false;
        $data['error'] = $mysqli->error;
    }

    $pid = $mysqli->thread_id;
    $mysqli->kill($pid);
    $mysqli->close();

    printJson($data);
}

# Import default DB
function importDatabase()
{
    $data = [];
    $sql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'database.sql');
    $mysqli = new mysqli($_SESSION['DBHOST'], $_SESSION['DBUSER'], $_SESSION['DBPASS'], $_SESSION['NEWDB']);
    if ($mysqli->connect_error) { /* check connection */
        $data['success'] = false;
        $data['error'] = $mysqli->connect_error;
    }

    /* execute multi query */
    if ($mysqli->multi_query($sql)) {
        $data['success'] = true;
        $data['error'] = null;
    } else {
        $data['success'] = false;
        $data['error'] = $mysqli->error;
    }

    $pid = $mysqli->thread_id;
    $mysqli->kill($pid);
    $mysqli->close();

    printJson($data);
}

# Test Connection
function connetionTest($host, $user, $password)
{
    $con = @mysqli_connect($host, $user, $password);
    $sql = @mysqli_query($con, "SHOW GRANTS FOR {$user}@{$host};");
    $row = @mysqli_fetch_assoc($sql);
    $row = @array_values($row);

    if (mysqli_connect_errno()) {
        $data['success'] = false;
        $data['error'] = mysqli_connect_error();
    } else {
        $cek = strpos(strtoupper($row[0]), 'GRANT ALL PRIVILEGES ON *.*');
        if ($cek !== false) {
            $data['success'] = true;
            $data['error'] = null;
            $_SESSION['DBHOST'] = $host;
            $_SESSION['DBUSER'] = $user;
            $_SESSION['DBPASS'] = $password;
        } else {
            $data['success'] = false;
            $data['error'] = 'user must be root!';
            $data['error'] .= PHP_EOL . mysqli_error($con);
        }
    }

    printJson($data);
}

# Save DB Conf
function saveDbConf($new_db, $user, $pass)
{
    $_SESSION['NEWDB'] = $new_db;
    $_SESSION['NEWUSER'] = $user;
    $_SESSION['NEWPASS'] = $pass;
    printJson(['saved']);
}

# Save Web Conf
function saveWebConf($url, $fname, $email, $phone, $user, $pass)
{
    $_SESSION['APP_BASEURL'] = $url;
    $_SESSION['APP_FNAME'] = $fname;
    $_SESSION['APP_EMAIL'] = $email;
    $_SESSION['APP_PHONE'] = $phone;
    $_SESSION['APP_USER'] = $user;
    $_SESSION['APP_PASS'] = $pass;
    printJson(['saved']);
}

# Create default DIR
function createDir()
{
    $path =  dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ScientiaAPP' . DIRECTORY_SEPARATOR;

    # Set default permission 755
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        chmod($item, 0755);
    }

    # Create Cache & Log folder
    if (!file_exists($path . 'App' . DIRECTORY_SEPARATOR . 'Cache')) mkdir($path . 'App' . DIRECTORY_SEPARATOR . 'Cache', 0777);
    if (!file_exists($path . 'App' . DIRECTORY_SEPARATOR . 'Log')) mkdir($path . 'App' . DIRECTORY_SEPARATOR . 'Log', 0777);

    # Set permission for development mode
    chmod($path . 'App' . DIRECTORY_SEPARATOR . 'Controllers', 0777);
    chmod($path . 'App' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Privates', 0777);
    chmod($path . 'App' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Publics', 0777);
    chmod($path . 'App' . DIRECTORY_SEPARATOR . 'Models', 0777);

    $data = [
        'created' => [
            $path . 'App' . DIRECTORY_SEPARATOR . 'Cache' . ' (chmod: 777)',
            $path . 'App' . DIRECTORY_SEPARATOR . 'Log' . ' (chmod: 777)'
        ],
        'permission' => [
            $path . 'App' . DIRECTORY_SEPARATOR . 'Controllers' . ' (chmod: 777)',
            $path . 'App' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Privates' . ' (chmod: 777)',
            $path . 'App' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Publics' . ' (chmod: 777)',
            $path . 'App' . DIRECTORY_SEPARATOR . 'Models' . ' (chmod: 777)'
        ]
    ];

    printJson($data);
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . DIRECTORY_SEPARATOR . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}
