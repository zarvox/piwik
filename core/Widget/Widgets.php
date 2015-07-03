<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Exception;

/**
 * Defines a new widget. You can create a new widget using the console command `./console generate:widget`.
 * The generated widget will guide you through the creation of a widget.
 *
 * For an example, see {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets/MyExampleWidget.php}
 *
 * @api since Piwik 2.15
 */
class Widgets
{
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return WidgetConfig[]
     */
    public function getWidgetConfigs()
    {
        $widgetClasses = $this->getAllWidgetClassNames();

        $configs = array();
        foreach ($widgetClasses as $widgetClass) {
            $configs[] = $this->getWidgetConfigForClassName($widgetClass);
        }

        return $configs;
    }

    /**
     * @return WidgetContainerConfig[]
     */
    public function getWidgetContainerConfigs()
    {
        $configs = array();

        $widgetContainerConfigs = $this->getAllWidgetContainerConfigClassNames();
        foreach ($widgetContainerConfigs as $widgetClass) {
            $configs[] = StaticContainer::get($widgetClass);
        }

        return $configs;
    }

    /**
     * @return Widget|null
     * @throws \Exception
     */
    public function factory($module, $action)
    {
        if (empty($module) || empty($action)) {
            return;
        }

        try {
            if (!$this->pluginManager->isPluginActivated($module)) {
                return;
            }

            $plugin = $this->pluginManager->getLoadedPlugin($module);
        } catch (\Exception $e) {
            // we are not allowed to use possible widgets, plugin is not active
            return;
        }

        /** @var Widget[] $widgetContainer */
        $widgets = $plugin->findMultipleComponents('Widgets', 'Piwik\\Widget\\Widget');

        foreach ($widgets as $widgetClass) {
            $config = $this->getWidgetConfigForClassName($widgetClass);
            if ($config->getAction() === $action) {
                $config->checkIsEnabled();
                return StaticContainer::get($widgetClass);
            }
        }
    }


    private function getWidgetConfigForClassName($widgetClass)
    {
        /** @var string|Widget $widgetClass */
        $config = new WidgetConfig();
        $config->setModule($this->getModuleFromWidgetClassName($widgetClass));
        $config->setAction($this->getActionFromWidgetClassName($widgetClass));
        $widgetClass::configure($config);

        return $config;
    }

    /**
     * @return string[]
     */
    private function getAllWidgetClassNames()
    {
        return $this->pluginManager->findMultipleComponents('Widgets', 'Piwik\\Widget\\Widget');
    }

    private function getModuleFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        return $parts[2];
    }

    private function getActionFromWidgetClassName($widgetClass)
    {
        $parts = explode('\\', $widgetClass);

        if (count($parts) >= 4) {
            return lcfirst(end($parts));
        }

        return '';
    }

    /**
     * @return string[]
     */
    private function getAllWidgetContainerConfigClassNames()
    {
        return $this->pluginManager->findMultipleComponents('Widgets', 'Piwik\\Widget\\WidgetContainerConfig');
    }
}
