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
use Piwik\Plugin\Manager as PluginManager;

/**
 * Defines a new widget. You can create a new widget using the console command `./console generate:widget`.
 * The generated widget will guide you through the creation of a widget.
 *
 * For an example, see {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets/MyExampleWidget.php}
 *
 * @api since Piwik 2.15
 */
class WidgetContainerConfig extends WidgetConfig
{
    /**
     * @var WidgetConfig[]
     */
    protected $widgets = array();
    protected $layout = '';
    protected $id;

    protected $module = 'CoreHome';
    protected $action = 'renderWidgetContainer';
    protected $isWidgetizable = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function addWidget(WidgetConfig $widget)
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * @return WidgetConfig[]
     */
    public function getWidgetConfigs()
    {
        return $this->widgets;
    }

    public function getParameters()
    {
        $params = parent::getParameters();
        $params['containerId'] = $this->getId();
        return $params;
    }

}