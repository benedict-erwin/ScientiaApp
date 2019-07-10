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

class M_menu extends \App\Plugin\DataTablesMysql
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
        $this->TagName = hash('sha256', $this->Sign . 'M_menu');
        $this->CacheExp = 3600; # in seconds (1 hour)

        /* Table Setup */
        $this->setTable('m_menu')
            ->setColumns(['id_menu', 'id_groupmenu', 'nama', 'icon', 'url', 'controller', 'tipe', 'aktif', 'urut', 'is_public'])
            ->setPkey('id_menu')
            ->setSearchCols(['nama', 'url', 'controller', 'tipe'])
            ->setDefaultOrder(['id_menu' => 'DESC']);
    }

    /**
     * Get Data in M_menu by Primary Key
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
            }else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Insert Data in M_menu
     *
     * @param array $data
     * @return int $last_insert_id
     */
    public function create(array $data = [])
    {
        try {
            if($this->isDuplicate(['/'.trim($data['url'], '/')], [$data['tipe']])){
                throw new \Exception('Menu sudah tersedia!');
            }

            if($lastId = $this->saveData($data)){
                $this->Cacher->deleteItemsByTags([
                    $this->TagName,
                    $this->Sign . '_router',
                    hash('sha256', $this->Sign . 'J_menu'),
                    hash('sha256', $this->Sign . 'CRUDGenerator')
                ]);
                return $lastId;
            }else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Retrieve data from M_menu
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
     * Update data from M_menu
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
                $this->Sign . '_router',
                hash('sha256', $this->Sign . 'J_menu'),
                hash('sha256', $this->Sign . 'CRUDGenerator')
            ]);
            return $update;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Remove single or multiple data from M_menu
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
                $this->Sign . '_router',
                hash('sha256', $this->Sign . 'J_menu'),
                hash('sha256', $this->Sign . 'CRUDGenerator')
            ]);
            return $delete;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Get List of role from m_role join j_menu
     *
     * @param integer $idmenu
     * @return array
     */
    public function getMenuRole(int $idmenu)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $idmenu);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $sql = 'SELECT a.idrole, a.deskripsi, c.idrole ID_ROLE
					FROM m_role a
					LEFT JOIN (
						SELECT b.id_menu, b.idrole
						FROM j_menu b
						WHERE b.id_menu=:idmenu
					) c ON a.idrole=c.idrole
					ORDER BY a.idrole DESC';
                $query = $this->db->pdo->prepare($sql);
                $query->bindParam(':idmenu', $idmenu, \PDO::PARAM_INT);
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

    /**
     * Get Complete Menu List by idrole
     *
     * @param integer $idrole
     * @return array
     */
    public function getMenu(int $idrole)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $idrole);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $sql = "SELECT a.idrole,
                            b.nama,
                            a.id_menu,
                            c.nama    nama_m,
                            CONCAT('ic_', REPLACE(LOWER(c.nama), ' ', '_')) badge,
                            c.url,
                            c.controller,
                            c.id_groupmenu,
                            d.nama    nama_g,
                            c.icon    icon_m,
                            d.icon    icon_g,
                            c.aktif aktif_m,
                            d.aktif aktif_g,
                            c.tipe,
                            c.urut order_m,
                            d.urut order_g
                        FROM j_menu a
                            LEFT JOIN m_role b
                                ON a.idrole = b.idrole
                            LEFT JOIN m_menu c
                                ON a.id_menu = c.id_menu
                            LEFT JOIN m_groupmenu d
                                ON c.id_groupmenu = d.id_groupmenu
                        WHERE b.idrole=:idrole
                            AND c.tipe=:tipe
                            AND d.aktif=1
                            AND c.aktif=1
                        ORDER BY d.urut ASC, c.urut ASC";

                $tipe = 'MENU';
                $query = $this->db->pdo->prepare($sql);
                $query->bindParam(':idrole', $idrole, \PDO::PARAM_INT);
                $query->bindParam(':tipe', $tipe, \PDO::PARAM_STR);
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

    /**
     * URL Authorization
     *
     * @param integer $idrole
     * @param string $url
     * @return string
     */
    public function controllerAuth(int $idrole, string $url)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $idrole . $url);
            $CachedString = $this->Cacher->getItem($cacheKey);

            if (!$CachedString->isHit()) {
                $output = $this->db->get(
                    'm_menu',
                    ['[>]j_menu' => 'id_menu'],
                    'm_menu.id_menu',
                    [
                        'j_menu.idrole' => $idrole,
                        'm_menu.url' => $url
                    ]
                );

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            }else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Check if Duplicated
     *
     * @param array $url
     * @param array $tipe
     * @return boolean
     */
    public function isDuplicate(array $url, array $tipe)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . json_encode(array_merge($url,$tipe)));
            $CachedString = $this->Cacher->getItem($cacheKey);

            if (!$CachedString->isHit()) {
                $output = $this->db->count(
                    $this->getTable(),
                    [
                        'url' => $url,
                        'tipe' => $tipe,
                    ]
                );

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return ((int)$output > 0) ? true:false;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }
}
