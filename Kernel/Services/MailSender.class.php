<?php

namespace Kernel\Services;
 
use Kernel\Exceptions as Exceptions;

/**
 * @brief This class allows to send mails.
 *
 * @details
 * A mail is composed of a a recipient, a subject, a content and a sender.
 */
class MailSender
{
    /**
     * @brief The mail boundary.
     * @var String.
     */
    private $boundary;
    /**
     * @brief The mail content.
     * @var String.
     */
    private $content;
    /**
     * @brief The mail of the sender.
     * @var String.
     */
    private $senderMail;
    /**
     * @brief The sender name.
     * @var String.
     */
    private $senderName;
    /**
     * @brief The mail subject.
     * @var String.
     */
    private $subject;
    /**
     * @brief The mail of the recipient.
     * @var String.
     */
    private $to;
    
    /**
     * @brief Constructor.
     * @param String $to The mail of the recipient.
     * @param String $subject The mail subject.
     * @param String $content The mail content.
     * @param String $senderName The sender name.
     * @param String $senderMail The mail of the sender.
     */
    public function __construct($to = '', $subject = '', $content = '', $senderName = '', $senderMail = '')
    {
        // Set main attributes.
        $this->to = $to;
        $this->subject = $subject;
        $this->content = $content;
        $this->senderName = $senderName;
        $this->senderMail = $senderMail;

        // Create the mail boundary.
        $this->boundary = md5(rand());
    }

    /**
     * @brief Create mail headers.
     * @return String Mail headers.
     */
    public function headers()
    {
        $return = $this->goNextLine();

        $boundary = '-----='.$this->boundary;

        $headers = 'From: \''.$this->senderName.'\'<'.$this->senderMail.'>'.$return;
        $headers.= 'Content-Type: text/html; charset=\'utf-8\''.$return;
        $headers.= 'MIME-Version: 1.0'.$return;
        $headers.= 'Content-Type: multipart/alternative;'.$return.' boundary=\''.$boundary.'\''.$return;

        return $headers.$return.'--'.$this->boundary.$return;
    }
    
    /**
     * @brief Send the mail.
     *
     * @exception Kernel::Exceptions::MailSendingException When mail wasn't successfully accepted for delivery.
     * 
     * @see http://fr2.php.net/manual/en/function.mail.php
     */
    public function send()
    {
        $return = $this->goNextLine();

        $message = $return.$this->content.$return;
        $message.= $return.'--'.$this->boundary.'--'.$return;
        $message.= $return.'--'.$this->boundary.'--'.$return;

        if(mail($this->to, $this->subject, $message, $this->headers()) === false)
        {
            throw new Exceptions\MailSenderException('Mail "'.$this->subject.'" to '.$this->to.' wasn\'t successfully accepted for delivery.');
        }
    }
    
    /**
     * @brief Set the mail content.
     * @param String $content The mail content.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @brief Set the mail of the sender.
     * @param String $mail The mail of the sender.
     */
    public function setSenderMail($mail)
    {
        $this->senderMail = $mail;
    }
    
    /**
     * @brief Set the sender name.
     * @param String $name The sender name.
     */
    public function setSenderName($name)
    {
        $this->senderName = $name;
    }
    
    /**
     * @brief Set the mail subject.
     * @param String $subject The mail subject.
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * @brief Set the mail of the recipient.
     * @param String $to The mail of the recipient.
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @brief Compute the end of line character.
     * @return String The end of line character.
     */
    private function goNextLine()
    {
        return (preg_match('#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#', $this->to)) ? '\n' : '\r\n';
    }
}

?>