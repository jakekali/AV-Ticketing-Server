angular
	.module('avTicketing')
	.controller('resetPasswordController', ['dbService', 'validateToken', '$stateParams', '$state', '$mdDialog', function (dbService, validateToken, $stateParams, $state, $mdDialog) {
		var vm = this;
		if(validateToken.email === undefined){
			$mdDialog.show(
				$mdDialog.alert({
					title: 'Invalid Reset Token',
					textContent: 'Password reset token is invalid. Please request a new one.',
					ok: 'Go To Forgot Password'
				})
			).then(function () {
				$state.go('forgotPassword');
			});
		}

		vm.resetPassword = function () {
			dbService.resetPassword($stateParams.token, vm.password, vm.password_confirmation, validateToken.email)
				.then(function (response) {
					$mdDialog.show(
						$mdDialog.alert({
							title: response.type,
							htmlContent : response.response,
							ok: "OK"
						})
					);
				})
		};
	}]);