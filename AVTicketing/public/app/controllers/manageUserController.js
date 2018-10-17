angular
	.module('avTicketing')
	.controller('manageUserController', ['$stateParams', 'userData', 'roles', 'dbService', function ($stateParams, userData, roles, dbService) {
		var vm = this;
		vm.user = userData;
		vm.roles = roles;

		vm.addRole = function () {
			dbService.addRoleToUser(vm.user.id, vm.addUserRole.id).then(function (response) {
				vm.user = response;
			});
			vm.addUserRole = undefined;
		};
		vm.removeRole = function (role) {
			dbService.removeRoleFromUser(vm.user.id, role.id).then(function (response) {
				vm.user = response;
			})
		};
		vm.alreadyAdded = function (item) {
			for(var i = 0; i < vm.user.roles.length; i++){
				if(vm.user.roles[i].display_name == item.display_name){
					return false;
				}
			}
			return true;
		};
		vm.getUserRoleText = function () {
			if (vm.addUserRole !== undefined) {
				return vm.addUserRole.display_name;
			} else {
				return "Add Role To User";
			}
		}
	}]);