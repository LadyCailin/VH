<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
/** 
 * HTMLText will render as a span iff there are attributes that have been added to it, otherwise it will not
 * render as an html element. Regardless, it escapes special characters in the content itself, so if you
 * need to format inner parts of the text, this class is not useful, you should look into HTMLBBText instead.
 */
class HTMLText extends HTMLContainer {

    public function __construct($text) {
        $text = strval($text);
        $this->content = $text;
    }

    public function render() {
        $attributes = $this->renderAttributes();
        if ($attributes === null || count($attributes) === 0) {
            return $this->getContent();
        } else {
            return parent::render();
        }
    }

    protected function getContent() {
        return htmlentities($this->content);
    }

    /**
     * We are handling render specially, so this won't be used in all cases.
     * @return null 
     */
    protected function getTagName() {
        return "span";
    }

}
?>
