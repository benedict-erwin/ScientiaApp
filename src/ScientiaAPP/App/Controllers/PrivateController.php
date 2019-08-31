<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    \App\Controllers
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers;

use Medoo\Medoo;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\Lib\Ipaddress;

class PrivateController extends \App\Controllers\BaseController
{
    protected $user_data = [];
    private $jwtJTID;
    private $M_MENU;
    private $M_USER;
    private $L_AUDITLOG;

    /**
     * Initialize the controller with the container
     *
     * @param Slim\Container $container Container instance
     */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Load Model */
        $this->M_MENU = new \App\Models\M_menu($container);
        $this->M_USER = new \App\Models\M_user($container);
        $this->L_AUDITLOG = new \App\Models\L_auditlog($container);

        /* Check Authentication */
        $this->jwt_validate();
        $this->getUser();
        $this->controllerAuth();
        $this->auditLog();
    }

    /* JWT Token Generator */
    public function getTokenJWT(array $userdata = [])
    {
        // Generate jtid for one time token
        $jtid = null;
        $userdata = (!empty($userdata)) ? $userdata : $this->user_data;
        $ckey = hash('md5', $this->sign . '_13ened1ctu5_' . $userdata['ID_USER'] . (($this->isAjaxAndReferer() === false) ? '_' . rand(0, time()) : ''));
        $CachedString = $this->InstanceCache->getItem($ckey);
        if (is_null($CachedString->get())) {
            $jtid = $ckey;
            $CachedString->set($jtid)->expiresAfter($this->jwtExp)->addTag($this->sign . "_userSession_" . $userdata['ID_USER']);
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
            $this->logger->info("jwt_validate_jti :: " . $jtid);
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
        $token = (string) $this->getTokenJWT();

        /* Write Response */
        $response = $this->response->withHeader('Cache-Control', 'no-cache, must-revalidate');
        $response = $response->withAddedHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');

        if ($token) {
            $response = $response->withAddedHeader('JWT', $token);
        }

        $response = $response->withJson($output, $code);

        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->info(__METHOD__ . ' :: ', ['INFO' => 'status : ' . $status]);
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
    public function jsonSuccess($message = null, $addl = null, $token = null, int $code = null)
    {
        $code = (is_int($code)) ? $code : 200;
        return $this->jsonResponse(true, $message, $addl, $token, $code);
    }

    /* Controller Authorization */
    public function controllerAuth()
    {
        $urlink = "/" . $this->uri_path;
        $urlink = str_replace('/' . $this->container->get('settings')['api_path'] . '/', '/', $urlink);
        $exp = explode('/', $urlink);
        $exp = trim(end($exp));
        $urlink = is_numeric($exp) ? str_replace("/{$exp}", '', $urlink) : $urlink;
        $query = $this->M_MENU->controllerAuth($this->user_data['ID_ROLE'], $urlink);

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
    private function auditLog()
    {
        if (!in_array($this->uri_path, ['api/cauth', 'api/cmenu'])) {
            $ip = new Ipaddress();
            $this->L_AUDITLOG->create(
                [
                    'iduser' => $this->user_data['ID_USER'],
                    'tanggal' => Medoo::raw('NOW()'),
                    'action' => $this->uri_path,
                    'http_method' => strtoupper($this->request->getMethod()),
                    'data' => json_encode($this->param),
                    'ip_address' => $ip->get_ip_address()
                ]
            );
        }
    }

    /* Get User from JWT */
    public function getUser()
    {
        $this->user_data = [];
        $token = explode('Bearer', $this->head['HTTP_AUTHORIZATION'][0]);
        $token = (new Parser())->parse((string) trim(end($token)));

        if (!$token) {
            return null;
        }

        $id_user = $this->kripto->decrypt($token->getClaim('ID_USER'));
        $userdata = $this->M_USER->getByID((int) $id_user);
        $userdata = $userdata;
        $this->user_data['ID_USER']  = $userdata['iduser'];
        $this->user_data['USERNAME'] = $userdata['username'];
        $this->user_data['NAME']     = $userdata['nama'];
        $this->user_data['EMAIL']    = strtolower($userdata['email']);
        $this->user_data['TELPON']   = $userdata['telpon'];
        $this->user_data['ID_ROLE'] = $userdata['idrole'];
        $this->user_data['ROLE']    = strtolower($userdata['role']);

        return $this->user_data;
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
                    $this->logger->info("jwt_validate_jti :: ", ['INFO' => 'JWTID : ' . $this->jwtJTID]);
                }

                if ($this->isAjax() === false) {
                    //logger
                    if ($this->container->get('settings')['mode'] != 'production') {
                        $this->logger->info("ONE_TIME-TOKEN :: ", ['INFO' => 'JWTID : ' . $this->jwtJTID]);
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
        // $head = $this->head;
        $signer = new Sha256();
        $token = null;

        if (array_key_exists('HTTP_AUTHORIZATION', $this->head)) {
            try {
                try {
                    $token = explode('Bearer', $this->head['HTTP_AUTHORIZATION'][0]);
                    $token = (new Parser())->parse((string) trim(end($token)));
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
    protected function clearUserCache($idrole = null, $iduser = null)
    {
        try {
            /* Vars */
            $idrole = ($idrole) ? $idrole : $this->user_data['ID_ROLE'];
            $iduser = ($iduser) ? $iduser : $this->user_data['ID_USER'];

            /* getMenu */
            $getMenu = hash('md5', $this->sign . '_cmenu_' . $idrole);
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
                $this->sign . '_userSession_' . $iduser,
                hash('sha256', $this->sign . 'M_user'),
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
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? true : false;
    }
}
