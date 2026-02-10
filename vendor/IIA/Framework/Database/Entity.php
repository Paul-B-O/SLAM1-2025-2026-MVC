<?php

namespace IIA\Framework\Database;
use ReflectionClass;

class Entity
{
    protected Database $database;
    protected string $table;
    protected int $id;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $tableName = new ReflectionClass($this)->getShortName();
        $this->table = "";

        for ($i=0; $i < strlen($tableName); $i++) {
            $char = $tableName[$i];
            if ($char >= 'A' && $char <= 'Z') {
                $char = strtolower($char);
                if ($i !== 0) $char = "_".$char;
            }
            $this->table = $this->table.$char;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOne(int $id) {
        $result = $this->database->query("SELECT * FROM $this->table WHERE id = $id", static::class);
        if (count($result) >= 1) {
            return $result[0];
        }
        return NULL;
    }

    public function getAll(array $where = [], array $order = [], ?int $limit = null): array
    {
        $request = "SELECT * FROM ".$this->table;
        if (count($where) > 0) {
            $request = $request." WHERE ".$where;
            foreach ($where as $key => $value) {
                $request = $request.$key." = ".$value." AND ";
            }

            $request = substr($request, 0, -5);
        }
        if (count($order) > 0) {
            $request = $request." ORDER BY ".$order;
            foreach ($order as $key => $value) {
                $request = $request.$key." ".$value;
            }
        }
        if ($limit) $request." LIMIT ".$limit;

        return $this->database->query($request, static::class);
    }

    public function delete() {
        $this->database->query("DELETE FROM ".$this->table." WHERE id = ".$this.geId());
    }

    public function save() {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();

        $fields = [];
        $values = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();

            if (in_array($name, ['database', 'table'])) {
                continue;
            }

            $value = $property->getValue($this);

            if ($name !== 'id') {
                $fields[] = $name;
                $values[] = is_string($value) ? "'$value'" : $value;
            }
        }

        if (!empty($this->id)) {
            $sets = [];
            foreach ($fields as $index => $field) {
                $sets[] = "$field = {$values[$index]}";
            }

            $sql = "UPDATE {$this->table} SET "
                . implode(', ', $sets)
                . " WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO {$this->table} ("
                . implode(', ', $fields)
                . ") VALUES ("
                . implode(', ', $values)
                . ")";
        }

        $this->database->query($sql);
    }


    public function __get(string $key)
    {
        $method = 'get' . ucfirst($key);
        return $this->$method();
    }
}