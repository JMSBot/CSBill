<?php

/*
 * This file is part of the CSBill package.
 *
 * (c) Pierre du Plessis <info@customscripts.co.za>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSBill\CoreBundle\Menu;

use Knp\Menu\Provider\MenuProviderInterface;
use CSBill\CoreBundle\Menu\Builder\MenuBuilder;
use CSBill\CoreBundle\Menu\Builder\BuilderInterface;
use CSBill\CoreBundle\Menu\Storage\MenuStorageInterface;

class Provider implements MenuProviderInterface
{
    /**
     * @var MenuStorageInterface
     */
    protected $storage;

    /**
     * @param MenuStorageInterface $storage
     */
    public function __construct(MenuStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Gets the storage for the specific menu
     *
     * @param string $name
     * @param array  $options
     */
    public function get($name, array $options = array())
    {
        return $this->storage->get($name);
    }

    /**
     * Checks if the storage has builders for the specified menu
     *
     * @param string $name
     * @param array $options
     */
    public function has($name, array $options = array())
    {
        return $this->storage->has($name);
    }

    /**
     * Adds a builder to the storage
     *
     * @param BuilderInterface $class
     * @param string           $name   The name of the menu the builder should be attached to
     * @param string           $method The method to call to build the menu
     */
    public function addBuilder(BuilderInterface $class, $name, $method)
    {
        $builder = new MenuBuilder($class, $method);

        $this->storage->get($name)->attach($builder);
    }
}
