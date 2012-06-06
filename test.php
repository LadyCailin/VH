<?php
include 'lib/include.inc';
/**
 * This file contains a framework that demonstrates a different approach to writing HTML that is generated by PHP.
 * It uses MVC concepts, as well as abstraction, inheritance, and dependency inversion to programmatically generate
 * the HTML, and can easily be extended to add custom views.
 */

//A CRUD interface would likely be able to generate a 2D array for us, but we'll generate one real quick
//This information would be pulled from the model, as needed
function getArrayFromModel() {
    $array = array();
    if (!isset($_SESSION['showEntities'])) {
        $_SESSION['showEntities'] = true;
    }
    if (!isset($_SESSION['radioButton'])) {
        $_SESSION['radioButton'] = "two";
    }
    for ($i = 0; $i < 5; $i++) {
        $inner = array();
        for ($j = 0; $j < 5; $j++) {
            $inner[] = "$i $j" . ($_SESSION['showEntities'] ? " <escaped entities>" : "") . " " . $_SESSION['radioButton'];
        }
        $array[] = $inner;
    }
    return $array;
}

function setModelData($showEntities, $radioButton) {
    $_SESSION['showEntities'] = $showEntities;
    $_SESSION['radioButton'] = $radioButton;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// For convenience, everything is in one file, but the code below is the only thing that would be in the controller, normally
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
session_start();
//First, create a new page manager.
$manager = new HTMLPageManager();

//A page consists of Views and Components. Views are entire pages, they know how to render
//everything from the doctype to the </html> tag. In this example, we have 2 views, which
//we are calling main, and view2. The registerView function takes two parameters, the view
//name, and a callback function. The callback function accepts one parameter, which is
//a reference to the current page manager. It must return an HTMLPage object, which will
//be rendered if this view is requested. Note that we are registering a callback here,
//so unless the callback is actually used, the overhead of running this code is almost 0,
//but because we are registering all the views, every time, the manager can do cool stuff
//for us, like show the default view if a view that doesn't exist is requested.
$manager->registerView("main", function($manager) {
    //Now, let's create the page, and add our table to it.
    $frame = new HTMLPage();
    //Here, we set the page title, and a meta tag. We could also set custom CSS styles, or add
    //external CSS or javascript from here as well. Often times, if you have a standard page
    //layout on every page, you could subclass HTMLPage, and this information could all be added
    //automatically in the constructor.
    $frame->setPageTitle("Title")->
            addMetaTag(new HTMLMeta("This is the page", "description"));
    //Here we create an image tag, but since we are doing something more complex (and PHP does not
    //support immediate dereferenceing of newly created objects) we put it in a variable first.
    $img = new HTMLImg("http://www.w3schools.com/images/compatible_firefox.gif", "firefox logo");
    //Pretend this is dynamic. If it were, we could change the alt text after we set it
    //in the constructor.
    if(true){
        $img->setAltText("New alt text");
    }
    $frame->appendContent($img)->     
            //Adds the table to the content area of the page. See below for the component named "table"
            appendContent($manager->getComponent("table"))-> 
            //We are also going to register a form later, but we need a button that will
            //activate showing the form. Note that we don't actually add the form to the
            //content area, but if we always wanted it visible, we could do that instead.
            appendContent(new HTMLShowFormButton("form", "Show Form"))->
            //Here we add a button that will take us to view2, which we will register in a second.
            appendContent(new HTMLDiv(new HTMLShowViewButton("view2", "Go to View 2, which demonstrates component reuse")))->
	    //Here, we add a button that will take us to the accordion view
	    appendContent(new HTMLDiv(new HTMLShowViewButton("accordion", "Go to the accordion demo")))->
	    appendContent(new HTMLDiv(new HTMLShowViewButton("formatting", "Go to the formatting tag demo (things like <strong>, etc)")));
    return $frame;
});

//We're also going to register a second view, to demonstrate the view switching.
$manager->registerView("view2", function($manager) {
    $frame = new HTMLPage();
    $frame->setPageTitle("View 2")->
            //Just create a button to go back to the other view
            appendContent(new HTMLShowViewButton("main", "Go back to main view"))->
            //...and add the same table that was on the first page...
            appendContent($manager->getComponent("table"))->
            //... and add some other stuff. This demonstrates how easy it is
            //to create both ordered and unordered lists, even nested ones.           
            appendContent(new HTMLUnorderedlist(array(
                "bat", 
                new HTMLOrderedlist(array(
                    "cat", 
                    "sat")), 
                "hat")))->
            appendContent(new HTMLOrderedList(array("one", "two", "three")));
    return $frame;
});

//This view demonstrates the ease with which an accordion widget can be created.
//Note that this view is returning just an HTMLView, not an HTMLPage, because
//of this, the manager will send this content to the wrapper generator, which
//by default simply uses a barebones HTML page, but can be overridden (as is done
//below) to return a more complex page, without having to re-implement the page.
$manager->registerView("accordion", function($manager){
	//Create a new block element, we need to add a button to go back to the main view,
	//as well as the accordion itself.
	$block = new HTMLDiv();	
	//The options here are passed directly to the jquery accordion constructor.
	//We have to provide an id now, if we wish, since that is how the manager
	//knows which accordion to tie these options to. However, if we tried to
	//set the id manually later, it would throw an exception.
	$acc = new HTMLAccordion(array("active"=>1), "accordionID");
	for($i = 0; $i < 5; $i++){
		//To add a section to the accordion, simply call addSection.
		$acc->addSection("Title " . ($i + 1), "This is the contents of section " . ($i + 1));
	}
	$block->addView($acc);	
	$block->addView(new HTMLShowViewButton("main", "Go back"));
	return $block;
});

//This view demonstrates the common formatting elements, such as strong, em, p, etc.
$manager->registerView("formatting", function($manager){
	$block = new HTMLDiv();
	$block->addView(new HTMLP("This is a paragraph, with ", new HTMLStrong("strong text"), ", ", new HTMLEm("and em text.")));
	$block->addView(new HTMLPre("This is preformatted text,\nwhich has newlines,\n      and spaces. <test>"));
	$block->addView(new HTMLBr());
	$block->addView(new HTMLBr());
	$block->addView(new HTMLBr());
	$block->addView("There are a few <br /> tags right above here");
	$block->addView(new HTMLDiv(new HTMLShowViewButton("main", "Go back")));
	return $block;
});

//Now, all of our views are added, but we also need to register our components.
//Whether or not you want to make a component out of something is up to you,
//but the general guidelines for deciding to make something a component is to
//ask if it is going to be re-used ever, or if it should ever be reloaded
//independant of an entire page reload. (For instance, due to a form submission
//or refreshed at some interval). Forms added with addForm are components too,
//but they also have other data, so you shouldn't use addComponent to add them.
//(You may however use getComponent to retrieve them.) Registering a component
//in this way is useful even if it isn't a form or a regenerated component,
//because if you use it in multiple views, you can just call getComponent
//instead of copy pasting the code everywhere. If you find that you are re-using
//this component across multiple controllers, you should consider creating
//a separate View, and moving most of the code outside of the controller altogether.
$manager->addComponent("table", function($manager) {
    $array = getArrayFromModel();
    //To spice it up, we're gonna make the first cell a link...
    $array[0][0] = new HTMLA($array[0][0], "http://www.google.com");
    //... and the second cell the current time.
    $array[0][1] = date("G:i.s");
    $table = new HTMLTable($array);
    //Add Our header/footer and attributes
    $table->addCSSBlock("table thead tr th", array("background-color" => "black", "color"=>"white"));
    //This becomes the cells in the footer...
    $table->addFooterArray(array("This", "is", "the", "footer", "yay"));
    //... and this the cells in the header. Note that I don't actually
    //care which order I register them in, they are put in the right spots
    //anyways.
    $table->addHeaderArray(array("This", "is", "the", "header", "yay"));
    //We do want the border
    $table->enableBorder(true);
    //And I'll set the table's id too.
    $table->setId("hi");

    //We want custom rendering per row, so we get a nice effect.
    //The function registered with addRowRenderer should return
    //a list of attributes that will be added to the tr element
    //that is automatically created. We are adding a gray background
    //to the cells in that row.
    $table->addCSSBlock(".odd td", array("background-color" => "gray"));
    $table->addRowRenderer(function($num) {
                if ($num % 2 == 0) {
                    return array("class" => "odd");
                } else {
                    return array("class" => "even");
                }
            });
    return $table;
});

//Now we are going to create an automatically managed form. First, we need
//to set a few options on the form, so that we can specify some automatic
//behavior. The HTMLFormOptions class is a simple struct that contains
//various options that we may want to change. They all default to reasonable
//values, so this step is optional, but if you want more precise behavior (or
//automatic validation) you should read over the values in the class.
$formOptions = new HTMLFormOptions();
//These values are sent to the jQuery dialog. We want this dialog to be modal.
$formOptions->dialogOptions = array("modal" => "true");
//Setting the validation options here makes the form validate the values
//both client side and server side, but we don't have to re-write the code each time.
$formOptions->validationOptions = array("text" => array('type' => 'string', 'minlen' => '2'));
//When we submit a form, we can force the submission to use javascript instead of doing a normal
//form submission, which is the default, however, we are going to disable that, and it
//will use a normal HTTP form submission.
$formOptions->isAsync = false;
//Now we actually add the form to our manager. The component is first given a name ("form" in this case)
//And a callback function that returns the actual form element. It is checked to verify that this
//function actually returns an instanceof HTMLForm, so make sure that's what you do at the bottom.
//Additionally, we also specify the handler for the form submission, which recieves the
//request parameters that were added to the form. Unlike the $_REQUEST variables,
//the $req variable passed in is guaranteed to contain all fields that were defined
//as inputs in the form. This is not guaranteed normally, for instance, when using
//an html checkbox, if the checkbox is not selected, it simply does not send the
//parameter, which causes "undefined index" notices if you forget to check it with
//isset first. Also, if any of the parameters failed validation, the form handler
//will not have been called in the first place, so anything verified with validationOptions
//is guaranteed to be correct at this point.
$manager->addForm("form", function($manager) {
    $form = new HTMLForm();    
    $form->addView(new HTMLDiv(new HTMLCheckboxInput("name", "Hi!", true)));
    $form->addView(new HTMLDiv(new HTMLRadioGroup("radio", array("one" => "One", "two" => "Two", "three" => "Three"), "two")));
    $form->addView(new HTMLDiv("Text: ", new HTMLTextInput("text")));
    $form->addView(new HTMLSubmitResetCancelInput("", ""));
    $form->setFieldsetName("Fieldset");
    $form->setMethod(HTMLForm::POST);
    return $form;
}, function($req) {
    setModelData($req['name'], $req['radio']);
}, $formOptions);

//Forms that are normally shown in a popup dialog box are shown on
//their own page if javascript is disabled. This method is used in those
//cases, and it will simply recieve the form as $content, which should be
//added to an HTML page, and returned.
//TODO: This should happen on all the views. This way, we can automatically
//add messages and things at the top, without it looking weird because
//the message is above the site's header and menu.
$manager->setWrapperGenerator(function($manager, $content) {
    $page = new HTMLPage($content);
    return $page;
});

//Finally, we trigger the manager, which will look at all the parameters
//passed in, and do most of the work for us.
$manager->handle();
?>
