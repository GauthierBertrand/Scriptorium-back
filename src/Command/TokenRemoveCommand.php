<?php

namespace App\Command;

use App\Repository\TokenRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TokenRemoveCommand extends Command
{
    protected static $defaultName = 'app:token:remove';
    protected static $defaultDescription = 'Remove old tokens from Token table in BDD';

    private $repository;
    

    public function __construct(TokenRepository $repository)
    {
        $this->repository=$repository;
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Trying to delete old tokens......');
        
        //Call the remove token method from the tokenRepository
        $message=$this->repository->removeOldTokens();

        $io->success($message);

        return Command::SUCCESS;
    }
}
