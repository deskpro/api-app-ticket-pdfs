<?php

require 'vendor/autoload.php';
require 'src/CollectTickets.php';

use DeskPRO\PdfTicketList\CollectTickets;

$config = require 'config.php';

$api = new \DpApi($config['deskpro_url'], $config['api_key']);
$collector = new CollectTickets($api);

$tickets = serialize($collector->getMostRecentTickets(5));

$cache = fopen('ticketsCache.txt', 'w+');
fwrite($cache, $tickets);

echo "Written!";
