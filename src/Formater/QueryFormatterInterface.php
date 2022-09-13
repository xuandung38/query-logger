<?php

declare(strict_types=1);

namespace Hxd\QueryLogger\Formater;

interface QueryFormatterInterface
{
    public function formatSql($sql);

    public function cleanupBindings(array $bindings);

}