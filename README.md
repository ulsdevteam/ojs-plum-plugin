# Plum Analytics Artifact Widget plugin for OJS

This plugin provides display of PlumX Metrics from [Plum Analytics](http://plumanalytics.com/) on the article level for PKP Open Journal Systems.

## Requirements

* OJS 3.x
  * for 2.x, see the [ojs-dev-2_4 branch](https://github.com/ulsdevteam/ojs-plum-plugin/tree/ojs-dev-2_4)
* Article level DOIs
  * see: Login -> Settings -> Website -> Plugins -> DOI
* PlumX subscription
  * see: [Plum Analytics OJS Integration](https://plumanalytics.com/integrate/load-your-data/ojs_integration/)

## Configuration

Install this as a "generic" plugin in OJS.  The preferred installation method is through the Plugin Gallery.  To install manually via the filesystem, extract the contents of this archive to a "plumAnalytics" directory under "plugins/generic" in your OJS root.  To install via Git submodule, target that same directory path: `git submodule add https://github.com/ulsdevteam/ojs-plum-plugin plugins/generic/plumAnalytics` and `git submodule update --init --recursive plugins/generic/plumAnalytics`.  Run the upgrade script to register this plugin, e.g.: `php tools/upgrade.php upgrade`

Login as a Journal Manger and navigate to the Journal for which you wish to use the Widget.  Enable the plugin via Login -> Settings -> Website -> Plugins -> Plum Analytics Artifact Widget -> Enable.

To configure the plugin, you will need to select what type of widget you want, and where it will display.  Additional options may apply to specific widget types.  See the [PlumX Widgets page](https://plu.mx/developers/widgets) for an overview of the different widget types and options.

## Author / License

Written by Clinton Graham for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
