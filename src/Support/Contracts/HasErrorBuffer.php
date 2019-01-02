<?php

namespace Nuwave\Lighthouse\Support\Contracts;

use Nuwave\Lighthouse\Execution\ErrorBuffer;

interface HasErrorBuffer
{
    /**
     * Get the ErrorBuffer instance.
     *
     * @return ErrorBuffer
     */
    public function errorBuffer(): ErrorBuffer;

    /**
     * Set the ErrorBuffer instance.
     *
     * @param ErrorBuffer $errorBuffer
     *
     * @return $this
     */
    public function setErrorBuffer(ErrorBuffer $errorBuffer): self;
}
