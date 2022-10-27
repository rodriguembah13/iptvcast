<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'app:install';
    protected static $defaultDescription = 'Add a short description for your command';
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(UserPasswordHasherInterface $encoder, ManagerRegistry $registry, ValidatorInterface $validator)
    {
        $this->encoder = $encoder;
        $this->doctrine = $registry;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $this->createUser();
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function createUser()
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setName('administrateur principal');
        $user->setEmail('admin@localhost.com');
        $user->setPhone("675066919");
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $hashedPassword = $this->encoder->hashPassword($user, "admin123456789");
        $user->setPassword($hashedPassword);
        $this->doctrine->getManager()->persist($user);
        $this->doctrine->getManager()->flush();

    }
}
