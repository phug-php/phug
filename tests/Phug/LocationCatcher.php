<?php

namespace Phug\Test;

use Phug\Util\SourceLocation;

class LocationCatcher
{
    protected $location;

    public function __construct(SourceLocation $location)
    {
        $this->location = $location;
    }

    /**
     * @return SourceLocation
     */
    public function getLocation()
    {
        return $this->location;
    }
}
