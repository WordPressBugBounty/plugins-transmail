<?php

/**
 * Zeptomail plugin helper class
 *
 * @author Zoho Mail
 */
 if(!defined('ABSPATH')){
	exit;
}
class Transmail_Helper {
    
    
    public static function getZeptoMailUrlForDomain($domain){
		
		 $domailURL = "https://";
		 if(str_ends_with($domain, "com")){
			 $domailURL .="zeptomail.zoho.com"; 
		 } else if(str_ends_with($domain, "eu")){
			 $domailURL .="zeptomail.zoho.eu"; 
		 } else if(str_ends_with($domain, "in")){
			 $domailURL .="zeptomail.zoho.in"; 
		 } else if(str_ends_with($domain, "com.cn")){
			 $domailURL .="zeptomail.zoho.com.cn"; 
		 } else if(str_ends_with($domain, "au")){
			 $domailURL .="zeptomail.zoho.com.au"; 
		 } else if(str_ends_with($domain, "jp")){
			 $domailURL .="zeptomail.zoho.jp"; 
		 } else if(str_ends_with($domain, "zohocloud.ca")){
			 $domailURL .="zeptomail.zohocloud.ca"; 
		 } else if(str_ends_with($domain, "sa")){
			 $domailURL .="zeptomail.zoho.sa"; 
		 } else{
			 $domailURL .="zeptomail.zoho.com"; 
		 }
		 return $domailURL;
		 
	}
	
}