<?php

if (!defined ('GLPI_ROOT'))
{
     die ("Sorry. You can't access directly to this file");
}

class PluginAmqpConfig extends CommonDBTM
{
     function getTabNameForItem (CommonGLPI $item, $withtemplate = 0)
     {
          if (!$withtemplate)
          {
               if ($item->getType () == 'Config')
               {
                    return 'AMQP plugin';
               }
          }

          return '';
     }

     function canCreate ()
     {
          return plugin_amqp_haveRight ('config', 'w');
     }

     function canView ()
     {
          return plugin_amqp_haveRight ('config', 'r');
     }

     function showForm ()
     {
          $id = $this->getFromDB (1);

          ?>
               <form name="form" action="config.form.php" method="post">
                    <div class="center" id="tabsbody">
                         <table class="tab_cadre_fixe">
                              <tr><th colspan="4">AMQP setup</th></tr>
                              <tr>
                                   <td>AMQP host</td>
                                   <td colspan="3">
                                        <input type="text" name="amqp_hostname" />
                                   </td>
                              </tr>
                              <tr>
                                   <td>AMQP Port</td>
                                   <td colspan="3">
                                        <input type="text" name="amqp_port" value="5672" />
                                   </td>
                              </tr>
                              <tr>
                                   <td>AMQP User</td>
                                   <td colspan="3">
                                        <input type="text" name="amqp_user" />
                                   </td>
                              </tr>
                              <tr>
                                   <td>AMQP Password</td>
                                   <td colspan="3">
                                        <input type="password" name="amqp_password" />
                                   </td>
                              </tr>
                              <tr>
                                   <td>AMQP Virtual Host</td>
                                   <td colspan="3">
                                        <input type="text" name="amqp_vhost" />
                                   </td>
                              </tr>
                              <tr>
                                   <td>AMQP Exchange</td>
                                   <td colspan="3">
                                        <input type="text" name="amqp_exchange" />
                                   </td>
                              </tr>
                              <tr class="tab_bg_2">
                                   <td colspan="4" class="center">
                                        <input type="hidden" name="id" value="1" class="submit" />
                                        <input type="submit" name="update" class="submit" value="modifier" />
                                   </td>
                              </tr>
                         </table>
                    </div>
               </form>
          <?php

          Html::closeForm ();

          return true;
     }
}
