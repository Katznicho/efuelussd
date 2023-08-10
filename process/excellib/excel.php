<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

include ('Classes/PHPExcel/IOFactory.php');

// $inputFileName = 'example1.xls';

class excel{

    public function getData($inputFileName)
    {
    $objReader = new PHPExcel_Reader_Excel5();
    if(empty($inputFileName))
    {
    return null;

    }
    $objPHPExcel = $objReader->load($inputFileName);
    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

    return $sheetData;
    }


}


$inputFileName = 'example1.xls';
$excel= new excel();
$data=$excel->getData($inputFileName);
print_r($data);

?>











