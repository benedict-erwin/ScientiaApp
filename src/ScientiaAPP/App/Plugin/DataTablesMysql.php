<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Plugin
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @updated    on Fri Jul 05 2019
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @requires
 *      1. Medoo Instance (https://github.com/catfan/Medoo)
 *      2. Monolog Instance (https://github.com/Seldaek/monolog)
 *
 **/

namespace App\Plugin;

class DataTablesMysql
{
    /* Declare Property */
    protected $db;
    private $logger, $mode;
    private $TABLE;
    private $COLUMNS    = [];
    private $PKEY;
    private $COLUMN_ORDER  = [];
    private $COLUMN_SEARCH = [];
    private $ORDER = [];
    private $AND_OR = "AND";
    private $SQL, $LAST_SQL;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        $this->db = $container->database;
        $this->mode = $container->get('settings')['mode'];
        $this->logger = $container->logger;
    }

    /* Set property TABLE */
    protected function setTable($table = '')
    {
        $this->TABLE = $table;
        return $this;
    }

    /* Get property TABLE */
    protected function getTable()
    {
        return $this->TABLE;
    }

    /* Set property COLUMNS */
    protected function setColumns(array $columns = [])
    {
        $this->COLUMNS = $columns;
        return $this;
    }

    /* Get property COLUMNS */
    protected function getColumns()
    {
        return $this->COLUMNS;
    }

    /* Set property PKEY */
    protected function setPkey($pkey = '')
    {
        $this->PKEY = $pkey;
        return $this;
    }

    /* Get property PKEY */
    protected function getPkey()
    {
        return $this->PKEY;
    }

    /* Set property COLUMN_ORDER */
    protected function setOrderCols(array $column_order = [])
    {
        $this->COLUMN_ORDER = $column_order;
        return $this;
    }

    /* Get property COLUMN_ORDER */
    protected function getOrderCols()
    {
        return $this->COLUMN_ORDER;
    }

    /* Set property COLUMN_SEARCH */
    protected function setSearchCols(array $column_search = [])
    {
        $this->COLUMN_SEARCH = $column_search;
        return $this;
    }

    /* Get property COLUMN_SEARCH */
    protected function getSearchCols()
    {
        return $this->COLUMN_SEARCH;
    }

    /* Set property ORDER */
    protected function setDefaultOrder(array $order = [])
    {
        $this->ORDER = $order;
        return $this;
    }

    /* Get property ORDER */
    protected function getDefaultOrder()
    {
        return $this->ORDER;
    }

    /* Set property AND_OR */
    protected function setAndOr($and_or)
    {
        $this->AND_OR = $and_or;
        return $this;
    }

    /* Set property SQL */
    protected function setQuery($sql = '')
    {
        $this->SQL = $sql;
        return $this;
    }

    /* Get property SQL */
    protected function getQuery()
    {
        return $this->SQL;
    }

    /* Set property LAST SQL */
    protected function setLastQuery($sql)
    {
        $this->LAST_SQL = $sql;
        return $this->LAST_SQL;
    }

    /* Get property LAST SQL */
    protected function getLastQuery()
    {
        return $this->LAST_SQL;
    }

    private function initiate_query()
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

        return $sql;
    }

    /* Function prepare query for DataTables */
    protected function get_datatables_query(array $safe)
    {
        $sql = $this->initiate_query();

        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            $x = 0;
            foreach ((array) $safe['opsional'] as $key => $nilai) {
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
        foreach ((array) $this->COLUMN_SEARCH as $item) {
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

        /* Set Manual Ordering */
        if (!empty($this->ORDER)) {
            $ord = str_replace('=', ' ', http_build_query($this->ORDER, '', ', '));
            $ord = utf8_decode(urldecode($ord));
            $sql .= " ORDER BY {$ord}";
        }
        /* Set Order from DataTables */ else {
            $kolum = (int) $safe['order']['0']['column'];
            $ord = (empty($this->COLUMN_ORDER[$kolum])) ? 1 : $this->COLUMN_ORDER[$kolum];
            $sort = (strtoupper($safe['order']['0']['dir']) === "ASC") ? " ASC" : " DESC";
            $sql .= " ORDER BY " . $ord . $sort;
        }

        /* Logger */
        if ($this->mode != 'production') {
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $sql]);
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
        $sql = $this->get_datatables_query($safe);

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
        $query = $this->db->pdo->prepare($sql);

        /* Opsional */
        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            foreach ((array) $safe['opsional'] as $key => $nilai) {
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
        if ($this->mode != 'production') {
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: BEFORE :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
            $arrFind = [':search_value', ':length', ':start'];
            $arrRep = ["'" . $search_value . "'", $length, $start];
            $sql = str_replace($arrFind, $arrRep, $sql);
            if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
                foreach ((array) $safe['opsional'] as $key => $nilai) {
                    /* Clean key for safe sql */
                    $binder = $key;
                    implode(
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
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: AFTER :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
        }

        /* Execute */
        $query->execute();

        /* Get Last Query */
        $this->setLastQuery($this->db->last());

        /* Return result */
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* Function count filtered record in table */
    protected function count_filtered(array $safe)
    {
        /* Get generated query */
        $sql = $this->get_datatables_query($safe);

        /* Param Variables */
        $safe['search']['value'] = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
        $search_value = "%" . strtoupper($safe['search']['value']) . "%";

        /* bindParam & execute */
        $query = $this->db->pdo->prepare($sql);

        /* Opsional */
        if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
            foreach ((array) $safe['opsional'] as $key => $nilai) {
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
        if ($this->mode != 'production') {
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: BEFORE :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
            $arrFind = [':search_value'];
            $arrRep = ["'" . $search_value . "'"];
            $sql = str_replace($arrFind, $arrRep, $sql);
            if (array_key_exists('opsional', $safe) && !empty($safe['opsional'])) {
                foreach ((array) $safe['opsional'] as $key => $nilai) {
                    if ($nilai || is_numeric($nilai)) {
                        $kolom = ':' . $key;
                        $sql = str_replace($kolom, $nilai, $sql);
                    }
                }
            }
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: AFTER :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
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
        $sql = $this->initiate_query();
        $data = $this->db->pdo->query($sql)->fetchAll();

        /* Logger */
        if ($this->mode != 'production') {
            $this->logger->info(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $this->db->last()]);
        }

        return count($data);
    }

    /* Function Insert */
    protected function saveData($data = [])
    {
        try {
            $result = $this->db->insert($this->TABLE, $data);
            if ($result->rowCount() > 0) {
                return $this->db->id();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            /* Logger */
            if ($this->mode != 'production') {
                $this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $this->db->last()]);
            }

            throw new \Exception($e->getMessage());
        }
    }

    /* Function Update */
    protected function updateData($data = [], $where = [])
    {
        try {
            $result = $this->db->update($this->TABLE, $data, $where);
            if ($result->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            /* Logger */
            if ($this->mode != 'production') {
                $this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $this->db->last()]);
            }

            throw new \Exception($e->getMessage());
        }
    }

    /* Function Delete by PK */
    protected function deleteData($pkey)
    {
        try {
            $result = $this->db->delete($this->TABLE, [$this->PKEY => $pkey]);
            if ($result->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            /* Logger */
            if ($this->mode != 'production') {
                $this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $this->db->last()]);
            }

            throw new \Exception($e->getMessage());
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
        try {
            $result = $this->db->delete($this->TABLE, $where);
            if ($result->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            /* Logger */
            if ($this->mode != 'production') {
                $this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['query' => $this->db->last()]);
            }

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get Data By ID (Primary Key)
     */
    protected function getDataById($id, $column = null)
    {
        if (!empty($this->SQL)) {
            $where = ((strpos(strtoupper($this->SQL), 'WHERE') !== false) ? ' AND ' : ' WHERE ')  . $this->PKEY . '=:' . $this->PKEY;
            $query = $this->db->pdo->prepare($this->SQL . $where);
            $query->bindParam(':' . $this->PKEY, $id, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $result = count($result > 0) ? $result[0] : null;
            return $result;
        }
        return $this->db->get($this->TABLE, (empty($column) ? (empty($this->COLUMNS) ? '*' : $this->COLUMNS) : $column), [$this->PKEY => $id]);
    }

    /* Override SQL Message */
    protected function overrideSQLMsg(String $msg = null)
    {
        $this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['error' => $msg]);
        $msgLower = strtolower($msg);
        $find = [
            "cannot delete or update a parent row: a foreign key constraint fails" => "Perubahan/penghapusan tidak diizinkan, data masih digunakan",
            "integrity constraint violation: 1062 duplicate entry" => "Data/kode primer telah ada dalam database, silahkan coba dengan data/kode lain",
        ];

        foreach ((array) $find as $key => $value) {
            if (strpos($msgLower, $key) !== false) {
                return $value;
            }
        }

        return $msg;
    }
}
