<?php

define ('GLPI_ROOT', __DIR__.'/../../..');
include (GLPI_ROOT.'/inc/includes.php');

/* get all buffered message */
$buffer = new PluginAmqpBuffer ();
$buffers = $buffer->find ();

foreach ($buffers as $row)
{
     $buf = $buffer->getFromDB ($row['id']);

     /* send message */
     $event = json_decode ($buf->getField ('msg'));

     if (PluginAmqpNotifier::sendAMQPMessage ($event))
     {
          /* if the message was sent, delete it from the buffer */
          $buf->deleteFromDB ();
     }
}