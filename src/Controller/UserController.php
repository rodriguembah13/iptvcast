<?php

namespace App\Controller;

use App\Entity\PrivilegeUser;
use App\Entity\User;
use App\Form\PrivilegeType;
use App\Form\UserType;
use App\Repository\PronosticUserRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @Route("/user")
 *
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    private $logger;
    private $dataTableFactory;
    private $userRepository;
    private $passwordEncoder;
    /**
     * @param $logger
     * @param $dataTableFactory
     * @param $userRepository
     */
    public function __construct(LoggerInterface $logger,UserPasswordHasherInterface $passwordEncoder, DataTableFactory $dataTableFactory, UserRepository $userRepository)
    {
        $this->logger = $logger;
        $this->passwordEncoder = $passwordEncoder;
        $this->dataTableFactory = $dataTableFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="user_index")
     */
    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('name', TextColumn::class)
            ->add('phone', TextColumn::class, [
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('email', TextColumn::class)
            ->add('username', TextColumn::class)
            ->add('isactivate', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
               // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                $url=$this->generateUrl('user_activate',['id'=>$context->getId()]);
                    if($value== true){
                        return '<a class="btn btn-sm btn-success" href='.$url.'>Activée</a>';
                    }else{
                        return '<a class="btn btn-sm btn-danger" href='.$url.'>Desactiveé</a>';
                    }
                }])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'user/buttonbar.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('user')
                        ->from(User::class, 'user')
                        //->join(Sgs::class, 'sgs')
                        //->join(Quittance::class, 'quittance')
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('user/index.html.twig', [
            'datatable' => $table
        ]);
    }
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $plainPassword= $form['password']->getData();
            $encodedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword= $form['password']->getData();
            $encodedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/privilege", name="user_privilege", methods={"GET","POST"})
     */
    public function privilege(Request $request, User $user): Response
    {
        $form = $this->createForm(PrivilegeType::class, $user->getPrivilege());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/privilege.html.twig', [
            'user' => $user,
            'privilege'=>$user->getPrivilege(),
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/pronosticuser", name="user_pronosticuser", methods={"GET","POST"})
     */
    public function pronosticuser(Request $request, User $user): Response
    {
        $pronostiques=$this->pronostiqueRepository->findBy(['user'=>$user]);
        return $this->render('user/pronosticuser.html.twig', [
            'user' => $user,
             'pronostics'=>$pronostiques,
        ]);
    }
    /**
     * @Route("activate/{id}", name="user_activate", methods={"GET","POST"})
     */
    public function activate(Request $request, User $user): Response
    {
        if ($user->getIsactivate()){
            $user->setIsactivate(false);
        }else{
            $user->setIsactivate(true);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('user_index');
    }
    /**
     * @Route("/delete/ajax", name="user_delete_ajax", methods={"GET"})
     *
     */
    public function deleteAjax(Request $request): JsonResponse
    {
        $em = $this->userRepository->find($request->get('item_id'));

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($em);
            $entityManager->flush();
            $this->addFlash('success', 'operation effectue avec success');
        } catch (\Exception $exception) {
            $this->addFlash('danger', 'operation impossible' . $exception->getMessage());
            return new JsonResponse( $exception->getMessage(), 403);
        }

        return new JsonResponse('success', 200);
    }
}
