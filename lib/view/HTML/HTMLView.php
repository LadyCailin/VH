<?php 
include_once(dirname(__FILE__)."/../interfaces/View.php");
/**
 * This is the root HTML based View. It knows about HTML specific things
 * like the <head> as well. 
 */
abstract class HTMLView implements View {

    private $externalScripts = null;
    private $internalScripts = null;
    private $externalCSS = null;
    private $metaTags = null;
    private $title = null;
    private $customCSS = null;

    /**
     * This appends fully custom css exactly as provided to the <style> tag in the head.
     * It is preferred to use addCSSBlock instead, which provides better granularity.
     * @param type $css 
     */
    public function appendRawCSS($css) {
        $this->customCSS = "\n" . $css;
        return $this;
    }

    public function getRawCSS() {
        return $this->customCSS;
    }

    /**
     * This adds a css selector block to the <style> tag in the <head> of the document. $name is the
     * css selector, i.e. #id, or .class. More complex selectors are also supported, the name is
     * put in as is. Multiple values at once are supported, i.e. "h1, h2".
     * @param type $name The name of the css selector
     * @param array $components An associative array of selector properties
     */
    public function addCSSBlock($name, array $components) {
        CSSSelectors::addSelector(null, $name, $components);
        return $this;
    }

    /**
     * Typically, CSS does not have inheritance. However, using this method will allow a selector to inherit
     * from another selector. Say you have two selectors:
     * .class1{
     *     border: solid black 1px;
     * }
     * .class2{
     *     background-color: white;
     * }
     * If you want the properties in class1 to be "absorbed" into class2, call this method as follows:
     * inheritCSSBlock(".class1", ".class2", array("background-color", "white"));
     * The inheritance isn't resolved until the page is rendered, so the parent element needn't exist
     * yet.
     * @param type $parent
     * @param type $name
     * @param array $components 
     */
    public function inheritCSSBlock($parent, $name, array $components) {
        CSSSelectors::addSelector($parent, $name, $components);
        return $this;
    }

    //A default implementation is provided, though it can be overridden by a subclass if needed, or if there's not even a chance that would make sense, we could make it final
    public function display() {
        echo $this->render();
    }

    /**
     * Adds a script that will get added to the head of a page, as an externally linked script.
     * @param string $script 
     */
    final public function addExternalScript($script) {
        if ($this->externalScripts === null) {
            $this->externalScripts = array();
        }
        if (!in_array($script, $this->externalScripts)) {
            $this->externalScripts[] = $script;
        }
        return $this;
    }

    /**
     * Adds a script that will get added as an inline script
     * @param string $script 
     */
    final protected function addInlineScript($script) {
        //We must separate scripts with newlines, so that line comments from the previous script don't affect this script
        $this->internalScripts .= $script . "\n";
        return $this;
    }

    final public function addExternalCSS($href) {
        if ($this->externalCSS === null) {
            $this->externalCSS = array();
        }
        if (!in_array($href, $this->externalCSS)) {
            $this->externalCSS[] = $href;
        }
        return $this;
    }

    final public function getExternalCSS() {
        if ($this->externalCSS === null) {
            $this->externalCSS = array();
        }
        return $this->externalCSS;
    }

    final public function addMetaTag(HTMLMeta $meta) {
        if ($this->metaTags === null) {
            $this->metaTags = array();
        }
        $this->metaTags[] = $meta;
        return $this;
    }

    final public function getMetaTags() {
        if ($this->metaTags === null) {
            $this->metaTags = array();
        }
        return $this->metaTags;
    }

    /**
     * Gets the external scripts linked to by this element
     * @return array
     */
    final public function getExternalScripts() {
        if ($this->externalScripts === null) {
            $this->externalScripts = array();
        }
        return $this->externalScripts;
    }

    /**
     * Gets the inline scripts needed by this element
     * @return string
     */
    final public function getInlineScripts() {
        return $this->internalScripts;
    }

    /**
     * Sets the title of this page. If $title is null, it is ignored.
     * @param type $title 
     */
    final public function setPageTitle($title) {
        if ($title === null) {
            return;
        }
        if ($this->title !== null) {
            trigger_warning("Setting page title more than once! Was {$this->title} but now is $title", E_USER_WARNING);
        }
        $this->title = $title;
        return $this;
    }

    final public function getPageTitle() {
        return $this->title;
    }

    /**
     * Given a view, assimilates the HTMLView level information into this object.
     * @param HTMLView $view 
     */
    final protected function extract(HTMLView $view) {
        if ($view->externalScripts !== null) {
            foreach ($view->externalScripts as $exScript) {
                $this->addExternalScript($exScript);
            }
        }
        if ($view->externalCSS !== null) {
            foreach ($view->externalCSS as $exCSS) {
                $this->addExternalCSS($exCSS);
            }
        }
        $this->addInlineScript($view->internalScripts . "\n");
        $this->setPageTitle($view->title);
        $this->customCSS .= ($this->customCSS != "" ? "\n" : "") . $view->customCSS;
    }

}
?>
