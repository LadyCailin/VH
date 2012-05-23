<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");

/**
 * An HTMLBlock element is a block level tag, which can contain more
 * child views, either block level, or inline. 
 */
class HTMLBlock extends HTMLComposite {

    public function __construct($contents) {
        parent::__construct(func_get_args());
    }

    protected function getCompositeTagName() {
        return "div";
    }

}
?>
