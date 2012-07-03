<?php 
include_once(dirname(__FILE__)."/HTMLBlock.php");
abstract class HTMLList extends HTMLBlock{
    public function __construct(array $list){
        foreach($list as $li){
	    if($li instanceof HTMLList){
		//TODO: If the list already has a list style on it, we don't
		//need to add our own list-style.
		$new = new HTMLListItem($li);		
		$new->addStyle("list-style", "none");
		$li = $new;
	    }
            if(!($li instanceof HTMLListItem)){
                $li = new HTMLListItem($li);
            }
            $this->addView($li);
        }
    }
}
?>
