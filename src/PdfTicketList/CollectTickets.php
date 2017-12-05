<?php

namespace DeskPRO\PdfTicketList;

use DeskPROClient\Api\DeskPROApi;
use DeskPRO\Api\Paginator;

class CollectTickets
{
    const ITEMS_PER_PAGE_MAX_COUNT = 50;
    
    /**
     *
     * @var DeskPROApi
     */
    private $api;

    /**
     *
     * @param DeskPROApi $api
     */
    public function __construct(DeskPROApi $api)
    {
        $this->api = $api;
    }

    /**
     *
     * @param int $numberOfTickets
     * @return array
     */
    public function getMostRecentTickets($numberOfTickets = 50)
    {
        $tickets = array();
        $ticketsRequest = $this->api->tickets()
            ->find()
            ->orderBy('date_created')
            ->orderDir('desc')
            ->count(min(self::ITEMS_PER_PAGE_MAX_COUNT, $numberOfTickets))
            ->sideload(['person']);

        foreach ((new Paginator($ticketsRequest)) as $ticketsResponse) {
            
            foreach ($ticketsResponse->getData() as $ticketData) {

                $linked = array(
                    'person' => $ticketsResponse->getLinked()['person'],
                    'ticket_attachment' => array()
                );

                // collect all ticket messages and linked data
                $messagesData = array();
                $messagesRequest = $this->api->tickets()
                    ->get($ticketData['id'])
                    ->messages()
                    ->find()
                    ->count(self::ITEMS_PER_PAGE_MAX_COUNT)
                    ->sideload(['ticket_attachment', 'person']);

                foreach ((new Paginator($messagesRequest)) as $messagesResponse) {

                    $messagesData = array_merge($messagesData, $messagesResponse->getData());
                    $linked['person'] += $messagesResponse->getLinked()['person'];
                    $linked['ticket_attachment'] += isset($messagesResponse->getLinked()['ticket_attachment'])
                                                    ? $messagesResponse->getLinked()['ticket_attachment']
                                                    : array();
                }

                $tickets[] = $this->getTicketPdfView(
                    $ticketData,
                    $messagesData,
                    $linked
                );

                if (count($tickets) >= $numberOfTickets) {
                    return $tickets;
                }
            }
        }

        return $tickets;
    }

    /**
     * Just grab all the information we need to generate the pdf.
     *
     * Means we don't have to pass around all of the data, we don't really need.
     *
     * @param array $ticket
     * @param array $messages
     * @param array $linked
     * @return array
     */
    private function getTicketPdfView(array $ticket, array $messages, array $linked)
    {
        // boring array for now, will probably change to value object
        $view = array();

        $view['id'] = $ticket['id'];
        $view['subject'] = $ticket['subject'];
        $view['name'] = $linked['person'][$ticket['person']]['name'];

        $view['messages'] = $this->getMessagePdfView($messages, $linked);

        return $view;
    }

    /**
     *
     * @param array $messages
     * @param array $linked
     * @return array
     */
    private function getMessagePdfView(array $messages, array $linked)
    {
        $messageView = array();

        foreach ($messages as $message) {
            $view = array(
                'id' => $message['id'],
                'date_created' => $message['date_created'],
                'from_name' => $linked['person'][$message['person']]['name'],
                'is_from_agent' => $linked['person'][$message['person']]['is_agent'],
                'is_agent_note' => $message['is_agent_note'],
                'body' => $message['message'],
            );

            $view['attachments'] = array();
            foreach ($message['attachments'] as $attachment) {
                $attachment = array(
                    'authcode' => $linked['ticket_attachment'][$attachment]['blob']['blob_auth'],
                    'filename' => $linked['ticket_attachment'][$attachment]['blob']['filename'],
                    'absolute_url' => $linked['ticket_attachment'][$attachment]['blob']['download_url'],
                );

                $view['attachments'][] = $attachment;
            }

            $messageView[] = $view;
        }

        return $messageView;
    }
}

