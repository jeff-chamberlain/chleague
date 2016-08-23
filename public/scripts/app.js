angular.module('leagueApp', ['ngMaterial'])
	.config(function($mdThemingProvider){
		$mdThemingProvider.theme('default')
			.primaryPalette('indigo')
			.accentPalette('teal');
	})
	.controller('AppCtrl', function($scope, $timeout, $mdSidenav , $http) {
		$scope.toggleLeft = function() {
			$mdSidenav('left').toggle();
		};

		$http.get('/data/user').then(function successCallback(response)
		{
			console.log('SUCCESS', response);
		}, function errorCallback (reseponse)
		{
			console.log('ERROR', response);
		});
	})
	.directive( 'goClick', function ( $window ) {
	  return function ( scope, element, attrs ) {
	    var path;

	    attrs.$observe( 'goClick', function (val) {
	      path = val;
	    });

	    element.bind( 'click', function () {
	      scope.$apply( function () {
	        $window.location.href = path;
	      });
	    });
	  };
	});