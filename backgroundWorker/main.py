# Copyright (c) 2022 perniciousflier@gmail.com  
# This code is a part of the ochin project (https://github.com/ochin-space)
# The LICENSE file is included in the project's root. 

import shutil
import os
import time
import sys
import subprocess
import time
import logging
from pwd import getpwnam
from xml.etree import ElementTree
    
def moveFile(source, dest):
    try:
        shutil.move(source, dest);
        logging.debug("File "+source+" updated");
    except:
        logging.error("Error while moving file ", source);
        
def removeFile(source):
    try:
        os.remove(source)
        logging.debug("File "+source+" removed.");
    except:
        logging.error("Error while deleting file ", source);

def whitelistManager(action, name, whitelistFile):
    with open(whitelistFile, "r") as file:
        whitelistObjs = file.read().split('\n');
        
    if action is True:
        logging.debug("Adding \""+name+"\" to the whitelist!");
        if name not in whitelistObjs: 
            with open(whitelistFile, "a") as file:
                file.write(name+"\n");
    else:
        logging.debug("Removing \""+name+"\" from the whitelist!");
        if name in whitelistObjs:         
            lines = [];
            with open(whitelistFile, 'r') as file:
                lines = file.readlines();
            with open(whitelistFile, "w") as file:
                for line in lines:
                    if line != name+"\n":
                        file.write(line)
    
def createModule(name, cmd_line, options):
    logging.debug("The \""+name+"\" module will be updated!");    
    with open("/etc/modules-load.d/"+name+".conf", 'w') as file:
        file.write(cmd_line);
        file.close();
    
    with open("/etc/modprobe.d/"+name+".conf", 'w') as file:
        script = "options "+cmd_line+" name="+cmd_line+" "+options;
        file.write(script);
        file.close();
          
def kernelModules(source, whitelistFile):
    with open(whitelistFile, "r") as file:
        whitelistModules = file.read().split('\n');
    modules2manage = os.listdir(source);
    for i in range(len(modules2manage)):
        logging.info("\n"+time.ctime(time.time()));
        logging.info("Kernel Module \""+modules2manage[i]+"\" managing process:");
        items = ElementTree.parse(source+modules2manage[i]).getroot(); 
        for j in range(len(items)):     
            name = items[j].attrib['name']; #module name
            if name is None:
                name = " ";                
            action = items[j][0].text;  #module action
            if action is None:
                action = " ";                
            old_name = items[j][1].text;  #module old name
            if old_name is None:
                old_name = " ";                
            cmd_line = items[j][2].text;  #module cmd_line 
            if cmd_line is None:
                cmd_line = " ";                
            options = items[j][3].text;  #module options 
            if options is None:
                options = " ";    
                
            #create load a kernel module
            if(action=="load"):
                logging.info("Load module");
                #add the module to the whitelist if not already present
                if name in whitelistModules:
                    logging.debug("The module \""+name+"\" is in the whitelist.");
                else:
                    if old_name in whitelistModules:
                        logging.debug("The module name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the service file has been deleted.");
                        whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist         
                        if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module
                            logging.debug("Module \""+name+"\" unloaded!");
                        else:
                            logging.error("Error unloading \""+name+"\" module!");
                        removeFile("/etc/modules-load.d/"+old_name+".conf"); #remove the old named modules-load.d file   
                        removeFile("/etc/modprobe.d/"+old_name+".conf"); #remove the old named modprobe.d file
                        whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                    else:
                        logging.debug("The module \""+name+"\" was not in the whitelist.\nThe module has been added to the whitelist.");
                        whitelistManager(True, name, whitelistFile);
                
                #create the files needed to start the kernel module at boot
                createModule(name, cmd_line, options); 
                if(os.system('sudo modprobe '+cmd_line+" "+options)==0): #load the module
                    logging.info("Module loaded!");
                else:
                    logging.error("Error loading module! ");
                    
            #unload a kernel module
            elif(action=="unload"):
                logging.info("Unload module");
                if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module
                    logging.info("Module unloaded!");
                else:
                    logging.error("Error unloading module!");
                    
            #unload and remove the kernel module files from the system
            elif(action=="remove"): 
                #remove the module from the whitelist if it's present
                if name in whitelistModules:
                    logging.info("The module \""+name+"\" is in the whitelist.");
                    whitelistManager(False, name, whitelistFile);
                else:
                    logging.debug("The module \""+name+"\" is not in the whitelist.");
                    
                logging.debug("Remove the module files from the system folders");
                if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module              
                    removeFile("/etc/modules-load.d/"+name+".conf");              
                    removeFile("/etc/modprobe.d/"+name+".conf");
                    logging.info("Module removed!");
                else:
                    logging.error("Error unloading module!");
            else:
                logging.warning("Action type not supported!");                
        removeFile(source+modules2manage[i]);
    
def createService(name, cmd_line, unitOptions, serviceOptions, installOptions):
    logging.debug("The \""+name+"\" service will be updated!");    
    with open("/lib/systemd/system/"+name+".service", 'w') as file:
        script = "[Unit]\nDescription="+name+"\n"+unitOptions+"\n\n[Service]\nExecStart="+cmd_line+"\n"+serviceOptions+"\n\n[Install]\n"+installOptions;
        file.write(script);
        file.close();

def checkLoadedService(name):
    return os.system("systemctl is-active --quiet "+name+" && echo 1 || echo 0");

def services(source, whitelistFile, whitelistSysFile):    
    with open(whitelistFile, "r") as file:
        whitelistServices = file.read().split('\n');
    with open(whitelistSysFile, "r") as file:
        whitelistSysServices = file.read().split('\n');
    services2manage = os.listdir(source);
    for i in range(len(services2manage)):
        logging.info("\n"+time.ctime(time.time()));
        logging.info("Systemctl Services \""+services2manage[i]+"\" managing process:");
        items = ElementTree.parse(source+services2manage[i]).getroot(); 
        for j in range(len(items)):     
            name = items[j].attrib['name']; #module name
            if name is None:
                name = "dummyname";                
            old_name = items[j][0].text;  #module old name
            if old_name is None:
                old_name = " ";                            
            action = items[j][1].text;  #module action
            if action is None:
                action = " ";                
            cmd_line = items[j][2].text;  #module cmd_line  
            if cmd_line is None:
                cmd_line = " ";                
            unitOptions = items[j][3].text;  #unit module options
            if unitOptions is None:
                unitOptions = " ";                
            serviceOptions = items[j][4].text;  #service module options
            if serviceOptions is None:
                serviceOptions = " ";                
            installOptions = items[j][5].text;  #install module options     
            if installOptions is None:
                installOptions = " ";                
                        
            #load a system service
            if(action=="sysLoad"):
                logging.info("Load a system service");
                #check if the service is present in the system service the whitelist 
                if name in whitelistSysServices:
                    logging.debug("The service \""+name+"\" is in the system service whitelist.");
                    if(os.system("sudo systemctl start "+name+".service")==0): #load the service
                        logging.debug("Service loaded!");
                    else:
                        logging.error("Error loading service! ");
                else: 
                    logging.warning("The service \""+name+"\" was not in the system service whitelist.\nThe service cannot be loaded.");
            #load a service
            elif(action=="load"):
                logging.info("Load service");
                #add the service to the whitelist if not already present
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the whitelist.");
                else:
                    if old_name in whitelistServices:
                        logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the service file has been deleted.");
                        whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                        if(os.system("sudo systemctl stop "+name+".service")==0): #unload the service
                            logging.debug("Service \""+name+"\" unloaded!");
                        else:
                            logging.error("Error unloading \""+name+"\" service!");   
                        removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file
                        whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                    else:
                        logging.debug("The service \""+name+"\" was not in the whitelist.\nThe service has been added to the whitelist.");
                        whitelistManager(True, name, whitelistFile);
                
                    #create the files needed to start the service at boot
                createService(name, cmd_line, unitOptions, serviceOptions, installOptions); 
                if(os.system("sudo systemctl start "+name+".service")==0): #load the service
                    logging.info("Service loaded!");
                else:
                    logging.error("Error loading service! ");
                    
            #enable service at boot
            elif(action=="enable"):
                logging.info("Enable service");
                #add the service to the whitelist if not already present
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the whitelist.");
                else:
                    if old_name in whitelistServices:
                        logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the service file has been deleted.");
                        whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                        if(os.system("sudo systemctl stop "+name+".service")==0): #unload the service
                            logging.debug("Service \""+name+"\" unloaded!");
                        else:
                            logging.error("Error unloading \""+name+"\" service!");   
                        removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file
                        whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                    else:
                        logging.debug("The service \""+name+"\" was not in the whitelist.\nThe service has been added to the whitelist.");
                        whitelistManager(True, name, whitelistFile);
                
                    #create the files needed to start the service at boot
                createService(name, cmd_line, unitOptions, serviceOptions, installOptions); 
                if(os.system("sudo systemctl --no-block start "+name+".service")==0): #load the service
                    logging.info("Service loaded!");
                else:
                    logging.error("Error loading service! ");
                if(os.system("sudo systemctl enable "+name+".service")==0): #load the service
                    logging.info("Service enabled at boot!");
                else:
                    logging.error("Error enabling service! ");
                    
            #unload a service
            elif(action=="unload"):
                logging.info("Unload service");
                if name in whitelistServices or name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the whitelist.");
                    logging.debug("Unload service");
                    if(os.system("sudo systemctl stop "+name+".service")==0): #unload the service
                        logging.info("Service unloaded!");
                    else:
                        logging.error("Error unloading service!");
                else:
                    logging.warning("The service \""+name+"\" is not in the whitelist and cannot be unloaded.");
                    
            #disable service from run at boot
            elif(action=="disable"):
                logging.info("Disable service");
                if name in whitelistServices or name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the whitelist.");
                    logging.debug("Unload service");
                    if(os.system("sudo systemctl --no-block stop "+name+".service")==0): #unload the service
                        logging.info("Service unloaded!");
                    else:
                        logging.error("Error unloading service!");
                    if(os.system("sudo systemctl disable "+name+".service")==0): #unload the service
                        logging.info("Service disabled!");
                    else:
                        logging.error("Error disabling service!");
                else:
                    logging.warning("The service \""+name+"\" is not in the whitelist and cannot be unloaded.");
                    
            #unload and remove the service files from the system
            elif(action=="remove"):
                logging.info("Remove service"); 
                #remove the service from the whitelist if it's present
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the whitelist.");
                    whitelistManager(False, name, whitelistFile);
                else:
                    logging.debug("The service \""+name+"\" is not in the whitelist.");
                    
                logging.debug("Remove the service files from the system folders");
                if(os.system("sudo systemctl stop "+name+".service")==0): #unload the service             
                    removeFile("/lib/systemd/system/"+name+".service");  
                    logging.info("Service removed!");
                else:
                    logging.error("Error unloading the service!");
            else:
                logging.warning("Action type not supported!");                
        removeFile(source+services2manage[i]);
    
def files2update(source, whitelist):
    with open(whitelist, "r") as whitelist:
        whiteFiles = whitelist.read().split('\n');
    files2update = os.listdir(source);
    for i in range(len(files2update)):
        logging.info("\n"+time.ctime(time.time()));
        logging.info("File update process:");
        try:
            whiteFiles.index(files2update[i]);
            dest = files2update[i].replace("@", "/");
            logging.debug("The file \""+files2update[i]+"\" is in the whitelist.");
            logging.info("The file \""+dest+"\" will be updated!");
            moveFile(source+"/"+files2update[i], dest);
        except:
            logging.warning("The file \""+files2update[i]+"\" is not in the whitelist.");
            logging.info("The operation is not permitted and the file \""+files2update[i]+"\" will be deleted!");
            removeFile(source+"/"+files2update[i]);

def files2remove(source, whitelist):
    with open(whitelist, "r") as whitelist:
        whiteFiles = whitelist.read().split('\n');
    files2remove = os.listdir(source);
    for i in range(len(files2remove)):
        logging.info("\n"+time.ctime(time.time()));
        logging.info("File remove process:");
        try:
            whiteFiles.index(files2remove[i]);
            dest = files2remove[i].replace("@", "/");
            logging.debug("The file \""+files2remove[i]+"\" is in the whitelist.");
            logging.info("The file \""+dest+"\" will be removed!");
            removeFile(source+"/"+files2remove[i]);
            removeFile(dest);
        except:
            removeFile(source+"/"+files2remove[i]);
            logging.warning("The file \""+files2remove[i]+"\" is not in the whitelist.");
            logging.info("The operation is not permitted and the file \""+files2remove[i]+"\" will be deleted!");

def checkEmptyFolders():
    if not os.path.exists("exchange/files2update/"):
        os.makedirs("exchange/files2update/");
        os.chown("exchange/files2update/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print("exchange/files2update/ folder created");
        
    if not os.path.exists("exchange/files2remove/"):
        os.makedirs("exchange/files2remove/");
        os.chown("exchange/files2remove/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print("exchange/files2remove/ folder created");
    
    if not os.path.exists("exchange/modules/"):
        os.makedirs("exchange/modules/");
        print("exchange/modules/ folder created");
        os.chown("exchange/modules/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
    
    if not os.path.exists("exchange/services/"):
        os.makedirs("exchange/services/");
        os.chown("exchange/services/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print("exchange/services/ folder created");
    
    if not os.path.exists("exchange/logs/"):
        os.makedirs("exchange/logs/");
        print("exchange/logs/ folder created");

def main():
    while(True):
        files2update(sourcefolder+"files2update/", sourcefolder+"update_whitelist.txt");
        files2remove(sourcefolder+"files2remove/", sourcefolder+"remove_whitelist.txt");
        kernelModules(sourcefolder+"modules/", sourcefolder+"modules_whitelist.txt");
        services(sourcefolder+"services/", sourcefolder+"services_whitelist.txt", sourcefolder+"sysServices_whitelist.txt");
        time.sleep(1);   
        
if __name__ == "__main__":
    checkEmptyFolders();
    
    opts = [opt for opt in sys.argv[1:] if opt.startswith("-")]
    args = [arg for arg in sys.argv[1:] if not arg.startswith("-")]
    
    if "-h" in opts:
        raise SystemExit(f"Usage: {sys.argv[0]} (-source folder | -logsPath path_of_the_log_files | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...")
    elif "-source" in opts:
        sourcefolder = args[opts.index("-source")];
        print("The source folder is: \""+sourcefolder+"\"");
        if "-logsPath" in opts:
            logsPath = args[opts.index("-logsPath")];
            print("The log files path is: \""+logsPath+"\"");            
            if "-logging" in opts:
                loglevel = args[opts.index("-logging")];
                if(loglevel!="DEBUG" and loglevel!="INFO" and loglevel!="WARNING" and loglevel!="ERROR" and loglevel!="CRITICAL"):
                    print("The \""+loglevel+"\" logging level is not available, please choose one level in the list\n");
                    raise SystemExit(f"Usage: {sys.argv[0]} (-source folder | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...");
                print("The logging level is set to: \""+loglevel+"\"");
            else:
                loglevel = "WARNING";
                print("The logging level is set to: \""+loglevel+"\" by default");
            logname =  time.strftime("%Y_%m_%d.log", time.localtime());   
            logging.basicConfig(filename=logsPath+logname, encoding='utf-8', level=logging.getLevelName(loglevel));
            logging.info('ochin_web backgroundWorker started!')
    else:
        raise SystemExit(f"Usage: {sys.argv[0]} (-source folder | -logsPath path_of_the_log_files | -logging (DEBUG | INFO | WARNING | ERROR | CRITICAL) | -h) <arguments>...")
           
    main();
