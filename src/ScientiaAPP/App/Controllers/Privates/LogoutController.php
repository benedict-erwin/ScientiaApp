<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    App\Controllers\Privates
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers\Privates;

class LogoutController extends \App\Controllers\PrivateController
{
    private $L_AUDITLOG;

    /**
     * Call Parent Constructor
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set Model */
        $this->L_AUDITLOG = new \App\Models\L_auditlog($container);
    }

    /**
     * Destroy JWT Session and Clear user cache
     *
     * @return json
     */
    public function logout()
    {
        try {
            $this->L_AUDITLOG->deleteReadLog($this->user_data['ID_USER']);
            $this->clearUserCache();
            $this->rmEmptyCache();
            return $this->jsonSuccess("Thanks " . ucfirst($this->user_data['USERNAME']) . ", see You again 🙂");
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }
}
