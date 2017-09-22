<?php

namespace Mindlahus\SymfonyAssets\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\CommandTraitInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\ControlFieldInterface;
use Mindlahus\SymfonyAssets\Traits\CommandTrait;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconcileControlFieldCommand extends ContainerAwareCommand implements CommandTraitInterface
{
    use CommandTrait;

    protected $repositories = [];

    protected function configure(): void
    {
        $this
            ->setName('mindlahus:v3:reconcile:control-field')
            ->setDescription('Reconciles the control fields from the database.')
            ->addArgument(
                'repository',
                InputArgument::OPTIONAL,
                'The entity of witch control field you want to reconcile.'
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
     * @param ControlFieldInterface $entity
     */
    public function prepEntity(OutputInterface $output, $entity): void
    {
        $entity->setControlField();
    }

    /**
     * @param OutputInterface $output
     * @param $entity
     */
    public function prePersist(OutputInterface $output, $entity): void
    {
    }
}
