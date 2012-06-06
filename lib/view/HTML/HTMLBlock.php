<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");

/**
 * An HTMLBlock element is a block level tag, which can contain more
 * child views, either block level, or inline. 
 */
abstract class HTMLBlock extends HTMLComposite {

    public function __construct($contents = null) {
	if($contents != null){
		parent::__construct(func_get_args());	
	} else {
		parent::__construct(array());
	}
    }
    
    protected function addView($view) {
	    return parent::addAnyView($view);
    }

}

class HTMLDiv extends HTMLBlock{
	public function addView($view) {
		return parent::addView($view);
	}

	protected function getCompositeTagName() {
		return "div";
	}
}
?>
