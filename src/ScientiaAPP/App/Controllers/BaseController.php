<?php
/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2019 benedict.erwin@gmail.com
 * @created    on Thu Jun 23 2019
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers;

class BaseController
{
    /* Declare Variable */
    protected $container;
    protected $dbpdo;
    protected $InstanceCache;
    protected $siteOwner;
    protected $logger;
    protected $CacheExp;
    protected $param;
    protected $sign;
    protected $jwtExp;
    protected $conf_data = array();

    /**
     * Initialize the controller with the container
     *
     * @param Slim\Container $container Container instance
     */
    public function __construct(\Slim\Container $container)
    {
        // Vars
        global $IC;

        $this->InstanceCache = $IC;
        $this->container = $container;
        $this->siteOwner = $this->container->get('settings')['base_url'];
        $this->param = $this->container->get('request')->getParsedBody();
        $this->sign = $this->container->get('settings')['dbnya']['SIGNATURE'];
        $this->CacheExp = 3600; //in seconds

        // PDO Setup & Kripto
        $this->dbpdo = $container->database;
        $this->logger = $container->logger;
        $this->conf_data = $this->getConfig();

        //JWT Expired time
        $this->jwtExp = 24 * 3600 * 30; //30Days

        //CacheExp
        $this->CacheExp = 3600; //in seconds
    }

    /**
     * Magic method to get things off of the container by referencing
     * them as properties on the current object
     */
    public function __get($property)
    {
        if (isset($this->container, $property)) {
            return $this->container->$property;
        }
        return null;
    }

    private function getConfig()
    {
        $arr = array();
        $ckey = hash('md5', $this->sign . '_load_m_config_');
        $CachedString = $this->InstanceCache->getItem($ckey);
        if (is_null($CachedString->get())) {
            $config = $this->dbpdo->select('m_config', ['name', 'value']);
            foreach ($config as $key => $value) {
                $arr[$value['name']] = $value['value'];
            }
            $CachedString->set($arr)->expiresAfter($this->CacheExp);
            $this->InstanceCache->save($CachedString);
        } else {
            $arr = $CachedString->get();
        }
        return $arr;
    }

    /* isAjax */
    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? true : false;
    }

    /* Destructor */
    public function __destruct()
    {
        $this->dbpdo = null;
        $this->param = null;
    }
}
