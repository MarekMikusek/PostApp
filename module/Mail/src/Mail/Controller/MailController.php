<?php

namespace Mail\Controller;

use Zend\Json\Server\Response;
use Zend\Mail\Storage\Imap as Imap;
use Zend\Mail\Storage\Message as Message;
use Zend\Mail\Storage\Part;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mime;

class MailController extends AbstractRestfulController
{

    public function ConnectToMailBox()
    {
        return $serverAnswer = new Imap (
            $this->getServiceLocator()->get('config')['imap']);
    }

    public function readMailHeader(Message $mail, $id)
    {
        $return = [];
        $return['id'] = $id;
        $return['date'] = $mail->date;
        $return['from'] = $mail->from;
        $return['subject'] = $mail->subject;
        $return['content'] = $this->getContent($mail);
        $return['flags'] = $mail->getFlags();
        $return['attachments'] = $this->getAttachmentsList($mail);
        return $return;
    }

    public function readMail(Message $mail, $id)
    {
        $return = $this->readMailHeader($mail, $id);
        return $return;
    }

    public function decode($part)
    {
        if ($part->getHeader('contenttransferencoding')->getFieldValue() == 'base64') {
            $content = base64_decode($part->getContent());
        } elseif ($part->getHeader('contenttransferencoding')->getFieldValue() == 'quoted-encoding') {
            $content = quoted_printable_decode($part->getContent());
        } else {
            $content = $part->getContent();
        }
        return $content;
    }

    private function showMails($folder)
    {
        $connectionToMailServer = $this->ConnectToMailBox();
        $connectionToMailServer->selectFolder($folder);
        $mails = [];

        for ($i = 1; $i <= $connectionToMailServer->countMessages(); $i++)
            $mails[] = $this->readMailHeader($connectionToMailServer->getMessage($i), $i);

        return $mails;
    }

    public function create($data)
    {
        $text = new Mime\Part();
        if ($data['isHtml']) {
            $text->type = Mime\Mime::TYPE_HTML;
        } else {
            $text->type = Mime\Mime::TYPE_TEXT;
        }
        $text->charset = 'utf-8';
        $text->setContent($data['body']);
        $attachments = $this->getRequest()->getFiles()->toArray()['attachment'];
        $mimeMessage = new Mime\Message();

        $mimeMessage->setParts([$text]);

        foreach ($attachments as $attachment) {
            $fileContent = fopen($attachment['tmp_name'], 'r');
            $attachmentToMail = new Mime\Part($fileContent);
            $attachmentToMail->type = $attachment['type'];
            $attachmentToMail->filename = $attachment['name'];
            $attachmentToMail->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachmentToMail->encoding = Mime\Mime::ENCODING_BASE64;
            $mimeMessage->addPart($attachmentToMail);
        }

        $message = new \Zend\Mail\Message();
        $message->setBody($mimeMessage);
        $message->setSubject($data['subject'])
            ->setTo($data['receiverEmail'])
            ->setFrom("marek.mikusek@onet.eu");
        if (strlen($data['cc']) > 0) {
            $message->setCc($data['cc']);
        }
        if (strlen($data['bcc']) > 0) {
            $message->setBcc($data['bcc'], '');
        }
        $transport = new SmtpTransport();
        $options = new SmtpOptions($this->getServiceLocator()->get('config')['smtp']);
        $transport->setOptions($options);
        $transport->send($message);

        return new JsonModel([]);
    }

    public function getList()
    {
        return new JsonModel($this->showMails("INBOX"));
    }

    public function get()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($this->params('attachmentId')) {
            return $this->openAttachment($id, $this->params('folder'), $this->params('attachmentId'));
        } else {
            $connectionToMailBox = $this->ConnectToMailBox();
            return new JsonModel($this->readMail($connectionToMailBox->getMessage($id), 1));
        }
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

    public function openAttachment($id, $folder, $attachmentId)
    {
        $connectionToMailBox = $this->ConnectToMailBox();
        $connectionToMailBox->selectFolder($folder);
        $mail = $connectionToMailBox->getMessage($id);
        $parts = $this->getParts($mail);
        $i = 1;
        foreach ($parts as $part) {
            if ($part->getHeaders()->has('Content-Disposition')) {
                if ($part->getHeaderField('Content-Disposition') == 'attachment') {
                    if ($i == $attachmentId) {
                        $partToOpen = $part;
                        break;
                    }
                    $i++;
                }
            }
        }
        if (!$partToOpen) {
            return false;
        } else {
            $content = $this->decode($partToOpen);
//            if ($partToOpen->getHeader('contenttransferencoding')->getFieldValue() == 'base64') {
//                $content = base64_decode($partToOpen->getContent());
//            } elseif ($partToOpen->getHeader('contenttransferencoding')->getFieldValue() == 'quoted-encoding') {
//                $content = quoted_printable_decode($partToOpen->getContent());
//            } else {
//                $content = $partToOpen->getContent();
//            }
            $contentType = $partToOpen->getHeaderField('contenttype');
            $fileName = $partToOpen->getHeader('contenttype')->getParameters()['name'];
            $contentDisposition = $partToOpen->getHeaderField('Content-Disposition');
            //echo $content . "  " . $contentType . "  " . $fileName;
        };
        $response = $this->getResponse();
        $response->setContent($content);
        $response->getHeaders()
            ->addHeaderLine('Content-Type', $contentType)
            ->addHeaderLine('Content-Length', mb_strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;

    }

    /**
     * Function returns array of mail parts
     * @param Part $mail
     * @return array
     */
    public function getParts(Part $mail)
    {
        $parts = [];
        $munOfParts = $mail->countParts();
        if ($munOfParts == 0) {
            return [$mail];
        } else {
            foreach ($mail as $part) {
                $parts = array_merge($parts, $this->getParts($part));
            }
        }
        return $parts;
    }

    public function getContent(Message $mail)
    {
        $content = '';
        $mail = $this->getParts($mail);
        foreach ($mail as $part) {
            if (strpos($part->getHeader('content-type')->getFieldValue(), 'text/plain') !== false) {

                if ($part->getHeader('contenttransferencoding')->getFieldValue() == 'base64') {
                    $content = base64_decode($part->getContent());
                } elseif ($part->getHeader('contenttransferencoding')->getFieldValue() == 'quoted-encoding') {
                    $content = quoted_printable_decode($part->getContent());
                } else {
                    $content = $part->getContent();
                }
            } elseif (strpos($part->getHeader('content-type')->getFieldValue(), 'text/html') !== false) {

                if ($part->getHeader('contenttransferencoding')->getFieldValue() == 'base64') {
                    $content = base64_decode($part->getContent());
                } elseif ($part->getHeader('contenttransferencoding')->getFieldValue() == 'quoted-encoding') {
                    $content = quoted_printable_decode($part->getContent());
                } else {
                    $content = $part->getContent();
                }
                break;
            } elseif (strpos($part->getHeader('content-type')->getFieldValue(), 'multipart/alternative') !== false) {
                foreach ($part as $innerPart) {
                    $a = 'a';
                }
            }
        }
        return $content;
    }

    public function getAttachmentFilename($part)
    {
        if ($part->getHeaders()->has('Content-Disposition')) {
            if ($part->getHeaderField('Content-Disposition') == 'attachment') {
                $string = $part->getHeader('Content-Disposition')->getFieldValue();
                $begin = strpos($string, 'filename=') + 10;
                $end = strpos($string, '"', $begin + 1);
                $fileName = substr($string, $begin, $end - $begin);
            }
        }
        return $fileName;
    }

    public function getAttachmentContent($part)
    {
        $content = '';
        if ($part->getHeaderField('contenttransferencoding') == 'base64') {
            $content .= base64_decode($part->getContent());
        } elseif ($part->getHeaderField('contenttransferencoding') == 'quoted-encoding') {
            $content .= quoted_printable_decode($part->getContent());
        } else {
            $content .= $part->getContent() . "\n";
        }
        return $content;
    }

    public function getAttachmentsList(Message $mail)
    {
        $attachmentList = [];
        $i = 1;
        foreach ($mail as $part) {
            if ($part->getHeaders()->has('Content-Disposition')) {
                if ($part->getHeaderField('Content-Disposition') == 'attachment') {
                    $string = $part->getHeader('Content-Disposition')->getFieldValue();
                    $begin = strpos($string, 'filename=') + 10;
                    $end = strpos($string, '"', $begin + 1);
                    $fileName = substr($string, $begin, $end - $begin);

                    $attachmentList[] = ['id' => $i,
                        'filename' => $fileName];
                    $i++;
                }
            }
        }
        return $attachmentList;
    }

}
