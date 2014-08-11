<?php

namespace DeskPRO\PdfTicketList;

class CollectTickets
{
    const MOST_RECENT = 'ticket.date_created:desc';

    private $api;

    public function __construct(\DpApi $api)
    {
        $this->api = $api;
    }

    public function getMostRecentTickets($numberOfTickets = 50)
    {
        $page = 1;
        $lastRetrievalCount = -1;
        $tickets = array();

        while($numberOfTickets > count($tickets) && $lastRetrievalCount !== 0) {
            $requiredNumberOfTicketsLeft = $numberOfTickets - count($tickets);

            $newResults = $this->api->findTickets(array(), $page, self::MOST_RECENT);
            $ticketsWeWant = array_slice($newResults['tickets'], 0, $requiredNumberOfTicketsLeft);
            $lastRetrievalCount = count($ticketsWeWant);

            foreach ($ticketsWeWant as $ticket) {
                $messages = $this->api->getTicketMessages($ticket['id']);

                $tickets[] = $this->getTicketPdfView($ticket, $messages['messages']);
            }

            $page++;
        }

        return $tickets;
    }

    /**
     * Just grab all the information we need to generate the pdf.
     *
     * Means we don't have to pass around all of the data, we don't really need.
     *
     * @param array $ticket
     */
    private function getTicketPdfView(array $ticket, array $messages)
    {
        // boring array for now, will probably change to value object
        $view = array();

        $view['id'] = $ticket['id'];
        $view['subject'] = $ticket['subject'];
        $view['name'] = $ticket['person']['name'];

        $view['messages'] = $this->getMessagePdfView($messages);

        return $view;
    }

    private function getMessagePdfView(array $messages)
    {
        $messageView = array();

        foreach ($messages as $message) {
            $view = array(
                'id' => $message['id'],
                'date_created' => $message['date_created'],
                'from_name' => $message['person']['name'],
                'is_from_agent' => $message['person']['is_agent'],
                'is_agent_note' => $message['is_agent_note'],
                'body' => $message['message'],
            );

            $view['attachments'] = array();
            foreach ($message['attachments'] as $attachment) {
                $attachment = array(
                    'authcode' => $attachment['blob']['authcode'],
                    'filename' => $attachment['blob']['filename'],
                    'absolute_url' => $attachment['blob']['download_url'],
                );

                $view['attachments'][] = $attachment;
            }

            $messageView[] = $view;
        }

        return $messageView;
    }
}

