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
	<script src="{$pluginJSPath}/lib/lens/index.js"></script>
	{url|assign:"xmlUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}

	<script type="text/javascript">{literal}
		$(document).ready(function(){
			var app = new Lens({
				document_url: "{/literal}{$pdfUrl|escape:'javascript'}{literal}"
			});
	{/literal}</script>
{/if}
