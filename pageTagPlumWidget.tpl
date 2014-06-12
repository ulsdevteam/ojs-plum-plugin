{**
 * plugins/generic/plumAnalytics/pageTagPlumWidget.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics Widget page tag.
 *
 *}
<!-- Plum Analytics -->
<div class="plumx-widget" plumx-widget-type="{$widgetType|escape}" doi="{$articleDOI|escape}" {if $hideWhenEmpty}hide-when-empty="{$hideWhenEmpty|escape}" {/if}{if $showTitle}show-title="{$showTitle|escape}" {/if}{if $showAuthor}show-author="{$showAuthor|escape}" {/if}{if $width}width="{$width|escape}{/if}"></div>
<!-- /Plum Analytics -->

