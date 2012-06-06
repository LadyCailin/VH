<?php 
include_once(dirname(__FILE__)."/HTMLInline.php");
include_once(dirname(__FILE__)."/HTMLBlock.php");
/**
 * This file contains all the formatting type tags, which are usually each quite small. 
 */

class HTMLBr extends HTMLInline{
	
	/**
	 * This element is always empty, and so content can't be added to it. 
	 */
	public function __construct(){
		parent::__construct(null);
	}

	protected function getCompositeTagName() {
		return "br";
	}
		
}

class HTMLP extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "p";
	}
	
	public function addView($view) {
		parent::addView($view);
	}
}

class HTMLEm extends HTMLInline{
	
	protected function getCompositeTagName() {
		return "em";
	}
	public function addView($view) {
		parent::addView($view);
	}
}

class HTMLStrong extends HTMLInline{
	
	protected function getCompositeTagName() {
		return "strong";
	}
	public function addView($view) {
		parent::addView($view);
	}
}

class HTMLPre extends HTMLInline{	
	
	protected function getCompositeTagName() {
		return "pre";
	}
	public function addView($view) {
		if($view instanceof HTMLView && !($view instanceof HTMLText)){
			trigger_error("Only text data should be passed to a pre tag");
		}
		parent::addView($view);
	}
}