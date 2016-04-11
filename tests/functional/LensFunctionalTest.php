<?php

/**
 * @file tests/functional/LensFunctionalTest.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LensFunctionalTest
 * @package plugins.generic.staticPages
 *
 * @brief Functional tests for the static pages plugin.
 */

import('tests.ContentBaseTestCase');

class LensFunctionalTest extends ContentBaseTestCase {
	/**
	 * @copydoc WebTestCase::getAffectedTables
	 */
	protected function getAffectedTables() {
		return PKP_TEST_ENTIRE_DB;
	}

	/**
	 * Enable the plugin
	 */
	function testLens() {
		$this->logIn('dbarnes');

		$this->waitForElementPresent($selector='//a[text()=\'Import/Export\']');
		$this->click($selector);

		$this->waitForElementPresent($selector='//a[text()=\'Native XML Plugin\']');
		$this->click($selector);

		$this->uploadFile(dirname(__FILE__) . '/issue.xml');
		$this->waitForElementPresent($selector='//input[@name=\'temporaryFileId\' and string-length(@value)>0]');
		$this->click('//form[@id=\'importXmlForm\']//button[starts-with(@id,\'submitFormButton-\')]');

		// Ensure that the import was listed as completed.
		$this->waitForElementPresent('//*[contains(text(),\'The import completed successfully.\')]//li[contains(text(),\'Vol 1 No 3\')]');

		// Plugin management
		$this->waitForElementPresent($selector='link=Website');
		$this->clickAndWait($selector);
		$this->click('link=Plugins');

		// Find and enable the plugin
		$this->waitForElementPresent($selector = '//input[starts-with(@id,\'select-cell-lensgalleyplugin-enabled\')]');
		$this->click($selector); // Enable plugin
		$this->waitJQuery();

		// View the associated issue
		$this->clickAndWait('link=View Site');
		$this->clickAndWait('link=Archives');
		$this->clickAndWait('link=Vol 1 No 3 (2014)');



		$this->logOut();
	}
}
