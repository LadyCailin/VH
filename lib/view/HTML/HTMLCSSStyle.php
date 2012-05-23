<?php 
include_once(dirname(__FILE__)."/HTMLStyle.php");
class HTMLCSSStyle extends HTMLStyle {

    public function __construct($content) {
        parent::__construct($content);
        $this->setAttribute("type", "text/css");
    }

}
?>
