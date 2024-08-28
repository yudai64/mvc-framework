<?php

class Request
{
    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function getGet($name, $default)
    {
        return $_GET[$name] ?? $default;
    }

    public function getPost($name, $default)
    {
        return $_POST[$name] ?? $default;
    }

    public function getHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    public function isSsl(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public function getRequestUri()
    {
        // URIのホスト部分より後の値が格納されている。GETパラメータの値も含む。
        return $_SERVER['REQUEST_URI'];
    }

    /*
    * ベースURLはこのフレームワーク上で扱う上での名称。
    * ホスト部分の後ろから、フロントコントローラまでの値。
    * HTML内にリンクを作成する際に使用する。
    */
    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];

        $request_uri = $this->getRequestUri();

        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;
        } elseif (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    /*
    * ベースURLより後の値。ただしGETパラメータは含まない。
    * 内部的なURL
    * この値を用いて、RouterクラスがURLとコントローラの対応付けを行う。
    */
    public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();

        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        $path_info = (string)substr($request_uri, strlen($base_url));

        return $path_info;
    }
}
