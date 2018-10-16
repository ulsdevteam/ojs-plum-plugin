<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsPlugin.inc.php
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class PlumAnalyticsPlugin
 * @ingroup plugins_generic_plumAnalytics
 *
 * @brief Plum Analytics plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PlumAnalyticsPlugin extends GenericPlugin {

	/**
	 * @var $settingsByWidgetType array()
	 *  This array associates widget types with the possible widget settings options
	 * _all is applied to each widget types; other array keys define widget types.
	 */
	public $settingsByWidgetType = array(
		'_all' => array('plumWidgetType', 'plumHideWhenEmpty', 'plumHook', 'plumHtmlPrefix', 'plumHtmlSuffix', 'plumBlockTitle'),
		'plumx-plum-print-popup' => array('plumPopup'),
		'plumx-summary' => array('plumOrientation', 'plumHidePrint'),
		'plumx-details' => array('plumWidth', 'plumBorder', 'plumHidePrint'),
	);

	/**
	 * @var $valuesByWidgetSetting array()
	 *  This array associates widget settings with the possible widget values options
	 */
	public $valuesByWidgetSetting = array(
		'plumPopup' => array(
			'top' => 'plugins.generic.plumAnalytics.manager.settings.popup.top',
			'bottom' => 'plugins.generic.plumAnalytics.manager.settings.popup.bottom',
			'left' => 'plugins.generic.plumAnalytics.manager.settings.popup.left',
			'right' => 'plugins.generic.plumAnalytics.manager.settings.popup.right',
		),
		'plumOrientation' => array(
			'vertical' => 'plugins.generic.plumAnalytics.manager.settings.orientation.vertical',
			'horizontal' => 'plugins.generic.plumAnalytics.manager.settings.orientation.horizontal',
		),
		'plumHook' => array(
			'footer' => 'plugins.generic.plumAnalytics.manager.settings.hook.footer',
			'moreInfo' => 'plugins.generic.plumAnalytics.manager.settings.hook.moreInfo',
			'details' => 'plugins.generic.plumAnalytics.manager.settings.hook.details',
			'block' => 'plugins.generic.plumAnalytics.block.displayName',
		)
	);

	/**
	 * @var $availableHooks array()
	 *  This array this the possible hooks
	 */
	public $availableHooks = array(
			'moreInfo' => 'Templates::Article::Main',
			'footer' => 'Templates::Article::Footer::PageFooter',
			'details' => 'Templates::Article::Details',
	);

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Attach to any possible hook; actual widget hook and script hook will be determined by insertWidget()
			foreach ($this->availableHooks as $k => $v) {
				HookRegistry::register($v, array($this, 'insertWidget'));
			}

			// Load this plugin as a block plugin as well
			$this->import('PlumAnalyticsBlockPlugin');
			PluginRegistry::register(
				'blocks',
				new PlumAnalyticsBlockPlugin($this->getName()),
				$this->getPluginPath()
			);

			// Enable TinyMCEditor in textarea fields
			HookRegistry::register('TinyMCEPlugin::getEnableFields', array($this, 'getTinyMCEEnabledFields'));

		}
		return $success;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.plumAnalytics.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription() {
		return __('plugins.generic.plumAnalytics.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * Insert Plum Analytics page tag to footer, if page is an Article
	 * @param $hookName string Name of hook calling function
	 * @param $params array of smarty and output objects
	 * @return boolean
	 */
	function insertWidget($hookName, $params) {
		$output =& $params[2];
		$templateMgr = TemplateManager::getManager();
		
		$request = $this->getRequest();
		$context = $request->getContext();
		$doi = $this->getSubmissionDOI($templateMgr, $context->getId(), $hookName);
		if ($doi) {
			if ($hookName == 'Templates::Article::Footer::PageFooter') {
				$output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagPlumScript.tpl');
			}
			if ($this->availableHooks[$this->getSetting($context->getId(), 'hook')] == $hookName) {
				$this->setupTemplateManager($context->getId(), $doi, $templateMgr);
				$output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagPlumWidget.tpl');
			}
		}
		return false;
	}

	/**
	 * Set required variables in the Template Manager
	 * @param $contextId integer Context Id
	 * @param $doi string DOI
	 * @param $templateMgr object Template Manager
	 */
	function setupTemplateManager($contextId, $doi, $templateMgr) {
		// Assign the article identifier
		$templateMgr->assign('plumSubmissionDOI', $doi);
		// Assign variables required by all widgetTypes
		foreach ($this->settingsByWidgetType['_all'] as $k) {
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$v = $this->getSetting($contextId, lcfirst(substr($k, 4)));
			$templateMgr->assign($k, $v);
		}
		// Assign variables as dictated by the settingsByWidgetType association
		foreach ($this->settingsByWidgetType[$this->getSetting($contextId, 'widgetType')] as $k) {
			// database setting is stored without "plum" prefix, e.g. plumSettingName is settingName
			$templateMgr->assign($k, $this->getSetting($contextId, lcfirst(substr($k, 4))));
		}
		$templateMgr->assign('plumWidgetTemplatePath', $this->getTemplatePath().'pageTagPlumWidget.tpl');
	}

	/**
	 * Check to see if the context of the Template Manger will support the widget
	 * @param $templateMgr object Template Manager
	 * @param $context int Context ID
	 * @param $hookName string the name of the hook calling this function
	 * @return string DOI, if present and plugin settings are configured appropriately
	 */
	function getSubmissionDOI($templateMgr, $context, $hookName) {
		// Shortcut this function if we are not in an article, or not in the selected hook, or not in the PageFooter
		if (Request::getRequestedPage() != 'article' || ($hookName != $this->availableHooks[$this->getSetting($context, 'hook')] && !($hookName == 'block' && $this->getSetting($context, 'hook') == 'block') && $hookName != 'Templates::Article::Footer::PageFooter')) {
			return false;
		}

		// submission is required to retreive DOI
		$submission = $templateMgr->get_template_vars('article');
		if (!$submission) {
			return false;
		}

		// requested page must be a submission with a DOI for widget display
		$doi = $submission->getStoredPubId('doi');
		if (!$doi) {
			return false;
		}

		// sanity check to ensure values required by _all widgets are included
		$requiredValues = true;
		foreach (array('widgetType', 'hook') as $k) {
			$v = $this->getSetting($context, $k);
			if (!$v) {
				$requiredValues = false;
			}
		}
		return ($requiredValues ? $doi : '');
	}

	/**
	 * @copydoc Plugin::manage()
	 */
	function manage($args, $request) {
		$verb = $request->getUserVar('verb');
		switch ($verb) {
			case 'settings':
				$templateMgr = TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
				$context = $request->getContext();

				$this->import('PlumAnalyticsSettingsForm');
				$form = new PlumAnalyticsSettingsForm($this, $context->getId());
				// This assigns select options
				foreach ($form->options as $k => $v) {
					$templateMgr->assign($k.'Types', $v);
				}
				$templateMgr->assign('plumWidgetTypes', $form->widgetTypes);
				$templateMgr->assign('allPlumWidgetSettings', $form->settingsKeys);
				$templateMgr->assign('validPlumWidgetSettings', $this->settingsByWidgetType);
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}
	
}
?>
