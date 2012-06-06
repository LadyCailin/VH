<?php 
include_once(dirname(__FILE__)."/HTMLInline.php");
/**
 * This class represents an a tag. 
 */
class HTMLA extends HTMLInline {
    /**
     * Used by target, opens the link in a new window 
     */

    const _BLANK = "_blank";

    /**
     * Used by target, opens the link in the same frame as it was clicked. This is the default,
     * so it is simply removed from the output if this is what it is set to. 
     */
    const _SELF = "_self";

    /**
     * Used by target, opens the link in the parent frame 
     */
    const _PARENT = "_parent";

    /**
     * Used by target, opens the link in the full body of the window 
     */
    const _TOP = "_top";

    private static $urlRegex = "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie";

    public function __construct($content, $href = null) {
        parent::__construct($content);
        if ($href !== null) {
            $this->setHref($href);
        }
    }

    /**
     * This does not encode the url, so you must do that yourself. Alternatively, use
     * the URL object, and guarantee the url will be properly encoded.
     * @param type $href 
     */
    public function setHref($href) {
        $this->appendAttribute("href", $href);
        return $this;
    }

    public function getHref() {
        return $this->getAttribute("href");
    }

    /**
     * Sets the target of this link. Note that using the target attribute causes validation errors,
     * however you must use javascript to get around that problem, which may not always
     * be acceptable as a solution.
     * @param type $target 
     */
    public function setTarget($target) {
        $this->appendAttribute("target", $target);
        return $this;
    }

    public function getTarget() {
        return $this->getAttribute("target");
    }

    public function appendAttribute($name, $content) {
        if ($name == "href") {
            $this->removeAttribute("href");
            if ($content instanceof URL) {
                $content = $content->build();
            }
        }
        if ($name == "target") {
            $this->removeAttribute("target");
            //_self is the default target, so we can just remove it entirely.
            if ($content == self::_SELF) {
                return;
            }
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

    protected function getCompositeTagName() {
		return "a";	    	    
    }

}
?>
