<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>ZKTeco Devices</title>
</head>
<body>
    <h1>ZKTeco Devices</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>SN</th>
                <th>Name</th>
                <th>IP</th>
                <th>Online</th>
                <th>Last Seen</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
                <tr>
                    <td>{{ $device->sn }}</td>
                    <td>{{ $device->name }}</td>
                    <td>{{ $device->ip }}</td>
                    <td>{{ $device->online ? 'Yes' : 'No' }}</td>
                    <td>{{ $device->last_seen_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('zk-devices.sync-users', $device) }}" style="display:inline;">
                            @csrf
                            <button type="submit">Sync Users</button>
                        </form>
                        <form method="POST" action="{{ route('zk-devices.sync-attendance', $device) }}" style="display:inline;">
                            @csrf
                            <button type="submit">Sync Attendance</button>
                        </form>
                        <form method="POST" action="{{ route('zk-devices.sync-biodata', $device) }}" style="display:inline;">
                            @csrf
                            <button type="submit">Sync Biodata</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
