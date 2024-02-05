<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier  $emailVerifier,
        private readonly UserRepository $userRepository,
        private readonly MailerService  $mailerService
    )
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $this->mailerService->sendVerifyEmail($user);

            $this->addFlash('success', 'The email to check your mail has been sent, please check your mailbox');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function sendVerifyUserEmail(Request $request): Response
    {
        if ($request->isMethod("POST")) {
            $email = $request->getSession()->get('email_not_verified');
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user === null) {
                throw $this->createNotFoundException('user not found for email');
            }

            $this->mailerService->sendVerifyEmail($user);

            if ($request->getSession()->has(SecurityRequestAttributes::AUTHENTICATION_ERROR)) {
                $request->getSession()->set(
                    SecurityRequestAttributes::AUTHENTICATION_ERROR,
                    null
                );
            }

            $this->addFlash('success', 'The email to check your mail has been sent, please check your mailbox');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/resend_verify_email.html.twig');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, #[MapQueryParameter] int $id): Response
    {
        $user = $this->userRepository->find($id);

        if ($user == null) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
