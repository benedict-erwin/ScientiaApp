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
##> Redirect
$app->get('/', function ($request, $response, $args) {
    echo '<meta http-equiv="refresh" content="0; url=scientia/" />';
});

##> Login Page
$app->get('/scientia/login', function ($request, $response, $args) use ($container) {
    $data['template'] = $container->get('settings')['cms_template'];
    return $this->view->render($response, 'Login/default/layout.twig', $data);
});

##> Default Page
$app->get('/scientia/', function ($request, $response, $args) use ($container) {
    $data['page'] = "home";
    $data['jsver'] = $this->get('settings')['jsversion'];
    $data['template'] = $container->get('settings')['cms_template'];
    return $this->view->render($response, 'Home/index.html', $data);
});


## Dynamic Route load template
$app->get('/scientia/{page}', function ($request, $response, $args) use ($container) {
    $data['page'] = $args['page'];
    $data['jsver'] = $this->get('settings')['jsversion'];
    $data['template'] = $container->get('settings')['cms_template'];
    return $this->view->render($response, 'Home/index.html', $data);
});

/** REST API **/
##> Load from DataBase
try {
    $ckey = hash('md5', $container->get('settings')['dbnya']['SIGNATURE'] . '_restapi_router');
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
            $app->post($url, "\App\Controller\\$controller");
        }
    }
    $result = null;
    $res = null;
    $query = null;
} catch (\PDOException $e) {
    $code = 'SC501';
    $container->logger->error('REST-API ROUTER ERROR :: ' . $e->getMessage(),
        [
            'code' => $code,
            'sql' => $container->database->last()
        ]
    );

    $msg = [
        'success' => false,
        'error' => "[$code] - Please contact your administrator for more support"
    ];

    header('Internal Server Error', true, 500);
    print(json_encode($msg));
    exit();
}
