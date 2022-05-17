# Copyright (c) 2022 perniciousflier@gmail.com  
# This code is a part of the ochin project (https://github.com/ochin-space)
# The LICENSE file is included in the project's root. 

import os
import time
import subprocess
import logging
from files import *
from xml.etree import ElementTree

#create a new module
def createModule(name, cmd_line, options):
    logging.debug("The \""+name+"\" module will be updated!");    
    with open("/etc/modules-load.d/"+name+".conf", 'w') as file:
        file.write(cmd_line);
        file.close();
    
    with open("/etc/modprobe.d/"+name+".conf", 'w') as file:
        script = "options "+cmd_line+" name="+cmd_line+" "+options;
        file.write(script);
        file.close();
          
#module manager
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
                        #if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module                        
                        if(subprocess.call(["sudo","modprobe","-r",cmd_line])==0):    #unload the module
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
                #if(os.system('sudo modprobe '+cmd_line+" "+options)==0): #load the module         
                if(subprocess.call(["sudo","modprobe",cmd_line,options])==0):    #unload the module
                    logging.info("Module loaded!");
                else:
                    logging.error("Error loading module! ");
                    
            #unload a kernel module
            elif(action=="unload"):
                logging.info("Unload module");
                #if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module                        
                if(subprocess.call(["sudo","modprobe","-r",cmd_line])==0):    #unload the module
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
                #if(os.system('sudo modprobe -r '+cmd_line)==0):    #unload the module                         
                if(subprocess.call(["sudo","modprobe","-r",cmd_line])==0):    #unload the module             
                    removeFile("/etc/modules-load.d/"+name+".conf");              
                    removeFile("/etc/modprobe.d/"+name+".conf");
                    logging.info("Module removed!");
                else:
                    logging.error("Error unloading module!");
            else:
                logging.warning("Action type not supported!");                
        removeFile(source+modules2manage[i]);
    