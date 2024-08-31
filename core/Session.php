<?php

class Session
{
    protected static bool $session_started = false;

    protected static bool $session_id_regenerated = false;

    public function __construct()
    {
        if (!self::$session_started) {
            // 1回のリクエストで1回のみ
            session_start();

            self::$session_started = true;
        }
    }

    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function get(string $name, mixed $default): mixed
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    public function remove(string $name): void
    {
        unset($_SESSION[$name]);
    }

    public function clear(): void
    {
        $_SESSION = array();
    }

    // セッションIDを新しく発行
    public function regenerate(bool $destroy = true) : void
    {
        if (!self::$session_id_regenerated) {
            session_regenerate_id($destroy);

            self::$session_id_regenerated = true;
        }
    }

    public function setAuthenticated(bool $bool): void
    {
        $this->set('_authenticated', $bool);

        $this->regenerate();
    }

    public function isAuthenticated()
    {
        return $this->get('_authenticated', false);
    }
}