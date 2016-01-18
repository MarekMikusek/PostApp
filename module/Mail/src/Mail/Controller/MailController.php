<?php


namespace Mail\Controller;

use Zend\Json\Server\Response;
use Zend\Mail\Storage\Imap as Imap;
use Zend\Mail\Storage\Message;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class MailController extends AbstractRestfulController
{

    public function ConnectToMailBox()
    {
        return $serverAnswer = new Imap ([
            'host' => 'imap.poczta.onet.pl',
            'user' => 'marek.mikusek@onet.eu',
            'password' => '123QWEasd',
        ]);
    }

    private function showMails($folder)
    {
        $connectionToMailServer = $this->ConnectToMailBox();
        $connectionToMailServer->selectFolder($folder);
        $mails = [];

        for ($i = 1; $i <= $connectionToMailServer->countMessages(); $i++) {
            $mail = $connectionToMailServer->getMessage($i);
            $mails[$i]['id'] = $i;
            $mails[$i]['date'] = $mail->date;
            $mails[$i]['from'] = $mail->from;
            $mails[$i]['subject'] = $mail->subject;
            foreach ($mail as $part)
                $content = quoted_printable_decode($part->getContent());
            $mails[$i]['content'] = $content;
        }

        return $mails;
    }

    public function getList()
    {
        return new JsonModel($this->showMails("INBOX"));
    }

    public function get()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        $connectionToMailBox = $this->ConnectToMailBox();
        $mail = $connectionToMailBox->getMessage($id);

        $mailToEdit['id'] = $id;
        $mailToEdit['date'] = $mail->date;
        $mailToEdit['from'] = $mail->from;
        $mailToEdit['subject'] = $mail->subject;
        foreach ($mail as $part)
            $content = quoted_printable_decode($part->getContent());
        $mailToEdit['content'] = $content;

        return new JsonModel($mailToEdit);
    }

    public function delete($id)
    {
        $this->ConnectToMailBox()->removeMessage($id);
        $this->getResponse()->setStatusCode(204);

        return;
    }

    public function dirAction()
    {
        $folders = $this->ConnectToMailBox()->getFolders();
        $return = [];
        foreach ($folders as $folder)
            $return[] = $folder->getGlobalName();
        return new JsonModel($return);
    }

    public function subdirAction()
    {
        return new JsonModel($this->showMails($this->params()->fromRoute('folder')));
    }

}
