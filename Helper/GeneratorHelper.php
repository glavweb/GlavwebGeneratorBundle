<?php

/*
 * This file is part of the Glavweb DatagridBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\GeneratorBundle\Helper;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Glavweb\GeneratorBundle\Util\ModelClassParser;

/**
 * Class GeneratorHelper
 *
 * @package Glavweb\GeneratorBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class GeneratorHelper
{
    /**
     * GeneratorHelper constructor.
     * @param ModelClassParser $modelClassParser
     */
    public function __construct(ModelClassParser $modelClassParser)
    {
        $this->modelClassParser = $modelClassParser;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function lowerWords($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->lowerWords($value);
            }

            return $spec;
        }

        $spec = Inflector::tableize($spec);
        $spec = str_replace('_', ' ', $spec);

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function tableize($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->tableize($value);
            }

            return $spec;
        }

        $spec = Inflector::tableize($spec);

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function plural($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->plural($value);
            }

            return $spec;
        }

        $spec = Inflector::pluralize($spec);

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function lowerFirst($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->lowerFirst($value);
            }

            return $spec;
        }

        $spec = strtolower(substr($spec, 0, 1)) . substr($spec, 1);

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function upperFirst($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->upperFirst($value);
            }

            return $spec;
        }

        $spec = ucfirst($spec);

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function lowerDash($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->lowerDash($value);
            }

            return $spec;
        }

        $spec = str_replace('_', '-', Inflector::tableize($spec));

        return $spec;
    }

    /**
     * @param string|array $spec
     * @return mixed
     */
    public function singular($spec)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $spec[$key] = $this->singular($value);
            }

            return $spec;
        }

        $spec = Inflector::singularize($spec);

        return $spec;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function fixture($field)
    {
        if (is_array($field)) {
            $count = count($field);
            $key = rand(0, $count - 1);

            return $field[$key];
        }

        $isDate = is_string($field) && preg_match('/\<\(new \\\DateTime\(\'(.*)\'\)\)\>/', $field, $matches);
        if ($isDate) {
            $field = $matches[1];
        }

        if (strpos($field, '<') === 0) {
            return '';
        }

        $field = str_replace("'", '"', $field);

        return $field;
    }

    /**
     * @param string $value
     * @param string $fieldType
     * @param string $additional
     * @return mixed
     */
    public function modifyValue($value, $fieldType, $additional = 'new')
    {
        if ($fieldType == 'boolean') {
            $value = $value ? 'false' : 'true';

        } elseif (is_numeric($value)) {
            $value = $value + rand(1, 100);

            // is date
        } elseif ($fieldType == 'date' || $fieldType == 'datetime') {
            $date = new \DateTime($value);
            $value = $date->modify('+1 day')->format('Y-m-d');

        } else {
            $reflectionClass = new \ReflectionClass(Type::getType($fieldType));
            if ($reflectionClass->isSubclassOf('\Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType')) {
                $value = $this->getEnumValue($reflectionClass, $value);

            } else {
                $value = $additional . ' ' . $value;
            }
        }

        return $value;
    }

    /**
     * @param $reflectionClass
     * @return mixed
     */
    public function getEnumValue($reflectionClass, $value)
    {
        $enumClass   = $reflectionClass->getName();
        $values      = $enumClass::getValues();

        // drop current value
        $currentKey = array_search($value, $values);
        if ($currentKey !== false) {
            unset($values[$currentKey]);
            $values = array_values($values);
        }

        $countValues = count($values);
        if ($countValues) {
            $numValue = rand(0, $countValues - 1);
            $value = $values[$numValue];
        }

        return $value;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function isManyToMany($type)
    {
        return $type == ClassMetadataInfo::MANY_TO_MANY;
    }

    /**
     * @param array  $uploadableFields
     * @param string $fieldName
     * @return bool
     */
    public function isUploadableField($uploadableFields, $fieldName)
    {
        foreach ($uploadableFields as $uploadableField) {
            if ($uploadableField['fileNameProperty'] == $fieldName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $basename
     * @param $suffix
     * @return string
     */
    public function addBasenameSuffix($basename, $suffix)
    {
        $basenameParts = $this->getCamelCaseParts($basename);
        $suffixParts   = $this->getCamelCaseParts($suffix);

        $countSuffixParts   = count($suffixParts);
        $countBasenameParts = count($basenameParts);
        for ($i = 0; $i < $countBasenameParts; $i++) {
            $lastBasenamePartsKey = $countBasenameParts - $i - 1;
            $lastSuffixPartsKey   = $countSuffixParts - $i - 1;
            
            if (isset($suffixParts[$lastSuffixPartsKey]) &&
                $basenameParts[$lastBasenamePartsKey] == $suffixParts[$lastSuffixPartsKey]
            ) {
                unset($basenameParts[$lastBasenamePartsKey]);
            }
        }

        $result = implode('', $basenameParts) . $suffix;

        return $result;
    }

    /**
     * @param $value
     * @param null $fieldType
     * @param bool $singleQuotes
     * @return string
     */
    public function wrapInQuote($value, $fieldType = null, $singleQuotes = true)
    {
        if ($fieldType == 'boolean' || $fieldType == 'integer') {
            return $value;
        }

        if ($singleQuotes) {
            return "'" . $value . "'";
        }

        return '"' . $value . '"';
    }

    /**
     * @param array $array
     * @return array
     */
    public function wrapInQuotes(array $array)
    {
        return array_map(function ($item) {
            return '"' . $item . '"';
        }, $array);
    }

    /**
     * @param string $string
     * @param string $part
     * @return string
     */
    public function addToEndIfNotEmpty($string, $part)
    {
        if ($string) {
            return $string . $part;
        }

        return $string;
    }

    /**
     * @param string $modelClass
     * @param string $suffix
     * @return string
     */
    public function getFixtureFieldName($modelClass, $suffix = '-1')
    {
        $modelBasename = $this->modelClassParser->getModelBasename($modelClass);
        $subDirs = $this->modelClassParser->getModelSubDirs($modelClass);
        $subDirsDashed = array_map(function ($subDir) {
            return $this->lowerDash($subDir);
        }, $subDirs);

        $name =
            ($subDirs ? implode('-', $subDirsDashed) . '-' : '') .
            $this->lowerDash($modelBasename) . $suffix
        ;

        return $name;
    }

    /**
     * @param $suffix
     * @return mixed
     */
    private function getCamelCaseParts($suffix)
    {
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $suffix, $matches);

        return $matches[0];
    }
}