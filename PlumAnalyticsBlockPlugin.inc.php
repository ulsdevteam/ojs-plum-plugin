<?php

/**
 * @file plugins/blocks/help/HelpBlockPlugin.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class HelpBlockPlugin
 * @ingroup plugins_blocks_help
 *
 * @brief Class for help block plugin
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class PlumAnalyticsBlockPlugin extends BlockPlugin {
	
	/** @var $parentPluginName string name of PlumAnalytics plugin */
	var $parentPluginName;
	
	/**
	 * Constructor
	 */
	function PlumAnalyticsBlockPlugin($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * Override currentVersion to prevent upgrade and delete management.
	 * @return boolean
	 */
	function getCurrentVersion() {
		return false;
	}

	/**
	 * Determine whether the plugin is enabled. Only enabled if the core Plum plugin is enabled.
	 * @return boolean
	 */
	function getEnabled() {
		if ($this->getPlumPlugin()->getEnabled()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.plumAnalytics.block.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription() {
		return __('plugins.generic.plumAnalytics.block.description');
	}
	
	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 * @return boolean
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR);
	}

	/**
	 * Get the plum plugin
	 * @return object
	 */
	function &getPlumPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	function getPluginPath() {
		$plugin =& $this->getPlumPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		$plugin =& $this->getPlumPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * Get the name of the block template file.
	 * @return String
	 */
	function getBlockTemplateFilename() {
		return 'blockPlumWidget.tpl';
	}

	/**
	 * Get the HTML contents for this block.
	 * @param $templateMgr object
	 * @return $string
	 */
	function getContents(&$templateMgr) {
		$plugin =& $this->getPlumPlugin();
		if ($validContext = $plugin->validateTemplateContext($templateMgr, 'block')) {
			$templateMgr->assign('blockTitle', $plugin->getSetting($validContext['journal'], 'blockTitle'));
			$plugin->setupTemplateManager($validContext['journal'], $validContext['article'], $templateMgr);
			return parent::getContents($templateMgr);
		} else {
			return false;
		}
	}
}

?>