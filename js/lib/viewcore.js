
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


