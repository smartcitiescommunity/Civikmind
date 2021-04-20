Changelog for MReporting
========================

GLPI Mreporting 1.3.1
---------------------

* Fix #56 : Bad comparaison in Nb tickets per SLA bug (thanks to awslgr)
* Fix #78 : Review dashboard feature on helpdesk interface
* Fix #74 : Remove require "GLPI_PHPMAILER_DIR"
* Fix #57 : Selection from the dropdown itilcategory (thanks to myoaction)
* Fix #73 : Don't use Templates and Deleted computers (thanks to johannsan)
* Fix #26 : Error Ext on GLPI 0.90 (thanks to tsmr - Infotel Conseil)
* Fix #77 : Queries filtered by status for inventory reporting (thanks to sebfun)
* Task #80 : Remove lib tcpdf

GLPI Mreporting 1.3.0
---------------------

* New release schema following [Semantic versionning](http://semver.org)
* Fix GLPi 9.1 compatibility
* Fix SLA graph
* Fix manufacturer graph
* Prevent not logged in users to display graphs

GLPI Mreporting 0.90+1.2
------------------------

* GLPi 9.1 compatible
* bugfixes

GLPI Mreporting 0.90+1.1
------------------------

* Bugfixes

GLPI Mreporting 0.90+1.0
------------------------

* GLPi 0.90 compatible
* ...


GLPI Mreporting 0.84+2.3.3
--------------------------

* Export : keep selected dates
* Fix week sql interval
* Fix variables for week interval (start of week : monday)
* Fix issue on graph 'Tickets per technician'


GLPI Mreporting 0.84+2.3.2
--------------------------

* New reports (OSX Assets)
* Fix warnings on export
* Fix migration error logging
* Improve detection of base classes


GLPI Mreporting 0.84+2.3.1
--------------------------

* 2 new reports (max os and linux version distribtion)
* week period fix
* dashboard order
* fix some php errors


GLPI Mreporting 0.84+2.3.0
--------------------------

* New Dashboard
* Profiles publication.
* Notifications fixed
* New reports


Changelogs for MReporting 0.85
==============================

Mreporting 0.85+1.0-RC4
-----------------------

* Migration to Jquery
* Plugins locales are now in gettext



Mreporting 0.85+1.0-RC3
-----------------------

* Migration to Jquery
* Plugins locales are now in gettext


Changelogs for MReporting 0.90
==============================

Mreporting 0.90-1.1
-------------------

* More strings in gettext : Put locales for reportHbarComputersByAge() in plugin locales
* Fix bugs
* Fix send reports in Notification (fix link, fix attachment)
* Now don't use Mailqueue for send reports
* Cleanup code

