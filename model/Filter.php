<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 30/5/2015
 * Time: 7:32 μμ
 */
require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');
class Filter {

    var $filterGroup;
    var $id;
    var $name;
    var $enName;
    var $new;

    public function __construct($name, $enName, $filterGroup, $new) {
        $this->name = $name;
        $this->enName = $enName;
        $this->filterGroup = $filterGroup;
        $this->new = $new;
    }
}