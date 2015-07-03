/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Example:
 * <div piwik-widget-loader="{module: '', action: '', ...}"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetLoader', piwikWidgetLoader);

    piwikWidgetLoader.$inject = ['piwik', 'piwikUrl', '$http', '$compile', '$q'];

    function piwikWidgetLoader(piwik, piwikUrl, $http, $compile, $q){
        return {
            restrict: 'A',
            transclude: true,
            scope: {
                piwikWidgetLoader: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/widget-loader/widgetloader.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {
                    var changeCounter = 0,
                        currentScope,
                        currentElement,
                        httpCanceler,
                        contentNode = element.find('.theWidgetContent');

                    var cleanupLastWidgetContent = function() {
                        if (currentElement) {
                            currentElement.remove();
                            currentElement = null;
                        }
                        if (currentScope) {
                            currentScope.$destroy();
                            currentScope = null;
                        }
                    };

                    var abortHttpRequestIfNeeded = function () {
                        if (httpCanceler) {
                            httpCanceler.resolve();
                            httpCanceler = null;
                        }
                    }

                    function getFullWidgetUrl(parameters) {

                        var url = $.param(parameters);

                        var idSite  = piwikUrl.getSearchParam('idSite');
                        var period  = piwikUrl.getSearchParam('period');
                        var date    = piwikUrl.getSearchParam('date');
                        var segment = piwikUrl.getSearchParam('segment');

                        url += '&idSite=' + idSite + '&period=' + period;
                        url += '&date=' + date + '&random=' + parseInt(Math.random() * 10000);

                        if (segment) {
                            url += '&segment=' + segment;
                        }

                        return '?' + url;
                    }

                    function loadWidgetUrl(parameters, thisChangeId)
                    {
                        scope.loading = true;

                        var url = getFullWidgetUrl(parameters);

                        abortHttpRequestIfNeeded();
                        cleanupLastWidgetContent();

                        httpCanceler = $q.defer();

                        $http.get(url, {timeout: httpCanceler.promise}).success(function(response) {
                            if (thisChangeId !== changeCounter || !response) {
                                // another widget was requested meanwhile, ignore this response
                                return;
                            }

                            httpCanceler = null;

                            var newScope = scope.$new();
                            currentScope = newScope;

                            scope.loading = false;
                            scope.loadingFailed = false;

                            currentElement = contentNode.html(response).children();
                            $compile(currentElement)(newScope);

                        }).error(function () {
                            if (thisChangeId !== changeCounter) {
                                // another widget was requested meanwhile, ignore this response
                                return;
                            }

                            httpCanceler = null;

                            cleanupLastWidgetContent();

                            scope.loading = false;
                            scope.loadingFailed = true;
                        });
                    }

                    scope.$watch('piwikWidgetLoader', function (parameters, oldUrl) {
                        if (parameters) {
                            loadWidgetUrl(parameters, ++changeCounter);
                        }
                    });

                    element.on('$destroy', function() {
                        abortHttpRequestIfNeeded();
                    });
                };
            }
        };
    }
})();