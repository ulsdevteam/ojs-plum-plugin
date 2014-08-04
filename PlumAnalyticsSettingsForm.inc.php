<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsSettingsForm.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class PlumAnalyticsSettingsForm
 * @ingroup plugins_generic_plumAnalytics
 *
 * @brief Form for journal managers to modify Plum Analytics plugin settings
 */


import('lib.pkp.classes.form.Form');

class PlumAnalyticsSettingsForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/** @var $widgetTypes array() hash of valid widget type options */
	var $widgetTypes;
	
	/** @var $options array() hash of valid widget settings options */
	var $options;
	
	/** @var $options array() convenience variable for each keyname for settings */
	var $settingsKeys;
	
	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function PlumAnalyticsSettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;
		
		// Set options for widgetTypes, and setup convenience variable for settings iterators
		$this->widgetTypes = array();
		$this->settingsKeys = array();
		$this->widgetTypes[''] = '';
		foreach ($plugin->settingsByWidgetType as $k => $v) {
			$this->widgetTypes[$k] = 'plugins.generic.plumAnalytics.manager.settings.widgetType.'.$k;
			$this->settingsKeys = array_merge($this->settingsKeys, $v);
		}
		unset($this->widgetTypes['_all']);
		$this->settingsKeys = array_unique($this->settingsKeys);
		// Set options for popup alignment
		$this->options = array();
		foreach ($plugin->valuesByWidgetSetting as $k => $v) {
			$this->options[$k] = array_merge(array('' => ''), $v);
		}
		
		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidator($this, 'widgetType', 'required', 'plugins.generic.plumAnalytics.manager.settings.widgetTypeRequired'));
		$this->addCheck(new FormValidator($this, 'hook', 'required', 'plugins.generic.plumAnalytics.manager.settings.hookRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;

		$this->_data = array();
		foreach ($this->settingsKeys as $k) {
			$this->_data[$k] = $plugin->getSetting($journalId, $k);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars($this->settingsKeys);
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;

		foreach ($this->settingsKeys as $k) {
			$saveData = $this->getData($k);
			// special handling of checkboxes
			if (in_array($k, array('hideWhenEmpty', 'hidePrint', 'border'))) {
				$saveData = $saveData ? 'true' : 'false';
			}
			$plugin->updateSetting($journalId, $k, $saveData, 'string');
		}
	}
	
	function addTinyMCE() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		$templateMgr =& TemplateManager::getManager();

		// Enable TinyMCE with specific params
		$additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');

		import('classes.file.JournalFileManager');
		$publicFileManager = new PublicFileManager();
		$tinyMCE_script = '
		<script language="javascript" type="text/javascript" src="'.Request::getBaseUrl().'/'.TINYMCE_JS_PATH.'/tiny_mce.js"></script>
		<script language="javascript" type="text/javascript">
			tinyMCE.init({
			mode : "textareas",
			plugins : "style,paste,jbimages",
			theme : "advanced",
			theme_advanced_buttons1 : "formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink",
			theme_advanced_buttons3 : "cut,copy,paste,pastetext,pasteword,|,cleanup,help,code,jbimages",
			theme_advanced_toolbar_location : "bottom",
			theme_advanced_toolbar_align : "left",
			content_css : "' . Request::getBaseUrl() . '/styles/common.css", 
			relative_urls : false,
			document_base_url : "'. Request::getBaseUrl() .'/'.$publicFileManager->getJournalFilesPath($journalId) .'/", 
			extended_valid_elements : "span[*], div[*]"
			});
		</script>';

		$templateMgr->assign('additionalHeadData', $additionalHeadData."\n".$tinyMCE_script);

	}
}

?>
