<?php

require_once 'Database.php';

class Task {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM tasks ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)");
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'new'
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $values = ['id' => $id];

        if (isset($data['title'])) {
            $fields[] = "title = :title";
            $values['title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $values['description'] = $data['description'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $values['status'] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}