<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Validator\ConstraintViolation;

trait CommandTrait
{
    /**
     * @var ContainerInterface $container
     */
    private $container;
    /**
     * @var EntityManagerInterface $em
     */
    private $em;
    /**
     * @var Logger $logger
     */
    private $logger;
    /**
     * @var OutputInterface $output
     */
    private $output;

    /**
     * @param OutputInterface $output
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @param Logger $logger
     */
    private function init(
        OutputInterface $output,
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        Logger $logger
    ): void
    {
        $this->container = $container;
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->output = $output;
    }

    /**
     * @param string $msg
     * @param string $context
     */
    private function log(string $msg, string $context = 'onew'): void
    {
        $this->logger->error($msg, [$context]);
    }

    /**
     * @param ProgressBar $progressBar
     * @param int $step
     * @return ProgressBar
     */
    private function advanceProgressBar(ProgressBar $progressBar, $step = 1): ProgressBar
    {
        $progressBar->advance($step);

        return $progressBar;
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @return OutputInterface
     */
    private function writeError(OutputInterface $output, $msg): OutputInterface
    {
        return $this->write($output, $msg, 'red');
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @return OutputInterface
     */
    private function writeInfo(OutputInterface $output, $msg): OutputInterface
    {
        return $this->write($output, $msg, 'green');
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @param string $fg
     * @return OutputInterface
     */
    private function write(OutputInterface $output, $msg, $fg = 'black'): OutputInterface
    {
        if (is_array($msg) !== true) {
            $msg = [$msg];
        }

        foreach ($msg as $line) {
            $output->writeln('<fg=' . $fg . ';options=bold>' . $line . '</>');
        }

        return $output;
    }

    /**
     * @param OutputInterface $output
     * @param StopwatchEvent $event
     */
    private function writeDebugInfo(OutputInterface $output, StopwatchEvent $event): void
    {
        $memory = number_format($event->getMemory() / 1000000, 2);
        $time = $event->getDuration() / 1000;
        $this->write($output, "({$time}s / {$memory}M)");
    }

    /**
     * @param array $commands
     * @throws \Throwable
     */
    private function exec_console_commands(array $commands): void
    {
        $bin_console_file_path = $this->container->get('kernel')->getRootDir()
            . '/../bin/console';
        $stdout = file_exists('/dev/null') ? '/dev/null 2>&1' : 'NUL';
        foreach ($commands as $command) {
            exec("php ${bin_console_file_path} ${command} >${stdout}");
        }
    }

    /**
     * @param $entity
     * @return bool
     * @throws \Throwable
     */
    private function validate($entity): bool
    {
        $validator = $this->container->get('validator');

        /**
         * @var array $errors
         */
        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            /**
             * @var ConstraintViolation $error
             */
            foreach ($errors as $error) {
                $this->log(
                    '[' . $error->getPropertyPath() . '] ' . $error->getMessage()
                );
            }
            return false;
        }

        return true;
    }

    private function flushIteratedResult(): void
    {
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();
    }

    private function flushResult(): void
    {
        $this->em->flush();
        gc_collect_cycles();
    }

    /**
     * @param OutputInterface $output
     * @param string $repository
     * @param bool $iterate
     * @param null | array | ArrayCollection | PersistentCollection | IterableResult $entities
     * @param int|null $total
     * @param bool $persist
     */
    private function executeReconciliation(
        OutputInterface $output,
        string $repository,
        bool $iterate = true,
        $entities = null,
        int $total = null,
        bool $persist = false
    ): void
    {
        gc_enable();

        try {
            if (!$entities) {
                $entities = $this->em
                    ->createQuery("SELECT t FROM {$repository} t")
                    ->iterate();
            }
            if (!$total) {
                $total = $this->em
                    ->createQuery("SELECT COUNT(t) FROM {$repository} t")
                    ->getSingleScalarResult();
            }

            if ($entities) {
                $this->write($output, "Working on {$repository}");
                $progressBar = new ProgressBar($output, $total);
                $progressBar->start();
                $count = 0;
                $stopwatch = new Stopwatch();
                $stopwatch->start($repository);
                foreach ($entities as $entity) {
                    if ($iterate === true) {
                        $entity = $entity[0];
                    }
                    if ($this->{'preEntityPrepTestFailed'}($output, $entity)) {
                        continue;
                    }
                    $this->{'prepEntity'}($output, $entity);
                    if ($persist === true) {
                        $this->{'prePersist'}($output, $entity);
                        $this->em->persist($entity);
                    }
                    if (!$this->validate($entity)) {
                        continue;
                    }
                    if ($count % 15 === 0) {
                        $this->advanceProgressBar($progressBar, 15);
                        $this->flushIteratedResult();
                    }
                    $count++;
                }
                $this->flushIteratedResult();
                $progressBar->finish();
                $event = $stopwatch->stop($repository);
                $this->writeInfo($output, "\n\r{$repository} successfully reconciled. Thank you!");
                $this->writeDebugInfo($output, $event);
            } else {
                $this->write($output, 'Nothing to reconcile.');
            }
        } catch (\Throwable $e) {
            $this->writeError($output, "\n\r" . $e->getMessage());
        }
    }
}