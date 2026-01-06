<?php

namespace IIA\Framework\Database;

class Entity
{
    protected Database $database;
    protected string $table;
    protected int $id;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAll(): array
    {
        return $this->database->query("SELECT * FROM " . $this->table, static::class);
    }

    public function __get(string $key)
    {
        $method = 'get' . ucfirst($key);
        return $this->$method();
    }
}