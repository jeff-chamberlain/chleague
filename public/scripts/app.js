angular.module('leagueApp', ['ngMaterial'])
	.config(function($mdThemingProvider){
		$mdThemingProvider.theme('default')
			.primaryPalette('indigo')
			.accentPalette('teal');
	})
	.controller('AppCtrl', function($scope, $timeout, $mdSidenav ) {
		$scope.toggleLeft = function() {
			$mdSidenav('left').toggle();
		};
	})
	.controller('ListCtrl', function($scope) {
		$scope.doPrimaryAction = function(event) {
			window.location.replace('/logout');
		};
	});