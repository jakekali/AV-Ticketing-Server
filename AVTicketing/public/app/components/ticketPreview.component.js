angular
	.module('avTicketing')
	.component('ticketPreviewComponent', {
		templateUrl : 'app/components/ticketPreview.component.html',
		bindings : {
			ticketData : '<'
		},
		controller : 'ticketPreviewController',
		controllerAs : 'ticketPreview'
	});