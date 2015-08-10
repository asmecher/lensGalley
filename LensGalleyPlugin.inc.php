<?php

/**
 * @file plugins/viewableFiles/lensGalley/LensGalleyPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LensGalleyPlugin
 * @ingroup plugins_viewableFiles_lensGalley
 *
 * @brief Class for LensGalley plugin
 */

import('classes.plugins.ViewableFilePlugin');

class LensGalleyPlugin extends ViewableFilePlugin {
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.viewableFiles.lensGalley.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.viewableFiles.lensGalley.description');
	}

	/**
	 * @see ViewableFilePlugin::displayArticleGalley
	 */
	function displayArticleGalley($templateMgr, $request, $params) {
		$templateMgr->assign('pluginLensPath', $this->getLensPath($request));
		$galley = $templateMgr->get_template_vars('galley');
		$galleyFiles = $galley->getLatestGalleyFiles();
		assert(count($galleyFiles)==1);
		$templateMgr->assign('firstGalleyFile', array_shift($galleyFiles));
		return parent::displayArticleGalley($templateMgr, $request, $params);
	}

	/**
	 * returns the base path for Lens JS included in this plugin.
	 * @param $request PKPRequest
	 * @return string
	 */
	function getLensPath($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/lib/lens';
	}
}

?>
