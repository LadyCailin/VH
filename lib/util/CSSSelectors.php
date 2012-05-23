<?php 
class CSSSelectors {

    private static $selectors = array();
    //Maps child(key) to parent(value)
    private static $inheritance = array();

    public static function addSelector($parent, $name, array $attributes) {
        //TODO: Check for invalid characters in the parent or name
        //Walk through our attributes and check for illegal characters
        foreach ($attributes as $key => $value) {
            $illegalRegex = '/[:;]/';
            if (preg_match($illegalRegex, $key) || preg_match($illegalRegex, $value)) {
                trigger_error("Illegal character in attribute list: '" . $key . "' or '" . $value . "'\n<br />You many not include any characters that"
                        . " match the following regex: $illegalRegex. Special characters are inserted for you", E_USER_WARNING);
            }
        }
        $selectors = self::standardizeCSSSelector($name);
        foreach ($selectors as $name) {
            if (isset(self::$selectors[$name])) {
                //It's already set, so just append these attributes
                foreach ($attributes as $prop => $value) {
                    self::$selectors[$name][$prop] = $value;
                }
            } else {
                //It's new, just set it
                self::$selectors[$name] = $attributes;
                if ($parent != null) {
                    self::$inheritance[$name] = $parent;
                }
            }
        }
    }

    public static function render() {
        $rendered = "";
        foreach (array_keys(self::$selectors) as $name) {
            $attributes = self::collectAttributes($name);
            $rendered .= "$name {\n";
            foreach ($attributes as $name => $value) {
                $rendered .= "\t$name: $value;\n";
            }
            $rendered .= "}\n\n";
        }
        return $rendered;
    }

    private static function collectAttributes($name) {
        $attributes = array();
        $hierarchy = array();
        array_push($hierarchy, $name);
        $find = $name;
        while (array_key_exists($find, self::$inheritance)) {
            //Avoid inheritance loops			
            if (in_array($find, $hierarchy)) {
                break;
            }
            array_push($hierarchy, $find);
            $find = self::$inheritance[$find];
        }
        while (($popped = array_pop($hierarchy)) !== null) {
            foreach (self::$selectors[$popped] as $key => $value) {
                //Merging this way allows for overridding properties
                $attributes[$key] = $value;
            }
        }
        return $attributes;
    }

    /**
     * Returns an array of standardized css names, that is, a name with extra spaces is removed.
     * The name is split on commas first, which allows for more granular control of the names.
     * @param type $selector 
     */
    private static function standardizeCSSSelector($selector) {
        $names = preg_split("/,/", $selector);
        $final = array();
        foreach ($names as $name) {
            $name = trim($name);
            if ($name == "") {
                continue;
            }
            //Standardize + and > with exactly one space on either side of it, so h1>h2 turns into h1 > h2
            $name = preg_replace('/>/', " > ", $name);
            $name = preg_replace('/\+/', " + ", $name);
            $name = preg_replace('/\s{2,}/', " ", $name);
            $final[] = $name;
        }
        return $final;
    }

}
?>
