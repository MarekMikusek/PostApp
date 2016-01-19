<?php


namespace Mail\Controller;

use Zend\Json\Server\Response;
//use Zend\Mail\Message;
use Zend\Mail\Storage\Imap as Imap;
use Zend\Mail\Storage\Message as Message;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class MailController extends AbstractRestfulController
{

    public function ConnectToMailBox()
    {
        return $serverAnswer = new Imap (
            $this->getServiceLocator()->get('config'));
    }

    public function readMail(Message $mail, $id)
    {
        $return = [];

        // var_dump($mail);die();

        $return['id'] = $id;
        $return['date'] = $mail->date;
        $return['from'] = $mail->from;
        $return['subject'] = $mail->subject;
        $return['flags'] = $mail->getFlags();
        $content = '';

        foreach ($mail as $part) {
            if ($part->getHeaders()->has('Content-Transfer-Encoding')) {

                if ($part->getHeader('contenttransferencoding')->getFieldValue() == 'base64') {
                    $content .= base64_decode($part->getContent());
                } elseif ($part->getHeader('contenttransferencoding')->getFieldValue() == 'quoted-encoding') {
                    $content .= quoted_printable_decode($part->getContent());

                } else {
                    $content .= $part->getContent();
                }
            }
        }
        $return['content'] = $content;

        return $return;
    }

    private function showMails($folder)
    {
        $connectionToMailServer = $this->ConnectToMailBox();
        $connectionToMailServer->selectFolder($folder);
        $mails = [];

        for ($i = 1; $i <= $connectionToMailServer->countMessages(); $i++)
            $mails[] = $this->readMail($connectionToMailServer->getMessage($i), $i);

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

        return new JsonModel($this->readMail($connectionToMailBox->getMessage($id), 1));
    }
    
    public function delete()
    {
        $connectionToMailServer = $this->ConnectToMailBox();
        $connectionToMailServer->selectFolder($this->params()->fromRoute('folder'));
        $connectionToMailServer->removeMessage($this->params()->fromRoute('id'));
        $this->getResponse()->setStatusCode(204);

        return;
    }

    /**
     * Returns list of folders in mailbox
     * @return JsonModel
     */
    public function dirAction()
    {
        $folders = $this->ConnectToMailBox()->getFolders();
        $return = [];
        foreach ($folders as $folder)
            $return[] = $folder->getGlobalName();
        return new JsonModel($return);
    }

    /**
     * returns mails in subdirectory
     * @return JsonModel
     */
    public function subdirAction()
    {
        return new JsonModel($this->showMails($this->params()->fromRoute('folder')));
    }

}
