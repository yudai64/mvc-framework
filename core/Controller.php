<?php

abstract class Controller
{
    protected string $controller_name;

    protected string $action_name;

    protected Application $application;

    protected Request $request;

    protected Response $response;

    protected Session $session;

    protected DbManager $db_manager;

    protected array|true $auth_actions = array();

    public function __construct(Application $application)
    {
        $this->controller_name = strtolower(substr(get_class($this), 0, -10)); // Controllerの10文字を取り除く

        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
    }

    public function run(string $action, array $params = array()): string
    {
        $this->action_name = $action;

        $action_method = $action . 'Action';
        if (!method_exists($this, $action_method)) {
            $this->forward404();
        }

        if ($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
            throw new UnauthorizedActionException();
        }

        $content = $this->$action_method($params);

        return $content;
    }

    protected function needsAuthentication(string $action)
    {
        if ($this->auth_actions || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))) {
            return true;
        }

        return false;
    }

    protected function render(array $variables = array(), ?string $template, string $layout = 'layout')
    {
        $defaults = array(
            'request' => $this->request,
            'base_url' => $this->request->getBaseUrl(),
            'session' => $this->session,
        );

        $view = new View($this->application->getViewDir(), $defaults);

        if (is_null($template)) {
            $template = $this->action_name;
        }

        $path = $this->controller_name . '/' . $template;

        return $view->render($path, $variables, $layout);
    }

    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from ' . $this->controller_name . '/' . $this->action_name);
    }

    protected function redirect(string $url)
    {
        if (!preg_match('#https?://#', $url)) {
            $protocol = $this->request->isSsl() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUrl();

            $url = $protocol . $host . $base_url . $url;
        }

        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    protected function generateCsrfToken(string $form_name)
    {
        $key = 'csrf_token/' . $form_name;
        $tokens = $this->session->get($key, array());
        // 複数画面の対応
        if (count($tokens) > 10) {
            array_shift($tokens);
        }

        $token = sha1($form_name . session_id(). microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    protected function checkCsrfToken(string $form_name, string $token)
    {
        $key = 'csrf_token/' . $form_name;
        $tokens = $this->session->get($key, array());

        if (false !== ($pos = array_search($token, $tokens, true))) {
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);

            return true;
        }

        return false;
    }
}
