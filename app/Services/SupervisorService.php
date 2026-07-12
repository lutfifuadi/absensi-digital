<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SupervisorService
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $program;

    public function __construct()
    {
        $this->host = config('supervisor.host', '127.0.0.1');
        $this->port = (int) config('supervisor.port', 9001);
        $this->username = config('supervisor.username', 'supervisor_api');
        $this->password = config('supervisor.password', '');
        $this->program = config('supervisor.program', 'laravel-worker');
    }

    /**
     * Call XML-RPC method to Supervisor API.
     *
     * @param string $method Nama method XML-RPC (e.g. supervisor.getState)
     * @param array $params Parameter untuk method
     * @return array Response dari Supervisor API
     * @throws \Exception
     */
    protected function call(string $method, array $params = []): array
    {
        $url = "http://{$this->host}:{$this->port}/RPC2";

        $request = xmlrpc_encode_request($method, $params);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: text/xml\r\nAuthorization: Basic " . base64_encode("{$this->username}:{$this->password}"),
                'content' => $request,
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \Exception('Tidak dapat terhubung ke Supervisor API');
        }

        $result = xmlrpc_decode($response);

        if (is_array($result) && isset($result['faultCode'])) {
            throw new \Exception("Supervisor API error: {$result['faultString']}");
        }

        return (array) $result;
    }

    /**
     * Cek apakah Supervisor dalam keadaan RUNNING.
     */
    public function isRunning(): bool
    {
        try {
            $state = $this->call('supervisor.getState');
            return ($state['statecode'] ?? 0) === 1; // RUNNING state
        } catch (\Exception $e) {
            Log::warning('SupervisorService::isRunning gagal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan informasi detail dari process worker.
     */
    public function getProcessInfo(): array
    {
        try {
            return $this->call('supervisor.getProcessInfo', [$this->program . ':' . $this->program . '_00']);
        } catch (\Exception $e) {
            Log::warning('SupervisorService::getProcessInfo gagal: ' . $e->getMessage());
            return [
                'name' => $this->program,
                'group' => $this->program,
                'status' => 0,
                'description' => 'Tidak terhubung ke Supervisor',
                'pid' => 0,
                'uptime' => 0,
                'start' => 0,
                'stop' => 0,
                'now' => 0,
                'statename' => 'UNKNOWN',
            ];
        }
    }

    /**
     * Start process group worker.
     */
    public function start(): array
    {
        return $this->call('supervisor.startProcessGroup', [$this->program]);
    }

    /**
     * Stop process group worker.
     */
    public function stop(): array
    {
        return $this->call('supervisor.stopProcessGroup', [$this->program]);
    }

    /**
     * Restart process group worker (stop + start).
     */
    public function restart(): array
    {
        $this->stop();
        sleep(1);
        return $this->start();
    }

    /**
     * Dapatkan status process worker secara keseluruhan.
     *
     * Return array: [success, message, status (running/stopped), process_info]
     */
    public function getStatus(): array
    {
        $supervisorRunning = $this->isRunning();

        if (!$supervisorRunning) {
            return [
                'success' => false,
                'message' => 'Supervisor tidak berjalan atau tidak dapat dijangkau.',
                'status' => 'stopped',
                'process_info' => null,
            ];
        }

        $processInfo = $this->getProcessInfo();
        $stateName = $processInfo['statename'] ?? 'UNKNOWN';
        $isRunning = in_array($stateName, ['RUNNING', 'STARTING']);

        return [
            'success' => true,
            'message' => $isRunning ? 'Worker sedang berjalan.' : 'Worker sedang berhenti.',
            'status' => $isRunning ? 'running' : 'stopped',
            'process_info' => $processInfo,
        ];
    }
}
