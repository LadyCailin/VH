<?php 
include_once(dirname(__FILE__)."/HTMLBlock.php");
abstract class HTMLList extends HTMLBlock{
    public function __construct(array $list){
        foreach($list as $li){
            if(!($li instanceof HTMLListItem) && !($li instanceof HTMLList)){
                $li = new HTMLListItem($li);
            }
            $this->addView($li);
        }
    }
}
?>
