<?php

define ('GLPI_ROOT', __DIR__.'/../../..');
include (GLPI_ROOT.'/inc/includes.php');

PluginAmqpNotifier::statistics ();

/* for each tickets */
$tfinder = new Ticket ();
$tickets = $tfinder->find ();

foreach ($tickets as $tid => $ticket)
{
     /* associate with each clients */
     $ufinder = new Ticket_User ();
     $users   = $ufinder->find ("tickets_id = ".$tid." AND type = 1");

     foreach ($users as $id => $row)
     {
          $user = new User ();
          $user->getFromDB ($row['users_id']);

          $event = array (
               "connector"       => "glpi",
               "connector_name"  => "glpi2amqp",
               "component"       => $user->getField ('name'),
               "source_type"     => "component",
               "event_type"      => "log",
               "timestamp"       => time (),
               "state"           => 0,
               "output"          => "Client ".$user->getField ('name')." associated with ticket #".$tid,
               "display_name"    => "Client ".$user->getField ('name')." associated with ticket #".$tid,
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