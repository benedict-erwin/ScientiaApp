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

class Encrypter
{
    protected $salt;

    public function __construct(String $salt = null)
    {
        $this->salt = $salt;
    }
    
    /**
     * Encryption function
     *
     * secure password
     */
    public function secure_passwd($username='', $password='', $p_hash=false)
    {
        $toHash   = $this->encrypt($password);
        $password = str_split($toHash, ((strlen($toHash)/2)+1));
        $hash     = hash('md5', $this->encrypt($username) . $password[0] . $this->salt . $password[1]);
        $hash     = strrev($hash);
        $hash     = hash('sha512', $hash);

        if ($p_hash) {
            return password_hash($hash, PASSWORD_DEFAULT);
        } else {
            return $hash;
        }
    }

    /* 
     * password_verify
     */
    public function verify_passwd(String $username, String $password, String $stored_password)
    {
        $password = $this->secure_passwd($username, $password);
        return password_verify($password, $stored_password);
    }

    /** Base64encode with key **/
    public function encrypt($sData)
    {
        $sResult = '';
        for ($i=0;$i<strlen($sData);$i++) {
            $sChar    = substr($sData, $i, 1);
            $sKeyChar = substr($this->salt, ($i % strlen($this->salt)) - 1, 1);
            $sChar    = chr(ord($sChar) + ord($sKeyChar));
            $sResult .= $sChar;
        }
        return $this->encode_base64($sResult);
    }

    /** Base64decode with key **/
    public function decrypt($sData)
    {
        $sResult = '';
        $sData     = $this->decode_base64($sData);
        for ($i=0;$i<strlen($sData);$i++) {
            $sChar    = substr($sData, $i, 1);
            $sKeyChar = substr($this->salt, ($i % strlen($this->salt)) - 1, 1);
            $sChar    = chr(ord($sChar) - ord($sKeyChar));
            $sResult .= $sChar;
        }
        return $sResult;
    }

    /** Base64encode compatible with crypt.js **/
    public function encode_base64($sData)
    {
        $sBase64 = base64_encode($sData);
        return strtr($sBase64, '+/', '-_');
    }

    /** Base64encode compatible with crypt.js **/
    public function decode_base64($sData)
    {
        $sBase64 = strtr($sData, '-_', '+/');
        return base64_decode($sBase64);
    }

}
