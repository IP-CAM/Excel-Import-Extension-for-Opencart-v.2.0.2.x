<?php

define("OC_AUTHENTICATION", FALSE);
define("DB_PREFIX", "pnt_");


require_once('library/Authentication.php');    
require_once('library/Database.php');
require_once('model/Filter.php');
require_once('model/Product.php');
require_once('model/FilterGroup.php');
require_once('model/CategoryPath.php');
require_once('model/Category.php');
require_once('controller/ImportController.php');
require_once('controller/CategoriesController.php');
require_once('controller/ProductsController.php');
?>