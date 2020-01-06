<?php

require_once './vendor/autoload.php';

$c = \Pars\Curl::app('https://basemarket.ru/')
->config_load();
//    ->set(CURLOPT_HEADER, 1)
//->add_header('User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0')
////    ->set(CURLOPT_NOBODY, true) // только заголовки
//    ->set(CURLOPT_REFERER, 'google.com')
//    ->set(CURLOPT_FOLLOWLOCATION, true);
//
//    ->set(CURLOPT_COOKIEJAR, 'temp/cookie.txt' )// сохранение куки в файл
//    ->set(CURLOPT_COOKIEFILE, 'temp/cookie.txt' )// использование куки из файла
////    ->set(CURLOPT_COOKIESESSION, true )// передаються только куки у которых есть дата истечения
// //   ->set(CURLOPT_RETURNTRANSFER, true) // сохранение данных в файл
//    ->https(0);

//$c->config_save();
//$c->cookie('cookie.txt');
//$c->config_load();
//$c->getSet();
$html = $c->request('garantii');
//
//
\Hellpers\Helper::xprint($html['headers']);
echo $html['html'];
