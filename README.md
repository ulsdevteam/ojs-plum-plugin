# Plum Analytics Artifact Widget plugin for OJS

This plugin provides display of PlumX Metrics from [Plum Analytics](http://plumanalytics.com/) on the article level for PKP Open Journal Systems.

## Requirements

* OJS 2.4 or a later release of OJS 2.x
* Article level DOIs
  * see: User Home -> Journal Manager -> System Plugins -> Public Identifier Plugins -> DOI
* PlumX subscription
  * see: [Plum Analytics OJS Integration](http://plumanalytics.com/ojs_integration/)

## Configuration

Install this as a "generic" plugin in OJS.  To install manually via the filesystem, extract the contents of this archive to a "plumAnalytics" directory under "plugins/generic" in your OJS root.  To install via Git submodule, target that same directory path: `git submodule add https://github.com/ulsdevteam/ojs-plum-plugin plugins/generic/plumAnalytics` and `git submodule update --init --recursive plugins/generic/plumAnalytics`.  Run the upgrade script to register this plugin, e.g.: `php tools/upgrade.php upgrade`

Login as a Journal Manger and navigate to the Journal for which you wish to use the Widget.  Enable the plugin via User Home -> Journal Manger -> System Plugins -> Generic Plugins -> Plum Analytics Artifact Widget -> Enable.

To configure the plugin, you will need to select what type of widget you want, and where it will display.  Additional options may apply to specific widget types.  See the [PlumX Widgets page](https://plu.mx/developers/widgets) for an overview of the different widget types and options.

## Author / License

Written by Clinton Graham for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
