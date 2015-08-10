<?php
/**
 * @defgroup plugins_viewableFiles_lensGalley eLife Lens Article Galley Plugin
 */

/**
 * @file plugins/viewableFiles/lensGalley/index.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_viewableFiles_lensGalley
 * @brief Wrapper for eLife Lens article galley plugin.
 *
 */

require_once('LensGalleyPlugin.inc.php');

return new LensGalleyPlugin();

?>
