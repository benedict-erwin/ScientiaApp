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
    $CachedString = $IC->getItem($ckey);
    if (is_null($CachedString->get())) {
        $sql = "SELECT 
                    c.nama  nama_m,
                    c.url,
                    c.controller,
                    c.id_groupmenu,
                    d.nama  nama_g,
                    c.icon  icon_m,
                    d.icon  icon_g,
                    c.aktif aktif_m,
                    d.aktif aktif_g,
                    c.tipe,
                    c.urut order_m,
                    d.urut order_g
                FROM m_menu c
                LEFT JOIN m_groupmenu d
                    ON c.id_groupmenu = d.id_groupmenu 
                WHERE d.aktif=1 AND c.aktif=1 
                ORDER  BY d.urut ASC, c.urut ASC";
        $query = $container->database->pdo->prepare($sql);
        $query->execute();
        $result = $query->fetchAll();
        $CachedString->set($result)->expiresAfter(28800)->addTag($container->get('settings')['dbnya']['SIGNATURE'] . '_router'); //8jam
        $IC->save($CachedString);
    }else {
        $result = $CachedString->get();
    }
    foreach ($result as $res) {
        $url = strtolower($res['url']);
        $method = strtolower($res['tipe']);
        $controller = $res['controller'];
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
