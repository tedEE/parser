<?php

namespace Pars;

use Hellpers\Helper;

class Curl
{
    private $ch;     // экземляр курла
    private $host; // хост - базовая часть урла без слеша на конце
    private $seting_curl; // настройки курла
    private $headers = ['Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                             'Accept-Encoding: gzip, deflate',
                             'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3']; //ряд заранее известных заголовков

    //
    // Инициализация класса для конкретного домена
    //
    private function __construct($host)
    {
        $this->ch = curl_init();
        $this->host = $host;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, []);
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    public static function app($host)
    {
        return new self($host);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     *  задать настройки курла
     */
    public function set($name, $value)
    {
        curl_setopt($this->ch, $name, $value);
        $this->seting_curl[$name] = $value;
//        $this->seting_curl[]="$name => $value|";
        return $this;
    }

    public function getSet(){
        Helper::xprint($this->seting_curl);
    }

    /**
     * Настройка конфигурации для метода POST
     * array - ассоциативный массив с параметрами
     * false - отлючить обращение методом POST
     */
    public function post($data){
        if ($data === false) {
            $this->set(CURLOPT_POST, false);
            return $this;
        }

        $this->set(CURLOPT_POST, true);
        $this->set(CURLOPT_POSTFIELDS, http_build_query($data));
        return $this;
    }

    // вспомогательные функци инкапсулрующие настройк

    /**
     * Включает или выключает заголовки ответа
     *
     * @param int $act
     * 1 - есть, 0 - нет
     */
    public function headers($act){
        $this->set(CURLOPT_HEADER, $act);
        return $this;
    }

    /**
     * Устанавливает, следовать ли за перенаправлением
     *
     * @param bool $param
     * TRUE - следовать
     * FALSE - не следовать
     */
    public function follow($param) {
        $this->set(CURLOPT_FOLLOWLOCATION, $param);
        return $this;
    }

    public function cookie($path)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $path, '');
        $this->set(CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/' . $path);
        $this->set(CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/' . $path);
        // ->set(CURLOPT_COOKIESESSION, true )// передаються только куки у которых есть дата истечения
    }

    public function referer($str)
    {
        $ref = ["http://www.google.com", "http://www.yandex.ru/", "http://www.ya.ru/", "", "https://mail.ru/", "https://www.yahoo.com/"];
        if(!$str){
            $rand_ref = array_rand($ref, 1);
            $str = $ref[$rand_ref];
        }
        return $str;
    }

    public function https($act)
    {
        $this->set(CURLOPT_SSL_VERIFYPEER, $act);
        $this->set(CURLOPT_SSL_VERIFYHOST, $act);
//        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $act);
//        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $act);
        return $this;
    }

    /**
     * Добавить 1 произвольный http-заголовок к запросу
     * @param $header
     * @return $this
     */
    public function add_header($header){
        $this->seting_curl[CURLOPT_HTTPHEADER][] = $header;
        $this->set(CURLOPT_HTTPHEADER, $this->seting_curl[CURLOPT_HTTPHEADER]);
        return $this;
    }


    /**
     * Добавить несколько произвольных http-заголовоков к запросу
     * @param $headers
     * @return $this
     */
    public function add_headers($headers){
        foreach($headers as $h)
            $this->seting_curl[CURLOPT_HTTPHEADER][] = $h;

        $this->set(CURLOPT_HTTPHEADER, $this->seting_curl[CURLOPT_HTTPHEADER]);
        return $this;
    }

    /**
     * Очиситить массив произвольных http-заголовков
     */
    public function clear_headers(){
        $this->seting_curl[CURLOPT_HTTPHEADER] = array();
        $this->set(CURLOPT_HTTPHEADER, $this->seting_curl[CURLOPT_HTTPHEADER]);
        return $this;
    }

    /**
     * @return $this
     *  сохранение настроек курла в файл
     */
    public function config_save()
    {
        $str = serialize($this->seting_curl);
        file_put_contents('./config.txt', $str);
        return $this;
    }

    /**
     * @return $this
     *  загрузка настроек курла из файла
     */
    public function config_load()
    {
        $str = file_get_contents('./config.txt');
        $unserialaiz_seting = unserialize($str, [true]);
        Helper::xprint($unserialaiz_seting);
        foreach ($unserialaiz_seting as $key => $seting) {
            $this->set($key, $seting);

        }
        return $this;
    }

    /**
     * @param $url
     * @return mixed
     * выполнить запрос
     */
    public function request($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->make_url($url));
        $data = curl_exec($this->ch);
//        return $data;
        return $this->process_result($data);
    }

    private function make_url($url)
    {
        if ($url[0] != '/')
            $url = '/' . $url;

        return $this->host . $url;
    }

    private function process_result($data){
        /* Если HEADER отключен */
        if(!isset($this->seting_curl[CURLOPT_HEADER]) || !$this->seting_curl[CURLOPT_HEADER]) {
            return array(
                'headers' => array(),
                'html' => $data
            );
        }

        /* Разделяем ответ на headers и body */
        $info = curl_getinfo($this->ch);

        $headers_part = trim(substr($data, 0, $info['header_size'])); // trim - чтобы обрезать перенос строки в конце
        $body_part = substr($data, $info['header_size']);

        /* Определяем символ переноса строки */
        $headers_part = str_replace("\r\n", "\n", $headers_part); // винда в никсовую
        $headers = str_replace("\r", "\n", $headers_part); // мак в никсовую

        /* Берем последний headers */
        $headers = explode("\n\n", $headers);
        $headers_part = end($headers);

        /* Парсим headers */
        $lines = explode("\n", $headers_part);
        $headers = array();

        $headers['start'] = $lines[0];

        $count_lines = count($lines);

        for($i = 1; $i < $count_lines; $i++){
            $del_pos = strpos($lines[$i], ':');
            $name = substr($lines[$i], 0, $del_pos);
            $value = substr($lines[$i], $del_pos + 2);
            $headers[$name] = $value;
        }

        return array(
            'headers' => $headers,
            'html' => $body_part
        );
    }

//    private function process_result($data)
//    {
//        $p_n = "\n";
//        $p_rn = "\r\n";
//
//        $h_end_n = strpos($data, $p_n . $p_n);    // int - false
//        $h_end_rn = strpos($data, $p_rn . $p_rn); // int - false
//
//        $start = $h_end_n; // h_end_n int
//        $p = $p_n;         // \n
//
//        if ($h_end_n === false || $h_end_rn < $h_end_n) {
//            $start = $h_end_rn;
//            $p = $p_rn;
//        }
//
//        $headers_part = substr($data, 0, $start);
//        $body_part = substr($data, $start + strlen($p) * 2);
//
//        $lines = explode($p, $headers_part);
//        $headers = [];
//
//        $headers['start'] = $lines[0];
//
//        for ($i = 1, $iMax = count($lines); $i < $iMax; $i++) {
//            $del_pos = strpos($lines[$i], ':');
//            $name = substr($lines[$i], 0, $del_pos);
//            $value = substr($lines[$i], $del_pos + 2);
//            $headers[$name] = $value;
//        }
//
//        return [
//            'headers' => $headers,
//            'html'    => $body_part,
//        ];
//    }
}
