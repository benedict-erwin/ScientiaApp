<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/Bootstrap
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

require_once BASE_PATH . '/env.php';

use \phpFastCache\CacheManager;
use Medoo\Medoo;

try {
    /* FastCachePHP - FileDriver
     * Setup cache File Path
     */
    CacheManager::setDefaultConfig(["path" => APP_PATH . "/Cache/Api"]);

    /* Create Instance Cache */
    $IC = CacheManager::getInstance("files");

    /* FastCachePHP - RedisDriver */
    // $IC = CacheManager::getInstance('redis', [
    //     'host' => $conf['REDIS_HOST'],
    //     'port' => $conf['REDIS_PORT'],
    //     'password' => $conf['REDIS_PASS'],
    //     'database' => $conf['REDIS_DB']
    // ]);
} catch (\Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    header("Content-Type: application/json;charset=utf-8");
    echo json_encode(['error' => 'Cache error ' . $e->getMessage()]);
    exit();
}

/* Configuration Parameter */
$config = [
    'settings' => [
        'app_version' => $conf['APPVER'],
        'app_name' => $conf['APPNAME'],
        'api_token' => $conf['API_TOKEN'],
        'api_path' => $conf['API_PATH'],
        'mode' => $conf['MODE'],
        'base_url' => $conf['BASE_URL'],
        'jsversion' => ($conf['MODE']=='develop') ? date('Ymdhis'):date('Ym'), // force to reload JS
        'displayErrorDetails' => ($conf['MODE']=='develop') ? true:false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Monolog settings
        'logger' => [
            'name' => 'ScientiaAPP',
            'path' => APP_PATH . '/Log/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'dbnya' => [
            'DB_HOST'=> $conf['DB_HOST'],
            'DB_USER'=>$conf['DB_USER'],
            'DB_PASS'=>$conf['DB_PASS'],
            'DB_NAME'=>$conf['DB_NAME'],
            'SIGNATURE'=>$conf['SIGNATURE']
        ],
    ],
];

$app = new \Slim\App($config);
$container = $app->getContainer();

/* Monolog Logger setup */
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $streamHandler = new Monolog\Handler\StreamHandler($settings['path']);
    $logger->pushHandler($streamHandler);
    return $logger;
};

/* Make the custom App autoloader */
spl_autoload_register(function ($class) use ($container){
    $classFile = APP_PATH . '/../' . str_replace('\\', '/', $class) . '.php';
    if (!is_file($classFile)) {
        throw new \Exception('Invalid File! cannot load class: ' . $class);
    }
    require_once $classFile;
});

/* Autoload in our controllers into the container */
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_PATH . DIRECTORY_SEPARATOR . 'Controller')) as $fileInfo) {
    if (is_dir($fileInfo)) continue;
    if (strpos(strtolower($fileInfo), '.php') === FALSE) continue;
    $file = str_replace(APP_PATH . '/Controller/', '', $fileInfo);
    $file = str_replace('/', '\\', $file);
    $class = 'App\\Controller\\' . str_replace('.php', '', $file);
    $container[$class] = function ($container) use ($class) {
        return new $class();
    };
}

// old autoload controller - not recursive
// foreach (new DirectoryIterator(APP_PATH.'/Controller') as $fileInfo) {
//     if ($fileInfo->isDot()) {
//         continue;
//     }
//     $class = 'App\\Controller\\'.str_replace('.php', '', $fileInfo->getFilename());
//     $container[$class] = function ($c) use ($class) {
//         return new $class();
//     };
// }

/* Database Configuration */
$container['database'] = function ($container) {
    $conf = $container->get('settings')['dbnya'];
    $log = ($container->get('settings')['mode'] == 'production') ? false:true;
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => $conf['DB_NAME'],
        'server' => $conf['DB_HOST'],
        'username' => $conf['DB_USER'],
        'password' => $conf['DB_PASS'],
        'logging' => $log,
        'option' => array( \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION ),
        'charset' => 'utf8',
        'command' => [ 'SET SQL_MODE=ANSI_QUOTES' ]
    ]);
};

/* Not Found Handler - http 404 */
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['response']->withJson(['error' => 'Resource not valid'], 404);
    };
};

/* Error Handler - http 500 */
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $code = 500;
        $message = 'There was an error';

        if ($exception !== null) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        }

        // If it's not a valid HTTP status code, replace it with a 500
        if (!is_integer($code) || $code < 100 || $code > 599) {
            $code = 500;
        }

        if ($container->get('settings')['mode'] == 'production') {
            if (stripos($message, 'Unable to find template') !== false) {
                return $container['view']->render($response, 'Home/404.html')->withStatus(404);
            } else {
                return $container['response']->withJson(['success' => false], $code);
            }
        } else {
            // Use this for debugging purposes
            $container['logger']->AddInfo($message.' in '.$exception->getFile().' - ('.$exception->getLine().', '.get_class($exception).')');
            return $container['response']->withJson(['success' => false, 'error' => $message], $code);
        }
    };
};

/* Not Allowed Handler */
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['response']->withJson(['error' => 'Method not allowed'], 405);
    };
};

/* Register component on container */
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(APP_PATH . '/Templates', [
        'cache' => ($container->get('settings')['mode'] == 'production') ? APP_PATH . '/Cache/Tpl':false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $view->addExtension(new Twig_Extension_StringLoader());
    return $view;
};

/* Add twig Global variable */
$container->get('view')->getEnvironment()->addGlobal('mode', $container->get('settings')['mode']);
$container->get('view')->getEnvironment()->addGlobal('base_url', $container->get('settings')['base_url']);
$container->get('view')->getEnvironment()->addGlobal('app_name', $container->get('settings')['app_name']);
$container->get('view')->getEnvironment()->addGlobal('app_version', $container->get('settings')['app_version']);
$container->get('view')->getEnvironment()->addGlobal('api_token', $container->get('settings')['api_token']);
$container->get('view')->getEnvironment()->addGlobal('api_path', $container->get('settings')['api_path']);
