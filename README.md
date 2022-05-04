![Alt text](images/ochin_logo.png?raw=true&=200x "ochin_web")
<h1>ochin_web</h1>
<p>The ochin_web software is designed to become a useful support for the developer of devices based on Raspberry Pi boards. In particular, it is designed to manage the Raspberry Pi CM4 module mounted on the <a href="https://github.com/ochin-space/ochin-CM4">ochin_CM4</a> carrier board, also part of the ochin project.</p>
<p>The idea is to speed up the development of applications, simplifying all those activities that are fundamental to the perfect success of a project, but not directly connected to the development of a software. Operations like the correct configuration of the system, often long and boring activities, which are easily lost track after having done them.</p>
<p>Operations such as the hardware configuration of the raspberry pi board, the creation of services, the startup of kernel modules or the configuration of the network interfaces.</p>
<p>By means of this software it is possible to manage all these things, keeping track of them by means of editable tables.</p>
<p>The software also integrates an addons manager that allows you to load additional packages to increase the functionality of the system. These packages will be released as side projects of the ochin_web, part of the ochin project itself.</p>
<p>They will be included in the following list from time to time:</p>
<ol>
<li><a href="">Local Atlas</a>, This addon allows you to download and manage geographic maps locally quickly and easily.</li>
</ol>

<h3>DISCLAIMER</h3>
...part of the DISCLAIMER.md doc present in the root of the project...
<i><ul><li>This software is based on the use of an Apache webserver and is in fact a website. Unlike a normal public website, it must be able to perform operations on the Operating System files with advanced administrator rights. For this to happen, the user "www-data" has been promoted to the "sudoers" group. This puts the entire operating system at great risk from possible external attacks. The software itself allows the user to perform advanced operations that can compromise the stability of the system. For these reasons, before using this software it is necessary to understand well what are the objectives for which it was created and its limits.</li>
<li>The software is meant to become a web development platform for Raspberry Pi board-based devices. The basic structure of the software allows you to manage services, the hardware configuration of the Raspberry pi board, kernel modules, networking boards and more. Addons can be installed on the base structure to provide advanced features. The software is therefore suitable for projects where the Raspberry Pi board is used to manage a hardware device, such as a robot. In this case the sw ochin_web is used as a graphical interface to manage the machine, accessible only to the developer or user of the robot. It is highly inadvisable to make the web interface public, due to the risks involved in having a public website managed by a user who is also an operating system administrator.</li></ul></i>
<h3>How to install ochin_web</h3>
<p>
To install the software it is necessary to prepare the Raspberry Pi board with a clean image of the system, configure the internet connection and update it with the following commands:</p>
<p> sudo apt-get update </p>
<p> sudo apt-get upgrade </p>
<p>and then install the git package</p>
<p>sudo apt-get install git</p>
<p>It's now possible to clone the ochin_web git repo with the following command:</p>
<p>sudo git clone https://github_token@github.com/ochin-space/ochin_web</p>
<p>In order to run, ochin_web needs additional software (apache, php, sqlite3 etc..) and it's also needed to setup some permissions. To simplify the installation process, there is a script in the root of the project that manage the needed packages installations and set the permissions.</p>
<p>cd ochin_web</p>
<p>sudo chmod +x ochin_web_install.sh</p>
<p>sudo ./ochin_web_install.sh</p>
<h3>How to use ochin_web</h3>
<p>
<p> If the installation process was successful, opening the browser at the ip address of the Raspberry Pi board. Alternatively to the IP address, you can reach the page with the hostname "ochin.local". </p>
<p> The first thing to do is to create a new user by clicking on "Register here" and then "Sign In". To sign in you can use the "username" or the "email" plus the password.</p>

![Alt text](images/signin.png?raw=true&=200x "sign in")

![Alt text](images/register.png?raw=true&=200x "register")
<p>As soon as you enter the software, you will only see the topbar.</p>
![Alt text](images/web_landing.png?raw=true&=200x "landing page")
<p>In the topbar you have the "Configuration" menu, the "Log Out" button and the user name and avatar on the right side.</p>
<p>To change the user data and the avatar image you have to enter the user profile manager clicking on the default avatar on the right or from the menu "Configuration-> User Profile"</p>

![Alt text](images/user.png?raw=true&=200x "User Profile")
<p>From the "configuration" menu there are links to the system configuration pages. </p>
<p>Each page is well documented and to see the guide just click the "i" icon at the top right of the page itself.</p>
<p> Pressing the "i" icon, will open a modal window with detailed information on the use of the page. The guide, in addition to describing the features, explains in detail what the software does in the background when configuration changes are made. </p>
<p> The information may apparently seem overly technical but the purpose is twofold, first of all to inform on which files changes are made and how, and secondly it is a way to learn. This also allows you to investigate the system directly if something does not work as expected, either due to a possible bug or user error. </p>
<p> The functionality of the individual pages will be briefly described below. </p>
<h4>WiFi Config</h4>

![Alt text](images/network.png?raw=true&=200x "network config")
<p>The "Network Configuration" is web a tool that allows you to modify the configuration of the network adapters.All the adapter connected to the board are displayed in a table that allows you to edit them configuration.</p>
<p>Edit the configuration</p>
<p>By pressing the "edit" button corresponding to the adapter of interest, a popup will be opened, from which it is possible to modify the network configuration. On the top of the modal windows there is the name of the adapter. The first switch is enabled by default and let you Enable / Disable the adapter. If the selected adapter is enabled, it is possible to choose wether to configure it as an access point or as a device connected to another WLAN. If You want to configure the adapter to connect to a certain WLAN, you just have to fill the two fieds below: SSID and Password.
Selecting the next switch is possible to set a static IP_Address and SubnetMask instead of using the DHCP.</p>
<p>
In case you want to create an Access Point, the first switch should be selected and new fields will be shown.
The SSID and Password are still there, but in AP mode those represent the brand new WLAN that it's going to be created. </p>
<h4>Hardware Configuration</h4>

![Alt text](images/hwconfig.png?raw=true&=200x "Hardware Configuration")
<p>The "Hardware Configuration" is web a tool that allows you to modify the "boot/config.txt" file.</p>
<p>The Raspberry Pi uses a configuration file instead of the BIOS you would expect to find on a conventional PC. The system configuration parameters, which would traditionally be edited and stored using a BIOS, are stored instead in an optional text file named config.txt. This is read by the GPU before the ARM CPU and Linux are initialised. It must therefore be located on the first (boot) partition of your SD card, alongside bootcode.bin and start.elf. This file is normally accessible as /boot/config.txt from Linux, and must be edited as root.</p>
<h4>Kernel Modules</h4>

![Alt text](images/kernelmod.png?raw=true&=200x "Kernel Modules")
<p>The "Kernel Modules editor" is web a tool that allows you to select new modules to be started at system boot by means of the modprobe manager.
All the kernel modules added are displayed in a table that allows you to edit or delete them.</p>
<p>Add a new Kernel Module</p>
<p>By pressing the "Add New Module +" icon on the top right edge of the table, you create a new empty row in the table. Once the new row is created you can edit in order to start your new Kernel Module.</p>
<p>Edit a Module</p>
<p>By pressing the "edit" button corresponding to the module of interest, a popup will be opened, from which it is possible to modify the parameters of the kernel module itself.
In the upper part of the modal window there is a selection box, from which it is possible to select a kernel module among those present by default. To add or remove module from the default list, you need to edit the file "/var/www/html/ochin/apps/modules_editor/helper/default_modules.json".
The "Name:" field contains is the name in the table.
The "Module name:" field contains is the name of the module to start.
The "Options:" field contains the options related to the module.
The "Description:" field contains a description of the kernel module, useful for noting the characteristics and functionalities of the module.</p>
<p>Delete a Module</p>
<p>By pressing the "delete" button corresponding to the module of interest, you can delete the module from the table.</p>
<p>Manual tool</p>
<p>By pressing the "Manual" button corresponding to the kernel module of interest a popup will be opened. On the "Manual" modal window it is possible to load, unload or check the status of the kernel module and see the results in a terminal console. </p>
<h4>Systemctl Services</h4>

![Alt text](images/services.png?raw=true&=200x "Service Configuration")
<p>The "Service editor" is web a tool that allows you to modify and create new services to be started at system boot by means of the systemd service manager.
All the services created are displayed in a table that allows you to edit or delete them.</p>
<p>Create a new Service</p>
<p>By pressing the "Add New Service +" icon on the top right edge of the table, you create a new empty row in the table. Once the new row is created you can edit in order to start your new service.</p>
<p>Edit a Service</p>
<p>By pressing the "edit" button corresponding to the service of interest, a popup will be opened, from which it is possible to modify the parameters of the service itself.
In the upper part of the modal window there is a selection box, from which it is possible to select a service among those present by default. To add or remove services from the default list, you need to edit the file "/var/www/html/ochin/apps/service_editor/helper/defaultServices.json".
The "Cmd Line:" field contains the script to execute.
The "[Unit] options:" field contains the options related to the unit.
The "[Service] options:" field contains the options related to the service.
The "[Install] options:" field contains the options relating to the installation phase of the unit (enable or disable).
for details look https://www.commandlinux.com/man-page/man5/systemd.unit.5.html website
The "Description:" field contains a description of the service, useful for noting the characteristics and functionalities of the service.</p>
<p>Delete a Service</p>
<p>By pressing the "delete" button corresponding to the service of interest, you can delete the service.</p>
<p>Manual tool</p>
<p>By pressing the "Manual" button corresponding to the service of interest a popup will be opened. On the "Manual" modal window it is possible to start, stop or check the status of the service and see the results in a terminal console. </p>
<h4>Autostart SW at boot</h4>

![Alt text](images/autostart.png?raw=true&=200x "Autostart sw at boot")
<p>The "Autostart SW at Boot" is web a tool that allows you to start a software at the end of the boot procedure.
All the istances created are displayed in a table that allows you to edit or delete them.</p>
<p>Add a new Cmd Line</p>
<p>By pressing the "Add New Cmd Line +" icon on the top right edge of the table, you create a new empty row in the table. Once the new row is created you can edit in order to start your software.</p>
<p>Edit</p>
</p>By pressing the "edit" button corresponding to the Cmd Line of interest, a popup will be opened, from which it is possible to modify the parameters of the Cmd Line itself.
In the upper part of the modal window there is a selection box, from which it is possible to select a Cmd Line among those present by default. To add or remove module from the default list, you need to edit the file "/var/www/html/ochin/apps/autostart/helper/defaultautostart.json".
The Checkbox lets you enable or disable the Cmd Line. If the Cmd Line is disabled, is still saved but commented out.
The "Name:" field contains is the name in the table.
The "Cmd Line:" field contain the command line to execute at boot. In order to make it work please locate the executable using absolute path.
The "Description:" field contains a description of the Cmd Line, useful for noting the characteristics and functionalities of the software.
</p>
<h4>Addons Manager</h4>

![Alt text](images/addonsman.png?raw=true&=200x "Addons Manager")
<p>The "Addons Manager" is a web tool that has the purpose of managing additional software packages to the ochin_web environment.</p>
<p>What is an addon</p>
<p>With the term "addon" we mean a software that is installed inside of the ochin_web basic structure, with the purpose of extending its functionality.</p>
<p>How to use the addons manager</p>
<p>Inside the page there are three tables, the first table refers to the addons present in the "Configuration" tab present in the topbar.
The second table contains the list of addons present in the "Applications" tab present in the topbar.
The third table contains the addons that are not included in any of the previous tabs and will be displayed on the first level of the topbar, to the right of the "Configuration" menu.</p>
<p>By clicking on the "Upload a new Addon" button it is possible to upload a new addon in ".zip" format. Each new addon loaded will be inserted into the table assigned to it, as foreseen by its "install.xml" file.
Each addon can be managed by means of the "edit" button. By pressing "edit" a modal diaolog box will be opened.</p>
<p>From the modal dialog you can enable or disable the addon. If the addon is disabled, it remains in the system but is not displayed in the topbar.
By means of a select bar it is possible to choose whether to assign the addon to the "Applications", "Configuration", "Development" or "Topbar". In the last case, the addon will be shown on the zero level in the topbar. From the "name" field you can change the name that will be displayed in the menu, while "folder name" is the path where the addon will be saved, inside of the "/var/www/html/ochin/addons/" folder.</p>
<p>By clicking the "Delete" button, the folder that contain the addon will be delated and the addon removed from the addons database. </p>