<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" >
    <title>AutoKaar</title>
    <style>
        table {
            border: 1px solid #000;
            margin: 15px 0;
        }
        table th,
            table td {
            border: 1px solid #000;
            padding: 10px;

        }
        </style>
  </head>
  <body>
    <div class="w-90">
        <table   class="table ">
                <tbody class=" my-3" >
                    @foreach($completed_rides as $ride)
                    <img src="{{ asset('assets/img/logo-text.png') }}" class="py-3" width="100" height="100"/>
                    <tr>
                        <th>Invoice ID</th>
                        <td>{{ '#00'.$loop->iteration }}</td>
                    </tr>
                    <tr>
                        <th>Ride ID</th>
                        <td>{{ '#00'.$ride->id }}</td>
                    </tr>
                    <tr>
                        <th>Ride Type</th>
                        @if($ride->ride_type == 1)
                        <td>Online Booking</td>
                        @elseif($ride->ride_type == 2)
                        <td>Pre Booking</td>
                        @elseif($ride->ride_type == 3)
                        <td>Offline Booking</td>
                        @endif
                    </tr>
                    <tr>
                        <th>Driver Name</th>
                        <td>{{ $ride->driver ? $ride->driver->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>User Name</th>
                        <td>{{ $ride->user ? $ride->user->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>From</th>
                        <td>{{ $ride->rideDetail->pickup_address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>To</th>
                        <td>{{ $ride->rideDetail->drop_address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Ride Cost (Including Tax)</th>
                        <td>{{ $ride->final_amount ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Type</th>
                            @if($ride->payment_type == 1)
                            <td>Cash Payment</td>
                            @else
                            <td>UPI Payment</td>
                            @endif
                        </tr>
                        <tr>
                        <th>Ride Start Date and Time</th>
                        <td>{{ Carbon\Carbon::parse($ride->started_time)->format('d/m/Y, h:i a') }}</td>
                    </tr>
                    <tr style="border: 1px solid #000;">
                        <th>Ride End Date and Time</th>
                        <td >{{ Carbon\Carbon::parse($ride->updated_at)->format('d/m/Y, h:i a') }}</td>
                    </tr>
                    <tr style="border:0; margin-bottom: 300%;">
                        <th style="border:0;"></th>
                        <td style="border:0;"></td>
                     </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  </body>
</html>

