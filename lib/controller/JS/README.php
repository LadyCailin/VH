<?php 

/**
 * The jstrans package contains all the classes that convert PHP method calls into javascript.
 * 
 * An example is a button that displays some text in an alert box when the button is clicked.
 * 
 * $js = new JS();
 * $button = new HTMLButton("Click me!");
 * $button->onClick($js->alert("You clicked me!"));
 * 
 * This adds a click handler to the button, without having to actually write any javascript.
 * The implementation handles passing the meta data to the page to register this event. Multiple
 * events could have occurred as well, for instance,
 * $button->onClick(
 *	$js->alert("Alert 1")->
 *                alert(
 *	               $js-concat("Alert 2: ", $js->getHTML('#jquerySelector'))
 *	          )
 *           )
 * );
 * would cause two alerts, the second one with dynamic content. Much like the HTML View Hierarchy,
 * the JSTrans object knows how to render javascript based on the chained calls, and the onClick
 * handlers that the HTMLViews have know where to put the javascript, such that it appears in a
 * clean, consistant place.
 */