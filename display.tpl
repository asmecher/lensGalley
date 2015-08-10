{**
 * plugins/viewableFile/pdfArticleGalley/display.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a PDF galley.
 *}
{if $galley}
	<script src="{$pluginLensPath}/lens.js"></script>
	{url|assign:"xmlUrl" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal):$firstGalleyFile->getId() escape=false}

	<script type="text/javascript">{literal}

		var linkElement = document.createElement("link");
		linkElement.rel = "stylesheet";
		linkElement.href = "{/literal}{$pluginLensPath|escape:"javascript"}{literal}/lens.css"; //Replace here

		document.head.appendChild(linkElement);

		$(document).ready(function(){
			var app = new Lens({
				document_url: "{/literal}{$xmlUrl|escape:'javascript'}{literal}"
			});
			app.start();
			window.app = app;
		});
	{/literal}</script>
{/if}
