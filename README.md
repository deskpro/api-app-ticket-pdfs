Converting your tickets to PDF documents
========================================

This app uses the DeskPRO API to download your tickets and then convert them to PDFs.

Download dependencies with composer
-----------------------------------

This example uses composer to install mpdf and the DeskPRO PHP API library. Run the `install` command from the project directory to install them:

    cd path/to/api-app-ticket-pdfs
    php composer.phar install

Usage
-----

*1. Prepare ticket data*

From the command-line, execute the `cmd-downlaod-tickets.php` command to download your ticket data from your site:

    $ php cmd-create-pdfs.php
    Written!

*2. Generate PDFs*

Once you have downlaoded your ticket data, you can run the `cmd-create-pdfs.php` command to generate PDF files:

    $ php cmd-create-pdfs.php
    Just did page 1 of 1.