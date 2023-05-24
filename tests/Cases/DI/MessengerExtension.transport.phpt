<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\Messenger\DI\MessengerExtension;
use Contributte\Tester\Toolkit;
use Nette\DI\Compiler;
use Nette\DI\InvalidConfigurationException;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Tester\Assert;
use Tests\Toolkit\Container;
use Tests\Toolkit\Helpers;

require_once __DIR__ . '/../../bootstrap.php';

// Default setup
Toolkit::test(function (): void {
	$container = Container::of()
		->withDefaults()
		->build();

	Assert::count(5, $container->findByType(TransportFactoryInterface::class));
});

// Count transport factories
Toolkit::test(function (): void {
	$container = Container::of()
		->withDefaults()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addConfig(Helpers::neon(<<<'NEON'
				messenger:
					transportFactory:
			NEON
			));
		})
		->build();

	Assert::count(5, $container->findByType(TransportFactoryInterface::class));
});

// Create transport from factory
Toolkit::test(static function() {
	$container = Container::of()
		->withDefaults()
		->withCompiler(static function (Compiler $compiler): void {
			$compiler->addConfig(Helpers::neon(<<<'NEON'
				messenger:
					transport:
						memory1:
							dsn: in-memory://

						memory2:
							dsn: in-memory://
							options:
								serialize: false
							serializer: Symfony\Component\Messenger\Transport\Serialization\PhpSerializer

						redis1:
							dsn: "redis://redis:user@localhost/queue/group/consumer?lazy=true"

						sync1:
							dsn: "sync://"
				NEON
			));
		})
		->build();

	Assert::type(InMemoryTransport::class, $container->getService('messenger.transport.memory1'));
	Assert::type(InMemoryTransport::class, $container->getService('messenger.transport.memory2'));
	Assert::type(RedisTransport::class, $container->getService('messenger.transport.redis1'));
	Assert::type(SyncTransport::class, $container->getService('messenger.transport.sync1'));

	Assert::exception(
		static fn() => $container->getByType(TransportFactoryInterface::class)->createTransport('fake://', [], new PhpSerializer()),
		InvalidArgumentException::class,
		'No transport supports the given Messenger DSN "fake://"..'
	);
});
