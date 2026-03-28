<?php

class DBController
{
    private $db;
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $dbport;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->dbport = $_ENV['DB_PORT'];
    }

    public function connect()
    {
        $this->db = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->dbport);

        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }

        return $this->db;
    }
}