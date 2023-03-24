<?php
include "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
$result='';
// Check if file was uploaded without errors
if(isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == 0){
  $sheetId=$_POST['sheetId'];
  // Get file name, size and type
  $fileName = $_FILES["fileToUpload"]["name"];
  $fileSize = $_FILES["fileToUpload"]["size"];
  $fileType = $_FILES["fileToUpload"]["type"];

  // Specify the allowed file types and size limit (1MB)
  $allowedTypes = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

  $maxSize = 1024 * 1024;

  // Validate file type and size
  if(in_array($fileType, $allowedTypes) && $fileSize <= $maxSize){
    // Choose a destination folder for the uploaded file
        $inputFileName = $_FILES["fileToUpload"]["tmp_name"];
        $spreadsheet = IOFactory::load($inputFileName);
        $spreadsheet=$spreadsheet->getSheet($sheetId);
        $arrJson=[];
        $highestRow = $spreadsheet->getHighestRow(); // e.g. 10
        $highestColumn = $spreadsheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $data = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            $riga = array();
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $riga[] = $spreadsheet->getCellByColumnAndRow($col, $row)->getValue();
            }
            if (1 === $row) {
                // Header row. Save it in "$keys".
                $keys = $riga;
                continue;
            }
            $data[] = array_combine($keys, $riga);
        }
      $result=['info'=>1,"data"=>$data,"message"=>"upload success"];
    
  } else{
    $result=['info'=>0,"message"=>"Invalid file type or size"];
  }
} else{
  $result=['info'=>0,"message"=>"No file uploaded or error occurred"];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
?>