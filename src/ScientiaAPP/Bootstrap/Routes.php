<?php
/*
 * @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
 * @package    \Bootstrap
 * @author     Benedict E. Pranata
 * @copyright  (c) 2018 benedict.erwin@gmail.com
 * @created    on Wed Sep 05 2018
 * @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

/** Front End **/
##> Redirect default page
$app->redirect('/', 'scientia/', 301);

##> Forbidden
$app->get('/api/forbidden', function(){
    header('Internal Server Error', true, 403);
    $msg = [
        'success' => false,
        'error' => "[SC403] - The server understood the request, but is refusing to authorize it."
    ];
    print(json_encode($msg));
    exit();
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
    return $this->view->render($response, 'Home/index.twig', $data);
});


## Dynamic Route load template
$app->get('/scientia/{page}', function ($request, $response, $args) use ($container) {
    $data['page'] = $args['page'];
    $data['jsver'] = $this->get('settings')['jsversion'];
    $data['template'] = $container->get('settings')['cms_template'];
    return $this->view->render($response, 'Home/index.twig', $data);
});

/** REST API **/
##> Load from DataBase
try {
    $ckey = hash('md5', $container->get('settings')['dbnya']['SIGNATURE'] . '_restapi_router');
    $api_path = $container->get('settings')['api_path'];
    $CachedString = $container->cacher->getItem($ckey);
    if (!$CachedString->isHit()) {
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
                'menu.tipe[!]' => 'MENU',
                'ORDER' => [
                    'group.urut' => 'ASC',
                    'menu.urut' => 'ASC'
                ]
            ]
        );
        $CachedString->set($result)->expiresAfter(28800)->addTag($container->get('settings')['dbnya']['SIGNATURE'] . '_router'); //8jam
        $container->cacher->save($CachedString);
    }else {
        $result = $CachedString->get();
    }
    foreach ($result as $res) {
        $method = strtoupper($res['tipe']);
        $exp = explode(':', $res['controller']);
        $controller = (!in_array($exp[0], ['BaseController', 'PrivateController', 'PublicController'])) ? (($res['is_public'] == 0) ? "Privates\\":"Publics\\") . $res['controller']: $res['controller'];
        $url = "/{$api_path}" . strtolower($res['url']);

        switch ($method) {
            case 'GET': # Get data by id
                $app->get("$url", "\App\Controllers\\$controller");
                break;
            case 'POST': # Create, READ
                $app->post($url, "\App\Controllers\\$controller");
                break;
            case 'PUT': # Update
                $app->put("$url/{id}", "\App\Controllers\\$controller");
                break;
            case 'DELETE': # Delete, Batch Delete
                $act = explode('/', $url);
                $act = end($act);
                $act = ($act == 'batch') ? '/batch':'/{id}';
                $app->delete("{$url}{$act}", "\App\Controllers\\$controller");
                break;
            default:
                break;
        }

    }
    $result = null;
    $res = null;
} catch (\PDOException $e) {
    $code = 'SC502';
    $container->logger->error('REST-API ROUTER ERROR :: ' . $e->getMessage(),
        [
            'code' => $code,
            'message' => $container->database->last()
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
