define(
	[
		"model/rates",
		"backbone",
		"json2",
		"underscore",
		"jquery"
	],
	function(){
		App.Model.Calculator = Backbone.Model.extend({

			defaults: {
				term: 30,
				loanAmount: 150000,
				interestRate: 3.463,
				creditScore: 700,
				creditProfile: function(){
					var cp = "poor";
					if(this.creditScore > 800){
						cp = "great";
					}
					else if(this.creditScore > 700){
						cp = "good";
					}
					else if(this.creditScore > 600){
						cp = "okay";
					}
					return cp;
				},
				propertyTax: 1.2,
				downPayment: 30000,
				monthlyPayment: 730.13
			}, // defaults

			initialize: function(){
				var iCalc = this,
				iRates = new App.Collection.Rates();
				iRates.on("reset", function(e){
					// $.log("Rates collection reset (post-fetch)");
					iCalc.set({ monthlyPayment: this.monthlyPayment });
				});
				iCalc.on("change", function(e){
					if(!this.hasChanged("monthlyPayment")){
						$.log("Calculator model changed, now fetching rates");
						iRates.fetch({
							data: this.attributes, 
							processData: true,
							success: function(m,r,o){ /* App.Event.trigger("success", m, r, o); */ }, 
							error: function(m,r,o){ /* App.Event.trigger("error", m, r, o); */ } 
						});
					}
				});				
			} // initialize

		}); // App.Model.?
		
		App.Model.Calculator.Settings = Backbone.Model.extend({

			url: function(){
				return "/cf/ajaxStub/ajaxSettings.cfm?" + $.param(this.attributes);
			},

			defaults: {
				id: '',
				downPaymentMin: 10000,
				downPaymentMax: 50000,
				loanTermMin: 1,
				loanTermMax: 30,
				loanAmountMin: 120000,
				loanAmountMax: 150000000
			}, // defaults

			initialize: function(){
			
			} // initialize

		}); // App.Model.?.?

		return App.Model.Calculator;
	} // fn
); // define