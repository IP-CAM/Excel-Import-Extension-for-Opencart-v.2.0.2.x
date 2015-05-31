<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 22/5/2015
 * Time: 2:05 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class ProductsController extends ImportController {

    private $products;

    public function __construct() {
    }

    public function parse() {
        $data = $this->getData();
        $products = array();
        if(empty($data)) {
            parent::$error = 'The file you uploaded is empty';
            return false;
        }

        foreach($data as $key => $xlsProduct) {

            if(count($xlsProduct[0]) < 10 ) {
                parent::$error = 'Found false information in row='.$key;
                return false;
            }

            $temp = $xlsProduct[0];
            $categories = $this->parseProductCategories($temp[9]);

            $product = new Product($temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6], $temp[7], $temp[8], $categories);

            if(!empty($temp[10]) && !empty($temp[11])) {
                $product->filters = $this->parseProductFilters($temp[10], $temp[11]);
            }

            $products[] = $product;

        }

        if(!empty($products)) {
            foreach($products as $product) {
                $this->saveProduct($product);
            }
        }

        return true;
    }

    private function parseProductFilters($grFilterStr, $enFilterStr) {
        $db = Database::getInstance();
        $tempGr = explode(",", $grFilterStr);
        $tempEn = explode(",", $enFilterStr);
        $filters = array();
        if(!empty($tempGr) && !empty($tempEn) && count($tempEn) == count($tempGr)) {
            for($i=0; $i<count($tempEn); $i++) {
                $ar = explode("=", $tempGr[$i]);
                $groupGrName = $ar[0];
                $filterGrValue = $ar[1];
                $ar = explode("=", $tempEn[$i]);
                $groupEnName = $ar[0];
                $filterEnValue = $ar[1];
                $query = "SELECT filter_group_id FROM ".$db->getPrefix()."filter_group_description WHERE `name`='".$db->escape($groupGrName)."'";
                $db->query($query);
                $obj = $db->loadObject();
                if($obj == NULL) {
                    continue;
                }

                $query = "SELECT filter_id FROM ".$db->getPrefix()."filter_description WHERE filter_group_id=$obj->filter_group_id AND language_id=2 AND `name`='".$db->escape($filterGrValue)."' ORDER BY filter_id ASC";
                $db->query($query);
                $result = $db->loadObject();
                $new = TRUE;
                if($result != NULL) {
                    $new = FALSE;
                    $id = $result->filter_id;
                }


                $filter = new Filter($filterGrValue, $filterEnValue, $obj->filter_group_id, $new);
                if($new == FALSE) {
                    $filter->id = $id;
                }
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    private function parseProductCategories($catString) {
        $catController = new CategoriesController();
        $xlsCategories = explode(",", $catString);
        $categories = array();
        if(!empty($xlsCategories)) {
            foreach($xlsCategories as $categoryName) {
                $result = $catController->searchCategory($categoryName);
                if(!empty($result) && $result != FALSE && $result != NULL) {
                    $categories[] = $result->category_id;
                }
            }
        }
        return $categories;
    }

    private function saveFilters($product) {
        $db = Database::getInstance();
        foreach($product->filters as $filter) {
            if($filter->new == TRUE) {
                //SAVE FILTER
                $query = "INSERT INTO ".$db->getPrefix()."filter (`filter_group_id`, `sort_order`) VALUES ($filter->filterGroup, 0)";
                $db->query($query);
                echo $db->getError();

                $filter->id = $db->getInsertedId();

                $query = "INSERT INTO ".$db->getPrefix()."filter_description (filter_id, `language_id`, filter_group_id, `name`) VALUES ($filter->id, 1, $filter->filterGroup, '".$db->escape($filter->enName)."')";
                $db->query($query);
                echo $db->getError();

                $query = "INSERT INTO ".$db->getPrefix()."filter_description (filter_id, `language_id`, filter_group_id, `name`) VALUES ($filter->id, 2, $filter->filterGroup, '".$db->escape($filter->name)."')";
                $db->query($query);
                echo $db->getError();

                //ADD FILTER TO ALL CATEGORIES
                foreach($product->categories as $category) {
                    $query = "INSERT INTO ".$db->getPrefix()."category_filter (category_id, filter_id) VALUES ($category, $filter->id)";
                    $db->query($query);
                    echo $db->getError();
                }
            } else {
                //CHECK IF ADDED TO CATEGORIES
                foreach($product->categories as $category) {
                    $query = "SELECT category_id FROM ".$db->getPrefix()."category_filter WHERE category_id=$category AND filter_id=$filter->id";
                    $db->query($query);
                    $result = $db->loadObject();
                    if($result == NULL) {
                        $query = "INSERT INTO ".$db->getPrefix()."category_filter (category_id, filter_id) VALUES ($category, $filter->id)";
                        $db->query($query);
                        echo $db->getError();
                    }
                }
            }
            //ADD FILTER TO PRODUCT
            $query = "INSERT INTO ".$db->getPrefix()."product_filter (product_id, filter_id) VALUES ($product->id, $filter->id)";
            $db->query($query);
            echo $db->getError();

        }

    }


    private function saveProduct($product) {
        $db = Database::getInstance();
        $query = "INSERT INTO ".$db->getPrefix()."product (model, quantity, stock_status_id, manufacturer_id,
              shipping, price, date_available, weight, weight_class_id, length_class_id, subtract, minimum,
              sort_order, status";
        if(!is_null($product->image) && !empty($product->image)) {
            $query .= ", image";
        }
        $query .= ") VALUES ('".$db->escape($product->code)."', $product->quantity,
              6, 0, 1, $product->price, NOW(), $product->weight, 1, 1, 1, 1, 0, 1";

        if(!is_null($product->image) && !empty($product->image)) {
            $temp = $this->clean($product->image);
            $query .= ", '".mysql_real_escape_string("catalog/products/".$temp)."'";
        }

        $query .= ")";
        $db->query($query);
        echo $db->getError();

        $product->id = $db->getInsertedId();

        $query = "INSERT INTO ".$db->getPrefix()."product_description (product_id, language_id, `name`, description) VALUES (
                  $product->id, 2,
                  '".$db->escape($product->name)."',
                  '".$db->escape($product->description)."')";
        $db->query($query);
        echo $db->getError();

        $query = "INSERT INTO ".$db->getPrefix()."product_description (product_id, language_id, `name`, description) VALUES (
                  $product->id, 1,
                  '".$db->escape($product->enName)."',
                  '".$db->escape($product->enDescription)."')";
        $db->query($query);
        echo $db->getError();

        foreach($product->categories as $category) {
            $query = "INSERT INTO ".$db->getPrefix()."product_to_category (product_id, category_id) VALUES ($product->id, $category)";
            $db->query($query);
            echo $db->getError();
        }

        $query = "INSERT INTO ".$db->getPrefix()."product_to_layout (product_id, store_id, layout_id) VALUES ($product->id, 0, 0)";
        $db->query($query);
        echo $db->getError();

        $query = "INSERT INTO ".$db->getPrefix()."product_to_store (product_id, store_id) VALUES ($product->id, 0)";
        $db->query($query);
        echo $db->getError();

        $seo = str_replace(" ", "_", strtolower($product->enName));
        $query = "INSERT INTO ".$db->getPrefix()."url_alias (`query`, `keyword`) VALUES ('product_id=$product->id', '".$db->escape($seo)."')";
        $db->query($query);
        echo $db->getError();

        $this->saveFilters($product);
    }


}