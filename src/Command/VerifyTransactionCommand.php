<?php

namespace App\Command;

use App\Entity\Activation;
use App\Entity\CardPending;
use App\Repository\ActivationRepository;
use App\Repository\CardPendingRepository;
use App\Service\paiement\OmService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:verify-transaction',
    description: 'Add a short description for your command',
)]
class VerifyTransactionCommand extends Command
{
    private $activationRepository;
    private $cardpendingRepository;
    private $omService;
    private $doctrine;
    /**
     * VerifyTransactionCommand constructor.
     * @param ActivationRepository $activationRepository
     * @param ManagerRegistry $registry
     * @param CardPendingRepository $cardpendingRepository
     * @param OmService $omService
     */
    public function __construct(ActivationRepository $activationRepository,ManagerRegistry $registry, CardPendingRepository $cardpendingRepository, OmService $omService)
    {
        $this->activationRepository = $activationRepository;
        $this->cardpendingRepository = $cardpendingRepository;
        $this->omService = $omService;
        $this->doctrine = $registry;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $activations=$this->activationRepository->findBy(['status'=>Activation::PENDING]);
        foreach ($activations as $activation){
            if (substr($activation->getReference(),0,2)=="MP"){
                $values=$this->omService->getStatusPayment($activation->getReference());
                $cardpending = $this->cardpendingRepository->findOneBy(['activation' => $activation->getId()]);
                if ($values['data']['status']=="SUCCESSFUL"){
                    $activation->setStatus(Activation::SUCCESS);
                    $cardpending->setStatus(CardPending::SUCCESS);
                }else{
                    $activation->setStatus(Activation::ECHEC);
                    $cardpending->setStatus(CardPending::ECHEC);
                }
            }
            $this->doctrine->getManager()->flush();
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
