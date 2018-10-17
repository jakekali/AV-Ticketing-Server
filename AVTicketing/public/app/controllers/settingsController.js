angular.module('avTicketing')
	.controller('settingsController', ['Upload', '$mdDialog', '$http', 'dbService', 'settings', 'userData', function (Upload, $mdDialog, $http, dbService, settings, userData) {
		var vm = this;
		vm.settings = {};
		vm.userData = userData;

		for(var i = 0; i < settings.length; i++){
			vm.settings[settings[i].name] = settings[i].pivot.value;
		}

		vm.uploadSchedule = function (file) {
			if(file == null){
				$mdDialog.show($mdDialog.alert({
					title: 'File Upload Error',
					textContent: 'Uploaded file must be a CSV.',
					ok: 'OK'
				}));
			}
			else{
				Upload.upload({
					url: 'api/uploadSchedule',
					data: {schedule: file},
					method: 'POST'
				}).then(function (response) {
					if(response.data == ""){
						$mdDialog.show($mdDialog.alert({
							title: 'File Uploaded',
							textContent: 'Schedule has been uploaded successfully.',
							ok: 'OK'
						}));
					}
				});
			}
		};

		vm.downloadSchedule = function () {
			$http({
				'method': 'GET',
				'url' : 'api/exportSchedule'
			}).then(function (response) {
				var file = new File([response.data], "Schedule.ics", {type: "text/calendar;charset=utf-8"});
				saveAs(file);
			})
		};

		vm.updateSettings = function () {
			dbService.updateSettings(vm.settings).then(function (response) {
				console.log(response);
			});
		};

		vm.updatePassword = function () {
			if(vm.currentPassword !== undefined && vm.newPassword !== undefined && vm.newPasswordConfirmation !== undefined){
				dbService.updatePassword(vm.currentPassword, vm.newPassword).then(function (response) {
					$mdDialog.show(
						$mdDialog.alert({
							title: response.type,
							textContent : response.result,
							ok : 'OK'
						})
					);
				});
			}
		};

		vm.updateUserInfo = function () {
			if(vm.userData.email !== undefined && vm.userData.firstName !== undefined && vm.userData.lastName !== undefined){
				dbService.updateUserInfo(vm.userData.email, vm.userData.firstName, vm.userData.lastName);
			}
		};
	}]);