<?php

namespace Rithy\ZktecoAdms\Services;

use Rithy\ZktecoAdms\Models\ZkAttendance;
use Rithy\ZktecoAdms\Models\ZkBiodata;
use Rithy\ZktecoAdms\Models\ZkCommand;
use Rithy\ZktecoAdms\Models\ZkDevice;
use Rithy\ZktecoAdms\Models\ZkUser;
use InvalidArgumentException;
use Illuminate\Support\Arr;

class ZkRecordService
{
    public function markDeviceOnline(?string $serialNumber, array $payload = [], ?string $ipAddress = null): ?ZkDevice
    {
        $serialNumber = $this->normalizeString($serialNumber);

        if ($serialNumber === null) {
            return null;
        }

        return ZkDevice::updateOrCreate(
            ['sn' => $serialNumber],
            $this->filterNullValues([
                'ip' => $this->normalizeString($ipAddress),
                'online' => true,
                'last_seen_at' => now(),
                'last_payload' => $payload !== [] ? $payload : null,
            ])
        );
    }

    public function storeUser(array $attributes): ZkUser
    {
        $payload = $this->filterNullValues([
            'pin' => $this->normalizeString($attributes['pin'] ?? null),
            'name' => $this->normalizeString($attributes['name'] ?? null),
            'privilege' => $this->normalizeString($attributes['privilege'] ?? null),
            'password' => $this->normalizeString($attributes['password'] ?? null),
            'card' => $this->normalizeString($attributes['card'] ?? null),
            'group' => $this->normalizeString($attributes['group'] ?? null),
            'timezone' => $this->normalizeString($attributes['timezone'] ?? null),
            'raw' => $attributes['raw'] ?? null,
        ]);

        $this->assertRequired($payload, ['pin']);

        return ZkUser::updateOrCreate(
            ['pin' => $payload['pin']],
            Arr::except($payload, ['pin'])
        );
    }

    public function storeAttendance(array $attributes): ZkAttendance
    {
        $payload = $this->filterNullValues([
            'device_sn' => $this->normalizeString($attributes['device_sn'] ?? null),
            'pin' => $this->normalizeString($attributes['pin'] ?? null),
            'punch_time' => $attributes['punch_time'] ?? null,
            'verify_type' => $this->parseInteger($attributes['verify_type'] ?? null),
            'punch_state' => $this->parseInteger($attributes['punch_state'] ?? null),
            'raw' => $attributes['raw'] ?? null,
        ]);

        $this->assertRequired($payload, ['device_sn', 'pin', 'punch_time']);

        return ZkAttendance::updateOrCreate(
            Arr::only($payload, ['device_sn', 'pin', 'punch_time']),
            Arr::except($payload, ['device_sn', 'pin', 'punch_time'])
        );
    }

    public function storeBiodata(array $attributes): ZkBiodata
    {
        $payload = $this->filterNullValues([
            'device_sn' => $this->normalizeString($attributes['device_sn'] ?? null),
            'pin' => $this->normalizeString($attributes['pin'] ?? null),
            'biometric_type' => $this->parseInteger($attributes['biometric_type'] ?? null),
            'template_no' => $this->parseInteger($attributes['template_no'] ?? null),
            'template_index' => $this->parseInteger($attributes['template_index'] ?? null),
            'valid' => $this->parseBoolean($attributes['valid'] ?? null),
            'duress' => $this->parseBoolean($attributes['duress'] ?? null),
            'major_version' => $this->parseInteger($attributes['major_version'] ?? null),
            'minor_version' => $this->parseInteger($attributes['minor_version'] ?? null),
            'format' => $this->parseInteger($attributes['format'] ?? null),
            'template' => $attributes['template'] ?? null,
            'raw_data' => $attributes['raw_data'] ?? null,
        ]);

        $this->assertRequired($payload, ['device_sn', 'pin', 'biometric_type', 'template_index']);

        return ZkBiodata::updateOrCreate(
            Arr::only($payload, ['device_sn', 'pin', 'biometric_type', 'template_no', 'template_index']),
            Arr::except($payload, ['device_sn', 'pin', 'biometric_type', 'template_no', 'template_index'])
        );
    }

    public function createCommand(array $attributes): ZkCommand
    {
        $payload = $this->filterNullValues([
            'device_sn' => $this->normalizeString($attributes['device_sn'] ?? null),
            'command' => $attributes['command'] ?? null,
            'status' => $this->normalizeString($attributes['status'] ?? null) ?? 'pending',
            'response' => $attributes['response'] ?? null,
            'sent_at' => $attributes['sent_at'] ?? null,
            'completed_at' => $attributes['completed_at'] ?? null,
        ]);

        $this->assertRequired($payload, ['device_sn', 'command']);

        return ZkCommand::create($payload);
    }

    private function normalizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseInteger($value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function parseBoolean($value): ?bool
    {
        $integer = $this->parseInteger($value);

        if ($integer === null) {
            return null;
        }

        return (bool) $integer;
    }

    private function filterNullValues(array $payload): array
    {
        return array_filter(
            $payload,
            static fn ($value) => $value !== null
        );
    }

    private function assertRequired(array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null) {
                throw new InvalidArgumentException("The [{$field}] field is required.");
            }
        }
    }
}
