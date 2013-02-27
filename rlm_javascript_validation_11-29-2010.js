/*
 * jQuery.validation - Form validation with HTML5
 * Written by Rachael L. Moore
 * Date: 2010/11/29
 * Requires Array Sum Prototype http://www.gscottolson.com/weblog/2007/12/09/sum-array-prototype-for-javascript/
 * Requires jQuery.html5type http://www.rachaelmoore.name/web-development/javascript/jquery-plugin-html5-input-type-attr-value
 * When triggered validate, also test support & set up fallback support for HTML5 forms.
 *
 * @author Rachael L. Moore
 * @version 0.7
*/
(function($){
	/* ------------------------------ Listing Detail ------------------------------ */
	$.fn.validation = function(method){
		// Method
		if($.fn.validation.methods.wrapper[method]){
			return $.fn.validation.methods.wrapper[method].apply(this, arguments);
		}
		// Init
		else if(typeof(method) === 'object' || !method){
			// This will only use the last arguments passed in (if multiple instances on a page)
			if(arguments){$.extend($.fn.validation.variables, $.fn.validation.defaults, arguments[0]);}
			return $.fn.validation.methods.init.start.apply(this, arguments);
		}
		// Error
		else{
			$.error('Method ' +  method + ' does not exist in jQuery.validation');
			return false;
		}
	}; // leadGen
	/* ------------------------------ ...& Methods ------------------------------ */
	$.fn.validation.methods = {

		/* ------------- INIT ------------- */
		/* Methods used only on initiation of plugin are in this "init" object. */
		init: {
			start: function(options){
				$.fn.validation.methods.support.detect();
				return this.each(function(){
					// Init Form
					$.fn.validation.methods.init.bind.apply(this);
					// Init Fields
					$(this).find("input, select").each(function(){
						var jqoThis = $(this);
						jqoThis.data("msg", $.fn.validation.variables.field.error.msg);
						$.fn.validation.methods.init.bind.apply(this);
					});
					return;
				}); // this.each
			}, // start
			bind: function(){
				$(this).unbind("validate").bind("validate",
					function(e){
						return $(this).validation("validate");
					} // fn
				); // validate
			} // bind
		}, // init

		/* ------------- META ------------- */
		/* Utility methods used by plugin are in this "meta" object. */
		meta: {
			/* -------- Return value for desired test -------- */
			getTestValue: function(test){
				var testValue = '',
				    testAttr = this.attr(test),
				    testData = this.data(test),
				    testProp = this.prop(test),
				    thisDom = this.get(0),
				    testHTML = thisDom.outerHTML,
				    HTMLregex = "/" + test + "=(\w+)/",
				    HTMLFound,
				    testDomAttr = thisDom.attributes,
				    i = 0;
				// check for testValue in attribute (jquery)
				if((typeof(testAttr) !== "undefined") && testAttr){ testValue = testAttr;}
				// check for testValue in data (jquery)
				if((typeof(testData) !== "undefined") && testData){ testValue = testData;}
				// check for testValue in properties (jquery)
				if((typeof(testProp) !== "undefined") && testProp){ testValue = testProp;}
				// check for testValue in html (js -- i blame jquery)
				if(!testValue.length && (typeof(testHTML) !== "undefined")){
					HTMLFound = testHTML.match(HTMLregex);
					if(HTMLFound){
						testValue = HTMLFound[1];
					}
				}
				// check for testValue in attributes collection (js -- i blame jquery)
				if(!testValue.length && (typeof(testDomAttr) !== "undefined")){
					for(i = 0; i < testDomAttr.length; i++){
						if(testDomAttr[i].nodeName === test){
							testValue = testDomAttr[i].nodeValue;
						}
					}
				}
				return testValue;
			}, // getTestValue
			/* -------- Return rule for desired test -------- */
			getRule: function(test){
				var rule;
				if($.fn.validation.rules.types[test] && $.fn.validation.rules.types[test].check){ rule = $.fn.validation.rules.types[test].check; }
				if($.fn.validation.rules.feats[test] && $.fn.validation.rules.feats[test].check){ rule = $.fn.validation.rules.feats[test].check; }
				if($.fn.validation.rules.cust[test] && $.fn.validation.rules.cust[test].check){ rule = $.fn.validation.rules.cust[test].check; }
				if($.fn.validation.rules.spec[test] && $.fn.validation.rules.spec[test].check){ rule = $.fn.validation.rules.spec[test].check; }
				return rule;
			} // getRule
		}, // meta

		/* ------------- SUPPORT ------------- */
		/* Part of plugin checks for & adds support for various form features.
		 * All methods dealing with support are in this "support" object. */
		support: {
			/* -------- Support check wrapper -------- */
			detect: function(){
				$.fn.validation.methods.support.types();
				$.fn.validation.methods.support.feats();
			}, // detect
			/* -------- Check for input type support -------- */
			types: function(){
				var types = $.fn.validation.reference.types,
				    i,
				    t;
				for(i = 0; i < types.length; i++){
					t = document.createElement("input");
					t.setAttribute("type", types[i]);
					if(t.type !== types[i]){
						$("html").addClass("no-input-" + types[i]);
						/* TO DO - Set up Fallbacks for Types (UI) Here */
					} // if
				} // for
			}, // types
			/* -------- Check for input feature support -------- */
			feats: function(){
				var feats = $.fn.validation.reference.feats,
				    i,
				    f;
				for(i = 0; i < feats.length; i++){
					f = document.createElement("input");
					if(!(feats[i] in f)){
						$("html").addClass("no-input-" + feats[i]);
						/* TO DO - Set up Fallbacks for Features (UI, Functionality) Here */
					} // if
				} // for
			} // feats
		}, // support

		/* ------------- VALIDATE ------------- */
		/* Part of plugin validates form input.
		 * All methods dealing with validation are in this "validate" object. */
		validate: {
			form: function(){
				var form = $(this),
				    fields = form.find("input, select"),
				    result,
				    valid = [];
				// Validate all fields
				fields.each(function(i){
					var jqoThis = $(this);
					result = jqoThis.validation("validate");
					valid.push(result);
				}); // each
				return (valid.sum() == valid.length);
			}, // form
			field: function(){
				var value = $.trim(this.val()),
				    length = value.length,
				    dfault = $.fn.validation.methods.meta.getRule.apply(this, ["data-default"]).apply(this, [value, length]),
				    type = $.fn.validation.methods.validate.type.apply(this, [value, length, dfault]),
				    feat = (type) ? $.fn.validation.methods.validate.feat.apply(this, [value, length, dfault]) : true,
				    cust = (type) ? $.fn.validation.methods.validate.cust.apply(this, [value, length, dfault]) : true,
				    valid = (type && feat && cust);
				// Message
				if(!valid){
					$(this).validation("addError");
				}
				return (valid) ? 1 : 0; // 1 & 0 for sum
			}, // field
			type: function(value, length, dfault){
				if(length && !dfault){ return $.fn.validation.methods.validate.check.apply(this, [this.html5type(), [value, length]]); }
				return true;
			}, // type
			feat: function(value, length, dfault){
				if(dfault){ return true; }
				var feats = $.fn.validation.reference.feats,
				    testValue,
				    result,
				    valid = [],
				    i;
				for(i = 0; i < feats.length; i++){
					testValue = $.fn.validation.methods.meta.getTestValue.apply(this, [feats[i]]);					
					result = ((testValue === true) || testValue.length) ? ($.fn.validation.methods.validate.check.apply(this, [feats[i], [value, length, testValue]])) ? 1 : 0 : 1; // 1 & 0 for sum
					valid.push(result);
				} // for
				return (valid.sum() == valid.length);
			}, // feat
			cust: function(value, length, dfault){
				if(dfault){ return true; }
				var custv = $.fn.validation.reference.cust,
				    testValue,
				    result,
				    valid = [],
				    i;
				for(i = 0; i < custv.length; i++){
					testValue = $.fn.validation.methods.meta.getTestValue.apply(this, [custv[i]]);					
					result = ((testValue === true) || testValue.length) ? ($.fn.validation.methods.validate.check.apply(this, [custv[i], [value, length, testValue]])) ? 1 : 0 : 1; // 1 & 0 for sum
					valid.push(result);
				} // for
				return (valid.sum() == valid.length);
			}, // cust
			check: function(which, args){
				var rule = $.fn.validation.methods.meta.getRule.apply(this, [which]),
				    valid = (typeof(rule) == 'function') ? rule.apply(this, args) : true,
				    msg = $.fn.validation.methods.ui.getMsg.apply(this, [which]);
				// Message
				$.fn.validation.methods.ui.clearUI.apply(this);
				if(!valid){
					$.fn.validation.methods.ui.modMsg.apply(this, [msg]); 
				}
				return valid;
			} // check
		}, // validate

		/* ------------- INTERFACE ------------- */
		/* Part of plugin responds to validation with interface changes.
		 * All methods dealing with form user interface are in this "ui" object. */
		ui: {
			/* -------- Clear ui changes from field -------- */
			clearUI: function(){
				var field = this;
				$.each($.fn.validation.variables.field, function(i, val){
					field.parent().removeClass(val.ui).find("." + val.ui + "-msg").remove();
				});
				return;
			}, // clearUI
			/* -------- Add necessary ui to field -------- */
			addUI: function(scope){
				this.parent().addClass($.fn.validation.variables.field[scope].ui);
				$.fn.validation.methods.ui.addMsg.apply(this, [scope]);
				return;
			}, // addUI
			/* -------- Add message to field -------- */
			addMsg: function(scope){
				this.after('<span class="' + $.fn.validation.variables.field[scope].ui + '-msg">' + this.data("msg") + '</span>');
				return;
			}, // addMsg
			getMsg: function(which){
				var rule = $.fn.validation.methods.meta.getRule.apply(this, ["data-msg"]),
				    cmsg = rule.apply(this);
				if(cmsg.length){ return cmsg; }				
				if($.fn.validation.rules.types[which] && $.fn.validation.rules.types[which].msg){ return $.fn.validation.rules.types[which].msg; }
				if($.fn.validation.rules.feats[which] && $.fn.validation.rules.feats[which].msg){ return $.fn.validation.rules.feats[which].msg; }
				if($.fn.validation.rules.cust[which] && $.fn.validation.rules.cust[which].msg){ return $.fn.validation.rules.cust[which].msg; }
				if($.fn.validation.rules.spec[which] && $.fn.validation.rules.spec[which].msg){ return $.fn.validation.rules.spec[which].msg; }
				return $.fn.validation.variables.field.error.msg;
			}, // getMsg
			modMsg: function(newMsg){
				this.data("msg", newMsg);
				return;
			} // modMsg
		}, // ui

		/* ------------- WRAPPER ------------- */
		/* For direct method calls by $(this).validate("method") syntax */
		wrapper: {
			detectSupport: function(){
				$.fn.validation.methods.support.detect();
				return;
			}, // detectSupport
			addError: function(){
				return $.fn.validation.methods.ui.addUI.apply(this, ["error"]);
			}, // addError
			addValid: function(){
				return $.fn.validation.methods.ui.addUI.apply(this, ["valid"]);
			}, // addValid
			validate: function(){
				var which = (this.is("form, fieldset")) ? "form" : "field";
				return $.fn.validation.methods.validate[which].apply(this, Array.prototype.slice.call(arguments, 1));
			} //validate
		} // wrapper

	}; // methods
	/* ------------------------------ ...& Validation Rules ------------------------------ */
	$.fn.validation.rules = {
		types: {
			// button: {},
			// checkbox: {},
			// color: {},
			currency: {
				check: function(value, length){
					value = parseFloat(value.replace(/[,$]+/gi, ""));
					this.val(value);
					return (length) ? (!isNaN(value)) : true;
					//return (length) ? (value.match(/[^\d,.]/gi)) ? false : true : true;
				},
				msg: "(0-9), (,), or (.) only"
			}, // currency
			// date: {},
			// datetime: {},
			// 'datetime-local': {},
			email: {
				check: function(value, length){
					return (length) ? (value.match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/)) : true;
				}
			}, // email
			// file: {},
			// hidden: {},
			// image: {},
			// month: {},
			number: {
				check: function(value, length){
					value = parseFloat(value.replace(/[,$]+/gi, ""));
					this.val(value);
					return (length) ? (!isNaN(value)) : true;
					//return (length) ? (value.match(/[^\d.]/gi)) ? false : true : true;
				},
				msg: "0-9 only."
			}, // number
			// password: {},
			// radio: {},
			// range: {},
			// reset: {},
			// search: {},
			// select: {},
			// submit: {},
			tel: {
				check: function(value, length){
					return (length) ? (value.match(/^(([(]?(\d){3}[)]?)([\s-.]?(\d){3}[\s-.]?)([\s-]?(\d){4}[\s-.]?))$/)) : true;
				}
			}, // tel
			// text: {},
			// time: {},
			// url: {},
			// week: {},
			year: {
				check: function(value, length){
					return (length) ? (value.match(/^(\d){4}$/)) : true;
				}
			} // year
		}, // types
		feats: {
			// autocomplete: {},
			// autofocus: {},
			// height: {},
			// list: {},
			max: {
				check: function(value, length, testValue){
					value = parseFloat(value);
					testValue = parseFloat(testValue);
					return (length) ? (value > testValue) ? false : true : true;
				},
				msg: "Too high"
			}, // max
			maxlength: {
				check: function(value, length, testValue){
					return (length) ? (length > testValue) ? false : true : true;
				},
				msg: "Too long"
			}, // maxlength
			min: {
				check: function(value, length, testValue){
					value = parseFloat(value);
					testValue = parseFloat(testValue);
					return (length) ? (value < testValue) ? false : true : true;
				},
				msg: "Too low"
			}, // min
			// multiple: {},
			// novalidate: {},
			pattern: {
				check: function(value, length, testValue){
					return (length) ? (value.match(testValue)) : true;
				}
			},
			// placeholder: {},
			// readonly: {},
			required: {
				check: function(value, length, testValue){
					return (length);
				},
				msg: "Required"
			},
			// size: {},
			step: {
				check: function(value, length, testValue){
					value = parseFloat(value);
					testValue = parseFloat(value);
					return (length) ? ((value % testValue) === 0) : true;
				},
				msg: "Invalid multiple"
			}//, // step
			// width: {}
		}, // feats
		cust: {	
			'data-range': {
				check: function(value, length){
					var groupId = this.attr("data-group"),
					    group = this.closest("form").find('*[data-group="'+groupId+'"]'),
					    which = this.attr("data-range"),
					    minField = group.filter('*[data-range="0"]'),
					    maxField = group.filter('*[data-range="1"]'),
					    min,
					    max;
					if(which == 1){
						min = parseFloat(minField.val());
						max = parseFloat(maxField.val());
						return (min <= max);
					}else{
						return true;
					}
				}, // check
				msg: "Invalid range"
			}
		}, // cust
		spec: {
			'data-default': {
				check: function(value, length){
					var dfault = this.attr("data-default");
					$.fn.validation.methods.ui.clearUI.apply(this);
					if(dfault){
						if(!length || (value === dfault)){
							this.val(dfault);
							return true;
						}
					}
					return false;
				} // check
			},
			'data-msg': {
				check: function(){
					var cmsg = this.attr("data-msg");
					return (cmsg) ? cmsg : '';
				} // check
			}
			// 'data-group': {},
		} // spec
	};
	/* ------------------------------ ...& Defaults ------------------------------ */
	$.fn.validation.defaults = {
		/* ----- Field Options ----- */
		field: {
			error: {
				ui: "ui-err-field",
				msg: "Invalid"
			}, // error
			valid: {
				ui: "ui-val-field",
				msg: "Valid"
			} // valid
		} // field
	}; // defaults
	$.fn.validation.variables = {};
	/* ------------------------------ ...& Reference ------------------------------ */
	$.fn.validation.reference = {
		/* ----- Input Types ----- */
		types: [
			"button",
			"checkbox",
			"color",
			"currency", // mine, not html5
			"date",
			"datetime",
			"datetime-local",
			"email",
			"file",
			"hidden",
			"image",
			"month",
			"number",
			"password",
			"radio",
			"range",
			"reset",
			"search",
			"submit",
			"tel",
			"text",
			"time",
			"url",
			"week",
			"year" // mine, not html5
		], // types
		/* ----- Input Features ----- */
		feats: [
			"autocomplete",
			"autofocus",
			"height",
			"list",
			"max",
			"maxlength",
			"min",
			"multiple",
			"novalidate",
			"pattern",
			"placeholder",
			"readonly",
			"required",
			"size",
			"step",
			"width"
		], // feats
		cust: [
			"data-range" // custom html5 data- attr
		],
		spec: [
			"data-default", // custom html5 data- attr
			"data-msg", // custom html5 data- attr
			"data-group" // custom html5 data- attr
		]
	}; // reference
})(jQuery);