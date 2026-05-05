<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;

/**
 * StreamHandler yang memangkas file log agar tidak melebihi $maxEntries entri.
 * Satu "entri" adalah satu blok log yang diawali baris [YYYY-MM-DD HH:MM:SS].
 * Entri terlama dihapus otomatis setiap kali limit terlewati.
 */
class LimitedStreamHandler extends StreamHandler
{
    public function __construct(
        private readonly int $maxEntries,
        mixed $stream,
        mixed $level = 'debug',
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
    ) {
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }

    protected function write(LogRecord $record): void
    {
        parent::write($record);

        // Tutup stream agar file bisa dibaca/ditulis ulang dengan aman
        $this->close();

        if ($this->url && file_exists($this->url)) {
            $this->trimEntries($this->url);
        }
    }

    private function trimEntries(string $path): void
    {
        $content = file_get_contents($path);

        if ($content === false || $content === '') {
            return;
        }

        // Pisahkan per entri log (setiap entri diawali "[YYYY-MM-DD")
        $entries = preg_split('/(?=^\[\d{4}-\d{2}-\d{2})/m', $content, -1, PREG_SPLIT_NO_EMPTY);

        if (count($entries) <= $this->maxEntries) {
            return;
        }

        // Pertahankan hanya entri terbaru
        $trimmed = array_slice($entries, -$this->maxEntries);

        file_put_contents($path, implode('', $trimmed), LOCK_EX);
    }
}
