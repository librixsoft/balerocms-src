<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Database;

use Exception;
use Throwable;
use Framework\Core\ErrorConsole;

class MySQL
{
    private string $host;
    private string $user;
    private string $pass;
    private string $db;

    private \mysqli $conn;

    /**
     * Puede ser mysqli_result o false o null (al inicio)
     * Cambiar el tipo para evitar error de asignación bool a ?mysqli_result
     */
    private \mysqli_result|bool|null $result = null;

    private bool|string $error = false;
    private array $rows = [];
    private ?array $row = null;

    private bool $status = false;

    public function __construct(string $host = "", string $user = "", string $pass = "", string $db = "")
    {
        try {
            $this->host = $host;
            $this->user = $user;
            $this->pass = $pass;
            $this->db = $db;

            $this->conn = new \mysqli($host, $user, $pass, $db);

            if ($this->conn->connect_errno) {
                $this->status = false;
                throw new Exception("MySQL connection failed: " . $this->conn->connect_error);
            }

            $this->status = true;
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Unexpected error in MySQL connection: " . $e->getMessage(), 0, $e));
        }
    }

    public function query(string $query, array $params = []): void
    {
        try {
            if (empty($params)) {
                // Consulta simple sin parámetros
                $res = $this->conn->query($query);
                if ($res === false) {
                    throw new Exception("SQL syntax error in query: " . $query);
                }
                $this->result = $res;
            } else {
                // Consulta preparada con parámetros
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $this->conn->error);
                }

                // Asumimos que todos los parámetros son strings para simplificar, puedes mejorarlo
                $types = str_repeat('s', count($params));

                // Bind de parámetros, usando referencias
                $stmt->bind_param($types, ...$params);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }

                $this->result = $stmt->get_result();

                $stmt->close();
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }

    public function get(): void
    {
        try {
            if (!($this->result instanceof \mysqli_result)) {
                throw new Exception("No valid result set available for fetching.");
            }

            $this->rows = [];
            while ($row = $this->result->fetch_array(MYSQLI_ASSOC)) {
                $this->rows[] = $row;
            }

            // Agrega esta línea para llenar $row
            $this->row = $this->rows[0] ?? null;

        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error while fetching query result: " . $e->getMessage(), 0, $e));
        }
    }


    public function num_rows(): int
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->result->num_rows;
        }
        return 0;
    }

    public function create(string $query): void
    {
        try {
            $success = $this->conn->multi_query($query);

            if (!$success) {
                throw new Exception("Failed to create table(s). Query: " . $query);
            }

            // Si usas multi_query, deberías limpiar resultados múltiples
            do {
                if ($result = $this->conn->store_result()) {
                    $result->free();
                }
            } while ($this->conn->more_results() && $this->conn->next_result());

        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            ErrorConsole::handleException($e);
        }
    }

    public function queryArray(): array
    {
        return $this->rows;
    }

    public function mySQLError(): string|bool
    {
        return $this->error;
    }

    // Getters and setters...

    public function getHost(): string { return $this->host; }
    public function setHost(string $host): void { $this->host = $host; }

    public function getUser(): string { return $this->user; }
    public function setUser(string $user): void { $this->user = $user; }

    public function getPass(): string { return $this->pass; }
    public function setPass(string $pass): void { $this->pass = $pass; }

    public function getDb(): string { return $this->db; }
    public function setDb(string $db): void { $this->db = $db; }

    public function getConn(): \mysqli { return $this->conn; }
    public function setConn(\mysqli $conn): void { $this->conn = $conn; }

    public function getResult(): \mysqli_result|bool|null { return $this->result; }
    public function setResult(\mysqli_result|bool|null $result): void { $this->result = $result; }

    public function isError(): bool
    {
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
