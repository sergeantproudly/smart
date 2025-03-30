<?php

   if (getenv('DEV')) {
      $Config['Db']['Host']   = 'localhost';
      $Config['Db']['Login']  = 'root';
      $Config['Db']['Pswd']   = '';
      $Config['Db']['DbName'] = 'smartenerg_new';  

   } else {
      $Config['Db']['Host']   = 'localhost';
      $Config['Db']['Login']  = 'root';
      $Config['Db']['Pswd']   = '';
      $Config['Db']['DbName'] = 'smartenerg_new';  
   }
   	
   $Config['Site']['Title']      = 'ООО «СмартЭнерго» - официальный сайт';
   $Config['Site']['Email']      = '';
   $Config['Site']['Keywords']      = '';
   $Config['Site']['Description']   = '';
   $Config['Site']['Url']        = 'https://smartenergo-rt.ru';
   	
   $Config['Smtp']['Server']	= 'smtp.mail.ru';
   $Config['Smtp']['Port']		= '465';
   $Config['Smtp']['Email']	= 'web_form@smartenergo-rt.ru';
	$Config['Smtp']['Password']	= 'J1p2PVYikle';
	$Config['Smtp']['Secure']	= 'ssl';
   	
   error_reporting (E_ALL & ~E_NOTICE);

	// constants
   define ('TEMPLATE_DIR', 'templates/');
   define ('TOOL_DIR', 'tools/');
   define ('IMAGE_DIR', 'images/');
   define ('MISC_DIR', 'misc/');
   define ('MODULE_DIR', 'modules/');
   define ('LIBRARY_DIR', 'library/');
   define ('DOWNLOADS_DIR', 'downloads/');
   	
   define ('ABS_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
   define ('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
   define ('TEMP_DIR', 'mycms/uploads/temp/');

?>