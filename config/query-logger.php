<?php

return [
    // Enable or disable query logger
    'enabled' => env('QUERY_LOGGER_ENABLED', true),

    // Enable or disable query logger for specific connection
    'enable_for_connection' => env('QUERY_LOGGER_ENABLE_FOR_CONNECTION', null),

    // Channel you want to save query into (must have in laravel logging channel config)
    'channel' => env('QUERY_LOGGER_LOG_CHANNEL', 'stack'),

    // Enable or Disable automatically assign values to the query,
    // by default the queries will be hidden values to ensure security.
    // Make sure you know what you're doing when you turn this on
    'enable_map_value' => env('QUERY_LOGGER_ENABLE_MAP_VALUE', true),

    // Log query execute time
    'log_execute_time' => env('QUERY_LOGGER_LOG_EXEC_TIME', true),

    // Look at the name, you know, the threshold to assign "SLOW QUERY" before your query in the log
    'slow_query_threshold' => env('QUERY_LOGGER_SLOW_QUERY_THRESHOLD', 0),

	// Log query execute path
	'log_execute_path' => env('QUERY_LOGGER_LOG_EXEC_PATH', true),

    // Log connetions
    'log_connections' => array_filter(explode(',', env('QUERY_LOGGER_LOG_CONNECTIONS', ''))),
];
