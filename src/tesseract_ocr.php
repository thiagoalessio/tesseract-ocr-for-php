<?php
class TesseractOCR
{
  public static function recognize($originalImage) {
    $tifImage       = TesseractOCR::convertImageToTif($originalImage);
    $configFile     = TesseractOCR::generateConfigFile(func_get_args());
    $outputFile     = TesseractOCR::executeTesseract($tifImage, $configFile);
    $recognizedText = TesseractOCR::readOutputFile($outputFile);
    TesseractOCR::removeTempFiles($tifImage, $outputFile, $configFile);
    return $recognizedText;
  }

  protected static function convertImageToTif($originalImage) {
    $tifImage = sys_get_temp_dir().DIRECTORY_SEPARATOR.rand().'.tif';
    exec("convert -colorspace gray +matte $originalImage $tifImage");
    return $tifImage;
  }

  protected static function generateConfigFile($arguments) {
    $configFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.rand().'.conf';
    exec("touch $configFile");
    $whitelist = TesseractOCR::generateWhitelist($arguments);
    if(!empty($whitelist)) {
      file_put_contents($configFile, "tessedit_char_whitelist $whitelist");
    }
    return $configFile;
  }

  protected static function generateWhitelist($arguments) {
    array_shift($arguments); //first element is the image path
    $whitelist = '';
    foreach($arguments as $chars) $whitelist.= join('', (array)$chars);
    return $whitelist;
  }

  protected static function executeTesseract($tifImage, $configFile) {
    $outputFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.rand();
    exec("tesseract $tifImage $outputFile nobatch $configFile");
    return $outputFile.'.txt'; //tesseract adds txt extension to output file
  }

  protected static function readOutputFile($outputFile) {
    return trim(file_get_contents($outputFile));
  }

  protected static function removeTempFiles() {
    array_map('unlink', func_get_args());
  }
}
?>
