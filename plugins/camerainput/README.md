# Camera Input Plugin for GLPI
This ties into the global search box, Physical Inventory plugin's search box, and my Asset Audit plugin's search box and allows you to use your camera as a digital barcode reader.
Since browsers can only use webcams/cameras in a secure context, you must be connected to your GLPI instance over HTTPS or localhost.

Currently, this only really works well under ideal circumstances where there is no glare, and the camera is of a good quality.
In other cases, it is preferable to use an actual laser barcode scanner.
This could be improved in the future by tweaking parameters used with the library being used, or by switching to a standardized JS Shape Detection/Barcode Detection API if any get finalized.

## Locale Support
- Contribute to existing localizations on [POEditor](https://poeditor.com/join/project?hash=UJXnGBmw5g).
- To request new languages, please open a GitHub issue.
