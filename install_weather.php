#!/usr/bin/php
<?php
//  ------------------------------------------------------------
//  (c) 2023/2025 by KJ5MZL/WRXB288 lagmrs.com all rights reserved
// This installer sets up and installs the software. 
// all software is hand coded in php from scratch 
// in North Louisiana.
// -------------------------------------------------------------
//
// v1.0 alpha 10/11/25
// 
$phpVersion= phpversion();
$path= "/etc/asterisk/local/mm-software";
$ver="v1.0"; $release="10-11-2025";
$out=""; $in=""; $skip="
























";
print $skip;c641($in);sleep(2);print $skip;
$piSystem=false;if (is_readable("/proc/device-tree/model")) {$piVersion = file_get_contents ("/proc/device-tree/model");$piSystem=true;}
else {$piVersion =	exec('uname -m -p');}


print "
  _  __    _ _____ __  __ _______                  
 | |/ /   | | ____|  \/  |___  / |                 
 | ' /    | | |__ | \  / |  / /| |                 
 |  < _   | |___ \| |\/| | / / | |                 
 | . \ |__| |___) | |  | |/ /__| |____             
 |_|\_\____/|____/|_| _|_/_____|______| ___   ___  
 \ \        / /  __ \ \ \ / /  _ \__ \ / _ \ / _ \ 
  \ \  /\  / /| |__) | \ V /| |_) | ) | (_) | (_) |
   \ \/  \/ / |  _  /   > < |  _ < / / > _ < > _ < 
    \  /\  /  | | \ \  / . \| |_) / /_| (_) | (_) |
     \/  \/   |_|  \_\/_/ \_\____/____|\___/ \___/ 
                                                   
                                                   

PHP:$phpVersion  Installer:$ver  Release date:$release 
CPU:$piVersion
(c) 2023/2025 by KJ5MZL/WRXB288 lagmrs.com all rights reserved
============================================================
 Welcome to my PI installer. Software made in loUiSiAna.
 This installs the NWS weather package. This is a mini
 version of the full package. It adds NWS cap weather
 Huricane and cap watch to the supermon. This small
 version does not talk. 
 
<-Be sure you have made a backup of your memory card->
============================================================
Software will be installed to [$path]

 i) install
 
 Any other key to abort 
";
$a = readline('Enter your command: ');

if ($a=="i"){
print " [Verifing ....";
$path= "/etc/asterisk/local/mm-software"; 
if(!is_dir($path)){ mkdir($path, 0755);}
print"-]\n";

installa($out);
chdir($path); 

if (file_exists("$path/setup_weather.php")){include ("$path/setup_weather.php");}
else {print "Error install failed! $path/setup_weather.php missing\n";}
print "

weather setup added to the admin menu.  
You now need to reboot to activate the admin menu.

If you like this software tell your frinds.
Louisiana software its just better.

Software Made in loUiSiAna
Thank you for downloading........... And have Many nice days

";
}
print"\n";


function installa($in){
global $docRouteP,$path;

$path  = "/etc/asterisk/local/mm-software";if(!is_dir($path)){ mkdir($path, 0755);}        
$repoURL= "https://raw.githubusercontent.com/tmastersmart/allstar/refs/heads/main/";
$pathR = "$path/repo";  if(!is_dir($pathR)){ mkdir($pathR, 0755);}
$pathB = "$path/backup";if(!is_dir($pathB)){ mkdir($pathB, 0755);}

print"Cleaning any existing repos........\n";
chdir($pathR);clean_($pathR);
print "Downloading new repos ............\n";
exec("sudo wget $repoURL/weather-download.zip",$output,$return_var);
print "Downloading finished..............\n";
chdir($pathR);
print "Unzipping and Installing..........\n";  

exec("unzip $pathR/weather-download.zip",$output,$return_var);
  
     
   foreach (glob("*.php") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing php file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){
    rename ("$pathR/$file", "$path/$file");
     exec("sudo chmod +x $path/$file",$output,$return_var); 
    } 
    print"ok\n";
    }
  }
  
   foreach (glob("*.csv") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing csv file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$path/$file"); } 
    print"ok\n";
    }
  }  
 
   foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { 
    print"Installing txt file:$path/$file "; 
    if (file_exists("$path/$file")){unlink("$path/$file");print"Replacing ";}// kill existing file
    if (file_exists("$pathR/$file")){rename ("$pathR/$file", "$path/$file"); } 
    print"ok\n";
    }
  }  
 
admin_sh_menuI("install");

}




function admin_sh_menuI(){

global $release;
print " Installing into admin menu ";
$file ="/usr/local/sbin/firsttime/adm01-shell.sh";
$file2="/usr/local/sbin/firsttime/weather-fx.sh";
               
copy($file, $file2); print "-";

$formated="#/!bin/bash
#MENUFT%055%Weather Setup Program Version:$release
";

$out='
$SON
reset

php /etc/asterisk/local/mm-software/weather_setup.php

exit 0
';
$out = "$formated $out";
$fileOUT = fopen($file2, "w") ;flock( $fileOUT, LOCK_EX );fwrite ($fileOUT, $out);flock( $fileOUT, LOCK_UN );fclose ($fileOUT); print "-";
exec("sudo chmod +x $file2",$output,$return_var);
if (file_exists($file2)){print"<ok>\n";}
else{print"<Error>\n";}
}
 
 
 






function clean_($in){

   chdir($in);
   
 foreach (glob("*.zip") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.php") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.txt") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.csv") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
 foreach (glob("*.ul") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }
 foreach (glob("*.wav") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }
 foreach (glob("*.gsm") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    } 
  foreach (glob("*.diz") as $file) {
    if($file == '.' || $file == '..') continue;
    if (is_file($file)) { unlink($file);print"del $file\n";  }
    }     
     
} 



 
function c641($in){
print"




        **** COMODORE 64 BASIC V2 **** 
 64K RAM SYSTEM  38911 BASIC BYTES FREE
READY.

";
}
