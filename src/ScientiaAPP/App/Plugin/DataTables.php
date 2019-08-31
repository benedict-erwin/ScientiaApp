<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Plugin
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @updated    on Mon Mar 18 2019
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 **/

namespace App\Plugin;

class DataTables extends \App\Controllers\PrivateController
{
    /* Declare Property */
    protected $SQL;
    protected $TABLE;
    protected $PKEY;
    protected $COLUMNS    = [];
    protected $COLUMN_ORDER  = [];
    protected $COLUMN_SEARCH = [];
    protected $ORDER = [];
    protected $AND_OR = "AND";

    /* Set property SQL */
    protected function set_SQL($sql = '')
    {
        $this->SQL = $sql;
        return $this;
    }

    /* Set property TABLE */
    protected function set_TABLE($table = '')
    {
        $this->TABLE = $table;
        return $this;
    }

    /* Set property PKEY */
    protected function set_PKEY($pkey = '')
    {
        $this->PKEY = $pkey;
        return $this;
    }

    /* Set property COLUMNS */
    protected function set_COLUMNS($columns = array())
    {
        $this->COLUMNS = $columns;
        return $this;
    }

    /* Set property COLUMN_ORDER */
    protected function set_COLUMN_ORDER($column_order = array())
    {
        $this->COLUMN_ORDER = $column_order;
        return $this;
    }

    /* Set property COLUMN_SEARCH */
    protected function set_COLUMN_SEARCH($column_search = array())
    {
        $this->COLUMN_SEARCH = $column_search;
        return $this;
    }

    /* Set property ORDER */
    protected function set_ORDER($order = array())
    {
        $this->ORDER = $order;
        return $this;
    }

    /* Set property AND_OR */
    protected function set_AND_OR($and_or)
    {
        $this->AND_OR = $and_or;
        return $this;
    }


    /* Function prepare query for DataTables */
    private function _get_datatables_query(array $safe)
    {
        //Generate Query
        if (!empty($this->SQL)) {
            $sql = $this->SQL;
        } else {
            $backtick = empty($this->COLUMNS) ? "*" : implode(", ", array_map(function ($a) {
                return "`" . $a . "`";
            }, $this->COLUMNS));
            $sql = "SELECT " . $backtick . " FROM " . "`" . $this->TABLE . "`";
        }

        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            $x = 0;
            foreach ($safe['opsional'] as $key => $nilai) {
                if ($nilai || is_numeric($nilai)) {
                    /* Clean key for safe sql */
                    $binder = $key;
                    $kol = implode(
                        ".",
                        array_map(function ($a) {
                            return "`" . $a . "`";
                        }, explode('.', preg_replace('/[^a-zA-Z_.]*/', '', $key)))
                    );

                    /* Explode get table column */
                    if (strpos($key, '.') !== false) {
                        $xp = explode('.', $key);
                        $binder = end($xp);
                    }

                    if ($x === 0) { //first loop
                        if ($nilai || is_numeric($nilai)) {
                            if (strpos(strtoupper($sql), 'WHERE') !== false) {
                                $sql .= " {$this->AND_OR} ";
                            } else {
                                $sql .= " WHERE ";
                            }

                            $sql .= " ( "; //open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                            $sql .= $kol . " = :" . $binder;
                        }
                    } else {
                        if ($nilai || is_numeric($nilai)) {
                            if (strpos(strtoupper($sql), 'WHERE') !== false) {
                                $sql .= " {$this->AND_OR} ";
                            } else {
                                $sql .= " WHERE ";
                            }

                            $sql .= $kol . " = :" . $binder;
                        }
                    }

                    if (count(array_filter($safe['opsional'], 'strlen')) - 1 == $x) {
                        $sql .= " ) "; //close bracket
                    }
                    $x++;
                }
            }
        }

        //Loop column search
        $i = 0;
        foreach ($this->COLUMN_SEARCH as $item) {
            $safe['search']['value'] = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
            if ($safe['search']['value']) {
                if ($i === 0) { //first loop
                    if (strpos(strtoupper($sql), 'WHERE') !== false) {
                        $sql .= " {$this->AND_OR} ";
                    } else {
                        $sql .= " WHERE ";
                    }

                    $sql .= " ( "; //open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $sql .= "$item LIKE :search_value ";
                } else {
                    $sql .= " OR $item LIKE :search_value ";
                }
                if (count($this->COLUMN_SEARCH) - 1 == $i) { //last loop
                    $sql .= " ) "; //close bracket
                }
            }
            $i++;
        }

        //Set Ordering
        if (!empty($this->ORDER)) {
            $ord = str_replace('=', ' ', http_build_query($this->ORDER, '', ', '));
            $ord = utf8_decode(urldecode($ord));
            $sql .= " ORDER BY {$ord}";
        } else {
            $kolum = (int) $safe['order']['0']['column'];
            $ord = (empty($this->COLUMN_ORDER[$kolum])) ? 1 : $this->COLUMN_ORDER[$kolum];
            $sort = (strtoupper($safe['order']['0']['dir']) === "ASC") ? " ASC" : " DESC";
            $sql .= " ORDER BY " . $ord . $sort;
        }

        return $sql;
    }

    /* Function Execute main query for DataTables */
    protected function get_datatables(array $safe)
    {
        if (!isset($safe['start'])) {
            throw new \Exception("Start param is required!", 1);
        }
        if (!isset($safe['length'])) {
            throw new \Exception("Length param is required!", 1);
        }

        /* Get generated query */
        $sql = $this->_get_datatables_query($safe);

        /* Check limit */
        if ($safe['length'] != -1) {
            $sql .= " LIMIT :length OFFSET :start";
        }

        /* Param Variables */
        $safe['search']['value'] = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
        $search_value = "%" . strtoupper($safe['search']['value']) . "%";
        $length = (int) $safe['length'];
        $start = (int) $safe['start'];

        /* bindParam & execute */
        $query = $this->dbpdo->pdo->prepare($sql);

        /* Opsional */
        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            foreach ($safe['opsional'] as $key => $nilai) {
                if ($nilai || is_numeric($nilai)) {
                    $binder = $key;
                    if (strpos($key, '.') !== false) {
                        $xp = explode('.', $key);
                        $binder = end($xp);
                    }
                    $query->bindValue(":$binder", $nilai, \PDO::PARAM_STR);
                }
            }
        }

        /* For Searching */
        if ($safe['search']['value']) {
            $query->bindParam(':search_value', $search_value, \PDO::PARAM_STR);
        }

        /* For limit */
        if ($safe['length'] != -1) {
            $query->bindParam(':length', $length, \PDO::PARAM_INT);
            $query->bindParam(':start', $start, \PDO::PARAM_INT);
        }

        /* Logger */
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: BEFORE :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
            $arrFind = [':search_value', ':length', ':start'];
            $arrRep = ["'" . $search_value . "'", $length, $start];
            $sql = str_replace($arrFind, $arrRep, $sql);
            if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
                foreach ($safe['opsional'] as $key => $nilai) {
                    /* Clean key for safe sql */
                    $binder = $key;
                    $kol = implode(
                        ".",
                        array_map(function ($a) {
                            return "`" . $a . "`";
                        }, explode('.', preg_replace('/[^a-zA-Z_.]*/', '', $key)))
                    );

                    /* Explode get table column */
                    if (strpos($key, '.') !== false) {
                        $xp = explode('.', $key);
                        $binder = end($xp);
                    }

                    /* Replace param with value */
                    if ($nilai || is_numeric($nilai)) {
                        $kolom = ':' . $binder;
                        $sql = str_replace($kolom, $nilai, $sql);
                    }
                }
            }
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: AFTER :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
        }

        /* Execute */
        $query->execute();

        /* Return result */
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* Function count filtered record in table */
    protected function count_filtered(array $safe)
    {
        /* Get generated query */
        $sql = $this->_get_datatables_query($safe);

        /* Param Variables */
        $safe['search']['value'] = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
        $search_value = "%" . strtoupper($safe['search']['value']) . "%";

        /* bindParam & execute */
        $query = $this->dbpdo->pdo->prepare($sql);

        /* Opsional */
        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            foreach ($safe['opsional'] as $key => $nilai) {
                if ($nilai || is_numeric($nilai)) {
                    $binder = $key;
                    if (strpos($key, '.') !== false) {
                        $xp = explode('.', $key);
                        $binder = end($xp);
                    }
                    $query->bindValue(":$binder", $nilai, \PDO::PARAM_INT);
                }
            }
        }

        /* If Searching */
        if ($safe['search']['value']) {
            $query->bindParam(':search_value', $search_value, \PDO::PARAM_STR);
        }

        /* Logger */
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: BEFORE :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
            $arrFind = [':search_value'];
            $arrRep = ["'" . $search_value . "'"];
            $sql = str_replace($arrFind, $arrRep, $sql);
            if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
                foreach ($safe['opsional'] as $key => $nilai) {
                    if ($nilai || is_numeric($nilai)) {
                        $kolom = ':' . $key;
                        $sql = str_replace($kolom, $nilai, $sql);
                    }
                }
            }
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: AFTER :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
        }

        /* Execute */
        $query->execute();

        /* Return result */
        $data = $query->fetchAll(\PDO::FETCH_ASSOC);
        return count($data);
    }

    /* Function Count All record in table */
    protected function count_all($safe = '')
    {
        /* Get AllData */
        $data = $this->dbpdo->select($this->TABLE, "*");

        /* Logger */
        if ($this->container->get('settings')['mode'] != 'production') {
            $this->logger->addInfo(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', $this->dbpdo->log());
        }

        return count($data);
    }

    /* Function Insert */
    protected function saveDb($data = [])
    {
        $result = $this->dbpdo->insert($this->TABLE, $data);
        if ($result->rowCount() > 0) {
            return $this->dbpdo->id();
        } else {
            /* Logger */
            if ($this->container->get('settings')['mode'] != 'production') {
                $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', $this->dbpdo->log());
            }
            return false;
        }
    }

    /* Function Update */
    protected function updateDb($data = [], $where = [])
    {
        $result = $this->dbpdo->update($this->TABLE, $data, $where);
        if ($result->rowCount() > 0) {
            return true;
        } else {
            /* Logger */
            if ($this->container->get('settings')['mode'] != 'production') {
                $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', $this->dbpdo->log());
            }
            return false;
        }
    }

    /* Function Delete by PK */
    protected function deleteDb($pkey)
    {
        $result = $this->dbpdo->delete($this->TABLE, [$this->PKEY => $pkey]);
        if ($result->rowCount() > 0) {
            return true;
        } else {
            /* Logger */
            if ($this->container->get('settings')['mode'] != 'production') {
                $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', $this->dbpdo->log());
            }
            return false;
        }
    }

    /**
     * Delete from table where data from param
     *
     * @param string $table
     * @param array $where
     * @return void
     */
    protected function deleteBy(array $where = [])
    {
        $result = $this->dbpdo->delete($this->TABLE, $where);
        if ($result->rowCount() > 0) {
            return true;
        } else {
            /* Logger */
            if ($this->container->get('settings')['mode'] != 'production') {
                $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', $this->dbpdo->log());
            }
            return false;
        }
    }

    /* Get Single Data */
    protected function getData($column = [], $where = [])
    {
        return $this->dbpdo->get($this->TABLE, (empty($column) ? (empty($this->COLUMNS) ? '*' : $this->COLUMNS) : $column), $where);
    }

    /**
     * Get Data By ID (Primary Key)
     */
    protected function getDataById($id, $column = null)
    {
        return $this->dbpdo->get($this->TABLE, (empty($column) ? (empty($this->COLUMNS) ? '*' : $this->COLUMNS) : $column), [$this->PKEY => $id]);
    }

    /* Override SQL Message */
    public function overrideSQLMsg(String $msg = null)
    {
        $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['error' => $msg]);
        $msgLower = strtolower($msg);
        $find = [
            "cannot delete or update a parent row: a foreign key constraint fails" => "Perubahan/penghapusan tidak diizinkan, data masih digunakan",
            "integrity constraint violation: 1062 duplicate entry" => "Data/kode primer telah ada dalam database, silahkan coba dengan data/kode lain",
        ];

        foreach ($find as $key => $value) {
            if (strpos($msgLower, $key) !== false) {
                return $value;
            }
        }

        return $msg;
    }
}
