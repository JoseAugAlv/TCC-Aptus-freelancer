<?php

require_once __DIR__ . '/../Config/database.php';

class Model
{
    protected $conn;
    protected $table;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Buscar todos os registros
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar por ID
    public function find($id, $column = 'id')
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Inserir genérico
    public function insert($data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($data);
    }

    // Atualizar genérico
    public function update($id, $data, $column = 'id')
    {
        $fields = "";

        foreach ($data as $key => $value) {
            $fields .= "{$key} = :{$key}, ";
        }

        $fields = rtrim($fields, ", ");

        $sql = "UPDATE {$this->table} SET {$fields} WHERE {$column} = :id";
        $stmt = $this->conn->prepare($sql);

        $data['id'] = $id;

        return $stmt->execute($data);
    }

    // Deletar
    public function delete($id, $column = 'id')
    {
        $sql = "DELETE FROM {$this->table} WHERE {$column} = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}