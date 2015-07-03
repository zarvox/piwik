/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Example:
 * <div piwik-widget-by-dimension-container container="widget"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetByDimensionContainer', piwikWidgetContainer);

    piwikWidgetContainer.$inject = ['piwik'];

    function piwikWidgetContainer(piwik){
        return {
            restrict: 'A',
            scope: {
                container: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/widget-bydimension-container/widget-bydimension-container.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {

                    var widgetsByCategory = {};

                    angular.forEach(scope.container.widgets, function (widget) {
                        var category = widget.subcategory.name;
                        if (!widgetsByCategory[category]) {
                            widgetsByCategory[category] = [];
                        }
                        widgetsByCategory[category].push(widget);
                    });

                    scope.widgetsByCategory = widgetsByCategory;

                    scope.selectWidget = function (widget) {
                        scope.selectedWidget = widget;
                    }

                    if (scope.container.widgets && scope.container.widgets.length) {
                        scope.selectWidget(scope.container.widgets[0]);
                    }
                };
            }
        };
    }
})();