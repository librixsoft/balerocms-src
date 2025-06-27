<?php

class errorConnection extends Exception {}

class MySQL {

    private $host;
    private $user;
    private $pass;
    private $db;

    private mysqli $conn;
    private $result;

    private bool $error = false;
    private $rows;
    private $row;

    private bool $status = false;

    public function __construct($host = "", $user = "", $pass = "", $db = "") {
        try {
            $this->conn = new mysqli($host, $user, $pass, $db);
            $this->status = true;

            if (mysqli_connect_errno()) {
                $this->status = false;
                throw new errorConnection("MySQL connection failed: " . mysqli_connect_error());
            }

        } catch (errorConnection $e) {
            ErrorConsole::handleException($e);
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Unexpected error in MySQL connection: " . $e->getMessage(), 0, $e));
        }
    }

    public function query($query) {
        try {
            $this->result = $this->conn->query($query);

            if (!$this->result) {
                throw new Exception("SQL syntax error in query: " . $query);
            }

        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }

    public function get() {
        try {
            if (!$this->result) {
                throw new Exception("No result set available for fetching.");
            }

            $this->rows = [];
            while ($row = $this->result->fetch_array(MYSQLI_ASSOC)) {
                $this->rows[] = $row;
            }

        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error while fetching query result: " . $e->getMessage(), 0, $e));
        }
    }

    public function num_rows(): int {
        return $this->result ? $this->result->num_rows : 0;
    }

    public function create($query) {
        try {
            $success = mysqli_multi_query($this->conn, $query);

            if (!$success) {
                throw new Exception("Failed to create table(s). Query: " . $query);
            }

        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            ErrorConsole::handleException($e);
        }
    }

    public function queryArray() {
        return $this->rows;
    }

    public function mySQLError() {
        return $this->error;
    }

    // Getters and setters

    public function getHost() { return $this->host; }
    public function setHost($host): void { $this->host = $host; }

    public function getUser() { return $this->user; }
    public function setUser($user): void { $this->user = $user; }

    public function getPass() { return $this->pass; }
    public function setPass($pass): void { $this->pass = $pass; }

    public function getDb() { return $this->db; }
    public function setDb($db): void { $this->db = $db; }

    public function getConn(): mysqli { return $this->conn; }
    public function setConn(mysqli $conn): void { $this->conn = $conn; }

    public function getResult() { return $this->result; }
    public function setResult($result): void { $this->result = $result; }

    public function isError(): bool { return $this->error; }
    public function setError(bool $error): void { $this->error = $error; }

    public function getRows() { return $this->rows; }
    public function setRows($rows): void { $this->rows = $rows; }

    public function getRow() { return $this->row; }
    public function setRow($row): void { $this->row = $row; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): void { $this->status = $status; }
}
