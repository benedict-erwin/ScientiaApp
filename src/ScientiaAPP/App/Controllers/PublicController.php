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

class PublicController extends \App\Controllers\BaseController
{
    /* Declare Variable */
    protected $conf_data = array();

    /**
     * Initialize the controller with the container
     *
     * @param Slim\Container $container Container instance
     */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);
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

    /**
     * Handle the response and put it into a standard JSON structure
     *
     * @param boolean $status Pass/fail status of the request
     * @param string $message Message to put in the response [optional]
     * @param array $addl Set of additional information to add to the response [optional]
     * @param string $token JWT token [optional]
     * @param int $code http status code
     */
    public function jsonResponse($status, $message = null, array $addl = null, int $code)
    {
        $output = ['success' => $status];

        if ($message !== null) {
            $output['message'] = $message;
        }

        if (!empty($addl)) {
            $output = array_merge($output, $addl);
        }

        $response = $this->response->withHeader('Cache-Control', 'no-cache, must-revalidate');
        $response = $response->withAddedHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response = $response->withJson($output, $code);

        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['INFO' => 'status : ' . $status]);
        }

        return $response;
    }

    /**
     * Handle a failure response
     *
     * @param string $message Message to put in response [optional]
     * @param array $addl Set of additional information to add to the response [optional]
     * @param int $code http status code
     */
    public function jsonFail($message = null, array $addl = [], int $code = null)
    {
        $code = (is_int($code)) ? $code : 200;
        return $this->jsonResponse(false, $message, $addl, $code);
    }

    /**
     * Handle a success response
     *
     * @param string $message Message to put in response [optional]
     * @param array $addl Set of additional information to add to the response [optional]
     * @param string $token JWT token [optional]
     * @param int $code http status code [optional]
     */
    public function jsonSuccess($message = null, $addl = null, int $code = null)
    {
        $code = (is_int($code)) ? $code : 200;
        return $this->jsonResponse(true, $message, $addl, $code);
    }

}
