<?php

namespace Rithy\ZktecoAdms\Console\Commands;

use Illuminate\Console\Command;
use Rithy\ZktecoAdms\Models\ZkCommand;
use Rithy\ZktecoAdms\Models\ZkDevice;

class SyncZktecoAttendanceCommand extends Command
{
    protected $signature = 'zkteco:sync-attendance';

    protected $description = 'Create ZKTeco attendance sync commands for online devices and mark old sent commands as timeout';

    public function handle(): int
    {
        foreach (ZkDevice::where('online', true)->get() as $device) {
            $start = $device->last_attendance_sync_at
                ? $device->last_attendance_sync_at->copy()->subMinutes(5)
                : now()->startOfDay();

            ZkCommand::create([
                'device_sn' => $device->sn,
                'command' => 'DATA QUERY ATTLOG StartTime=' . $start->format('Y-m-d H:i:s')
                    . "\tEndTime=" . now()->format('Y-m-d H:i:s'),
            ]);
        }

        ZkCommand::where('status', 'sent')
            ->where('sent_at', '<', now()->subMinutes(5))
            ->update(['status' => 'timeout']);

        $this->info('ZKTeco attendance sync commands created successfully.');

        return self::SUCCESS;
    }
}