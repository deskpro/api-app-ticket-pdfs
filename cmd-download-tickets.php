<?php

require __DIR__ . '/vendor/autoload.php';
require  __DIR__ . '/src/PdfTicketList/CollectTickets.php';
require  __DIR__ . '/src/Api/Paginator.php';

use DeskPRO\PdfTicketList\CollectTickets;
use DeskPROClient\Api\DeskPROApi;

$config = require 'config.php';
$api = new DeskPROApi($config['deskpro_url'], $config['authHeader']);
$collector = new CollectTickets($api);

$tickets = serialize($collector->getMostRecentTickets(5));

$cache = fopen('ticketsCache.txt', 'w+');
fwrite($cache, $tickets);

echo "Written!";
