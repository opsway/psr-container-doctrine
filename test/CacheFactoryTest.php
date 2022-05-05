<?php

declare(strict_types=1);

namespace RoaveTest\PsrContainerDoctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Roave\PsrContainerDoctrine\AbstractFactory;
use Roave\PsrContainerDoctrine\CacheFactory;
use Roave\PsrContainerDoctrine\Exception\OutOfBoundsException;

/**
 * @coversDefaultClass \Roave\PsrContainerDoctrine\CacheFactory
 */
final class CacheFactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testExtendsAbstractFactory(): void
    {
        self::assertInstanceOf(AbstractFactory::class, new CacheFactory());
    }

    /**
     * @covers ::createWithConfig
     */
    public function testThrowsForMissingConfigKey(): void
    {
        $container = $this->createContainerMockWithConfig(
            [
                'doctrine' => [
                    'cache' => [],
                ],
            ]
        );

        $factory = new CacheFactory('foo');
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Missing "class" config key');
        $factory($container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainerMockWithConfig(array $config): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('config')->willReturn(true);
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);

        return $container;
    }

    public function testCanRetrieveCacheItemPoolFromContainer(): void
    {
        $containerId = 'ContainerId';

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->withConsecutive(['config'], [$containerId])
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $container
            ->method('get')
            ->withConsecutive(['config'], [$containerId])
            ->willReturnOnConsecutiveCalls(
                ['doctrine' => ['cache' => ['foo' => ['class' => $containerId]]]],
                $cacheItemPool
            );

        $factory = new CacheFactory('foo');
        self::assertSame($cacheItemPool, $factory($container));
    }
}
