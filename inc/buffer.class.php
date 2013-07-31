<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

class PluginAmqpBuffer extends CommonDBTM
{
     static function save_event ($event)
     {
          $row = array (
               "msg"  => json_encode ($event)
          );

          $buffer = new PluginAmqpBuffer ();
          $buffer->update ($row);
     }
}
