<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 22/5/2015
 * Time: 6:43 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

class CategoriesController extends ImportController {

    private $categories;
    private $xlsCategories;

    public function __construct() {}

    public function searchCategory($name) {
        $db = Database::getInstance();
        $query = "SELECT category_id FROM ".$db->getPrefix()."category_description WHERE `name`='".$db->escape($name)."'";

        $db->query($query);
        $category = $db->loadObject();
        return $category;
    }



    private function getLastId() {
        $db = Database::getInstance();
        $query = "SELECT MAX(category_id) as maxid FROM ".$db->getPrefix()."category";
        $db->query($query);
        $obj = $db->loadObject();
        if($obj == FALSE || $obj == NULL || empty($obj)) {
            return 0;
        }
        return $obj->maxid;
    }

    public function parse() {
        $data = $this->getData();
        $this->categories = array();
        if(empty($data)) {
            parent::$error = 'The file you uploaded is empty';
            return false;
        }
        $maxId = $this->getLastId();
        foreach($data as $key => $xlsCategory) {
            if(count($xlsCategory[0]) < 6 ) {
                parent::$error = 'Found false information in row='.$key;
                return false;
            }
            $temp = $xlsCategory[0];
            $category = new Category(($key+1+$maxId), $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5]);

            if(count($xlsCategory[0]) == 8 && !empty($temp[6]) && !empty($temp[7])) {
                $grFilterGroups = explode(",", $temp[6]);
                $enFilterGroups = explode(",", $temp[7]);
                if(count($grFilterGroups) == count($enFilterGroups) ) {
                    $filterGroups = array();
                    for($i = 0; $i<count($grFilterGroups); $i++) {
                        $filterGroups[] = new FilterGroup($grFilterGroups[$i], $enFilterGroups[$i]);
                    }
                    $category->filterGroups = $filterGroups;
                }
            }

            $this->xlsCategories[] = $category;

        }

        $this->getOldCategories();
        $this->generateParents();
        $this->getNewCategories();
        $this->generatePaths();

        $this->deleteOldPaths();

        foreach($this->categories as $category) {
            if($category->new == false) {
                $this->saveOldCategory($category);
            } else {
                $this->saveNewCategory($category);
            }
        }

        return true;
    }

    private function deleteOldPaths() {
        $database = Database::getInstance();
        $query = "DELETE FROM ".$database->getPrefix()."category_path";
        $database->query($query);
        echo $database->getError();
    }

    private function getNewCategories() {
        foreach($this->xlsCategories as $category) {
            $this->categories[$category->id] = $category;
            $this->categories[$category->id]->new = true;
        }
    }

    private function getOldCategories() {
        $db = Database::getInstance();
        $query = "SELECT category_id, parent_id, image FROM ".$db->getPrefix()."category";
        $db->query($query);
        $oldCategories = $db->loadObjectList();
        if(!empty($oldCategories)) {
            foreach($oldCategories as $category) {
                $query = "SELECT `name`, `description` FROM ".$db->getPrefix()."category_description WHERE category_id=$category->category_id AND language_id=2";
                $db->query($query);
                $tmp = $db->loadObject();
                $category->name = '';
                if(!empty($tmp) && $tmp != NULL && $tmp != FALSE) {
                    $category->name = $tmp->name;
                    $category->description = $tmp->description;
                }
                $query = "SELECT `name`, `description` FROM ".$db->getPrefix()."category_description WHERE category_id=$category->category_id AND language_id=1";
                $db->query($query);
                $tmp = $db->loadObject();
                $category->enName = '';
                if(!empty($tmp) && $tmp != NULL && $tmp != FALSE) {
                    $category->enName = $tmp->name;
                    $category->enDescription = $tmp->description;
                }
            }
        }

        $this->categories = array();
        foreach($oldCategories as $category) {
            $tmp = new Category($category->category_id, $category->name, $category->description
                , $category->enName, $category->enDescription, $category->image, $category->parent_id);
            $this->categories[$category->category_id] = $tmp;
        }
    }

    private function generateParents() {
        foreach($this->xlsCategories as $category) {
            if(!empty($category->parent) && $category->parent != null) {
                $found = false;
                foreach($this->categories as $scanCategory) {
                    if($scanCategory->name == $category->parent || $scanCategory->enName == $category->parent) {
                        $category->parent = $scanCategory->id;
                        $found = true;
                        break;
                    }
                }

                if($found == false) {
                    foreach($this->xlsCategories as $scanCategory) {
                        if($scanCategory->name == $category->parent || $scanCategory->enName == $category->parent) {
                            $category->parent = $scanCategory->id;
                            break;
                        }
                    }
                }
            } else {
                $category->parent = 0;
                $this->categories[$category->id]->parent = 0;
            }
        }
    }

    private function generatePaths() {

        foreach($this->categories as $category) {
            $category->path = array();
            $category->path[0] = new CategoryPath($category->id, $category->id);

            $tmp = $category;

            while(intval($tmp->parent) != 0) {
                $obj = new CategoryPath($category->id, $tmp->parent);
                $category->path[] = $obj;
                $tmp = $this->categories[$tmp->parent];
            }

            $category->path[0]->level = count($category->path);
            if(count($category->path) > 1) {
                for($i=0; $i<count($category->path); $i++) {
                    $category->path[$i]->level = count($category->path)-($i+1);
                }
            }
        }
    }

    private function saveOldCategory($category) {
        $database = Database::getInstance();
        if(!empty($category->path)) {
            foreach($category->path as $path) {
                $query = "INSERT INTO ".$database->getPrefix()."category_path (category_id, path_id, level) VALUES
            ($path->category_id, ".$path->path_id.", ".$path->level.")";
                $database->query($query);
                echo $database->getError();
            }
        }
    }

    private function saveNewCategory($category) {
        $database = Database::getInstance();
        $query = "INSERT INTO ".$database->getPrefix()."category (category_id, ";
        if($category->image != NULL) {
            $query .= "image, ";
        }
        $query .= "parent_id, sort_order, status, `top`, `column`)
              VALUES ($category->id, ";
        if($category->image != NULL) {
            $temp = clean($category->image);
            $query .= " '".$database->escape("catalog/categories/".$temp)."',";
        }
        $query .= " $category->parent, 0, 1, 1, 1)";
        $database->query($query);
        echo $database->getError();

        $query = "INSERT INTO ".$database->getPrefix()."category_to_store (category_id, store_id) VALUES ($category->id, 0)";
        $database->query($query);
        echo $database->getError();

        $query = "INSERT INTO ".$database->getPrefix()."category_to_layout (category_id, store_id, layout_id) VALUES ($category->id, 0, 0)";
        $database->query($query);
        echo $database->getError();

        if(!empty($category->path)) {
            foreach($category->path as $path) {
                $query = "INSERT INTO ".$database->getPrefix()."category_path (category_id, path_id, level) VALUES
            ($path->category_id, ".$path->path_id.", ".$path->level.")";
                $database->query($query);
                echo $database->getError();
            }
        }

        $query = "INSERT INTO ".$database->getPrefix()."category_description (category_id, language_id, `name`, `description`, meta_title, meta_description)
          VALUES ($category->id, 2, '".$database->escape($category->name)."', '".$database->escape($category->description)."', '".$database->escape($category->name)."', '".$database->escape($category->name)."')";
        $database->query($query);
        echo $database->getError();

        $query = "INSERT INTO ".$db->getPrefix()."category_description (category_id, language_id, `name`, `description`, meta_title, meta_description)
          VALUES ($category->id, 1, '".$database->escape($category->enName)."', '".$database->escape($category->enDescription)."', '".$database->escape($category->enName)."', '".$database->escape($category->enDescription)."')";
        $database->query($query);
        echo $database->getError();


        $seo = str_replace(" ", "_", strtolower($category->enName));
        $query = "INSERT INTO ".$database->getPrefix()."url_alias (`query`, `keyword`) VALUES ('category_id=$category->id', '".$database->escape($seo)."')";
        $database->query($query);
        echo $database->getError();

        if(!empty($category->filterGroups)) {
            foreach($category->filterGroups as $group) {
                $query = "SELECT filter_group_id FROM ".$database->getPrefix()."filter_group_description WHERE `name`='".$database->escape($group->name)."'";
                $database->query($query);
                $obj = $database->loadObject();
                if($obj != NULL) {
                    $group->id = $obj->filter_group_id;
                } else {
                    $query = "INSERT INTO ".$database->getPrefix()."filter_group (sort_order) VALUES (0)";
                    $database->query($query);
                    $group->id = $database->getInsertedId();

                    $query = "INSERT INTO ".$database->getPrefix()."filter_group_description (filter_group_id, language_id, `name`) VALUES ($group->id, 2, '".$database->escape($group->name)."')";
                    $database->query($query);
                    echo $database->getError();

                    $query = "INSERT INTO ".$database->getPrefix()."filter_group_description (filter_group_id, language_id, `name`) VALUES ($group->id, 1, '".$database->escape($group->enName)."')";
                    $database->query($query);
                    echo $database->getError();
                }

            }
        }
    }
}