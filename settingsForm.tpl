{**
 * plugins/generic/plumAnalytics/settingsForm.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics plugin settings
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.plumAnalytics.manager.plumAnalyticsSettings"}
{include file="common/header.tpl"}
{/strip}
<div id="plumAnalyticsSettings">
<div id="description">{translate key="plugins.generic.plumAnalytics.manager.settings.description"}</div>

<div class="separator"></div>

<br />

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsVersion" required="true" key="plugins.generic.plumAnalytics.manager.settings.version"}</td>
		<td width="80%" class="value">
			<input type="text" name="version" id="plumAnalyticsVersion" value="{$version|escape}" size="5" maxlength="10" class="textField" />
			<br />
			<span class="instruct">{translate key="plugins.generic.plumAnalytics.manager.settings.versionInstructions"}</span>
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsWidgetType" required="true" key="plugins.generic.plumAnalytics.manager.settings.widgetType"}</td>
		<td width="80%" class="value">
			<select class="selectMenu" name="widgetType" id="plumAnalyticsWidgetType">
				{foreach from=$widgetTypes key=key item=value}
					<option value="{$key|escape}" {if $key eq $widgetType}selected="selected" {/if}>{$value|escape}</option>
				{/foreach}
			</select>
			<br />
			<span class="instruct">{translate key="plugins.generic.plumAnalytics.manager.settings.widgetTypeInstructions"}</span>
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsWidth" key="plugins.generic.plumAnalytics.manager.settings.width"}</td>
		<td width="80%" class="value"><input type="text" name="width" id="plumAnalyticsWidth" value="{$width|escape}" size="5" maxlength="10" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsShowTitle" key="plugins.generic.plumAnalytics.manager.settings.showTitle"}</td>
		<td width="80%" class="value"><input type="checkbox" name="showTitle" id="plumAnalyticsShowTitle" {if $showTitle eq 'true' }checked="checked" {/if}/> </td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsShowAuthor" key="plugins.generic.plumAnalytics.manager.settings.showAuthor"}</td>
		<td width="80%" class="value"><input type="checkbox" name="showAuthor" id="plumAnalyticsShowAuthor" {if $showAuthor eq 'true' }checked="checked" {/if}/> </td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsAlignment" key="plugins.generic.plumAnalytics.manager.settings.alignment"}</td>
		<td width="80%" class="value">
			<select class="selectMenu" name="alignment" id="plumAnalyticsAlignment">
				{foreach from=$alignments key=key item=value}
					<option value="{$key|escape}" {if $key eq $alignment}selected="selected" {/if}>{$value|escape}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="plumAnalyticsHideWhenEmpty" key="plugins.generic.plumAnalytics.manager.settings.hideWhenEmpty"}</td>
		<td width="80%" class="value"><input type="checkbox" name="hideWhenEmpty" id="plumAnalyticsHideWhenEmpty" {if $hideWhenEmpty eq 'true' }checked="checked" {/if}/> </td>
	</tr>
</table>

<br/>

<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/><input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}
