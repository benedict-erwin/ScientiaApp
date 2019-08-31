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

class CRUDGenerator extends \App\Plugin\DataTablesMysql
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
        $this->TagName = hash('sha256', $this->Sign . 'CRUDGenerator');
        $this->CacheExp = 3600; # in seconds (1 hour)
    }

    /**
     * List All Available Tables
     *
     * @return array
     */
    public function getAllTables()
    {
        try {
            $query = $this->db->pdo->prepare("SHOW TABLES");
            $query->execute();
            $output = $query->fetchAll(\PDO::FETCH_ASSOC);
            return $output;
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Describe Table
     *
     * @param string $table
     * @return array
     */
    public function describeTable(string $table)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $table);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $query = $this->db->pdo->prepare("DESCRIBE `{$table}`");
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
     * Get Table Relation
     *
     * @param string $table
     * @return array
     */
    public function getTableRelation(string $table)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $table);
            $CachedString = $this->Cacher->getItem($cacheKey);
            if (!$CachedString->isHit()) {
                $sql = "SELECT  REFERENCED_TABLE_NAME,
                                REFERENCED_COLUMN_NAME,
                                (
                                    SELECT COLUMN_NAME
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE TABLE_SCHEMA = DATABASE()
                                    AND TABLE_NAME = REFERENCED_TABLE_NAME
                                    AND (DATA_TYPE LIKE '%VARCHAR%' OR DATA_TYPE LIKE '%TEXT%')
                                    LIMIT 1
                                ) IS_CHAR
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = :table_name
                            AND REFERENCED_COLUMN_NAME IS NOT NULL";
                $query = $this->db->pdo->prepare($sql);
                $query->bindParam(':table_name', $table, \PDO::PARAM_STR);
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
     * Get Groupmenu Name & Icon
     *
     * @param integer $id_groupmenu
     * @return array
     */
    public function getGroupmenuIcon(int $id_groupmenu)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $id_groupmenu);
            $CachedString = $this->Cacher->getItem($cacheKey);

            if (!$CachedString->isHit()) {
                $output = $this->db->get('m_groupmenu', ['nama', 'icon'], ['id_groupmenu' => $id_groupmenu]);
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
     * Get Urutan Menu
     *
     * @param integer $id_groupmenu
     * @return integer
     */
    public function urutanMenu(int $id_groupmenu)
    {
        try {
            $output = null;
            $cacheKey = hash('md5', $this->Sign . __METHOD__ . $id_groupmenu);
            $CachedString = $this->Cacher->getItem($cacheKey);

            if (!$CachedString->isHit()) {
                $output = $this->db->max('m_menu', 'urut', ['id_groupmenu' => $id_groupmenu, 'tipe' => 'MENU']);
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
                $this->Cacher->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return ((int) $output + 1);
        } catch (\Exception $e) {
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }

    /**
     * Generate Menu and Set Permission
     *
     * @param array $safe
     * @param array $data
     * @return void
     */
    public function generateMenus(array $safe, array $data)
    {
        /* Start transaction */
        $this->db->pdo->beginTransaction();
        try {
            /* Menu & Backend Create */
            if (in_array('c', $safe['crud'], true)) {
                /* Menu */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => $data['menu'],
                        'icon' => null,
                        'controller' => $data['className'] . ':index',
                        'url' => '/' . $data['url'],
                        'tipe' => 'MENU',
                        'aktif' => 1,
                        'urut' => $data['urutGroup']
                    ]
                );

                /* Permission Menu */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }

                /* Backen Create */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Create',
                        'icon' => null,
                        'controller' => $data['className'] . ':create',
                        'url' => '/' . $data['url'] . '/create',
                        'tipe' => 'POST',
                        'aktif' => 1,
                        'urut' => 1
                    ]
                );

                /* Permission Backend Create */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }
            }

            /* Backend Read */
            if (in_array('r', $safe['crud'], true)) {
                /* Read Data By ID */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Get By ID',
                        'icon' => null,
                        'controller' => $data['className'] . ':get',
                        'url' => '/' . $data['url'] . '/{id}',
                        'tipe' => 'GET',
                        'aktif' => 1,
                        'urut' => 2
                    ]
                );

                /* Permission Backend Read Data By ID */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }

                /* Read Data */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Read',
                        'icon' => null,
                        'controller' => $data['className'] . ':read',
                        'url' => '/' . $data['url'] . '/read',
                        'tipe' => 'POST',
                        'aktif' => 1,
                        'urut' => 3
                    ]
                );

                /* Permission Backend Read Data */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }
            }

            /* Backend Update */
            if (in_array('u', $safe['crud'], true)) {
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Update',
                        'icon' => null,
                        'controller' => $data['className'] . ':update',
                        'url' => '/' . $data['url'],
                        'tipe' => 'PUT',
                        'aktif' => 1,
                        'urut' => 4
                    ]
                );

                /* Permission Backend Update */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }
            }

            /* Backend Delete */
            if (in_array('d', $safe['crud'], true)) {
                /* Single delete */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Delete',
                        'icon' => null,
                        'controller' => $data['className'] . ':delete',
                        'url' => '/' . $data['url'],
                        'tipe' => 'DELETE',
                        'aktif' => 1,
                        'urut' => 5
                    ]
                );

                /* Permission Backend Single Delete */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }

                /* Batch delete */
                $this->db->insert(
                    'm_menu',
                    [
                        'id_groupmenu' => $data['id_groupmenu'],
                        'nama' => 'Api ' . $data['menu'] . ' Batch Delete',
                        'icon' => null,
                        'controller' => $data['className'] . ':delete',
                        'url' => '/' . $data['url'] . '/batch',
                        'tipe' => 'DELETE',
                        'aktif' => 1,
                        'urut' => 6
                    ]
                );

                /* Permission Backend Batch Delete */
                $idfirst = $this->db->id();
                foreach ($safe['id_jabatan'] as $jbt) {
                    $this->db->insert(
                        'j_menu',
                        [
                            'id_menu' => $idfirst,
                            'idrole' => $jbt
                        ]
                    );
                }
            }

            /* Commit transaction & Refresh Cache */
            $this->db->pdo->commit();
            $this->Cacher->deleteItemsByTags([
                $this->TagName,
                $this->Sign . '_router',
                hash('sha256', $this->Sign . 'M_menu'),
                hash('sha256', $this->Sign . 'M_role'),
                hash('sha256', $this->Sign . 'M_groupmenu')
            ]);
        } catch (\Exception $e) {
            /* Rollback transaction on error */
            $this->db->pdo->rollBack();
            throw new \Exception($this->overrideSQLMsg($e->getMessage()));
        }
    }
}
