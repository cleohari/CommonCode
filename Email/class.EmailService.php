<?php
namespace Email;

class EmailService
{
    public function __construct($params)
    {
    }

    public function canSend()
    {
        return false;
    }

    public function sendEmail($email)
    {
        return false;
    }
}
