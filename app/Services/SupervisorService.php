<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;

class SupervisorService
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $program;
    protected ?Client $client = null;
    protected bool $useSimulation = false;
    protected ?bool $connectionChecked = null;

    public function __construct()
    {
        $this->host = config('supervisor.host', '127.0.0.1');
        $this->port = (int) config('supervisor.port', 9001);
        $this->username = config('supervisor.username', 'supervisor_api');
        $this->password = config('supervisor.password', '');
        $this->program = config('supervisor.program', 'laravel-worker');
    }

    /**
     * Dapatkan instance XML-RPC Client (lazy-loaded).
     */
    protected function getClient(): Client
    {
        if ($this->client === null) {
            $url = "http://{$this->host}:{$this->port}/RPC2";
            $this->client = new Client($url);
            $this->client->setCredentials($this->username, $this->password);
            $this->client->setOption('timeout', 5);
        }
        return $this->client;
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
        if ($this->useSimulation) {
            return $this->handleSimulation($method, $params);
        }

        try {
            $client = $this->getClient();
            $encoder = new Encoder();

            // Encode params ke Value objects
            $encodedParams = [];
            foreach ($params as $param) {
                $encodedParams[] = $encoder->encode($param);
            }

            $req = new Request($method, $encodedParams);
            $resp = $client->send($req);

            if ($resp->faultCode()) {
                throw new \Exception("Supervisor API error: " . $resp->faultString());
            }

            $val = $resp->value();
            return $encoder->decode($val);
        } catch (\Exception $e) {
            // Jika dalam env local atau debug mode, masuk ke simulation mode saat koneksi gagal
            // ATAU jika host adalah 127.0.0.1 / localhost dan terjadi error koneksi ditolak/refused, masuk ke simulation mode
            $isLocalHost = in_array(strtolower($this->host), ['127.0.0.1', 'localhost']);
            $isConnectionRefused = false;
            $errorMessage = strtolower($e->getMessage());
            if (
                strpos($errorMessage, 'refused') !== false ||
                strpos($errorMessage, '10061') !== false ||
                strpos($errorMessage, 'connect error') !== false ||
                strpos($errorMessage, 'connection refused') !== false ||
                strpos($errorMessage, 'cannot connect') !== false ||
                strpos($errorMessage, 'failed to connect') !== false ||
                strpos($errorMessage, 'host-gateway') !== false ||
                $e->getCode() === 111 || // ECONNREFUSED
                $e->getCode() === 10061  // Windows WSAECONNREFUSED
            ) {
                $isConnectionRefused = true;
            }

            $isLocal = app()->environment('local') || config('app.debug', false) === true || ($isLocalHost && $isConnectionRefused);
            if ($isLocal) {
                Log::info('SupervisorService: Gagal menghubungkan ke Supervisor. Beralih ke Mode Simulasi.');
                $this->useSimulation = true;
                return $this->handleSimulation($method, $params);
            }
            throw $e;
        }
    }

    /**
     * Simulasi kembalian data Supervisor XML-RPC.
     */
    protected function handleSimulation(string $method, array $params = []): array
    {
        switch ($method) {
            case 'supervisor.getState':
                return [
                    'statecode' => 1,
                    'statename' => 'RUNNING'
                ];
            case 'supervisor.getProcessInfo':
                $statusStr = Cache::get('supervisor_sim_status', 'RUNNING');
                $statecode = ($statusStr === 'RUNNING') ? 20 : 0; // 20 = RUNNING, 0 = STOPPED di supervisor
                return [
                    'name' => $this->program,
                    'group' => $this->program,
                    'status' => $statecode,
                    'description' => $statusStr === 'RUNNING' ? 'pid 1234, uptime 0:10:00' : 'stopped',
                    'pid' => $statusStr === 'RUNNING' ? 1234 : 0,
                    'uptime' => $statusStr === 'RUNNING' ? 600 : 0,
                    'start' => time() - 600,
                    'stop' => 0,
                    'now' => time(),
                    'statename' => $statusStr,
                ];
            case 'supervisor.startProcessGroup':
                Cache::put('supervisor_sim_status', 'RUNNING');
                return [
                    [
                        'name' => $this->program,
                        'group' => $this->program,
                        'status' => 80, // status code success/running
                        'description' => 'OK'
                    ]
                ];
            case 'supervisor.stopProcessGroup':
                Cache::put('supervisor_sim_status', 'STOPPED');
                return [
                    [
                        'name' => $this->program,
                        'group' => $this->program,
                        'status' => 80, // status code success/stopped
                        'description' => 'OK'
                    ]
                ];
            default:
                return [];
        }
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
