<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers\Publics;

use Medoo\Medoo;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\Lib\Ipaddress;

class LoginController extends \App\Controllers\PublicController
{
    private $M_USER, $L_AUDITLOG;

    /**
     * Call Parent Constructor
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Load Model */
        $this->M_USER = new \App\Models\M_user($container);
        $this->L_AUDITLOG = new \App\Models\L_auditlog($container);
    }

    /**
     * Filter login param and Call authenticator
     *
     * @return verify
     */
    public function index()
    {
        $gump = new \GUMP();
        $gump->validation_rules([
            'tx_username' => 'required',
            'tx_password' => 'required'
        ]);

        $gump->filter_rules([
            'tx_username' => 'trim|lower_case',
            'tx_password' => 'trim'
        ]);

        try {
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->error(__METHOD__ . ' :: ', [ 'USER_REQUEST' => $this->param[ 'tx_username'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                return $this->verify($safe);
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Login Verification
     *
     * @param array $userpass
     * @return json
     */
    private function verify(array $userpass = [])
    {
        /* Check db */
        try {
            $ip = new Ipaddress();
            $output['success'] = true;
            $output['message'] = '';
            $userdata = $this->M_USER->usernameCheck($userpass['tx_username']);

            if ($userdata) {
                if ($this->kripto->verify_passwd($userpass['tx_username'], $userpass['tx_password'], $userdata['password'])) {
                    /* Insert l_auditlog */
                    $this->L_AUDITLOG->create(
                        [
                            'iduser' => $userdata['iduser'],
                            'tanggal' => Medoo::raw('NOW()'),
                            'action' => 'clogin',
                            'http_method' => 'POST',
                            'data' => json_encode(['action' => 'sign_in']),
                            'ip_address' => $ip->get_ip_address()
                        ]
                    );

                    /* Update last login */
                    $this->M_USER->update(
                        [
                            'lastlogin' => Medoo::raw('NOW()'),
                            'ip_address' => $ip->get_ip_address()
                        ],
                        $userdata['iduser']
                    );

                    /* Generate token */
                    $jtid = null;
                    $ckey = hash('md5', $this->sign . '_13ened1ctu5_' . $userdata['iduser'] . (($this->isAjaxAndReferer() === false) ? '_' . rand(0, time()) : ''));
                    $CachedString = $this->InstanceCache->getItem($ckey);
                    if (is_null($CachedString->get())) {
                        $jtid = $ckey;
                        $CachedString->set($jtid)->expiresAfter($this->jwtExp)->addTag($this->sign . "_userSession_" . $userdata['iduser']);
                        $this->InstanceCache->save($CachedString);
                    } else {
                        $jtid = $CachedString->get();
                    }

                    /* Generate JWT */
                    $signer = new Sha256();
                    $token = (new Builder())->setIssuer($this->siteOwner)
                        ->setAudience($this->siteOwner)
                        ->setId($jtid, true)
                        ->setIssuedAt(time())
                        ->setNotBefore(time())
                        ->setExpiration(time() + ($this->jwtExp))
                        ->set('ID_USER', $this->kripto->encrypt($userdata['iduser']))
                        ->set('USERNAME', $userdata['username'])
                        ->sign($signer, $this->sign)
                        ->getToken();

                    /* Return success message */
                    $output['success'] = true;
                    $output['message'] = 'Welcome Back ' . ucfirst($userdata['username']) . ' 🙂';
                    $output['username'] = $userdata['username'];
                } else {
                    throw new \Exception("Oops, username or password is incorrect 🙁");
                }
            } else {
                throw new \Exception("Oops, username or password is incorrect 🙁");
            }

            /* Return Response */
            $response = $this->response->withHeader('Cache-Control', 'no-cache, must-revalidate');
            $response = $response->withAddedHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
            $response = $response->withAddedHeader('JWT', $token);
            $response = $response->withJson($output, 202);
            return $response;

        } catch (PDOException $e) {
            /* Return fail message */
            $output['success'] = false;
            $output['message'] = 'Verification fail!';
            $output['error'] = $e->getMessage();

            /* Return Response */
            $response = $this->response->withHeader('Cache-Control', 'no-cache, must-revalidate');
            $response = $response->withAddedHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
            $response = $response->withJson($output, 401);
            return $response;
        }
    }
}
