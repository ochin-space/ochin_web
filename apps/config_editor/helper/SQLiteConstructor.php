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
    public function createTable_config_txt() {
        $command = 'CREATE TABLE IF NOT EXISTS hwConfig (
            id   INTEGER PRIMARY KEY,
            en INTEGER NOT NULL,
            name TEXT NOT NULL,
            cmd_line TEXT NOT NULL,
            description TEXT NOT NULL)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function cmdlineCheck($cmdline) {
		$command = ("SELECT EXISTS (SELECT 1 FROM hwConfig WHERE cmd_line = '$cmdline')");
		$stmt = $this->pdo->query($command)->fetch();
		return $stmt[0];
    }

    public function insertRow_config_txt($en, $name, $cmd_line, $description) {
        $command = ("INSERT INTO hwConfig (en, name, cmd_line, description) VALUES( '$en', '$name', '$cmd_line', '$description');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function deleteRow_config_txt($id) {
        $command = ("DELETE FROM hwConfig WHERE id = '$id'");
        $this->pdo->exec($command);
		return $this->pdo->lastInsertId();
    }

    public function updateRow_config_txt($id, $en, $name, $cmd_line, $description) {
        $command = ("UPDATE hwConfig 
                    SET en = '$en',  name = '$name',  cmd_line = '$cmd_line', description = '$description'
                    WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function getRows_config_txt() {
        $stmt = $this->pdo->query('SELECT id, en, name, cmd_line, description FROM hwConfig');
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