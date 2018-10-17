angular
	.module('avTicketing')
	.controller('registerUserController', ['dbService', 'roles', '$mdDialog', function (dbService, roles, $mdDialog) {
		var vm = this;
		vm.roles = roles;
		vm.saveUser = function () {
			dbService.register(vm.email, vm.password, vm.firstName, vm.lastName, vm.role.id).then(function (response) {
				$mdDialog.show($mdDialog.alert({
					title: "User has been inserted",
					textContent: "User has been successfully inserted",
					ok: "OK"
				}));
			});
		};

		vm.getUserRoleText = function () {
			return vm.role == null ? "Role" : vm.role.display_name;
		}
	}]);