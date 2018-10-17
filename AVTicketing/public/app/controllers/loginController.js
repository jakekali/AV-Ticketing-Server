angular
	.module('avTicketing')
	.controller('loginController', ['$auth', '$mdToast', '$location', 'dbService', '$stateParams', function ($auth, $mdToast, $location, dbService, $stateParams) {
		var vm = this;
		vm.login = function () {
			$auth.login({
				"email":vm.username,
				"password":vm.password
			})
				.then(function (response) {
					vm.error = false;
					dbService.getUserRoles().then(function (response) {
						localStorage.setItem('roles', JSON.stringify(response));
						var redirectUrl = window.decodeURIComponent($stateParams.redirectURL);
						$location.path(redirectUrl);
					});
				})
				.catch(function (response) {
					switch (response.data.error){
						case 'invalid_credentials':
							vm.error = 'Username or password is incorrect.';
							break;
						default:
							vm.error = 'Error. Please try again later.';
					}
					if(response.status == 429){
						vm.error = "You have tried too many times. Wait and try again later.";
					}
					$mdToast.show(
						$mdToast.simple()
						.textContent(vm.error)
					);
				});
		};
	}]);