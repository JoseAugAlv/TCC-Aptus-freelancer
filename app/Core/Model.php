<?php
// app/Core/Model.php

require_once __DIR__ . '/../Config/database.php';

class Model
{
    protected $conn;
    protected $table;
    protected $allowedColumns = [];

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Valida identificadores SQL (nomes de coluna/tabela) contra injeção.
     */
    protected function safeIdentifier(string $name): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("Identificador SQL inválido: {$name}");
        }
        return $name;
    }

    protected function safeTable(): string
    {
        return $this->safeIdentifier((string) $this->table);
    }

    public function all()
    {
        $sql  = "SELECT * FROM " . $this->safeTable();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id, $column = 'id')
    {
        $column = $this->safeIdentifier($column);
        $sql  = "SELECT * FROM " . $this->safeTable() . " WHERE {$column} = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Dados vazios para INSERT.');
        }

        $cols = [];
        foreach (array_keys($data) as $col) {
            $cols[] = $this->safeIdentifier($col);
        }

        $columns      = implode(', ', $cols);
        $placeholders = ':' . implode(', :', $cols);

        $sql  = "INSERT INTO " . $this->safeTable() . " ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data, $column = 'id')
    {
        if (empty($data)) {
            return false;
        }

        $column = $this->safeIdentifier($column);
        $fields = [];

        foreach (array_keys($data) as $key) {
            $fields[] = $this->safeIdentifier($key) . " = :{$key}";
        }

        $sql = "UPDATE " . $this->safeTable()
             . " SET " . implode(', ', $fields)
             . " WHERE {$column} = :id";

        $data['id'] = $id;
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id, $column = 'id')
    {
        $column = $this->safeIdentifier($column);
        $sql  = "DELETE FROM " . $this->safeTable() . " WHERE {$column} = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}