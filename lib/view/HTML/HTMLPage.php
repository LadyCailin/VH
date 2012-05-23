<?php 
include_once(dirname(__FILE__)."/HTMLView.php");
class HTMLPage extends HTMLView {

    private $body;
    private $headerScripts = "";

    public function __construct($body = null) {
        if ($body === null) {
            $body = new HTMLBody(null); //They're probably going to add to us later
        } else if ($body instanceof HTMLView) {
            if (!($body instanceof HTMLBody)) {
                $body = new HTMLBody($body);
            }
        } else {
            $body = new HTMLText($body);
        }
        $this->body = $body;
    }

    /**
     * You can use HTMLPage like HTMLBody for the most part, but if you need specific access to the body,
     * you can get to it with this method. 
     */
    public function getBody() {
        return $this->body;
    }

    public function appendContent($view) {
        if (!($view instanceof HTMLView)) {
            $view = new HTMLText($view);
        }
        $this->body->appendContent($view);
        return $this;
    }

    /**
     * Adds a script that will be inlined into the <head> tag.
     * @param type $script 
     */
    public function addHeaderScript($script) {
        $this->headerScripts .= $script . "\n";
        return $this;
    }

    final public function render() {
        $body = $this->body;
        $this->extract($body);
        $render = "";
        $render .= $this->getDoctype();
        $htmlAttributes = $this->getHTMLAttributes();
        $render .= "<html";
        if ($htmlAttributes !== null) {
            $render .= " " . HTMLContainer::SRenderAttributes($htmlAttributes);
        }
        $render .= ">";
        $headAttributes = $this->getHeadAttributes();
        $render .= "<head";
        if ($headAttributes !== null) {
            $render .= " " . HTMLContainer::SRenderAttributes($headAttributes);
        }
        $render .= ">";

        foreach ($this->getMetaTags() as $metaTag) {
            $render .= $metaTag->render();
        }

        foreach ($this->getExternalCSS() as $location) {
            $render .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$location\" />";
        }

        $renderedCSS = trim(CSSSelectors::render());
        $customCSS = trim($this->getRawCSS());

        if ($renderedCSS != "" || $customCSS != "") {
            $css = new HTMLCSSStyle($renderedCSS . "\n" . $customCSS);
            $render .= $css->render();
        }

        foreach ($this->getExternalScripts() as $script) {
            $s = new HTMLJavaScript();
            $s->setExternal($script);
            $render .= $s->render();
        }
        if (trim($this->headerScripts) != "") {
            $s = new HTMLJavaScript();
            $s->setInline($this->headerScripts);
            $render .= $s->render();
        }

        $render .= "<title";
        if ($this->getTitleAttributes() !== null) {
            $render .= " " . $this->getTitleAttributes();
        }
        $render .= ">" . $this->getTitle() . "</title>";
        $render .= "</head>";
        $render .= $body->render();
        $render .= "</html>";
        return $render;
    }

    protected function getHTMLAttributes() {
        return array("xmlns" => "http://www.w3.org/1999/xhtml", "xml:lang" => "en-us");
    }

    protected function getHeadAttributes() {
        return null;
    }

    protected function getTitleAttributes() {
        return null;
    }

    protected function getDoctype() {
        //$doctype = new HTMLDocType("html", "PUBLIC", "-//W3C//DTD XHTML 1.1//EN", "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd");
        //return $doctype->render();
        //For efficiency sake, we can just return the string, however, highly dynamic subclasses may want to use the above syntax.
        return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
    }

}
?>
