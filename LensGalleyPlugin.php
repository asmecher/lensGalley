<?php

/**
 * @file plugins/generic/lensGalley/LensGalleyPlugin.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class LensGalleyPlugin
 *
 * @ingroup plugins_generic_lensGalley
 *
 * @brief Class for LensGalley plugin
 */

namespace APP\plugins\generic\lensGalley;

use APP\core\Application;
use APP\core\Request;
use APP\core\Services;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\observers\events\UsageEvent;
use APP\template\TemplateManager;
use PKP\config\Config;
use PKP\core\PKPRequest;
use PKP\galley\Galley;
use PKP\plugins\Hook;
use PKP\submissionFile\SubmissionFile;

class LensGalleyPlugin extends \PKP\plugins\GenericPlugin
{
    /**
     * @copydoc LazyLoadPlugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled()) {
                Hook::add('ArticleHandler::view::galley', $this->articleCallback(...));
                Hook::add('IssueHandler::view::galley', $this->issueCallback(...));
                Hook::add('ArticleHandler::download', $this->articleDownloadCallback(...), Hook::SEQUENCE_LATE);
            }
            return true;
        }
        return false;
    }

    /**
     * Install default settings on journal creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.lensGalley.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.generic.lensGalley.description');
    }

    /**
     * Callback that renders the article galley.
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function articleCallback($hookName, $args)
    {
        $request = & $args[0];
        $issue = & $args[1];
        $galley = & $args[2];
        $submission = & $args[3];

        $templateMgr = TemplateManager::getManager($request);
        if ($galley && in_array($galley->getFileType(), ['application/xml', 'text/xml'])) {
            $galleyPublication = null;
            foreach ($submission->getData('publications') as $publication) {
                if ($publication->getId() === $galley->getData('publicationId')) {
                    $galleyPublication = $publication;
                    break;
                }
            }
            $templateMgr->assign([
                'pluginLensPath' => $this->getLensPath($request),
                'displayTemplatePath' => $this->getTemplateResource('display.tpl'),
                'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
                'galleyFile' => $galley->getFile(),
                'issue' => $issue,
                'article' => $submission,
                'bestId' => $galleyPublication->getData('urlPath') ?? $submission->getId(),
                'isLatestPublication' => $submission->getData('currentPublicationId') === $galley->getData('publicationId'),
                'galleyPublication' => $galleyPublication,
                'galley' => $galley,
                'jQueryUrl' => $this->_getJQueryUrl($request),
            ]);
            $templateMgr->display($this->getTemplateResource('articleGalley.tpl'));
            return true;
        }

        return false;
    }

    /**
     * Callback that renders the issue galley.
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function issueCallback($hookName, $args)
    {
        $request = & $args[0];
        $issue = & $args[1];
        $galley = & $args[2];

        $templateMgr = TemplateManager::getManager($request);
        if ($galley && in_array($galley->getFileType(), ['application/xml', 'text/xml'])) {
            $templateMgr->assign([
                'pluginLensPath' => $this->getLensPath($request),
                'displayTemplatePath' => $this->getTemplateResource('display.tpl'),
                'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
                'galleyFile' => $galley->getFile(),
                'issue' => $issue,
                'galley' => $galley,
                'jQueryUrl' => $this->_getJQueryUrl($request),
            ]);
            $templateMgr->display($this->getTemplateResource('issueGalley.tpl'));
            return true;
        }

        return false;
    }

    /**
     * Get the URL for JQuery JS.
     *
     * @param PKPRequest $request
     *
     * @return string
     */
    private function _getJQueryUrl($request)
    {
        $min = Config::getVar('general', 'enable_minified') ? '.min' : '';
        return $request->getBaseUrl() . '/js/build/jquery/jquery' . $min . '.js';
    }

    /**
     * returns the base path for Lens JS included in this plugin.
     *
     * @param PKPRequest $request
     *
     * @return string
     */
    public function getLensPath($request)
    {
        return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/lib/lens';
    }

    /**
     * Present rewritten XML.
     *
     * @param string $hookName
     * @param array $args
     */
    public function articleDownloadCallback($hookName, $args)
    {
        $article = & $args[0];
        $galley = & $args[1];
        $fileId = & $args[2];
        $request = Application::get()->getRequest();

        if (!$galley) {
            return false;
        }

        $submissionFile = $galley->getFile();
        if ($galley->getData('submissionFileId') == $fileId && in_array($submissionFile->getData('mimetype'), ['application/xml', 'text/xml']) && $galley->getData('submissionFileId') == $submissionFile->getId()) {
            if (!Hook::run('LensGalleyPlugin::articleDownload', [[$article,  &$galley, &$fileId]])) {
                $xmlContents = $this->_getXMLContents($request, $galley);
                header('Content-Type: application/xml');
                header('Content-Length: ' . strlen($xmlContents));
                header('Content-Disposition: inline');
                header('Cache-Control: private');
                header('Pragma: public');
                echo $xmlContents;
                $returner = true;
                Hook::run('LensGalleyPlugin::articleDownloadFinished', [[&$returner]]);

                $submissionFile = Repo::submissionFile()->get($galley->getData('submissionFileId'));
                $publication = Repo::publication()->get($galley->getData('publicationId'));
                $issue = null;
                if ($publication->getData('issueId')) {
                    $issue = Repo::issue()->get($publication->getData('issueId'));
                    $issue = $issue->getJournalId() == $article->getData('contextId') ? $issue : null;
                }
                event(new UsageEvent(Application::ASSOC_TYPE_SUBMISSION_FILE, $request->getContext(), $article, $galley, $submissionFile, $issue));
            }
            return true;
        }

        return false;
    }

    /**
     * Return string containing the contents of the XML file.
     * This function performs any necessary filtering, like image URL replacement.
     *
     * @param Request $request
     * @param Galley $galley
     *
     * @return string
     */
    public function _getXMLContents($request, $galley)
    {
        $journal = $request->getJournal();
        $submissionFile = $galley->getFile();
        $fileService = Services::get('file');
        $file = $fileService->get($submissionFile->getData('fileId'));
        $contents = $fileService->fs->read($file->path);

        // Replace media file references
        $embeddableFiles = Repo::submissionFile()
            ->getCollector()
            ->filterByAssoc(
                Application::ASSOC_TYPE_SUBMISSION_FILE,
                [$submissionFile->getId()]
            )
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_DEPENDENT])
            ->includeDependentFiles()
            ->getMany();

        $referredArticle = $referredPublication = null;
        foreach ($embeddableFiles as $embeddableFile) {
            // Ensure that the $referredArticle object refers to the article we want
            if (!$referredArticle || !$referredPublication || $referredPublication->getData('submissionId') != $referredArticle->getId() || $referredPublication->getId() != $galley->getData('publicationId')) {
                $referredPublication = Repo::publication()->get($galley->getData('publicationId'));
                $referredArticle = Repo::submission()->get($referredPublication->getData('submissionId'));
            }

            $params = [];

            if ($embeddableFile->getData('mimetype') == 'text/plain' || $embeddableFile->getData('mimetype') == 'text/css') {
                $params['inline'] = 'true';
            }

            $fileUrl = $request->url(null, 'article', 'download', [$referredPublication->getData('urlPath') ?? $referredArticle->getId(), 'version', $galley->getData('publicationId'), $galley->getBestGalleyId(), $embeddableFile->getId(), $embeddableFile->getLocalizedData('name')], $params);
            $pattern = preg_quote(rawurlencode($embeddableFile->getLocalizedData('name')), '/');
            $contents = preg_replace(
                $pattern = '/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $pattern . ')"/',
                '\1="' . $fileUrl . '"',
                $contents
            );
            if ($contents === null) {
                error_log('PREG error in ' . __FILE__ . ' line ' . __LINE__ . ': ' . preg_last_error());
            }
        }

        // Perform replacement for ojs://... URLs
        $contents = preg_replace_callback(
            '/(<[^<>]*")[Oo][Jj][Ss]:\/\/([^"]+)("[^<>]*>)/',
            $this->_handleOjsUrl(...),
            $contents
        );
        if ($contents === null) {
            error_log('PREG error in ' . __FILE__ . ' line ' . __LINE__ . ': ' . preg_last_error());
        }

        // Perform variable replacement for journal, issue, site info
        if (!$referredPublication) $referredPublication = Repo::publication()->get($galley->getData('publicationId'));
        $issue = Repo::issue()->getBySubmissionId($referredPublication->getData('submissionId'));

        $journal = $request->getJournal();
        $site = $request->getSite();

        $paramArray = [
            'issueTitle' => $issue ? $issue->getIssueIdentification() : __('editor.article.scheduleForPublication.toBeAssigned'),
            'journalTitle' => $journal->getLocalizedName(),
            'siteTitle' => $site->getLocalizedTitle(),
            'currentUrl' => $request->getRequestUrl(),
        ];

        foreach ($paramArray as $key => $value) {
            $contents = str_replace('{$' . $key . '}', $value, $contents);
        }

        return $contents;
    }

    public function _handleOjsUrl($matchArray)
    {
        $request = Application::get()->getRequest();
        $url = $matchArray[2];
        $anchor = null;
        if (($i = strpos($url, '#')) !== false) {
            $anchor = substr($url, $i + 1);
            $url = substr($url, 0, $i);
        }
        $urlParts = explode('/', $url);
        if (isset($urlParts[0])) {
            switch (strtolower($urlParts[0])) {
                case 'journal':
                    $url = $request->url(
                        $urlParts[1] ?? $request->getRouter()->getRequestedContextPath($request),
                        null,
                        null,
                        null,
                        null,
                        $anchor
                    );
                    break;
                case 'article':
                    if (isset($urlParts[1])) {
                        $url = $request->url(
                            null,
                            'article',
                            'view',
                            [$urlParts[1]],
                            null,
                            $anchor
                        );
                    }
                    break;
                case 'issue':
                    if (isset($urlParts[1])) {
                        $url = $request->url(
                            null,
                            'issue',
                            'view',
                            [$urlParts[1]],
                            null,
                            $anchor
                        );
                    } else {
                        $url = $request->url(
                            null,
                            'issue',
                            'current',
                            null,
                            null,
                            $anchor
                        );
                    }
                    break;
                case 'sitepublic':
                    array_shift($urlParts);
                    $publicFileManager = new PublicFileManager();
                    $url = $request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath() . '/' . implode('/', $urlParts) . ($anchor ? '#' . $anchor : '');
                    break;
                case 'public':
                    array_shift($urlParts);
                    $journal = $request->getJournal();
                    $publicFileManager = new PublicFileManager();
                    $url = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($journal->getId()) . '/' . implode('/', $urlParts) . ($anchor ? '#' . $anchor : '');
                    break;
            }
        }
        return $matchArray[1] . $url . $matchArray[3];
    }
}
