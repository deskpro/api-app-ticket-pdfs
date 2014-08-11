<?php

namespace DeskPRO\Tests\PdfTicketList;

require 'vendor/autoload.php';

use DeskPRO\PdfTicketList\GenerateTicketsPdf;
use Mockery as m;

class GenerateTicketsPdfTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAcceptArrayOfTicketsAndOutputTheirTitles()
    {
        $mpdf = $this->getMockMpdf();

        $mpdf->shouldReceive('WriteHTML')->with('<p class="ticket-title">Ticket #1234: <em>A subject about the ticket</em> - from Jimmy</p>')->once();
        $mpdf->shouldReceive('WriteHTML')->with('<p class="ticket-title">Ticket #199984: <em>Another subject about the ticket</em> - from Robbie</p>')->once();
        $mpdf->shouldReceive('WriteHTML')->with(m::any());
        $mpdf->shouldReceive('AddPage')->twice();
        $mpdf->shouldReceive('Output')->with('filename.pdf', 'F')->once();

        $tickets = array(
            array(
                'id' => "1234",
                'subject' => "A subject about the ticket",
                'name' => "Jimmy",
            ),
            array(
                'id' => "199984",
                'subject' => "Another subject about the ticket",
                'name' => "Robbie",
            ),
        );

        $generator = new GenerateTicketsPdf($mpdf, $tickets);
        $generator->output('filename.pdf');
    }

    private function getMockMpdf()
    {
        return m::mock('\mPDF');
    }

    public function tearDown()
    {
        m::close();
    }
}

