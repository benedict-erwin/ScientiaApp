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

class M_user extends \App\Plugin\DataTables
{
    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set DataTables Variables */
        $this->set_TABLE('m_user');
        $this->set_PKEY('iduser');
        $this->set_COLUMNS(['iduser' , 'nama' , 'email' , 'idrole' , 'telpon' , 'lastlogin' , 'username']);
        $this->set_COLUMN_SEARCH(['nama' , 'email' , 'telpon' , 'username']);
        $this->set_COLUMN_ORDER(['iduser', null, 'nama' , 'email', 'telpon', null, 'username']);
    }

	/* Function Create */
	public function create()
	{
        $gump = new \GUMP('id');
        $gump->validation_rules([
            'idrole' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'email' => 'required|valid_email',
            'telpon' => 'required|numeric',
            'username' => 'required|alpha_numeric',
            'password' => 'required|min_len,8',
        ]);

        $gump->filter_rules([
            'idrole' => 'sanitize_numbers',
            'nama' => 'trim|sanitize_string',
            'email' => 'trim|sanitize_email',
            'telpon' => 'trim|sanitize_numbers',
            'username' => 'trim|sanitize_string',
            'password' => 'trim|sanitize_string',
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
                    $safe['password'] = $this->kripto->secure_passwd($safe['username'], $safe['password'], true);
                    if ($this->saveDb($safe) !== false) {
                        $this->InstanceCache->deleteItemsByTag($this->sign . '_M_user_read_');
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
            'opsional' => 'trim|sanitize_numbers',
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
                    $ckey = hash('md5', 'M_user' . $this->user_data['ID_ROLE'] . $start . $length . $opsional . $search);
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
                            'data' => $data,
                            'recordsTotal' => $this->count_all($safe),
                            'recordsFiltered' => $this->count_filtered($safe)
                        ];

                        $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_M_user_read_');
                        $this->InstanceCache->save($CachedString);
                    } else {
                        $output = $CachedString->get();
                    }

                    /* Send back draw */
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
            'id' => 'required|numeric',
            'idrole' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'email' => 'required|valid_email',
            'telpon' => 'required|numeric',
            'username' => 'required|alpha_numeric',
            'password' => 'required|min_len,8',
        ]);

        $gump->filter_rules([
            'id' => 'sanitize_numbers',
            'idrole' => 'sanitize_numbers',
            'nama' => 'trim|sanitize_string',
            'email' => 'trim|sanitize_email',
            'telpon' => 'trim|sanitize_numbers',
            'username' => 'trim|sanitize_string',
            'password' => 'trim|sanitize_string',
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
                    if (isset($safe['password'])) {
                        $safe['password'] = $this->kripto->secure_passwd($safe['username'], $safe['password'], true);
                    }
                    $where = [$this->PKEY => $safe['id']];
                    unset($safe['id']);

                    /* Send to DB */
                    if ($this->updateDb($safe, $where)) {
                        $this->InstanceCache->deleteItemsByTag($this->sign . '_M_user_read_');
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
                $this->InstanceCache->deleteItemsByTag($this->sign . '_M_user_read_');
                return $this->jsonSuccess('Data berhasil dihapus');
            } else {
                throw new \Exception('Penghapusan gagal dilakukan!');
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
	}

}
