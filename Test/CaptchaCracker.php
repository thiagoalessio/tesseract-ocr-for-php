<?php
 /**
  * crack the aptcha
  *
  * PHP version 5
  *
  * @category  PHP
  * @author    Yongtao Pang <pytonic@foxmail.com>
  */
class CaptchaCracker{

    /**
     * 4 array for white list 
     *
     * @var array
     */
    private $number;
    private $alpha;
    private $alphb;
    private $operator;

    // convert(ImageMagick's)
    private $compress;
    private $depth;
    private $alpha;
    private $colorspace;
    private $scale;

    public function __construct(){

        $this->number = range(0, 9);
        $this->alpha  = range('A', 'Z'); 
        $this->alphb  = range('a', 'z'); 
        $this->operator     = array('-', '+', '=', '?');


	$this->compress = "none";
	$this->depth = 8;
	$this->alpha = "off";
	$this->colorspace = "gray";
	$this->scale = "180%";
    }

    /**
     * convert image to tif, do image gray process 
     *
     * return 
     */
    public function convertToTif($filename){

        $ret = 0;
        $output = array();
        $tif_filename = $filename . ".tif";

	//if convert cmd is not found, the cmd below Just not work. never mind.
        $cmd = "convert -compress $this->compress -depth $this->depth -alpha $this->alpha -colorspace $this->colorspace -scale $this->scale $filename $tif_filename";
        $lastline = exec($cmd, $output, $ret);
        if ($ret) {
            return $filename;
        }
        return $tif_filename;

    }


    /**
     * process the result of recognize
     * this function can deal with(plain text, add and subtract ) aptcha
     *
     * @return string 
     */
    public function genResult($recognize, $operator){
	if(empty($recognize)){
	    return ;
	}
        /*
           $recognize = "1234";
           $recognize = "ewrt";
           $recognize = "12-4=?";
           $recognize = "12-4=";
           $recognize = "12-4= ";
           $recognize = "12+4=?";
           $recognize = "12 4=?";
           $recognize = "12-4";
           $recognize = "12+4";
         */

        $recognize = trim($recognize);
        foreach ($operator as $one) {
            if (false !== strpos($recognize, $one)) {
                // 
                list($left, ) = explode("?", $recognize);
                list($left, ) = explode("=", $left);
                $left = trim($left);

                if (false !== strpos($left, "-")) {
                    $ret = explode("-", $left);
                    $left  = trim($ret[0]);
                    $right = trim($ret[1]);
                    $result = $left - $right;

                }elseif ( false !== strpos($left, "+")) {
                    $ret  = explode("+", $left);
                    $left  = trim($ret[0]);
                    $right = trim($ret[1]);
                    $result = $left + $right;
/*
                }elseif ( false !== strpos($left, "*")) {
                    $ret  = explode("*", $left);
                    $left  = trim($ret[0]);
                    $right = trim($ret[1]);
                    $result = $left * $right;
*/

                }else{
                    if (false !== strpos($left, " ")) {
                        list($left, $right) = explode(" ", $left);
                        $left = trim($left);
                        $right = trim($right);
                        // I guess it is minus... just guess
                        $result = $left - $right;
                    }else{
                        $result = $recognize;
                    }

                }

                return $result;

            }
        }

        //没有特殊字符，如果有空格则算作减法，否则就当做文字验证码
        //there is no operator in the recognize result; if space exists, i guess it is minus(be recognized as space)，other will be treat as text aptcha
        $result = $recognize;
        if ($recognize && false !== strpos(" ", $recognize)) {
            list($left, $right) = explode(" ", $recognize);
            $result = $left - $right;
        }
        return $result;

    }



    /**
     * do recognize 
     *
     * @return string 
     */
    public function getRecognizeResult($filename){

        require_once dirname(__FILE__).'/../TesseractOCR/TesseractOCR.php';

        $filename = $this->convertToTif($filename);
        $tesseract = new TesseractOCR($filename);

        $whitelist = array_merge($this->number, $this->alpha, $this->alphb, $this->operator);
        $tesseract->setWhitelist($whitelist);

        $tesseract->setPsm(6);

        $e = $tesseract->recognize();

        return $this->genResult($e, $this->operator);

    }

}

/*
$cls = new CaptchaCracker();
$filename = dirname(__FILE__)."/images/yanzhengma-53.jpg";
$result = $cls->getRecognizeResult($filename);
echo $result;
*/
