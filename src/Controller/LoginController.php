<?php

namespace App\Controller;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
/**
 * @Route("/auth")
 *
 */
class LoginController extends AbstractController
{
    private $passwordEncoder;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param UserPasswordHasherInterface $passwordEncoder
     */
    public function __construct(LoggerInterface $logger,UserPasswordHasherInterface $passwordEncoder)
    {
        $this->logger = $logger;
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request): Response
    {


        if ($request->getMethod()=="POST"){
            $entityManager = $this->getDoctrine()->getManager();
            $user = new User();
            $user->setName($request->get('firstname').' '.$request->get('lastname'));
            $user->setEmail($request->get('emailaddress'));
            $plainPassword= $request->get('password');
            $user->setUsername($request->get('firstname'));
            $user->setPhone($request->get('phone'));
            $encodedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $user->setIsactivate(false);
            $user->setRoles(["ROLE_USER"]);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('confirmuser');
        }
        return $this->render('login/register.html.twig', [
        ]);
    }
    /**
     * @Route("/confirmuser", name="confirmuser")
     */
    public function confirmuser(Request $request): Response
    {
        $user=$this->getUser();
        $activate=$user->getIsactivate();
        if ($activate==true){
            $this->logger->info("----------------------------------");
          return  $this->redirectToRoute('home');
        }
        return $this->render('login/confirmuser.html.twig', [
            'user'=>$activate
        ]);
    }
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error=$authenticationUtils->getLastAuthenticationError();
        $lastusername=$authenticationUtils->getLastUsername();
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'last_username'=>$lastusername,
            'error'=>$error
        ]);
    }
    /**
     * @Route("/connect/google", name="connect_google")
     */
    public function connectGoogleAction(ClientRegistry $clientRegistry)
    {
        //Redirect to google
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/google/check", name="connect_google_check")

     */
    public function connectGoogleCheckAction(Request $request)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
    }
    /**
     * @Route("/connect/facebook", name="connect_facebook_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('facebook_main')->redirect(['public_profile', 'email']);
    }
    /**
     * Facebook redirects to back here afterwards
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient $client */
        $client = $clientRegistry->getClient('facebook_main');
        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
            $user = $client->fetchUser();
          //  dump($user);
            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
          //  var_dump($user); die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
         //   var_dump($e->getMessage()); die;
        }
    }
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(AuthenticationUtils $authenticationUtils): void
    {
    throw new \Exception("Don\'t forget to active logout");

    }
}
