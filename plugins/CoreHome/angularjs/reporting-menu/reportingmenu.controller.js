/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingMenuController', ReportingMenuController);

    ReportingMenuController.$inject = ['$scope', 'piwik', '$location', '$timeout', 'reportingMenuModel', '$rootScope'];

    function ReportingMenuController($scope, piwik, $location, $timeout, menuModel, $rootScope){
        function markAllCategoriesAsInactive()
        {
            angular.forEach(menuModel.menu, function (cat) {
                cat.active = false;
                cat.hover = false;
                angular.forEach(cat.subcategories, function (subcat) {
                    subcat.active = false;
                });
            });
        }

        function getUrlParam(param)
        {
            var value = piwik.broadcast.getValueFromHash(param);
            if (!value) {
                value = piwik.broadcast.getValueFromUrl(param);
            }
            return value;
        }

        $scope.menuModel = menuModel;

        var timeoutPromise = null;

        $scope.enterCategory = function (category) {

            if (timeoutPromise) {
                $timeout.cancel(timeoutPromise);
            }

            angular.forEach(menuModel.menu, function (cat) {
                cat.hover = false;
            });

            category.hover = true;
        };

        $scope.leaveCategory = function (category) {

            if (timeoutPromise) {
                $timeout.cancel(timeoutPromise);
            }

            angular.forEach(menuModel.menu, function (cat) {
                if (!cat.active) {
                    cat.hover = false;
                }
            });

            timeoutPromise = $timeout(function () {
                angular.forEach(menuModel.menu, function (cat) {
                    if (cat.active) {
                        cat.hover = true;
                    }
                });
            }, 2000);
        };

        $scope.enterSubcategory = function (category, subcategory) {
            if (!category || !subcategory) {
                return;
            }

            markAllCategoriesAsInactive();

            category.active = true;
            category.hover = true;
            subcategory.active = true;
        };

        var idSite = getUrlParam('idSite');
        var period = getUrlParam('period');
        var date   = getUrlParam('date');

        $scope.makeUrl = function (category, subcategory) {
            return 'idSite=' + idSite + '&period=' + period + '&date=' + date + '&category=' + category.id + '&subcategory=' + subcategory.id;
        }

        $scope.loadSubcategory = function (category, subcategory) {
            if (subcategory && subcategory.active) {
                // this menu item is already active, a location change success would not be triggered,
                // instead trigger an event
                $rootScope.$emit('loadPage', category.id, subcategory.id);
            }
        };

        menuModel.fetchMenuItems().then(function (menu) {
            if (!$location.search().subcategory) {
                $scope.enterSubcategory(menu[0], menu[0].subcategories[0]);
                $location.search($scope.makeUrl(menu[0], menu[0].subcategories[0]));
            }
        });

        $rootScope.$on('$locationChangeSuccess', function () {
            var category    = $location.search().category;
            var subcategory = $location.search().subcategory;

            if (!category || !subcategory) {
                return;
            }

            var found = menuModel.findSubcategory(category, subcategory);
            $scope.enterSubcategory(found.category, found.subcategory);
        });

    }
})();
