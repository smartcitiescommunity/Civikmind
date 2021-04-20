<?php

class PluginMreportingNotificationTargetNotification extends NotificationTarget {

   var $additionalData;

   function getEvents() {
      return ['sendReporting' => __('More Reporting', 'mreporting')];
   }

   function getTags() {
      $this->addTagToList(['tag'   => 'mreporting.file_url',
                           'label' => __('Link'),
                           'value' => true]);

      asort($this->tag_descriptions);
   }

   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI;

      $file_name = $this->_buildPDF(mt_rand().'_');

      $this->data['##lang.mreporting.file_url##'] = __('Link');
      $this->data['##mreporting.file_url##']      = $CFG_GLPI['url_base'].
                                                    "/index.php?redirect=plugin_mreporting_$file_name";

      $this->additionalData['attachment']['path'] = GLPI_PLUGIN_DOC_DIR."/mreporting/notifications/".$file_name;
      $this->additionalData['attachment']['name'] = $file_name;
   }


   /**
    * Generate a PDF file (with mreporting reports) to be send in the notifications
    *
    * @return string hash Name of the created file
    */
   private function _buildPDF($user_name = '') {
      global $CFG_GLPI, $DB, $LANG;

      $dir = GLPI_PLUGIN_DOC_DIR.'/mreporting/notifications';

      if (!is_dir($dir)) {
         return false;
      }

      setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
      ini_set('memory_limit', '256M');
      set_time_limit(300);

      $images = [];

      $result = $DB->query('SELECT id, name, classname, default_delay
                           FROM glpi_plugin_mreporting_configs
                           WHERE is_notified = 1
                              AND is_active = 1');

      $graphs = [];
      while ($graph = $result->fetch_array()) {
         $type = preg_split('/(?<=\\w)(?=[A-Z])/', $graph['name']);

         $graphs[] = [
            'class'     => substr($graph['classname'], 16),
            'classname' => $graph['classname'],
            'method'    => $graph['name'],
            'type'      => $type[1],
            'start'     => date('Y-m-d', strtotime(date('Y-m-d 00:00:00').
                           ' -'.$graph['default_delay'].' day')),
            'end'       => date('Y-m-d', strtotime(date('Y-m-d 00:00:00').' -1 day')),
         ];
      }

      foreach ($graphs as $graph) {
         $_REQUEST = ['switchto'        => 'png',
                      'short_classname' => $graph['class'],
                      'f_name'          => $graph['method'],
                      'gtype'           => $graph['type'],
                      'date1PluginMreporting'.$graph['class'].$graph['method'] => $graph['start'],
                      'date2PluginMreporting'.$graph['class'].$graph['method'] => $graph['end'],
                      'randname'        => 'PluginMreporting'.$graph['class'].$graph['method'],
                      'hide_title'      => false]; //New code

         ob_start();
         $common = new PluginMreportingCommon();
         $common->showGraph($_REQUEST, false, 'PNG');
         $content = ob_get_clean();

         preg_match_all('/<img .*?(?=src)src=\'([^\']+)\'/si', $content, $matches);

         // find image content
         if (!isset($matches[1][2])) {
            continue;
         }
         $image_base64 = $matches[1][2];
         if (strpos($image_base64, 'data:image/png;base64,') === false) {
            if (isset($matches[1][3])) {
               $image_base64 = $matches[1][3];
            }
         }
         if (strpos($image_base64, 'data:image/png;base64,') === false) {
            continue;
         }

         // clean image
         $image_base64  = str_replace('data:image/png;base64,', '', $image_base64);

         $image         = imagecreatefromstring(base64_decode($image_base64));
         $image_width   = imagesx($image);
         $image_height  = imagesy($image);

         $format = '%e';
         if (strftime('%Y', strtotime($graph['start'])) != strftime('%Y', strtotime($graph['end']))) {
            $format .= ' %B %Y';
         } else if (strftime('%B', strtotime($graph['start'])) != strftime('%B', strtotime($graph['end']))) {
            $format .= ' %B';
         }

         $image_title  = $LANG['plugin_mreporting'][$graph['class']][$graph['method']]['title'];
         $image_title .= " du ".strftime($format, strtotime($graph['start']));
         $image_title .= " au ".strftime('%e %B %Y', strtotime($graph['end']));

         array_push($images, ['title'  => $image_title,
                              'base64' => $image_base64,
                              'width'  => $image_width,
                              'height' => $image_height]);
      }

      $file_name = 'glpi_report_'.$user_name.date('d-m-Y').'.pdf';

      $pdf = new PluginMreportingPdf();
      $pdf->Init();
      $pdf->Content($images);
      $pdf->Output($dir.'/'.$file_name, 'F');

      // Return the generated filename
      return $file_name;
   }
}
