GLPI ProjectBridge Plugin
=========================

By Probesys: https://probesys.com

Language: french / english ( and more if you want translate it in other languages )

Works with: GLPI 9.3.x, 9.4.x and 9.5.x

This plugin allows to count down time from contracts by linking tickets with project tasks and project tasks with contracts.

## History

This plugin is based on the old glpi plugin "Best management". We have rewritten and rethought all the code and the features.

## Features

* configure recipients of expiration alerts and reached quota alerts
* link default contracts to entities: tickets created in that entity will automatically be linked to selected contract (and thus corresponding ongoing project task)
* link contracts to projects
* automatically create a project and it's task when creating a contract
* renew a contract when quota is reached or it expired
* change a ticket's link to another project, and thus another contract

## Configuration

From configuration panel you can :

1. Match status names and their values in your GLPI configuration.
2. Define users receiving alerts from ProjectBridge.
3. Include private ticket tasks duration to contract.

![Setup](https://raw.githubusercontent.com/Probesys/glpi-plugins-projectbridge/github/screenshots/configuration-panel.png)

## Usage

* From the contract page, if no project task is active, you can create new one :

![Usage](https://raw.githubusercontent.com/Probesys/glpi-plugins-projectbridge/github/screenshots/create-and-affect-new-contract.gif)

* Renew a contract when quota is reached or it expired :

![Usage](https://raw.githubusercontent.com/Probesys/glpi-plugins-projectbridge/github/screenshots/renew-contract.gif) 

## Known issues

* when there is no contract start date or a wrongly formatted one, renewal does not work
* link_data.php script does not link all existing contracts with projects, a manual check is required
* alerts are still sent even if notifications are disabled

## Possible evolutions

* add a way to link all concerned tickets to their project tasks in link_data.php
* use GLPI notifications to send the contract alerts

## Contact 
glpi@probesys.com
