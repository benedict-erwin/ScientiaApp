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

class MenuController extends \App\Controllers\PrivateController
{
    private $M_MENU, $J_MENU;

    /* Constructor */
    public function __construct(\Slim\Container $container)
    {
        /* Call Parent Constructor */
        parent::__construct($container);

        /* Set Model */
        $this->M_MENU = new \App\Models\M_menu($container);
        $this->J_MENU = new \App\Models\J_menu($container);
    }

    public function index()
    {
        return $this->getMenus((int)$this->user_data['ID_ROLE']);
    }

    private function getMenus(int $idrole)
    {
        try {
            $GROUPMENU = [];
            $ckey = hash('md5', $this->sign . '_groupmenu_' . $idrole);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (!$CachedString->isHit()) {
                $result = $this->M_MENU->getMenu($idrole);
                $c = 0;
                $x = 0;
                foreach ($result as $r) {
                    if ($c != $r['id_groupmenu']) {
                        $c = $r['id_groupmenu'];
                        $x = 0;
                    }

                    $gm = str_replace(' ', '_', strtolower($r['nama_g']));
                    $GROUPMENU[$r['id_groupmenu']]['NM_GROUPMENU'] = $r['nama_g'];
                    $GROUPMENU[$r['id_groupmenu']]['GM'] = $gm;
                    $GROUPMENU[$r['id_groupmenu']]['ICON_GROUPMENU'] = $r['icon_g'];
                    $GROUPMENU[$r['id_groupmenu']]['ORDER'] = $r['order_g'];
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['GM'] = $gm;
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['NM_MENU'] = $r['nama_m'];
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['ICON_MENU'] = $r['icon_m'];
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['BADGE_MENU'] = $r['badge'];
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['URL'] = $r['url'];
                    $GROUPMENU[$r['id_groupmenu']]['MENU_LIST'][$x]['ORDER'] = $r['order_m'];
                    $x++;
                }
                $CachedString->set($GROUPMENU)->expiresAfter($this->CacheExp)->addTag($this->sign . '_getMenus_');
                $this->InstanceCache->save($CachedString);
            } else {
                $GROUPMENU = $CachedString->get();
            }
            return $this->jsonSuccess(array_values($GROUPMENU), ['sp_uname'=>$this->user_data['USERNAME']]);
        } catch (PDOException $e) {
            throw new \Exception("Cannot generate menu!");
        }
    }

    public function getAuthMenu()
    {
        $gump = new \GUMP();
        $gump->validation_rules(["path" => "required"]);
        $gump->filter_rules(["path" => "trim|lower_case"]);

        try {
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if($this->container->get('settings')['mode'] != 'production'){
                    $this->logger->error(__METHOD__ . ' :: ', ['INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                $cn = $this->getPermission($safe['path'], (int)$this->user_data['ID_ROLE']);
                if ($cn) {
                    return $this->jsonSuccess($cn);
                }else {
                    return $this->jsonFail('Execution Fail!', ['error' => 'Unauthorized!']);
                }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }

    private function getPermission(string $url = null, int $idrole=null)
    {
        try {
            $data = [];
            $result = $this->J_MENU->getPermission($url, $idrole);
            if (!empty($result)) {
                foreach ($result as $res) {
                    $cnt = explode(':', $res['controller']);
                    $data[] = end($cnt);
                }
                return array_values(array_unique($data));
            }
            return false;

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
}
