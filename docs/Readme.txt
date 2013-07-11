# glpi2amqp

Connector between GLPI ( http://www.glpi-project.org ) and AMQP.

Send events on AMQP sockets when tickets are added, updated, deleted, ...
Some data are sent regularly on the AMQP socket (via the internal Cron of GLPI).

# Installation

In the GLPI root directory :

    # cd plugins
    # git clone https://github.com/linkdd/glpi2amqp.git amqp

Then, in the plugins configuration of GLPI, install and activate the plugin.
