<?php

class DbManager
{
    protected array $connections = array();

    protected array $repository_connection_map = array();

    protected $repositories = array();

    public function connect(string $name, array $params): void
    {
        $params = array_merge(
            array(
                'dsn' => null,
                'user' => '',
                'password' => '',
                'options' => array(),
            ),
            $params
        );

        $con = new PDO($params['dsn'], $params['user'], $params['password'], $params['options']);

        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connections[$name] = $params;
    }

    public function getConnection(string $name = ''): PDO
    {
        if ($name === '') {
            return current($this->connections);
        }

        return $this->connections[$name];
    }

    public function setRepositoryConnectionMap(string $repository_name, string $name): void
    {
        $this->repository_connection_map[$repository_name] = $name;
    }

    public function getConnectionForRepository(string $repository_name): PDO
    {
        if (isset($this->repository_connection_map[$repository_name])) {
            $name = $this->repository_connection_map[$repository_name];
            $con = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    public function get(string $repository_name): DbRepository
    {
        if (!isset($this->repositories[$repository_name])) {
            $repository_class = $repository_name . 'Repository';
            $con = $this->getConnectionForRepository($repository_name);

            $repository = new $repository_class($con);

            $this->repositories[$repository_name] = $repository;
        }

        return $this->repositories[$repository_name];
    }

    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}