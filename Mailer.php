<?php

namespace mii\mailer;

use mii\core\Component;
use mii\web\Block;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends Component
{
    public PHPMailer $mailer;

    protected array $to = [];
    protected string $subject;
    protected string $body;
    protected array $attachments = [];

    protected bool $isHtml = true;

    protected string $assets_path = '';

    protected string $From = '';
    protected string $FromName = '';
    protected string $Host = '';
    protected string $Username = '';
    protected string $Password = '';

    protected array $config = [];

    public function init(array $config = []): void
    {
        parent::init($config);

        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet = 'UTF-8';

        $this->mailer->isSMTP();

        $this->config = array_replace([
            'From' => $this->From,
            'FromName' => $this->FromName,
            'Host' => $this->Host,
            'Username' => $this->Username,
            'Password' => $this->Password,
            'SMTPAuth' => true,
            'SMTPSecure' => PHPMailer::ENCRYPTION_STARTTLS,
            'Port' => 587
        ], $this->config);

        foreach ($this->config as $key => $value) {
            $this->mailer->$key = $value;
        }
    }


    public function attachment(string $attachment) {
        $this->attachments[] = $attachment;

        return $this;
    }

    public function to(string $to, string $name = '', bool $clear = false) {
        if($clear)
            $this->to = [];

        $this->to[] = [$to, $name];

        return $this;
    }

    public function subject(string $subject) {
        $this->subject = $subject;

        return $this;
    }

    public function htmlMode(bool $is_html = true) {
        $this->isHtml = $is_html;
        return $this;
    }

    public function body($body) {
        if ($body instanceof Block) {
            $this->body = $body->render(true);
            $this->assets_path = \Mii::$app->blocks->assets_path_by_name($body->name());
            $this->isHtml = true;
        } else {
            $this->body = $body;
        }
        return $this;
    }


    public function reset() {
        $this->to = [];
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

        if ($this->isHtml) {
            $this->mailer->msgHTML($this->body, $this->assets_path);
        } else {
            $this->mailer->Body = $this->body;
        }

        $result = $this->mailer->send();

        $this->mailer->clearAllRecipients();

        return $result;
    }

}
