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
	 * Determine whether this plugin can handle the specified content.
	 * @param $galley ArticleGalley|IssueGalley
	 * @return boolean True iff the plugin can handle the content
	 */
	function canHandle($galley) {
		if (is_a($galley, 'ArticleGalley') && $galley->getGalleyType() == $this->getName()) {
			return true;
		} elseif (is_a($galley, 'IssueGalley') && $galley->getFileType() == 'application/xml') {
			return true;
		}
		return false;
	}

	/**
	 * @see ViewableFilePlugin::displayArticleGalley
	 */
	function displayArticleGalley($request, $issue, $article, $galley) {
		$templateMgr = TemplateManager::getManager($request);
		$galleyFiles = $galley->getLatestGalleyFiles();
		assert(count($galleyFiles)==1);
		$templateMgr->assign(array(
			'pluginLensPath' => $this->getLensPath($request),
			'firstGalleyFile' => array_shift($galleyFiles),
			'pluginTemplatePath' => $this->getTemplatePath(),
		));
		return parent::displayArticleGalley($request, $issue, $article, $galley);
	}

	/**
	 * @see ViewableFilePlugin::displayArticleGalley
	 */
	function displayIssueGalley($request, $issue, $galley) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'pluginLensPath' => $this->getLensPath($request),
			'pluginTemplatePath' => $this->getTemplatePath(),
		));
		return parent::displayIssueGalley($request, $issue, $galley);
	}

	/**
	 * returns the base path for Lens JS included in this plugin.
	 * @param $request PKPRequest
	 * @return string
	 */
	function getLensPath($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/lib/lens';
	}

	/**
	 * Get the template path
	 * @return string
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
}

?>
