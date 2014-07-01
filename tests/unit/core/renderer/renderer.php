<?php

use FOF30\Render\RenderAbstract as F0FRenderAbstract;
use FOF30\Input\Input as F0FInput;
use FOF30\Form\Form as F0FForm;
use FOF30\Model\Model as F0FModel;

class FtestRenderer extends F0FRenderAbstract {

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct()
	{
		$this->priority	 = 1000;
		$this->enabled	 = true;
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   F0FInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function preRender($view, $task, $input, $config = array()){
		return 'pre';
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   F0FInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function postRender($view, $task, $input, $config = array())
	{
		return 'post';
	}

	/**
	 * Renders a F0FForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   F0FForm   &$form  The form to render
	 * @param   F0FModel  $model  The model providing our data
	 * @param   F0FInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormBrowse(F0FForm &$form, F0FModel $model, F0FInput $input)
	{
		return 'browse';
	}

	/**
	 * Renders a F0FForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   F0FForm   &$form  The form to render
	 * @param   F0FModel  $model  The model providing our data
	 * @param   F0FInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormRead(F0FForm &$form, F0FModel $model, F0FInput $input)
	{
		return 'read';
	}

	/**
	 * Renders a F0FForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   F0FForm   &$form  The form to render
	 * @param   F0FModel  $model  The model providing our data
	 * @param   F0FInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormEdit(F0FForm &$form, F0FModel $model, F0FInput $input)
	{
		return 'edit';
	}

	/**
	 * Renders a raw F0FForm and returns the corresponding HTML
	 *
	 * @param   F0FForm   &$form  	The form to render
	 * @param   F0FModel  $model  	The model providing our data
	 * @param   F0FInput  $input  	The input object
	 * @param   string	  $formType The form type e.g. 'edit' or 'read'
	 *
	 * @return  string    The HTML rendering of the form
	 */
	protected function renderFormRaw(F0FForm &$form, F0FModel $model, F0FInput $input, $formType)
	{
		return 'raw';
	}

}