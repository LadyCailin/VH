<?php 
include_once(dirname(__FILE__)."/HTMLView.php");
/**
 * Classes that extend HTMLContainer will by default
 * add non-HTMLView content as a HTMLText view. If this
 * is not desired, this class may be used to wrap
 * the content. Typically, you should not use
 * this class directly however, you should decide
 * why you need to treat this content as a raw HTML,
 * and make a class for it. 
 */
class HTMLRaw extends HTMLView {

    private $content;

    public function __construct($content) {
        $this->content = $content;
    }

    public function render() {
        return $this->content;
    }

}
?>
