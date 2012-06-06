<?php
include_once(dirname(__FILE__)."/../HTMLBlock.php");
/**
 * Most of the widgets need to manage the ID somehow, and this class is a common
 * class that the widgets can extend from, which manages the id for you. You must
 * send it the ID that was passed in from the user (which should default to null in
 * your constructor), and also set the variable $assignId, which should be set to
 * true if you're going to need to assign meta data to this widget. If that is
 * true, an id will be assigned, even if the user provided null for the id.
 * 
 * You may then use $this->getId(), and if the id was null, then your (possibly complex)
 * condition to see if you need to include meta data should also be false.
 *
 * @author lsmith
 */
abstract class HTMLAbstractWidget extends HTMLDiv{
	private $disableIDCheck = false;
	
	public function __construct($id, $assignId){
		if($assignId || $id != null){
			if($id == null){
				$id = HTMLContainer::getRandomId();
			}
			$this->disableIDCheck = true;
			$this->setId($id);
			$this->disableIDCheck = false;
		}
	}

	public function appendAttribute($name, $content) {
		if ($name == "id" && !$this->disableIDCheck) {
			trigger_error("Cannot set the id except using the constructor for HTML Widgets.");
			return;
		}
		parent::appendAttribute($name, $content);
	}

}

?>
