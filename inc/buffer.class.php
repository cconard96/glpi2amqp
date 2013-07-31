<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

class PluginAmqpBuffer
{
     static function get_events ()
     {
          global $DB;

          $query = "SELECT * FROM glpi_plugin_amqp_buffer";

          $sqlres = $DB->query ($query) or die ("Can't retrieve messages in database: ".$DB->error ());

          $messages = array ();

          while ($row = $DB->fetch_assoc ($sqlres))
          {
               $messages[$row['id']] = $row;
          }

          return $messages;
     }

     static function delete_event ($msg)
     {
          global $DB;

          $query = "DELETE FROM glpi_plugin_amqp_buffer WHERE id = ".$msg['id'];
          $DB->query ($query) or die ("Can't delete message #".$msg['id']." in database: ".$DB->error ());
     }

     static function save_event ($event)
     {
          global $DB;

          $msg = json_encode ($event);

          $query = "INSERT INTO glpi_plugin_amqp_buffer (`msg`) VALUES ('".$msg."')";
          $DB->query ($query) or die ("Can't save message #".$msg['id']." to database: ".$DB->error ());
     }
}
