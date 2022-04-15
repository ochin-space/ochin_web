<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
class SQLiteConstructor_main{
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

    //TABLE  users
    public function createTableUsers() {
        $command = 'CREATE TABLE IF NOT EXISTS users (
            id   INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            avatar TEXT,
            password TEXT NOT NULL)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }

    public function insertRowUsers($name, $email, $password) {
        $command = ("INSERT INTO users (name, email, password) VALUES( '$name', '$email', '$password');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function updateUsers_Row($tableName, $id, $name, $email, $password) {
        $command = ("UPDATE '$tableName' 
                    SET en = '$en', name = '$name',  email = '$email',  avatar = '$avatar',  password = '$password'
                    WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }
    
    function checkUsers_Credentials($email, $name, $password)
    {
        $hashedPsw = MD5($password);
        $query = 'SELECT id,name,avatar,email,password FROM users WHERE (email = :email AND password = :password) OR (name = :name AND password = :password)';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array(':email' => $email , ':name' => $name , ':password' => $hashedPsw));
        $result = $stmt->fetch();
        return $result;
    }

    public function checkUsers_UserExists($name) {
        $stmt =$this->pdo->prepare('SELECT name FROM users WHERE name = :name');
		$stmt->execute(array(':name' => $name));
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function checkUsers_EmailExists($email) {
        $stmt =$this->pdo->prepare('SELECT email FROM users WHERE email = :email');
		$stmt->execute(array(':email' => $email));
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function getUsers_RowsUsers() {
        $stmt = $this->pdo->query('SELECT id, name, email, password ' 
                                    . 'FROM users');
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'avatar' => $row['avatar'],
                'password' => $row['password']
            ];
        }
        return $features;
    }
    
    public function updateUsers_name($id, $name) {
        $command = ("UPDATE users SET name = '$name' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }
    
    public function updateUsers_email($id, $email) {
        $command = ("UPDATE users SET email = '$email' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }
    
    public function updateUsers_password($id, $password) {
        $command = ("UPDATE users SET password = '$password' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function updateUsers_avatar($id, $avatar) {
        $command = ("UPDATE users SET avatar = '$avatar' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }	
	
    //TABLE  topbar
    public function createTableAddons() {
        $command = 'CREATE TABLE IF NOT EXISTS addons (
            id   INTEGER PRIMARY KEY,
            en TEXT NOT NULL,
            tab TEXT NOT NULL,
            name TEXT NOT NULL,
            foldername TEXT NOT NULL,
            description TEXT NOT NULL)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function nameCheck($name) {
		$command = ("SELECT EXISTS (SELECT 1 FROM addons WHERE name = '$name')");
		$stmt = $this->pdo->query($command)->fetch();
		return $stmt[0];
    }
	
    public function insertRowAddons($tab, $en, $name, $foldername, $description) {
		if(!$this->nameCheck($name))
		{
			$command = ("INSERT INTO addons (tab, en, name, foldername, description) VALUES( '$tab', '$en', '$name', '$foldername', '$description');");
			$this->pdo->exec($command);
			return $this->pdo->lastInsertId();
		}
    }

    public function deleteRowAddons($id) {
        $command = ("DELETE FROM addons WHERE id = '$id'");
        $this->pdo->exec($command);
		return $this->pdo->lastInsertId();
    }

    public function updateRowAddons($id, $tab, $en, $name, $foldername, $description) {
        $command = ("UPDATE addons 
                    SET en = '$en', tab = '$tab', name = '$name', foldername = '$foldername', description = '$description' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }
    
    public function getRowsAddons_InTabs($tabs, $condition, $logic) {
        $query="SELECT id, tab, en, name, foldername, description FROM addons WHERE ";
		for($i=0;$i<sizeof($tabs);$i++)
		{
			if($i>0) $query .= $logic." ";
			$query .= "tab ".$condition." '".$tabs[$i]."' ";			
		}
		$stmt = $this->pdo->query($query);
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'en' => $row['en'],
                'tab' => $row['tab'],
                'name' => $row['name'],
                'foldername' => $row['foldername'],
                'description' => $row['description']
            ];
        }
        return $features;
    }
}
?>