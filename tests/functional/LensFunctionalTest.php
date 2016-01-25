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
		$this->register(array(
			'username' => 'godoghue',
			'firstName' => 'Geoff',
			'lastName' => 'O\'Donoghue',
			'affiliation' => 'University of California, Berkeley',
			'country' => 'United States',
			'roles' => array('Author'),
		));

		$title = 'Direct single molecule measurement of TCR triggering by agonist pMHC in living primary T cells';
		$this->createSubmission(array(
			'title' => $title,
			'abstract' => 'T cells discriminate between self and foreign antigenic peptides, displayed on antigen presenting cell surfaces, via the TCR. While the molecular interactions between TCR and its ligands are well characterized in vitro, quantitative measurements of these interactions in living cells are required to accurately resolve the physical mechanisms of TCR signaling. We report direct single molecule measurements of TCR triggering by agonist pMHC in hybrid junctions between live primary T cells and supported lipid membranes. Every pMHC:TCR complex over the entire cell is tracked while simultaneously monitoring the local membrane recruitment of ZAP70, as a readout of TCR triggering. Mean dwell times for pMHC:TCR molecular binding of 5 and 54 s were measured for two different pMHC:TCR systems. Single molecule measurements of the pMHC:TCR:ZAP70 complex indicate that TCR triggering is stoichiometric with agonist pMHC in a 1:1 ratio. Thus any signal amplification must occur downstream of TCR triggering.',
			'keywords' => array(
				'TCR triggering',
				'single molecule kinetics',
				'T cells',
			),
			'additionalAuthors' => array(
				array(
					'firstName' => 'Rafal',
					'lastName' => 'Pielak',
					'country' => 'United States',
					'affiliation' => 'University of California, Berkeley',
					'email' => 'rpielak@mailinator.com',
				),
				array(
					'firstName' => 'Alexander',
					'lastName' => 'Smoligovets',
					'country' => 'United States',
					'affiliation' => 'University of California, Berkeley',
					'email' => 'asmoligovets@mailinator.com',
				),
				array(
					'firstName' => 'Jenny',
					'lastName' => 'Lin',
					'country' => 'United States',
					'affiliation' => 'University of California, Berkeley',
					'email' => 'jlin@mailinator.com',
				),
			),
			'files' => array(
				array(
					'file' => dirname(__FILE__) . '/sample.xml',
					'fileTitle' => $title,
				),
			),
		));

		$this->logOut();

		// Expedite the submission
		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->waitForElementPresent($selector = 'css=[id^=expedite-button-]');
		$this->click($selector);
		$this->waitForElementPresent($selector = 'id=issueId');
		$this->select($selector, 'Vol 1 No 1 (2014)');
		$this->waitForElementPresent($selector = '//button[text()=\'Save\']');
		$this->click($selector);
		$this->waitJQuery();

		// Plugin management
		$this->waitForElementPresent($selector='link=Website');
		$this->clickAndWait($selector);
		$this->click('link=Plugins');

		// Find and enable the plugin
		$this->waitForElementPresent($selector = '//input[@id=\'select-cell-lensgalleyplugin-enabled\']');
		$this->click($selector); // Enable plugin
		$this->waitJQuery();

		// View the associated issue
		$this->clickAndWait('link=View Site');
		$this->clickAndWait('link=Archives');
		$this->clickAndWait('link=Vol 1 No 1 (2014)');

		

		$this->logOut();
	}
}
