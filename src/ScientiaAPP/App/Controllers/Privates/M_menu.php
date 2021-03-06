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

class M_menu extends \App\Controllers\PrivateController
{
    private $MODEL, $JMENU;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set Model */
        $this->MODEL = new \App\Models\M_menu($container);
        $this->JMENU = new \App\Models\J_menu($container);
    }

    /* Get Data By Id */
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
            'id_groupmenu' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'url' => 'required',
            'tipe' => 'required|alpha',
            'aktif' => 'numeric',
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    /* Send to DB */
                    $safe['aktif'] = (isset($safe['aktif'])) ? (($safe['aktif'] == 1) ? 1 : 0) : 0;
                    $safe['is_public'] = (isset($safe['is_public'])) ? (($safe['is_public'] == 1) ? 1 : 0) : 0;
                    $safe['url'] = '/' . trim($safe['url'], '/');
                    $safe['controller'] = ($safe['tipe'] == 'MENU') ? null:ucfirst($safe['controller']);

                    if ($lastID = $this->MODEL->create($safe)) {
                        return $this->jsonSuccess('Data berhasil ditambahkan', ['id' => $lastID], null, 201);
                    } else {
                        throw new \Exception('Penyimpanan gagal dilakukan!');
                    }
                } catch (\Exception $e) {
                    return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
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
            'id_groupmenu' => 'required|numeric',
            'nama' => 'required|alpha_space',
            'url' => 'required',
            'tipe' => 'required|alpha',
            'aktif' => 'numeric',
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $id = $safe['id'];
                    unset($safe['id']);
                    $safe['url'] = '/' . trim($safe['url'], '/');
                    $safe['aktif'] = (isset($safe['aktif'])) ? (($safe['aktif'] == 1) ? 1 : 0) : 0;
                    $safe['controller'] = ($safe['tipe'] == 'MENU') ? null : ucfirst($safe['controller']);
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
                    $this->logger->error(__METHOD__, ['USER_REQUEST' => $this->user_data['USERNAME'], 'INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                try {
                    $result = $this->MODEL->getMenuRole($safe['idmenu']);
                    return $this->jsonSuccess($result);
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
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
        $gump->validation_rules(['idmenu' => 'required|numeric']);
        $gump->filter_rules(['ID_ROLE' => 'trim|sanitize_numbers', 'idmenu' => 'trim|sanitize_numbers',]);

        try {
            /* Sanitize parameter */
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if (isset($this->param['ID_ROLE'])) {
                if (in_array(false, array_map('is_numeric', $this->param['ID_ROLE']))) throw new \Exception('ID_ROLE tidak valid!');
            }

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
                    $this->JMENU->setPermission($this->param, $safe['idmenu']);
                    return $this->jsonSuccess('Permission updated!');
                } catch (\Exception $e) {
                    return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Invalid Request', ['error' => $e->getMessage()]);
        }
    }
}
