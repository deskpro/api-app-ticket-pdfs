Converting your tickets to PDF documents
========================================

This app uses the DeskPRO API to download your tickets and then convert them to PDFs.

Download dependencies with composer
-----------------------------------

This example uses composer to install mpdf and the DeskPRO PHP API library. Run the `install` command from the project directory to install them:

    cd path/to/api-app-ticket-pdfs
    php composer.phar install

Refer to the composer website for instructions on how to install composer on your computer:
[https://getcomposer.org/doc/01-basic-usage.md](https://getcomposer.org/doc/01-basic-usage.md)

Usage
-----

*1. Set API details*

Update config.php to set your DeskPRO API credentials

*2. Prepare ticket data*

From the command-line, execute the `cmd-downlaod-tickets.php` command to download your ticket data from your site:

    $ php cmd-downlaod-tickets.php
    Written!

*3. Generate PDFs*

Once you have downlaoded your ticket data, you can run the `cmd-create-pdfs.php` command to generate PDF files:

    $ php cmd-create-pdfs.php
    Just did page 1 of 1.
