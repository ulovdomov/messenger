<?php declare(strict_types = 1);

namespace Contributte\Messenger\Logger;

use Psr\Log\AbstractLogger;
use Stringable;

class BufferLogger extends AbstractLogger
{

	/** @var array<array{message: string|Stringable}> */
	private array $logs = [];

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @param string|Stringable $message
	 * @param mixed[] $context
	 */
	public function log(mixed $level, $message, array $context = []): void
	{
		$this->logs[] = ['message' => $message];
	}

	/**
	 * @return array<array{message: string|Stringable}>
	 */
	public function obtain(): array
	{
		return $this->logs;
	}

}
