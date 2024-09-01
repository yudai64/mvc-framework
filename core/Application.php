<?php

abstract class Application
{
    protected bool $debug = false;

    protected Request $request;

    protected Response $response;

    protected Session $session;

    protected DbManager $db_manager;

    protected Router $router;

    protected array $login_action = array();

    public function __construct(bool $debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    protected function setDebugMode(bool $debug): void
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    protected function initialize() : void
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->db_manager = new DbManager();
        $this->router = new Router($this->registerRoutes());
    }

    protected function configure() {}

    abstract public function getRootDir();

    abstract protected function registerRoutes(): array;

    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getDbManager(): DbManager
    {
        return $this->db_manager;
    }

    public function getControllerDir(): string
    {
        return $this->getRootDir() . '/controllers';
    }

    public function getViewDir(): string
    {
        return $this->getRootDir() . '/views';
    }

    public function getModelDir(): string
    {
        return $this->getRootDir() . '/models';
    }

    public function getWebDir(): string
    {
        return $this->getRootDir() . '/web';
    }

    public function run()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
            }
            
            $controller = $params['controller'];
            $action = $params['action'];
            
            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        } catch (UnauthorizedActionException $e) {
            list($controller, $action) = $this->login_action;
            $this->runAction($controller, $action);
        }

        $this->response->send();
    }

    public function runAction(string $controller_name, string $action_name, array $params = array()): void
    {
        $controller_class = ucfirst($controller_name) . 'Controller';

        /** @var Controller|false $controller */
        $controller = $this->findController($controller_class);
        if ($controller === false) {
            throw new HttpNotFoundException($controller_class . ' controller is not found.');
        }

        $content = $controller->run($action_name, $params);

        $this->response->setContent($content);
    }

    protected function findController(string $controller_class)
    {
        if (!class_exists($controller_class)) {
            $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
            if (!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;
                if (!class_exists($controller_class)) {
                    return false;
                }
            }
        }

        return new $controller_class($this);
    }

    protected function render404Page(Exception $e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page Not Found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(
<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html"; charset=utf-8" />
    <title>404</title>
</head>
</html>
<body>
    {$message}
</body>
</html>
EOF
        );
    }
}
