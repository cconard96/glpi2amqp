# glpi2amqp

Connector between GLPI ( http://www.glpi-project.org ) and AMQP.

Send events on AMQP sockets when tickets are added, updated, deleted, ...
Some data are sent regularly on the AMQP socket (via the internal Cron of GLPI).

# Requirements

Follow the instruction on [this page](http://www.php.net/manual/en/amqp.setup.php)
to install the PECL AMQP extension.

Do not forget to add ``extension=amqp.so`` in your ``php.ini``.

# Installation

In the GLPI root directory :

    # cd plugins
    # git clone https://github.com/linkdd/glpi2amqp.git amqp

Then, in the plugins configuration of GLPI, install and activate the plugin.

Now, you have to edit the crontab of your apache user (on Debian : ``www-data``) :

    # crontab -u www-data -e
    */1 * * * * /usr/bin/php5 /var/www/glpi/plugins/amqp/front/cron.php &>/dev/null

Do not forget to replace ``/var/www/glpi`` by the path to your GLPI root directory.

