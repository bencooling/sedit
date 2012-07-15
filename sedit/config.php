<?php
/**
 *
 * Config
 *  
 */

define('PUBLIC_SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/sedit-example');

// When pathname does not retrive the doucment name due to Apache DirectoryIndex, fallback to this value
define('DIRECTORY_INDEX', 'index.html');

// Absolute path of sedit
define('ABSPATH', dirname(__FILE__));

// Location of where to read and write files to the data store relative to the web root
define('DATA_STORE', ABSPATH . '/store');