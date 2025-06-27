<?php

class virtual_page_Model extends Model {

    protected MySQL $db;
    public array $rows = [];
    public string $lang = "main";

    public function __construct() {
        try {
            parent::dbConnect();
            $this->LoadSettings();
        } catch (Exception $e) {
        }
    }

    public function theme(): string {
        $this->db->query("SELECT * FROM custom_settings WHERE id = 1");
        $this->db->get();

        foreach ($this->db->getRows() as $row) {
            return $row['theme'];
        }

        return "";
    }

    public function get_virtual_page_by_id(int $id): array {
        $table = ($this->lang === "main" || empty($this->lang)) ? "virtual_page" : "virtual_page_multilang";
        $langCondition = ($table === "virtual_page_multilang") ? " AND code = '{$this->lang}'" : "";

        $this->db->query("SELECT * FROM {$table} WHERE id = '{$id}'{$langCondition}");
        $this->db->get();
        return $this->db->getRows();
    }

    public function get_virtual_pages(): array {
        $table = ($this->lang === "main" || empty($this->lang)) ? "virtual_page" : "virtual_page_multilang";
        $condition = ($table === "virtual_page") ? "WHERE active = '1'" : "WHERE code = '{$this->lang}'";

        $this->db->query("SELECT * FROM {$table} {$condition}");
        $this->db->get();

        return $this->db->getRows() ?: [];
    }

    public function getLang(): string {
        return "main";
    }

    public function total_pages(): int {
        $table = ($this->lang === "main" || empty($this->lang)) ? "virtual_page" : "virtual_page_multilang";
        $condition = ($table === "virtual_page") ? "WHERE active = '1'" : "WHERE code = '{$this->lang}'";

        $this->db->query("SELECT * FROM {$table} {$condition}");
        $this->db->get();

        return $this->db->num_rows();
    }

    public function limit(): int {
        $this->db->query("SELECT * FROM custom_settings WHERE id = 1");
        $this->db->get();

        foreach ($this->db->getRows() as $row) {
            return (int) $row['pagination'];
        }

        return 10; // default fallback
    }
}
