<?php

namespace Rithy\ZktecoAdms\Http\Controllers;

use Rithy\ZktecoAdms\Models\ZkCommand;
use Rithy\ZktecoAdms\Models\ZkDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ZkDeviceController extends \Illuminate\Routing\Controller
{
    public function index(): View
    {
        $devices = ZkDevice::latest()->get();

        return view('zk-devices.index', compact('devices'));
    }

    public function syncUsers(ZkDevice $device): RedirectResponse
    {
        ZkCommand::create([
            'device_sn' => $device->sn,
            'command' => 'DATA QUERY USERINFO',
            'status' => 'pending',
        ]);

        return back()->with('success', 'User sync command queued.');
    }

    public function syncBiodata(ZkDevice $device): RedirectResponse
    {
        ZkCommand::create([
            'device_sn' => $device->sn,
            'command' => 'DATA QUERY BIODATA',
            'status' => 'pending',
        ]);

        return back()->with('success', 'Biodata sync command queued.');
    }

    public function syncAttendance(ZkDevice $device): RedirectResponse
    {
        $start = $device->last_attendance_sync_at
            ? $device->last_attendance_sync_at->copy()->subMinutes(5)
            : now()->startOfDay();

        $end = now();

        ZkCommand::create([
            'device_sn' => $device->sn,
            'command' => 'DATA QUERY ATTLOG StartTime=' . $start->format('Y-m-d H:i:s')
                . "\tEndTime=" . $end->format('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Attendance sync command queued.');
    }
}