# Copyright (c) 2022 perniciousflier@gmail.com  
# This code is a part of the ochin project (https://github.com/ochin-space)
# The LICENSE file is included in the project's root. 

import os
import time
import subprocess
import logging
from files import *
from xml.etree import ElementTree

def createService(name, cmd_line, unitOptions, serviceOptions, installOptions):
    logging.debug("The \""+name+"\".service file will be updated");    
    with open("/lib/systemd/system/"+name+".service", 'w') as file:
        script = "[Unit]\nDescription="+name+"\n"+unitOptions+"\n\n[Service]\nExecStart="+cmd_line+"\n"+serviceOptions+"\n\n[Install]\n"+installOptions;
        file.write(script);
        file.close();
        
def enableService(name):
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'masked\n'): 
        subprocess.run(["sudo","systemctl","unmask",name+".service"]);   
        logging.debug("Service unmasked");                     
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'disabled\n'): 
        subprocess.run(["sudo","systemctl","enable",name+".service"]);   
        is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
        if(is_enabled==b'enabled\n'): 
            logging.debug("Service enabled");     
        else:
            logging.warning("Error enabling service");     
            return False;                   
    if(is_enabled==b'enabled\n'): 
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                            
        if(is_active==b'inactive\n'):       
            subprocess.run(["sudo","systemctl","start",name+".service"]);                  
            is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                            
            if(is_active==b'active\n'): 
                logging.debug("System loaded");
                return True;
            else:
                logging.warning("Error loading service");
                return False;
    else:
        return False;
                        
def disableService(name):
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'masked\n'): 
        subprocess.run(["sudo","systemctl","unmask",name+".service"]);   
        logging.debug("System service unmasked");                      
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'enabled\n'): 
        subprocess.run(["sudo","systemctl","disable",name+".service"]);   
        is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
        if(is_enabled==b'disabled\n'): 
            logging.debug("Service disabled");     
        else:
            logging.warning("Service can't be disabled");   
            return False;                   
    if(is_enabled==b'disabled\n'):                   
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                            
        if(is_active==b'active\n'):       
            subprocess.run(["sudo","systemctl","stop",name+".service"]);                  
            is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                            
            if(is_active==b'inactive\n'): 
                logging.debug("Service unloaded");
                return True;
            else:
                logging.warning("Error unloading service");
                return False;
        else:
            return True;
    return False;

def startService(name):
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'masked\n'): 
        subprocess.run(["sudo","systemctl","unmask",name+".service"]);   
        logging.debug("System service unmasked");      
    if(is_enabled!=b'masked\n'): 
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                    
        if(is_active==b'inactive\n'): 
            subprocess.run(["sudo","systemctl","start",name+".service"]); 
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                           
        if(is_active==b'active\n'):                       
            logging.debug("System service loaded");
            return True;
        else:
            logging.error("Error loading system service");
            return False;
    return False;
    
def stopService(name):
    is_enabled = subprocess.Popen(["sudo","systemctl","is-enabled",name+".service"], stdout=subprocess.PIPE).communicate()[0];
    if(is_enabled==b'masked\n'): 
        subprocess.run(["sudo","systemctl","unmask",name+".service"]);   
        logging.debug("System service unmasked");      
    if(is_enabled!=b'masked\n'): 
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                    
        if(is_active==b'active\n'): 
            subprocess.run(["sudo","systemctl","stop",name+".service"]); 
        is_active = subprocess.Popen(["sudo","systemctl","is-active",name+".service"], stdout=subprocess.PIPE).communicate()[0];                           
        if(is_active==b'inactive\n'):                       
            logging.debug("System service unloaded");
            return True;
        else:
            logging.error("Error unloading system service");
            return False;
    return False;
    
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
                #check if the service is present in the system service whitelist 
                if name in whitelistSysServices:
                    logging.debug("The service \""+name+"\" is in the system service whitelist.");
                    if(startService(name)):
                        logging.info("System service loaded");
                    else:
                        logging.warning("Error loading system service");
                        
                else: 
                    logging.warning("The service \""+name+"\" was not in the system service whitelist.\nThe service cannot be loaded.");
                    
            #unload a system service
            elif(action=="sysUnload"):
                logging.info("Unload a system service");
                #check if the service is present in the system service whitelist 
                if name in whitelistSysServices:
                    logging.debug("The service \""+name+"\" is in the system service whitelist.");
                    if(stopService(name)):
                        logging.info("System service unloaded");
                    else:
                        logging.warning("Error unloading system service");
                else: 
                    logging.warning("The service \""+name+"\" was not in the system service whitelist.\nThe service cannot be unloaded.");
                    
            #unload a system service
            elif(action=="sysEnable"):
                logging.info("Enable a system service");
                #check if the service is present in the system service whitelist 
                if name in whitelistSysServices:
                    logging.debug("The service \""+name+"\" is in the system service whitelist.");
                    if(enableService(name)):
                        logging.info("System service enabled");
                    else:
                        logging.warning("Error enabling system service");
                else: 
                    logging.warning("The service \""+name+"\" was not in the system service whitelist.\nThe service cannot be enabled.");
                    
            #unload a system service
            elif(action=="sysDisable"):
                logging.info("Disable a system service");
                #check if the service is present in the system service whitelist 
                if name in whitelistSysServices:        
                    logging.debug("The service \""+name+"\" is in the system service whitelist.");            
                    if(disableService(name)):
                        logging.info("System service disabled");
                    else:
                        logging.warning("Error disabling system service");
                else: 
                    logging.warning("The service \""+name+"\" was not in the system service whitelist.\nThe service cannot be disabled.");
            
            #load a service
            if(action=="load"):
                logging.info("Load a service");
                #check if the service is present in the service whitelist 
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the service whitelist.");
                elif old_name in whitelistServices:
                    logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the old service file has been deleted.");
                    if(disableService(old_name)):
                        logging.info("Service disabled");
                    else:
                        logging.warning("Error disabling service");
                    whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                    removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file
                    whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                else:
                    logging.debug("The service \""+name+"\" was not in the whitelist.\nThe service has been added to the whitelist.");
                    whitelistManager(True, name, whitelistFile);  
                #create the files needed to start the service at boot   
                removeFile("/lib/systemd/system/"+name+".service"); #remove the old named file
                createService(name, cmd_line, unitOptions, serviceOptions, installOptions);    
                if(startService(name)):
                    logging.info("Service loaded");
                else:
                    logging.warning("Error loading service");
       
            #unload a service
            elif(action=="unload"):
                logging.info("Unload a service");
                #check if the service is present in the service whitelist 
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the service whitelist.");
                    createService(name, cmd_line, unitOptions, serviceOptions, installOptions); 
                    if(stopService(name)):
                        logging.info("Service unloaded");
                    else:
                        logging.warning("Error unloading service");
                elif old_name in whitelistServices:
                    logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the old service file has been deleted.");
                    if(stopService(old_name)):
                        logging.info("Service unloaded");
                    else:
                        logging.warning("Error unloading service");
                    whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                    #create the files needed to start the service at boot
                    createService(name, cmd_line, unitOptions, serviceOptions, installOptions); 
                    if(stopService(name)):
                        logging.info("Service unloaded");
                    else:
                        logging.warning("Error unloading service"); 
                else:
                    logging.warning("The service \""+name+"\" was not in the service whitelist.\nThe service cannot be unloaded.");
                    
            #enable a service
            elif(action=="enable"):
                logging.info("Enable a service");
                #check if the service is present in the service whitelist 
                if name in whitelistServices:
                    logging.debug("The service \""+name+"\" is in the service whitelist.");
                elif old_name in whitelistServices:
                        logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the old service file has been deleted.");                        
                        if(disableService(old_name)):
                            logging.info("Service disabled");
                        else:
                            logging.warning("Error disabling service");
                        whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                        removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file
                        whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                else:
                    logging.debug("The service \""+name+"\" was not in the whitelist.\nThe service has been added to the whitelist.");
                    whitelistManager(True, name, whitelistFile);    #add new name to the white list                   
                #create the files needed to start the service at boot
                createService(name, cmd_line, unitOptions, serviceOptions, installOptions);    
                if(enableService(name)):
                    logging.info("Service enabled");
                else:
                    logging.warning("Error enabling service");
                    
            #disable a service
            elif(action=="disable"):
                logging.info("Disable a service");
                #check if the service is present in the service the whitelist 
                if name in whitelistServices:        
                    logging.debug("The service \""+name+"\" is in the whitelist.");     
                    if(disableService(name)):
                        logging.info("Service disabled");
                    else:
                        logging.warning("Error disabling service");
                elif old_name in whitelistServices:
                    logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe whitelist has been updated and the old service file has been disabled");                                        
                    if(disableService(old_name)):
                        logging.info("Service disabled");
                    else:
                        logging.warning("Error disabling service");
                    whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                    removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file 
                    whitelistManager(True, name, whitelistFile);    #add new name to the white list             
                else:
                    whitelistManager(True, name, whitelistFile);    #add new name to the white list     
                    logging.warning("The service \""+name+"\" was not in the service whitelist.\nThe service has been created but disabled.");
                createService(name, cmd_line, unitOptions, serviceOptions, installOptions);    
                     
            #unload and remove the service files from the system
            elif(action=="remove"):
                logging.info("Remove service"); 
                #remove the service from the whitelist if it's present
                if name in whitelistServices:        
                    logging.debug("The service \""+name+"\" is in the service whitelist.");                                        
                    if(disableService(name)):
                        logging.info("Service disabled");
                    else:
                        logging.warning("Error disabling service");
                    removeFile("/lib/systemd/system/"+name+".service");
                    whitelistManager(False, name, whitelistFile);   #remove the old name from the whitelist      
                    logging.info("Service removed");                    
                elif old_name in whitelistServices:
                    logging.debug("The service name \""+old_name+"\" is changed to \""+name+"\".\nThe service file has been deleted.");
                    whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist                                         
                    if(disableService(old_name)):
                        logging.info("Service disabled");
                    else:
                        logging.warning("Error disabling service");
                    removeFile("/lib/systemd/system/"+old_name+".service"); #remove the old named file 
                    whitelistManager(False, old_name, whitelistFile);   #remove the old name from the whitelist      
                    logging.info("Service removed");
                else:
                    logging.warning("The service \""+name+"\" was not in the service whitelist.\nThe service cannot be removed.");
            else:
                logging.warning("Action type not supported");                
        removeFile(source+services2manage[i]);
    