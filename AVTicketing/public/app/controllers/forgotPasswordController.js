angular
	.module('avTicketing')
	.controller('forgotPasswordController', ['dbService', '$mdDialog', function (dbService, $mdDialog) {
		var vm = this;
		vm.sendLink = function () {
			if(vm.username !== undefined && vm.username.length > 1)
				dbService.sendPasswordResetLink(vm.username).then(function (response) {
					$mdDialog.show(
						$mdDialog.alert({
							title: response.type,
							textContent: response.response,
							ok: "OK"
						})
					);
				});
		};
	}]);