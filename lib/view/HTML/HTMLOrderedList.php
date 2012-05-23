<?php 
include_once(dirname(__FILE__)."/HTMLList.php");
class HTMLOrderedList extends HTMLList{    
    protected function getCompositeTagName() {
        return "ol";
    }
}
?>
