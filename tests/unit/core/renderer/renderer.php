<?php

class FtestRenderer extends FOF30\Render\RenderAbstract
{

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct()
	{
		$this->priority = 1000;
		$this->enabled = true;
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string            $view   The current view
	 * @param   string            $task   The current task
	 * @param   FOF30\Input\Input $input  The input array (request parameters)
	 * @param   array             $config The view configuration array
	 *
	 * @return  void
	 */
	public function preRender($view, $task, $input, $config = array())
	{
		return 'pre';
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string            $view   The current view
	 * @param   string            $task   The current task
	 * @param   FOF30\Input\Input $input  The input array (request parameters)
	 * @param   array             $config The view configuration array
	 *
	 * @return  void
	 */
	public function postRender($view, $task, $input, $config = array())
	{
		return 'post';
	}

	/**
	 * Renders a FOF30\Form\Form for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOF30\Form\Form   &$form The form to render
	 * @param   FOF30\Model\Model $model The model providing our data
	 * @param   FOF30\Input\Input $input The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormBrowse(FOF30\Form\Form &$form, FOF30\Model\Model $model, FOF30\Input\Input $input)
	{
		return 'browse';
	}

	/**
	 * Renders a FOF30\Form\Form for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOF30\Form\Form   &$form The form to render
	 * @param   FOF30\Model\Model $model The model providing our data
	 * @param   FOF30\Input\Input $input The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormRead(FOF30\Form\Form &$form, FOF30\Model\Model $model, FOF30\Input\Input $input)
	{
		return 'read';
	}

	/**
	 * Renders a FOF30\Form\Form for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOF30\Form\Form   &$form The form to render
	 * @param   FOF30\Model\Model $model The model providing our data
	 * @param   FOF30\Input\Input $input The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormEdit(FOF30\Form\Form &$form, FOF30\Model\Model $model, FOF30\Input\Input $input)
	{
		return 'edit';
	}

	/**
	 * Renders a raw FOF30\Form\Form and returns the corresponding HTML
	 *
	 * @param   FOF30\Form\Form   &$form    The form to render
	 * @param   FOF30\Model\Model $model    The model providing our data
	 * @param   FOF30\Input\Input $input    The input object
	 * @param   string            $formType The form type e.g. 'edit' or 'read'
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormRaw(FOF30\Form\Form &$form, FOF30\Model\Model $model, FOF30\Input\Input $input, $formType)
	{
		return 'raw';
	}
}