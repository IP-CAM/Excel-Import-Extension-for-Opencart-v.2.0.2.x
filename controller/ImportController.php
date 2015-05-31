<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 22/5/2015
 * Time: 2:06 μμ
 */

require_once('library/Authentication.php');
Authentication::authenticate() or die('Access Forbidden');

require_once('library/PHPExcel/Classes/PHPExcel/IOFactory.php');

class ImportController {

    protected static $error;
    protected static $file;
    private $data;

    public function __construct() {}

    private function validateType($fileName) {
        $temp = explode(".", $fileName);

        $allowedExts = array("xls", "xlsx");

        $extension = end($temp);

        if( in_array($extension, $allowedExts)) {
            return true;
        } else {
            return false;
        }

    }

    private function uploadFile($fileName, $tmpFileName) {
        self::$file = 'temp/'.$fileName;
        $check = move_uploaded_file($tmpFileName, self::$file);
        if (!$check) {
            self::$error = "The file ". basename( $fileName). " can't be uploaded.";
            return false;
        }
        return true;
    }


    private function readFile() {
        //  Read your Excel workbook
        try {
            $inputFileType = PHPExcel_IOFactory::identify(self::$file);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load(self::$file);
        } catch(Exception $e) {
            self::$error = 'Error loading file "'.pathinfo(self::$file,PATHINFO_BASENAME).'": '.$e->getMessage();
            return false;
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $this->data = array();

        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++){
            //  Read a row of data into an array
            $this->data[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

        }
        return true;
    }


    public function validateFile($file) {
        $check = $this->validateType($file["name"]);
        if($check == FALSE) {
            self::$error = "Not supported file format!";
            return $check;
        }
        $check = $this->uploadFile($file["name"], $file["tmp_name"]);
        if($check == FALSE) {
            return $check;
        }

        $check = $this->readFile();
        if($check == FALSE) {
            return $check;
        }
    }

    public function clean($string) {
        $regex = <<<'END'
        /
          (
            (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
            |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
            |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
            |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
            ){1,100}                        # ...one or more times
          )
        | .                                 # anything else
        /x
END;

        $greek   = array('α','ά','Ά','Α','β','Β','γ', 'Γ', 'δ','Δ','ε','έ','Ε','Έ','ζ','Ζ','η','ή','Η','θ','Θ','ι','ί','ϊ','ΐ','Ι','Ί', 'κ','Κ','λ','Λ','μ','Μ','ν','Ν','ξ','Ξ','ο','ό','Ο','Ό','π','Π','ρ','Ρ','σ','ς', 'Σ','τ','Τ','υ','ύ','Υ','Ύ','φ','Φ','χ','Χ','ψ','Ψ','ω','ώ','Ω','Ώ',' ',"'","'",',');
        $english = array('a', 'a','A','A','b','B','g','G','d','D','e','e','E','E','z','Z','i','i','I','th','Th', 'i','i','i','i','I','I','k','K','l','L','m','M','n','N','x','X','o','o','O','O','p','P' ,'r','R','s','s','S','t','T','u','u','Y','Y','f','F','ch','Ch','ps','Ps','o','o','O','O','_','_','_','_');
        $ready  = str_replace($greek, $english, preg_replace($regex, '$1', $string));
        return $ready;
    }

    public function getError() {
        return self::$error;
    }

    public function getData() {
        return $this->data;
    }
}