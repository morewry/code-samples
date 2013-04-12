define(
	[
		"hgn!html/sliders",
		"hgn!html/payment",
		"backbone",
		"json2",
		"underscore",
		"jquery",
		"syphon"
	],
	function(tSliders, tPayment){
		App.View.Slider = Backbone.View.extend({

			events: {
				"change input": "updateValue",
				"submit form": "getNewRate"
			},
			
			initialize: function(){
				this.timesRendered = 1;
				this.monthly = new App.View.Slider.Payment({ model: this.model });
				this.preRender();
			},
			
			preRender: function(){				
				this.$el.html(tSliders(this.model.attributes)).append(this.monthly.$el);
				this.monthly.render();
			},
			
			render: function(){ 
				this.timesRendered++;
				$.log("Times Rendered: " + this.timesRendered);
				this.preRender();
				App.$el.content.html(this.$el);
			},

			updateValue: function(e){
				$(e.currentTarget).parents("form").submit();
			},

			getNewRate: function(e){
				e.preventDefault();
				this.model.set(Backbone.Syphon.serialize(e.currentTarget));
			}

		}); // App.View.?
		
		App.View.Slider.Payment = Backbone.View.extend({
		
				initialize: function(){
					var self = this;
					this.model.on("change:monthlyPayment", function(){
						// $.log("Monthly payment attribute (in calculator model) changed");
						self.render();
					});					
				},
				
				render: function() {
					this.$el.html(tPayment(this.model.attributes));
					// $.log("Subview render: " + this.model.get("monthlyPayment"));
				}
			
		}); // App.View.?.?
		
		return App.View.Slider;
	} // fn
); // define