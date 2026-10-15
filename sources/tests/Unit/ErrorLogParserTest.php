<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ErrorLogParserTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function test_log_regex_parsing()
    {
        $logLine = '[2026-09-29 11:15:20] local.ERROR: [TSU_DATA_KARYAWAN_DELETE_FAIL] Data Karyawan tidak ditemukan. {"exception":"[object]"}';
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[\+-]\d{2}:\d{2})?)\]\s+(?:(\w+)\.)?([A-Z]+):\s*(.*)$/';

        $matched = preg_match($pattern, $logLine, $matches);
        $this->assertEquals(1, $matched);
        $this->assertEquals('2026-09-29 11:15:20', $matches[1]);
        $this->assertEquals('local', $matches[2]);
        $this->assertEquals('ERROR', $matches[3]);

        $message = $matches[4];
        $matchedTsu = preg_match('/\[TSU_[A-Z0-9_]+\]/', $message, $tsuMatch);
        $this->assertEquals(1, $matchedTsu);
        $this->assertEquals('[TSU_DATA_KARYAWAN_DELETE_FAIL]', $tsuMatch[0]);
    }
}
