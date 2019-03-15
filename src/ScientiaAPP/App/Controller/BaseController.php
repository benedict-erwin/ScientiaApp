<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controller;

use Medoo\Medoo;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\Lib\Encrypter;
use App\Lib\Ipaddress;

class BaseController
{
    protected $container;
    protected $dbpdo;
    protected $param;
    protected $InstanceCache;
    protected $sign;
    protected $siteOwner;
    protected $jwtExp;
    protected $user_data = array();
    protected $kripto;
    protected $CacheExp;
    private $uri_path;
    private $jwtJTID;

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
        $this->head = $this->container->get('request')->getHeaders();
        $this->param = $this->container->get('request')->getParsedBody();
        $this->sign = $this->container->get('settings')['dbnya']['SIGNATURE'];
        $this->siteOwner = $this->container->get('settings')['base_url'];

        // PDO Setup & Kripto
        $this->dbpdo    = $container->database;
        $this->kripto = new Encrypter($this->sign);

        // URI Path variables
        $this->uri_path = trim($this->request->getUri()->getPath(), '/');
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' request_path :: ', ['INFO'=> 'URL_PATH : '. $this->uri_path]);
        }

        //JWT Expired time
        $this->jwtExp = 24*3600*30; //30Days

        //CacheExp
        $this->CacheExp = 3600; //in seconds

        // Check Authentication
        if (!in_array($this->uri_path, ['clogin'])) {
            $this->jwt_validate();
            $this->getUser();
            $this->controllerAuth();
            $this->auditLog();
        }
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

    /* JWT Token Generator */
    public function getTokenJWT(array $userdata = [])
    {
        // Generate jtid for one time token
        $userdata = (!empty($userdata)) ? $userdata:$this->user_data;
        $jtid = null;
        $ckey = hash('md5', $this->sign . '_13ened1ctu5_' . $userdata['ID_USER'] . (($this->isAjax() === false) ? '_'.rand(0, time()):''));
        $CachedString = $this->InstanceCache->getItem($ckey);
        if (is_null($CachedString->get())) {
            $jtid = $ckey;
            $CachedString->set($jtid)->expiresAfter($this->jwtExp)->addTag($this->sign . "_userSession_");
            $this->InstanceCache->save($CachedString);
        } else {
            $jtid = $CachedString->get();
        }

        // Generate JWT
        $signer = new Sha256();
        $token = (new Builder())->setIssuer($this->siteOwner)
            ->setAudience($this->siteOwner)
            ->setId($jtid, true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + ($this->jwtExp))
            ->set('ID_USER', $this->kripto->encrypt($userdata['ID_USER']))
            ->set('USERNAME', $userdata['USERNAME'])
            ->sign($signer, $this->sign)
            ->getToken();

        /* Logger in develop mode */
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo("jwt_validate_jti :: " . $jtid);
        }

        return $token = (string) $token;
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
    public function jsonResponse($status, $message = null, array $addl = null, $token = null, int $code)
    {
        $output = ['success' => $status];

        if ($message !== null) {
            $output['message'] = $message;
        }

        if (!empty($addl)) {
            $output = array_merge($output, $addl);
        }

        /* Generate new JWT */
        if (!in_array($this->uri_path, ['clogin', 'clogout'])) {
            $token = (string) $this->getTokenJWT();
        }

        $response = $this->response->withHeader('Cache-Control', 'no-cache, must-revalidate');
        $response = $response->withAddedHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');

        if ($token) {
            $response = $response->withAddedHeader('JWT', $token);
        }

        $response = $response->withJson($output, $code);

        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['INFO'=>'status : ' . $status]);
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
        $code = (is_int($code)) ? $code:200;
        return $this->jsonResponse(false, $message, $addl, null, $code);
    }

    /**
     * Handle a success response
     *
     * @param string $message Message to put in response [optional]
     * @param array $addl Set of additional information to add to the response [optional]
     * @param string $token JWT token [optional]
     * @param int $code http status code [optional]
     */
    public function jsonSuccess($message=null, $addl=null, $token=null, int $code=null)
    {
        $code = (is_int($code)) ? $code:200;
        return $this->jsonResponse(true, $message, $addl, $token, $code);
    }

    /* Controller Authorization */
    public function controllerAuth()
    {
        $urlink = "/" . $this->uri_path;
        $query = $this->dbpdo->get(
            'm_menu',
            [ '[>]j_menu' => 'id_menu' ],
            'm_menu.controller',
            [
                'j_menu.idjabatan' => $this->user_data['ID_JABATAN'],
                'm_menu.url' => $urlink
            ]
        );

        /* Unauthorized! */
        if (!$query) {
            header('HTTP/1.1 403 Forbidden');
            header("Content-Type: application/json;charset=utf-8");
            die("{\"success\":false,\"message\":{\"error\":\"Unauthorized!\"}}");
        }

        /* Authorized */
        return true;
    }

    /* AuditLog Function */
    private function auditLog($data = [])
    {
        if (!in_array($this->uri_path, array('cauth'))) {
            $ip = new Ipaddress();
            return $this->dbpdo->insert(
                'l_auditlog',
                [
                    'iduser' => $this->user_data['ID_USER'],
                    'tanggal' => Medoo::raw('NOW()'),
                    'action' => $this->uri_path,
                    'data' => json_encode($this->param),
                    'ip_address' => $ip->get_ip_address()
                ]
            );
        }
    }

    /* Get User from JWT */
    public function getUser()
    {
        $this->user_data = array();
        $head = $this->head;
        $token = (new Parser())->parse((string) $head['HTTP_JWT'][0]);

        if (!$token) {
            return null;
        }

        $id_user = $this->kripto->decrypt($token->getClaim('ID_USER'));
        $userdata = $this->getUserDetail($id_user);
        $this->user_data['ID_USER']  = $userdata['iduser'];
        $this->user_data['USERNAME'] = $userdata['username'];
        $this->user_data['NAME']     = $userdata['nama'];
        $this->user_data['EMAIL']    = strtolower($userdata['email']);
        $this->user_data['TELPON']   = $userdata['telpon'];
        $this->user_data['ID_JABATAN'] = $userdata['idjabatan'];
        $this->user_data['JABATAN']    = strtolower($userdata['jabatan']);

        return $this->user_data;
    }

    /* Get User Role */
    private function getUserDetail($id_user='')
    {
        $ckey = md5($this->sign . '_iduser_' . $id_user);
        $CachedString = $this->InstanceCache->getItem($ckey);
        if (is_null($CachedString->get())) {
            $userdata = $this->dbpdo->get(
                "m_user",
                ["[>]m_jabatan" => "idjabatan"],
                [
                    "m_user.iduser",
                    "m_user.username",
                    "m_user.nama",
                    "m_user.email",
                    "m_user.telpon",
                    "m_user.idjabatan",
                    "m_jabatan.nama (jabatan)",
                ],
                ["m_user.iduser" => $id_user]
            );
            $CachedString->set($userdata)->expiresAfter($this->jwtExp)->addTag($this->sign . "_UserDetail");
            $this->InstanceCache->save($CachedString);
        } else {
            $userdata = $CachedString->get();
        }
        return $userdata;
    }

    /* Validate JWT */
    public function jwt_validate()
    {
        if ($token = $this->check()) {
            $data = new ValidationData();
            $jtid = $token->getHeader('jti');
            $CachedString = $this->InstanceCache->getItem($jtid);
            if (is_null($CachedString->get())) {
                header('HTTP/1.1 419 Token Expired');
                header("Content-Type: application/json;charset=utf-8");
                die("{\"success\":false,\"message\":\"Token signature expired\"}");
            } else {
                $data->setIssuer($this->siteOwner);
                $data->setAudience($this->siteOwner);
                $data->setId($jtid);

                if (!$token->validate($data)) {
                    header('HTTP/1.1 419 Token Expired');
                    header("Content-Type: application/json;charset=utf-8");
                    die("{\"success\":false,\"message\":\"Token signature expired\"}");
                }

                $this->jwtJTID = $jtid;
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addInfo("jwt_validate_jti :: ", ['INFO'=>'JWTID : ' . $this->jwtJTID]);
                }

                if ($this->isAjax()===false) {
                    //logger
                    if ($this->container->get('settings')['mode'] != 'production') {
                        $this->logger->addInfo("ONE_TIME-TOKEN :: ", ['INFO'=>'JWTID : ' . $this->jwtJTID]);
                    }
                    // Remove cache for one time token
                    $this->InstanceCache->deleteItem($this->jwtJTID);
                }
            }
        }
    }

    /**
     * Authorization Verification
     *
     * $cek are aid collection, authorized aid
     */
    public function check()
    {
        $head = $this->head;
        $signer = new Sha256();
        $token = null;

        if (array_key_exists('HTTP_JWT', $head)) {
            try {
                try {
                    $token = (new Parser())->parse((string) $head['HTTP_JWT'][0]);
                    if (!$token->verify($signer, $this->sign)) {
                        header('HTTP/1.1 498 Token Invalid');
                        header("Content-Type: application/json;charset=utf-8");
                        die("{\"success\":false,\"message\":\"Token signature invalid\"}");
                    }
                } catch (\Exception $e) {
                    header('HTTP/1.1 498 Token Invalid');
                    header("Content-Type: application/json;charset=utf-8");
                    die("{\"success\":false,\"message\":\"Token invalid\"}");
                }
            } catch (\Exception $e) {
                header('HTTP/1.1 498 Token Invalid');
                header("Content-Type: application/json;charset=utf-8");
                die("{\"success\":false,\"message\":\"Token invalid\"}");
            }
        } else {
            header('HTTP/1.1 498 Token Invalid');
            header("Content-Type: application/json;charset=utf-8");
            die("{\"success\":false,\"message\":\"Token not found\"}");
        }

        return $token;
    }

    /* Clear Cache */
    protected function clearMenuCache($idjabatan=null, $iduser=null)
    {
        try {
            /* Vars */
            $idjabatan = ($idjabatan) ? $idjabatan:$this->user_data['ID_JABATAN'];
            $iduser = ($iduser) ? $iduser:$this->user_data['ID_USER'];

            /* getMenu */
            $getMenu = hash('md5', $this->sign . '_cmenu_' . $idjabatan);
            $this->InstanceCache->deleteItem($getMenu);

            /* iduser */
            $rmIduser = hash('md5', $this->sign . '_iduser_' . $iduser);
            $this->InstanceCache->deleteItem($rmIduser);

            /* Flush userSession & other cache */
            $this->InstanceCache->deleteItemsByTags([
                $this->sign . '_getAuthMenu_',
                $this->sign . '_getMenus_',
                $this->sign . '_getPermission_',
                $this->sign . '_router',
                $this->sign . '_userSession_',
                $this->sign . '_UserDetail',
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /* Remove Empty Cache Directory */
    protected function rmEmptyCache()
    {
        exec("find '" . APP_PATH    . "/Cache' -empty -type d -delete");
    }

    /* isAjax */
    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? true:false;
    }

    /* Destructor */
    public function __destruct()
    {
        $this->dbpdo = null;
        $this->param = null;
    }
}
