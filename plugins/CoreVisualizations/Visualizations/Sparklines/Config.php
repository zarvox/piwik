<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Metrics;

/**
 * DataTable Visualization that derives from Sparklines.
 */
class Config extends \Piwik\ViewDataTable\Config
{
    /**
     * The name of the JavaScript class to use as this graph's external series toggle. The class
     * must be a subclass of JQPlotExternalSeriesToggle.
     *
     * @see self::EXTERNAL_SERIES_TOGGLE_SHOW_ALL
     *
     * Default value: false
     */
    public $sparkline_metrics_to_display = array();

    public function __construct()
    {
        parent::__construct();

        $this->translations = Metrics::getDefaultMetricTranslations();
    }

    /**
     * @param array|string $columns The columns to remove that was previously added
     * @param array|string $replacementColumns  If given the removed columns will be replaced with these columns
     */
    public function removeSparklineMetricToDisplay($columns, $replacementColumns = null)
    {
        $index = array_search($columns, $this->sparkline_metrics_to_display);

        if (false !== $index) {
            if (isset($replacementColumns)) {
                $replacementColumns = array($replacementColumns);
            }

            array_splice($this->sparkline_metrics_to_display, $index, 1, $replacementColumns);
        }
    }

    public function addSparklineMetricsToDisplay($columns)
    {
        $this->sparkline_metrics_to_display[] = $columns;
    }

}
