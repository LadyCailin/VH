<?php 
include_once(dirname(__FILE__)."/HTMLList.php");
class HTMLUnorderedlist extends HTMLList{    
    protected function getCompositeTagName() {
        return "ul";
    }
}
?>
