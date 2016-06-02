<?php

/*
 * This file is part of the Glavweb DatagridBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\GeneratorBundle\Util;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ModelClassParser
 *
 * @package Glavweb\GeneratorBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ModelClassParser
{
    /**
     * Entity dir
     */
    const ENTITY_DIR = 'Entity';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * ModelClassParser constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $class
     * @return string
     * @throws \InvalidArgumentException
     */
    public function validateClass($class)
    {
        $class = str_replace('/', '\\', $class);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        return $class;
    }

    /**
     * @param $modelClass
     * @return array
     */
    public function parseModelClass($modelClass)
    {
        $bundle = $this->getBundle($modelClass);

        $modelBasename          = $this->getModelBasename($modelClass);
        $modelBundleName        = $this->getModelBundleName($modelClass);
        $createFormTypeClass    = $this->getFormTypeClass($modelClass, 'Create');
        $createFormTypeBasename = $this->getModelBasename($createFormTypeClass);
        $editFormTypeClass      = $this->getFormTypeClass($modelClass, 'Edit');
        $editFormTypeBasename   = $this->getModelBasename($editFormTypeClass);

        return array(
            $modelBasename,
            $modelBundleName,
            $createFormTypeClass,
            $createFormTypeBasename,
            $editFormTypeClass,
            $editFormTypeBasename,
            $bundle
        );
    }

    /**
     * @param string $modelClass
     * @return BundleInterface
     */
    public function getBundle($modelClass)
    {
        $name = $this->getBundleName($modelClass);

        return $this->kernel->getBundle($name);
    }

    /**
     * @param string $modelClass
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getBundleName($modelClass)
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if (strpos($modelClass, $bundle->getNamespace() . '\\') === 0) {
                return $bundle->getName();
            }
        }

        return null;
    }

    /**
     * @param $modelClass
     * @return mixed
     */
    public function getModelBasename($modelClass)
    {
        $modelBasename = current(array_slice(explode('\\', $modelClass), -1));

        return $modelBasename;
    }

    /**
     * @param $modelClass
     * @return string
     */
    public function getModelBundleName($modelClass)
    {
        $bundle           = $this->getBundle($modelClass);
        $modelSubDirsPath = implode('\\', $this->getModelSubDirs($modelClass));
        $modelBasename    = $this->getModelBasename($modelClass);

        $modelBundleName =
            $bundle->getName() . ':' .
            ($modelSubDirsPath ? $modelSubDirsPath . '\\' : '') .
            $modelBasename
        ;

        return $modelBundleName;
    }

    /**
     * @param $modelClass
     * @return array
     */
    public function getModelSubDirs($modelClass)
    {
        $bundle        = $this->getBundle($modelClass);
        $modelBasename = $this->getModelBasename($modelClass);

        $entityPath = $bundle->getNamespace() . '\\' . self::ENTITY_DIR . '\\';
        if (strpos($modelClass, $entityPath) === 0) {
            $modelFullName = substr($modelClass, strlen($entityPath));

            if ($modelFullName == $modelBasename) {
                return [];
            }

            if (strrpos($modelFullName, $modelBasename) > 0) {
                $subDirs = substr($modelFullName, 0, -strlen($modelBasename)-1);

                return explode('\\', $subDirs);
            }
        }

        throw new \RuntimeException('The model class should contain "' . $entityPath . '".');
    }

    /**
     * @param $modelClass
     * @param string $prefix
     * @return string
     */
    public function getFormTypeClass($modelClass, $prefix = '')
    {
        $modelBasename    = $this->getModelBasename($modelClass);
        $formTypeBasename = $modelBasename . 'Type';

        if ($prefix) {
            $prefixFormTypeBasename = ucfirst($prefix) . $formTypeBasename;

            $prefixFormTypeClass = $this->getFormTypeClassByBasename($modelClass, $prefixFormTypeBasename);
            $prefixFormTypePath =
                realpath($this->kernel->getRootDir() . '/../src') . DIRECTORY_SEPARATOR .
                $prefixFormTypeClass. '.php'
            ;

            if (is_file($prefixFormTypePath )) {

                return $prefixFormTypeClass;
            }
        }

        $formTypeClass = $this->getFormTypeClassByBasename($modelClass, $formTypeBasename);

        return $formTypeClass;
    }

    /**
     * @param $modelClass
     * @param $formTypeBasename
     * @return string
     */
    private function getFormTypeClassByBasename($modelClass, $formTypeBasename)
    {
        $bundle           = $this->getBundle($modelClass);
        $modelSubDirsPath = implode('\\', $this->getModelSubDirs($modelClass));

        $formTypeClass =
            $bundle->getNamespace() .
            '\\Form\\' .
            ($modelSubDirsPath ? $modelSubDirsPath . '\\' : '') .
            $formTypeBasename
        ;

        return $formTypeClass;
    }
}