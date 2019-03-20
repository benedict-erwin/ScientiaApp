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
use App\Lib\Ipaddress;

class LoginController extends \App\Controller\BaseController
{

    /**
     * Call Parent Constructor
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        parent::__construct($container);
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
                    $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', [ 'USER_REQUEST' => $this->param[ 'tx_username'], 'INFO' => $ers]);
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
            $userdata = $this->dbpdo->get("m_user", ["iduser", "username", "password"], ["username" => $userpass['tx_username']]);
            $ip = new Ipaddress();

            if ($userdata) {
                if ($this->kripto->verify_passwd($userpass['tx_username'], $userpass['tx_password'], $userdata['password'])) {
                    /* Insert l_auditlog */
                    $this->dbpdo->insert(
                        'l_auditlog',
                        [
                            'iduser' => $userdata['iduser'],
                            'tanggal' => Medoo::raw('NOW()'),
                            'action' => 'clogin',
                            'data' => json_encode(['action' => 'sign_in']),
                            'ip_address' => $ip->get_ip_address()
                        ]
                    );

                    /* Update last login */
                    $this->dbpdo->update(
                        'm_user',
                        [
                            'lastlogin' => Medoo::raw('NOW()'),
                            'ip_address' => $ip->get_ip_address()
                        ],
                        ['iduser' => $userdata['iduser']]
                    );

                    /* Generate token */
                    $userdata['ID_USER'] = $userdata['iduser'];
                    $userdata['USERNAME'] = $userdata['username'];
                    $token = (string) $this->getTokenJWT($userdata);

                    /* Return success message */
                    return $this->jsonSuccess("Welcome " . ucfirst($userdata['username']) . " 🙂", ['username' => $userdata['username']], $token, 202);
                } else {
                    throw new \Exception("Oops, username or password is incorrect 🙁");
                }
            } else {
                throw new \Exception("Oops, username or password is incorrect 🙁");
            }
        } catch (PDOException $e) {
            return $this->jsonFail('Verification fail!', ['error'=>$e->getMessage()], 401);
        }
    }

    /**
     * Destroy JWT Session and Clear user cache
     *
     * @return json
     */
    public function logout()
    {
        try {
            $this->clearUserCache();
            $this->rmEmptyCache();
            return $this->jsonSuccess("Thanks " . ucfirst($this->user_data['USERNAME']) . ", see You again 🙂");
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }
}
