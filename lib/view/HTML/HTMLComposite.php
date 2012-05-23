<?php 
include_once(dirname(__FILE__)."/HTMLFlatComposite.php");
/** 
 * An HTML Composite class encapsulates several HTMLContainer views into one, and is the basis for more complex html structures.
 */
abstract class HTMLComposite extends HTMLFlatComposite {

    protected function getTagName() {
        return $this->getCompositeTagName();
    }

    protected abstract function getCompositeTagName();
}
?>
