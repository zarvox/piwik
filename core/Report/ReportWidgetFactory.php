<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Report;

use Piwik\Plugin\Report;
use Piwik\Widget\WidgetContainerConfig;

/**
 * Singleton that manages user access to Piwik resources.
 *
 * To check whether a user has access to a resource, use one of the {@link Piwik Piwik::checkUser...}
 * methods.
 *
 * In Piwik there are four different access levels:
 *
 * - **no access**: Users with this access level cannot view the resource.
 * - **view access**: Users with this access level can view the resource, but cannot modify it.
 * - **admin access**: Users with this access level can view and modify the resource.
 * - **Super User access**: Only the Super User has this access level. It means the user can do
 *                          whatever he/she wants.
 *
 *                          Super user access is required to set some configuration options.
 *                          All other options are specific to the user or to a website.
 *
 * Access is granted per website. Uses with access for a website can view all
 * data associated with that website.
 *
 * @api
 */
class ReportWidgetFactory
{
    /**
     * @var Report
     */
    private $report  = null;

    public function __construct(Report $report)
    {
        $this->setReport($report);
    }

    private function setReport($report)
    {
        $this->report = $report;
    }

    public function createContainerWidget($containerId)
    {
        $widget = new WidgetContainerConfig();
        $widget->setCategoryId($this->report->getCategoryId());
        $widget->setId($containerId);

        if ($this->report->getSubcategoryId()) {
            $widget->setSubcategoryId($this->report->getSubcategoryId());
        }

        $orderThatListsReportsAtTheEndOfEachCategory = 100 + $this->report->getOrder();
        $widget->setOrder($orderThatListsReportsAtTheEndOfEachCategory);

        return $widget;
    }

    public function createWidget()
    {
        $widget = new ReportWidgetConfig();
        $widget->setName($this->report->getName());
        $widget->setCategoryId($this->report->getCategoryId());

        if ($this->report->getDefaultTypeViewDataTable()) {
            $widget->setDefaultView($this->report->getDefaultTypeViewDataTable());
        }

        if ($this->report->getSubcategoryId()) {
            $widget->setSubcategoryId($this->report->getSubcategoryId());
        }

        $widget->setModule($this->report->getModule());
        $widget->setAction($this->report->getAction());

        $orderThatListsReportsAtTheEndOfEachCategory = 100 + $this->report->getOrder();
        $widget->setOrder($orderThatListsReportsAtTheEndOfEachCategory);

        $parameters = $this->report->getParameters();
        if (!empty($parameters)) {
            $widget->setParameters($parameters);
        }

        return $widget;
    }

    public function createCustomWidget($action)
    {
        $widget = $this->createWidget();
        $widget->setDefaultView(null);
        $widget->setAction($action);

        return $widget;
    }
}