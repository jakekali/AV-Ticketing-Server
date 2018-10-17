angular
	.module('avTicketing')
	.controller('navController', ['$state', '$rootScope', '$auth', 'authorizationService', function($state, $rootScope, $auth, authorizationService){
		var vm = this;
		vm.currNavItem = $state.current.name;

		$rootScope.$on("$stateChangeSuccess", function(event, toState, toParams, fromState, fromParams) {
			if (toState.name != "") {
				vm.currNavItem = $state.current.name;
			}
		});

		$rootScope.$on("$stateChangeError", function(event, toState, toParams, fromState, fromParams) {
			if (toState.name != "") {
				vm.currNavItem = $state.current.name;
			}
		});

		vm.role = localStorage.role || "Unknown";

		vm.loggedIn = function(){
			return $state.current.name != 'login' && $state.current.name != 'forgotPassword' && $state.current.name != 'resetPassword';
		};

		vm.logOut = function(){
			$auth.logout();
			localStorage.removeItem("roles");
			$state.go('login');
		};

		vm.showAdmin = function () {
			return authorizationService.isAdmin();
		}
	}]);