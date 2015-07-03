<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Category;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Widget\WidgetConfig;

/**
 * Base type for metric metadata classes that describe aggregated metrics. These metrics are
 * computed in the backend data store and are aggregated in PHP when Piwik archives period reports.
 *
 * Note: This class is a placeholder. It will be filled out at a later date. Right now, only
 * processed metrics can be defined this way.
 */
class Subcategory
{
    protected $categoryId = '';

    // name and id are usually the same and just a translation key. The name is used in the menu, the id in the url
    protected $name = '';
    protected $id = '';

    /**
     * @var WidgetConfig[]
     */
    protected $widgets = array();

    protected $order = 99;

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function getWidgetConfigs()
    {
        return $this->widgets;
    }

    public function addWidgetConfig(WidgetConfig $widget)
    {
        $this->widgets[] = $widget;
    }

    public function getName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        return $this->id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}