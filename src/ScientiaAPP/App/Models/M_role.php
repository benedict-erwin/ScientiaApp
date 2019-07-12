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

class M_role extends \App\Plugin\DataTablesMysql
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
        $this->TagName = hash('sha256', $this->Sign . 'M_role');
        $this->CacheExp = 3600; # in seconds (1 hour)

        /* Table Setup */
        $this->setTable('m_role')
            ->setColumns(['idrole', 'nama', 'deskripsi'])
            ->setPkey('idrole')
            ->setSearchCols(['nama', 'deskripsi'])
            ->setDefaultOrder(['idrole' => 'DESC']);
    }

    /**
     * Get Data in M_role by Primary Key
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
     * Insert Data in M_role
     *
     * @param array $data
     * @return int $last_insert_id
     */
    public function create(array $data = [])
    {
        try {
            if($lastId = $this->saveData($data)){
                $this->Cacher->deleteItemsByTags([
                    $this->TagName,
                    $this->Sign . '_getMenus',
                    $this->Sign . '_router',
                ]);
                return $lastId;
            }else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    public function createAndSetDefaultPermission(array $data = [])
    {
        /* Start transaction */
        $this->db->pdo->beginTransaction();
        try {
            /* Save and get last_insert_id */
            if($idrole = $this->create($data)){

                /* Select default access */
                $jmenu = $this->db->select(
                    'm_menu',
                    'id_menu',
                    [
                        'url' => [
                            '/clogin',
                            '/clogout',
                            '/cauth',
                            '/cmenu'
                        ]
                    ]
                );

                /* Grant access to new role */
                foreach ($jmenu as $menu) {
                    $this->db->insert('j_menu', ['id_menu' => $menu, 'idrole' => $idrole]);
                }

                /* Commit transaction & delete old cache */
                $this->db->pdo->commit();
                $this->Cacher->deleteItemsByTags([
                    $this->TagName,
                    hash('sha256', $this->Sign . 'M_menu'),
                    $this->Sign . '_getMenus',
                    $this->Sign . '_router',
                ]);

                return $idrole;
            }else {
                return false;
            }

        } catch (\Exception $e) {
            /* Rollback transaction on error */
            $this->db->pdo->rollBack();
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Retrieve data from M_role
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
            }else {
                $output = $CachedString->get();
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Update data from M_role
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
                $this->Sign . '_getMenus',
                $this->Sign . '_router',
            ]);
            return $update;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Remove single or multiple data from M_role
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
                $this->Sign . '_getMenus',
                $this->Sign . '_router',
            ]);
            return $delete;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }
}
