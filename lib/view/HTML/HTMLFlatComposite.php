<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
/**
 * A view may contain several other views, but itself not be a container. This class knows how to contain several
 * elements, but it will not add an outer container to the child elements. It will
 * however extract <head> information, and pass it up the chain. The getContent method can be overridden and $views
 * can manually be rendered if need be.
 */
abstract class HTMLFlatComposite extends HTMLContainer {

    protected $views = array();

    /**
     * Initializes the HTMLFlatComposite parent. If $views is null, it is ignored, if it is a single HTMLView it is added
     * to the view list, if it's not an HTMLView object, it is put into an HTMLText object, then added, and if it's
     * an array of HTMLViews, it is walked through and each view is added.
     * @param type $views
     * @param HTMLContainer $container The container for the content.
     * @return type 
     */
    protected function __construct($views = null) {
        $this->construct0(func_get_args());
    }

    private function construct0() {
        foreach (func_get_args() as $arg) {
            if ($arg === null) {
                continue;
            }
            if (is_array($arg)) {
                foreach ($arg as $view) {
                    $this->construct0($view);
                }
            } else {
                $this->addAnyView($arg);
            }
        }
    }

    /**
     * Convenience method to add a component to this composite. If the object passed in
     * is not an HTMLView, it is first wrapped in an HTMLText object. Since this isn't
     * always desirable, this method is protected (and final) but you can override
     * addView and pass it to this function, which will in turn call this class's
     * addView method, ensuring it passes in an HTMLView.
     * @param HTMLView $view 
     */
    protected function addAnyView($view) {
        if ($view instanceof HTMLView) {
            self::addView($view);
        } else {
            self::addView(new HTMLText($view));
        }
    }

    /**
     * Adding a view here will extract up the information contained in the view, so that extractable information is
     * carried up all the way. Implementing classes may want to expose this method as public, but should call
     * this class's method first.
     * @param HTMLView $view 
     */
    protected function addView(HTMLView $view) {
        $this->views[] = $view;
        $this->extract($view);
        return $this;
    }

    protected function getContent() {
        $content = "";
        foreach ($this->views as $view) {
            $content .= $view->render();
        }
        if (trim($content) == "") {
            return null;
        }
        $content = new HTMLRaw($content);
        return $content;
    }

    protected function getTagName() {
        return null;
    }

    /**
     * Recurses down into all the children in this view, and returns them all as a flat list
     * @return array 
     */
    protected function getAllViews() {
        $views = array();
        $this->getAllViews0($views);
        return $views;
    }

    private function getAllViews0(&$views) {
        $views[] = $this;
        foreach ($this->views as $view) {
            if ($view instanceof HTMLFlatComposite) {
                $view->getAllViews0($views);
            } else {
                $views[] = $view;
            }
        }
    }

}
?>
