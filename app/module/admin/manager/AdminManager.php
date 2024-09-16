<?php

namespace Tymy\Module\Admin\Manager;

/**
 * Description of AdminManager
 */
class AdminManager
{
    public function __construct(private ?array $ghosts)
    {
    }

    /**
     * Returns validity of given token
     */
    public function allowToken(string $token): bool
    {
        return $this->ghosts && in_array($token, $this->ghosts);
    }
}
