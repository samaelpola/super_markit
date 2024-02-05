<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class MailerService
{
    public function __construct(
        private EmailVerifier   $emailVerifier,
        private MailerInterface $mailer
    )
    {
    }

    public function sendVerifyEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            $email
        );
    }

    public function sendResetPasswordEmail(User $user, ResetPasswordToken $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($email);
    }
}
