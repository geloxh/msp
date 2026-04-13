<?php

namespace core;

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryhKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    public function all(array $conditions = [], string $orderBy = '', int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key -> $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int {
        $fields  = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql->db->query($sql, array_values($data));
        return (int) $this->db->lastIndertId();
    }

    public function update
}