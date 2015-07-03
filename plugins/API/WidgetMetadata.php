<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Category\Categories;
use Piwik\Piwik;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Category\Category;
use Piwik\Category\Subcategory;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

class WidgetMetadata
{
    private $categories;

    public function __construct(Categories $categories)
    {
        $this->categories = $categories;
    }

    public function getPagesMetadata(WidgetsList $widgetsList)
    {
        $categories  = $this->moveWidgetsIntoCategories($widgetsList->getWidgetConfigs());
        $categories  = $this->buildPagesMetadata($categories);

        return $categories;
    }

    public function getWidgetMetadata(WidgetsList $widgetsList, $deep)
    {
        $flat = array();

        $categories = $this->moveWidgetsIntoCategories($widgetsList->getWidgetConfigs());

        foreach ($widgetsList->getWidgetConfigs() as $widgetConfig) {

            /** @var WidgetConfig[] $widgets */
            $widgets = array($widgetConfig);
            if ($widgetConfig instanceof WidgetContainerConfig) {
                $widgets = array_merge($widgets, $widgetConfig->getWidgetConfigs());
            }

            foreach ($widgets as $widget) {
                if (!$widget->isWidgetizeable() || !$widget->getName()) {
                    continue;
                }

                $category    = null;
                $subcategory = null;

                if (isset($categories[$widget->getCategoryId()])) {
                    $category    = $categories[$widget->getCategoryId()];
                    $subcategory = $category->getSubcategory($widget->getSubcategoryId());
                }

                $flat[] = $this->buildWidgetMetadata($widget, $category, $subcategory, $deep);
            }
        }

        usort($flat, array($this, 'sortWidgets'));

        return $flat;
    }

    public function buildWidgetMetadata(WidgetConfig $widget, $category, $subcategory, $addNestedWidgets = true)
    {
        $category = $category ? $this->buildCategoryMetadata($category) : null;
        $subcategory = $subcategory ? $this->buildSubcategoryMetadata($subcategory) : null;

        $item = array(
            'name'        => Piwik::translate($widget->getName()),
            'category'    => $category,
            'subcategory' => $subcategory,
            'module'      => $widget->getModule(),
            'action'      => $widget->getAction(),
            'order'       => $widget->getOrder(),
            'parameters'  => $widget->getParameters(),
            'uniqueId'    => $widget->getUniqueId(),
        );

        if ($widget instanceof ReportWidgetConfig) {
            $item['viewDataTable'] = $widget->getDefaultView();
            $item['isReport'] = true;
        }

        if ($widget instanceof WidgetContainerConfig) {
            $item['layout'] = $widget->getLayout();
            $item['isContainer'] = true;

            if ($addNestedWidgets) {
                $children = array();
                foreach ($widget->getWidgetConfigs() as $widgetConfig) {
                    $cat = $this->createCategory($widgetConfig->getCategoryId());
                    $subcat = $this->createSubcategory($widgetConfig->getCategoryId(), $widgetConfig->getSubcategoryId());

                    $child = $this->buildWidgetMetadata($widgetConfig, $cat, $subcat, $addNestedWidgets);
                    $children[] = $child;
                }
                $item['widgets'] = $children;
            }
        }

        return $item;
    }

    private function sortWidgets($widgetA, $widgetB) {
        $orderA = $widgetA['category']['order'];
        $orderB = $widgetB['category']['order'];

        if ($orderA === $orderB) {
            if (!empty($widgetA['subcategory']['order']) && !empty($widgetB['subcategory']['order'])) {

                $subOrderA = $widgetA['subcategory']['order'];
                $subOrderB = $widgetB['subcategory']['order'];

                if ($subOrderA === $subOrderB) {
                    return 0;
                }

                return $subOrderA > $subOrderB ? 1 : -1;

            } elseif (!empty($orderA)) {

                return 1;
            }

            return -1;
        }

        return $orderA > $orderB ? 1 : -1;
    }

    private function buildCategoryMetadata(Category $category)
    {
        return array(
            'name'  => Piwik::translate($category->getName()),
            'order' => $category->getOrder(),
            'id'    => (string) $category->getId()
        );
    }

    private function buildSubcategoryMetadata(Subcategory $subcategory)
    {
        return array(
            'name'  => Piwik::translate($subcategory->getName()),
            'order' => $subcategory->getOrder(),
            'id'    => (string) $subcategory->getId()
        );
    }

    /**
     * @param WidgetConfig[] $widgetConfigs
     * @return Category[]
     */
    private function moveWidgetsIntoCategories($widgetConfigs)
    {
        $all = $this->categories->getAllCategoriesWithSubcategories();

        // move reports into categories/subcategories and create missing ones if needed
        foreach ($widgetConfigs as $widgetConfig) {
            $category    = $widgetConfig->getCategoryId();
            $subcategory = $widgetConfig->getSubcategoryId();

            if (!$category) {
                continue;
            }

            if ($widgetConfig instanceof WidgetContainerConfig && !$widgetConfig->getWidgetConfigs()) {
                // if a container does not contain any widgets, ignore it
                continue;
            }

            if (!isset($all[$category])) {
                $all[$category] = $this->createCategory($category);
            }

            if (!$subcategory) {
                continue;
            }

            if (!$all[$category]->hasSubcategory($subcategory)) {
                $all[$category]->addSubcategory($this->createSubcategory($category, $subcategory));
            }

            $all[$category]->getSubcategory($subcategory)->addWidgetConfig($widgetConfig);
        }

        return $all;
    }

    private function createCategory($categoryId)
    {
        $category = new Category();
        $category->setId($categoryId);
        return $category;
    }

    private function createSubcategory($categoryId, $subcategoryId)
    {
        $subcategory = new Subcategory();
        $subcategory->setCategoryId($categoryId);
        $subcategory->setId($subcategoryId);
        return $subcategory;
    }

    /**
     * @param Category[] $categories
     * @return array
     */
    private function buildPagesMetadata($categories)
    {
        // todo should be sorted by order
        $pages = array();

        foreach ($categories as $category) {
            foreach ($category->getSubcategories() as $subcategory) {
                $page = $this->buildPageMetadata($category, $subcategory);

                if (!empty($page['widgets'])) {
                    $pages[] = $page;
                }
            }
        }

        return $pages;
    }

    private function buildPageMetadata(Category $category, Subcategory $subcategory)
    {
        $ca = array(
            'uniqueId' => $category->getName() . '.' . $subcategory->getName(),
            'category' => $this->buildCategoryMetadata($category),
            'subcategory' => $this->buildSubcategoryMetadata($subcategory),
            'widgets' => array()
        );

        foreach ($subcategory->getWidgetConfigs() as $config) {
            $widget = $this->buildWidgetMetadata($config, $cat = null, $subcat = null);
            unset($widget['category']);
            unset($widget['subcategory']);

            $ca['widgets'][] = $widget;
        }

        return $ca;
    }

}