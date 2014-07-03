<?php

/**
 * @file plugins/generic/plumAnalytics/PlumAnalyticsPlugin.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
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
		'_all' => array('widgetType', 'hideWhenEmpty'),
		'plumx-plum-print-popup' => array('popup'),
		'plumx-summary' => array('orientation', 'hidePrint'),
		'plumx-details' => array('width', 'border', 'hidePrint'),
	);
	
	/**
	 * @var $valuesByWidgetSetting array()
	 *  This array associates widget settings with the possible widget values options
	 */
	public $valuesByWidgetSetting = array(
		'popup' => array('top', 'bottom', 'left', 'right'),
		'orientation' => array('vertical', 'horizontal'),
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
			// Insert Plum Analytics widget into the article
			HookRegistry::register('Templates::Article::MoreInfo', array($this, 'insertWidget'));

			// Insert Plum Analytics script into the footer
			HookRegistry::register('Templates::Article::Footer::PageFooter', array($this, 'insertWidget'));
		}
		return $success;
	}
	
	function getDisplayName() {
		return __('plugins.generic.plumAnalytics.displayName');
	}

	function getDescription() {
		return __('plugins.generic.plumAnalytics.description');
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items
	 * to append.
	 * @param $isSubclass boolean
	 */
	function setBreadcrumbs($isSubclass = false) {
		$templateMgr =& TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);
		if ($isSubclass) {
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins'),
				'manager.plugins'
			);
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins', 'generic'),
				'plugins.categories.generic'
			);
		}

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}


	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.plumAnalytics.manager.settings'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Insert Plum Analytics page tag to footer, if page is an Article
	 */
	function insertWidget($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		$templateMgr =& TemplateManager::getManager();
				
		// journal is required to retreive settings
		$currentJournal = $templateMgr->get_template_vars('currentJournal');
		if (!empty($currentJournal)) {
			$journal =& Request::getJournal();
			$journalId = $journal->getId();
			// Shortcut this function if we are not in an article, or not in the selected hook, or not in the PageFooter
			if (Request::getRequestedPage() != 'article' && $hookName != $this->availableHooks[$this->getSetting($journalId, 'hook')] && $hookName != 'Templates::Article::Footer::PageFooter') {
				return false;
			}

			// article is required to retreive DOI
			$article = $templateMgr->get_template_vars('article');
			if (!$article) {
				return false;
			}
			
			// requested page must be an article with a DOI for widget display
			$articleDOI = $article->getPubId('doi');
			if (!$articleDOI) {
				return false;
			}

			// sanity check to ensure values required by _all widgets are included
			$requiredValues = true;
			foreach ($this->settingsByWidgetType['_all'] as $k) {
				$v = $this->getSetting($journalId, $k);
				$templateMgr->assign($k, $v);
				if (!$v) {
					$requiredValues = false;
				}
			}
			if ($requiredValues) {
				if ($hookName == 'Templates::Article::Footer::PageFooter') {
					$output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagPlumScript.tpl');
				} elseif ($hookName == 'Templates::Article::MoreInfo') {
					$templateMgr->assign('articleDOI', $articleDOI);
					// Assign variables as dictated by the settingsByWidgetType association
					foreach ($this->settingsByWidgetType[$this->getSetting($journalId, 'widgetType')] as $k) {
						$templateMgr->assign($k, $this->getSetting($journalId, $k));
					}
					$output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTagPlumWidget.tpl');
				}
			}

		}
		return false;
	}

 	/**
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Result status message
	 * @param $messageParams array Parameters for the message key
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message, &$messageParams) {
		if (!parent::manage($verb, $args, $message, $messageParams)) {
			// If enabling this plugin, go directly to the settings
			if ($verb == 'enable') {
				$verb = 'settings';
			} else {
				return false;
			}
		}

		switch ($verb) {
			case 'settings':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$journal =& Request::getJournal();

				$this->import('PlumAnalyticsSettingsForm');
				$form = new PlumAnalyticsSettingsForm($this, $journal->getId());
				// This assigns select options
				foreach ($form->options as $k => $v) {
					$templateMgr->assign($k.'Types', $v);
				}
				$templateMgr->assign('widgetTypes', $form->widgetTypes);
				$templateMgr->assign('allPlumWidgetSettings', $form->settingsKeys);
				$templateMgr->assign('validPlumWidgetSettings', $this->settingsByWidgetType);
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$user =& Request::getUser();
						import('classes.notification.NotificationManager');
						$notificationManager = new NotificationManager();
						$notificationManager->createTrivialNotification($user->getId());
						Request::redirect(null, 'manager', 'plugins', 'generic');
						return false;
					} else {
						$this->setBreadCrumbs(true);
						$form->display();
					}
				} else {
					$this->setBreadCrumbs(true);
					$form->initData();
					$form->display();
				}
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
}
?>
