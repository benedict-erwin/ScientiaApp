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

class Stringer
{
    /* Stripos with array haystack */
    public static function is_like($arr = [], $str)
    {
        foreach ($arr as $cek) {
            if (stripos($str, $cek) !== false) {
                return true;
            }
        }
        return false;
    }

    /* Check if first char is uppercase    */
    public static function is_ucfirst($str = null)
    {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    /* Return numeric only */
    public static function numeric($str = null)
    {
        return preg_replace('/\D/', '', $str);
    }

    /**
     * Split Array into n
     *
     * @param [type] $array
     * @param [type] $parts
     * @return array
     * 
     * https://stackoverflow.com/a/16345458/1795275
     */
    public static function fill_chunck($array, $parts) {
        $t = 0;
        $result = array_fill(0, $parts - 1, array());
        $max = ceil(count($array) / $parts);
        foreach($array as $v) {
            @count($result[$t]) >= $max and $t ++;
            $result[$t][] = $v;
        }
        return $result;
    }
    
}
