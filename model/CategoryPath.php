<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 23/5/2015
 * Time: 1:49 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class CategoryPath {

    var $category_id;
    var $path_id;
    var $level;

    public function __construct($category_id, $path_id) {
        $this->category_id = $category_id;
        $this->path_id = $path_id;
    }


}