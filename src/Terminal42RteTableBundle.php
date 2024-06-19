<?php

declare(strict_types=1);

namespace Terminal42\RteTableBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42RteTableBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
