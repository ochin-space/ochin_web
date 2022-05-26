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

    //TABLE  networks
    public function createTable_networks() {
        $command = 'CREATE TABLE IF NOT EXISTS networkConfig (
            id   INTEGER PRIMARY KEY,
            en INTEGER NOT NULL,
            running INTEGER NOT NULL,
            name TEXT NOT NULL,
            static INTEGER NOT NULL,
            APmode INTEGER NOT NULL,
            cCode TEXT NOT NULL,
            ssid TEXT NOT NULL,
            password TEXT NOT NULL,
            ipaddress TEXT,
            netmask TEXT,
            dhcpIpStart TEXT,
            dhcpIpStop TEXT)';
        $stmt = $this->pdo->prepare($command);
        $stmt->execute();
    }
	
    public function ssidCheck($cmdline) {
		$command = ("SELECT EXISTS (SELECT 1 FROM networkConfig WHERE ssid = '$ssid')");
		$stmt = $this->pdo->query($command)->fetch();
		return $stmt[0];
    }
	
	public function EableConfigId($id, $en) 
	{
		$command = ("UPDATE networkConfig SET en = '$en' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
	}
	
	public function RunningConfigId($id, $running) 
	{
		$command = ("UPDATE networkConfig SET running = '$running' WHERE id = '$id'");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
	}
	
	#check if there is another enabled config for the same adapter and eventually disable it
	public function AdapterEnCheck($en, $name, $id, $APmode) 
	{
		if($en == 'true')
		{
			if($APmode == 'true')	//config is APmode (disable all the other AP configs for this adapter)
			{				
				$stmt = $this->pdo->query("SELECT * FROM networkConfig WHERE id != '$id' AND APmode = 'false'");
				while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) 
				{
					$this->RunningConfigId($row['id'], 'false');
					$response[] = [
						'id' => $row['id'],
						'running' => $row['running']
					];
				}
			}
			else	//config is STAmode (disable all the APmode config for this adapter)
			{			
				$stmt = $this->pdo->query("SELECT * FROM networkConfig WHERE id != '$id' AND APmode = 'false'");
				while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) 
				{
					$this->RunningConfigId($row['id'], 'true');
					$response[] = [
						'id' => $row['id'],
						'running' => $row['running']
					];
				}
			}
			
			//disable all the other AP configs for this adapter
			$stmt = $this->pdo->query("SELECT * FROM networkConfig WHERE name = '$name' AND id != '$id' AND APmode = 'true'");
			while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) 
			{
				$this->EableConfigId($row['id'], 'false');
				$response[] = [
					'id' => $row['id'],
					'en' => $row['en']
				];
				$this->RunningConfigId($row['id'], 'false');
				$response[] = [
					'id' => $row['id'],
					'running' => $row['running']
				];
			}
			return $response;
		}
		else return 0;
    }

    public function insertRow_networks($en, $running, $name, $static, $APmode, $cCode, $ssid, $password, $ipaddress, $netmask, $dhcpIpStart, $dhcpIpStop) {
        $command = ("INSERT INTO networkConfig (en, running, name, static, APmode, cCode, ssid, password, ipaddress, netmask, dhcpIpStart, dhcpIpStop)
		VALUES( '$en', '$running', '$name', '$static', '$APmode', '$cCode', '$ssid', '$password', '$ipaddress', '$netmask', '$dhcpIpStart', '$dhcpIpStop');");
        $this->pdo->exec($command);
        return $this->pdo->lastInsertId();
    }

    public function deleteRow_networks($id) {
        $command = ("DELETE FROM networkConfig WHERE id = '$id'");
        $this->pdo->exec($command);
		return $this->pdo->lastInsertId();
    }
	
	public function wpa_passphrase($ssid, $passphrase) 
	{
		$bin = hash_pbkdf2('sha1', $passphrase, $ssid, 4096, 32, true);
		return bin2hex($bin);
	}
	
    public function updateRow_networks($id, $en, $name, $static, $APmode, $cCode, $ssid, $password, $ipaddress, $netmask, $dhcpIpStart, $dhcpIpStop) {
        if($en == 'true') $running = 'true';
		else $running = 'false';
		$command = ("UPDATE networkConfig 
                    SET en = '$en', running = '$running',  name = '$name',  static = '$static', APmode = '$APmode', cCode = '$cCode', ssid = '$ssid', password = '$password',
					ipaddress = '$ipaddress', netmask = '$netmask', dhcpIpStart = '$dhcpIpStart', dhcpIpStop = '$dhcpIpStop'					
                    WHERE id = '$id'");
        $this->pdo->exec($command);
		$this->AdapterEnCheck($en, $name, $id, $APmode);	//if is APmode, disable all other config for the same adapter, if APmode disable the AP config only
        return $this->pdo->lastInsertId();
    }

    public function getRows_networks() {
        $stmt = $this->pdo->query('SELECT id, running, en, name, static, APmode, cCode, ssid, password, ipaddress, netmask, dhcpIpStart, dhcpIpStop FROM networkConfig');
        $features = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $features[] = [
                'id' => $row['id'],
                'en' => $row['en'],
                'running' => $row['running'],
                'name' => $row['name'],
                'static' => $row['static'],
                'APmode' => $row['APmode'],
                'cCode' => $row['cCode'],
                'ssid' => $row['ssid'],
                'ipaddress' => $row['ipaddress'],
                'netmask' => $row['netmask'],
                'dhcpIpStart' => $row['dhcpIpStart'],
                'dhcpIpStop' => $row['dhcpIpStop']
            ];
        }
        return $features;
    }
}
?>