# Copyright (c) 2022 perniciousflier@gmail.com  
# This code is a part of the ochin project (https://github.com/ochin-space)
# The LICENSE file is included in the project's root. 

import time
import shutil
import os
import subprocess
import logging
from pwd import getpwnam
 
def moveFile(source, dest):
    if(os.path.exists(source)):
        try:
            shutil.move(source, dest);
            logging.debug("File "+source+" updated");
        except:
            logging.error("Error while moving file \""+source+"\"");
    else:
        logging.warning("Cannot delete the file \""+source+"\" since it doesn't exist!");

def append2File(sourceFile, destFile):
    if(os.path.exists(destFile)):
        try:
            with open(sourceFile, 'r') as file:
                script = file.read();
            logging.debug(script);
            content = open(destFile, 'a');
            content.write(script);
            content.close();
        except:
            logging.error("Error while appending to file \""+destFile+"\"");
    else:
        logging.warning("Cannot append to the file \""+destFile+"\" since it doesn't exist!");
    
def removeFile(source):
    if(os.path.exists(source)):
        try:
            subprocess.run(["sudo","rm",source]);
            logging.debug("File "+source+" removed.");
        except:
            logging.error("Error while deleting file ");
    else:
        logging.warning("Cannot delete the file \""+source+"\" since it doesn't exist!");
        

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
 
def files2append(source, whitelist):
    with open(whitelist, "r") as whitelist:
        whiteFiles = whitelist.read().split('\n');
    files2append = os.listdir(source);
    for i in range(len(files2append)):
        logging.info("\n"+time.ctime(time.time()));
        logging.info("File append process:");
        try:
            whiteFiles.index(files2append[i]);
            destFile = files2append[i].replace("@", "/");
            logging.debug("The file \""+files2append[i]+"\" is in the whitelist.");
            logging.info("The script will be appended!");
            append2File(source+"/"+files2append[i],destFile);
            removeFile(source+"/"+files2append[i]);
        except:
            logging.warning("The file \""+files2append[i]+"\" is not in the whitelist.");
            logging.info("The operation is not permitted and the file \""+files2append[i]+"\" will be deleted!");
            removeFile(source+"/"+files2append[i]);
    
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

def checkEmptyFolders(sourcefolder):
    if not os.path.exists(sourcefolder+"files2update/"):
        os.makedirs(sourcefolder+"files2update/");
        os.chown(sourcefolder+"files2update/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print(sourcefolder+"files2update/ folder created");
        
    if not os.path.exists(sourcefolder+"files2append/"):
        os.makedirs(sourcefolder+"files2append/");
        os.chown(sourcefolder+"files2append/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print(sourcefolder+"files2append/ folder created");
        
    if not os.path.exists(sourcefolder+"files2remove/"):
        os.makedirs(sourcefolder+"files2remove/");
        os.chown(sourcefolder+"files2remove/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print(sourcefolder+"files2remove/ folder created");
    
    if not os.path.exists(sourcefolder+"modules/"):
        os.makedirs(sourcefolder+"modules/");
        print(sourcefolder+"modules/ folder created");
        os.chown(sourcefolder+"modules/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
    
    if not os.path.exists(sourcefolder+"services/"):
        os.makedirs(sourcefolder+"services/");
        os.chown(sourcefolder+"services/",getpwnam('www-data').pw_uid,getpwnam('www-data').pw_gid);
        print(sourcefolder+"services/ folder created");
    
    if not os.path.exists(sourcefolder+"logs/"):
        os.makedirs(sourcefolder+"logs/");
        print(sourcefolder+"logs/ folder created");
