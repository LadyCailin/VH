<?php

/**
 * This interface represents the root of all View objects. All views
 * must know how to render themselves, in the context of their domain.
 * @author lsmith
 */
interface View {

    /** 
     * Returns the rendered content as a string. Null may be returned,
     * indicating that this View is not to be rendered. 
     */
    public function render();

    /** 
     * Causes this view to actively be displayed. 
     */
    public function display();
}

?>
