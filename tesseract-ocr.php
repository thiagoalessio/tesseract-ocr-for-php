<?php
class TesseractOCR {

  function recognize($originalImage) {
    $tifImage   = TesseractOCR::convertImageToTif($originalImage);
    $outputFile = sys_get_temp_dir().'/tesseract-ocr-output-'.rand();
    exec("tesseract $tifImage $outputFile 2> /dev/null");
    $outputFile.= ".txt"; // tesseract always append a .txt extension
    $recognizedText = TesseractOCR::readOutputFile($outputFile);
    TesseractOCR::removeTempFiles($tifImage, $outputFile);
    return $recognizedText;
  }

  function convertImageToTif($originalImage) {
    $tifImage = sys_get_temp_dir().'/tesseract-ocr-tif-'.rand().'.tif';
    exec("convert -colorspace gray +matte $originalImage $tifImage");
    return $tifImage;
  }

  function readOutputFile($outputFile) {
    return trim(file_get_contents($outputFile));
  }

  function removeTempFiles() { array_map("unlink", func_get_args()); }
}
?>
