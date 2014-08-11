<?php

namespace DeskPRO\Tests\PdfTicketList;

require 'vendor/autoload.php';

use DeskPRO\PdfTicketList\CollectTickets;
use Mockery as m;

class CollectTicketsTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldGetTicketsForExpectedSite()
    {
        $dummyTickets = $this->getDummyTickets(2);

        $mockApi = $this->getMockDpApi();
        $mockApi
            ->shouldReceive('findTickets')
            ->with(array(), 1, CollectTickets::MOST_RECENT)
            ->andReturn($dummyTickets);

        $collector = new CollectTickets($mockApi);

        $this->assertCount(2, $collector->getMostRecentTickets(2));
    }

    public function testShouldGetSpecificCountOfTickets()
    {
        $mockApi = $this->getMockDpApi();
        // we're looking for 30, and the API we're mocking returns elements in batches of 25.
        // we should never be asking for anything other than the 1st or 2nd page, if we are, something
        // is wrong.
        $mockApi->shouldReceive('findTickets')->with(array(), m::anyOf(1, 2), CollectTickets::MOST_RECENT)->andReturn($this->getDummyTickets(25));

        $collector = new CollectTickets($mockApi);

        $this->assertEquals(30, count($collector->getMostRecentTickets(30)));
    }

    /**
     * There's lots of data return with a ticket API call - we just need some of it.
     */
    public function testShouldGetBackASimplfiedTicketView()
    {
        $expectedTicketView = array(
            array(
                'id' => 1020,
                'subject' => 'Subject 1',
                'name' => 'User name1',

                'messages' => array(
                    array(
                        'id' => 76515,
                        'from_name' => 'James Voice',
                        'date_created' => "2014-02-26 11:56:03",
                        'body' => 'Body of the message',
                        'is_from_agent' => false,
                        'is_agent_note' => false,
                        'attachments' => array(
                            array(
                                'authcode' => '87987dkjds88sdkks8',
                                'filename' => 'foobar.png',
                                'absolute_url' => 'http://support.deskpro.com/file.php/87987dkjds88sdkks8/foobar.png',
                            )
                        )
                    )
                ),
            )
        );

        $ticketView = $expectedTicketView[0];
        $messageView = $ticketView['messages'][0];
        $attachmentView = $messageView['attachments'][0];

        // this is designed to look exactly like the api response we'll get
        $mockApiResponse = array(
            'tickets' => array(
                array(
                    'id' => $ticketView['id'],
                    'subject' => $ticketView['subject'],
                    'person' => array(
                        'name' => $ticketView['name'],
                    ),
                )
            ),
        );

        $mockApi = m::mock('\DpApi');
        $mockApi->shouldReceive('findTickets')->with(array(), m::anyOf(1), CollectTickets::MOST_RECENT)->andReturn($mockApiResponse);

        $mockApiResponseMessages = array(
            'messages' => array(
                array(
                    'id' => $messageView['id'],
                    'date_created' => $messageView['date_created'],
                    'message' => $messageView['body'],
                    'is_agent_note' => false,
                    'person' => array(
                        'name' => $messageView['from_name'],
                        'is_agent' => false
                    ),
                    'attachments' => array(
                        array(
                            'blob' => array(
                                'authcode' => $attachmentView['authcode'],
                                'filename' => $attachmentView['filename'],
                                'download_url' => $attachmentView['absolute_url'],
                            )
                        ),
                    ),
                ),
            ),
        );

        $mockApi->shouldReceive('getTicketMessages')->with($expectedTicketView[0]['id'])->andReturn($mockApiResponseMessages);

        $collector = new CollectTickets($mockApi);

        $this->assertEquals($expectedTicketView, $collector->getMostRecentTickets(1));
    }

    public function getMockDpApi()
    {
        $mockApi = m::mock('\DpApi');

        $mockApi->shouldReceive('getTicketMessages')->with(m::any())->andReturn(array('messages' => array()));

        return $mockApi;
    }

    private function getDummyTickets($amount)
    {
        $tickets = array();

        for($i = 0; $i < $amount; $i++) {
            $id = uniqid("ticket");

            $tickets[] = array(
                'id' => $id,
                'subject' => 'Welcome to DeskPro',
                'name' => 'Rakesh',
            );
        }

        return array('tickets' => $tickets);
    }
}

