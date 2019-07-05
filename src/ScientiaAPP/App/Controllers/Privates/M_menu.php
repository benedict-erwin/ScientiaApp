<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers\Privates;

class M_menu extends \App\Plugin\DataTables
{
    /* Declare Variable */
    private $safe;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set DataTables Variables */
        $this->set_TABLE('m_menu');
        $this->set_PKEY('id_menu');
        $this->set_COLUMNS([
            'id_menu',
            'id_groupmenu',
            'nama',
            'icon',
            'url',
            'controller',
            'tipe',
            'aktif',
            'urut',
            'is_public'
        ]);
        $this->set_COLUMN_SEARCH([
            'nama',
            'url',
            'controller',
            'tipe'
        ]);
        $this->set_ORDER([ 'id_menu'=> 'DESC' ]);
    }

    /* Function Create */
    public function create()
    {
        $gump = new \GUMP('id');
        $gump->validation_rules([
            'id_groupmenu' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'url' => 'required',
            'controller' => 'required',
            'tipe' => 'required|alpha',
            'aktif' => 'required|numeric',
            'is_public' => 'required|numeric',
        ]);
        $gump->filter_rules([
            'id_groupmenu' => 'trim|sanitize_numbers',
            'nama' => 'trim|sanitize_string',
            'icon' => 'trim|sanitize_string',
            'url' => 'trim|sanitize_string',
            'controller' => 'trim|sanitize_string',
            'tipe' => 'trim|sanitize_string',
            'aktif' => 'trim|sanitize_numbers',
            'urut' => 'trim|sanitize_numbers',
            'is_public' => 'trim|sanitize_numbers',
        ]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    /* Send to DB */
                    $safe['aktif'] = (isset($safe['aktif'])) ? (($safe['aktif'] == 1) ? 1 : 0) : 0;
                    $safe['is_public'] = (isset($safe['is_public'])) ? (($safe['is_public'] == 1) ? 1 : 0) : 0;
                    $safe['url'] = '/' . trim($safe['url'], '/');
                    $safe['controller'] = ucfirst($safe['controller']);

                    /* Check if router exists */
                    $isExist = $this->dbpdo->count('m_menu', [
                        'url' => $safe['url'],
                        'tipe' => $safe['tipe'],
                        'controller' => $safe['controller'],
                    ]);

                    if ($isExist > 0) {
                        throw new \Exception('Menu sudah tersedia di database!');
                    }

                    if ($this->saveDb($safe) !== false) {
                        $this->InstanceCache->deleteItemsByTags([
                            $this->sign . '_getMenus_',
                            $this->sign . '_router',
                            $this->sign . '_M_menu_read_',
                            $this->sign . '_getPermission_',
                            $this->sign . '_getAuthMenu_',
                            $this->sign . '_CRUDGenerator_read_menu'
                        ]);
                        return $this->jsonSuccess('Data berhasil ditambahkan', null, null, 201);
                    } else {
                        throw new \Exception('Penyimpanan gagal dilakukan!');
                    }
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }

    /* Function Read */
    public function read()
    {
        $gump = new \GUMP('id');
        $gump->validation_rules([
            'draw' => 'numeric',
            'start' => 'numeric',
            'length' => 'numeric',
        ]);

        $gump->filter_rules([
            'draw' => 'sanitize_numbers',
            'start' => 'sanitize_numbers',
            'length' => 'sanitize_numbers',
            'search' => 'trim|sanitize_string',
            'opsional' => 'trim|sanitize_string',
        ]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    /* Check Cache */
                    $output = [];
                    $opsional = (isset($safe['opsional']) ? json_encode($safe['opsional']) : null);
                    $search = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
                    $length = (isset($safe['length']) ? $safe['length'] : null);
                    $start = (isset($safe['start']) ? $safe['start'] : null);
                    $ckey = hash('md5', 'M_menu' . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
                    $CachedString = $this->InstanceCache->getItem($ckey);

                    /* If not in Cache */
                    if (is_null($CachedString->get())) {
                        /* Execute DataTables */
                        $data = [];
                        $list = $this->get_datatables($safe);
                        $no = (int) $safe['start'];
                        foreach ($list as $cols) {
                            $no++;
                            $cols['no'] = $no;
                            $data[] = $cols;
                        }

                        $output = [
                            'recordsTotal' => $this->count_all($safe),
                            'recordsFiltered' => $this->count_filtered($safe),
                            'data' => $data
                        ];

                        $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_M_menu_read_');
                        $this->InstanceCache->save($CachedString);
                    } else {
                        /* Get data from Cache */
                        $output = $CachedString->get();
                    }

                    //send back draw
                    $output['draw'] = (int) (isset($safe['draw']) ? $safe['draw'] : 0);
                    return $this->jsonSuccess($output);
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }

    /* Function Update */
    public function update($request, $response, $args)
    {
        $gump = new \GUMP('id');
        $data = array_merge($this->param, $args);
        $gump->validation_rules([
            'id_groupmenu' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'url' => 'required',
            'controller' => 'required',
            'tipe' => 'required|alpha',
            'aktif' => 'required|numeric',
            'is_public' => 'required|numeric',
        ]);
        $gump->filter_rules([
            'id_groupmenu' => 'trim|sanitize_numbers',
            'nama' => 'trim|sanitize_string',
            'icon' => 'trim|sanitize_string',
            'url' => 'trim|sanitize_string',
            'controller' => 'trim|sanitize_string',
            'tipe' => 'trim|sanitize_string',
            'aktif' => 'trim|sanitize_numbers',
            'urut' => 'trim|sanitize_numbers',
            'is_public' => 'trim|sanitize_numbers',
        ]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($data);
            $safe = $gump->run($data);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $safe['url'] = '/' . trim($safe['url'], '/');
                    $where = [$this->PKEY => $safe['id']];
                    unset($safe['id']);

                    /* Send to DB */
                    if ($this->updateDb($safe, $where)) {
                        //remove old chace
                        $this->InstanceCache->deleteItemsByTags([
                            $this->sign . '_getMenus_',
                            $this->sign . '_router',
                            $this->sign . '_M_menu_read_',
                            $this->sign . '_getPermission_',
                            $this->sign . '_getAuthMenu_',
                            $this->sign . '_CRUDGenerator_read_menu'
                        ]);
                        return $this->jsonSuccess('Perubahan data berhasil');
                    } else {
                        throw new \Exception('Perubahan gagal dilakukan!');
                    }
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }

    /* Function Delete */
    public function delete($request, $response, $args)
    {
        /** Path variable  */
        $path = explode('/', $request->getUri()->getPath());

        /** Batch delete */
        if (trim(end($path)) == 'batch') {
            if (!is_array($this->param['id'])) throw new \Exception('ID tidak valid!');
            if (in_array(false, array_map('is_numeric', $this->param['id']))) throw new \Exception('ID tidak valid!');
            $safe = $this->param;
        } else {
            /** Single delete */
            if (!is_numeric($args['id'])) throw new \Exception('ID tidak valid!');
            $safe = $args;
        }

        try {
            /* Delete from DB */
            if ($this->deleteDb($safe['id'])) {
                $this->InstanceCache->deleteItemsByTags([
                    $this->sign . '_getMenus_',
                    $this->sign . '_router',
                    $this->sign . '_M_menu_read_',
                    $this->sign . '_getPermission_',
                    $this->sign . '_getAuthMenu_',
                    $this->sign . '_CRUDGenerator_read_menu'
                ]);
                return $this->jsonSuccess('Data berhasil dihapus');
            } else {
                throw new \Exception('Penghapusan gagal dilakukan!');
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Get Jabatan join j_menu */
    public function jabatanMenu()
    {
        $gump = new \GUMP('id');
        $gump->validation_rules(['idmenu' => 'required|numeric']);
        $gump->filter_rules(['idmenu' => 'trim|sanitize_numbers',]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $sql = 'SELECT a.idrole, a.deskripsi, c.idrole ID_ROLE
					FROM m_role a
					LEFT JOIN (
						SELECT b.id_menu, b.idrole
						FROM j_menu b
						WHERE b.id_menu=:idmenu
					) c ON a.idrole=c.idrole
					ORDER BY a.idrole DESC';
                    $query = $this->dbpdo->pdo->prepare($sql);
                    $query->bindParam(':idmenu', $safe['idmenu'], \PDO::PARAM_INT);
                    $query->execute();
                    $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                    return $this->jsonSuccess($result);
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }

    /* Permission setup - j_menu */
    public function setPermission()
    {
        $gump = new \GUMP('id');
        $gump->validation_rules(['ID_ROLE' => 'required', 'idmenu' => 'required|numeric']);
        $gump->filter_rules(['ID_ROLE' => 'trim|sanitize_numbers', 'idmenu' => 'trim|sanitize_numbers',]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if (!is_array($this->param['ID_ROLE'])) throw new \Exception('ID_ROLE tidak valid!');
            if (in_array(false, array_map('is_numeric', $this->param['ID_ROLE']))) throw new \Exception('ID_ROLE tidak valid!');

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    /* Start transaction */

                    $this->dbpdo->pdo->beginTransaction();
                    try {
                        $idmenu = $safe['idmenu'];
                        $this->dbpdo->delete('j_menu', ['id_menu' => $idmenu]);
                        if (array_key_exists('ID_ROLE', $this->param)) {
                            foreach ($this->param['ID_ROLE'] as $jbt) {
                                $this->dbpdo->insert('j_menu', ['id_menu' => $idmenu, 'idrole' => $jbt]);
                            }
                        }

                        /* Commit transaction & Refresh Cache */
                        $this->InstanceCache->deleteItemsByTags([
                            $this->sign . '_getMenus_',
                            $this->sign . '_router',
                            $this->sign . '_M_menu_read_',
                            $this->sign . '_getPermission_',
                            $this->sign . '_getAuthMenu_',
                            $this->sign . '_CRUDGenerator_read_menu'
                        ]);
                        $this->dbpdo->pdo->commit();
                        return $this->jsonSuccess('Permission updated!');
                    } catch (\Exception $e) {
                        /* Logger */
                        if ($this->container->get('settings')['mode'] != 'production') {
                            $this->logger->addError(get_class($this) . '->' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'SQL' => $this->dbpdo->log()]);
                        }

                        /* Rollback transaction on error */
                        $this->dbpdo->pdo->rollBack();
                        throw new \Exception($e->getMessage());
                    }
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }
}
