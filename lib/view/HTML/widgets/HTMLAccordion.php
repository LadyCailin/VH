<?php
include_once(dirname(__FILE__)."/HTMLAbstractWidget.php");
/**
 * Wraps a jQuery UI accordion widget. The most important methods in this class
 * are the constructor, which takes an array of options, and (optionally) the id
 * of the component, and addSection, which adds a new accordion fold. The content
 * may be a more complex view if need be, but the title must be an inline level
 * element.
 *
 * @author lsmith
 */
class HTMLAccordion extends HTMLAbstractWidget{			

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
		parent::__construct($id, count($options) != 0);
		$this->addClass("--intercept");
		$this->addClass("--accordion");
		if(($id = $this->getId()) != null){			
			$this->addInlineScript("VC.addComponentMeta('$id', " . json_encode($options) . ");");
		}		
	}
	
	public function addSection($title, $contents){
		if($title instanceof HTMLView && !($title instanceof HTMLInline)){
			trigger_error("Only inline elements can be added as the title in addSection", E_USER_WARNING);
		}
		$this->addView(new HTMLH3(new HTMLA($title, "#")));
		if($contents instanceof HTMLBlock){
			$this->addView($contents);
		} else {
			$this->addView(new HTMLDiv($contents));
		}
	}			
}

?>
