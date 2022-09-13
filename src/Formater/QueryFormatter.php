<?php
declare(strict_types=1);

namespace Hxd\QueryLogger\Formater;

class QueryFormatter implements QueryFormatterInterface
{
    
    /**
     * Removes extra spaces at the beginning and end of the SQL query and its lines.
     *
     * @param  string $sql
     * @return string
     */
    public function formatSql($sql)
    {
        $sql = preg_replace("/\?(?=(?:[^'\\\']*'[^'\\']*')*[^'\\\']*$)(?:\?)/", '?', $sql);
        $sql = trim(preg_replace("/\s*\n\s*/", "\n", $sql));

        return $sql;
    }

    /**
     * Make the bindings safe for outputting.
     *
     * @param array $bindings
     * @return array
     */
    public function escapeBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            $binding = htmlentities((string) $binding, ENT_QUOTES, 'UTF-8', false);
        }

        return $bindings;
    }

    /**
     * Cleanup bindings for illegal (non UTF-8) strings, like Binary data, datetime data.
     *
     * @param $bindings
     * @return mixed
     */

    public function cleanupBindings(array $bindings)
    {
        return array_map(function ($binding) {

            if (is_string($binding) && !mb_check_encoding($binding, 'UTF-8')) {
                $binding = '[BINARY DATA]';
            }
            
            if (is_string($binding) && mb_check_encoding($binding, 'UTF-8')) {
                $binding = "'{$binding}'";
            }

            if ($binding instanceof \DateTimeInterface) {
                return $binding->format('Y-m-d H:i:s');
            }

            if (is_array($binding)) {
                $binding = $this->cleanupBindings($binding);
                $binding = '[' . implode(',', $binding) . ']';
            }

            if (is_object($binding)) {
                $binding =  json_encode($binding);
            }
            return $binding;
        }, $bindings);
    }
}