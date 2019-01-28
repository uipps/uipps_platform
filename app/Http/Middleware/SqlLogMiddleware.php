<?php

namespace App\Http\Middleware;

use App\Lib\Logs\Sql as SqlLog;
use Closure;

class SqlLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        foreach (app('db')->getConnections() as $conn) {
            $logger = new SqlLog($conn->getName());
            $logs = $conn->getQueryLog();
            $logger->info($logs);
        }

        return $response;
    }
}
