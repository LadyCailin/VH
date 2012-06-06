<?php

/**
 * Wraps a jQuery UI accordion widget. The most important methods in this class
 * are the constructor, which takes an array of options, and (optionally) the id
 * of the component, and addSection, which adds a new accordion fold. The content
 * may be a more complex view if need be, but the title must be an inline level
 * element.
 *
 * @author lsmith
 */
class HTMLAccoridion extends HTMLComposite{			
	private $disableIDCheck = false;
	/**
	 * Creates a new Accordion object, based on jQuery's accordion UI widget.
	 * $id is the id that will be associated with this component, using setId will
	 * not work here, because the widget must have an id if options are provided. If one is not provided, 
	 * a random one will be generated and used if options are also provided.
	 * $options is an associative array of options that will be sent to the
	 * jQuery initialization mechanism. See the options listed at http://jqueryui.com/demos/accordion/
	 * for full details.
	 * @param type $options 
	 */
	public function __construct(array $options = array(), $id = null){
		parent::__construct();
		$this->addClass("--intercept");
		$this->addClass("--accordion");
		if(count($options) != 0){
			if($id == null){
				$id = HTMLContainer::getRandomId();
			}
			$this->addInlineScript("VC.addComponentMeta('$id', " . json_encode($options) . ");");
		}
		$this->disableIDCheck = true;
		$this->setId($id);
		$this->disableIDCheck = false;
	}
	
	public function addSection($title, $contents){
		if(!($title instanceof HTMLInline)){
			
		}
		$this->addView(new HTMLH3(new HTMLA($title, "#")));
		if($contents instanceof HTMLBlock){
			$this->addView($contents);
		} else {
			$this->addView(new HTMLDiv($contents));
		}
	}
	
	protected function getCompositeTagName() {
		return "div";
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
