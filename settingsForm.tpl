{**
 * plugins/generic/plumAnalytics/settingsForm.tpl
 *
 * Copyright (c) 2018 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Plum Analytics plugin settings
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#plumSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="plumSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="plumSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.plumAnalytics.manager.settings.description"}</div>

	{fbvFormArea id="plumSettingsFormArea"}
		{fbvFormSection for="plumWidgetType" description="plugins.generic.plumAnalytics.manager.settings.widgetType"}
			{fbvElement type="select" id="plumWidgetType" from=$plumWidgetTypes selected=$plumWidgetType translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="plumHideWhenEmpty" label="plugins.generic.plumAnalytics.manager.settings.hideWhenEmpty" checked=$plumHideWhenEmpty|compare:true}
			{fbvElement type="checkbox" id="plumBorder" label="plugins.generic.plumAnalytics.manager.settings.border" checked=$plumBorder|compare:true}
			{fbvElement type="checkbox" id="plumHidePrint" label="plugins.generic.plumAnalytics.manager.settings.hidePrint" checked=$plumHidePrint|compare:true}
		{/fbvFormSection}
		{fbvFormSection for="plumPopup" description="plugins.generic.plumAnalytics.manager.settings.popup"}
			{fbvElement type="select" id="plumPopup" from=$plumPopupTypes selected=$plumPopup translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection for="plumOrientation" description="plugins.generic.plumAnalytics.manager.settings.orientation"}
			{fbvElement type="select" id="plumOrientation" from=$plumOrientationTypes selected=$plumOrientation translate=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" label="plugins.generic.plumAnalytics.manager.settings.width" id="plumWidth" value=$plumWidth inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection description="plugins.generic.plumAnalytics.manager.settings.hook"}
			<div>{translate key="plugins.generic.plumAnalytics.manager.settings.hookInstructions"}</div>
			{fbvElement type="select" id="plumHook" from=$plumHookTypes selected=$plumHook translate=true size=$fbvStyles.size.SMALL}
			{fbvElement type="text" label="plugins.generic.plumAnalytics.manager.settings.blockTitle" id="plumBlockTitle" value=$plumBlockTitle inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.plumAnalytics.manager.settings.htmlPrefix" for="plumHtmlPrefix"}
			{fbvElement type="textarea" multilingual=false name="plumHtmlPrefix" id="plumHtmlPrefix" value=$plumHtmlPrefix rich=true height=$fbvStyles.height.TALL}
		{/fbvFormSection}
		{fbvFormSection label="plugins.generic.plumAnalytics.manager.settings.htmlSuffix" for="plumHtmlSuffix"}
			{fbvElement type="textarea" multilingual=false name="plumHtmlSuffix" id="plumHtmlSuffix" value=$plumHtmlSuffix rich=true height=$fbvStyles.height.TALL}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
