# Protocols Manager
GLPI Plugin to make PDF reports with user inventory.
## Features
* Making PDFs with all or selected user inventory
* Saving protocols in GLPI Documents
* Possibility to create different protocol templates
* Templates have configurable name, font, orientation, logo image, city, content and footer
* Possibility to make comments to any selected item
* Showing Manufacturer (only first word to be clearly) and Model of item
* Showing serial number or inventory number in one or two columns
* Possibility to add custom rows
* Possibility to add notes to export
## What's new in 1.4?
* New optional feature - sending emails with PDFs - automatically after generating PDF or manually in any moment
* New text field in template above the table
* Now you can use fields: Owner name - {owner}, current date - {cur_date} and admin name - {admin} in template text fields and email content and subject.
* Fixed some bugs
## In 1.4.2:
* Fixed one column mode in serial number
* Document is now assigned to default user's entity

## Compatibility
GLPI 9.3 or higher
PHP 7.1 or higher  
***NOTE:*** in GLPI 9.3.x, you have to modify /inc/generate.class.php - search and replace: **GLPI_UPLOAD_DIR** to **GLPI_TMP_DIR**.
## Installation
1. Download and extract package
2. Copy protocolsmanager folder to GLPI plugins directory
3. Go to GLPI Plugin Menu and click 'install' and then 'activate'

![Setup](https://raw.githubusercontent.com/mateusznitka/protocolsmanager/master/docs/img/setup.gif)
## Updating
1. Extract package and copy to plguins directory (replace old protocolsmanager folder)
2. Go to GLPI Plugin Menu, you should see 'to update' status.
3. Click on 'install' and then 'activate'
## Preparing
1. Go to Profiles and click on profile you want to add permissions to plugin
2. Select permissions and save
3. Go to Plugins -> Protocols manager
4. Edit default or create new template: Fill all or some textboxes, choose your font and logo if you want
5. Save template / templates

![Preparing](https://raw.githubusercontent.com/mateusznitka/protocolsmanager/master/docs/img/config.gif)
## Using the plugin
1. Go to Administration -> Users and click on user login
2. Go to Protocols Manager tab
3. Select some or all items
4. Write a comment to an item (optional)
5. Add and fill custom rows (optional)
6. Write a note to export (optional)
7. Select your template from list and click "Create"
8. Your protocol is on list above now, you can open it in new tab. It is available in Managament -> Documents too.
9. You can delete all or some protocols by selecting them and click "Delete".

![Generate](https://raw.githubusercontent.com/mateusznitka/protocolsmanager/master/docs/img/generate_standard.gif)
## Notes
1. Generated items depends on what you assign to the user in GLPI
2. You can edit template core in HTML by editing template.php file in protocolsmanager/inc directory
## To do
1. More customization
2. Give an idea...
## Contact 
mateusznitka01@gmail.com
## Buy me a coffee :)
If you like my work, you can support me by a donate here:

<a href="https://www.buymeacoffee.com/mateusznitka" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-yellow.png" alt="Buy Me A Coffee" height="51px" width="210px"></a>
