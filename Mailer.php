<?php

namespace levmorozov\phpmailer;

use mii\core\Component;
use mii\web\Block;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends Component
{
    /**
     * @var PHPMailer
     */
    public $mailer;

    protected $config;

    protected $to = [];
    protected $from;
    protected $reply_to;
    protected $subject;
    protected $attachments = [];

    protected $is_html = true;

    protected $assets_path = '';


    public function init(array $config = []): void
    {

        parent::init($config);

        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet = 'UTF-8';

        $this->mailer->isSMTP();

        foreach ($this->config as $key => $value) {
            $this->mailer->$key = $value;
        }
    }


    public function attachment(string $attachment) {
        $this->attachments[] = $attachment;

        return $this;
    }

    public function to(string $to, $name = '', $clear = false) {
        if($clear)
            $this->to = [];

        $this->to[] = [$to, $name];

        return $this;
    }

    public function from(string $from, $name = '') {
        $this->from = [$from, $name];

        return $this;
    }

    public function reply_to(string $to, $name = '') {

        $this->reply_to = [$to, $name];

        return $this;
    }

    public function subject(string $subject) {
        $this->subject = $subject;

        return $this;
    }

    public function html_mode($is_html = true) {
        $this->is_html = $is_html;
        return $this;
    }

    public function body($body) {
        if ($body instanceof Block) {
            $this->body = $body->render(true);
            $this->assets_path = \Mii::$app->blocks->assets_path_by_name($body->name());
            $this->is_html = true;
        } else {
            $this->body = $body;
        }
        return $this;
    }


    public function reset() {
        $this->from = '';
        $this->to = [];
        $this->reply_to = '';
        $this->subject = '';
        $this->body = '';
        $this->attachments = [];
        $this->assets_path = '';

        return $this;
    }


    public function send()
    {
        foreach ($this->to as $address) {
            $this->mailer->addAddress($address[0], $address[1]);
        }

        $this->mailer->Subject = $this->subject;

        if ($this->is_html) {
            $this->mailer->msgHTML($this->body, $this->assets_path);
        } else {
            $this->mailer->Body = $this->body;
        }

        $result = $this->mailer->send();

        $this->mailer->clearAllRecipients();

        return $result;
    }

}