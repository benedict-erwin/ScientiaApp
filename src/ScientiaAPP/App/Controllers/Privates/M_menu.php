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

        /* Sanitize Param */
        $this->sanitizer($this->param);
    }

    /**
     * Parameter Sanitizer
     *
     * @param array $request
     * @return void
     */
    private function sanitizer(array $request)
    {
        $gump = new \GUMP();
        $gump->validation_rules([
            "draw" => "numeric",
            "start" => "numeric",
            "length" => "numeric",
            "id_menu" => "numeric",
            "id_groupmenu" => "numeric",
            "urut" => "numeric",
            "aktif" => "numeric"
        ]);

        $gump->filter_rules([
            "draw" => "trim|sanitize_numbers",
            "start" => "trim|sanitize_numbers",
            "length" => "trim|sanitize_numbers",
            "nama" => "trim",
            "icon" => "trim"
        ]);

        try {
            //sanitize parameter
            $gump->xss_clean($request);
            $this->safe = $gump->run($request);

            if ($this->safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(__FUNCTION__, ['USER_REQUEST'=>$this->user_data['USERNAME'],  'INFO'=>$ers]);
                }
                throw new \Exception($err);
            } else {
                return $this->safe;
            }
        } catch (\Exception $e) {
            return $this->jsonFail('There was a missing or invalid parameter in the request', ['error' => $e->getMessage()]);
        }
    }

    /* Function Create */
    public function create()
    {
        if ($this->safe){
			try {
                /* Send to DB */
                $this->safe['aktif'] = (isset($this->safe['aktif'])) ? (($this->safe['aktif'] == 1) ? 1:0):0;
                $this->safe['url'] = "/" . trim($this->safe['url'], "/");
                if ($this->saveDb($this->safe) !== false) {
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
    }

    /* Function Read */
    public function read()
    {
        if ($this->safe){
            try  {
                /* Check Cache */
                $output = [];
                $opsional = (isset($this->safe["opsional"]) ? json_encode($this->safe["opsional"]):null);
                $search = (isset($this->safe['search']['value']) ? $this->safe['search']['value']:null);
                $length = (isset($this->safe['length']) ? $this->safe['length']:null);
                $start = (isset($this->safe['start']) ? $this->safe['start']:null);
                $ckey = hash("md5", "M_menu" . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
                $CachedString = $this->InstanceCache->getItem($ckey);

                /* If not in Cache */
                if(is_null($CachedString->get())){
                     /* Execute DataTables */
                    $data = [];
                    $list = $this->get_datatables($this->safe);
                    $no = (int)$this->safe['start'];
                    foreach ($list as $cols) {
                        $no++;
                        $cols['no'] = $no;
                        $data[] = $cols;
                    }

                    $output = [
                        "recordsTotal" => $this->count_all($this->safe),
                        "recordsFiltered" => $this->count_filtered($this->safe),
                        "data" => $data
                    ];

                    $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . "_M_menu_read_");
                    $this->InstanceCache->save($CachedString);
                } else {
                    /* Get data from Cache */
                    $output = $CachedString->get();
                }

                //send back draw
                $output["draw"] = (int)(isset($this->safe["draw"]) ? $this->safe["draw"] : 0);
				return $this->jsonSuccess($output);
            } catch (\Exception $e) {
                return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
            }
        }
    }

    /* Function Update */
    public function update()
    {
        if ($this->safe){
            try  {
                $this->safe['aktif'] = (isset($this->safe['aktif'])) ? (($this->safe['aktif'] == 1) ? 1:0):0;
                $this->safe['url'] = "/" . trim($this->safe['url'], "/");
                $where = [$this->PKEY => $this->safe['id']];
                unset($this->safe['id']);

                /* Send to DB */
				if ($this->updateDb($this->safe, $where)) {
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
				}else{
					throw new \Exception('Perubahan gagal dilakukan!');
				}
            } catch (\Exception $e) {
                return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
            }
        }
    }

    /* Function Delete */
    public function delete($request, $response, $args)
    {
        if ($this->safe){
			try {
                $id = null;
                $path = explode('/', $request->getUri()->getPath());

                /** Batch delete */
                if (trim(end($path)) == 'batch') {
                    if (!is_array($this->safe['id'])) throw new \Exception('ID tidak valid!');
                    if (in_array(false, array_map('is_numeric', $this->safe['id']))) throw new \Exception('ID tidak valid!');
                    $id = $this->safe['id'];
                } else {
                    /** Single delete */
                    $id = $args['id'];
                }

				/* Send to DB */
				if ($this->deleteDb($id)) {
                    //remove old chace
                    $this->InstanceCache->deleteItemsByTags([
                        $this->sign . '_getMenus_',
                        $this->sign . '_router',
                        $this->sign . '_M_menu_read_',
                        $this->sign . '_getPermission_',
                        $this->sign . '_getAuthMenu_',
                        $this->sign . '_CRUDGenerator_read_menu'
                    ]);
					return $this->jsonSuccess('Data berhasil dihapus');
				}else{
					throw new \Exception('Penghapusan gagal dilakukan!');
				}
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
        }
    }

    /* Get Jabatan join j_menu */
    public function jabatanMenu()
    {
        if ($this->safe){
			try {
                $sql = "SELECT a.idrole, a.deskripsi, c.idrole ID_ROLE
					FROM m_role a
					LEFT JOIN (
						SELECT b.id_menu, b.idrole
						FROM j_menu b
						WHERE b.id_menu=:idmenu
					) c ON a.idrole=c.idrole
					ORDER BY a.idrole DESC";
                $query = $this->dbpdo->pdo->prepare($sql);
                $query->bindParam(':idmenu', $this->safe['idmenu'], \PDO::PARAM_INT);
                $query->execute();
                $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                return $this->jsonSuccess($result);
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
        }
    }

    /* Update j_menu */
    public function setPermission()
    {
        if ($this->safe){
			try {
                /* Start transaction */
                $this->dbpdo->pdo->beginTransaction();
                try {
                    $idmenu = $this->safe['idmenu'];
                    $this->dbpdo->delete('j_menu', ['id_menu' => $idmenu]);
                    if (array_key_exists('ID_ROLE', $this->safe)) {
                        foreach ( $this->safe['ID_ROLE'] as $jbt) {
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
                    /* Rollback transaction on error */
                    $this->dbpdo->pdo->rollBack();
                    throw new \Exception($e->getMessage());
                }
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
		}
    }
}
