<?php

/**
 * Description of HTMLProgressBar
 *
 * @author lsmith
 */
class HTMLProgressBar {
	/**
	 * All of the options are passed straight through to the jQuery widget.
	 * @param array $options
	 * @param type $id 
	 */
	public function __construct(array $options = array(), $id = null){
		
	}
	
	public function appendAttribute($name, $content) {
		if($name == "id" && !$this->disableIDCheck){
			trigger_error("Cannot set the id except using the constructor for HTMLAccordion.");
			return;
		}
		parent::appendAttribute($name, $content);
	}
}

?>
