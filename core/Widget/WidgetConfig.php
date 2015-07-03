<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;

use Piwik\Piwik;
use Exception;

/**
 * Configures a widget. Use this class to configure a {@link Piwik\Widget\Widget`} or to
 * add a widget to the WidgetsList via {@link WidgetsList::addWidget}.
 *
 * @api since Piwik 2.15
 */
class WidgetConfig
{
    protected $categoryId = '';
    protected $subcategoryId = '';
    protected $module = '';
    protected $action = '';
    protected $parameters = array();
    protected $name   = '';
    protected $order  = 99;
    protected $isEnabled = true;
    protected $isWidgetizable = true;

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getSubcategoryId()
    {
        return $this->subcategoryId;
    }

    public function setSubcategoryId($subcategoryId)
    {
        $this->subcategoryId = $subcategoryId;

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the module of the widget
     * @param string $module
     * @return static
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the action of the widget
     * @param string $action
     * @return static
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Here you can optionally define URL parameters that will be used when this widget is requested.
     * @return array  Eg ('urlparam' => 'urlvalue').
     * @api
     */
    public function getParameters()
    {
        $defaultParams = array(
            'module' => $this->getModule(),
            'action' => $this->getAction()
        );

        return $defaultParams + $this->parameters;
    }

    /**
     * Add new parameters to existing parameters
     * @param array $parameters
     * @return static
     * @api
     */
    public function addParameters($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Set the parameters of the widget
     * @param array $parameters
     * @return static
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the name of the widget
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the widget
     * @param string $name
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the order of the report
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the order of the widget
     * @param int $order
     * @return static
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Defines whether a widget is enabled or not. For instance some widgets might not be available to every user or
     * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
     * return `true` or `false`. If your report is only available to users having super user access you can do the
     * following: `return Piwik::hasUserSuperUserAccess();`
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (bool) $isEnabled;
    }

    public function enable()
    {
        $this->setIsEnabled(true);
    }

    public function disable()
    {
        $this->setIsEnabled(false);
    }

    /**
     * This method checks whether the widget is available, see {@isEnabled()}. If not, it triggers an exception
     * containing a message that will be displayed to the user. You can overwrite this message in case you want to
     * customize the error message. Eg.
     * ```
     * if (!$this->isEnabled()) {
     *     throw new Exception('Setting XYZ is not enabled or the user has not enough permission');
     * }
     * ```
     * @throws \Exception
     * @api
     */
    public function checkIsEnabled()
    {
        if (!$this->isEnabled()) {
            throw new Exception(Piwik::translate('General_ExceptionWidgetNotEnabled'));
        }
    }

    /**
     * Returns the unique id of an widget with the given parameters
     *
     * @return string
     */
    public function getUniqueId()
    {
        $parameters = $this->getParameters();
        unset($parameters['module']);
        unset($parameters['action']);

        return WidgetsList::getWidgetUniqueId($this->getModule(), $this->getAction(), $parameters);
    }

    public function setIsNotWidgetizable()
    {
        $this->isWidgetizable = false;
        return $this;
    }

    public function setIsWidgetizable()
    {
        $this->isWidgetizable = true;
        return $this;
    }

    public function isWidgetizeable()
    {
        return $this->isWidgetizable;
    }


}