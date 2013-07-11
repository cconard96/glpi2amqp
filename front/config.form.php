<?php

define ('GLPI_ROOT', '../../..');
include (GLPI_ROOT.'/inc/includes.php');

$plugin = new Plugin ();

if ($plugin->isActivated ('amqp'))
{
     $config = new PluginAmqpConfig ();

     if (isset ($_POST['update']))
     {
          Session::checkRight ('config', 'w');
          $config->update ($_POST) or die ("Error while updating configuration.");
          Html::back ();
     }
     else
     {
          Html::header ('AMQP Plugin', $_SERVER['PHP_SELF'], 'config', 'plugins');

          $config->showForm ();

          Html::footer ();
     }
}
else
{
     Html::header ('configuration', '', 'config', 'plugins');

     ?>
          <div class="center">
               <br />
               <br />
               <img src="<?php echo $CFG_GLPI['root_doc']; ?>/pics/warning.png" alt="warning" />
               <br />
               <br />
               <b>Please activate the plugin</b>
          </div>
     <?php

     Html::footer ();
}
