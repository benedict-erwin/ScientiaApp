<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/App/Controller
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 **/

namespace App\Controller\Privates;

use App\Lib\Stringer;

class CRUDGenerator extends \App\Plugin\DataTables
{
    private $template;
    private $aproject;
    private $aauthor;
    private $acopyright;

    /**
     * Constructor
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        /* Execute parent constructor */
        parent::__construct($container);

        /* Set Header variables */
        $this->aproject = "ScientiaAPP - Web Apps Skeleton & CRUD Generator";
        $this->aauthor = "Benedict E. Pranata";
        $this->acopyright = "benedict.erwin@gmail.com";

        /* Template */
        $this->template = 'notika';
    }

    /* Index Function */
    public function index()
    {
        \GUMP::add_validator("is_ucfirst", function ($field, $input, $param = null) {
            return Stringer::is_ucfirst($input[$field]);
        }, 'Controller first character must be uppercase');

        $gump = new \GUMP();
        $gump->validation_rules([
            "action" => "required|alpha_dash",
            "controller" => "alpha_dash|is_ucfirst",
            "get_tables" => "alpha_dash",
            "generate" => "alpha|exact_len,2",
        ]);

        $gump->filter_rules([
            "action" => "trim|lower_case",
            "controller" => "trim",
            "get_tables" => "trim",
            "generate" => "trim|lower_case",
        ]);

        try {
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $err = implode(', ', array_values($gump->get_errors_array()));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(__class__ . ' :: ' . __FUNCTION__ . ' :: ' . $err);
                }
                throw new \Exception($err);
            } else {
                /* GoTo Switcher */
                return $this->switcher($safe);
            }
        } catch (\Exception $e) {
            return $this->jsonFail(null, ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Switcher */
    private function switcher($safe = [])
    {
        switch ($safe['action']) {
            case 'generate':
                /* Set DataTables Variables */
                $this->set_TABLE("m_menu");
                $this->set_PKEY('id_menu');
                $this->set_COLUMN_SEARCH(['nama', 'url', 'controller']);
                $this->set_ORDER(['id_menu' => 'DESC']);
                if ($safe['generate'] == 'bf') {
                    $safe['crud'] = ['c', 'r', 'u', 'd'];
                }
                return $this->generate($safe);
                break;
            case 'read_menu':
                /* Set DataTables Variables */
                $this->set_TABLE("m_menu");
                $this->set_PKEY('id_menu');
                $this->set_COLUMN_SEARCH(['nama', 'url', 'controller']);
                $this->set_ORDER(['id_menu' => 'DESC']);

                return $this->read_menu($safe);
                break;
            case 'get_tables':
                 /* Set DataTables Variables */
                $this->set_TABLE("m_menu");
                $this->set_PKEY('id_menu');
                $this->set_COLUMN_SEARCH(['nama', 'url', 'controller']);
                $this->set_ORDER(['id_menu' => 'DESC']);

                return $this->get_tables($safe);
                break;
            case 'get_jabatan':
                /* Set Table m_jabatan */
                $this->set_TABLE('m_jabatan');
                $this->set_PKEY('idjabatan');
                $this->set_COLUMNS(['idjabatan', 'nama', 'deskripsi']);
                $this->set_COLUMN_SEARCH(['nama', 'deskripsi']);
                $this->set_ORDER(['idjabatan' => 'DESC']);

                return $this->getJabatan($safe);
                break;
            case 'get_groupmenu':
                /* Set DataTables Variables */
                $this->set_TABLE('m_groupmenu');
                $this->set_PKEY('id_groupmenu');
                $this->set_COLUMNS(['id_groupmenu', 'nama', 'icon', 'urut', 'aktif']);
                $this->set_COLUMN_SEARCH(['nama']);
                $this->set_ORDER(['urut' => 'ASC']);

                return $this->get_groupmenu($safe);
                break;
            default:
                return $this->read_menu($safe);
                break;
        }
    }

    /* Generate BackEnd & FrontEnd */
    private function generate($safe = [])
    {
        try {
            /* Initiate variables */
            $data = [];
            $cselect = null;
            $csearch = null;
            $corder = null;
            $prikey = null;
            $input = [];
            $tbcols = [];
            $colTypeToSearch = ['char', 'text'];
            $gumpValidationRules = '';
            $gumpFilterRules = '';
            $describer = $this->describe_table($safe['get_tables']);
            $describe = $describer['describe'];
            $relation = $describer['relation'];

            /* Get table detail */
            foreach ($describe as $desc) {
                /* ColumnSelect */
                $cselect .= "\n\t\t\t'" . $desc['Field'] . "', ";

                /* DataTables Column */
                $tbcols[] = $desc['Field'];

                /* ColumnSearch */
                if (Stringer::is_like($colTypeToSearch, $desc['Type'])) {
                    $csearch .= "\n\t\t\t'" . $desc['Field'] . "', ";
                    $gumpFilterRules .= "\n\t\t\t\"" . $desc['Field'] . '" => "trim",';
                }

                /* ColumnOrder, PrimaryKey */
                if ($desc['Key'] == 'PRI') {
                    $corder = "['" . $desc['Field'] . "'=> 'DESC' ]";
                    $prikey = $desc['Field'];
                }

                /* Input */
                if ($desc['Key'] != 'PRI') {
                    $input[] = $desc['Field'];
                }

                /* Gump */
                if (Stringer::is_like(['int'], $desc['Type'])) {
                    $gumpValidationRules .= "\n\t\t\t\"" . $desc['Field'] . '" => "numeric",';
                }
            }

            /* Variable for generate backend */
            $groupmenu = $this->dbpdo->get('m_groupmenu', ['nama', 'icon'], ['id_groupmenu' => $safe['m_groupmenu']]);
            $urutGroup = $this->dbpdo->max('m_menu', 'urut', ['id_groupmenu' => $safe['m_groupmenu'], 'tipe' => 'GET']);
            $data['className'] = $safe['controller'];
            $data['tableName'] = $safe['get_tables'];
            $data['columnsSelect'] = empty($cselect) ? null : trim($cselect, ', ');
            $data['columnsSearch'] = empty($csearch) ? null : trim($csearch, ', ');
            $data['columnsOrder'] = empty($corder) ? null : $corder;
            $data['primaryKey'] = $prikey;
            $data['gump_validation'] = trim($gumpValidationRules, ', ');
            $data['gump_filter'] = trim($gumpFilterRules, ', ');
            $data['input'] = $input;
            $data['dtcols'] = $tbcols;
            $data['describe'] = $describe;
            $data['relation'] = $relation;
            $data['id_groupmenu'] = $safe['m_groupmenu'];
            $data['nama_groupmenu'] = $groupmenu['nama'];
            $data['icon_groupmenu'] = $groupmenu['icon'];
            $data['urutGroup'] = (int) $urutGroup +1;
            $data['menu'] = ucwords(strtolower($safe['menu']));
            $data['url'] = strtolower(trim($safe['url'], "/"));
            $data['crud'] = $safe['crud'];

            /* Insert to table M_menu */
            $cek = $this->dbpdo->select(
                $this->TABLE,
                'id_menu',
                [
                    'nama' => [
                        $data['menu'],
                        'Api ' . $data['menu'] . ' Create',
                        'Api ' . $data['menu'] . ' Read',
                        'Api ' . $data['menu'] . ' Update',
                        'Api ' . $data['menu'] . ' Delete',
                    ],
                    'id_groupmenu' => $data['id_groupmenu'],
                    'tipe' => ['GET', 'POST']
                ]
            );

            /* Start transaction */
            $this->dbpdo->pdo->beginTransaction();
            try {
                if (count($cek) === 0) {
                    /* Insert to table m_menu */

                    /* Backend Create */
                    if (in_array('c', $safe['crud'], true)) {
                        $this->dbpdo->insert(
                            $this->TABLE,
                            [
                                'id_groupmenu' => $data['id_groupmenu'],
                                'nama' => 'Api ' . $data['menu'] . ' Create',
                                'icon' => null,
                                'controller' => $data['className'] . ':create',
                                'url' => '/c_' . $data['url'] . '_create',
                                'tipe' => 'POST',
                                'aktif' => 1,
                                'urut' => 0
                            ]
                        );

                        /* Permission Backend Create */
                        $idfirst = $this->dbpdo->id();
                        foreach ($safe['id_jabatan'] as $jbt) {
                            $this->dbpdo->insert(
                                'j_menu',
                                [
                                    'id_menu' => $idfirst,
                                    'idjabatan' => $jbt
                                ]
                            );
                        }
                    }

                    /* Backend Read */
                    if (in_array('r', $safe['crud'], true)) {
                        $this->dbpdo->insert(
                            $this->TABLE,
                            [
                                'id_groupmenu' => $data['id_groupmenu'],
                                'nama' => 'Api ' . $data['menu'] . ' Read',
                                'icon' => null,
                                'controller' => $data['className'] . ':read',
                                'url' => '/c_' . $data['url'] . '_read',
                                'tipe' => 'POST',
                                'aktif' => 1,
                                'urut' => 0
                            ]
                        );

                        /* Permission Backend Read */
                        $idfirst = $this->dbpdo->id();
                        foreach ($safe['id_jabatan'] as $jbt) {
                            $this->dbpdo->insert(
                                'j_menu',
                                [
                                    'id_menu' => $idfirst,
                                    'idjabatan' => $jbt
                                ]
                            );
                        }
                    }

                    /* Backend Update */
                    if (in_array('u', $safe['crud'], true)) {
                        $this->dbpdo->insert(
                            $this->TABLE,
                            [
                                'id_groupmenu' => $data['id_groupmenu'],
                                'nama' => 'Api ' . $data['menu'] . ' Update',
                                'icon' => null,
                                'controller' => $data['className'] . ':update',
                                'url' => '/c_' . $data['url'] . '_update',
                                'tipe' => 'POST',
                                'aktif' => 1,
                                'urut' => 0
                            ]
                        );

                        /* Permission Backend Update */
                        $idfirst = $this->dbpdo->id();
                        foreach ($safe['id_jabatan'] as $jbt) {
                            $this->dbpdo->insert(
                                'j_menu',
                                [
                                    'id_menu' => $idfirst,
                                    'idjabatan' => $jbt
                                ]
                            );
                        }
                    }

                    /* Backend Delete */
                    if (in_array('d', $safe['crud'], true)) {
                        $this->dbpdo->insert(
                            $this->TABLE,
                            [
                                'id_groupmenu' => $data['id_groupmenu'],
                                'nama' => 'Api ' . $data['menu'] . ' Delete',
                                'icon' => null,
                                'controller' => $data['className'] . ':delete',
                                'url' => '/c_' . $data['url'] . '_delete',
                                'tipe' => 'POST',
                                'aktif' => 1,
                                'urut' => 0
                            ]
                        );

                        /* Permission Backend Delete */
                        $idfirst = $this->dbpdo->id();
                        foreach ($safe['id_jabatan'] as $jbt) {
                            $this->dbpdo->insert(
                                'j_menu',
                                [
                                    'id_menu' => $idfirst,
                                    'idjabatan' => $jbt
                                ]
                            );
                        }
                    }

                    if ($safe['generate'] == 'bf') {
                        /* Frontend */
                        $this->dbpdo->insert(
                            $this->TABLE,
                            [
                                'id_groupmenu' => $data['id_groupmenu'],
                                'nama' => $data['menu'],
                                'icon' => null,
                                'controller' => $data['className'] . ':index',
                                'url' => '/' . $data['url'],
                                'tipe' => 'GET',
                                'aktif' => 1,
                                'urut' => $data['urutGroup']
                            ]
                        );

                        /* Permission Frontend */
                        $idsecond = $this->dbpdo->id();
                        foreach ($safe['id_jabatan'] as $jbt) {
                            $this->dbpdo->insert(
                                'j_menu',
                                [
                                    'id_menu' => $idsecond,
                                    'idjabatan' => $jbt
                                ]
                            );
                        }
                    }

                    /* Error Check */
                    if (!$this->dbpdo->error()) {
                        throw new \Exception("Error on insert db!");
                    }
                }


                /* Generate PHP BackEnd */
                $phpFileName = APP_PATH . '/Controller/' . $data['className'] . '.php';
                $handle = @fopen($phpFileName, 'w') or die('Cannot open file:  ' . $phpFileName);
                $php = $this->stringPHP($data);
                if (!fwrite($handle, $php)) {
                    throw new \Exception("PHP File not generated");
                }


                if ($safe['generate'] == 'bf') {
                    /* HTML */
                    $htmlFileName = APP_PATH . '/Templates/Home/' . $this->template . '/' . str_replace(' ', '_', strtolower($data['url'])) . '.html';
                    $handleHTML = @fopen($htmlFileName, 'w') or die('Cannot open file:  ' . $htmlFileName);
                    $html = $this->stringHTML($data);
                    if (!fwrite($handleHTML, $html)) {
                        throw new Exception("HTML File not generated");
                    }

                    /* JS */
                    $jsFileName = BASE_PATH . '/public/assets/scripts/page/' . str_replace(' ', '_', strtolower($data['url'])) . '.js';
                    $handleJS = @fopen($jsFileName, 'w') or die('Cannot open file:  ' . $jsFileName);
                    $js = $this->stringJS($data);
                    if (!fwrite($handleJS, $js)) {
                        throw new \Exception("JS File not generated");
                    }
                }

                /* Commit transaction & Refresh Router  */
                $this->dbpdo->pdo->commit();
                $this->InstanceCache->deleteItemsByTags([
                    $this->sign . '_getMenus_',
                    $this->sign . '_router',
                    $this->sign . '_M_menu_read_',
                    $this->sign . '_CRUDGenerator_read_menu',
                    $this->sign . '_describe_table',
                    $this->sign . '_tableForeignKeys',
                    $this->sign . '_getJabatan',
                    $this->sign . '_get_groupmenu',
                    $this->sign . '_get_tables'
                ]);
                return $this->jsonSuccess('File Created');
            } catch (\Exception $e) {
                /* Rollback transaction on error */
                $this->dbpdo->pdo->rollBack();
                throw new \Exception($e->getMessage());
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Read */
    private function read_menu(array $safe = [])
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "CRUDGenerator_read_menu" . $safe["start"] . $safe["length"] . (isset($safe["opsional"]) ? json_encode($safe["opsional"]) : null) . $safe["search"]["value"]);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {

                /* Execute DataTables */
                $list = $this->get_datatables($safe);
                $data = [];
                $no = (int)$safe['start'];

                foreach ($list as $cols) {
                    $no++;
                    $row = [];
                    $row['no'] = $no;
                    $row['id_menu'] = (int)$cols['id_menu'];
                    $row['id_groupmenu'] = (int)$cols['id_groupmenu'];
                    $row['nama'] = $cols['nama'];
                    $row['icon'] = $cols['icon'];
                    $row['url'] = $cols['url'];
                    $row['controller'] = $cols['controller'];
                    $row['tipe'] = $cols['tipe'];
                    $row['aktif'] = $cols['aktif'];
                    $row['urut'] = (int)$cols['urut'];
                    $data[] = $row;
                }

                $output = [
                    "recordsTotal" => $this->count_all($safe),
                    "recordsFiltered" => $this->count_filtered($safe),
                    "data" => $data
                ];

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_CRUDGenerator_read_menu');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            //send back draw
            $output["draw"] = (int)(isset($safe["draw"]) ? $safe["draw"] : 0);
            return $this->jsonSuccess($output);
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Describe Table */
    private function describe_table($table = null)
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "describe_table" . $table);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                $query = $this->dbpdo->pdo->prepare("DESCRIBE `$table`");
                $query->execute();
                $dsc = $query->fetchAll(\PDO::FETCH_ASSOC);
                $rel = $this->tableForeignKeys($table);
                $output = ['describe' => $dsc, 'relation' => $rel];

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_describe_table');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }
            return $output;
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /**
     * Get Table Relationship
     * @param String $table
     */
    private function tableForeignKeys(String $table = null)
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "tableForeignKeys" . $table);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                // Get Table Relation
                $sql = "SELECT  REFERENCED_TABLE_NAME,
                                REFERENCED_COLUMN_NAME,
                                (
                                    SELECT COLUMN_NAME
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE TABLE_SCHEMA = DATABASE()
                                    AND TABLE_NAME = REFERENCED_TABLE_NAME
                                    AND (DATA_TYPE LIKE '%VARCHAR%' OR DATA_TYPE LIKE '%TEXT%')
                                    LIMIT 1
                                ) IS_CHAR
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = '$table'
                            AND REFERENCED_COLUMN_NAME IS NOT NULL";

                $query = $this->dbpdo->pdo->prepare($sql);
                $query->execute();
                $relation = $query->fetchAll(\PDO::FETCH_ASSOC);
                $output = ['relation' => $relation];
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_tableForeignKeys');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            // Return result
            return $output;
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Get Jabatan */
    private function getJabatan(array $safe = [])
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "getJabatan" . $safe["start"] . $safe["length"] . $safe["search"]["value"]);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                /* Execute DataTables */
                $list = $this->get_datatables($safe);
                $data = [];
                $no = (int)$safe['start'];
                foreach ($list as $cols) {
                    $no++;
                    $row = [];
                    $row['no'] = $no;
                    $row['idjabatan'] = $cols['idjabatan'];
                    $row['nama'] = $cols['nama'];
                    $row['deskripsi'] = $cols['deskripsi'];
                    $data[] = $row;
                }

                $output = [
                    "recordsTotal" => $this->count_all($safe),
                    "recordsFiltered" => $this->count_filtered($safe),
                    "data" => $data
                ];
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_getJabatan');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            //send back draw
            $output["draw"] = (int)(isset($safe["draw"]) ? $safe["draw"] : 0);
            return $this->jsonSuccess($output);
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Get Groupmenu */
    private function get_groupmenu(array $safe = [])
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "get_groupmenu" . $safe["start"] . $safe["length"] . $safe["search"]["value"]);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                /* Execute DataTables */
                $list = $this->get_datatables($safe);
                $data = [];
                $no = (int)$safe['start'];

                foreach ($list as $cols) {
                    $no++;
                    $row = [];
                    $row['no'] = $no;
                    $row['id_groupmenu'] = $cols['id_groupmenu'];
                    $row['nama'] = $cols['nama'];
                    $row['icon'] = $cols['icon'];
                    $row['urut'] = $cols['urut'];
                    $row['aktif'] = $cols['aktif'];
                    $data[] = $row;
                }

                $output = [
                    "recordsTotal" => $this->count_all($safe),
                    "recordsFiltered" => $this->count_filtered($safe),
                    "data" => $data
                ];

                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_get_groupmenu');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            //send back draw
            $output["draw"] = (int)(isset($safe["draw"]) ? $safe["draw"] : 0);
            return $this->jsonSuccess($output);
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    /* Show Tables */
    private function get_tables()
    {
        try {
            /* Check Cache */
            $output = [];
            $ckey = hash("md5", "get_tables");
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                $query = $this->dbpdo->pdo->prepare("SHOW TABLES");
                $query->execute();
                $data = $query->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($data as $tbl) {
                    $output['table'][] = $tbl[key($tbl)];
                }
                $CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->sign . '_get_tables');
                $this->InstanceCache->save($CachedString);
            } else {
                $output = $CachedString->get();
            }

            return $this->jsonSuccess($output);
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $this->overrideSQLMsg($e->getMessage())]);
        }
    }

    private function filterTableTop(array $data = [])
    {
        $html = '';
        $groups = Stringer::fill_chunck($data, ceil(count($data)/4));
        foreach ($groups as $group) {
            $html .= "\n\t\t\t\t\t\t\t\t<div class=\"row margin-bt-20\">";

            foreach ($group as $col) {
                $html .= "\n\t\t\t\t\t\t\t\t\t<div class=\"col-lg-3 col-md-3 col-sm-3 col-xs-12\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<div class=\"nk-int-mk sl-dp-mn\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<h2>Filter " . ucwords($col["REFERENCED_COLUMN_NAME"]) . "</h2>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<span style=\"margin-left: 10px;\"><i class=\"fa fa-spinner fa-spin\"></i> Please wait...</span>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t<div class=\"chosen-select-act fm-cmp-mg\" style=\"display:none;\">";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<select id=\"" . $col['REFERENCED_TABLE_NAME'] . "\" name=\"" . $col['REFERENCED_TABLE_NAME'] . "\" class=\"chosen " . $col["REFERENCED_COLUMN_NAME"] . "\"></select>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t\t\t\t\t</div>";
            }

            $html .= "\n\t\t\t\t\t\t\t\t</div>";
        }
        return $html;
    }

    /**
     * PHP - Controller Generator
     *
     * @param array $data
     * @return void
     */
    private function stringPHP($data = [])
    {
        $php = "<?php\n";
        $php .= "/**";
        $php .= "\n* @project    " . $this->aproject;
        $php .= "\n* @package    ScientiaAPP/App/Controller";
        $php .= "\n* @author     " . $this->aauthor;
        $php .= "\n* @copyright  (c) " . date('Y') . " " . $this->acopyright;
        ;
        $php .= "\n* @created    on " . date('D M d Y') . "";
        $php .= "\n* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>";
        $php .= "\n*/\n\n";
        $php .= "namespace App\Controller;\n\n";

        /* Class Head */
        $php .= "class " . $data['className'] . " extends \App\Plugin\DataTables\n{";

        /* Declare Variable */
        $php .= "\n\t/* Declare Variable */";
        $php .= "\n\tprivate \$safe;\n";

        /* Constructor */
        $php .= "\n\t/* Constructor */";
        $php .= "\n\tpublic function __construct(\\Slim\\Container \$container)\n\t{";
        $php .= "\n\t\t/* Call Parent Constructor */";
        $php .= "\n\t\tparent::__construct(\$container);\n";
        $php .= "\n\t\t/* Set DataTables Variables */";
        $php .= "\n\t\t\$this->set_TABLE('" . $data['tableName'] . "');";
        $php .= "\n\t\t\$this->set_PKEY('" . $data['primaryKey'] . "');";
        $php .= "\n\t\t\$this->set_COLUMNS([" . $data['columnsSelect'] . "\n\t\t]);\n";
        $php .= "\n\t\t\$this->set_COLUMN_SEARCH([" . $data['columnsSearch'] . "\n\t\t]);\n";
        $php .= "\n\t\t\$this->set_ORDER(" . $data['columnsOrder'] . ");";
        $php .= "\n\t\t\$this->set_CASE_SENSITIVE(false);\n";
        $php .= "\n\t\t/* Sanitize Param */";
        $php .= "\n\t\t\$this->sanitizer(\$this->param);";
        $php .= "\n\t}\n";

        /* Parameter Sanitizer */
        $php .= "\n\t/**";
        $php .= "\n\t * Parameter Sanitizer";
        $php .= "\n\t *";
        $php .= "\n\t * @param array \$request";
        $php .= "\n\t * @return void";
        $php .= "\n\t */";
        $php .= "\n\tprivate function sanitizer(array \$request)\n\t{";
        $php .= "\n\t\t\$gump = new \GUMP();";
        $php .= "\n\t\t\$gump->validation_rules([\n\t\t\t\"draw\" => \"numeric\",\n\t\t\t\"start\" => \"numeric\",\n\t\t\t\"length\" => \"numeric\"," . $data['gump_validation'] . "\n\t\t]);\n";
        $php .= "\n\t\t\$gump->filter_rules([\n\t\t\t\"draw\" => \"trim|sanitize_numbers\",\n\t\t\t\"start\" => \"trim|sanitize_numbers\",\n\t\t\t\"length\" => \"trim|sanitize_numbers\"," . $data['gump_filter'] . "\n\t\t]);\n";
        $php .= "\n\t\ttry {";
        $php .= "\n\t\t\t//sanitize parameter";
        $php .= "\n\t\t\t\$gump->xss_clean(\$request);";
        $php .= "\n\t\t\t\$this->safe = \$gump->run(\$request);\n";
        $php .= "\n\t\t\tif (\$this->safe === false) {";
        $php .= "\n\t\t\t\t\$ers = \$gump->get_errors_array();";
        $php .= "\n\t\t\t\t\$err = implode(', ', array_values(\$ers));\n";
        $php .= "\n\t\t\t\t/* Logger */";
        $php .= "\n\t\t\t\tif (\$this->container->get('settings')['mode'] != 'production') {";
        $php .= "\n\t\t\t\t\t\$this->logger->addError(__FUNCTION__ , ['USER_REQUEST'=>\$this->user_data['USERNAME'], 'INFO'=>\$ers]);";
        $php .= "\n\t\t\t\t}";
        $php .= "\n\t\t\t\tthrow new \\Exception(\$err);";
        $php .= "\n\t\t\t} else {";
        $php .= "\n\t\t\t\treturn \$this->safe;";
        $php .= "\n\t\t\t}";
        $php .= "\n\t\t} catch (\\Exception \$e) {";
        $php .= "\n\t\t\treturn \$this->jsonFail('There was a missing or invalid parameter in the request', ['error' => \$e->getMessage()]);";
        $php .= "\n\t\t}";
        $php .= "\n\t}\n";

        /* Function Create */
        if (in_array('c', $data['crud'], true)) {
            $php .= "\n\t/* Function Create */";
            $php .= "\n\tpublic function create()\n\t{";
            $php .= "\n\t\tif (\$this->safe){";
            $php .= "\n\t\t\ttry {";
            $php .= "\n\t\t\t\t/* Send to DB */";
            $php .= "\n\t\t\t\tif (\$this->saveDb(\$this->safe) !== false) {";
            $php .= "\n\t\t\t\t\t//remove old chace";
            $php .= "\n\t\t\t\t\t\$this->InstanceCache->deleteItemsByTag(\$this->sign . \"_" . $data['className'] . "_read_\");";
            $php .= "\n\t\t\t\t\treturn \$this->jsonSuccess('Data berhasil ditambahkan', null, null, 201);";
            $php .= "\n\t\t\t\t}else{";
            $php .= "\n\t\t\t\t\tthrow new \\Exception('Penyimpanan gagal dilakukan!');";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t} catch (\\Exception \$e) {";
            $php .= "\n\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$this->overrideSQLMsg(\$e->getMessage())]);";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* Function Read */
        if (in_array('r', $data['crud'], true)) {
            $php .= "\n\t/* Function Read */";
            $php .= "\n\tpublic function read()\n\t{";
            $php .= "\n\t\tif (\$this->safe){";
            $php .= "\n\t\t\ttry {";
            $php .= "\n\t\t\t\t/* Check Cache */";
            $php .= "\n\t\t\t\t\$output = [];";
            $php .= "\n\t\t\t\t\$opsional = (isset(\$this->safe[\"opsional\"]) ? json_encode(\$this->safe[\"opsional\"]):null);";
            $php .= "\n\t\t\t\t\$search = (isset(\$this->safe['search']['value']) ? \$this->safe['search']['value']:null);";
            $php .= "\n\t\t\t\t\$length = (isset(\$this->safe['length']) ? \$this->safe['length']:null);";
            $php .= "\n\t\t\t\t\$start = (isset(\$this->safe['start']) ? \$this->safe['start']:null);";
            $php .= "\n\t\t\t\t\$ckey = hash(\"md5\", \"" . $data["className"] . "\" . \$this->user_data['ID_JABATAN'] . \$start . \$length . \$opsional . \$search);";
            $php .= "\n\t\t\t\t\$CachedString = \$this->InstanceCache->getItem(\$ckey);\n";
            $php .= "\n\t\t\t\t/* If not in Cache */";
            $php .= "\n\t\t\t\tif(is_null(\$CachedString->get())){";
            $php .= "\n\t\t\t\t\t/* Execute DataTables */";
            $php .= "\n\t\t\t\t\t\$data = [];";
            $php .= "\n\t\t\t\t\t\$list = \$this->get_datatables(\$this->safe);";
            $php .= "\n\t\t\t\t\t\$no = (int)\$this->safe['start'];";
            $php .= "\n\t\t\t\t\tforeach (\$list as \$cols) {";
            $php .= "\n\t\t\t\t\t\t\$no++;";
            $php .= "\n\t\t\t\t\t\t\$cols['no'] = \$no;";
            $php .= "\n\t\t\t\t\t\t\$data[] = \$cols;";
            $php .= "\n\t\t\t\t\t}\n";
            $php .= "\n\t\t\t\t\t\$output = [";
            $php .= "\n\t\t\t\t\t\t\"recordsTotal\" => \$this->count_all(\$this->safe),";
            $php .= "\n\t\t\t\t\t\t\"recordsFiltered\" => \$this->count_filtered(\$this->safe),";
            $php .= "\n\t\t\t\t\t\t\"data\" => \$data";
            $php .= "\n\t\t\t\t\t];\n";
            $php .= "\n\t\t\t\t\t\$CachedString->set(\$output)->expiresAfter(\$this->CacheExp)->addTag(\$this->sign . \"_" . $data["className"] ."_read_\");";
            $php .= "\n\t\t\t\t\t\$this->InstanceCache->save(\$CachedString);";
            $php .= "\n\t\t\t\t} else {";
            $php .= "\n\t\t\t\t\t/* Get data from Cache */";
            $php .= "\n\t\t\t\t\t\$output = \$CachedString->get();";
            $php .= "\n\t\t\t\t}\n";
            $php .= "\n\t\t\t\t//send back draw";
            $php .= "\n\t\t\t\t\$output[\"draw\"] = (int)(isset(\$this->safe[\"draw\"]) ? \$this->safe[\"draw\"] : 0);";
            $php .= "\n\t\t\t\treturn \$this->jsonSuccess(\$output);";
            $php .= "\n\t\t\t}  catch (\\Exception \$e) {";
            $php .= "\n\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$this->overrideSQLMsg(\$e->getMessage())]);";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* Function Update */
        if (in_array('u', $data['crud'], true)) {
            $php .= "\n\t/* Function Update */";
            $php .= "\n\tpublic function update()\n\t{";
            $php .= "\n\t\tif (\$this->safe){";
            $php .= "\n\t\t\ttry {";
            $php .= "\n\t\t\t\t/* Prepare vars */";
            $php .= "\n\t\t\t\t\$where = [\$this->PKEY => \$this->safe['pKey']];";
            $php .= "\n\t\t\t\tunset(\$this->safe['pKey']);\n";
            $php .= "\n\t\t\t\t/* Send to DB */";
            $php .= "\n\t\t\t\tif (\$this->updateDb(\$this->safe, \$where)) {";
            $php .= "\n\t\t\t\t\t//remove old chace";
            $php .= "\n\t\t\t\t\t\$this->InstanceCache->deleteItemsByTag(\$this->sign . \"_" . $data['className'] . "_read_\");";
            $php .= "\n\t\t\t\t\treturn \$this->jsonSuccess('Perubahan data berhasil');";
            $php .= "\n\t\t\t\t}else{";
            $php .= "\n\t\t\t\t\tthrow new \\Exception('Perubahan gagal dilakukan!');";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t} catch (\\Exception \$e) {";
            $php .= "\n\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$this->overrideSQLMsg(\$e->getMessage())]);";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }


        /* Function Delete */
        if (in_array('d', $data['crud'], true)) {
            $php .= "\n\t/* Function Delete */";
            $php .= "\n\tpublic function delete()\n\t{";
            $php .= "\n\t\tif (\$this->safe){";
            $php .= "\n\t\t\ttry {";
            $php .= "\n\t\t\t\t/* Send to DB */";
            $php .= "\n\t\t\t\tif (\$this->deleteDb(\$this->safe['pKey'])) {";
            $php .= "\n\t\t\t\t\t//remove old chace";
            $php .= "\n\t\t\t\t\t\$this->InstanceCache->deleteItemsByTag(\$this->sign . \"_" . $data['className'] . "_read_\");";
            $php .= "\n\t\t\t\t\treturn \$this->jsonSuccess('Data berhasil dihapus');";
            $php .= "\n\t\t\t\t}else{";
            $php .= "\n\t\t\t\t\tthrow new \\Exception('Penghapusan gagal dilakukan!');";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t} catch (\\Exception \$e) {";
            $php .= "\n\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$this->overrideSQLMsg(\$e->getMessage())]);";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }


        $php .= "\n}\n";
        /* End Class */

        return $php;
    }


    /**
     * HTML Template Generator
     *
     * @param array $data
     * @return string
     */
    private function stringHTML($data = [])
    {
        $tipe = null;
        $nama = null;
        $html = '';

        /* Breadcomb Area */
        $html .= "\n<!-- Breadcomb area Start-->";
        $html .= "\n<div class=\"breadcomb-area\">";
        $html .= "\n\t<div class=\"container\">";
        $html .= "\n\t\t<div class=\"row\">";
        $html .= "\n\t\t\t<div class=\"col-lg-12 col-md-12 col-sm-12 col-xs-12\">";
        $html .= "\n\t\t\t\t<div class=\"breadcomb-list\">";
        $html .= "\n\t\t\t\t\t<div class=\"row\">";
        $html .= "\n\t\t\t\t\t\t<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"breadcomb-wp\">";
        $html .= "\n\t\t\t\t\t\t\t\t<div class=\"breadcomb-icon\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<i class=\"notika-icon " . $data['icon_groupmenu'] . " animated infinite flip\"></i>";
        $html .= "\n\t\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t\t\t<div class=\"breadcomb-ctn\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<h2>" . $data['menu'] . "</h2>";
        $html .= "\n\t\t\t\t\t\t\t\t\t<p>Add / Edit / Delete</p>";
        $html .= "\n\t\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-3\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"breadcomb-report\">";
        $html .= "\n\t\t\t\t\t\t\t\t<button id=\"tikClock\" data-toggle=\"tooltip\" data-placement=\"left\" title=\"\" class=\"btn\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<i class=\"fa fa-clock-o\"></i>";
        $html .= "\n\t\t\t\t\t\t\t\t</button>";
        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t</div>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- Breadcomb area End-->";
        //-->

        /* Main Content + DataTables */
        $html .= "\n<!-- Main area Start-->";
        $html .= "\n<div class=\"colr-area\">";
        $html .= "\n\t<div class=\"container\">";
        $html .= "\n\t\t<div class=\"row\">";
        $html .= "\n\t\t\t<div class=\"col-lg-12 col-md-12 col-sm-12 col-xs-12\">";
        $html .= "\n\t\t\t\t<div class=\"color-wrap\">";
        $html .= "\n\t\t\t\t\t<div class=\"color-hd\">";
        $html .= "\n\t\t\t\t\t\t<div class=\"x_content\">";
        $html .= "\n\t\t\t\t\t\t\t<div class=\"cssload-loader\">Please Wait</div>";
        $html .= "\n\t\t\t\t\t\t\t<div id=\"dtableDiv\" style=\"display:none;\">";

        /* # Filter */
        if (!empty($data['relation']['relation'])) {
            $idx = 0;
            $fmSelect = null;
            $html .= $this->filterTableTop($data['relation']['relation']);
            foreach ($data['relation']['relation'] as $col) {
                /* Select - Modal Form */
                $fmSelect .= "\n\t\t\t\t\t<div class=\"form-group-15\">";
                $fmSelect .= "\n\t\t\t\t\t\t<label>" . ucwords($col["REFERENCED_COLUMN_NAME"]) . " <span class=\"required\">*</span></label>";
                $fmSelect .= "\n\t\t\t\t\t\t<div class=\"nk-int-st\">";
                $fmSelect .= "\n\t\t\t\t\t\t\t<span style=\"margin-left: 10px;\"><i class=\"fa fa-spinner fa-spin\"></i> Please wait...</span>";
                $fmSelect .= "\n\t\t\t\t\t\t\t<div class=\"chosen-select-act fm-cmp-mg\" style=\"display:none;\">";
                $fmSelect .= "\n\t\t\t\t\t\t\t\t<select id=\"" . $col["REFERENCED_COLUMN_NAME"] . "\" name=\"" . $col["REFERENCED_COLUMN_NAME"] . "\" class=\"chosen " . $col["REFERENCED_COLUMN_NAME"] . "\" required=\"required\" data-parsley-group=\"role\"></select>";
                $fmSelect .= "\n\t\t\t\t\t\t\t</div>";
                $fmSelect .= "\n\t\t\t\t\t\t</div>";
                $fmSelect .= "\n\t\t\t\t\t</div>";
            }
        }
        // #-->

        /* DataTables */
        $html .= "\n\t\t\t\t\t\t\t\t<table id=\"datatable-responsive\"";
        $html .= "\n\t\t\t\t\t\t\t\t\tclass=\"table table-striped table-bordered table-hover dt-responsive wrap\"";
        $html .= "\n\t\t\t\t\t\t\t\t\tcellspacing=\"0\" width=\"100%\">";
        $html .= "\n\t\t\t\t\t\t\t\t\t<thead>";
        $html .= "\n\t\t\t\t\t\t\t\t\t\t<tr>";

        /* Set Table Header */
        foreach ($data['dtcols'] as $idx => $cols) {
            $cols = strtoupper(strtolower(str_replace("_", " ", $cols)));
            if ($idx == 0) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"1%\"></th>";
            } elseif ($idx == 1) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"2%\" class=\"text-center\">NO</th>";
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"10%\">" . $cols . "</th>";
            } elseif ($idx > 1) {
                $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"10%\">" . $cols . "</th>";
            }
        }
        $html .= "\n\t\t\t\t\t\t\t\t\t\t\t<th width=\"12%\" class=\"all\">ACTION</th>";
        $html .= "\n\t\t\t\t\t\t\t\t\t\t</tr>";

        $html .= "\n\t\t\t\t\t\t\t\t\t</thead>";
        $html .= "\n\t\t\t\t\t\t\t\t</table>";
        //-->

        $html .= "\n\t\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t\t</div>";
        $html .= "\n\t\t\t\t</div>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- Main area End-->";
        //-->

        /* Modal Form */
        $html .= "\n<!-- #region modal -->";
        $html .= "\n<div class=\"modal fade formEditorModal\" data-backdrop=\"static\" role=\"dialog\" aria-hidden=\"true\">";
        $html .= "\n\t<div class=\"modal-dialog modals-default\">";
        $html .= "\n\t\t<div class=\"modal-content\">";
        $html .= "\n\t\t\t<div class=\"modal-header form-group-15\">";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
        $html .= "\n\t\t\t\t<h4 class=\"modal-title\">New " . $data['menu'] . "</h4>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t\t<div class=\"modal-body\">";
        $html .= "\n\t\t\t\t<form id=\"formEditor\" data-parsley-validate class=\"form-horizontal form-label-left\">";

        /* Generate select if relation */
        $html .= (isset($fmSelect)) ? $fmSelect : '';

        /* Generate Input Field */
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);
            $label = ucwords(str_replace("_", " ", $nama));
            $isNull = (strtoupper(trim($desc['Null'])) == 'NO') ? "required=\"required\"" : "";
            $spanReq = (strtoupper(trim($desc['Null'])) == 'NO') ? "<span class=\"required\">*</span>" : "";

            /* Set input type */
            if (Stringer::is_like(['char'], $desc['Type'])) {
                $tipe = "<input type=\"text\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"" . $label . "\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['int', 'decimal', 'float'], $desc['Type'])) {
                $tipe = "<input type=\"number\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"" . $label . "\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['date'], $desc['Type'])) {
                $tipe = "<input type=\"date\" class=\"form-control input-sm\" name=\"" . $nama . "\" placeholder=\"" . $label . "\" data-parsley-group=\"role\" " . $isNull . ">";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "<textarea class=\"form-control auto-size\" rows=\"2\" placeholder=\"Input " . $label . " here...\" name=\"" . $nama . "\" " . $isNull . "></textarea>";
            }

            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                $html .= "\n\t\t\t\t\t<div class=\"form-group-15\">";
                $html .= "\n\t\t\t\t\t\t<label>" . $label . $spanReq . "</label>";
                $html .= "\n\t\t\t\t\t\t<div class=\"nk-int-st\">";
                $html .= "\n\t\t\t\t\t\t\t" . $tipe;
                $html .= "\n\t\t\t\t\t\t</div>";
                $html .= "\n\t\t\t\t\t</div>";
            }
        }

        $html .= "\n\t\t\t\t</form>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t\t<div class=\"modal-footer\">";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"btn_save btn btn-default notika-btn-default waves-effect\" data-loading-text=\"Loading...\">";
        $html .= "\n\t\t\t\t\t<i class=\"fa fa-save\"></i> Save";
        $html .= "\n\t\t\t\t</button>";
        $html .= "\n\t\t\t\t<button type=\"button\" class=\"btn_cancel btn btn-danger notika-btn-danger waves-effect\" data-dismiss=\"modal\">";
        $html .= "\n\t\t\t\t\t<i class=\"fa fa-times\"></i> Close";
        $html .= "\n\t\t\t\t</button>";
        $html .= "\n\t\t\t</div>";
        $html .= "\n\t\t</div>";
        $html .= "\n\t</div>";
        $html .= "\n</div>";
        $html .= "\n<!-- #endregion modal -->";
        //-->

        return $html;
    }

    /**
     * Javascript Code Generator
     *
     * @param array $data
     * @return string
     */
    private function stringJS($data = [])
    {
        /* Get Relation */
        if (!empty($data['relation']['relation'])) {
            $postOpt = [];
            $getSelect = [];
            $jsPopulate = null;
            $changeSelect = [];

            foreach ($data['relation']['relation'] as $col) {
                $isChar = (empty($col['IS_CHAR']) ? $col['REFERENCED_COLUMN_NAME'] : $col['IS_CHAR']);
                $jsPopulate .= "\n/* Get " . $col['REFERENCED_TABLE_NAME'] . " */";
                $jsPopulate .= "\nfunction get" . $col['REFERENCED_TABLE_NAME'] . "(obj, sel = null) {";
                $jsPopulate .= "\n\tlet opt = \$(obj);";
                $jsPopulate .= "\n\tlet url = SiteRoot + 'c_" . $col['REFERENCED_TABLE_NAME'] . "_read';";
                $jsPopulate .= "\n\tlet post_data = {";
                $jsPopulate .= "\n\t\t'draw': 1,";
                $jsPopulate .= "\n\t\t'start': 0,";
                $jsPopulate .= "\n\t\t'length': -1, /* All data */";
                $jsPopulate .= "\n\t\t'search[value]': ''";
                $jsPopulate .= "\n\t}";
                $jsPopulate .= "\n\tpopulateSelect(url, opt, post_data, sel, '" . $col['REFERENCED_COLUMN_NAME'] . "', '" . $isChar . "');";
                $jsPopulate .= "\n};\n";

                $getSelect[] = "get" . $col['REFERENCED_TABLE_NAME'] . "('#" . $col['REFERENCED_COLUMN_NAME'] . "', '0');";
                $changeSelect[] = "select[name=" . $col['REFERENCED_TABLE_NAME'] . "]";
                $postOpt[] = "'" . $col['REFERENCED_COLUMN_NAME'] . "': \$(\"select[name=" . $col['REFERENCED_TABLE_NAME'] . "]\").val(),";
            }
        }

        /* JS String start */
        $js = "/* Variables */;";
        $js .= "\nvar apiUrl = SiteRoot + 'c_" . $data['url'] . "';";
        $js .= "\nvar tbl = \"#datatable-responsive tbody\";";
        $js .= "\nvar pKey, table;";
        $js .= "\nvar saveUpdate = \"save\";\n";

        //start DocumentReady
        $js .= "\n/* Document Ready */";
        $js .= "\n\$(document).ready(function() {";
        $js .= "\n\t/* First Event Load */";
        $js .= "\n\tvar enterBackspace = true;\n";

        /* Call populateSelect */
        if (isset($getSelect) && !empty($getSelect)) {
            foreach ($getSelect as $selOpt) {
                $js .= "\n\t" . str_replace(['#', ", '0'"], ['.', ''], $selOpt);
            }
            $js .= "\n\n";
        }

        /* Generate enterKey */
        $count = count($data['describe']);
        $i = 0;
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);
            if ($desc['Key'] == 'PRI') {
                $count = $count - 1;
            }

            /* Set input type */
            if (Stringer::is_like(['char', 'int', 'decimal', 'float', 'date'], $desc['Type'])) {
                $tipe = "input[name=" . $nama . "]";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "textarea[name=" . $nama . "]";
            }

            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                /* Event Save on enter input */
                $i++;
                if ($i === $count) {
                    $js .= "\t\$(\"" . $tipe . "\").enterKey(function (e) {";
                    $js .= "\n\t\te.preventDefault();";
                    $js .= "\n\t\t\$(\".btn_save\").click();";
                    $js .= "\n\t});\n";
                }
            }
        }

        $js .= "\n\t/* Datatables set_token */";
        $js .= "\n\t\$(\"#datatable-responsive\").on('xhr.dt', function(e, settings, json, jqXHR){";
        $js .= "\n\t\tredirectLogin(jqXHR);";
        $js .= "\n\t\tset_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));";
        $js .= "\n\t});\n";

        $js .= "\n\t/* Datatables handler */";
        $js .= "\n\ttable = \$(\"#datatable-responsive\").DataTable({";
        $js .= "\n\t\tautoWidth: false,";
        $js .= "\n\t\tlanguage: {";
        $js .= "\n\t\t\t\"zeroRecords\": \"Maaf, pencarian Anda tidak ditemukan\",";
        $js .= "\n\t\t\t\"info\": \"Menampilkan _START_ - _END_ dari _TOTAL_ data\",";
        $js .= "\n\t\t\t\"infoEmpty\": \"Data belum tersedia\",";
        $js .= "\n\t\t\t\"infoFiltered\": \"(terfilter dari _MAX_ total data)\",";
        $js .= "\n\t\t\t\"searchPlaceholder\": \"Enter untuk mencari\"";
        $js .= "\n\t\t},";
        $js .= "\n\t\t\"dom\": \"<'row'<'col-sm-8'B><'col-sm-4'f>><'row'<'col-sm-12't>><'row'<'col-sm-4'<'pull-left' p>><'col-sm-8'<'pull-right' i>>>\",";

        //start button
        $js .= "\n\t\t\"buttons\": [";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\textend: \"pageLength\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm bt-separ\"";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i id='dtSpiner' class='fa fa-refresh fa-spin'></i> <span id='tx_dtSpiner'>Reload</span>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btReload\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Reload Data\",";
        $js .= "\n\t\t\t\taction: function() {";
        $js .= "\n\t\t\t\t\tdtReload(table);";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";

        /* Export PDF */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-file-pdf-o'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm\",";
        $js .= "\n\t\t\t\textend: \"pdfHtml5\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Export PDF\",";
        $js .= "\n\t\t\t\tdownload: \"open\",";
        $js .= "\n\t\t\t\tpageSize: \"LEGAL\",";
        $js .= "\n\t\t\t\torientation: \"portrait\", /* portrait | landscape */";
        $js .= "\n\t\t\t\ttitle: \"" . $data['menu'] . "\",";
        $js .= "\n\t\t\t\texportOptions: {";
        $js .= "\n\t\t\t\t\t/* Show column */";
        $js .= "\n\t\t\t\t\tcolumns: \":visible\" /* [1, 2, 3] => selected column only */";
        $js .= "\n\t\t\t\t},";
        $js .= "\n\t\t\t\tcustomize: function (doc) {";
        $js .= "\n\t\t\t\t\t/* Set Default Table Header Alignment */";
        $js .= "\n\t\t\t\t\tdoc.styles.tableHeader.alignment = 'left';\n";
        $js .= "\n\t\t\t\t\t/* Set table width each column */";
        $js .= "\n\t\t\t\t\tdoc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split(''); /* ['*', '15%', 'auto'] => each column width*/\n";
        $js .= "\n\t\t\t\t\t/* Set column alignment */";
        $js .= "\n\t\t\t\t\tvar rowCount = doc.content[1].table.body.length;";
        $js .= "\n\t\t\t\t\tfor (i = 0; i < rowCount; i++) {";
        $js .= "\n\t\t\t\t\t\tdoc.content[1].table.body[i][0].alignment = \"right\"; /* 1st column align right */";
        $js .= "\n\t\t\t\t\t};";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        //-->

        /* Create */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-plus-circle'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btn-primary btn_add hidden\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Create New\",";
        $js .= "\n\t\t\t\taction: function() {";
        $js .= "\n\t\t\t\t\tbtn_add();";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        //-->

        /* Delete */
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\ttext: \"<i class='fa fa-trash'></i>\",";
        $js .= "\n\t\t\t\tclassName: \"btn-sm btn-danger btDels act-delete hidden\",";
        $js .= "\n\t\t\t\ttitleAttr: \"Multiple Delete\",";
        $js .= "\n\t\t\t},";
        //-->

        $js .= "\n\t\t],";
        //End button

        $js .= "\n\t\t\"pagingType\": \"numbers\",";
        $js .= "\n\t\t\"lengthMenu\": [";
        $js .= "\n\t\t\t[10, 25, 50, 100, -1],";
        $js .= "\n\t\t\t[10, 25, 50, 100, 'All']";
        $js .= "\n\t\t],";
        $js .= "\n\t\t\"responsive\": true,";
        $js .= "\n\t\t\"processing\": false,";
        $js .= "\n\t\t\"ordering\": false,";
        $js .= "\n\t\t\"serverSide\": true,";
        $js .= "\n\t\t\"ajax\": {";
        $js .= "\n\t\t\t\"url\": apiUrl + '_read',";
        $js .= "\n\t\t\t\"type\": 'post',";
        $js .= "\n\t\t\t\"headers\": { JWT: get_token(API_TOKEN) },";
        $js .= "\n\t\t\t\"data\": function(data, settings){";
        $js .= "\n\t\t\t\t/* start_loader */";
        $js .= "\n\t\t\t\t\$(\".cssload-loader\").hide();";
        $js .= "\n\t\t\t\t\$(\"#dtableDiv\").fadeIn(\"slow\");";
        $js .= "\n\t\t\t\t\$(\"a.btn.btn-default.btn-sm\").addClass('disabled');";
        $js .= "\n\t\t\t\t\$(\"#tx_dtSpiner\").text('Please wait...');";
        $js .= "\n\t\t\t\t\$(\"#dtSpiner\").removeClass('pause-spinner');\n";

        //start combobox optional
        if (isset($postOpt) && !empty($postOpt)) {
            $optIdx = 0;
            $optCtr = count($postOpt) - 1;
            $js .= "\n\t\t\t\t/* Post Data */";
            $js .= "\n\t\t\t\tdata.opsional = {";
            foreach ($postOpt as $opsional) {
                $js .= "\n\t\t\t\t\t" . ($optCtr == $optIdx ? trim($opsional, ',') : $opsional);
                $optIdx++;
            }
            $js .= "\n\t\t\t\t};\n";
        }
        //end combobox

        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t\"dataSrc\": function(json) {";
        $js .= "\n\t\t\t\t/* stop_loader */";
        $js .= "\n\t\t\t\tcheckAuth(function(){";
        $js .= "\n\t\t\t\t\t\$(\"#tx_dtSpiner\").text('Reload');";
        $js .= "\n\t\t\t\t\t\$(\"#dtSpiner\").addClass('pause-spinner');";
        $js .= "\n\t\t\t\t\t\$(\"a.btn.btn-default.btn-sm\").removeClass('disabled');";
        $js .= "\n\t\t\t\t\tsetNprogressLoader(\"done\");\n";
        $js .= "\n\t\t\t\t});";

        //start json reordering
        $js .= "\n\t\t\t\t/* return variable */";
        $js .= "\n\t\t\t\tvar return_data = [];";
        $js .= "\n\t\t\t\tif (json.success === true) {";
        $js .= "\n\t\t\t\t\t/* Redraw json result */";
        $js .= "\n\t\t\t\t\tjson.draw = json.message.draw;";
        $js .= "\n\t\t\t\t\tjson.recordsFiltered = json.message.recordsFiltered;";
        $js .= "\n\t\t\t\t\tjson.recordsTotal = json.message.recordsTotal;\n";
        $js .= "\n\t\t\t\t\t/* ReOrdering json result */";
        $js .= "\n\t\t\t\t\tfor (var i = 0; i < json.message.data.length; i++) {";
        $js .= "\n\t\t\t\t\t\treturn_data.push({";

        foreach ($data['dtcols'] as $idx => $cols) {
            if ($idx == 0) {
                $js .= "\n\t\t\t\t\t\t\t$idx: json.message.data[i].$cols,";
            } elseif ($idx == 1) {
                $js .= "\n\t\t\t\t\t\t\t$idx: json.message.data[i].no,";
                $js .= "\n\t\t\t\t\t\t\t" . ($idx + 1) . ": json.message.data[i].$cols,";
            } elseif ($idx > 1) {
                $js .= "\n\t\t\t\t\t\t\t" . ($idx + 1) . ": json.message.data[i].$cols,";
            }
        }

        $js .= "\n\t\t\t\t\t\t})";
        $js .= "\n\t\t\t\t\t}";
        $js .= "\n\t\t\t\t\treturn return_data;";
        $js .= "\n\t\t\t\t} else {";
        $js .= "\n\t\t\t\t\tjson.draw = null;";
        $js .= "\n\t\t\t\t\tjson.recordsFiltered = null;";
        $js .= "\n\t\t\t\t\tjson.recordsTotal = null;";
        $js .= "\n\t\t\t\t\treturn_data = [];";
        $js .= "\n\t\t\t\t\tnotification(json.error, 'warn', 2, json.message);";
        $js .= "\n\t\t\t\t\treturn return_data;";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t\"error\": function (jqXHR, textStatus, errorThrown) {";
        $js .= "\n\t\t\t\tnotification(jqXHR . responseJSON . error, 'error', 3, 'ERROR');";
        $js .= "\n\t\t\t\tconsole.log(jqXHR);";
        $js .= "\n\t\t\t\tconsole.log(textStatus);";
        $js .= "\n\t\t\t\tconsole.log(errorThrown);";
        $js .= "\n\t\t\t}";
        $js .= "\n\t\t},";
        //end json reordering

        //start column definition
        $js .= "\n\t\t\"deferRender\": true,";
        $js .= "\n\t\t\"columnDefs\": [";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": 0,";
        $js .= "\n\t\t\t\t\"className\": \"select-checkbox\",";
        $js .= "\n\t\t\t\t\"checkboxes\": {";
        $js .= "\n\t\t\t\t\t\"selectRow\": true";
        $js .= "\n\t\t\t\t},";
        $js .= "\n\t\t\t\t\"render\": function () {";
        $js .= "\n\t\t\t\t\treturn '';";
        $js .= "\n\t\t\t\t}";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": 1,";
        $js .= "\n\t\t\t\t\"className\": \"dt-center\",";
        $js .= "\n\t\t\t},";
        $js .= "\n\t\t\t{";
        $js .= "\n\t\t\t\t\"targets\": -1,";
        $js .= "\n\t\t\t\t\"className\": \"dt-center\",";
        $js .= "\n\t\t\t\t\"data\": null,";
        $js .= "\n\t\t\t\t\"defaultContent\":";
        $js .= "\n\t\t\t\t\t'<span class=\"button-icon-btn button-icon-btn-cl sm-res-mg-t-30\"><button title=\"Edit\" id=\"btEdit\" class=\"hidden btn-act act-edit btn btn-warning warning-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs\" type=\"button\"><i class=\"notika-icon notika-draft\"></i></button></span>' +";
        $js .= "\n\t\t\t\t\t'<span class=\"button-icon-btn button-icon-btn-cl sm-res-mg-t-30\"><button title=\"Delete\" id=\"btDel\" class=\"hidden btn-act act-delete btn btn-danger danger-icon-notika btn-reco-mg btn-button-mg waves-effect btn-xs\" type=\"button\"><i class=\"notika-icon notika-close\"></i></button></span>'";
        $js .= "\n\t\t\t}";
        $js .= "\n\t\t],";
        $js .= "\n\t\t\"select\": {";
        $js .= "\n\t\t\t\"style\": \"multi\",";
        $js .= "\n\t\t\t\"selector\": \"td:first-child\",";
        $js .= "\n\t\t}";
        $js .= "\n\t});\n";
        //end column definition

        //enterAndSearch
        $js .= "\n\t/* DataTable search on enter */";
        $js .= "\n\tenterAndSearch(table, '#datatable-responsive', enterBackspace)\n";
        //-->

        /* Create */
        $js .= "\n\t/* Button Save Action */";
        $js .= "\n\t\$('.btn_save').on('click', function() {";
        $js .= "\n\t\tsaveOrUpdate(saveUpdate, apiUrl, pKey, '#formEditor');";
        $js .= "\n\t});\n";
        //-->

        /* Update */
        $js .= "\n\t/* Button Edit Action */";
        $js .= "\n\t\$(tbl).on( 'click', '#btEdit', function () {";
        $js .= "\n\t\tsaveUpdate = 'update';";
        $js .= "\n\t\tlet data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();";
        $js .= "\n\t\tpKey = data[0];\n";
        $js .= "\n\t\t/* Set Edit Form Value */";

        foreach ($data['dtcols'] as $idx => $cols) {
            if ($idx >= 1) {
                $js .= "\n\t\t\$(\"input[name=$cols]\").val(data[" . ($idx + 1) . "]);";
            }
        }

        $js .= "\n\t\t\$('.btn_save').html('<i class=\"fa fa-save\"></i> Update');";
        $js .= "\n\t\t\$('.modal-title').html('Edit " . $data['menu'] . "');";
        $js .= "\n\t\t\$('.formEditorModal').modal();";
        $js .= "\n\t});\n";
        //-->

        /* Delete Single */
        $js .= "\n\t/* Button Delete */";
        $js .= "\n\t\$(tbl).on( 'click', '#btDel', function () {";
        $js .= "\n\t\tlet data = (table.row($(this).closest('tr')).data() === undefined) ? table.row($(this).closest('li')).data() : table.row($(this).closest('tr')).data();";
        $js .= "\n\t\tdeleteSingle(apiUrl, data);";
        $js .= "\n\t});\n";
        //-->

        /* Delete Multiple */
        $js .= "\n\t/* Button Delete Multi */";
        $js .= "\n\t\$('.btDels').on( 'click', function () {";
        $js .= "\n\t\tlet rows_selected = table.column(0).checkboxes.selected();";
        $js .= "\n\t\tdeleteMultiple(apiUrl, table, rows_selected);";
        $js .= "\n\t});";
        //-->

        $js .= "\n});\n";
        //<-- end DocumentReady

        /* populateSelect */
        $js .= isset($jsPopulate) ? $jsPopulate : '';

        /* changeSelect Function */
        if (isset($changeSelect) && !empty($changeSelect)) {
            foreach ($changeSelect as $csl) {
                $js .= "\n/* Filter " . $data['menu'] . " Event Change */";
                $js .= "\n\$(\"" . $csl . "\").on('change', function() {";
                $js .= "\n\t\$(\".btReload\").click();";
                $js .= "\n});\n";
            }
        }

        /* Action Button Create */
        $js .= "\n/* Button Create Action */";
        $js .= "\nfunction btn_add() {";
        $js .= "\n\tid = '';";
        $js .= "\n\tsaveUpdate = 'save';";

        /* Call populateSelect */
        if (isset($getSelect) && !empty($getSelect)) {
            foreach ($getSelect as $selOpt) {
                $js .= "\n\t" . $selOpt;
            }
        }

        $js .= "\n\t\$('.btn_save').html('<i class=\"fa fa-save\"></i> Save');";
        $js .= "\n\t\$('.modal-title').html('New " . $data['menu'] . "');";
        $js .= "\n\t\$('.formEditorModal form')[0].reset();";
        $js .= "\n\t$('.formEditorModal').modal();";
        $js .= "\n};\n";

        /* Modal Show */
        $js .= "\n/* Modal on show */";
        $js .= "\n\$('.formEditorModal').on('shown.bs.modal', function () {";
        $js .= "\n\t/* code */";

        /* Generate onFocus */
        $j = 0;
        foreach ($data['describe'] as $desc) {
            /* input name */
            $nama = strtolower($desc['Field']);

            /* Set input type */
            if (Stringer::is_like(['char', 'int', 'decimal', 'float', 'date'], $desc['Type'])) {
                $tipe = "input[name=" . $nama . "]";
            } elseif (Stringer::is_like(['text'], $desc['Type'])) {
                $tipe = "textarea[name=" . $nama . "]";
            }
            /* Skip if PrimaryKey */
            if ($desc['Key'] != 'PRI') {
                /* Set focus on modal show */
                if ($j === 0) {
                    $js .= "\n\t\$(\"" . $tipe . "\").focus();";
                }
                $j++;
            }
        }

        $js .= "\n});\n";

        /* Modal Close */
        $js .= "\n/* Modal on dissmis */";
        $js .= "\n\$('.formEditorModal').on('hide.bs.modal', function() {";
        $js .= "\n\t/* code */";
        $js .= "\n\t$(\"form#formEditor\").parsley().reset();";
        $js .= "\n});\n";

        /* Return String JS */
        return $js;
    }
}
