<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\NoAccessException;
use Piwik\Period\Range;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Site;
use Piwik\Url;
use Piwik\View;

/**
 * Reads the requested DataTable from the API and prepare data for the Sparkline view.
 *
 * @property Sparklines\Config $config
 */
class Sparklines extends ViewDataTable
{
    const ID = 'sparklines';

    public static function getDefaultConfig()
    {
        return new Sparklines\Config();
    }

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    public function render()
    {
        $view = new View('@CoreVisualizations/_dataTableViz_sparklines.twig');

        $columns = $this->config->sparkline_metrics_to_display;

        $columnsList = array();
        if (!empty($columns)) {
            foreach ($columns as $cols) {
                $columnsList = array_merge($cols, $columnsList);
            }
        }
        $this->requestConfig->request_parameters_to_modify['columns'] = $columnsList;
        $this->requestConfig->request_parameters_to_modify['format_metrics'] = '1';

        $data = $this->loadDataTableFromAPI();

        $this->applyFilters($data);
        if (empty($columns)) {
            $columns = $data->getColumns();
        }

        $translations = $this->config->translations;

        $firstRow = $data->getFirstRow();

        $sparklines = array();
        foreach ($columns as $column) {
            if ($column === 'label') {
                continue;
            }

            $blankSparkline = array('url' => '', 'metrics' => array());

            if (empty($column)) {
                $sparklines[] = $blankSparkline;
                continue;
            }

            $sparkline = array(
                'url' => $this->getUrlSparkline(array('columns' => $column)),
                'metrics' => array()
            );

            if (!is_array($column)) {
                $column = array($column);
            }

            foreach ($column as $col) {
                $value = $firstRow->getColumn($col);

                if ($value === false) {
                    $value = 0;
                }

                if ($col === 'nb_users' && (!is_numeric($value) || 0 >= $value)) {
                    $sparklines[] = $blankSparkline;
                    continue;
                }

                $sparkline['metrics'][] = array(
                    'value' => $value,
                    'description' => isset($translations[$col]) ? $translations[$col] : $col
                );
            }

            if (!empty($sparkline['metrics'])) {
                $sparklines[] = $sparkline;
            }
        }

        $view->sparklines = $sparklines;

        return $view->render();
    }

    private function applyFilters(DataTable\DataTableInterface $table)
    {
        foreach ($this->config->getPriorityFilters() as $filter) {
            $table->filter($filter[0], $filter[1]);
        }

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($this->config->getPresentationFilters() as $filter) {
            $table->queueFilter($filter[0], $filter[1]);
        }

        $table->applyQueuedFilters();
    }

    /**
     * Returns a URL to a sparkline image for a report served by the current plugin.
     *
     * The result of this URL should be used with the [sparkline()](/api-reference/Piwik/View#twig) twig function.
     *
     * The current site ID and period will be used.
     *
     * @param string $action Method name of the controller that serves the report.
     * @param array $customParameters The array of query parameter name/value pairs that
     *                                should be set in result URL.
     * @return string The generated URL.
     * @api
     */
    protected function getUrlSparkline($customParameters = array())
    {
        $params = $this->getGraphParamsModified(
            array('viewDataTable' => 'sparkline',
                'action'        => $this->requestConfig->getApiMethodToRequest(),
                'module'        => $this->requestConfig->getApiModuleToRequest())
            + $customParameters
        );
        // convert array values to comma separated
        foreach ($params as &$value) {
            if (is_array($value)) {
                $value = rawurlencode(implode(',', $value));
            }
        }
        $url = Url::getCurrentQueryStringWithParametersModified($params);
        return $url;
    }

    /**
     * Returns the array of new processed parameters once the parameters are applied.
     * For example: if you set range=last30 and date=2008-03-10,
     *  the date element of the returned array will be "2008-02-10,2008-03-10"
     *
     * Parameters you can set:
     * - range: last30, previous10, etc.
     * - date: YYYY-MM-DD, today, yesterday
     * - period: day, week, month, year
     *
     * @param array $paramsToSet array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
     * @throws \Piwik\NoAccessException
     * @return array
     */
    protected function getGraphParamsModified($paramsToSet = array())
    {
        if (!isset($paramsToSet['period'])) {
            $period = Common::getRequestVar('period');
        } else {
            $period = $paramsToSet['period'];
        }

        if ($period == 'range') {
            return $paramsToSet;
        }

        if (!isset($paramsToSet['range'])) {
            $range = 'last30';
        } else {
            $range = $paramsToSet['range'];
        }

        if (!isset($paramsToSet['idSite'])) {
            $idSite = Common::getRequestVar('idSite');
        } else {
            $idSite = $paramsToSet['idSite'];
        }

        if (!isset($paramsToSet['date'])) {
            $endDate = Common::getRequestVar('date', 'yesterday', 'string');
        } else {
            $endDate = $paramsToSet['date'];
        }

        $site = new Site($idSite);

        if (is_null($site)) {
            throw new NoAccessException("Website not initialized, check that you are logged in and/or using the correct token_auth.");
        }

        $paramDate = Range::getRelativeToEndDate($period, $range, $endDate, $site);

        $params = array_merge($paramsToSet, array('date' => $paramDate));
        return $params;
    }
}
