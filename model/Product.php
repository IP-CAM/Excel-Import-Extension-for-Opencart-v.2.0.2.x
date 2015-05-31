<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 22/5/2015
 * Time: 5:35 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class Product {

    var $name;
    var $description;
    var $enName;
    var $enDescription;
    var $code;
    var $price;
    var $quantity;
    var $weight;
    var $image;
    var $categories;
    var $id;
    var $filters;

    function __construct($name, $description, $enName, $enDescription, $code, $price, $quantity, $weight, $image, $categories) {
        $this->name = $name;
        $this->description = $description;
        $this->enName = $enName;
        $this->enDescription = $enDescription;
        $this->code = $code;
        $this->price = ($price == NULL) ? 0 : $price;
        $this->quantity = ($quantity == NULL) ? 0 : $quantity;
        $this->weight = ($weight == NULL) ? 0 : $weight;
        $this->categories = $categories;
        $this->filters = array();
    }
}