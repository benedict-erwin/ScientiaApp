<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Lib
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */


namespace App\Lib;

class Ipaddress
{
    /*
    * Get Client ip address 
    */
    public function get_ip_address() {
        $ip_keys = ['REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    /* trim for safety measures */
                    $ip = trim($ip);

                    /* attempt to validate IP */
                    if ($this->validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        /* return unreliable ip since all else failed */
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
    }

    /*
     * Validate ipv4 and ipv6
     */
    public function validate_ip($ip)
    {
        return inet_pton($ip) !== false;
    }


}
