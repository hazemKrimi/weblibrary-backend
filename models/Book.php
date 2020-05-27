<?php
    class Book {
        private $conn;
        private $table = "books";
        public $id;
        public $author;
        public $name;
        public $path;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function list() {
            $stmt = $this->conn->prepare("SELECT books.id, author, name, path, username FROM ".$this->table.", users WHERE books.author = users.id ORDER BY books.id DESC");
            $stmt->execute();
            return $stmt;
        }

        public function create() {
            $stmt = $this->conn->prepare("INSERT INTO ".$this->table."(author, name, path) VALUES (?, ?, ?)");
            if ($stmt->execute([$this->author, $this->name, $this->path])) return true;
            else return false;
        }

        public function delete() {
            $stmt = $this->conn->prepare("DELETE FROM ".$this->table." WHERE id = ?");
            if ($stmt->execute([$this->id])) return true;
            else return false;
        }
    }
?>