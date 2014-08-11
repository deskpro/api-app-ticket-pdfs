<?php

namespace DeskPRO\PdfTicketList;

class GenerateTicketsPdf
{
    private $tickets;
    private $mpdf;

    public function __construct(\mPDF $mpdf, array $tickets)
    {
        $this->pdfGenerator = $mpdf;
        $this->tickets = $tickets;
    }

    public function output($fileName)
    {
        $this->outputCss();
        $this->pdfGenerator->WriteHTML('<div class="ticket">');

        foreach ($this->tickets as $ticket) {
            $this->outputTicket($ticket);

            if (array_key_exists('messages', $ticket)) {
                foreach ($ticket['messages'] as $message) {
                    $this->outputMessage($message);
                }
            }

            $this->pdfGenerator->AddPage();
        }

        $this->pdfGenerator->WriteHTML("</div>");

        $this->pdfGenerator->Output($fileName, 'F');
    }

    private function outputCss()
    {
        $css = <<<CSS
<style>
    p, div {
        font-size: 9px;
    }

    .ticket-title {
        font-size: 23px;
    }

    .from-agent {
        border-color: green;
    }

    .from-user {
        border-color: blue;
    }

    .from-note {
        border-color: yellow;
    }

    .message {
        border: 2px solid #447799;
        margin: 10px;
        padding: 5px;
    }
</style>
CSS;

        $this->pdfGenerator->WriteHTML($css);
    }

    private function outputTicket(array $ticket)
    {
        $this->pdfGenerator->WriteHTML('<p class="ticket-title">Ticket #' . $ticket['id'] . ': <em>' . $ticket['subject'] . '</em> - from ' . $ticket['name'] . '</p>');
    }

    private function outputMessage($message)
    {
        if ($message['is_from_agent']) {
            $from = "one of our agents, ";
            $class = "from-agent";
        } else {
            $from = "";
            $class = "from-user";
        }

        if ($message['is_agent_note']) {
            $titleLead = "Private note";
            $class = "note";
        } else {
            $titleLead = "Message";
        }

        $html = '<div class="message ' . $class . '"><p>' . $titleLead . '#' . $message['id'] . ': From ';
        $html .= $from . ' <em>' . $message['from_name'] . '</em> on ' . $message['date_created'] . '</p>';

        $body = $this->swapImagesInMessageBodyForIMGTags($message['body'], $message['attachments']);

        $html .= '<div class="message-body">' . $body . '</div>';

        $html .= '</div>';

        $this->pdfGenerator->WriteHTML($html);
    }

    private function swapImagesInMessageBodyForIMGTags($body, array $attachments)
    {
        foreach ($attachments as $attachment) {
            $body = str_replace('[attach:image:' . $attachment['authcode'] . ':' . $attachment['filename'] . ']', '<img src="' . $attachment['absolute_url'] .'">', $body);
        }

        return $body;
    }
}
