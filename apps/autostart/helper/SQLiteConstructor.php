<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
class SQLiteConstructor{
    private $pdo;

    public function connect($dbName) {
        if ($this->pdo == null) {
            try {
                $this->pdo = new \PDO("sqlite:" . $dbName);
             } catch (\PDOException $e) {
                 echo $e;
                // handle the exception here
             }
        }
        return $this->pdo;
    }

    //TABLE  config_txt
    public function createTable_autostart() {
        $command = 'CREATE TABLE IF NOT EXISTS autostart (
            id   INTEGER PRIMARY KEY,
            en INTEGER NOT NULL,
            name TEXT NOT NULL,
            cmd_line TEXT NOT NULL,
            description TEXT NOT NULL)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function cmdlineCheck($cmdline) {
		$command = ("SELECT EXISTS (SELECT 1 FROM autostart WHERE cmd_line = '$cmdline')");
		$stmt = $this->pdo->query($command)->fetch();
		return $stmt[0];
    }

    public function insertRow_autostart($en, $name, $cmd_line, $description) {
        $command = ("INSERT INTO autostart (en, name, cmd_line, description) VALUES( '$en', '$name', '$cmd_line', '$description');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function deleteRow_autostart($id) {
        $command = ("DELETE FROM autostart WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function updateRow_autostart($id, $en, $name, $cmd_line, $description) {
        $command = ("UPDATE autostart 
                    SET en = '$en',  name = '$name',  cmd_line = '$cmd_line', description = '$description'
                    WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function getRows_autostart() {
        $stmt = $this->pdo->query('SELECT id, en, name, cmd_line, description FROM autostart');
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'en' => $row['en'],
                'name' => $row['name'],
                'cmd_line' => $row['cmd_line'],
                'description' => $row['description']
            ];
        }
        return $features;
    }
}
?>