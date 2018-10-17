angular
	.module('avTicketing')
	.controller('adminController', ['dbService', '$state', 'authorizationService', function (dbService, $state, authorizationService) {
		var vm = this;
		vm.syncWithSlack = function () {
			dbService.syncWithSlack();
		};

		var roles = JSON.parse(localStorage.getItem('roles'));
		if(!authorizationService.isAdmin()){
			$state.go('/');
		}

	}]);