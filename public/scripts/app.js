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

		$scope.submit = function() {
			var inputData = {'input': $scope.choice_input};
			console.log(inputData);
			$scope.submitting = true;

			$http.post('/data/input/draft', inputData).finally(function() {
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
				response.data.players.forEach(function(player) {
					$scope.players[player.team_key] = player;
				});

				$scope.game_data.currentChoice = 1;
				$scope.game_data.userChoice = null;
				$scope.game_data.currentDraft = [];
				$scope.game_data.currentDraft.length = response.data.game_data.length;
				response.data.game_data.forEach(function(drafter) {
					if( drafter.draft_pick != null )
					{
						$scope.game_data.currentDraft[drafter.draft_pick - 1] = drafter.team_key;
						if( drafter.draft_choice >= $scope.game_data.currentChoice )
						{
							$scope.game_data.currentChoice = drafter.draft_choice + 1;
						}
					}

					if (drafter.team_key === $scope.user.team_key)
					{
						$scope.game_data.userChoice = drafter.draft_choice;
					}
				});
				console.log($scope.game_data.currentDraft);
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