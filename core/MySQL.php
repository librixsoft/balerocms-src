<?php

class errorConnection extends Exception {}

class MySQL {

    private string $host;
    private string $user;
    private string $pass;
    private string $db;

    private mysqli $conn;
    private ?mysqli_result $result = null;

    private bool|String $error = false;  // Aquí error parece string o bool, mejor usar string|null
    private array $rows = [];
    private ?array $row = null;

    private bool $status = false;

    public function __construct(string $host = "", string $user = "", string $pass = "", string $db = "") {
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

    public function query(string $query): void {
        try {
            $this->result = $this->conn->query($query);

            if (!$this->result) {
                throw new Exception("SQL syntax error in query: " . $query);
            }

        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }

    public function get(): void {
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

    public function create(string $query): void {
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

    public function queryArray(): array {
        return $this->rows;
    }

    public function mySQLError(): string|bool {
        return $this->error;
    }

    // Getters and setters

    public function getHost(): string { return $this->host; }
    public function setHost(string $host): void { $this->host = $host; }

    public function getUser(): string { return $this->user; }
    public function setUser(string $user): void { $this->user = $user; }

    public function getPass(): string { return $this->pass; }
    public function setPass(string $pass): void { $this->pass = $pass; }

    public function getDb(): string { return $this->db; }
    public function setDb(string $db): void { $this->db = $db; }

    public function getConn(): mysqli { return $this->conn; }
    public function setConn(mysqli $conn): void { $this->conn = $conn; }

    public function getResult(): ?mysqli_result { return $this->result; }
    public function setResult(?mysqli_result $result): void { $this->result = $result; }

    public function isError(): bool {
        return (bool) $this->error;
    }
    public function setError(bool|string $error): void { $this->error = $error; }

    public function getRows(): array { return $this->rows; }
    public function setRows(array $rows): void { $this->rows = $rows; }

    public function getRow(): ?array { return $this->row; }
    public function setRow(?array $row): void { $this->row = $row; }

    public function isStatus(): bool { return $this->status; }
    public function setStatus(bool $status): void { $this->status = $status; }
}
