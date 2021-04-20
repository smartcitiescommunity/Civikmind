<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 *  NotificationMailing class extends phpmail and implements the NotificationInterface
**/
class PluginMreportingNotificationMail extends NotificationMailing {

   /**
    * @param $options   array
   **/
   function sendNotification($options = []) {

      $mmail = new GLPIMailer();
      $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
      // For exchange
      $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");

      $mmail->SetFrom($options['from'], $options['fromname'], false);

      if ($options['replyto']) {
         $mmail->AddReplyTo($options['replyto'], $options['replytoname']);
      }
      $mmail->Subject  = $options['subject'];

      if (empty($options['content_html'])) {
         $mmail->isHTML(false);
         $mmail->Body = $options['content_text'];
      } else {
         $mmail->isHTML(true);
         $mmail->Body    = $options['content_html'];
         $mmail->AltBody = $options['content_text'];
      }

      $mmail->AddAddress($options['to'], $options['toname']);

      if (!empty($options['messageid'])) {
         $mmail->MessageID = "<".$options['messageid'].">";
      }

      // Attach pdf to mail
      $mmail->AddAttachment($options['attachment']['path'], $options['attachment']['name']);

      $messageerror = __('Error in sending the email');

      if (!$mmail->Send()) {
         $senderror = true;
         Session::addMessageAfterRedirect($messageerror."<br>".$mmail->ErrorInfo, true);
      } else {
         //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
         Toolbox::logInFile("mail", sprintf(__('%1$s: %2$s'),
                                            sprintf(__('An email was sent to %s'), $options['to']),
                                            $options['subject']."\n"));
      }

      $mmail->ClearAddresses();
      return true;
   }

}

