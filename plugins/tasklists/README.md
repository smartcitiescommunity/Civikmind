# Plugin Tasklists for GLPI

![Plugin tasklists](https://raw.githubusercontent.com/InfotelGLPI/tasklists/master/screenshots/kanban.png "Plugin tasklists")

This plugin is on Transifex - Help us to translate :
https://www.transifex.com/infotelGLPI/GLPI_tasklists/

See wiki for use it ? https://github.com/InfotelGLPI/tasklists/wiki

Adding a management of tasks & a kanban. This plugin adds in GLPI, an interface to add tasks & manage them into a kanban

Last features :

- [X] Clone task
- [X] Add comments to tasks
- [X] See authors tasks filter
- [X] See archived tasks filter
- [X] See in progress tasks filter
- [X] Send notifications for add / change / delete task
- [X] Use richtext on tasks
- [X] Add templates by context
- [X] Add entity pre-selection
- [X] Use a default Backlog from list
- [X] Link to tickets
- [X] Preference : context by default
- [X] See percentage of completion from tasks
- [X] Order States
- [X] Add context to States
- [X] Add color to States
- [X] Minimize closed tasks
- [X] Can be used with mail collector to create tasks
- [ ] Add notification for revive user in charge
- [ ] Add min - max by state
- [ ] Add notifications to comments

For GLPI versions <9.1, for use it with mail collector you must to modify "inc/rulemailcollector.class.php" file, into  "executeActions" fonction, into switch : switch ($action->fields["action_type"]), add a default case  : 

```
default:
   //plugins actions
   $executeaction = clone $this;
   $output = $executeaction->executePluginsActions($action, $output, $params);
   break;
```
