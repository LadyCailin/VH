<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
abstract class HTMLList extends HTMLComposite{
    public function __construct(array $list){
        foreach($list as $li){
            if(!($li instanceof HTMLListItem)){
                $li = new HTMLListItem($li);
            }
            $this->addView($li);
        }
    }
}
?>
