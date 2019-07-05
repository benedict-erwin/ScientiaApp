<?php
/**
* @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
* @package    ScientiaAPP/App/Controller
* @author     Benedict E. Pranata
* @copyright  (c) 2019 benedict.erwin@gmail.com
* @created    on Tue Jan 15 2019
* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
*/

namespace App\Controllers\Privates;

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
		$this->set_COLUMNS(['id_config' , 'name' , 'value' , 'description', 'scope']);
		$this->set_COLUMN_SEARCH(['name' , 'value' , 'description']);
		$this->set_ORDER(['id_config'=> 'DESC' ]);
		$this->set_CASE_SENSITIVE(false);
	}

	/* Function Create */
	public function create()
	{
        $gump = new \GUMP('id');
        $gump->validation_rules([
            'name' => 'required|alpha_dash',
            'value' => 'required',
            'description' => 'required',
            'scope' => 'required|numeric',
        ]);
        $gump->filter_rules([
            'name' => 'trim|sanitize_string',
            'value' => 'trim|sanitize_string',
            'description' => 'trim|sanitize_string',
            'scope' => 'trim|sanitize_numbers',
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
                    if ($this->saveDb($safe) !== false) {
                        $this->InstanceCache->deleteItemsByTag($this->sign . "_M_config_read_");
                        //remove old chace
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
                    $opsional = (isset($safe["opsional"]) ? json_encode($safe["opsional"]) : null);
                    $search = (isset($safe['search']['value']) ? $safe['search']['value'] : null);
                    $length = (isset($safe['length']) ? $safe['length'] : null);
                    $start = (isset($safe['start']) ? $safe['start'] : null);
                    $ckey = hash("md5", "M_config" . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
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
                            "recordsTotal" => $this->count_all($safe),
                            "recordsFiltered" => $this->count_filtered($safe),
                            "data" => $data
                        ];

                        $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . "_M_config_read_");
                        $this->InstanceCache->save($CachedString);
                    } else {
                        /* Get data from Cache */
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
        $gump = new \GUMP('id');
        $data = array_merge($this->param, $args);
        $gump->validation_rules([
            'id' => 'required|numeric',
            'name' => 'required|alpha_dash',
            'value' => 'required',
            'description' => 'required',
            'scope' => 'required|numeric',
        ]);
        $gump->filter_rules([
            'id' => 'sanitize_numbers',
            'name' => 'trim|sanitize_string',
            'value' => 'trim|sanitize_string',
            'description' => 'trim|sanitize_string',
            'scope' => 'trim|sanitize_numbers',
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
                    /* Prepare vars */
                    $where = [$this->PKEY => $safe['id']];
                    unset($safe['id']);

                    /* Send to DB */
                    if ($this->updateDb($safe, $where)) {
                        //remove old chace
                        $this->InstanceCache->deleteItemsByTag($this->sign . "_M_config_read_");
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
                $this->InstanceCache->deleteItemsByTag($this->sign . "_M_config_read_");
                return $this->jsonSuccess('Data berhasil dihapus');
            } else {
                throw new \Exception('Penghapusan gagal dilakukan!');
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

}
