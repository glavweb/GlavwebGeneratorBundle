<?php

/*
 * This file is part of the Glavweb DatagridBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\GeneratorBundle\Command;

use Glavweb\GeneratorBundle\Generator\FixtureGenerator;
use Glavweb\GeneratorBundle\Util\ModelClassParser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AbstractCommand
 *
 * @package Glavweb\GeneratorBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @return ModelClassParser
     */
    public function getModelClassParser()
    {
        return $this->getContainer()->get('glavweb_generator.util.model_class_parser');
    }

    /**
     * @return KernelInterface
     */
    protected function getKernel()
    {
        /* @var $application Application */
        $application = $this->getApplication();

        return $application->getKernel();
    }

    /**
     * @param OutputInterface $output
     * @param string $message
     */
    protected function writeError(OutputInterface $output, $message)
    {
        $output->writeln(sprintf("\n<error>%s</error>", $message));
    }
}
