<?php

class View
{
    protected string $base_dir;

    protected array $defaults;

    protected array $layout_variables = array();

    public function __construct(string $base_dir, array $defaults = array())
    {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }

    public function setLayoutVar(string $name, mixed $value)
    {
        $this->layout_variables[$name] = $value;
    }

    public function render(string $_path, array $_variables = array(), string|false $_layout)
    {
        $_file = $this->base_dir . '/' . $_path . '.php';

        extract(array_merge($this->defaults, $_variables));

        ob_start();
        ob_implicit_flush(0);

        require $_file;

        $content = ob_get_clean();

        if ($_layout) {
            $content = $this->render(
                $_layout,
                array_merge(
                    $this->layout_variables,
                    array('_content' => $content)
                ),
                false
            );
        }

        return $content;
    }

    public function escape(string $string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
