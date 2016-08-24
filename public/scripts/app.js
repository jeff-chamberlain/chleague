angular.module('leagueApp', ['ngMaterial'])
	.config(function($mdThemingProvider){
		$mdThemingProvider.theme('default')
			.primaryPalette('indigo')
			.accentPalette('teal');
	})
	.controller('AppCtrl', function($scope, $timeout, $mdSidenav , $http) {
		$scope.activated = false;

		$scope.toggleLeft = function() {
			$mdSidenav('left').toggle();
		};

		$http.get('/data/user').then(function successCallback(response)
		{
			console.log('SUCCESS', response);
			$scope.user = response.data;
		}, function errorCallback (response)
		{
			console.log('ERROR', response);
		}).then( function () {
			return $http.get('/data/game').then(function successCallback(response)
			{
				console.log('SUCCESS', response);
				$scope.game = response.data;
			}, function errorCallback (response)
			{
				console.log('ERROR', response);
			})
		}).then( function () {
			console.log('ACTIVATED', $scope );
			$scope.activated = true;
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