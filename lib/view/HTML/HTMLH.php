<?php 
include_once(dirname(__FILE__)."/HTMLBlock.php");
class HTMLH1 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h1";
	}
}
class HTMLH2 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h2";
	}
}
class HTMLH3 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h3";
	}
}
class HTMLH4 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h4";
	}
}
class HTMLH5 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h5";
	}
}
class HTMLH6 extends HTMLBlock{
	
	protected function getCompositeTagName() {
		return "h6";
	}
}

?>
