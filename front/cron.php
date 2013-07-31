<?php

define ('GLPI_ROOT', __DIR__.'/../../..');
include (GLPI_ROOT.'/inc/includes.php');

/* get all buffered message */
$messages = PluginAmqpBuffer::get_events ();

foreach ($messages as $id => $row)
{
     $message = json_decode ($row['msg'], TRUE);

     /* try to send it again on AMQP */
     if (PluginAmqpNotifier::sendAMQPMessage ($message))
     {
          /* if succeed, drop it from database */
          PluginAmqpBuffer::delete_event ($message);
     }
}