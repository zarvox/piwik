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
use Exception;

/**
 * Defines a new widget. You can create a new widget using the console command `./console generate:widget`.
 * The generated widget will guide you through the creation of a widget.
 *
 * For an example, see {@link https://github.com/piwik/piwik/blob/master/plugins/ExamplePlugin/Widgets/MyExampleWidget.php}
 *
 * @api since Piwik 2.15
 */
class Widget
{
    /**
     * @param WidgetConfig $config
     * @api
     */
    public static function configure(WidgetConfig $config)
    {
    }

    /**
     * @return string
     */
    public function render()
    {
        return '';
    }

}
