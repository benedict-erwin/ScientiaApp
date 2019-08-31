<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Models
 * @author     Benedict E. Pranata
 * @copyright  (c) 2019 benedict.erwin@gmail.com
 * @created    on Fri Jul 05 2019
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Models;

class J_menu extends \App\Plugin\DataTablesMysql
{
    /* Declare private variable */
    private $Cacher;
    private $CacheExp;
    private $TagName;
    private $Sign;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Cache Setup */
        $this->Sign = $container->get('settings')['dbnya']['SIGNATURE'];
        $this->Cacher = $container->cacher;
        $this->TagName = hash('sha256', $this->Sign . 'J_menu');
        $this->CacheExp = 3600; # in seconds (1 hour)

        /* Table Setup */
        $this->setTable('j_menu')
            ->setColumns([
                'jm.id_menu',
                'jm.idrole',
                'mm.nama',
                'mm.url',
                'mm.controller',
                'mm.is_public',
                'mr.nama'
            ])->setPkey('jm.id_jmenu')
            ->setSearchCols(['mm.name', 'mm.url', 'mm.controller', 'mr.nama'])
            ->setDefaultOrder(['jm.id_jmenu' => 'DESC'])
            ->setQuery($this->alterSql());
    }

    /* Alter Default DataTablles Query */
    public function alterSql()
    {
        return 'SELECT jm.*, mm.nama, mm.url, mm.controller, mm.is_public, mr.nama
                FROM j_menu jm
                LEFT JOIN m_menu mm ON mm.id_menu=jm.id_menu
                LEFT JOIN m_role mr ON mr.idrole=jm.idrole';
    }

    /**
     * Get Data in J_menu by Primary Key
     *
     * @param integer $id
     * @return array
     */
    public function getByID(int $id)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $id);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $output = $this->getDataById($id);
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Insert Data in J_menu
     *
     * @param array $data
     * @return int $last_insert_id
     */
    public function create(array $data = [])
    {
        try {
            if ($lastId = $this->saveData($data)) {
                $this->Cacher->deleteItemsByTags([
                    $this->TagName,
                    hash('sha256', $this->Sign . 'M_menu'),
                    $this->Sign . '_router',
                ]);
                return $lastId;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Retrieve data from J_menu
     *
     * @param array $data
     * @return array $output
     */
    public function read(array $data = [])
    {
        try {
            unset($data['draw']);
            $output = [];
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . json_encode($data));
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $output = [
                    'datalist' => $this->get_datatables($data),
                    'recordsTotal' => $this->count_all($data),
                    'recordsFiltered' => $this->count_filtered($data)
                ];
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Update data from J_menu
     *
     * @param array $data
     * @param integer $id
     * @return bool
     */
    public function update(array $data = [], int $id)
    {
        try {
            $update = $this->updateData($data, [$this->getPkey() => $id]);
            $this->Cacher->deleteItemsByTags([
                $this->TagName,
                hash('sha256', $this->Sign . 'M_menu'),
                $this->Sign . '_router',
            ]);
            return $update;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Remove single or multiple data from J_menu
     *
     * @param array|integer $data
     * @return bool
     */
    public function delete($data)
    {
        try {
            $delete = $this->deleteData($data);
            $this->Cacher->deleteItemsByTags([
                $this->TagName,
                hash('sha256', $this->Sign . 'M_menu'),
                $this->Sign . '_router',
            ]);
            return $delete;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Update user permission
     *
     * @param array $idrole
     * @param int $idmenu
     * @return void
     */
    public function setPermission($idrole, $idmenu)
    {
        /* Start transaction */
        $this->db->pdo->beginTransaction();
        try {
            $this->deleteBy(['id_menu' => $idmenu]);
            if (array_key_exists('ID_ROLE', $idrole)) {
                foreach ($idrole['ID_ROLE'] as $role) {
                    $this->create(['id_menu' => $idmenu, 'idrole' => $role]);
                }
            }

            /* Commit transaction & Refresh Cache */
            $this->db->pdo->commit();
            $this->Cacher->deleteItemsByTags([
                $this->TagName,
                hash('sha256', $this->Sign . 'M_menu'),
                $this->Sign . '_router',
            ]);
        } catch (\Exception $e) {
            /* Rollback transaction on error */
            $this->db->pdo->rollBack();
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    public function getPermission(string $url = null, int $idrole = null)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $url . $idrole);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                //Controller
                $res = $this->db->get('m_menu', ['id_menu', 'controller', 'tipe'], ['url' => $url, 'ORDER' => ['controller' => 'DESC']]);
                if ($res['tipe'] == 'MENU') {
                    $cond = "c.id_menu='" . $res['id_menu'] . "'";
                } else {
                    $cond = "c.controller LIKE :controller";
                    $controller = explode(':', $res['controller']);
                    $controller = trim($controller[0]) . ':%';
                }

                //Permission
                $sql = "SELECT a.idrole, a.deskripsi, c.idrole, c.controller
                        FROM m_role a
                        LEFT JOIN (
                            SELECT b.id_menu, b.idrole, m.controller, m.aktif
                            FROM j_menu b
                            LEFT JOIN m_menu m
                                ON b.id_menu=m.id_menu
                        ) c ON a.idrole=c.idrole
                        WHERE {$cond} AND a.idrole=:idrole AND c.aktif=1
                        ORDER BY a.idrole";

                $query = $this->db->pdo->prepare($sql);
                if ($res['tipe'] != 'MENU') {
                    $query->bindParam(':controller', $controller, \PDO::PARAM_STR);
                }
                $query->bindParam(':idrole', $idrole, \PDO::PARAM_INT);
                $query->execute();
                $output = $query->fetchAll(\PDO::FETCH_ASSOC);

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }
}
