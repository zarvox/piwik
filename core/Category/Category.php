<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Category;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;

/**
 * Base type for metric metadata classes that describe aggregated metrics. These metrics are
 * computed in the backend data store and are aggregated in PHP when Piwik archives period reports.
 *
 * Note: This class is a placeholder. It will be filled out at a later date. Right now, only
 * processed metrics can be defined this way.
 */
class Category
{
    // name and id are usually the same and just a translation key. The name is used in the menu, the id in the url
    protected $id = '';
    protected $name = '';

    /**
     * @var Subcategory[]
     */
    protected $subcategories = array();

    protected $order = 99;

    public function getOrder()
    {
        return $this->order;
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

    public function addSubcategory(Subcategory $subcategory)
    {
        $this->subcategories[$subcategory->getId()] = $subcategory;
    }

    public function hasSubcategory($subcategoryId)
    {
        return isset($this->subcategories[$subcategoryId]);
    }

    public function getSubcategory($subcategoryId)
    {
        if ($this->hasSubcategory($subcategoryId)) {
            return $this->subcategories[$subcategoryId];
        }
    }

    public function getSubcategories()
    {
        return $this->subcategories;
    }

    public function hasSubCategories()
    {
        return !empty($this->subcategories);
    }
}