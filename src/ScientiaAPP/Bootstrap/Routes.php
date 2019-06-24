<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    ScientiaAPP/Bootstrap
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/** Front End **/
##> Login Page
$app->get('/login', function ($request, $response, $args) {
    $data['template'] = 'notika';
    return $this->view->render($response, 'Login/default/layout.html', $data);
});

##> Default Page
$app->get('/', function ($request, $response, $args) {
    $data['page'] = "home";
    $data['jsver'] = $this->get('settings')['jsversion'];
    $data['template'] = 'notika';
    return $this->view->render($response, 'Home/index.html', $data);
});

## Dynamic Route load template
$app->get('/{page}', function ($request, $response, $args) {
    $data['page'] = $args['page'];
    $data['jsver'] = $this->get('settings')['jsversion'];
    $data['template'] = 'notika';
    return $this->view->render($response, 'Home/index.html', $data);
});

/** Back End **/
##> Load from DataBase
try {
    $ckey = hash('md5', $container->get('settings')['dbnya']['SIGNATURE'] . '_backend_router');
    $api_path = $container->get('settings')['api_path'];
    $CachedString = $IC->getItem($ckey);
    if (is_null($CachedString->get())) {
        $result = $container->database->select('m_menu (menu)',
            [
                '[>]m_groupmenu (group)' => 'id_groupmenu'
            ],
            [
                'menu.nama (nama_m)',
                'menu.url',
                'menu.controller',
                'menu.id_groupmenu',
                'group.nama (nama_g)',
                'menu.icon (icon_m)',
                'group.icon (icon_g)',
                'menu.aktif (aktif_m)',
                'group.aktif (aktif_g)',
                'menu.tipe',
                'menu.is_public',
                'menu.urut (order_m)',
                'group.urut (order_g)'
            ],
            [
                'menu.aktif' => 1,
                'group.aktif' => 1,
                'ORDER' => [
                    'group.urut' => 'ASC',
                    'menu.urut' => 'ASC'
                ]
            ]
        );
        $CachedString->set($result)->expiresAfter(28800)->addTag($container->get('settings')['dbnya']['SIGNATURE'] . '_router'); //8jam
        $IC->save($CachedString);
    }else {
        $result = $CachedString->get();
    }
    foreach ($result as $res) {
        $url = strtolower($res['url']);
        $method = strtolower($res['tipe']);
        $exp = explode(':', $res['controller']);
        $controller = (!in_array($exp[0], ['PrivateController', 'PublicController', 'LoginController'])) ? (($res['is_public'] == 0) ? "Privates\\":"Publics\\") . $res['controller']: $res['controller'];
        if ($method == 'post') {
            $app->post("$url", "\App\Controller\\$controller");
        }
    }
    $result = null;
    $res = null;
    $query = null;
} catch (\PDOException $e) {
    //Maybe a custom error page here
    die($e->getMessage());
}
