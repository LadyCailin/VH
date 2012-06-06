<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
/**
 * An HTMLInline object may only contain other inline objects, it
 * cannot contain any block level children. 
 */
abstract class HTMLInline extends HTMLComposite {

    public function __construct($contents) {
        parent::__construct($contents);
    }

    
    protected function addView(HTMLView $view) {
        if($view instanceof HTMLBlock){
            trigger_error("Cannot add Block level element to Inline level element", E_USER_WARNING);
        }
        parent::addView($view);
    }
    
    protected function addAnyView($view){
        if ($view instanceof HTMLView) {
            self::addView($view);
        } else {
            self::addView(new HTMLText($view));
        }
    }

}

class HTMLSpan extends HTMLInline{	
    protected function getCompositeTagName() {
        return "span";
    }
    public function addView($view) {
	parent::addAnyView($view);
    }
}
?>
