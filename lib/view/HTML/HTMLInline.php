<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
/**
 * An HTMLInline object may only contain other inline objects, it
 * cannot contain any block level children. 
 */
class HTMLInline extends HTMLComposite {

    public function __construct($contents) {
        parent::__construct($contents);
    }

    protected function getCompositeTagName() {
        return "span";
    }
    
    protected function addView(HTMLView $view) {
        if($view instanceof HTMLBlock){
            trigger_error("Cannot add Block level element to Inline level element", E_USER_WARNING);
        }
        parent::addView($view);
    }

}
?>
