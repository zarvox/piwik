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
use Piwik\Plugin;

class Categories
{
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /** @return \Piwik\Category\Category[] */
    private function getAllCategories()
    {
        $categories = $this->pluginManager->findMultipleComponents('Categories', '\\Piwik\\Category\\Category');

        $instances = array();
        foreach ($categories as $category) {
            $cat = StaticContainer::get($category);
            $instances[$cat->getId()] = $cat;
        }

        return $instances;
    }

    /** @return \Piwik\Category\Subcategory[] */
    private function getAllSubcategories()
    {
        $subcategories = array();

        Piwik::postEvent('Subcategory.addSubcategories', array(&$subcategories));

        $classes = $this->pluginManager->findMultipleComponents('Categories', '\\Piwik\\Category\\Subcategory');

        foreach ($classes as $subcategory) {
            $subcategories[] = StaticContainer::get($subcategory);
        }

        return $subcategories;
    }

    /**
     * @return \Piwik\Category\Category[] indexed by categoryId
     */
    public function getAllCategoriesWithSubcategories()
    {
        $categories    = $this->getAllCategories();
        $subcategories = $this->getAllSubcategories();

        // move subcategories into categories
        foreach ($subcategories as $subcategory) {
            $categoryId = $subcategory->getCategoryId();

            if (!$categoryId) {
                continue;
            }

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = new Category();
                $categories[$categoryId]->setId($categoryId);
            }

            $categories[$categoryId]->addSubcategory($subcategory);
        }

        return $categories;
    }
}