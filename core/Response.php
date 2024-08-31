<?php

class Response
{
    protected string $content;

    protected int $status_code = 200;

    protected string $status_text = 'OK';

    protected array $http_headers = array();

    public function send()
    {
        header('HTTP/1.1 ' . $this->status_code . ' ' . $this->status_text);

        foreach ($this->http_headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function setStatusCode(int $status_code, string $status_text = '')
    {
        $this->status_code = $status_code;
        $this->status_text = $status_text;
    }

    public function setHttpHeader(string $name, string $value)
    {
        $this->http_headers[$name] = $value;
    }
}
