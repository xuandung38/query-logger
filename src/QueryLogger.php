<?php
declare(strict_types=1);

namespace Hxd\QueryLogger;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QueryLogger
{
	private ? object $query;
	protected string $fullLog;
	private bool $isEnabled;
	private string $logChannel;
	private bool $enableMapValue;
	private bool $logExecTime;
	private int $slowQueryThreshold;
	private bool $logExecPath;
	private ?string $logConnections;

	public function __construct()
	{
		$this->isEnabled = config("query-logger.enabled");
		$this->logChannel = config("query-logger.channel", "stack");
		$this->enableMapValue = config("query-logger.map_value");
		$this->logExecTime = config("query-logger.log_exec_time");
		$this->slowQueryThreshold = config("query-logger.slow_query_threshold");
		$this->logExecPath = config("query-logger.log_exec_path");
		$this->logConnections = config("query-logger.log_connections");
	}

	/**
	 * @param string $connectionName
	 *
	 * @return bool
	 */
	public function isEnabledForConnection(string $connectionName): bool
	{
		if (is_null($this->logConnections)) {
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
	public function mapValue()
	{
		if ($this->enableMapValue) {
			$this->fullLog = Str::replaceArray("?", $this->query->bindings, $this->query->sql);
		}
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	private function addSlowPrefix()
	{
		if ($this->slowQueryThreshold > 0 && $this->query->time >= $this->slowQueryThreshold) {
			$this->fullLog = "#SLOW_QUERY: " . $this->fullLog;
		}
	}

	private function addExecTime()
	{
		if ($this->logExecTime) {
			Log::channel($this->logChannel)->info(
				"Execute Time: " . $this->query->time . ' ms'
			);
		}
	}

	private function addExecPath()
	{
		if ($this->logExecPath) {
			Log::channel($this->logChannel)->info(
				"Execute Path: " . request()->route()->getActionName()
			);
		}
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	private function generateLog(): void
	{
		Log::channel($this->logChannel)->info('START QUERY LOG');

		self::mapValue();
		self::addSlowPrefix();

		Log::channel($this->logChannel)->info(sprintf('[connection.%s] %s', $this->query->connectionName, $this->fullLog));

		self::addExecTime();
		self::addExecPath();

		Log::channel($this->logChannel)->info('END QUERY LOG');
	}

	/**
	 * @return void
	 */
	public function init()
	{
		if ($this->isEnabled) {
			DB::listen(function ($query) {
				if ($this->isEnabledForConnection($query->connectionName)) {
					$this->query = $query;
					$this->generateLog();
				}
				return 0;
			});
		}
	}
}
