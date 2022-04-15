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

    //TABLE  modules
    public function createTable_modules() {
        $command = 'CREATE TABLE IF NOT EXISTS modules (
            id   INTEGER PRIMARY KEY,
            en INTEGER NOT NULL,
            name TEXT NOT NULL,
            cmd_line TEXT NOT NULL,
            options TEXT NOT NULL,
            description TEXT NOT NULL)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function nameCheck($name) {
		$index=0;
		$newname=$name;
		$command = ("SELECT EXISTS (SELECT 1 FROM modules WHERE name = '$newname')");
		$stmt = $this->pdo->query($command)->fetch();
		while($stmt[0] == 1)
		{
			$index = $index+1;
			$newname = $name."_".$index;
			$command = ("SELECT EXISTS (SELECT 1 FROM modules WHERE name = '$newname')");
			$stmt = $this->pdo->query($command)->fetch();
		}		
		return $newname;
    }

    public function insertRow_modules($en, $name, $description, $cmd_line, $options) {
        $command = ("INSERT INTO modules (en,name,description,cmd_line,options) VALUES( '$en', '$name', '$description', '$cmd_line', '$options');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function deleteRow_modules($id) {
        $command = ("DELETE FROM modules WHERE id = '$id'");
        $this->pdo->exec($command);
        return $id;
    }

    public function updateRow_modules($id, $en, $name, $description, $cmd_line, $options) {
        $command = ("UPDATE modules 
                    SET en = '$en', name = '$name', description = '$description',  cmd_line = '$cmd_line', options = '$options'
                    WHERE id = '$id'");
        $this->pdo->exec($command);
        return $id;
    }

    public function getRows_modules() {
        $stmt = $this->pdo->query('SELECT id, en, name, description, cmd_line, options FROM modules');
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'en' => $row['en'],
                'name' => $row['name'],
                'description' => $row['description'],
                'cmd_line' => $row['cmd_line'],
                'options' => $row['options']
            ];
        }
        return $features;
    }
}
?>