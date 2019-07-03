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

class M_role extends \App\Plugin\DataTables
{
	/* Constructor */
	public function __construct(\Slim\Container $container)
	{
        /* Call Parent Constructor */
		parent::__construct($container);

        /* Set DataTables Variables */
		$this->set_TABLE('m_role');
		$this->set_PKEY('idrole');
		$this->set_COLUMNS(['idrole', 'nama', 'deskripsi']);
		$this->set_COLUMN_SEARCH(['nama', 'deskripsi']);
		$this->set_ORDER(['idrole' => 'DESC']);
	}

	/* Function Create */
	public function create()
	{
        $gump = new \GUMP('en');
        $gump->validation_rules(["nama" => "required", 'deskripsi' => 'required']);
        $gump->filter_rules([ "nama" => "trim|sanitize_string", "deskripsi" => "trim|sanitize_string", ]);

        try {
            //sanitize parameter
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
                    /* Save and get last_insert_id */
                    $idrole = $this->saveDb($safe);

                    /* Insert to j_menu */
                    if ($idrole !== false) {
                        /* Start transaction */
                        $this->dbpdo->pdo->beginTransaction();
                        try {
                            /* Select default access */
                            $jmenu = $this->dbpdo->select('m_menu', 'id_menu', ['url' => ['/clogin', '/clogout', '/cauth', '/cmenu']]);
                            foreach ($jmenu as $menu) {
                                $this->dbpdo->insert('j_menu', ['id_menu' => $menu, 'idrole' => $idrole]);
                            }

                            /* Commit transaction & delete old cache */
                            $this->dbpdo->pdo->commit();
                            $this->InstanceCache->deleteItemsByTags([
                                $this->sign . '_getMenus',
                                $this->sign . '_router',
                                $this->sign . '_M_role_read_'
                            ]);
                            return $this->jsonSuccess('Data berhasil ditambahkan', null, null, 201);
                        } catch (\Exception $e) {
                            /* Rollback transaction on error */
                            $this->dbpdo->pdo->rollBack();
                            throw new \Exception($e->getMessage());
                        }
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
        $gump = new \GUMP('en');
        $gump->validation_rules([
            "draw" => "numeric",
            "start" => "numeric",
            "length" => "numeric",
            "idrole" => "numeric"
        ]);

        $gump->filter_rules([
            "draw" => "sanitize_numbers",
            "start" => "sanitize_numbers",
            "length" => "sanitize_numbers",
            "nama" => "trim",
            "deskripsi" => "trim"
        ]);

        try {
            //sanitize parameter
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
            }else {
                try {
                    /* Check Cache */
                    $output = [];
                    $opsional = (isset($safe["opsional"]) ? json_encode($safe["opsional"]) : null);
                    $search = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
                    $length = (isset($safe['length']) ? $safe['length'] : null);
                    $start = (isset($safe['start']) ? $safe['start'] : null);
                    $ckey = hash("md5", "M_role" . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
                    $CachedString = $this->InstanceCache->getItem($ckey);
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
                            "data" => $data,
                            "recordsTotal" => $this->count_all($safe),
                            "recordsFiltered" => $this->count_filtered($safe)
                        ];

                        $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_M_role_read_');
                        $this->InstanceCache->save($CachedString);
                    } else {
                        $output = $CachedString->get();
                    }

                    //send back draw
                    $output["draw"] = (int) (isset($safe["draw"]) ? $safe["draw"] : 0);
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
        $gump = new \GUMP('en');
        $data = array_merge($this->param, $args);
        $gump->validation_rules(["id" => "required|numeric",]);
        $gump->filter_rules([
            "id" => "sanitize_numbers",
            "nama" => "trim|sanitize_string",
            "deskripsi" => "trim|sanitize_string",
        ]);

        try {
            //sanitize parameter
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
            }else {
                    try {
                        /* Prepare vars */
                        $where = [$this->PKEY => $safe['id']];
                        unset($safe['id']);

                        /* Send to DB */
                        if ($this->updateDb($safe, $where)) {
                            $this->InstanceCache->deleteItemsByTags([
                                $this->sign . '_getMenus',
                                $this->sign . '_router',
                                $this->sign . '_M_role_read_'
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
        $path = explode('/', $request->getUri()->getPath());

        /** Batch delete */
        if (trim(end($path)) == 'batch') {
            if (!is_array($this->param['id'])) throw new \Exception('ID tidak valid!');
            if (in_array(false, array_map('is_numeric', $this->param['id']))) throw new \Exception('ID tidak valid!');
            $data = $this->param;
        }
        /** Single delete */
        else {
            $data = $args;
        }

        $gump = new \GUMP();
        $gump->validation_rules(["id" => "required|numeric"]);
        $gump->filter_rules([ "id" => "sanitize_numbers", ]);

        try {
            //sanitize parameter
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
                    /* Delete from DB */
                    if ($this->deleteDb($safe['id'])) {
                        $this->InstanceCache->deleteItemsByTags([
                            $this->sign . '_getMenus',
                            $this->sign . '_router',
                            $this->sign . '_M_role_read_'
                        ]);
                        return $this->jsonSuccess('Data berhasil dihapus');
                    } else {
                        throw new \Exception('Penghapusan gagal dilakukan!');
                    }
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
                }
            }

        } catch (\Exception $e) {
            return $this->jsonFail('There was a missing or invalid parameter in the request', ['error' => $e->getMessage()]);
        }
	}

}
