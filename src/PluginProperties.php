<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use Exception;

/**
 * @method string basename()
 * @method string dirPath()
 * @method string dirUrl()
 * @method string filePath()
 * @method string name()
 * @method string website()
 * @method string version()
 * @method string textDomain()
 * @method string textDomainPath()
 */
class PluginProperties implements \ArrayAccess
{
    const BASENAME = 'basename';
    const DIR_PATH = 'dirPath';
    const DIR_URL = 'dirUrl';
    const FILE_PATH = 'filePath';
    const NAME = 'name';
    const WEBSITE = 'website';
    const VERSION = 'version';
    const TEXT_DOMAIN = 'textDomain';
    const TEXT_DOMAIN_PATH = 'textDomainPath';

    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $pluginFilePath
     */
    public function __construct($pluginFilePath)
    {
        assert(is_string($pluginFilePath));

        if (is_array($this->properties)) {
            return;
        }

        $fileData = [
            self::BASENAME => plugin_basename($pluginFilePath),
            self::DIR_PATH => plugin_dir_path($pluginFilePath),
            self::DIR_URL => plugins_url('/', $pluginFilePath),
            self::FILE_PATH => $pluginFilePath,
        ];

        $headerData = get_file_data(
            $pluginFilePath,
            [
                self::NAME => 'Plugin Name',
                self::WEBSITE => 'Plugin URI',
                self::VERSION => 'Version',
                self::TEXT_DOMAIN => 'Text Domain',
                self::TEXT_DOMAIN_PATH => 'Domain Path',
            ]
        );

        $this->properties = array_map('strval', array_merge($fileData, $headerData));
    }

    /**
     * @param $name
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function __call($name, array $args = [])
    {
        assert(is_string($name));

        if (!array_key_exists($name, $this->properties)) {
            throw new Exception(
                sprintf(
                    'Call to undefined method %s::%s()',
                    __CLASS__,
                    $name
                )
            );
        }

        return $this->properties[$name];
    }

    /**
     * Checks if a property with the given name exists.
     *
     * @param string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Returns the value of the property with the given name.
     *
     * @param string $offset
     * @return mixed
     * @throws \OutOfRangeException If there is no property with the given name.
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException("'{$offset}' is not a valid plugin property.");
        }

        return $this->properties[$offset];
    }

    /**
     * Disabled.
     *
     * @inheritdoc
     *
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(
            __METHOD__ . ' is not allowed. ' . __CLASS__ . ' is read only.'
        );
    }

    /**
     * Disabled.
     *
     * @inheritdoc
     *
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(
            __METHOD__ . ' is not allowed. ' . __CLASS__ . ' is read only.'
        );
    }
}