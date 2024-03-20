
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Passenger ID</th>
            <th>Passenger Name</th>
            <th>Mobile</th>
            <th>Email ID</th>
            <th>Reward Amount</th>
            <th>Ride Amount</th>
            <th>Status</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->user ? $report->user->id : '-' }}</td>
                <td>{{ $report->user ? $report->user->name : '-' }}</td>
                <td>{{ $report->user ? $report->user->mobile : '-' }}</td>
                <td>{{ $report->user ? $report->user->email : '-' }}</td>
                <td>{{ $report->reward_amount ? $report->reward_amount : '-' }}</td>
                <td>{{ $report->ride_amount ? $report->ride_amount : '-' }}</td>
                @if($report->status == 0)
                <td>Gifted</td>
                @elseif($report->status == 1)
                <td>Scratced</td>
                @elseif($report->status == 2)
                <td>Expired</td>
                @elseif($report->status == 3)
                <td>Requested</td>
                @else
                <td>Credited</td>
                @endif
            </tr>
        @endforeach

    </tbody>
</table>

