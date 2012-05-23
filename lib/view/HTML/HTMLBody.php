<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
/**
 * The top level HTMLComposite element 
 */
class HTMLBody extends HTMLComposite {

    public function __construct($content) {
        parent::__construct($content);
        $this->selfClosed = false;
    }

    public function appendContent(HTMLView $view) {
        $this->addView($view);
        return $this;
    }

    protected function getContent() {
        $content = parent::getContent();
        $inlineScripts = $this->getInlineScripts();
        if (trim($inlineScripts) != "") {
            //TODO: The javascript can be minified at this point, if desired.
            $content .=
                    '<script type="text/javascript"><!--//--><![CDATA[//><!--' . "\n"
                    . $inlineScripts
                    . "\n//--><]]></script>";
        }
        return new HTMLBlock($content);
    }

    protected function getCompositeTagName() {
        return "body";
    }

}
?>
