<?php

namespace Rithy\ZktecoAdms\Http\Controllers;

use Rithy\ZktecoAdms\Models\ZkCommand;
use Rithy\ZktecoAdms\Models\ZkDevice;
use Rithy\ZktecoAdms\Services\ZkRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IclockController extends \Illuminate\Routing\Controller
{
    public function handshake(Request $request, ZkRecordService $zkRecordService)
    {
        $sn = $request->input('SN');

        $zkRecordService->markDeviceOnline($sn, [
            'query' => $request->query(),
            'body' => $request->getContent(),
            'option' => $request->input('option') ?? $request->input('options'),
        ], $request->ip());

        $r = "GET OPTION FROM: {$sn}\r\n" .
            "Stamp=9999\r\n" .
            "OpStamp=" . time() . "\r\n" .
            "ErrorDelay=60\r\n" .
            "Delay=30\r\n" .
            "ResLogDay=18250\r\n" .
            "ResLogDelCount=10000\r\n" .
            "ResLogCount=50000\r\n" .
            "TransTimes=00:00;14:05\r\n" .
            "TransInterval=1\r\n" .
            "TransFlag=1111000000\r\n" .
            "Realtime=1\r\n" .
            "Encrypt=0\r\n";

        return response($r, 200)->header('Content-Type', 'text/plain');
    }

    public function receiveRecords(Request $request, ZkRecordService $zkRecordService)
    {
        $sn = (string) ($request->query('SN') ?? $request->input('SN') ?? '');
        $table = strtoupper((string) $request->query('table'));
        $body = trim($request->getContent());

        Log::info('Receive Records', [
            'sn' => $sn,
            'table' => $table,
            'query' => $request->query(),
            'body' => $body,
        ]);

        $zkRecordService->markDeviceOnline($sn, [
            'table' => $table,
            'query' => $request->query(),
            'body' => $body,
        ], $request->ip());

        if ($body === '') {
            return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
        }

        if ($table === 'ATTLOG') {
            $this->storeAttendanceLines($body, $sn, $zkRecordService);
        }

        if (
            $table === 'USERINFO' ||
            $table === 'USER' ||
            ($table === 'OPERLOG' && str_contains($body, 'USER PIN='))
        ) {
            $this->storeUserLines($body, $zkRecordService);
        }

        if ($table === 'BIODATA') {
            $this->storeBiodataLines($body, $sn, $zkRecordService);
        }

        return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
    }

    public function getrequest(Request $request, ZkRecordService $zkRecordService)
    {
        $sn = $request->input('SN');

        $zkRecordService->markDeviceOnline($sn, [
            'query' => $request->query(),
            'body' => $request->getContent(),
        ], $request->ip());

        $command = ZkCommand::where('device_sn', $sn)
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (! $command) {
            return response("OK\r\n", 200)->header('Content-Type', 'text/plain');
        }

        $command->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response("C:{$command->id}:{$command->command}\r\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    public function deviceCmd(Request $request)
    {
        $body = trim($request->getContent());

        Log::info('ZKTeco devicecmd called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query(),
            'body' => $body,
            'all' => $request->all(),
        ]);

        parse_str($body, $data);

        $commandId = $data['ID'] ?? null;

        if (! $commandId) {
            return response("OK\r\n", 200)
                ->header('Content-Type', 'text/plain');
        }

        $command = ZkCommand::find($commandId);

        if (! $command) {
            Log::warning('ZKTeco command result received but command not found', [
                'command_id' => $commandId,
                'body' => $body,
            ]);

            return response("OK\r\n", 200)
                ->header('Content-Type', 'text/plain');
        }

        $success = ($data['Return'] ?? null) === '0';

        $command->update([
            'status' => $success ? 'success' : 'failed',
            'response' => $body,
            'completed_at' => now(),
        ]);

        if ($success) {
            $device = ZkDevice::where('sn', $command->device_sn)->first();

            if ($device) {
                if (str_contains($command->command, 'DATA QUERY USERINFO')) {
                    $device->update(['last_user_sync_at' => now()]);
                }

                if (str_contains($command->command, 'DATA QUERY ATTLOG')) {
                    $device->update(['last_attendance_sync_at' => now()]);
                }

                if (str_contains($command->command, 'DATA QUERY BIODATA')) {
                    $device->update(['last_biodata_sync_at' => now()]);
                }
            }
        }

        return response("OK\r\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    private function storeAttendanceLines(string $body, string $sn, ZkRecordService $zkRecordService): void
    {
        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = explode("\t", $line);

            if (count($parts) < 2) {
                continue;
            }

            $zkRecordService->storeAttendance([
                'device_sn' => $sn,
                'pin' => $parts[0],
                'punch_time' => $parts[1],
                'verify_type' => $parts[2] ?? null,
                'punch_state' => $parts[3] ?? null,
                'raw' => $line,
            ]);
        }
    }

    private function storeUserLines(string $body, ZkRecordService $zkRecordService): void
    {
        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, 'USERINFO')) {
                $line = trim(substr($line, strlen('USERINFO')));
            }

            if (str_starts_with($line, 'USER')) {
                $line = trim(substr($line, strlen('USER')));
            }

            if (str_starts_with($line, 'USER ')) {
                $line = trim(substr($line, strlen('USER')));
            }

            $data = $this->parseKeyValueLine($line);

            $pin = $data['PIN'] ?? $data['Pin'] ?? null;

            if ($pin === null || $pin === '') {
                Log::warning('Invalid USER skipped', [
                    'line' => $line,
                    'parsed' => $data,
                ]);
                continue;
            }

            $zkRecordService->storeUser([
                'pin' => $pin,
                'name' => $data['Name'] ?? null,
                'privilege' => $data['Pri'] ?? null,
                'password' => $data['Passwd'] ?? null,
                'card' => $data['Card'] ?? null,
                'group' => $data['Grp'] ?? null,
                'timezone' => $data['TZ'] ?? null,
                'raw' => $line,
            ]);
        }
    }

    private function storeBiodataLines(string $body, string $sn, ZkRecordService $zkRecordService): void
    {
        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '' || ! str_starts_with($line, 'BIODATA')) {
                continue;
            }

            $data = $this->parseBiodataLine($line);

            foreach (['Pin', 'Type', 'Index', 'Tmp'] as $key) {
                if (! array_key_exists($key, $data) || $data[$key] === '') {
                    Log::warning('Invalid BIODATA skipped', [
                        'missing' => $key,
                        'line' => $line,
                        'parsed' => $data,
                    ]);
                    continue 2;
                }
            }

            $zkRecordService->storeBiodata([
                'device_sn' => $sn,
                'pin' => $data['Pin'],
                'biometric_type' => $data['Type'],
                'template_no' => $data['No'] ?? null,
                'template_index' => $data['Index'],
                'valid' => $data['Valid'] ?? null,
                'duress' => $data['Duress'] ?? null,
                'major_version' => $data['MajorVer'] ?? null,
                'minor_version' => $data['MinorVer'] ?? null,
                'format' => $data['Format'] ?? null,
                'template' => $data['Tmp'],
                'raw_data' => $line,
            ]);
        }
    }

    private function parseBiodataLine(string $line): array
    {
        $payload = trim(substr($line, strlen('BIODATA')));

        return $this->parseKeyValueLine($payload);
    }

    private function parseKeyValueLine(string $line): array
    {
        $segments = preg_split('/\t+/', trim($line));
        $data = [];

        foreach ($segments as $segment) {
            if ($segment === '' || ! str_contains($segment, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $segment, 2);
            $key = trim($key);

            if ($key !== '') {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}