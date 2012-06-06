<?php 
include_once(dirname(__FILE__)."/HTMLView.php");
/**
 * An HTMLContainer can encapsulate any content, but allows for attributes to be added to the html element.
 * Basic elements would probably use HTMLBlock or HTMLInline as the actual implementation.
 * 
 * Typically, you won't extend this class directly, unless you are creating a class
 * to represent a single HTML tag.
 */
abstract class HTMLContainer extends HTMLView {

    /**
     * This array contains a list of the known ids in this heirarchy
     * @var type 
     */
    private $knownIDs = array();

    /**
     * If this container's tag should self close if there is no content
     * @var type 
     */
    protected $selfClosed = true;

    /** These are the custom attributes */
    private $attributes;
    private $styles;
    private static $randID = null;

    /**
     * Sometimes complex components need to generate a random ID to assign to automatically generated
     * and managed components. This method returns a (more than likely) guaranteed unique id, across
     * all runs of the script.
     */
    public static function getRandomId() {
        if (self::$randID === null) {
            self::$randID = (int) (rand(10, 1000) + time()) / 1000;
        }
        $id = self::$randID++;
        return "autoGenID_" . $id;
    }

    /**
     * As a convenience to simple classes, the $content variable may be used to store contained data, which
     * is returned by getContent() by default. 
     * @var mixed
     */
    protected $content = null;

    protected function __construct($content) {
        if (!($content instanceof HTMLView)) {
            $content = new HTMLText($content);
        }
        $this->content = $content;
        $this->extract($content);
    }

    protected function isSelfClosingSupported() {
        return $this->selfClosed;
    }

    /** Sets the specified attribute, overwriting the old one if already set */
    final public function setAttribute($name, $content) {
        $this->removeAttribute($name);
        if ($name == "id") {
            unset($this->knownIDs[spl_object_hash($this)]);
            //Check for duplicate ids 
            if (in_array($content, $this->knownIDs)) {
                trigger_error("Duplicate id being added to an element! \"" . $content . '"', E_USER_WARNING);
            }
            $this->knownIDs[spl_object_hash($this)] = $content;
        }
        self::appendAttribute($name, $content);
        return $this;
    }

    final public function removeAttribute($name) {
        if ($this->attributes === null) {
            $this->attributes = array();
        }
        if ($name == "id") {
            unset($this->knownIDs[spl_object_hash($this)]);
        }
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Returns the value stored in a specific attribute.
     * @param string $name
     * @return mixed Null if the attribute isn't added, or the contents if it has
     */
    final public function getAttribute($name) {
        if ($this->attributes === null) {
            $this->attributes = array();
        }
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return null;
        }
    }

    /**
     * Convenience method to set the id of this element
     * @param type $id 
     */
    public function setId($id) {
	$this->removeAttribute("id");
        $this->appendAttribute("id", $id);
        return $this;
    }
    
    public function getId(){
	    return $this->getAttribute("id");
    }

    /**
     * Convenience method to add a class to this element
     * @param type $class 
     */
    public function addClass($class) {
        $this->appendAttribute("class", $class);
        return $this;
    }

    /**
     * Convenience method to add a style to this element. The caller of this
     * method need not worry about separating it from the existing content, it will be added properly, that is,
     * the semi-colon will be added only if necessary, but will be done automatically.
     */
    public function addStyle($style, $value) {
        if ($this->styles === null) {
            $this->styles = array();
        }
        $this->styles[$style] = $value;
        return $this;
    }

    private static $coreAttributesDisallowed = array("base", "head", "html", "meta", "param", "script", "style", "title");
    private static $coreAttributes = array("class", "id", "style", "title");
    private static $languageAttributesDisallowed = array("base", "br", "frame", "frameset", "hr", "iframe", "param", "script");
    private static $languageAttributes = array("dir", "lang", "xml:lang");

    /**
     * Checks to see if any disallowed attributes are being added, based on this element name
     * @param type $attributeName 
     */
    private function checkAttribute($attributeName) {
        $me = $this->getTagName();
        $trigger = false;
        if (in_array($attributeName, self::$coreAttributes)) {
            if (in_array($me, self::$coreAttributesDisallowed)) {
                $trigger = true;
            }
        }
        if (in_array($attributeName, self::$languageAttributes)) {
            if (in_array($me, self::$languageAttributesDisallowed)) {
                $trigger = true;
            }
        }

        if ($trigger) {
            trigger_error("The attribute '$attributeName' is not allowed in $me tags", E_USER_WARNING);
        }
    }

    /**
     * Appends the content to the specified attribute, creating it first if it doesn't exist. For the special	  
     * case of the style attribute, puts a ; between the existing content and the new content. Subclasses
     * may override this method to do more specific checks on attributes added to an element. Note that
     * setting an attribute is the same as removing the attribute, then appending it.
     * 
     * Note to subclass implementers: You should call parent::appendAttribute for all attributes you aren't
     * specially handling, and you should use it to actually add the attribute as well, or setAttribute.
     */
    public function appendAttribute($name, $content) {
	if($this->attributes === null){
		$this->attributes = array();
	}
        $name = trim(strtolower($name));
        $this->checkAttribute($name);
        if ($name == "id" && isset($this->attributes['id'])) {
            //It doesn't make sense to append to an id, so trigger a warning
            trigger_error("Attempting to append to the id attribute", E_USER_WARNING);
        }
        if ($name == "style") {
            trigger_error("You shouldn't use appendAttribute to add styles. Use addStyle instead.", E_USER_WARNING);
        }
        if ($name == "class") {
            //Look at the existing content to see if it already has a space at the end. If not, add a space first
            if (isset($this->attributes["class"])) {
                $content = $this->attributes["class"] . " " . $content;
                $classes = array();
                foreach (preg_split("/[ ]+/", $content) as $class) {
                    $classes[] = $class;
                }
                $content = implode(" ", array_unique($classes));
            }
        }
        //We give special treatment to the standard HTML attributes,
        //such as id, class, style, and title, since we can be smarter about them
        if ($name === "id") {
            $this->attributes[$name] = $content;
            return;
        }

        if (in_array($name, $this->attributes)) {
            $this->attributes[$name] = $this->attributes[$name] . $content;
            return;
        }
        $this->attributes[$name] = $content;
        return $this;
    }

    /**
     * Convenience method to set an associative array of attributes
     * @param type $attributes 
     */
    final public function addAttributeArray($attributes) {
        foreach ($attributes as $name => $content) {
            $this->appendAttribute($name, $content);
        }
        return $this;
    }

    private function renderStyles() {
        return self::SRenderStyles($this->styles);
    }

    public static final function SRenderStyles($styleArray) {
        if ($styleArray == null) {
            return null;
        }
        $first = true;
        $rendered = "";
        foreach ($styleArray as $style => $value) {
            if (!$first) {
                $rendered .= "; ";
            }
            $rendered .= "$style: $value";
            $first = false;
        }
        return $rendered;
    }

    /**
     * Renders out the attribute set. Uses $this->attributes if $attributes is null. If the attribute
     * list is empty (or null), null is returned. Attributes are properly escaped at this point,
     * so they don't need to actually be escaped elsewhere.
     * @return type 
     */
    final protected function renderAttributes($attributes = null) {
        if ($attributes === null) {
            $attributes = $this->attributes;
            $renderedStyles = $this->renderStyles();
            if ($renderedStyles !== null) {
                $attributes["style"] = $renderedStyles;
            }
        }
        return self::SRenderAttributes($attributes);
    }

    public static final function SRenderAttributes($attributes) {
        if ($attributes === null) {
            return null;
        }
        if ($attributes !== null && !is_array($attributes)) {
            throw new Exception("Value passed to renderAttributes is not an array or null");
        }
        if ($attributes === null || count($attributes) === 0) {
            //No attributes on this element
            return null;
        }
        $result = "";
        foreach ($attributes as $name => $content) {
            //We need to escape the special characters in the content
            $result .= $name . '="' . htmlentities($content) . '" ';
        }
        return trim($result);
    }

    /** Gets the content of this item, which is used in the default implementation of render(). If this
      returns null, the tag will be self closed if that option is set. By default, returns $content, which
      is by default null. */
    protected function getContent() {
        return $this->content;
    }

    /**
     * Returns the tag name, which is used by the default implementation of render. 
     */
    abstract protected function getTagName();

    /**
     * The default implementation of render returns the generated HTML, based on tag name provided by getTagName,
     * any attributes added to the component, and the contents returned by getContent.
     * @return string 
     */
    public function render() {
        $content = $this->getContent();
        if ($content instanceof HTMLView) {
            $content = $content->render();
        }
        if ($this->getTagName() === null) {
            return $content;
        }
        $attributes = $this->renderAttributes();

        $rendered = "<" . $this->getTagName();
        if ($attributes !== null) {
            $rendered .= " $attributes";
        }
        if ($content === null && $this->selfClosed) {
            $rendered .= " />";
            return $rendered;
        } else {
            $rendered .= ">";
            $rendered .= $content
                    . "</" . $this->getTagName() . ">";
        }
        return $rendered;
    }

}
?>
