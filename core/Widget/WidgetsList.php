<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;

use Piwik\Cache as PiwikCache;
use Piwik\Container\StaticContainer;
use Piwik\Development;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Report\ReportWidgetFactory;

/**
 * Manages the global list of reports that can be displayed as dashboard widgets.
 *
 * Reports are added as dashboard widgets through the {@hook WidgetsList.addWidgets}
 * event. Observers for this event should call the {@link add()} method to add reports.
 *
 * @api
 */
class WidgetsList
{
    /**
     * List of widgets
     *
     * @var WidgetConfig[]
     */
    private $widgets = array();

    /**
     * @var WidgetContainerConfig[]
     */
    private $container;

    /**
     * @var array
     */
    private $containerWidgets;

    public function addWidget(WidgetConfig $widget)
    {
        $this->checkIsValidWidget($widget);

        $this->widgets[] = $widget;
    }

    public function addContainer(WidgetContainerConfig $containerWidget)
    {
        $widgetId = $containerWidget->getId();

        $this->container[$widgetId] = $containerWidget;
        $this->widgets[] = $containerWidget;

        // widgets were added to this container, but the container did not exist yet.
        if (isset($this->containerWidgets[$widgetId])) {
            foreach ($this->containerWidgets[$widgetId] as $widget) {
                $containerWidget->addWidget($widget);
            }
            unset($this->containerWidgets[$widgetId]);
        }
    }

    public function addWidgets($widgets)
    {
        foreach ($widgets as $widget) {
            if ($widget instanceof WidgetContainerConfig) {
                $this->addContainer($widget);
            } else {
                $this->addWidget($widget);
            }
        }
    }

    public function getWidgets()
    {
        return $this->widgets;
    }

    private function checkIsValidWidget(WidgetConfig $widget)
    {
        if (!Development::isEnabled()) {
            return;
        }

        if (!$widget->getModule()) {
            Development::error('No module is defined for added widget having name "' . $widget->getName());
        }

        if (!$widget->getAction()) {
            Development::error('No action is defined for added widget having name "' . $widget->getName());
        }
    }

    public function addToContainerWidget($containerId, WidgetConfig $widget)
    {
        if (isset($this->container[$containerId])) {
            $this->container[$containerId]->addWidget($widget);
        } else {
            if (!isset($this->containerWidgets[$containerId])) {
                $this->containerWidgets[$containerId] = array();
            }

            $this->containerWidgets[$containerId][] = $widget;
        }
    }

    /**
     * Removes one or more widgets from the widget list.
     *
     * @param string $widgetCategoryId The widget category. Can be a translation token.
     * @param string|false $widgetName The name of the widget to remove. Cannot be a
     *                                 translation token. If not supplied, the entire category
     *                                 will be removed.
     */
    public function remove($widgetCategoryId, $widgetName = false)
    {
        foreach ($this->widgets as $index => $widget) {
            if ($widget->getCategoryId() === $widgetCategoryId) {
                if (!$widgetName || $widget->getName() === $widgetName) {
                    unset($this->widgets[$index]);
                }
            }
        }
    }

    /**
     * Returns `true` if a report exists in the widget list, `false` if otherwise.
     *
     * @param string $module The controller name of the report.
     * @param string $action The controller action of the report.
     * @return bool
     */
    public function isDefined($module, $action)
    {
        foreach ($this->widgets as $widget) {
            if ($widget->getModule() === $module && $widget->getAction() === $action) {
                return true;
            }
        }

        return false;
    }

    public static function get()
    {
        $list = new static;

        Piwik::postEvent('Widgets.addWidgets', array($list));

        /** @var Widgets $widgets */
        $widgets = StaticContainer::get(__NAMESPACE__ . '\Widgets');

        $widgetContainerConfigs = $widgets->getWidgetContainerConfigs();
        foreach ($widgetContainerConfigs as $config) {
            if ($config->isEnabled()) {
                $list->addContainer($config);
            }
        }

        $widgetConfigs = $widgets->getWidgetConfigs();
        foreach ($widgetConfigs as $widget) {
            if ($widget->isEnabled()) {
                $list->addWidget($widget);
            }
        }

        $reports = Report::getAllReports();
        foreach ($reports as $report) {
            if ($report->isEnabled()) {
                $factory = new ReportWidgetFactory($report);
                $report->configureWidgets($list, $factory);
            }
        }

        Piwik::postEvent('Widgets.filterWidgets', array($list));

        return $list;
    }

    /**
     * CAUTION! If you ever change this method, existing updates will fail as they currently use that method!
     * If you change the output the uniqueId for existing widgets would not be found anymore
     *
     * Returns the unique id of an widget with the given parameters
     *
     * @param $controllerName
     * @param $controllerAction
     * @param array $customParameters
     * @return string
     */
    public static function getWidgetUniqueId($controllerName, $controllerAction, $customParameters = array())
    {
        $widgetUniqueId = 'widget' . $controllerName . $controllerAction;

        foreach ($customParameters as $name => $value) {
            if (is_array($value)) {
                // use 'Array' for backward compatibility;
                // could we switch to using $value[0]?
                $value = 'Array';
            }
            $widgetUniqueId .= $name . urlencode($value);
        }

        return $widgetUniqueId;
    }

}
