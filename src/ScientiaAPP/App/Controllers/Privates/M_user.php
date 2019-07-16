<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    App\Controllers\Privates
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

namespace App\Controllers\Privates;

class M_user extends \App\Controllers\PrivateController
{
    private $MODEL;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set Model */
        $this->MODEL = new \App\Models\M_user($container);
    }

    public function get($request, $response, $args)
    {
        try {
            if (!is_numeric($args['id'])) throw new \Exception('ID tidak valid!');
            $output = $this->MODEL->getByID($args['id']);
            if (!empty($output)) {
                return $this->jsonSuccess($output);
            } else {
                return $this->jsonFail('Data tidak ditemukan', [], 404);
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
        }
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    /* Send to DB */
                    $safe['password'] = $this->kripto->secure_passwd($safe['username'], $safe['password'], true);
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
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
                        'draw' => (int) (isset($safe['draw']) ? $safe['draw'] : 0),
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
            'id' => 'required|numeric',
            'idrole' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'email' => 'required|valid_email',
            'telpon' => 'required|numeric',
            'username' => 'required|alpha_numeric',
            'password' => 'min_len,8',
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $id = $safe['id'];
                    unset($safe['id']);
                    if (isset($safe['password']) && !empty($safe['password'])) {
                        $safe['password'] = $this->kripto->secure_passwd($safe['username'], $safe['password'], true);
                    }else {
                        unset($safe['password']);
                    }

                    if ($this->MODEL->update($safe, $id)) {
                        return $this->jsonSuccess('Perubahan data berhasil');
                    } else {
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
                if (!is_numeric($args['id'])) throw new \Exception('ID tidak valid!');
                $safe = $args;
            }

            /* Delete from DB */
            if ($this->MODEL->delete($safe['id'])) {
                return $this->jsonSuccess('Data berhasil dihapus');
            } else {
                throw new \Exception('Penghapusan gagal dilakukan!');
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
        }
    }

    /* Get User Profile */
    public function getProfile()
    {
        $userdata = $this->MODEL->getByID($this->user_data['ID_USER']);
        return $this->jsonSuccess($userdata);
    }

    public function updateProfile()
    {
        $gump = new \GUMP('id');
        $data = $this->param;
        $gump->validation_rules([
            'fp_nama' => 'required',
            'fp_email' => 'required|valid_email',
            'fp_telpon' => 'required|numeric',
            'fp_password' => 'min_len,8',
        ]);

        $gump->filter_rules([
            'fp_nama' => 'trim|sanitize_string',
            'fp_email' => 'trim|sanitize_email',
            'fp_telpon' => 'trim|sanitize_numbers',
            'fp_password' => 'trim|sanitize_string',
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $data = [];
                    if (isset($safe['fp_password']) && !empty($safe['fp_password'])) {
                        $data = [
                            'nama' => $safe['fp_nama'],
                            'email' => $safe['fp_email'],
                            'telpon' => $safe['fp_telpon'],
                            'password' => $this->kripto->secure_passwd($safe['username'], $safe['password'], true),
                        ];
                    } else {
                        $data = [
                            'nama' => $safe['fp_nama'],
                            'email' => $safe['fp_email'],
                            'telpon' => $safe['fp_telpon']
                        ];
                    }

                    if ($this->MODEL->update($data, $this->user_data['ID_USER'])) {
                        return $this->jsonSuccess('Perubahan data berhasil');
                    } else {
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

}
