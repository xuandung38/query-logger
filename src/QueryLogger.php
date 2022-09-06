<?php

declare(strict_types=1);

namespace Hxd\QueryLogger;

use Illuminate\Database\Connection;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class QueryLogger implements QueryLoggerInterface
{
    /** @var object|null */
	private $query = null;

    /** @var string */
	private $fullLog = '';

    /** @var bool */
	private $isEnabled = true;
    /** @var bool */
	private $isMapValueEnabled = true;

    /** @var bool */
	private $logExecuteTime = true;

    /** @var int */
	private $slowQueryThreshold = 0;

    /** @var bool */
	private $logExecutePath = true;

    /** @var array */
	private $connections;

    /** @var \Illuminate\Database\Connection */
    private $db;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

	public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger->channel(
            config('query-logger.channel', 'stack')
        );

		$this->isEnabled = config('query-logger.enabled', true);
		$this->isMapValueEnabled = config('query-logger.enable_map_value', true);
		$this->logExecuteTime = config('query-logger.log_execute_time', true);
		$this->slowQueryThreshold = config('query-logger.slow_query_threshold', 0);
		$this->logExecutePath = config('query-logger.log_execute_path', true);
		$this->connections = config('query-logger.log_connections', []);
	}

	/**
	 * @param  string  $name
	 * @return bool
	 */
	private function isEnabledForConnection($name)
	{
		if (empty($this->connections)) {
			return true;
		}

		return in_array($name, $this->connections, true);
	}

	private function mappingBindingValues()
	{
		if (!$this->isMapValueEnabled) {
            $this->fullLog = $this->query->sql;
            return;
		}

        $this->fullLog = Str::replaceArray('?', $this->query->bindings, $this->query->sql);
	}

	private function addSlowQueryPrefixIfNeeded()
	{
		if ($this->slowQueryThreshold > 0 && $this->query->time >= $this->slowQueryThreshold) {
			$this->fullLog = "# SLOW_QUERY: {$this->fullLog}";
		}
	}

	private function addExecTime()
	{
		if ($this->logExecuteTime) {
		    $this->logger->info(
				"Execute Time: {$this->query->time}ms"
			);
		}
	}

	private function addExecPath()
	{
        // FIXME: Move to backtrace, below only run on Http (Console/Job/Queuing is error!)
		if ($this->logExecutePath && app()->runningInConsole()) {
			$this->logger->info(
				'Execute Path: ' . request()->route()->getActionName()
			);
		}
	}


	private function log(): void
	{
		$this->logger->info('---- START QUERY LOG');

		$this->mappingBindingValues();

		$this->addSlowQueryPrefixIfNeeded();

		$this->logger->info(
            sprintf('[connection.%s] %s', $this->query->connectionName, $this->fullLog)
        );

		$this->addExecTime();

		$this->addExecPath();

		$this->logger->info('---- END QUERY LOG');
	}

	public function boot()
	{
		if ($this->isEnabled) {
			$this->db->listen(function ($query) {
				if (! $this->isEnabledForConnection($query->connectionName)) {
                    return;
                }

                $this->query = $query;
                $this->log();
			});
		}
	}
}
