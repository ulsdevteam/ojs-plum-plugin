{**
 * plugins/generic/plumAnalytics/pageTagPlumWidget.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics Widget page tag.
 *
 *}
{if $htmlPrefix}{$htmlPrefix}{/if}
 <!-- Plum Analytics -->
<a href="https://plu.mx/a?doi={$articleDOI|escape}" class="{$widgetType|escape}" {if $hideWhenEmpty}data-hide-when-empty="{$hideWhenEmpty|escape}" {/if}{if $hidePrint}data-hide-print="{$hidePrint|escape}" {/if}{if $orientation}data-orientation="{$orientation|escape}" {/if}{if $popup}data-popup="{$popup|escape}" {/if}{if $border}data-border="{$border|escape}"{/if}{if $width}data-width="{$width|escape}"{/if}></a>
<!-- /Plum Analytics -->
{if $htmlSuffix}{$htmlSuffix}{/if}
