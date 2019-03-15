<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controller;

class M_jabatan extends \App\Plugin\DataTables
{
	/* Declare variable */
	private $safe;

    /* Constructor */
	public function __construct(\Slim\Container $container)
	{
        /* Call Parent Constructor */
		parent::__construct($container);

        /* Set DataTables Variables */
		$this->set_TABLE('m_jabatan');
		$this->set_PKEY('idjabatan');
		$this->set_COLUMNS(['idjabatan', 'nama', 'deskripsi']);
		$this->set_COLUMN_SEARCH(['nama', 'deskripsi']);
		$this->set_ORDER(['idjabatan' => 'DESC']);

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
			"idjabatan" => "numeric"
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
			$gump->xss_clean($request);
			$this->safe = $gump->run($request);

			if ($this->safe === false) {
				$ers = $gump->get_errors_array();
				$err = implode(', ', array_values($ers));

				/* Logger */
				if ($this->container->get('settings')['mode'] != 'production') {
					$this->logger->addError(get_class($this) . '->' .__FUNCTION__, ['USER_REQUEST'=>$this->user_data['USERNAME'], 'INFO'=>$ers]);
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
		if ($this->safe) {
			try {
				/* Prepare vars */
				$data['nama'] = $this->safe['nama'];
				$data['deskripsi'] = $this->safe['deskripsi'];

				/* Save and get last_insert_id */
				$idjabatan = $this->saveDb($data);

				/* Insert to j_menu */
				if ($idjabatan !== false) {
					/* Start transaction */
					$this->dbpdo->pdo->beginTransaction();
					try {
						/* Select default access */
						$jmenu = $this->dbpdo->select('m_menu', 'id_menu', ['url' => ['/clogin', '/clogout', '/cauth', '/cmenu']]);
						foreach ($jmenu as $menu) {
							$this->dbpdo->insert('j_menu', ['id_menu' => $menu, 'idjabatan' => $idjabatan]);
						}

						/* Commit transaction & delete old cache */
						$this->dbpdo->pdo->commit();
						$this->InstanceCache->deleteItemsByTags([
                            $this->sign . '_getMenus',
                            $this->sign . '_router',
                            $this->sign . '_M_jabatan_read_'
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
	}

	/* Function Read */
	public function read()
	{
		if ($this->safe) {
			try {
				/* Check Cache */
                $output = [];
                $opsional = (isset($this->safe["opsional"]) ? json_encode($this->safe["opsional"]):null);
                $search = (isset($this->safe['search']['value']) ? $this->safe['search']['value']:null);
                $length = (isset($this->safe['length']) ? $this->safe['length']:null);
                $start = (isset($this->safe['start']) ? $this->safe['start']:null);
				$ckey = hash("md5", "M_jabatan" . $this->user_data['ID_JABATAN'] . $start . $length . $opsional . $search);
				$CachedString = $this->InstanceCache->getItem($ckey);
				if (is_null($CachedString->get())) {
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
						"data" => $data,
						"recordsTotal" => $this->count_all($this->safe),
						"recordsFiltered" => $this->count_filtered($this->safe)
					];

					$CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign .'_M_jabatan_read_');
					$this->InstanceCache->save($CachedString);
				} else {
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
		if ($this->safe) {
			try {
				/* Prepare vars */
				$where = [$this->PKEY => $this->safe['pKey']];
                unset($this->safe['pKey']);

				/* Send to DB */
				if ($this->updateDb($this->safe, $where)) {
                    $this->InstanceCache->deleteItemsByTags([
                        $this->sign . '_getMenus',
                        $this->sign . '_router',
                        $this->sign . '_M_jabatan_read_'
                    ]);
					return $this->jsonSuccess('Perubahan data berhasil');
				} else {
					throw new \Exception('Perubahan gagal dilakukan!');
				}
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
		}
	}

	/* Function Delete */
	public function delete()
	{
		if ($this->safe) {
			try {
                /* Delete from DB */
                $this->set_TABLE('j_menu');
				if ($this->deleteBy(['idjabatan' => $this->safe['pKey']])) {
                    $this->set_TABLE('m_jabatan');
					$this->deleteDb($this->safe['pKey']);
                    $this->InstanceCache->deleteItemsByTags([
                        $this->sign . '_getMenus',
                        $this->sign . '_router',
                        $this->sign . '_M_jabatan_read_'
                    ]);
					return $this->jsonSuccess('Data berhasil dihapus');
				} else {
					throw new \Exception('Penghapusan gagal dilakukan!');
				}
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
		}
	}

}
