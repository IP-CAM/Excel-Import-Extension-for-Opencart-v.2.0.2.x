<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 23/5/2015
 * Time: 10:37 πμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class Category {

    var $id;
    var $name;
    var $description;
    var $enName;
    var $enDescription;
    var $image;
    var $parent;
    var $path;
    var $filterGroups;
    var $new = false;

    public function __construct($id, $name, $description, $enName, $enDescription, $image, $parent) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->enName = $enName;
        $this->enDescription = $enDescription;
        $this->image = $image;
        $this->parent = $parent;
        $this->path = array();
        $this->filterGroups = array();
    }

}