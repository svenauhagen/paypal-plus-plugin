<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Service;

/**
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
 */
class ServiceProvidersCollection implements \Countable
{
    /**
     * @var \SplObjectStorage
     */
    private $storage;

    /**
     * ServiceProvidersCollection constructor.
     */
    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    /**
     * Adds the given service provider to the collection.
     *
     * @param ServiceProvider $provider
     * @return ServiceProvidersCollection
     */
    public function add(ServiceProvider $provider)
    {
        $this->storage->attach($provider);

        return $this;
    }

    /**
     * Removes the given service provider from the collection.
     *
     * @param ServiceProvider $provider
     * @return ServiceProvidersCollection
     */
    public function remove(ServiceProvider $provider)
    {
        $this->storage->detach($provider);

        return $this;
    }

    /**
     * Calls the method with the given name on all registered providers,
     * and passes on potential further arguments.
     *
     * @param string $methodName
     * @param array ...$args
     */
    public function applyMethod($methodName)
    {
        assert(is_string($methodName));
        $args = array_slice(func_get_args(), 1);

        $this->storage->rewind();

        while ($this->storage->valid()) {
            /** @var callable $method */
            $method = [$this->storage->current(), $methodName];
            call_user_func_array($method, $args);

            $this->storage->next();
        }
    }

    /**
     * Executes the given callback for all registered providers,
     * and passes along potential further arguments.
     *
     * @param callable $callback
     * @param array ...$args
     */
    public function applyCallback(callable $callback)
    {
        $args = array_slice(func_get_args(), 1);

        $this->storage->rewind();

        while ($this->storage->valid()) {
            call_user_func_array($callback, [$this->storage->current()] + $args);
            $this->storage->next();
        }
    }

    /**
     * Executes the given callback for all registered providers, and returns the instance that
     * contains the providers that passed the filtering.
     *
     * @param callable $callback
     * @param array ...$args
     * @return ServiceProvidersCollection
     */
    public function filter(callable $callback)
    {
        $collection = new static();
        $args = array_slice(func_get_args(), 1);

        $this->storage->rewind();
        while ($this->storage->valid()) {
            /** @var ServiceProvider $provider */
            $provider = $this->storage->current();

            $callbackResponse = call_user_func_array($callback, [$provider] + $args);
            if ($callbackResponse) {
                $collection->add($provider);
            }

            $this->storage->next();
        }

        return $collection;
    }

    /**
     * Executes the given callback for all registered providers, and returns the instance that
     * contains the providers obtained.
     *
     * @param callable $callback
     * @param array ...$args
     * @return ServiceProvidersCollection
     * @throws \UnexpectedValueException If a given callback did not return a service provider instance.
     */
    public function map(callable $callback)
    {
        $collection = new static();
        $args = array_slice(func_get_args(), 1);

        $this->storage->rewind();
        while ($this->storage->valid()) {
            $provider = call_user_func_array($callback, [$this->storage->current()] + $args);
            if (!$provider instanceof ServiceProvider) {
                throw new \UnexpectedValueException(
                    __METHOD__ . ' expects transformation callbacks to return a service provider instance.'
                );
            }

            $collection->add($provider);
            $this->storage->next();
        }

        return $collection;
    }

    /**
     * Executes the given callback for all registered providers, and passes along the result of
     * previous callback.
     *
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function reduce(callable $callback, $initial = null)
    {
        // phpcs:enable

        $this->storage->rewind();
        $carry = $initial;
        while ($this->storage->valid()) {
            $carry = $callback($carry, $this->storage->current());
            $this->storage->next();
        }

        return $carry;
    }

    /**
     * Returns the number of providers in the collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->storage->count();
    }
}
