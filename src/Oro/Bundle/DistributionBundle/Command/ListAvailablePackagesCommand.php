<?php

namespace Oro\Bundle\DistributionBundle\Command;

use Composer\Package\PackageInterface;
use Oro\Bundle\DistributionBundle\Console\Grid;
use Oro\Bundle\DistributionBundle\Manager\PackageManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List of available packages
 */
class ListAvailablePackagesCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'oro:package:available';

    /** @var PackageManager */
    private $packageManager;

    /**
     * @param PackageManager $packageManager
     */
    public function __construct(PackageManager $packageManager)
    {
        $this->packageManager = $packageManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('List of available packages');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PackageInterface[] $availablePackages */
        $availablePackages = $this->packageManager->getAvailable();

        $grid = new Grid(2, [':']);
        foreach ($availablePackages as $package) {
            $grid->addRow([$package->getName(), $package->getPrettyVersion()]);
        }

        $output->writeln($grid->render());
    }
}
