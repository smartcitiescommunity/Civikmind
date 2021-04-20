# JAMF Plugin for GLPI

[![CodeFactor](https://www.codefactor.io/repository/github/cconard96/jamf/badge/master)](https://www.codefactor.io/repository/github/cconard96/jamf/overview/master)

Syncs data from JAMF Pro to GLPI.

## Requirements
- GLPI >= 9.5.0
- PHP >= 7.2.0
- Jamf Pro >= 10.20.0

## Usage
- Server/sync configuration is found in Setup > Config under the JAMF Plugin tab.
- JSS User account used must have read access to mobile devices at least. Additional access may be required depending on what items are synced (software, etc).
- The two automatic actions "importJamf' and 'syncJamf" can only be run in CLI/Cron mode due to how long they can take.
- There is a rule engine used to filter out imported devices. The default import action is to allow the import.
- iPads and AppleTVs are imported as Computers, while iPhones are imported as Phones.

## Locale Support
- Contribute to French and Spanish localizations on [POEditor](https://poeditor.com/join/project/BepTgrM7ab).
- To request new languages, please open a GitHub issue.

## Versioning/Support
- For each new major version of GLPI supported, the major version number of this plugin gets incremented.
- For each feature release on the same major GLPI version, the minor version number of this plugin gets incremented.
- Each bugfix release will increment the patch version number.
- Bugfixes for the current and previous major versions of the plugin will be supported at least. Older versions may be supported depending on community interest and my own company's need. This plugin will not be backported to support versions older than 9.4.0.
- I will strive to have at least a beta release for the latest major version of GLPi within a week of the full release.