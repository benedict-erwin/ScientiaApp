<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    \App\Controllers
 * @author     Benedict E. Pranata
 * @copyright  (c) 2019 benedict.erwin@gmail.com
 * @created    on Thu Jun 23 2019
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers;

use App\Lib\Encrypter;

class BaseController
{
    /* Declare Variable */
    protected $container;
    protected $InstanceCache;
    protected $siteOwner;
    protected $logger;
    protected $param;
    protected $sign;
    protected $kripto;
    protected $jwtExp;
    protected $CacheExp;

    /**
     * Initialize the controller with the container
     *
     * @param Slim\Container $container Container instance
     */
    public function __construct(\Slim\Container $container)
    {
        // Default Vars
        $this->InstanceCache = $container->cacher;
        $this->logger = $container->logger;
        $this->container = $container;
        $this->head = $this->container->get('request')->getHeaders();
        $this->param = $this->container->get('request')->getParsedBody();
        $this->sign = $this->container->get('settings')['dbnya']['SIGNATURE'];
        $this->siteOwner = $this->container->get('settings')['base_url'];

        // Kripto
        $this->kripto = new Encrypter($this->sign);

        // URI Path variables
        $this->uri_path = trim($this->request->getUri()->getPath(), '/');
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->info(__METHOD__ . ' request_path :: ', ['INFO' => 'URL_PATH : ' . $this->uri_path]);
        }

        // JWT Expired time
        $this->jwtExp = 24 * 3600 * 30; //30Days

        // Cache Expired time
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

    /* isAjaxAndReferer */
    public function isAjaxAndReferer()
    {
        $xrequest = $this->container->get('request')->isXhr();
        $referer = @(strpos($_SERVER['HTTP_REFERER'], $this->siteOwner) !== false) ? true : false;
        return ($xrequest && $referer);
    }

    /* Destructor */
    public function __destruct()
    {
        $this->InstanceCache = null;
        $this->logger = null;
        $this->container = null;
        $this->head = null;
        $this->param = null;
        $this->sign = null;
        $this->siteOwner = null;
        $this->kripto = null;
        $this->CacheExp = null;
        $this->jwtExp = null;
    }
}
