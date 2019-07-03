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

class MenuController extends \App\Controllers\PrivateController
{
    public function index()
    {
        $gump = new \GUMP();
        $gump->validation_rules(["reload" => "numeric"]);
        $gump->filter_rules(["reload" => "trim"]);

        try {
            $gump->xss_clean($this->param);
            $safe = $gump->run($this->param);

            if ($safe === false) {
                $ers = $gump->get_errors_array();
                $err = implode(', ', array_values($ers));

                /* Logger */
                if ($this->container->get('settings')['mode'] != 'production') {
                    $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['INFO' => $ers]);
                }

                throw new \Exception($err);
            } else {
                return $this->getMenus($this->user_data['ID_ROLE'], $safe['reload']);
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }

    private function getMenus($idrole=NULL, $reload=false)
    {
        try {
            $GROUPMENU = array();
            if ($reload==1) {
                $this->InstanceCache->deleteItemsByTags([
                    $this->sign . '_getMenus',
                    $this->sign . '_restapi_router',
                    $this->sign . '_M_menu_read',
                    $this->sign . '_CRUDGenerator_read_menu'
                ]);
            }
            $ckey = hash('md5', $this->sign . '_cmenu_' . $idrole);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                $sql = "SELECT a.idrole,
                            b.nama,
                            a.id_menu,
                            c.nama    nama_m,
                            CONCAT('ic_', REPLACE(LOWER(c.nama), ' ', '_')) badge,
                            c.url,
                            c.controller,
                            c.id_groupmenu,
                            d.nama    nama_g,
                            c.icon    icon_m,
                            d.icon    icon_g,
                            c.aktif aktif_m,
                            d.aktif aktif_g,
                            c.tipe,
                            c.urut order_m,
                            d.urut order_g
                        FROM j_menu a
                            LEFT JOIN m_role b
                                ON a.idrole = b.idrole
                            LEFT JOIN m_menu c
                                ON a.id_menu = c.id_menu
                            LEFT JOIN m_groupmenu d
                                ON c.id_groupmenu = d.id_groupmenu
                        WHERE b.idrole=:idrole
                            AND c.tipe=:tipe
                            AND d.aktif=1
                            AND c.aktif=1
                        ORDER BY d.urut ASC, c.urut ASC";

                $tipe = 'MENU';
                $query = $this->dbpdo->pdo->prepare($sql);
                $query->bindParam(':idrole', $idrole, \PDO::PARAM_STR);
                $query->bindParam(':tipe', $tipe, \PDO::PARAM_STR);
                $query->execute();
                $result = $query->fetchAll(\PDO::FETCH_ASSOC);
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
                    $this->logger->addError(__CLASS__ . ' :: ' . __FUNCTION__ . ' :: ', ['INFO' => $ers]);
                }
                throw new \Exception($err);
            } else {
                $cn = $this->getPermission($safe['path'], $this->user_data['ID_ROLE']);
                return $this->jsonSuccess($cn);
            //     try {
            //         $result = null;
            //         $ckey = md5($this->sign . '_cauth_' . $this->user_data['ID_USER'] . $safe['path']);
            //         $CachedString = $this->InstanceCache->getItem($ckey);
            //         if (is_null($CachedString->get())) {
            //             $sql = "SELECT a.idrole,
            //                         b.nama,
            //                         a.id_menu,
            //                         c.nama    nama_m,
            //                         c.url,
            //                         c.controller,
            //                         c.id_groupmenu,
            //                         d.nama nama_g,
            //                         c.icon icon_m,
            //                         d.icon icon_g,
            //                         c.aktif aktif_m,
            //                         d.aktif aktif_g,
            //                         c.tipe,
            //                         c.urut order_m,
            //                         d.urut order_g
            //                     FROM j_menu a
            //                     LEFT JOIN m_role b
            //                         ON a.idrole = b.idrole
            //                     LEFT JOIN m_menu c
            //                         ON a.id_menu = c.id_menu
            //                     LEFT JOIN m_groupmenu d
            //                         ON c.id_groupmenu = d.id_groupmenu
            //                     WHERE b.idrole=:idrole AND c.url=:url
            //                     ORDER BY d.urut ASC, c.urut ASC";

            //             /* log sql */
            //             if($this->container->get('settings')['mode'] != 'production'){
            //                 $this->logger->addInfo(__CLASS__ . ' query :: ' . preg_replace('/\v(?:[\v\h]+)/', ' ', $sql));
            //             }

            //             $path = ($safe['path']=='/') ? '/home':$safe['path'];
            //             $query = $this->dbpdo->pdo->prepare($sql);
            //             $query->bindParam(':idrole', $this->user_data['ID_ROLE'], \PDO::PARAM_STR);
            //             $query->bindParam(':url', $path, \PDO::PARAM_STR);
            //             $query->execute();
            //             $result = $query->fetch();
            //             $CachedString->set($result)->expiresAfter($this->CacheExp)->addTag($this->sign . '_getAuthMenu_');
            //             $this->InstanceCache->save($CachedString);
            //         } else {
            //             $result = $CachedString->get();
            //         }

            //         if ($result) {
            //             $cn = $this->getPermission($safe['path'], $this->user_data['ID_ROLE']);
            //             return $this->jsonSuccess($cn);
            //         }else {
            //             throw new \Exception("User not authorized!");
            //         }

            //     } catch (\PDOException $e) {
            //         throw new \Exception("User not authorized!");
            //     }
            }
        } catch (\Exception $e) {
            return $this->jsonFail('Unable to process request', ['error' => $e->getMessage()]);
        }
    }

    private function getPermission(string $url = null, int $idrole=null)
    {
        try {
            $ckey = md5($this->sign . '_perm_' . $url . $idrole);
            $CachedString = $this->InstanceCache->getItem($ckey);
            if (is_null($CachedString->get())) {
                //Controller
                $data = [];
                $query = $this->dbpdo->get('m_menu', 'controller', ['url' => $url]);
                $controller = explode(':', $query);
                $controller = trim($controller[0]) . ':%';

                //Permission
                $sql = "SELECT a.idrole, a.deskripsi, c.idrole, c.controller
                        FROM m_role a
                        LEFT JOIN (
                            SELECT b.id_menu, b.idrole, m.controller, m.aktif
                            FROM j_menu b
                            LEFT JOIN m_menu m
                                ON b.id_menu=m.id_menu
                        ) c ON a.idrole=c.idrole
                        WHERE c.controller LIKE :controller AND a.idrole=:idrole AND c.aktif=1
                        ORDER BY a.idrole";

                $query = $this->dbpdo->pdo->prepare($sql);
                $query->bindParam(':controller', $controller, \PDO::PARAM_STR);
                $query->bindParam(':idrole', $idrole, \PDO::PARAM_INT);
                $query->execute();
                $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($result as $res) {
                    $cnt = explode(':', $res['controller']);
                    $data[] = end($cnt);
                }

                $CachedString->set($data)->expiresAfter($this->CacheExp)->addTag($this->sign . '_getPermission_');
                $this->InstanceCache->save($CachedString);
            }else {
                $data = $CachedString->get();
            }
            return array_values($data);

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
}
