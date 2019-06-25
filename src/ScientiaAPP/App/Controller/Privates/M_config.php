<?php
/**
* @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
* @package    ScientiaAPP/App/Controller
* @author     Benedict E. Pranata
* @copyright  (c) 2019 benedict.erwin@gmail.com
* @created    on Tue Jan 15 2019
* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
*/

namespace App\Controller\Privates;

class M_config extends \App\Plugin\DataTables
{
	/* Declare Variable */
	private $safe;

	/* Constructor */
	public function __construct(\Slim\Container $container)
	{
		/* Call Parent Constructor */
		parent::__construct($container);

		/* Set DataTables Variables */
		$this->set_TABLE('m_config');
		$this->set_PKEY('id_config');
		$this->set_COLUMNS(['id_config' , 'name' , 'value' , 'description']);
		$this->set_COLUMN_SEARCH(['name' , 'value' , 'description']);
		$this->set_ORDER(['id_config'=> 'DESC' ]);
		$this->set_CASE_SENSITIVE(false);

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
			"id_config" => "numeric"
		]);

		$gump->filter_rules([
			"draw" => "sanitize_numbers",
			"start" => "sanitize_numbers",
			"length" => "sanitize_numbers",
			"id_config" => "sanitize_numbers",
			"name" => "trim",
			"value" => "trim",
			"description" => "trim"
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
		if ($this->safe){
			try {
				/* Send to DB */
				if ($this->saveDb($this->safe) !== false) {
					$this->InstanceCache->deleteItemsByTag($this->sign ."_M_config_read_");
					//remove old chace
					return $this->jsonSuccess('Data berhasil ditambahkan', null, null, 201);
				}else{
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
			try {
				/* Check Cache */
                $output = [];
                $opsional = (isset($this->safe["opsional"]) ? json_encode($this->safe["opsional"]):null);
                $search = (isset($this->safe['search']['value']) ? $this->safe['search']['value']:null);
                $length = (isset($this->safe['length']) ? $this->safe['length']:null);
                $start = (isset($this->safe['start']) ? $this->safe['start']:null);
				$ckey = hash("md5", "M_config" . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
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

					$CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . "_M_config_read_");
					$this->InstanceCache->save($CachedString);
				} else {
					/* Get data from Cache */
					$output = $CachedString->get();
				}

                //send back draw
                $output["draw"] = (int)(isset($this->safe["draw"]) ? $this->safe["draw"] : 0);
				return $this->jsonSuccess($output);
			}  catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
		}
	}

	/* Function Update */
	public function update()
	{
		if ($this->safe){
			try {
				/* Prepare vars */
				$where = [$this->PKEY => $this->safe['pKey']];
                unset($this->safe['pKey']);

				/* Send to DB */
				if ($this->updateDb($this->safe, $where)) {
					//remove old chace
					$this->InstanceCache->deleteItemsByTag($this->sign . "_M_config_read_");
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
	public function delete()
	{
		if ($this->safe){
			try {
				/* Send to DB */
				if ($this->deleteDb($this->safe['pKey'])) {
					//remove old chace
					$this->InstanceCache->deleteItemsByTag($this->sign . "_M_config_read_");
					return $this->jsonSuccess('Data berhasil dihapus');
				}else{
					throw new \Exception('Penghapusan gagal dilakukan!');
				}
			} catch (\Exception $e) {
				return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
			}
		}
	}

}
