# Web Resources Plugin for GLPI

Adds a dashboard for web resources.
Resources can be scoped to specific Entities, Profiles, Groups, or Users (or a mix).
Resources can have an image icon (favicon for example), or a FontAwesome icon like 'fab fa-github".
Non-image icons can have their colors changed as you see fit.

![Dashboard](https://raw.githubusercontent.com/cconard96/glpi-webresources-plugin/master/screenshots/Dashboard.png)

Resources can be any weblink or a link with a special URI scheme. For example these links are all valid:
 - https://glpi-project.org (Standard URL)
 - market://details?id=org.glpi.inventory.agent&hl=en_US (Link to app on Android's Play Store)
 - softwarecenter://Page=AvailableSoftware (Link to the Available Software page in the SCCM/MEM Software Center)
For more information about URI schemes please refer to https://en.wikipedia.org/wiki/List_of_URI_schemes.

If you want to try automatically getting an icon for a URL, you should make sure the `ext-dom` extension for PHP is installed and loaded. Otherwise, the plugin will try to fallback to 'DOMAIN/favicon.ico'.
## How to use
Please refer to the [Wiki](https://github.com/cconard96/glpi-webresources-plugin/wiki/Quick-Start) for a Quick Start guide.

## Locale Support
- Contribute to existing localizations on [POEditor](https://poeditor.com/join/project?hash=H4Yugw8tw6).
- To request new languages, please open a GitHub issue.
