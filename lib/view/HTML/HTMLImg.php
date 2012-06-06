<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
class HTMLImg extends HTMLInline {

    public function __construct($src, $alt, $width = null, $height = null) {
        if (!isset($alt)) {
            trigger_error("No alt text provided for the image at $src", E_USER_WARNING);
        }
        //Self closed, so nothing should be put in the contents
        $this->setAttribute("src", $src);
        $this->setAttribute("alt", $alt);
        if ($width !== null)
            $this->setAttribute("width", $width);
        if ($height !== null)
            $this->setAttribute("height", $height);
    }

    protected function getCompositeTagName() {
        return "img";
    }
    
    public function getAltText(){
        return $this->getAttribute("alt");
    }
    
    public function setAltText($text){
        $this->setAttribute("alt", $text);
        return $this;
    }

}
?>
