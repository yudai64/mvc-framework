<?php

abstract class DbRepository
{
    protected PDO $con;

    public function __construct(PDO $con)
    {
        $this->setConnection($con);
    }

    public function setConnection(PDO $con): void
    {
        $this->con = $con;
    }

    public function execute(string $sql, array $params = array())
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetch(string $sql, array $params = array()): mixed
    {
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll(string $sql, array $params = array()): mixed
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}