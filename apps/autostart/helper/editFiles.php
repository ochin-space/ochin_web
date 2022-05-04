<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 	
function isClientLocal()
{
	$clientNet =  pathinfo($_SERVER['REMOTE_ADDR'],PATHINFO_FILENAME);
	$serverNet = pathinfo($_SERVER['SERVER_ADDR'],PATHINFO_FILENAME);
	$result=0;
	if(strcmp($serverNet,$clientNet)==0) $result=1; else $result=0;
	return $result;
}

function replace_a_line($filename, $en, $cmd_line, $new_cmd_line) 
{
	if(isClientLocal())
	{
		$filename_encoded = str_replace("/", "@", $filename);
		if($cmd_line==null) $addline = true;
		else $addline=false;
		
		if(file_exists(Config::backgroundWorker_path."files2update/".$filename_encoded))
		{
			$reading = fopen(Config::backgroundWorker_path."files2update/".$filename_encoded, 'r');
		}
		else
		{
			$reading = fopen($filename, 'r');
		}
		$writing = fopen('tmp/'.$filename_encoded, 'w');
		$replaced = false;
		$cmd_line = $cmd_line." & #added by the ochin web GUI";
		$new_cmd_line = $new_cmd_line." & #added by the ochin web GUI";	
		$cmd_line_duplicated = false;
		//check if the cmd_line is already present in the file
		while (!feof($reading)) 
		{
			$line = fgets($reading);        
			$line_Clean = $line;
			if(substr($line_Clean, 0, 1) == "#") $line_Clean = substr($line_Clean, 1);  //remove "#" at the beginning
			if(stristr($line_Clean,"\n", true)) $line_Clean = stristr($line_Clean,"\n", true);  //remove "\n" at the end
			
			if(strcmp($line_Clean,$new_cmd_line)==0 and strcmp($cmd_line,$new_cmd_line)!=0 ) $cmd_line_duplicated = true;
		}
		rewind($reading);
		while (!feof($reading)) 
		{
			$line = fgets($reading);        
			$line_Clean = $line;
			if(substr($line_Clean, 0, 1) == "#") $line_Clean = substr($line_Clean, 1);  //remove "#" at the beginning
			if(stristr($line_Clean,"\n", true)) $line_Clean = stristr($line_Clean,"\n", true);  //remove "\n" at the end
			if(strcmp("exit 0",$line_Clean)==0) $line = "";					
			if(strcmp($line_Clean,$cmd_line)==0 and $cmd_line_duplicated==false)  //find cmd_line
			{
				if($en)	
				{
					$line = $new_cmd_line."\n";
				} 
				else 
				{
					$line = "#".$new_cmd_line."\n";   //comment line
				}
				$replaced = true;
			}
			fputs($writing, $line);			
		}
		fclose($reading);

		if ($replaced == false and $cmd_line_duplicated==false)	#brand new line
		{        
			if($en)
			{
				$line = $new_cmd_line."\n";     //new line
			} else {
				$line = "#".$new_cmd_line."\n";   //new line commented
			}
			fputs($writing, $line);
			$line = "exit 0";
			fputs($writing, $line);
			fclose($writing);
			rename('tmp/'.$filename_encoded,Config::backgroundWorker_path."files2update/".$filename_encoded);
			
			return 1;
		}
		else if($replaced == true and $cmd_line_duplicated==false)	#edited line
		{
			fputs($writing, $line);
			$line = "exit 0";
			fputs($writing, $line);
			fclose($writing);
			rename('tmp/'.$filename_encoded,Config::backgroundWorker_path."files2update/".$filename_encoded);
			
			return 1;
		}
		else if($cmd_line_duplicated==true)	#new line duplicated
		{
			$line = "exit 0";
			fputs($writing, $line);
			fclose($writing);
			rename('tmp/'.$filename_encoded,Config::backgroundWorker_path."files2update/".$filename_encoded);
			
			return "Is not possible to add the command line. The command is already present in the file!";
		}
	}
	else
	{
		return "The client is not connected locally. The operation is denied!";
	}
}

function delete_a_line($filename, $cmd_line) 
{
	if(isClientLocal())
	{
		$filename_encoded = str_replace("/", "@", $filename);
		if(file_exists(Config::backgroundWorker_path."files2update/".$filename_encoded))
		{
			$reading = fopen(Config::backgroundWorker_path."files2update/".$filename_encoded, 'r');
		}
		else
		{
			$reading = fopen($filename, 'r');
		}
		$writing = fopen('tmp/'.$filename_encoded, 'w');
		$deleted = false;      

		$cmd_line = $cmd_line." & #added by the ochin web GUI";
		if(substr($cmd_line, 0, 1) == "#") $cmd_line = substr($cmd_line, 1);  //remove "#" at the beginning

		while (!feof($reading)) 
		{
			$line = fgets($reading);        
			$line_Clean = $line;
			if(substr($line_Clean, 0, 1) == "#") $line_Clean = substr($line_Clean, 1);  //remove "#" at the beginning
			if(stristr($line_Clean,"\n", true)) $line_Clean = stristr($line_Clean,"\n", true);  //remove "\r\n" at the end
			
			if(substr_compare($line_Clean,$cmd_line,0)==0 || $line=="\n")  //find cmd_line
			{
				$deleted = true;
			}
			else fputs($writing, $line);
		}
		fclose($reading);
		fclose($writing);
		rename('tmp/'.$filename_encoded,Config::backgroundWorker_path."files2update/".$filename_encoded);
		return 1;
	}
	else
	{
		return 0;
	}
}
?>