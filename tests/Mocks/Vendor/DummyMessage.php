<?php declare(strict_types = 1);

namespace Tests\Mocks\Vendor;

final class DummyMessage
{

	public string $text;

	public function __construct(string $text)
	{
		$this->text = $text;
	}

}
