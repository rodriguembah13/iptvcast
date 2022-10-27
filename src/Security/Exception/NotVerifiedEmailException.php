<?php

namespace App\Security\Exception;

use Symfony\Component\Security\CoreException\CustomUserMessageAuthenticationException;


class NotVerifiedEmailException extends CustomUserMessageAuthenticationException
{
	
	public function __construct(
		string $message = "Ce compte ne semble pas posséder d\'email vérifié",
		array $messageData = [],
		int $code = 0,
		Throwable $previous = null
	) {
		parent::__construct($message, $messageData, $code, $previous);
	}
} 