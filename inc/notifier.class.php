<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

require_once ('../lib/php-amqplib/vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PluginAmqpNotifier
{
     static function sendAMQPMessage ($msg_body)
     {
          /* get configuration */
          $config = new PluginAmqpConfig ();
          $config->getFromDB (1);

          /* connect to AMQP socket */
          $conn = new AMQPConnection (
               $config->getField ('host'),
               $config->getField ('port'),
               $config->getField ('user'),
               $config->getField ('pass'),
               $config->getField ('vhost');
          );

          $channel = $conn->channel ();

          /* Declare exchange if not exist */
          $channel->exchange_declare ($config->getField ('exchange'), 'topic', false, true, false);

          /* build routing key */
          $msg_rk = $msg_body['connector'].".".$msg_body['connector_name'].".".$msg_body['event_type'].".".$msg_body['source_type'].".".$msg_body['component'];

          if ($msg_body['source_type'] == 'resource')
          {
               $msg_rk .= ".".$msg_body['resource'];
          }

          /* generate AMQP message */
          $msg_raw = json_encode ($msg_body);

          $msg = new AMQPMessage ($msg_raw, array ("content-type" => "application/json", "delivery_mode" => 2));

          /* publish event */
          $channel->basic_publish ($msg, $config->getField ('exchange'), $msg_rk);

          /* close connection */
          $channel->close ();
          $conn->close ();
     }

     static function add_item (CommonDBTM $item)
     {
     }

     static function update_item (CommonDBTM $item)
     {
     }

     static function delete_item (CommonDBTM $item)
     {
     }

     static function purge_item (CommonDBTM $item)
     {
     }

     static function restore_item (CommonDBTM $item)
     {
     }
}
