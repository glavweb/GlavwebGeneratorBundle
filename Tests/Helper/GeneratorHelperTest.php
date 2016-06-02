<?php

/*
 * This file is part of the Glavweb DatagridBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\GeneratorBundle\Tests\Helper;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Glavweb\GeneratorBundle\Helper\GeneratorHelper;

/**
 * Class GeneratorHelperTest
 *
 * @package Glavweb\GeneratorBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class GeneratorHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GeneratorHelper
     */
    private $generatorHelper;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->generatorHelper = new GeneratorHelper();
    }

   /**
     * testAddSuffix
     */
    public function testAddBasenameSuffix()
    {
        $basenames      = ['Level', 'LevelType', 'LevelFormType'];
        $suffix         = 'FormType';
        $expectedResult = 'LevelFormType';

        foreach ($basenames as $basename) {
            $actualResult = $this->generatorHelper->addBasenameSuffix($basename, $suffix);
            $this->assertEquals($expectedResult, $actualResult);
        }
    }
}