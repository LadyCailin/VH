<?php
include_once(dirname(__FILE__)."/HTMLContainer.php");
/**
 * This class represents an HTML Style tag. In most cases, it is more appropriate
 * to use HTMLCSSStyle instead. 
 */
class HTMLStyle extends HTMLContainer {

    public function appendAttribute($name, $content) {
        parent::appendAttribute($name, $content);
        return $this;
    }

    protected function getTagName() {
        return "style";
    }

}
?>
