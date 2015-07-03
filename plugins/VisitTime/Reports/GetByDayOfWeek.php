<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\VisitTime\Columns\DayOfTheWeek;
use Piwik\Period;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Site;
use Piwik\Widget\WidgetsList;

class GetByDayOfWeek extends Base
{
    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->dimension     = new DayOfTheWeek();
        $this->name          = Piwik::translate('VisitTime_VisitsByDayOfWeek');
        $this->documentation = Piwik::translate('VisitTime_WidgetByDayOfWeekDocumentation');
        $this->constantRowsCount = true;
        $this->order = 25;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        // we have to do it manually since it's only done automatically if a subcategoryId is specified,
        // we do not set a subcategoryId since this report is not supposed to be shown in the UI
        $widgetsList->addWidget($factory->createWidget());
    }

    public function configureView(ViewDataTable $view)
    {
        $this->setBasicConfigViewProperties($view);

        $view->requestConfig->filter_limit = 7;

        $view->config->enable_sort = false;
        $view->config->show_footer_message = Piwik::translate('General_ReportGeneratedFrom', $this->getDateRangeForFooterMessage());
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->config->disable_row_evolution = true;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
            $view->config->show_all_ticks     = true;
        }
    }

    private function getDateRangeForFooterMessage()
    {
        // get query params
        $idSite = Common::getRequestVar('idSite', false);
        $date = Common::getRequestVar('date', false);
        $period = Common::getRequestVar('period', false);

        // create a period instance
        try {
            $oPeriod = Period\Factory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date);
        } catch (\Exception $ex) {
            return ''; // if query params are incorrect, forget about the footer message
        }

        // set the footer message using the period start & end date
        $start = $oPeriod->getDateStart()->toString();
        $end = $oPeriod->getDateEnd()->toString();
        if ($start == $end) {
            $dateRange = $start;
        } else {
            $dateRange = $start . " &ndash; " . $end;
        }
        return $dateRange;
    }
}
