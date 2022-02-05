<?php

namespace Tymy\Module\Admin\Manager;

/**
 * Description of AdminManager
 *
 * @author kminekmatej, 25. 10. 2021
 */
class AdminManager
{
    private array $ghosts;

    public function __construct(array $ghosts)
    {
        $this->ghosts = $ghosts;
    }

    /**
     * Returns validity of given token
     *
     * @param string $token
     * @return bool
     */
    public function allowToken(string $token): bool
    {
        return in_array($token, $this->ghosts);
    }
}
