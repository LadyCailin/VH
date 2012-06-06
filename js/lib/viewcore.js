
var VC = new function ViewCore(){	
	/* Private **************************************************************************************/
	var formOptions = new Array();
	var metaData = new Array();
	//Initialization function
	(function(){
		$(function(){		
			//We need to first iterate through all the intercept 
			//We also need to inject a dialog box container at the bottom of the page
			$(".--intercept").each(function(index, element){
				if($(element).hasClass("--show-form")){
					//We need to register to intercept all the forms that are being automatically managed by us					
					results = $(element).get(0).className.split(/\s+/);
					for(clazz in results){
						clazz = results[clazz];
						if((res = /---(.*)/.exec(clazz)) != null){
							prepareForm(res[1]);
						}
					}
				}
				if($(element).hasClass("--show-view")){
					prepareShowView($(element).find("input[name='_view']").val());
				}
				if($(element).hasClass("--accordion")){
					prepareAccordion($(element));
				}
			});			
		});
		installDialogBox();
	})();
	function prepareForm(form){
		$(".---" + form).submit(function(e){			
			showDialog("form input[type='hidden'][name='_action'][value='" + form + "']", form);
			e.preventDefault();					
			return false;
		});		
	}
	
	function prepareShowView(view){
		$("input[value='" + view + "']").parents("form").submit(function(e){
			window.location = window.location.pathname + "?_view=" + view;
			e.preventDefault();
			return false;
		})
	}
	
	function prepareAccordion(view){
		var options = getMetaData($(view).attr("id"), {});
		$(view).accordion(options);
	}
	
	function showDialog(content, form){
		$("#autoDialog").dialog(formOptions[form]["dialogOptions"]);
                //We create a pseudo form here, so it will work correctly with the reset button
                var newForm = $("<form action='' method='post'>" + $(content).parents("form").html() + "</form>");
                newForm.attr("action", $(content).parents("form").attr("action"));
                newForm.attr("method", $(content).parents("form").attr("method"));
		$("#autoDialog").html(newForm);
                //Now, we need to hijack the Submit and Cancel buttons, if they exist.
                $(newForm).submit(function(e){
                    var validationOptions = formOptions[form]['validationOptions'];
                    for(var input in validationOptions){
                        var options = validationOptions[input];
                        var val = $("#autoDialog input[name='" + input + "']").val();
                        var errorMsg = "";
                        var failValidation = false;
                        if(options['type'] == "string"){
                            var hasMinLen = false;
                            if(options.hasOwnProperty("minlen")){
                                hasMinLen = true;
                                if(val.length < options['minlen']){
                                    failValidation = true;
                                }
                            }
                            var hasMaxLen = false;
                            if(options.hasOwnProperty("maxlen")){
                                hasMaxLen = true;
                                if(val.length > options['maxlen']){
                                    failValidation = true;
                                }
                            }
                            if(hasMaxLen && hasMinLen){
                                if(options['minlen'] == options['maxlen']){
                                    errorMsg = "You must enter exactly " + options['minlen'] + " character" + (options['minlen']==1?"":"s") + ".";
                                } else {
                                    errorMsg = "You must enter between " + options['minlen'] + "-" + options['maxlen'] + " characters.";
                                }
                            } else if(hasMaxLen){
                                errorMsg = "You must enter no more than " + options['maxlen'] + " characters.";
                            } else if(hasMinLen){
                                errorMsg = "You must enter at least " + options['minlen'] + " characters.";
                            }
                        } else if(options['type'] == 'numeric' || options['type'] == 'integer'){
                            //TODO
                            if(isNaN(val)){

                            }
                        }                                  
                        //TODO: Change this from an alert to something more elegant, like a tooltip or something.
                        if(failValidation){
                            alert(errorMsg);
                        } else {
                            //Do the submission. If this form isAsync, hijack the submission, and do it via ajax. Otherwise, do the submission normally.
                            if(formOptions[form]['isAsync']){
                                alert("Would submit via ajax now");
                                closeDialog();
                            } else {
                                return true;
                            }
                        }

                    }
                    e.preventDefault();
                    return false;
                });
                $("#autoDialog a.autoCancel").each(function(index, element){
                   $(element).click(function(e){
                       closeDialog();
                       e.preventDefault();
                       return false;
                   })
                });
	}
	
	function closeDialog(){
            $("#autoDialog").dialog("destroy");            
	}
	
	function installDialogBox(){
		$(function(){
			if($("#autoDialog").length == 0){
				$("body").append("<div id='autoDialog'></div>");
			}			
		});
	}
	
	/**
	 * Returns the specified component's meta data. If there is no meta data
	 * specified, _default can be set to be returned instead. Null is returned
	 * by default.
	 */
	function getMetaData(id, _default){
		if(id != null && id != "" && metaData.hasOwnProperty(id)){
			return metaData[id];
		} else {
			return _default;
		}
	}
	
	/* Public ***************************************************************************************/
	
	this.addFormOptions = function(name, options){
		formOptions[name] = options;
	};
	
	/**
	 * Adds any sort of meta data to the component with the specified ID. It is up to
	 * that component to know how to deal with the given data, but it will be available
	 * for lookup based on the id.
	 */
	this.addComponentMeta = function(id, meta){
		metaData[id] = meta;
	};
	
};

//The widget controller works in conjunction with the jQuery UI functions to provide easier
//access to certain widgets.
var widgets = new function WidgetController(){
	var Accordion = function(handle){
		//Events
		this.onCreate = function(method){
			handle.bind("accordioncreate", method);
		};
		
		this.onChange = function(method){
			handle.bind("accordionchange", method);
		}
		
		this.onChangeStart = function(method){
			handle.bind("accordionchangestart", method);
		}
		//Methods
		this.destroy = function(){
			handle.accordion("destroy");
		};
		
		this.disable = function(){
			handle.accordion("disable");
		};
		
		this.enable = function(){
			handle.accordion("enable");
		};
		
		this.option = function(name, value){
			handle.accordion.apply(handle, Util.array_merge(['option'], arguments));
		};
		
		this.activate = function(index){
			handle.accordion("activate", index);
		}
		
		this.resize = function(){
			handle.accordion("resize");
		}
		
		this.closeAll = function(){
			this.option("collapsible", true);
			this.activate(false);
		}
	};

	var Dialog = function(handle){
		
	};
	/**
	 * The first step to getting a widget is to call widgets.get('The id you assigned the widget').
	 * Once you have that, you'll have an instance of a widget controller for that particular widget.
	 * The type of the widget will be automatically determined, and you can call methods on it to
	 * control that individual widget. If such an id doesn't exist, or isn't any sort of jquery widget,
	 * this will return null.
	 * 
	 * Note that it doesn't matter if this widget was created through VH or not, but the methods
	 * all correspond to the PHP controlled functions.
	 */
	this.get = function(widgetId){
		id = "#" + widgetId;
		if($(id).data("accordion") instanceof $.ui.accordion)
			return new Accordion($(id));
		if($(id).data("dialog") instanceof $.ui.dialog)
			return new Dialog($(id));
		return null;
	};
};


//Utility functions
var Util = new function(){
	this.array_merge = function array_merge () {
		// This function retrieved from http://phpjs.org/functions/array_merge:326, which is MIT licensed.
		// http://kevin.vanzonneveld.net
		// +   original by: Brett Zamir (http://brett-zamir.me)
		// +   bugfixed by: Nate
		// +   input by: josh
		// +   bugfixed by: Brett Zamir (http://brett-zamir.me)
		// *     example 1: arr1 = {"color": "red", 0: 2, 1: 4}
		// *     example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
		// *     example 1: array_merge(arr1, arr2)
		// *     returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
		// *     example 2: arr1 = []
		// *     example 2: arr2 = {1: "data"}
		// *     example 2: array_merge(arr1, arr2)
		// *     returns 2: {0: "data"}
		var args = Array.prototype.slice.call(arguments),
			argl = args.length,
			arg,
			retObj = {},
			k = '', 
			argil = 0,
			j = 0,
			i = 0,
			ct = 0,
			toStr = Object.prototype.toString,
			retArr = true;

		for (i = 0; i < argl; i++) {
			if (toStr.call(args[i]) !== '[object Array]') {
			retArr = false;
			break;
			}
		}

		if (retArr) {
			retArr = [];
			for (i = 0; i < argl; i++) {
			retArr = retArr.concat(args[i]);
			}
			return retArr;
		}

		for (i = 0, ct = 0; i < argl; i++) {
			arg = args[i];
			if (toStr.call(arg) === '[object Array]') {
			for (j = 0, argil = arg.length; j < argil; j++) {
				retObj[ct++] = arg[j];
			}
			}
			else {
			for (k in arg) {
				if (arg.hasOwnProperty(k)) {
				if (parseInt(k, 10) + '' === k) {
					retObj[ct++] = arg[k];
				}
				else {
					retObj[k] = arg[k];
				}
				}
			}
			}
		}
		return retObj;
		};

};

