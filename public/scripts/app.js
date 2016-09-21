angular.module('leagueApp', ['ngMaterial'])
	.config(function($mdThemingProvider){
		$mdThemingProvider.theme('default')
			.primaryPalette('indigo')
			.accentPalette('teal');
	})
	.controller('AppCtrl', function($scope, $mdSidenav, $http, $window ) {
		$scope.activated = false;
		$scope.submitting = false;
		$scope.game_data = {};
		$scope.players = {};
		$scope.choice_input = undefined;

		$scope.toggleLeft = function() {
			$mdSidenav('left').toggle();
		};

		$scope.selectPlayer = function(player) {
			if ( !$scope.game_data.selected_player || player.player_key !== $scope.game_data.selected_player.player_key )
			{
				$scope.game_data.selected_player = player;
				$scope.choice_input = player.player_key;
			}
		};

		$scope.submit = function() {
			var inputData = {'input': $scope.choice_input};
			console.log(inputData);
			$scope.submitting = true;

			$http.post('/data/input/survivor', inputData).finally(function() {
				$window.location.reload(true);
			});
		}

		$http.get('/data/user').then(function successCallback(response)
		{
			console.log('SUCCESS', response);
			$scope.user = response.data;
		}, function errorCallback (response)
		{
			console.log('ERROR', response);
		}).then( function () {
			if (!$scope.user.loggedIn )
			{
				return;
			}
			return $http.get('/data/game').then(function successCallback(response)
			{
				console.log('SUCCESS', response);
				$scope.game_data.roster = response.data.players;
				if ( response.data.selected_player != null )
				{
					$scope.game_data.roster.forEach(function(player) {
						if ( player.player_key === response.data.selected_player )
						{
							$scope.game_data.selected_player = player;
						}
					});
				}
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