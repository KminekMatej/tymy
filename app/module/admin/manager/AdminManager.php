<?php

namespace Tymy\Module\Admin\Manager;

/**
 * Description of AdminManager
 *
 * @author kminekmatej, 25. 10. 2021
 */
class AdminManager
{
    public function __construct(private array $ghosts)
    {
    }

    /**
     * Returns validity of given token
     */
    public function allowToken(string $token): bool
    {
        return in_array($token, $this->ghosts);
    }
}
