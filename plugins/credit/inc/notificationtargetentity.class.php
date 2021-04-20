<?php

class PluginCreditNotificationTargetEntity extends NotificationTarget {

   function getEvents() {
      return [
          'expired' => __('Expiration date', 'credit')
      ];
   }

   public function addDataForTemplate($event, $options = []) {
      global $DB;

      $this->data['##credit.name##'] = $this->obj->getField('name');
      $this->data['##credit.quantity_sold##'] = $this->obj->getField('quantity');
      $this->data['##credit.enddate##'] = $this->obj->getField('end_date');

      $req = $DB->request(
         [
            'SELECT' => [
               'SUM' => 'consumed AS consumed_total'
            ],
            'FROM'   => 'glpi_plugin_credit_tickets',
            'WHERE'  => [
               'plugin_credit_entities_id' => $this->obj->getField('id'),
            ],
         ]
      );
      $data = $req->next();
      $this->data['##credit.quantity_remaining##'] = (int)$this->obj->getField('quantity') - (int)$data['consumed_total'];

      $this->data['##lang.credit.enddate##'] = __('End date', 'credit');
      $this->data['##lang.credit.quantity_remaining##'] = __('Quantity remaining', 'credit');
      $this->data['##lang.credit.quantity_sold##'] = __('Quantity sold', 'credit');
      $this->data['##lang.credit.name##'] = __('Credit voucher', 'credit');

      $this->getTags();
      foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
         if (!isset($this->data[$tag])) {
            $this->data[$tag] = $values['label'];
         }
      }
   }

   public function getTags() {
      $tags = [
         'credit.name'               => __('Credit voucher', 'credit'),
         'credit.quantity_sold'      => __('Quantity sold', 'credit'),
         'credit.enddate'            => __('End date', 'credit'),
         'credit.quantity_remaining' => __('Quantity remaining', 'credit'),
      ];

      foreach ($tags as $tag => $label) {
         $this->addTagToList(
            [
               'tag'   => $tag,
               'label' => $label,
               'value' => true,
            ]
         );
      }

      $lang = [
         'credit.expired'             => __('Credit voucher expiration', 'credit'),
         'credit.expired.information' => __('This credit voucher will expire soon. Please, consider buying a new one.', 'credit')
      ];

      foreach ($lang as $tag => $label) {
         $this->addTagToList(
            [
               'tag'   => $tag,
               'label' => $label,
               'value' => false,
               'lang'  => true,
            ]
         );
      }

      asort($this->tag_descriptions);

      return $this->tag_descriptions;
   }


   public static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginCreditEntity notifications");

      $template     = new NotificationTemplate();
      $translation  = new NotificationTemplateTranslation();
      $notification = new Notification();
      $n_n_template = new Notification_NotificationTemplate();
      $target       = new NotificationTarget();

      $templates_id = false;
      $result = $DB->request(
         [
            'SELECT' => 'id',
            'FROM'   => 'glpi_notificationtemplates',
            'WHERE'  => [
               'itemtype' => 'PluginCreditEntity',
               'name'     => 'Credit expired',
            ]
         ]
      );

      if (count($result) > 0) {
         $data = $result->next();
         $templates_id = $data['id'];
      } else {
         $templates_id = $template->add(
            [
               'name'     => 'Credit expired',
               'itemtype' => 'PluginCreditEntity',
               'date_mod' => $_SESSION['glpi_currenttime'],
               'comment'  => '',
               'css'      => '',
            ]
         );
      }

      if ($templates_id) {
         $tanslation_count = countElementsInTable(
            $translation->getTable(),
            ['notificationtemplates_id' => $templates_id]
         );
         if ($tanslation_count == 0) {
            $translation->add(
               [
                  'notificationtemplates_id' => $templates_id,
                  'language'                 => '',
                  'subject'                  => '##lang.credit.expired## : ##credit.name##',
                  'content_text'             => '##lang.credit.expired.information##',
                  'content_html'             => '##lang.credit.expired.information##',
               ]
            );
         }

         $notifications_count = countElementsInTable(
            $notification->getTable(),
            ['itemtype' => 'PluginCreditEntity', 'event' => 'expired']
         );

         if ($notifications_count == 0) {
            $notification_id = $notification->add(
               [
                  'name'         => 'Credit expired',
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginCreditEntity',
                  'event'        => 'expired',
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ]
            );

            $n_n_template->add(
               [
                  'notifications_id'         => $notification_id,
                  'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
                  'notificationtemplates_id' => $templates_id,
               ]
            );

            $target->add(
               [
                  'notifications_id' => $notification_id,
                  'type'             => Notification::USER_TYPE,
                  'items_id'         => Notification::ENTITY_ADMINISTRATOR,
               ]
            );
         }
      }
   }


   public static function uninstall() {
      global $DB;

      $notification = new Notification();
      $notifications_iterator = $DB->request(
         [
            'SELECT' => 'id',
            'FROM'   => $notification->getTable(),
            'WHERE'  => [
               'itemtype' => 'PluginCreditEntity',
               'event'    => 'expired',
            ],
         ]
      );
      foreach ($notifications_iterator as $notification_data) {
         $notification->delete($notification_data);
      }

      $template    = new NotificationTemplate();
      $templates_iterator = $DB->request(
         [
            'SELECT' => 'id',
            'FROM'   => $template->getTable(),
            'WHERE'  => [
               'itemtype' => 'PluginCreditEntity',
            ],
         ]
      );
      foreach ($templates_iterator as $template_data) {
         $translation = new NotificationTemplateTranslation();
         $translations_iterator = $DB->request(
            [
               'SELECT' => 'id',
               'FROM'   => $translation->getTable(),
               'WHERE'  => [
                  'notificationtemplates_id' => $template_data['id'],
               ],
            ]
         );
         foreach ($translations_iterator as $translation_data) {
            $translation->delete($translation_data);
         }

         $template->delete($template_data);
      }
   }

}
