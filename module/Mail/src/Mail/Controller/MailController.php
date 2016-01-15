<?php


namespace Mail\Controller;

use Zend\Http\Header\TransferEncoding;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mail\Storage\Imap as Imap;
use Zend\Mail\Storage\Message;

class MailController extends AbstractActionController
{
    public function indexAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $serverAnswer = new Imap ([
                'host' => 'imap.poczta.onet.pl',
                'user' => 'marek.mikusek@onet.eu',
                'password' => '123QWEasd',
            ]);
            $mails = [];
            $i = 1;
            foreach ($serverAnswer as $inputMail) {
                $mail = $serverAnswer->getMessage($i);
                $mails[$i]['date'] = $mail->date;
                $mails[$i]['from'] = $mail->from;
                $mails[$i]['subject'] = $mail->subject;
                foreach ($mail as $part)
                    $content = quoted_printable_decode($part->getContent());
                $mails[$i]['content'] = $content;
                $i++;
            }
            return ['mails' => json_encode($mails)];
        }
    }

}
