# Copyright (c) 2022 perniciousflier@gmail.com  
# This code is a part of the ochin project (https://github.com/ochin-space)
# The LICENSE file is included in the project's root. 

import time
import sys
import logging
from files import *
from modules import *
from services import *
from pwd import getpwnam
  
def main():
    while(True):
        files2update(sourcefolder+"files2update", sourcefolder+"update_whitelist.txt");
        files2append(sourcefolder+"files2append", sourcefolder+"append_whitelist.txt");
        files2remove(sourcefolder+"files2remove", sourcefolder+"remove_whitelist.txt");
        kernelModules(sourcefolder+"modules", sourcefolder+"modules_whitelist.txt");
        services(sourcefolder+"services/", sourcefolder+"services_whitelist.txt", sourcefolder+"sysServices_whitelist.txt");
        time.sleep(1);   
        
if __name__ == "__main__":    
    opts = [opt for opt in sys.argv[1:] if opt.startswith("-")]
    args = [arg for arg in sys.argv[1:] if not arg.startswith("-")]
    
    if "-h" in opts:
        raise SystemExit("Usage: {sys.argv[0]} (-source folder | -logsPath path_of_the_log_files | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...")
    elif "-source" in opts:
        sourcefolder = args[opts.index("-source")];
        print("The source folder is: \""+sourcefolder+"\"");
        checkEmptyFolders(sourcefolder);
        if "-logsPath" in opts:
            logsPath = args[opts.index("-logsPath")];
            print("The log files path is: \""+logsPath+"\"");            
            if "-logging" in opts:
                loglevel = args[opts.index("-logging")];
                if(loglevel!="DEBUG" and loglevel!="INFO" and loglevel!="WARNING" and loglevel!="ERROR" and loglevel!="CRITICAL"):
                    print("The \""+loglevel+"\" logging level is not available, please choose one level in the list\n");
                    raise SystemExit("Usage: {sys.argv[0]} (-source folder | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...");
                print("The logging level is set to: \""+loglevel+"\"");
            else:
                loglevel = "WARNING";
                print("The logging level is set to: \""+loglevel+"\" by default");
            logname =  time.strftime("%Y_%m_%d.log", time.localtime());   
            logging.basicConfig(filename=logsPath+logname, encoding='utf-8', level=logging.getLevelName(loglevel));
            logging.info('ochin_web backgroundWorker started!');
    else:
        raise SystemExit("Usage: {sys.argv[0]} (-source folder | -logsPath path_of_the_log_files | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...")
    main();
