<?php

if (getenv('DEV')) {
   $Config['Db']['Host']   = 'localhost';
   $Config['Db']['Login']  = 'root';
   $Config['Db']['Pswd']   = '';
   $Config['Db']['DbName'] = 'smartenerg_new';
} else {
   $Config['Db']['Host']   = 'localhost';
   $Config['Db']['Login']  = 'smartenerg_new';
   $Config['Db']['Pswd']   = 'sd4ugmRe';
   $Config['Db']['DbName'] = 'smartenerg_new';
}

$Config['Site']['Title']      = '«СмартЭнерго»';
$Config['Site']['Email']      = '';
$Config['Site']['Keywords']      = '';
$Config['Site']['Description']   = '';
$Config['Site']['Url']        = 'https://smartenergo-rt.ru';

$Config['Smtp']['Server']  = 'smtp.yandex.ru';
$Config['Smtp']['Port']    = '465';
$Config['Smtp']['Email']   = 'info@proudly.ru';
$Config['Smtp']['Password']   = 'sergeantpepperr7';
$Config['Smtp']['Secure']  = 'SSL';

error_reporting(E_ALL & ~E_NOTICE);

// constants
define('TEMPLATES_DIR', 'templates/');
define('TOOLS_DIR', 'tools/');
define('IMAGES_DIR', 'images/');
define('MODULES_DIR', 'modules/');
define('LIBRARY_DIR', 'library/');
define('LIBRARY_SITE_DIR', '../library/');
define('UPLOADS_DIR', '../uploads/');
define('TEMP_DIR', 'uploads/temp/');

define('ABS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/mycms/');
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
define('ROOT_DIR', '../');
