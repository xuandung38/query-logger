<?php

declare(strict_types=1);

namespace Hxd\QueryLogger;

use Hxd\QueryLogger\Formater\QueryFormatter;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Connection;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class QueryLogger implements QueryLoggerInterface
{
    /** @var object|null */
    private $query = null;

    /** @var string */
    private $log = '';

    /** @var array */
    private $config;

    /** @var \Illuminate\Database\Connection */
    private $db;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;


    /** @var \Hxd\QueryLogger\Formater\QueryFormatter */
    private $queryFormatter;

    public function __construct(
        Repository $config,
        Connection $db,
        LoggerInterface $logger,
        QueryFormatter $queryFormatter
    ) {
        $this->config = $config->get('query-logger', []);
        $this->db = $db;
        $this->logger = $logger->channel(
            $this->config['channel']
        );
        $this->queryFormatter = $queryFormatter;
    }

    /**
     * @param  string  $name
     * @return bool
     */
    private function isEnabledForConnection($name)
    {
        $connections = $this->config['log_connections'];

        if (empty($connections)) {
            return true;
        }

        return in_array($name, $connections, true);
    }

    private function mappingBindingValues()
    {
        $sql =  $this->queryFormatter->formatSql( $this->query->sql);

        if (! $this->config['enable_map_value']) {
            $this->log = $sql;
            return;
        }
        
        $bindings = $this->queryFormatter->cleanupBindings($this->query->bindings);

        $this->log = Str::replaceArray('?', $bindings, $sql);
        
    }

    private function addSlowQueryPrefixIfNeeded()
    {
        $slowQueryThreshold = $this->config['slow_query_threshold'];

        if ($slowQueryThreshold <= 0 || $this->query->time < $slowQueryThreshold) {
            return;
        }

        $this->log = "# SLOW_QUERY: {$this->log}";
    }

    private function addExecTime()
    {
        if ($this->config['log_execute_time']) {
            $this->logger->info(
                "Execute Time: {$this->query->time}ms"
            );
        }
    }

    private function addExecPath()
    {
        // FIXME: Move to backtrace, below only run on Http (Console/Job/Queuing is error!)
        if ($this->config['log_execute_path'] && !app()->runningInConsole()) {
            $this->logger->info(
                'Execute Path: ' . request()->route()->getActionName()
            );
        }
    }

    private function logging()
    {
        $this->logger->info('---- START QUERY LOG');

        $this->mappingBindingValues();

        $this->addSlowQueryPrefixIfNeeded();

        $this->logger->info(
            sprintf('[connection.%s] %s', $this->query->connectionName, $this->log)
        );

        $this->addExecTime();

        $this->addExecPath();

        $this->logger->info('---- END QUERY LOG');
    }

    public function boot()
    {
        if (!$this->config['enabled']) {
            return;
        }

        $this->db->listen(function ($query) {
            if (!$this->isEnabledForConnection($query->connectionName)) {
                return;
            }

            $this->query = $query;

            
            $this->logging();
        });
    }
}
