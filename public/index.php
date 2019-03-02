<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @file       index.php
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/* Define Path variable */
define('BASE_PATH', __DIR__ . '/..');
define('APP_PATH', BASE_PATH . '/src/ScientiaAPP/App');

/* Load autoload and app first - most important */
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/ScientiaAPP/Bootstrap/App.php';

/* Set Error Reporting */
if ($conf['MODE']=='develop') {
    error_reporting(-1);
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    } else {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
    }
}

/* Auto require everything in BASE_PATH/Bootstrap */
foreach (new DirectoryIterator(BASE_PATH . '/src/ScientiaAPP/Bootstrap') as $fileInfo) {
    if($fileInfo->isDot()) continue;
    require_once $fileInfo->getPathname();
}

/* Run Application */
$app->run();
