<?php

class DB {
    private $connection = null;

    public function connect() {
        try {
            $this->connection = new SQLite3(__DIR__ . '/../database/database.sqlite');
            $this->connection->exec('PRAGMA foreign_keys = ON');
            return $this->connection;
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

}