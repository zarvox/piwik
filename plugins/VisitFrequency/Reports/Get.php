<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\VisitFrequency\Columns\Metrics\ReturningMetric;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->categoryId      = 'General_Visitors';
        $this->name          = Piwik::translate('VisitFrequency_ColumnReturningVisits');
        $this->documentation = ''; // TODO
        $this->processedMetrics = array(
            new ReturningMetric(new AverageTimeOnSite()),
            new ReturningMetric(new ActionsPerVisit()),
            new ReturningMetric(new BounceRate())
        );
        $this->metrics       = array(
            'nb_visits_returning',
            'nb_actions_returning',
            'nb_uniq_visitors_returning',
            'nb_users_returning',
            'max_actions_returning'
        );
        $this->order = 40;
        $this->subcategoryId = 'VisitorInterest_Engagement';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidget(
            $factory->createWidget()
                ->setName('VisitFrequency_WidgetGraphReturning')
                ->forceViewDataTable(Evolution::ID)
                ->setAction('getEvolutionGraph')
                ->setOrder(1)
        );

        $widgetsList->addWidget(
            $factory->createWidget()
                ->forceViewDataTable(Sparklines::ID)
                ->setName('VisitFrequency_WidgetOverview')
                ->setOrder(2)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {
            $view->requestConfig->apiMethodToRequestDataTable = 'API.get';
            $view->config->sparkline_metrics_to_display = $this->getSparklineColumns();
            $view->config->addTranslations($this->getSparklineTranslations());
        }
    }

    private function getSparklineTranslations()
    {
        $translations = array(
            'nb_visits_returning' => 'ReturnVisits',
            'nb_actions_returning' => 'ReturnAvgActions',
            'nb_actions_per_visit_returning' => 'ReturnActions',
            'avg_time_on_site_returning' => 'ReturnAverageVisitDuration',
            'bounce_rate_returning' => 'ReturnBounceRate',
        );

        foreach ($translations as $metric => $key) {
            $translations[$metric] = Piwik::translate('VisitFrequency_' . $key);
        }

        return $translations;
    }

    private function getSparklineColumns()
    {
        $columns = array(
            array('nb_visits_returning'),
            array('avg_time_on_site_returning'),
            array('nb_actions_per_visit_returning'),
            array('bounce_rate_returning'),
            array('nb_actions_returning'),
        );

        return $columns;
    }

}
