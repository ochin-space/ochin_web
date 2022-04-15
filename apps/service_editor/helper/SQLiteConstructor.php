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

    //TABLE  services
    public function createTable_services() {
        $command = 'CREATE TABLE IF NOT EXISTS services (
            id   INTEGER PRIMARY KEY,
            en INTEGER NOT NULL,
            name TEXT NOT NULL,
            cmd_line TEXT NOT NULL,
            unitOptions TEXT,
            serviceOptions TEXT,
            installOptions TEXT,
            description TEXT)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function nameCheck($name) {
		$index=0;
		$newname=$name;
		$command = ("SELECT EXISTS (SELECT 1 FROM services WHERE name = '$newname')");
		$stmt = $this->pdo->query($command)->fetch();
		while($stmt[0] == 1)
		{
			$index = $index+1;
			$newname = $name."_".$index;
			$command = ("SELECT EXISTS (SELECT 1 FROM services WHERE name = '$newname')");
			$stmt = $this->pdo->query($command)->fetch();
		}		
		return $newname;
    }

    public function insertRow_services($en, $name, $cmd_line, $unitOptions, $serviceOptions, $installOptions, $description) {
        $command = ("INSERT INTO services (en, name, cmd_line, unitOptions, serviceOptions, installOptions, description)
		VALUES( '$en', '$name', '$cmd_line', '$unitOptions', '$serviceOptions', '$installOptions', '$description');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function deleteRow_services($id) {
        $command = ("DELETE FROM services WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function updateRow_services($id, $en, $name, $cmd_line, $unitOptions, $serviceOptions, $installOptions, $description) {
        $command = ("UPDATE services 
                    SET en = '$en', name = '$name', cmd_line = '$cmd_line', unitOptions = '$unitOptions', serviceOptions = '$serviceOptions', installOptions = '$installOptions', description = '$description'
                    WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function getRows_services() {
        $stmt = $this->pdo->query('SELECT id, en, name, cmd_line, unitOptions, serviceOptions, installOptions, description FROM services');
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'en' => $row['en'],
                'name' => $row['name'],
                'cmd_line' => $row['cmd_line'],
                'unitOptions' => $row['unitOptions'],
                'serviceOptions' => $row['serviceOptions'],
                'installOptions' => $row['installOptions'],
                'description' => $row['description']
            ];
        }
        return $features;
    }
}
?>