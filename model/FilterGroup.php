<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 27/5/2015
 * Time: 7:04 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class FilterGroup {
    var $id;
    var $name;
    var $enName;

    public function __construct($name, $enName) {
        $this->name = $name;
        $this->enName = $enName;
    }
}