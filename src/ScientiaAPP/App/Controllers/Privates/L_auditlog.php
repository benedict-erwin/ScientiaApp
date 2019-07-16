<?php
/**
* @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
* @package    App\Controllers\Privates
* @author     Benedict E. Pranata
* @copyright  (c) 2019 benedict.erwin@gmail.com
* @created    on Tue Jul 09 2019
* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
*/

namespace App\Controllers\Privates;

class L_auditlog extends \App\Controllers\PrivateController
{
	/* Declare Model */
	private $MODEL;

	/* Constructor */
	public function __construct(\Slim\Container $container)
	{
		/* Call Parent Constructor */
		parent::__construct($container);

		/* Set Model */
		$this->MODEL = new \App\Models\L_auditlog($container);
	}

	/* Function Get Data By ID */
	public function get($request, $response, $args)
	{
		try {
            if (!is_numeric($args['id'])) throw new \Exception('ID tidak valid!');
			$output = $this->MODEL->getByID($args['id']);
			if (!empty($output)) {
				return $this->jsonSuccess($output);
			}else {
				return $this->jsonFail('Data tidak ditemukan', [], 404);
			}
		} catch (\Exception $e) {
			return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
		}
	}

	/**
	 * Create function
	 *
	 * @return void
	 */
	public function create()
	{
		$gump = new \GUMP('id');
		$gump->validation_rules([
			"idauditlog" => "numeric",
			"iduser" => "numeric"
		]);

		$gump->filter_rules([
			"action" => "trim|sanitize_string",
			"http_method" => "trim|sanitize_string",
			"data" => "trim|sanitize_string",
			"ip_address" => "trim|sanitize_string"
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
					$this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
				}
				throw new \Exception($err);
			} else {
				try {
                    /* Send to DB */
                    if ($lastID = $this->MODEL->create($safe)) {
                        return $this->jsonSuccess('Data berhasil ditambahkan', ['id' => $lastID], null, 201);
					} else {
						throw new \Exception('Penyimpanan gagal dilakukan!');
					}
				} catch (\Exception $e) {
					return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
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
			"draw" => "numeric",
			"start" => "numeric",
			"length" => "numeric",
			"idauditlog" => "numeric",
            "iduser" => "numeric",
            "periode_start" => "date",
            "periode_end" => "date"
		]);

		$gump->filter_rules([
			"draw" => "sanitize_numbers",
			"start" => "sanitize_numbers",
			"length" => "sanitize_numbers",
			"action" => "trim|sanitize_string",
			"http_method" => "trim|sanitize_string",
			"data" => "trim|sanitize_string",
            "ip_address" => "trim|sanitize_string",
            "periode_start" => "trim",
            "periode_end" => "trim"
		]);

		try {
			/* Sanitize parameter */
			$gump->xss_clean($this->param);
			$safe = $gump->run($this->param);

			if ($safe === false) {
				$ers = $gump->get_errors_array();
				$err = implode(', ', array_values($ers) );

				/* Logger */
				if ($this->container->get('settings')['mode'] != 'production') {
					$this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
				}
				throw new \Exception($err);
			} else {
				try {
					/* Get Data */
					$data = [];
					$output = [];
					$records = $this->MODEL->read($safe);
					$no = (int) $safe['start'];
					foreach ($records['datalist'] as $cols) {
						$no++;
						$cols['no'] = $no;
						$data[] = $cols;
					}

					$output = [
						'recordsTotal' => $records['recordsTotal'],
						'recordsFiltered' => $records['recordsFiltered'],
						'data' => $data,
						'draw' => (int) (isset($safe['draw']) ? $safe['draw']: 0),
					];

					return $this->jsonSuccess($output);
				} catch (\Exception $e) {
					return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
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
			"id" => "required|numeric",
			"idauditlog" => "numeric",
			"iduser" => "numeric"
		]);

		$gump->filter_rules([
			"id" => "sanitize_numbers",
			"action" => "trim|sanitize_string",
			"http_method" => "trim|sanitize_string",
			"data" => "trim|sanitize_string",
			"ip_address" => "trim|sanitize_string"
		]);

		try {
			/* Sanitize parameter */
			$gump->xss_clean($data);
			$safe = $gump->run($data);

			if ($safe === false) {
				$ers = $gump->get_errors_array();
				$err = implode(', ', array_values($ers) );

				/* Logger */
				if ($this->container->get('settings')['mode'] != 'production') {
					$this->logger->error(__CLASS__ . ' :: ' . __FUNCTION__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
				}
				throw new \Exception($err);
			} else {
				try {
					$id = $safe['id']; unset($safe['id']);
					if ($this->MODEL->update($safe, $id)) {
						return $this->jsonSuccess('Perubahan data berhasil');
					}else {
						throw new \Exception('Tidak ada perubahan data!');
					}
				} catch (\Exception $e) {
					return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
				}
			}
		} catch (\Exception $e) {
			return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
		}
	}

	/* Function Delete */
	public function delete($request, $response, $args)
	{
		try {
			/** Path variable  */
			$path = explode('/', $request->getUri()->getPath());

			/** Batch delete */
			if (trim(end($path)) == 'batch') {
				if (!is_array($this->param['id'])) throw new \Exception('ID tidak valid!');
				if (in_array(false, array_map('is_numeric', $this->param['id']))) throw new \Exception('ID tidak valid!');
				$safe = $this->param;
			} else {
				/** Single delete */
				if (!is_numeric($args['id'])) throw new \Exception('ID t idak valid!');
				$safe = $args;
			}

			/* Delete from DB */
			if ($this->MODEL->delete($safe['id'])) {
				return $this->jsonSuccess('Data berhasil dihapus');
			}else {
				throw new \Exception('Penghapusan gagal dilakukan!');
			}
		} catch (\Exception$e) {
			return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
		}
	}
}
