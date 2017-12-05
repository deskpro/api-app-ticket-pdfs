<?php

require 'vendor/autoload.php';
require 'src/PdfTicketList/GenerateTicketsPdf.php';

use DeskPRO\PdfTicketList\GenerateTicketsPdf;

// basically, because pdf generate takes a little while, and might time out, lets generate them in sets
$pageToGenerate = array_key_exists(1, $argv) ? $argv[1] : 1;
$numberPerPage = 100;

$cachedContents = @file_get_contents('ticketsCache.txt');

if (false == $cachedContents) {
    echo "You need to run collectAndStore.php (to generate a cache) before you can generate pdfs.";
    exit;
}

$allTickets = unserialize($cachedContents);

$pagesRequired = ceil(count($allTickets) / $numberPerPage);

$pagesTickets = array_slice($allTickets, $numberPerPage * ($pageToGenerate - 1), $numberPerPage);

$pdfGenerator = new GenerateTicketsPdf(new \Mpdf\Mpdf(), $pagesTickets);
$pdfGenerator->output('tickets-' . $pageToGenerate . '.pdf');

echo "Just did page $pageToGenerate there's a total of $pagesRequired required.";

