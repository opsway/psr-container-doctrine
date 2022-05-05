<?php

declare(strict_types=1);

namespace Roave\PsrContainerDoctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

use function array_key_exists;
use function array_map;
use function assert;
use function is_array;
use function is_string;

/**
 * @method Cache|CacheItemPoolInterface __invoke(ContainerInterface $container)
 */
final class CacheFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    protected function createWithConfig(ContainerInterface $container, string $configKey)
    {
        $config = $this->retrieveConfig($container, $configKey, 'cache');

        if (! array_key_exists('class', $config)) {
            throw OutOfBoundsException::forMissingConfigKey('class');
        }

        $cache = $container->has($config['class']) ? $container->get($config['class']) : new $config['class']();

        if ($cache instanceof CacheProvider && array_key_exists('namespace', $config)) {
            $cache->setNamespace($config['namespace']);
        }

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig(string $configKey) : array
    {
        return [];
    }
}
