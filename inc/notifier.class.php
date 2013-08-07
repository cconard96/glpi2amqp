<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

class PluginAmqpNotifier
{
     static function sendAMQPMessage ($msg_body)
     {
          /* get configuration */
          $config = new PluginAmqpConfig ();
          $config->getFromDB (1);

          $cred = array (
               'host'     => $config->getField ('host'),
               'port'     => $config->getField ('port'),
               'login'    => $config->getField ('user'),
               'password' => $config->getField ('password'),
               'vhost'    => $config->getField ('vhost')
          );

          error_log ("Initialize amqp://".$cred['login']."@".$cred['host'].":".$cred['port']."/".$cred['vhost']."...");

          /* connect to AMQP socket */
          try
          {
               $conn = new AMQPConnection ($cred);
               $conn->connect ();
               $channel = new AMQPChannel ($conn);

               /* Declare exchange if not exist */
               error_log ("Declare AMQP exchange ".$config->getField ('exchange')."...");

               $ex = new AMQPExchange ($channel);
               $ex->setName ($config->getField ('exchange'));
               $ex->setType (AMQP_EX_TYPE_TOPIC);
               $ex->setFlags (AMQP_PASSIVE);
               $ex->declareExchange ();

               /* build routing key */
               $msg_rk = $msg_body['connector'].".".$msg_body['connector_name'].".".$msg_body['event_type'].".".$msg_body['source_type'].".".$msg_body['component'];

               if ($msg_body['source_type'] == 'resource')
               {
                    $msg_rk .= ".".$msg_body['resource'];
               }

               /* generate AMQP message */
               $msg_raw = json_encode ($msg_body);

               /* publish event */
               error_log ("Send AMQP message #".$msg_rk.": ".$msg_raw);

               $msg = $ex->publish ($msg_raw, $msg_rk);

               if (!$msg)
               {
                    error_log ("Error: AMQP message '".$msg."' not sent.");
                    return false;
               }
               else
               {
                    error_log ("Success: AMQP message '".$msg."' sent.");
               }

               $conn->disconnect ();
          }
          catch (AMQPException $e)
          {
               error_log ("Error AMQP: ".$e->getMessage ());
               return false;
          }

          return true;
     }

     static function get_item_users (CommonDBTM $item, $type)
     {
          $finder  = new Ticket_User ();
          $results = $finder->find ("tickets_id = ".$item->getField ("id")." AND type = ".$type);
          $users   = array ();

          foreach ($results as $id => $row)
          {
               $user = new User ();
               $user->getFromDB ($row['users_id']);

               $users[] = $user;
          }

          return $users;
     }

     static function item_to_event (CommonDBTM $item)
     {
          $event = array (
               "connector"      => "glpi",
               "connector_name" => "glpi2amqp",
               "resource"       => "item".$item->getField ("id"),
               "source_type"    => "resource",
               "timestamp"      => time (),
               "event_type"     => "log",
               "state"          => 0,
               "display_name"   => $item->getField ("name"),
               "item_data"      => array (
                    "id"        => $item->getField ("id"),
                    "name"      => $item->getField ("name"),
                    "status"    => $item->getField ("status"),
                    "content"   => $item->getField ("content"),
                    "urgency"   => $item->getField ("urgency")
               )
          );

          return $event;
     }

     static function add_item (CommonDBTM $item)
     {
          $users = PluginAmqpNotifier::get_item_users ($item, 1);

          /* send event */
          foreach ($users as $user)
          {
               $event = PluginAmqpNotifier::item_to_event ($item);
               $event["component"] = $user["name"];
               $event["output"]    = "Add item";

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }

     static function update_item (CommonDBTM $item)
     {
          $users = PluginAmqpNotifier::get_item_users ($item, 1);

          /* send event */
          foreach ($users as $user)
          {
               $event = PluginAmqpNotifier::item_to_event ($item);
               $event["component"] = $user["name"];
               $event["output"]    = "Add item";

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }

     static function delete_item (CommonDBTM $item)
     {
          $users = PluginAmqpNotifier::get_item_users ($item, 1);

          /* send event */
          foreach ($users as $user)
          {
               $event = PluginAmqpNotifier::item_to_event ($item);
               $event["component"] = $user["name"];
               $event["output"]    = "Add item";

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }

     static function purge_item (CommonDBTM $item)
     {
          $users = PluginAmqpNotifier::get_item_users ($item, 1);

          /* send event */
          foreach ($users as $user)
          {
               $event = PluginAmqpNotifier::item_to_event ($item);
               $event["component"] = $user["name"];
               $event["output"]    = "Add item";

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }

     static function restore_item (CommonDBTM $item)
     {
          $users = PluginAmqpNotifier::get_item_users ($item, 1);

          /* send event */
          foreach ($users as $user)
          {
               $event = PluginAmqpNotifier::item_to_event ($item);
               $event["component"] = $user["name"];
               $event["output"]    = "Add item";

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }

     static function statistics ()
     {
          global $DB;

          $query = "SELECT
               status,
               COUNT(*) AS total,
               AVG(TIME_TO_SEC(TIMEDIFF(`closedate`, `date`))) AS avgtime
          FROM
               ".getTableForItemType ('Ticket')."
          GROUP BY `status`";

          /* This query will group all tickets by status, count how many items there
           * are in each groups, and calculate the average time passed on closed tickets.
           *
           * Result :
           *
           *  +--------+-------+-----------+
           *  | status | total |  avgtime  |
           *  +--------+-------+-----------+
           *  | assign |   6   |    NULL   |
           *  +--------+-------+-----------+
           *  | closed |  1975 | 634542.08 |
           *  +--------+-------+-----------+
           */

          $result = $DB->query ($query);

          if ($result)
          {
               /* build event for AMQP message */
               $event = array (
                    "connector"       => "glpi",
                    "connector_name"  => "glpi2amqp",
                    "component"       => "glpi",
                    "resource"        => "stats",
                    "timestamp"       => time (),
                    "source_type"     => "resource",
                    "event_type"      => "log",
                    "state"           => 0,
                    "perf_data_array" => array ()
               );

               /* loop over all returned rows to build the metrics */
               while ($row = $DB->fetch_assoc ($result))
               {
                    switch ($row['status'])
                    {
                         case 'closed':
                              /* send average time only if the ticket is closed */

                              $event['perf_data_array'][] = array (
                                   "metric" => "tickets_time_avg",
                                   "value"  => (int) ($row['avgtime']),
                                   "unit"   => "s",
                                   "min"    => 0,
                                   "max"    => NULL,
                                   "warn"   => NULL,
                                   "crit"   => NULL,
                                   "type"   => "GAUGE"
                              );

                         default:
                              /* add the number of tickets in the group */
                              $event['perf_data_array'][] = array (
                                   "metric" => "n_tickets_".$row['status'],
                                   "value"  => (int) ($row['total']),
                                   "unit"   => NULL,
                                   "min"    => 0,
                                   "max"    => NULL,
                                   "warn"   => NULL,
                                   "crit"   => NULL,
                                   "type"   => "GAUGE"
                              );

                              break;
                    }
               }

               /* now send the event */
               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
          else
          {
               /* log possible error */
               error_log ("Error while getting data from database: ".$DB->error ());
          }
     }
}
