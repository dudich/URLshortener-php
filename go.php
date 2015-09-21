<?php
/**
 * Created by PhpStorm.
 * Date: 21.09.15
 * Time: 17:58
 */

spl_autoload_register(function ($class) {
    include $class . '.class.php';
});
$storage = new Storage();

if (!empty($_GET['url'])) {
    echo '//' . $_SERVER['SERVER_NAME'] . "/" . $storage->urlToShortCode($_GET['url']);
    exit;
}

$url = $storage->getUrlFromDbByHash(str_replace('/', '', $_SERVER['REQUEST_URI']));
if (isset($url['link']))
    header("Location: " . $url['link']);
else
    echo "url not found";