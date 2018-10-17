/* From here: http://plnkr.co/edit/FipgiTUaaymm5Mk6HIfn?p=preview */
angular
	.module('avTicketing')
	.directive("compareTo", function() {
		return {
			require: "ngModel",
			scope: {
				otherModelValue: "=compareTo"
			},
			link: function(scope, element, attributes, ngModel) {

				ngModel.$validators.compareTo = function(modelValue) {
					return modelValue == scope.otherModelValue;
				};

				scope.$watch("otherModelValue", function() {
					ngModel.$validate();
				});
			}
		};
	});