<?php

namespace Mindlahus\SymfonyAssets\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\CommandTraitInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\NameInterface;
use Mindlahus\SymfonyAssets\Traits\CommandTrait;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconcileNameCommand extends ContainerAwareCommand implements CommandTraitInterface
{
    use CommandTrait;

    protected $repositories = [];

    protected function configure(): void
    {
        $this
            ->setName('mindlahus:v3:reconcile:name')
            ->setDescription('Reconciles First Last & Last First names from the database.')
            ->addArgument(
                'repository',
                InputArgument::OPTIONAL,
                'The entity of witch First & Last name combination you want to reconcile.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        /**
         * @var Logger $logger
         */
        $logger = $container->get('monolog.logger.onew');
        $this->init(
            $output,
            $container,
            $entityManager,
            $logger
        );

        if ($input->getArgument('repository')) {
            $this->repositories = [
                $input->getArgument('repository')
            ];
        }
        foreach ($this->repositories as $repository) {
            $this->executeReconciliation($output, $repository);
        }
    }

    /**
     * @param OutputInterface $output
     * @param $entity
     * @return bool
     */
    public function preEntityPrepTestFailed(OutputInterface $output, $entity): bool
    {
        return false;
    }

    /**
     * @param OutputInterface $output
     * @param NameInterface $entity
     */
    public function prepEntity(OutputInterface $output, $entity): void
    {
        $entity->setFirstLastName();
        $entity->setLastFirstName();
    }

    /**
     * @param OutputInterface $output
     * @param $entity
     */
    public function prePersist(OutputInterface $output, $entity): void
    {
    }
}
