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
               PRIMARY KEY (`id`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

          $DB->query ($query) or die ('Error while creating configuration table: '.$DB->error ());

          $query = "INSERT INTO `glpi_plugin_amqp_configs`
               (`id`, `host`, `port`, `user`, `password`, `vhost`, `exchange`)
          VALUES (
               NULL,
               '127.0.0.1',
               5672,
               'guest',
               'guest',
               'myvhost',
               'myvhost.events'
          )";

          $DB->query ($query) or die ('Error while creating default configuration: '.$DB->error ());
     }

     if (!TableExists ('glpi_plugin_amqp_buffer'))
     {
          $query = "CREATE TABLE `glpi_plugin_amqp_buffer` (
               `id` INT(11) NOT NULL auto_increment,
               `msg` TEXT NOT NULL,
               PRIMARY KEY (`id`)
          ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

          $DB->query ($query) or die ('Error while creating buffer table: '.$DB->error ());
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

     if (TableExists ('glpi_plugin_amqp_buffer'))
     {
          $query = "DROP TABLE `glpi_plugin_amqp_buffer`";
          $DB->query ($query) or die ('Error while cleaning buffer table: '.$DB->error ());
     }

     return true;
}

function plugin_amqp_item_add ($item)
{
     PluginAmqpNotifier::statistics ();

     if ($item instanceof Ticket_User)
     {
          /* get user */
          $user = new User ();
          $user->getFromDB ($item->getField ('users_id'));

          /* notify component */
          $event = array (
               "connector"       => "glpi",
               "connector_name"  => "glpi2amqp",
               "component"       => $user->getField ('name'),
               "source_type"     => "component",
               "event_type"      => "log",
               "timestamp"       => time (),
               "state"           => 0,
               "output"          => "Client ".$user->getField ('name')." associated",
               "long_output"     => "Client ".$user->getField ('name')." associated",
               "display_name"    => "Client ".$user->getField ('name')." associated",
               "perf_data_array" => array (
                    array (
                         "metric" => "ticket",
                         "value"  => 1,
                         "unit"   => NULL,
                         "min"    => 0,
                         "max"    => NULL,
                         "warn"   => NULL,
                         "crit"   => NULL,
                         "type"   => "COUNTER"
                    )
               )
          );

          if (!PluginAmqpNotifier::sendAMQPMessage ($event))
          {
               PluginAmqpBuffer::save_event ($event);
          }
     }
}

function plugin_amqp_item_update ($item)
{
     PluginAmqpNotifier::statistics ();

     if ($item instanceof Ticket)
     {
          $ufinder = new Ticket_User ();
          $users   = $ufinder->find ("tickets_id = ".$item->getID ()." AND type = 1");

          foreach ($users as $id => $row)
          {
               $event = PluginAmqpNotifier::item_to_event ($row['name'], $item);
               $event['output'] = 'Update item #'.$item->getID ();

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }
}

function plugin_amqp_item_delete ($item)
{
     PluginAmqpNotifier::statistics ();

     if ($item instanceof Ticket)
     {
          $ufinder = new Ticket_User ();
          $users   = $ufinder->find ("tickets_id = ".$item->getID ()." AND type = 1");

          foreach ($users as $id => $row)
          {
               $event = PluginAmqpNotifier::item_to_event ($row['name'], $item);
               $event['output'] = 'Delete item #'.$item->getID ();

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }
}

function plugin_amqp_item_purge ($item)
{
     PluginAmqpNotifier::statistics ();

     if ($item instanceof Ticket)
     {
          $ufinder = new Ticket_User ();
          $users   = $ufinder->find ("tickets_id = ".$item->getID ()." AND type = 1");

          foreach ($users as $id => $row)
          {
               $event = PluginAmqpNotifier::item_to_event ($row['name'], $item);
               $event['output'] = 'Purge item #'.$item->getID ();

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }
}

function plugin_amqp_item_restore ($item)
{
     PluginAmqpNotifier::statistics ();

     if ($item instanceof Ticket)
     {
          $ufinder = new Ticket_User ();
          $users   = $ufinder->find ("tickets_id = ".$item->getID ()." AND type = 1");

          foreach ($users as $id => $row)
          {
               $event = PluginAmqpNotifier::item_to_event ($row['name'], $item);
               $event['output'] = 'Restore item #'.$item->getID ();

               if (!PluginAmqpNotifier::sendAMQPMessage ($event))
               {
                    PluginAmqpBuffer::save_event ($event);
               }
          }
     }
}
