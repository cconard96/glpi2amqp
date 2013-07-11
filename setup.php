<?php

function plugin_init_amqp ()
{
     global $PLUGIN_HOOKS;

     Plugin::registerClass ('PluginAmqpConfig', array ('addtabon' => 'Config'));

     $PLUGIN_HOOKS['pre_item_add']['amqp']     = array ('Ticket' => 'plugin_pre_item_add_amqp');
     $PLUGIN_HOOKS['pre_item_update']['amqp']  = array ('Ticket' => 'plugin_pre_item_update_amqp');
     $PLUGIN_HOOKS['pre_item_delete']['amqp']  = array ('Ticket' => 'plugin_pre_item_delete_amqp');
     $PLUGIN_HOOKS['pre_item_purge']['amqp']   = array ('Ticket' => 'plugin_pre_item_purge_amqp');
     $PLUGIN_HOOKS['pre_item_restore']['amqp'] = array ('Ticket' => 'plugin_pre_item_restore_amqp');

     if (Session::haveRight ("config", "w"))
     {
          $PLUGIN_HOOKS['config_page']['amqp'] = 'front/config.form.php';
     }
}

function plugin_version_amqp ()
{
     return array (
          "name"           => "AMQP",
          "version"        => "0.1",
          "author"         => "David Delassus <ddelassus@capensis.fr>",
          "license"        => "GPLv2+",
          "homepage"       => "http://github.com/linkdd/amqp",
          "minGlpiVersion" => "0.83"
     );
}

function plugin_amqp_check_prerequisites ()
{
     if (version_compare (GLPI_VERSION, "0.83", "lt"))
     {
          echo "This plugin requires GLPI >= 0.83";
          return false;
     }

     return true;
}

function plugin_amqp_check_config ($verbose = false)
{
     return true;
}
