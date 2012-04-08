<?php
class TesseractOCR {

  function recognize($originalImage) {
    $tifImage       = TesseractOCR::convertImageToTif($originalImage);
    $configFile     = TesseractOCR::generateConfigFile(func_get_args());
    $outputFile     = TesseractOCR::executeTesseract($tifImage, $configFile);
    $recognizedText = TesseractOCR::readOutputFile($outputFile);
    TesseractOCR::removeTempFiles($tifImage, $outputFile, $configFile);
    return $recognizedText;
  }

  function convertImageToTif($originalImage) {
    $tifImage = sys_get_temp_dir().'/tesseract-ocr-tif-'.rand().'.tif';
    exec("convert -colorspace gray +matte $originalImage $tifImage");
    return $tifImage;
  }

  function generateConfigFile($arguments) {
    $configFile = sys_get_temp_dir().'/tesseract-ocr-config-'.rand().'.conf';
    exec("touch $configFile");
    $whitelist = TesseractOCR::generateWhitelist($arguments);
    if(!empty($whitelist)) {
      $fp = fopen($configFile, 'w');
      fwrite($fp, "tessedit_char_whitelist $whitelist");
      fclose($fp);
    }
    return $configFile;
  }

  function generateWhitelist($arguments) {
    array_shift($arguments); //first element is the image path
    $whitelist = '';
    foreach($arguments as $chars) $whitelist.= join('', (array)$chars);
    return $whitelist;
  }

  function executeTesseract($tifImage, $configFile) {
    $outputFile = sys_get_temp_dir().'/tesseract-ocr-output-'.rand();
    exec("tesseract $tifImage $outputFile nobatch $configFile 2> /dev/null");
    return $outputFile.'.txt'; //tesseract appends txt extension to output file
  }

  function readOutputFile($outputFile) {
    return trim(file_get_contents($outputFile));
  }

  function removeTempFiles() { array_map("unlink", func_get_args()); }
}
?>
