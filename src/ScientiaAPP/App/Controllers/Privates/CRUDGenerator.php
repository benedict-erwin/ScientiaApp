<?php

/**
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    App\Controllers\Privates
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 **/

namespace App\Controllers\Privates;

use App\Lib\Stringer;

class CRUDGenerator extends \App\Controllers\PrivateController
{
    private $template;
    private $aproject;
    private $aauthor;
    private $acopyright;
    private $M_ROLE;
    private $M_MENU;
    private $M_GROUPMENU;
    private $CRUDGEN;
    private $generator;

    /**
     * Constructor
     *
     * @param \Slim\Container $container
     */
    public function __construct(\Slim\Container $container)
    {
        /* Execute parent constructor */
        parent::__construct($container);

        /* Set Model */
        $this->M_ROLE = new \App\Models\M_role($container);
        $this->M_MENU = new \App\Models\M_menu($container);
        $this->M_GROUPMENU = new \App\Models\M_groupmenu($container);
        $this->CRUDGEN = new \App\Models\CRUDGenerator($container);

        /* Set Header variables */
        $this->aproject = "ScientiaAPP - Web Apps Skeleton & CRUD Generator";
        $this->aauthor = "Benedict E. Pranata";
        $this->acopyright = "benedict.erwin@gmail.com";

        /* Template */
        $this->template = $container->get('settings')['cms_template'];
        $genClass = "\\App\\Plugin\\CRUDGenerator\\" . ucfirst($this->template);
        $this->generator = new $genClass();
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
                throw new \Exception($err);
            } else {
                /* GoTo Switcher */
                return $this->switcher($safe);
            }
        } catch (\Exception $e) {
            /* Logger */
            if ($this->container->get('settings')['mode'] != 'production') {
                $this->logger->error(__METHOD__ . ' :: ' . $e->getMessage());
            }
            return $this->jsonFail(null, ['error' => $e->getMessage()]);
        }
    }

    /* Switcher */
    private function switcher($safe = [])
    {
        switch ($safe['action']) {
            case 'generate':
                return $this->generate($safe);
                break;
            case 'read_menu':
                return $this->read_menu($safe);
                break;
            case 'get_tables':
                return $this->get_tables($safe);
                break;
            case 'get_jabatan':
                return $this->getJabatan($safe);
                break;
            case 'get_groupmenu':
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
            if ($safe['generate'] == 'bf') {
                $safe['crud'] = ['c', 'r', 'u', 'd'];
            }

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

                /* ColumnSearch & Gump String Filter */
                if (Stringer::is_like($colTypeToSearch, $desc['Type'])) {
                    $csearch .= "\n\t\t\t'" . $desc['Field'] . "', ";
                    $gumpFilterRules .= "\n\t\t\t\"" . $desc['Field'] . '" => "trim|sanitize_string",';
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

                /* Gump Numeric Validation */
                if (Stringer::is_like(['int'], $desc['Type'])) {
                    $gumpValidationRules .= "\n\t\t\t\"" . $desc['Field'] . '" => "numeric",';
                }
            }

            /* Variable for generate backend */
            $groupmenu = $this->CRUDGEN->getGroupmenuIcon($safe['m_groupmenu']);
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
            $data['urutGroup'] = $this->CRUDGEN->urutanMenu($safe['m_groupmenu']);
            $data['menu'] = ucwords(strtolower($safe['menu']));
            $data['url'] = strtolower(trim($safe['url'], "/"));
            $data['crud'] = $safe['crud'];

            /* Check Duplicate M_menu */
            $cek = $this->M_MENU->isDuplicate(
                [
                    '/' . $data['url'],
                    '/' . $data['url'] . '/create',
                    '/' . $data['url'] . '/read',
                    '/' . $data['url'] . '/batch',
                ],
                [
                    'GET',
                    'PUT',
                    'POST',
                    'DELETE',
                    'MENU',
                ]
            );

            /* Start transaction */
            try {

                if ($cek === false) {
                    $this->CRUDGEN->generateMenus($safe, $data);
                }

                /* Generate Pivate Controller */
                $phpFileName = APP_PATH . '/Controllers/Privates/' . $data['className'] . '.php';
                $handle = @fopen($phpFileName, 'w') or die('Cannot open file:  ' . $phpFileName);
                $php = $this->controllerPHP($data);
                if (!fwrite($handle, $php)) {
                    throw new \Exception("PHP File not generated");
                }

                /* Generate Model */
                $modFileName = APP_PATH . '/Models/' . $data['className'] . '.php';
                $modHandle = @fopen($modFileName, 'w') or die('Cannot open file:  ' . $modFileName);
                $phpMod = $this->modelPHP($data);
                if (!fwrite($modHandle, $phpMod)) {
                    throw new \Exception("PHP File not generated");
                }

                if ($safe['generate'] == 'bf') {
                    /* HTML */
                    $htmlFileName = APP_PATH . '/Templates/Backend/' . $this->template . '/' . str_replace(' ', '_', strtolower($data['url'])) . '.twig';
                    $handleHTML = @fopen($htmlFileName, 'w') or die('Cannot open file:  ' . $htmlFileName);
                    $html = $this->generator->stringHTML($data);
                    if (!fwrite($handleHTML, $html)) {
                        throw new \Exception("HTML File not generated");
                    }

                    /* JS */
                    $jsFileName = BASE_PATH . '/public/assets/scripts/page/' . $this->template . '/' . str_replace(' ', '_', strtolower($data['url'])) . '.js';
                    $handleJS = @fopen($jsFileName, 'w') or die('Cannot open file:  ' . $jsFileName);
                    $js = $this->generator->stringJS($data);
                    if (!fwrite($handleJS, $js)) {
                        throw new \Exception("JS File not generated");
                    }
                }

                return $this->jsonSuccess('File Created');
            } catch (\Exception $e) {
                $this->logger->error(__CLASS__ . '->' .  __METHOD__ . ' :: ', [
                    'error' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);
                throw new \Exception($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->error(__CLASS__ . '->' .  __METHOD__ . ' :: ', [
                'error' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }

    /* Read */
    private function read_menu(array $safe = [])
    {
        try {
            /* Get Data */
            $data = [];
            $output = [];
            $records = $this->M_MENU->read($safe);
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

    /* Describe Table */
    private function describe_table($table = null)
    {
        try {
            return [
                'describe' => $this->CRUDGEN->describeTable($table),
                'relation' => $this->CRUDGEN->getTableRelation($table),
            ];
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
        }
    }

    /* Get Jabatan */
    private function getJabatan(array $safe = [])
    {
        try {
            /* Get Data */
            $data = [];
            $output = [];
            $records = $this->M_ROLE->read($safe);
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

    /* Get Groupmenu */
    private function get_groupmenu(array $safe = [])
    {
        try {
            /* Get Data */
            $data = [];
            $output = [];
            $records = $this->M_GROUPMENU->read($safe);
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

    /* Show Tables */
    private function get_tables()
    {
        try {
            /* Get Data */
            $data = $this->CRUDGEN->getAllTables();
            foreach ($data as $tbl) {
                $output['table'][] = $tbl[key($tbl)];
            }

            return $this->jsonSuccess($output);
        } catch (\Exception $e) {
            return $this->jsonFail('Execution Fail!', ['error' => $e->getMessage()]);
        }
    }

    /**
     * PHP - Models Generator
     *
     * @param array $data
     * @return string
     */
    public function modelPHP(array $data)
    {
        $php = "<?php\n";
        $php .= "/**";
        $php .= "\n* @project    " . $this->aproject;
        $php .= "\n* @package    App\Models";
        $php .= "\n* @author     " . $this->aauthor;
        $php .= "\n* @copyright  (c) " . date('Y') . " " . $this->acopyright;
        $php .= "\n* @created    on " . date('D M d Y') . "";
        $php .= "\n* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>";
        $php .= "\n*/\n\n";
        $php .= "namespace App\Models;\n\n";

        /* Class Head - Opening tag */
        $php .= "class " . $data['className'] . " extends \App\Models\DataTablesMysql\n{";

        /* Declare Variable */
        $php .= "\n\t/* Declare private variable */";
        $php .= "\n\tprivate \$Cacher;";
        $php .= "\n\tprivate \$CacheExp;";
        $php .= "\n\tprivate \$TagName;";
        $php .= "\n\tprivate \$Sign;\n";

        /* Constructor */
        $php .= "\n\t/* Constructor */";
        $php .= "\n\tpublic function __construct(\\Slim\\Container \$container)\n\t{";
        $php .= "\n\t\t/* Call Parent Constructor */";
        $php .= "\n\t\tparent::__construct(\$container);\n";
        $php .= "\n\t\t/* Cache Setup */";
        $php .= "\n\t\t\$this->Sign = \$container->get('settings')['dbnya']['SIGNATURE'];";
        $php .= "\n\t\t\$this->Cacher = \$container->cacher;";
        $php .= "\n\t\t\$this->TagName = hash('sha256', \$this->Sign . '" . $data['className'] . "');";
        $php .= "\n\t\t\$this->CacheExp = 3600; # in seconds (1 hour)\n";
        $php .= "\n\t\t/* Table Setup */";
        $php .= "\n\t\t\$this->setTable('" . $data['tableName'] . "')";
        $php .= "\n\t\t\t->setColumns([" . $data['columnsSelect'] . "\n\t\t])";
        $php .= "\n\t\t\t->setPkey('" . $data['primaryKey'] . "')";
        $php .= "\n\t\t\t->setSearchCols([" . $data['columnsSearch'] . "\n\t\t])";
        $php .= "\n\t\t\t->setDefaultOrder(" . $data['columnsOrder'] . ");";
        $php .= "\n\t}\n";

        /* READ */
        if (in_array('r', $data['crud'], true)) {
            /* Get Data By ID */
            $php .= "\n\t/**";
            $php .= "\n\t * Get Data in " . $data['className'] . " by Primary Key";
            $php .= "\n\t *";
            $php .= "\n\t * @param integer \$id";
            $php .= "\n\t * @return array";
            $php .= "\n\t */";
            $php .= "\n\tpublic function getByID(int \$id)";
            $php .= "\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t\$output = null;";
            $php .= "\n\t\t\t\$cacheKey = hash('md5', \$this->Sign . __METHOD__ . \$id);";
            $php .= "\n\t\t\t\$CachedString = \$this->Cacher->getItem(\$cacheKey);";
            $php .= "\n\t\t\tif (!\$CachedString->isHit()) {";
            $php .= "\n\t\t\t\t\$output = \$this->getDataById(\$id);";
            $php .= "\n\t\t\t\t\$CachedString->set(\$output)->expiresAfter(\$this->CacheExp)->addTag(\$this->TagName);";
            $php .= "\n\t\t\t\t\$this->Cacher->save(\$CachedString);";
            $php .= "\n\t\t\t}else {";
            $php .= "\n\t\t\t\t\$output = \$CachedString->get();";
            $php .= "\n\t\t\t}\n";
            $php .= "\n\t\t\treturn \$output;";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\tthrow new \Exception(\$e->getMessage());";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";

            /* Get Data & Filter */
            $php .= "\n\t/**";
            $php .= "\n\t * Retrieve data from " . $data['className'] . "";
            $php .= "\n\t *";
            $php .= "\n\t * @param array \$data";
            $php .= "\n\t * @return array \$output";
            $php .= "\n\t */";
            $php .= "\n\tpublic function read(array \$data = [])";
            $php .= "\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\tunset(\$data['draw']);";
            $php .= "\n\t\t\t\$output = [];";
            $php .= "\n\t\t\t\$cacheKey = hash('md5', \$this->Sign . __METHOD__ . json_encode(\$data));";
            $php .= "\n\t\t\t\$CachedString = \$this->Cacher->getItem(\$cacheKey);";
            $php .= "\n\t\t\tif (!\$CachedString->isHit()) {";
            $php .= "\n\t\t\t\t\$output = [";
            $php .= "\n\t\t\t\t\t'datalist' => \$this->get_datatables(\$data),";
            $php .= "\n\t\t\t\t\t'recordsTotal' => \$this->count_all(\$data),";
            $php .= "\n\t\t\t\t\t'recordsFiltered' => \$this->count_filtered(\$data)";
            $php .= "\n\t\t\t\t];";
            $php .= "\n\t\t\t\t\$CachedString->set(\$output)->expiresAfter(\$this->CacheExp)->addTag(\$this->TagName);";
            $php .= "\n\t\t\t\t\$this->Cacher->save(\$CachedString);";
            $php .= "\n\t\t\t} else {";
            $php .= "\n\t\t\t\t\$output = \$CachedString->get();";
            $php .= "\n\t\t\t}\n";
            $php .= "\n\t\t\treturn \$output;";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\tthrow new \Exception(\$e->getMessage());";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* CREATE */
        if (in_array('c', $data['crud'], true)) {
            /* Insert Data */
            $php .= "\n\t/**";
            $php .= "\n\t * Insert Data in " . $data['className'] . "";
            $php .= "\n\t *";
            $php .= "\n\t * @param array \$data";
            $php .= "\n\t * @return int \$last_insert_id";
            $php .= "\n\t */";
            $php .= "\n\tpublic function create(array \$data = [])";
            $php .= "\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\tif(\$lastId = \$this->saveData(\$data)){";
            $php .= "\n\t\t\t\t\$this->Cacher->deleteItemsByTag(\$this->TagName);";
            $php .= "\n\t\t\t\treturn \$lastId;";
            $php .= "\n\t\t\t}else {";
            $php .= "\n\t\t\t\treturn false;";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\tthrow new \Exception(\$e->getMessage());";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* UPDATE */
        if (in_array('u', $data['crud'], true)) {
            /* Update Data */
            $php .= "\n\t/**";
            $php .= "\n\t * Update data from " . $data['className'] . "";
            $php .= "\n\t *";
            $php .= "\n\t * @param array \$data";
            $php .= "\n\t * @param integer \$id";
            $php .= "\n\t * @return bool";
            $php .= "\n\t */";
            $php .= "\n\tpublic function update(array \$data = [], int \$id)";
            $php .= "\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t\$update = \$this->updateData(\$data, [\$this->getPkey() => \$id]);";
            $php .= "\n\t\t\t\$this->Cacher->deleteItemsByTag(\$this->TagName);";
            $php .= "\n\t\t\treturn \$update;";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\tthrow new \Exception(\$e->getMessage());";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* DELETE */
        if (in_array('d', $data['crud'], true)) {
            /* Delete Data */
            $php .= "\n\t/**";
            $php .= "\n\t * Remove single or multiple data from " . $data['className'] . "";
            $php .= "\n\t *";
            $php .= "\n\t * @param array|integer \$data";
            $php .= "\n\t * @return bool";
            $php .= "\n\t */";
            $php .= "\n\tpublic function delete(\$data)";
            $php .= "\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t\$delete = \$this->deleteData(\$data);";
            $php .= "\n\t\t\t\$this->Cacher->deleteItemsByTag(\$this->TagName);";
            $php .= "\n\t\t\treturn \$delete;";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\tthrow new \Exception(\$e->getMessage());";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* End Class - Closing tag */
        $php .= "\n}\n";

        /* Return all string */
        return $php;
    }

    /**
     * PHP - Controllers Generator
     *
     * @param array $data
     * @return string
     */
    private function controllerPHP(array $data)
    {
        $php = "<?php\n";
        $php .= "/**";
        $php .= "\n* @project    " . $this->aproject;
        $php .= "\n* @package    App\Controllers\Privates";
        $php .= "\n* @author     " . $this->aauthor;
        $php .= "\n* @copyright  (c) " . date('Y') . " " . $this->acopyright;
        $php .= "\n* @created    on " . date('D M d Y') . "";
        $php .= "\n* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>";
        $php .= "\n*/\n\n";
        $php .= "namespace App\Controllers\Privates;\n\n";

        /* Class Head */
        $php .= "class " . $data['className'] . " extends \App\Controllers\PrivateController\n{";

        /* Declare Variable */
        $php .= "\n\t/* Declare Model */";
        $php .= "\n\tprivate \$MODEL;\n";

        /* Constructor */
        $php .= "\n\t/* Constructor */";
        $php .= "\n\tpublic function __construct(\\Slim\\Container \$container)\n\t{";
        $php .= "\n\t\t/* Call Parent Constructor */";
        $php .= "\n\t\tparent::__construct(\$container);\n";
        $php .= "\n\t\t/* Set Model */";
        $php .= "\n\t\t\$this->MODEL = new \App\Models\\" . $data['className'] . "(\$container);";
        $php .= "\n\t}\n";

        /* Function Get By ID */
        if (in_array('r', $data['crud'], true)) {
            $php .= "\n\t/* Function Get Data By ID */";
            $php .= "\n\tpublic function get(\$request, \$response, \$args)\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\tif (!is_numeric(\$args['id'])) throw new \Exception('ID tidak valid!');";
            $php .= "\n\t\t\t\$output = \$this->MODEL->getByID(\$args['id']);";
            $php .= "\n\t\t\tif (!empty(\$output)) {";
            $php .= "\n\t\t\t\treturn \$this->jsonSuccess(\$output);";
            $php .= "\n\t\t\t}else {";
            $php .= "\n\t\t\t\treturn \$this->jsonFail('Data tidak ditemukan', [], 404);";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* Function Create */
        if (in_array('c', $data['crud'], true)) {
            $php .= "\n\t/**";
            $php .= "\n\t * Create function";
            $php .= "\n\t *";
            $php .= "\n\t * @return void";
            $php .= "\n\t */";
            $php .= "\n\tpublic function create()\n\t{";
            $php .= "\n\t\t\$gump = new \GUMP('id');";
            $php .= "\n\t\t\$gump->validation_rules([" . $data['gump_validation'] . "\n\t\t]);\n";
            $php .= "\n\t\t\$gump->filter_rules([" . $data['gump_filter'] . "\n\t\t]);\n";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t/* Sanitize parameter */";
            $php .= "\n\t\t\t\$gump->xss_clean(\$this->param);";
            $php .= "\n\t\t\t\$safe = \$gump->run(\$this->param);\n";
            $php .= "\n\t\t\tif (\$safe === false) {";
            $php .= "\n\t\t\t\t\$ers = \$gump->get_errors_array();";
            $php .= "\n\t\t\t\t\$err = implode(', ', array_values(\$ers));\n";
            $php .= "\n\t\t\t\t/* Logger */";
            $php .= "\n\t\t\t\tif (\$this->container->get('settings')['mode'] != 'production') {";
            $php .= "\n\t\t\t\t\t\$this->logger->error(__METHOD__, ['USER_REQUEST' => \$this->user_data['USERNAME'], 'INFO' => \$ers]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t\tthrow new \Exception(\$err);";
            $php .= "\n\t\t\t} else {";
            $php .= "\n\t\t\t\ttry {";
            $php .= "\n\t\t\t\t\t/* Send to DB */";
            $php .= "\n\t\t\t\t\tif (\$lastID = \$this->MODEL->create(\$safe)) {";
            $php .= "\n\t\t\t\t\t\treturn \$this->jsonSuccess('Data berhasil ditambahkan', ['id' => \$lastID], null, 201);";
            $php .= "\n\t\t\t\t\t} else {";
            $php .= "\n\t\t\t\t\t\tthrow new \Exception('Penyimpanan gagal dilakukan!');";
            $php .= "\n\t\t\t\t\t}";
            $php .= "\n\t\t\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\treturn \$this->jsonFail('Invalid Request', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* Function Read */
        if (in_array('r', $data['crud'], true)) {

            $php .= "\n\t/* Function Read */";
            $php .= "\n\tpublic function read()\n\t{";
            $php .= "\n\t\t\$gump = new \GUMP('id');";
            $php .= "\n\t\t\$gump->validation_rules([\n\t\t\t\"draw\" => \"numeric\",\n\t\t\t\"start\" => \"numeric\",\n\t\t\t\"length\" => \"numeric\"," . $data['gump_validation'] . "\n\t\t]);\n";
            $php .= "\n\t\t\$gump->filter_rules([\n\t\t\t\"draw\" => \"sanitize_numbers\",\n\t\t\t\"start\" => \"sanitize_numbers\",\n\t\t\t\"length\" => \"sanitize_numbers\"," . $data['gump_filter'] . "\n\t\t]);\n";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t/* Sanitize parameter */";
            $php .= "\n\t\t\t\$gump->xss_clean(\$this->param);";
            $php .= "\n\t\t\t\$safe = \$gump->run(\$this->param);\n";
            $php .= "\n\t\t\tif (\$safe === false) {";
            $php .= "\n\t\t\t\t\$ers = \$gump->get_errors_array();";
            $php .= "\n\t\t\t\t\$err = implode(', ', array_values(\$ers) );\n";
            $php .= "\n\t\t\t\t/* Logger */";
            $php .= "\n\t\t\t\tif (\$this->container->get('settings')['mode'] != 'production') {";
            $php .= "\n\t\t\t\t\t\$this->logger->error(__METHOD__, ['USER_REQUEST' => \$this->user_data['USERNAME'], 'INFO' => \$ers]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t\tthrow new \Exception(\$err);";
            $php .= "\n\t\t\t} else {";
            $php .= "\n\t\t\t\ttry {";
            $php .= "\n\t\t\t\t\t/* Get Data */";
            $php .= "\n\t\t\t\t\t\$data = [];";
            $php .= "\n\t\t\t\t\t\$output = [];";
            $php .= "\n\t\t\t\t\t\$records = \$this->MODEL->read(\$safe);";
            $php .= "\n\t\t\t\t\t\$no = (int) \$safe['start'];";
            $php .= "\n\t\t\t\t\tforeach (\$records['datalist'] as \$cols) {";
            $php .= "\n\t\t\t\t\t\t\$no++;";
            $php .= "\n\t\t\t\t\t\t\$cols['no'] = \$no;";
            $php .= "\n\t\t\t\t\t\t\$data[] = \$cols;";
            $php .= "\n\t\t\t\t\t}\n";
            $php .= "\n\t\t\t\t\t\$output = [";
            $php .= "\n\t\t\t\t\t\t'recordsTotal' => \$records['recordsTotal'],";
            $php .= "\n\t\t\t\t\t\t'recordsFiltered' => \$records['recordsFiltered'],";
            $php .= "\n\t\t\t\t\t\t'data' => \$data,";
            $php .= "\n\t\t\t\t\t\t'draw' => (int) (isset(\$safe['draw']) ? \$safe['draw']: 0),";
            $php .= "\n\t\t\t\t\t];\n";
            $php .= "\n\t\t\t\t\treturn \$this->jsonSuccess(\$output);";
            $php .= "\n\t\t\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\treturn \$this->jsonFail('Invalid Request', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }

        /* Function Update */
        if (in_array('u', $data['crud'], true)) {

            $php .= "\n\t/* Function Update */";
            $php .= "\n\tpublic function update(\$request, \$response, \$args)\n\t{";
            $php .= "\n\t\t\$gump = new \GUMP('id');";
            $php .= "\n\t\t\$data = array_merge(\$this->param, \$args);";
            $php .= "\n\t\t\$gump->validation_rules([\n\t\t\t\"id\" => \"required|numeric\"," . $data['gump_validation'] . "\n\t\t]);\n";
            $php .= "\n\t\t\$gump->filter_rules([\n\t\t\t\"id\" => \"sanitize_numbers\"," . $data['gump_filter'] . "\n\t\t]);\n";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t/* Sanitize parameter */";
            $php .= "\n\t\t\t\$gump->xss_clean(\$data);";
            $php .= "\n\t\t\t\$safe = \$gump->run(\$data);\n";
            $php .= "\n\t\t\tif (\$safe === false) {";
            $php .= "\n\t\t\t\t\$ers = \$gump->get_errors_array();";
            $php .= "\n\t\t\t\t\$err = implode(', ', array_values(\$ers) );\n";
            $php .= "\n\t\t\t\t/* Logger */";
            $php .= "\n\t\t\t\tif (\$this->container->get('settings')['mode'] != 'production') {";
            $php .= "\n\t\t\t\t\t\$this->logger->error(__METHOD__, ['USER_REQUEST' => \$this->user_data['USERNAME'], 'INFO' => \$ers]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t\tthrow new \Exception(\$err);";
            $php .= "\n\t\t\t} else {";
            $php .= "\n\t\t\t\ttry {";
            $php .= "\n\t\t\t\t\t\$id = \$safe['id']; unset(\$safe['id']);";
            $php .= "\n\t\t\t\t\tif (\$this->MODEL->update(\$safe, \$id)) {";
            $php .= "\n\t\t\t\t\t\treturn \$this->jsonSuccess('Perubahan data berhasil');";
            $php .= "\n\t\t\t\t\t}else {";
            $php .= "\n\t\t\t\t\t\tthrow new \Exception('Tidak ada perubahan data!');";
            $php .= "\n\t\t\t\t\t}";
            $php .= "\n\t\t\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t\t\t}";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception \$e) {";
            $php .= "\n\t\t\treturn \$this->jsonFail('Invalid Request', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t}";
            $php .= "\n\t}\n";
        }


        /* Function Delete */
        if (in_array('d', $data['crud'], true)) {

            $php .= "\n\t/* Function Delete */";
            $php .= "\n\tpublic function delete(\$request, \$response, \$args)\n\t{";
            $php .= "\n\t\ttry {";
            $php .= "\n\t\t\t/** Path variable  */";
            $php .= "\n\t\t\t\$path = explode('/', \$request->getUri()->getPath());\n";
            $php .= "\n\t\t\t/** Batch delete */";
            $php .= "\n\t\t\tif (trim(end(\$path)) == 'batch') {";
            $php .= "\n\t\t\t\tif (!is_array(\$this->param['id'])) throw new \Exception('ID tidak valid!');";
            $php .= "\n\t\t\t\tif (in_array(false, array_map('is_numeric', \$this->param['id']))) throw new \Exception('ID tidak valid!');";
            $php .= "\n\t\t\t\t\$safe = \$this->param;";
            $php .= "\n\t\t\t} else {";
            $php .= "\n\t\t\t\t/** Single delete */";
            $php .= "\n\t\t\t\tif (!is_numeric(\$args['id'])) throw new \Exception('ID t idak valid!');";
            $php .= "\n\t\t\t\t\$safe = \$args;";
            $php .= "\n\t\t\t}\n";
            $php .= "\n\t\t\t/* Delete from DB */";
            $php .= "\n\t\t\tif (\$this->MODEL->delete(\$safe['id'])) {";
            $php .= "\n\t\t\t\treturn \$this->jsonSuccess('Data berhasil dihapus');";
            $php .= "\n\t\t\t}else {";
            $php .= "\n\t\t\t\tthrow new \Exception('Penghapusan gagal dilakukan!');";
            $php .= "\n\t\t\t}";
            $php .= "\n\t\t} catch (\Exception\$e) {";
            $php .= "\n\t\t\treturn \$this->jsonFail('Execution Fail!', ['error' => \$e->getMessage()]);";
            $php .= "\n\t\t}";
            $php .= "\n\t}";
        }


        $php .= "\n}\n";
        /* End Class */

        return $php;
    }
}
