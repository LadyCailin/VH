<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
class HTMLListItem extends HTMLComposite{    
    protected function getCompositeTagName() {
        return "li";
    }
}
?>
