<?php

class PluginMreportingNotification extends CommonDBTM {

   /**
    * @var boolean activate the history for the plugin
    */
   public $dohistory = true;

   /**
    * Return the localized name of the current Type (PluginMreporting)
    *
    * @see CommonGLPI::getTypeName()
    * @param string $nb
    * @return string name of the plugin
    */
   static function getTypeName($nb = 0) {
      return __("More Reporting", 'mreporting');
   }

   /**
    * Install mreporting notifications.
    *
    * @return array 'success' => true on success
    */
   static function install($migration) {
      global $DB;

      // Création du template de la notification
      $template = new NotificationTemplate();
      $found_template = $template->find(['itemtype' => 'PluginMreportingNotification']);
      if (empty($found_template)) {
         $template_id = $template->add([
            'name'                     => __('Notification for "More Reporting"', 'mreporting'),
            'comment'                  => "",
            'itemtype'                 => __CLASS__,
         ]);

         $content_html = __("\n<p>Hello,</p>\n\n<p>GLPI reports are available.<br />\nYou will find attached in this email.</p>\n\n", 'mreporting');

         // Ajout d'une traduction (texte) en Français
         $translation = new NotificationTemplateTranslation();
         $translation->add([
            'notificationtemplates_id' => $template_id,
            'language'                 => '',
            'subject'                  => __("GLPI statistics reports", 'mreporting'),
            'content_text'             => __("Hello,\n\nGLPI reports are available.\nYou will find attached in this email.\n\n", 'mreporting'),
            'content_html'             => $content_html]
         );

         // Création de la notification
         $notification = new Notification();
         $notification_id = $notification->add(
            [
               'name'                     => __('Notification for "More Reporting"', 'mreporting'),
               'comment'                  => "",
               'entities_id'              => 0,
               'is_recursive'             => 1,
               'is_active'                => 1,
               'itemtype'                 => __CLASS__,
               'event'                    => 'sendReporting',
            ]
         );

         $n_n_template = new Notification_NotificationTemplate();
         $n_n_template->add(
            [
               'notifications_id'         => $notification_id,
               'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
               'notificationtemplates_id' => $template_id,
            ]
         );

         $DB->query('INSERT INTO glpi_notificationtargets (items_id, type, notifications_id)
              VALUES (1, 1, ' . $notification_id . ');');
      }

      return ['success' => true];
   }

   /**
    * Remove mreporting notifications from GLPI.
    *
    * @return array 'success' => true on success
    */
   static function uninstall() {
      global $DB;

      $queries = [];

      // Remove NotificationTargets and Notifications
      $notification = new Notification();
      $result = $notification->find(['itemtype' => 'PluginMreportingNotification']);
      foreach ($result as $row) {
         $notification_id = $row['id'];
         $queries[] = "DELETE FROM glpi_notificationtargets WHERE notifications_id = " . $notification_id;
         $queries[] = "DELETE FROM glpi_notifications WHERE id = " . $notification_id;
      }

      // Remove NotificationTemplateTranslations and NotificationTemplates
      $template = new NotificationTemplate();
      $result = $template->find(['itemtype' => 'PluginMreportingNotification']);
      foreach ($result as $row) {
         $template_id = $row['id'];
         $queries[] = "DELETE FROM glpi_notificationtemplatetranslations
                        WHERE notificationtemplates_id = " . $template_id;
         $queries[] = "DELETE FROM glpi_notificationtemplates
                        WHERE id = " . $template_id;
      }

      foreach ($queries as $query) {
         $DB->query($query);
      }

      return ['success' => true];
   }

   /**
    * Give localized information about 1 task
    *
    * @param $name of the task
    *
    * @return array of strings
    */
   static function cronInfo($name) {
      switch ($name) {
         case 'SendNotifications' :
            return ['description' => __('Notification for "More Reporting"', 'mreporting')];
      }
      return [];
   }

   /**
    * @param $mailing_options
   **/
   static function send($mailing_options, $additional_options) {

      $mail = new PluginMreportingNotificationMail();
      $mail->sendNotification(array_merge($mailing_options, $additional_options));
   }

   /**
    * Execute 1 task manage by the plugin
    *
    * @param CronTask $task Object of CronTask class for log / stat
    *
    * @return interger
    *    >0 : done
    *    <0 : to be run again (not finished)
    *     0 : nothing to do
    */
   static function cronSendNotifications($task) {
      $task->log(__("Notification(s) sent !", 'mreporting'));
      PluginMreportingNotificationEvent::raiseEvent('sendReporting', new self(), $task->fields);
      return 1;
   }
}
