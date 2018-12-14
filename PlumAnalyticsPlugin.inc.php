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
	 * _general are plugin settings not mapped to Plum data attributes
	 * _all are data attributes applied to each widget type
	 * other array keys define widget types with specific data attributes
	 */
	public $settingsByWidgetType = array(
		'_general' => array('plumWidgetType', 'plumHook', 'plumHtmlPrefix', 'plumHtmlSuffix', 'plumBlockTitle', 'plumTheme'),
		'_all' => array('data-hide-usage', 'data-hide-captures', 'data-hide-mentions', 'data-hide-socialmedia', 'data-hide-citations', 'data-pass-hidden-categories', 'data-hide-when-empty'),
		'plumx-plum-print-popup' => array('data-popup'),
		'plumx-summary' => array('data-orientation', 'data-hide-print'),
		'plumx-details' => array('data-width', 'data-border', 'data-hide-print'),
	);

	/**
	 * @var $valuesByWidgetSetting array()
	 *  This array associates widget settings with the possible widget values options
	 */
	public $valuesByWidgetSetting = array(
		'plumTheme' => array(
			'' => 'plugins.generic.plumAnalytics.manager.settings.theme.default',
			'plum-liberty-theme' => 'plugins.generic.plumAnalytics.manager.settings.theme.liberty',
			'plum-bigben-theme' => 'plugins.generic.plumAnalytics.manager.settings.theme.bigben',
		),
		'data-popup' => array(
			'top' => 'plugins.generic.plumAnalytics.manager.settings.data-popup.top',
			'bottom' => 'plugins.generic.plumAnalytics.manager.settings.data-popup.bottom',
			'left' => 'plugins.generic.plumAnalytics.manager.settings.data-popup.left',
			'right' => 'plugins.generic.plumAnalytics.manager.settings.data-popup.right',
			'hidden' => 'plugins.generic.plumAnalytics.manager.settings.data-popup.hidden',
		),
		'data-orientation' => array(
			'vertical' => 'plugins.generic.plumAnalytics.manager.settings.data-orientation.vertical',
			'horizontal' => 'plugins.generic.plumAnalytics.manager.settings.data-orientation.horizontal',
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
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
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
				new PlumAnalyticsBlockPlugin($this->getName(), $this->getPluginPath()),
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
			if ($this->availableHooks[$this->getSetting($context->getId(), 'plumHook')] == $hookName) {
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
		foreach ($this->settingsByWidgetType['_generic'] as $k) {
			$v = $this->getSetting($contextId, $k);
			$templateMgr->assign($k, $v);
		}
		$dataOptions = array();
		foreach ($this->settingsByWidgetType['_all'] as $k) {
			$dataOptions[$k] = $this->getSetting($contextId, $k);
		}
		// Assign variables as dictated by the settingsByWidgetType association
		foreach ($this->settingsByWidgetType[$this->getSetting($contextId, 'plumWidgetType')] as $k) {
			$dataOptions[$k] = $this->getSetting($contextId, $k);
		}
		$templateMgr->assign('dataOptions', $this->getSetting($contextId, $dataOptions));
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
		foreach (array('plumWidgetType', 'plumHook') as $k) {
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
