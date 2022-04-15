<?php 
/* 
* Copyright (c) 2022 perniciousflier@gmail.com  
* This code is a part of the ochin project (https://github.com/ochin-space)
* The LICENSE file is included in the project's root. 
*/ 
function replace_a_line($filename, $en, $cmd_line, $new_cmd_line) 
{
    $reading = fopen($filename, 'r');
    $writing = fopen('./tmp/file.tmp', 'wx');
    $replaced = false;
    $cmd_line = $cmd_line." & #added by the ochin web GUI";
    $new_cmd_line = $new_cmd_line." & #added by the ochin web GUI";	
	
    while (!feof($reading)) 
    {
        $line = fgets($reading);        
        $line_Clean = $line;
        if(substr($line_Clean, 0, 1) == "#") $line_Clean = substr($line_Clean, 1);  //remove "#" at the beginning
        if(stristr($line_Clean,"\n", true)) $line_Clean = stristr($line_Clean,"\n", true);  //remove "\n" at the end
		if(strcmp("exit 0",$line_Clean)==0) $line = "";
		
		if(strcmp($line_Clean,$cmd_line)==0)  //find cmd_line
		{
			if($en)
			{
				$line = $new_cmd_line."\n";
			} else {
				$line = "#".$new_cmd_line."\n";   //comment line
			}
			$replaced = true;
		}
        fputs($writing, $line);
		
    }
    fclose($reading);

    if ($replaced == false) 
    {        
        if($en)
        {
            $line = $new_cmd_line."\n";     //new line
        } else {
            $line = "#".$new_cmd_line."\n";   //new line commented
        }
        fputs($writing, $line);
    }
	$line = "exit 0";
    fputs($writing, $line);
    fclose($writing);
    //rename('./tmp/file.tmp', '/home/pi/autoexec.sh');//$filename);
	shell_exec("sudo mv tmp/file.tmp ".$filename);// ."> debug.log 2>&1");
    chmod($filename, 0755);	//make it executable
	
}

function delete_a_line($filename, $cmd_line) 
{
    $reading = fopen($filename, 'r');
    $writing = fopen('./tmp/file.tmp', 'w');
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

    if ($deleted == true) 
    {        
        //rename('./tmp/file.tmp', $filename);
		shell_exec("sudo mv tmp/file.tmp ".$filename);// ."> debug.log 2>&1");
    }
    else
    {        
        unlink('/tmp/file.tmp');
    }

}

function readSelectFields($filename)
{
	return file_get_contents($filename, 'r');
}
?>