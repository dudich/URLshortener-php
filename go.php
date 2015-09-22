<?php
define('DIRECT_ACCESS', 1);
spl_autoload_register(function ($class) {
    include $class . '.class.php';
});
$storage = new Storage();
if (isset($_GET['url'])) {
    try {
        $code = $storage->urlToShortCode(base64_decode($_GET['url']));
        echo json_encode(array('error' => false, 'result' => '//' . $_SERVER['SERVER_NAME'] . "/" . $code));
        exit;
    } catch (Exception $e) {
        echo json_encode(array('error' => $e->getMessage()));
        exit;
    }
}


$url = $storage->getUrlFromDbByHash(str_replace('/', '', $_SERVER['REQUEST_URI']));
if (isset($url['link']))
    header("Location: " . $url['link']);
else
    echo "url not found";