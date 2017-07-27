<?php

namespace Mindlahus\SymfonyAssets\AbstractInterface;

use Symfony\Component\Console\Output\OutputInterface;

interface CommandTraitInterface
{
    /**
     * @param OutputInterface $output
     * @param $entity
     * @return bool
     */
    public function preEntityPrepTestFailed(OutputInterface $output, $entity): bool;

    /**
     * @param OutputInterface $output
     * @param $entity
     */
    public function prepEntity(OutputInterface $output, $entity): void;

    /**
     * @param OutputInterface $output
     * @param $entity
     */
    public function prePersist(OutputInterface $output, $entity): void;
}