<?php
session_start();
//error_reporting(0);
class db {
	private static $con=null;
  
 function con(){
        
        if( !self::$con){
            if($_SERVER['HTTP_HOST']=='localhost'){
				self::$con = new PDO("mysql:host=localhost; dbname=gautam_clinic", "root", "");	
			} else{
				self::$con = new PDO("mysql:host=srv834.hstgr.io; dbname=u457158986_gautamayurveda", "u457158986_gautamayurveda", "#Gautam@123");
			}
         self::$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);//which tells PDO to disable emulated prepared statements and use real prepared statements. This makes sure the statement and the values aren't parsed by PHP before sending it to the MySQL server (giving a possible attacker no chance to inject malicious SQL).
         self::$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        }
        
        return self::$con;
    }
    

}

?>