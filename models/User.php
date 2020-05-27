<?php
    class User {
        private $conn;
        private $table = "users";
        public $id;
        public $username;
        public $email;
        public $password;
        public $type;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function create() {
            $stmt = $this->conn->prepare("INSERT INTO ".$this->table."(username, email, password, type) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$this->username, $this->email, password_hash($this->password, PASSWORD_BCRYPT), $this->type])) return true;
            else return false;
        }

        public function update() {
            $username_set = !empty($this->username) ? "username = :username" : "";
            $email_set = !empty($this->email) ? ", email = :email" : "";
            $password_set = !empty($this->password) ? ", password = :password" : "";
            $stmt = $this->conn->prepare("UPDATE ".$this->table." SET {$username_set}{$email_set}{$password_set} WHERE id = :id");
            if (!empty($this->username)) $stmt->bindParam(":username", $this->username);
            if (!empty($this->email)) $stmt->bindParam(":email", $this->email);
            if (!empty($this->password)) $stmt->bindParam(":password", password_hash($this->password, PASSWORD_BCRYPT));
            $stmt->bindParam(":id", $this->id);
            if ($stmt->execute()) return true;
            else return false;
        }

        public function delete() {
            $stmt = $this->conn->prepare("DELETE FROM ".$this->table." WHERE id = ?");
            if ($stmt->execute([$this->id])) return true;
            else return false;
        }

        public function email_exists() {
            $stmt = $this->conn->prepare("SELECT * FROM ".$this->table." WHERE email = ? LIMIT 0,1");
            $stmt->execute([$this->email]);
            $num = $stmt->rowCount();
            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->password = $row['password'];
                $this->type = $row['type'];
                return true;
            } else return false;
        }
    }
?>