<?php

function plugin_amqp_install ()
{
     global $DB;

     if (!TableExists ('glpi_plugin_amqp_configs'))
     {
          $query = "CREATE TABLE `glpi_plugin_amqp_configs` (
               `id` INT(11) NOT NULL auto_increment,
               `host` VARCHAR(255) NOT NULL,
               `port` INT(11) NOT NULL,
               `user` VARCHAR(255) NOT NULL,
               `password` VARCHAR(255) NOT NULL,
               `vhost` VARCHAR(255) NOT NULL,
               `exchange` VARCHAR(255) NOT NULL,
               `cron_interval` INT(11) NOT NULL,
               PRIMARY KEY (`id`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

          $DB->query ($query) or die ('Error while creating configuration table: '.$DB->error ());

          $query = "INSERT INTO `glpi_plugin_amqp_configs`
               (`id`, `host`, `port`, `user`, `password`, `vhost`, `exchange`, `cron_interval`)
          VALUES (
               NULL,
               '127.0.0.1',
               5672,
               'guest',
               'guest',
               'myvhost',
               'myvhost.events',
               3600
          )";

          $DB->query ($query) or die ('Error while creating default configuration: '.$DB->error ());
     }

     return true;
}

function plugin_amqp_uninstall ()
{
     global $DB;

     if (TableExists ('glpi_plugin_amqp_configs'))
     {
          $query = "DROP TABLE `glpi_plugin_amqp_configs`";
          $DB->query ($query) or die ('Error while cleaning configuration table: '.$DB->error ());
     }

     return true;
}

function cron_plugin_amqp ()
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
               "resource"        => "cron",
               "timestamp"       => time (),
               "source_type"     => "resource",
               "event_type"      => "log",
               "state"           => 0,
               "perf_data_array" => array ()
          );

          /* loop over all returned rows to build the metrics */
          while ($row = mysql_fetch_assoc ($result))
          {
               switch ($row['status'])
               {
                    case 'closed':
                         /* send average time only if the ticket is closed */

                         $event['perf_data_array'][] = array (
                              "metric" => "tickets_time_avg",
                              "value"  => int ($row['avgtime']),
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
                              "value"  => int ($row['total']),
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
          PluginAmqpNotifier::sendAMQPMessage ($event);
     }
     else
     {
          /* log possible error */
          error_log ("Error while getting data from database: ".$DB->error ());
     }
}

function plugin_pre_item_add_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::add_item ($item);
     }
}

function plugin_pre_item_update_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::update_item ($item);
     }
}

function plugin_pre_item_delete_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::delete_item ($item);
     }
}

function plugin_pre_item_purge_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::purge_item ($item);
     }
}

function plugin_pre_item_restore_amqp ($item)
{
     if ($item instanceof Ticket)
     {
          return PluginAmqpNotifier::restore_item ($item);
     }
}
