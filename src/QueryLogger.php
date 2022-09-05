<?php
declare(strict_types=1);

namespace Hxd\QueryLogger;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QueryLogger
{
	protected string $fullLog;
	private bool $isEnabled;
	private string $logChannel;
	private bool $enableMapValue;
	private bool $logExecTime;
	private int $slowQueryThreshold;
	private string $logConnections;

	public function __construct()
	{
		$this->isEnabled = config("query-logger.enabled");
		$this->logChannel = config("query-logger.channel", "stack");
		$this->enableMapValue = config("query-logger.map_value");
		$this->logExecTime = config("query-logger.log_exec_time");
		$this->slowQueryThreshold = config("query-logger.slow_query_threshold");
		$this->logConnections = config("query-logger.log_connections");
	}

	/**
	 * @param string $connectionName
	 *
	 * @return bool
	 */
	public function isEnabledForConnection(string $connectionName): bool
	{
		if ($this->logConnections === "all") {
			return true;
		}

		return in_array(
			$connectionName,
			explode(",", $this->logConnections)
		);
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	public function mapValue($query)
	{
		$this->fullLog = Str::replaceArray("?", $query->bindings, $query->sql);
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	private function addSlowPrefix($query)
	{
		$this->fullLog =
			$query->time >= $this->slowQueryThreshold
				? "SLOW QUERY: " . $this->fullLog
				: $this->fullLog;
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	private function generateLog($query): void
	{

		$this->fullLog = $query->sql;

		Log::channel($this->logChannel)->info('START QUERY LOG');

		if ($this->enableMapValue) {
			$this->mapValue($query);
		}
		if ($this->slowQueryThreshold > 0) {
			$this->addSlowPrefix($query);
		}

		Log::channel($this->logChannel)->info($this->fullLog);

		if ($this->logExecTime) {
			Log::channel($this->logChannel)->info(
				"Query Execute Time: " . $query->time . ' ms'
			);
		}
		Log::channel($this->logChannel)->info('END QUERYLOG');
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isEnabled) {
			DB::listen(function ($query) {
				if ($this->isEnabledForConnection($query->connectionName)) {
					$this->generateLog($query);
				}
				return 0;
			});
		}
	}
}
