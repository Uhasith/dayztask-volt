<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dayztasks - Day Ending Update</title>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 680px;
            height: auto;
            margin: 20px auto;
        }

        .header {
            background: #E1F29680;
            padding: 16px 80px;
            text-align: center;
        }

        .header img {
            width: 144px;
            height: 24px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: black;
            margin: 10px 0 5px 0;
        }

        .header p {
            color: black;
            font-size: 12px;
            margin: 0;
        }

        .content {
            padding: 24px;
            color: black;
        }

        .content p {
            font-size: 14px;
            margin: 0;
            color: black
        }

        .button {
            background: #5BB98A;
            border-radius: 20px;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            margin: 20px auto;
            text-decoration: none;
        }

        .button-container {
            max-width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .content h2 {
            font-size: 24px;
            color: black;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #343A3F;
        }

        .social-links img {
            height: 24px;
            margin: 0 5px;
        }

        .sub-content p {
            color: #343A3F;
            font-size: 14px
        }

        .mt-4 {
            margin-top: 25px;
        }
        p{
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            {{-- <img src="{{asset('images/logo.png')}}" alt="logo"> --}}
            <h1>Hi, {{$data['team']->owner->name}}, your {{$data['team']->name}} member {{$user->name}} has checked out
                for the day!</h1>
            <p>Collaborate, Organize, and Achieve Together</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello {{$data['team']->owner->name}},</h2>
            <p>We wanted to let you know that <b>{{$user->name}}</b> checked out for today <b>{{
                    Carbon\Carbon::parse($data['checkout'])->format('m-d') }} at
                    {{Carbon\Carbon::parse($data['checkout'])->format('H:i')}}</b>. His/Her checkout remark for today as
                follows:</p>
            <div style="margin-top: 25px; padding: 10px; background: #F2F4F8; border-radius: 5px;">
                {!! $data['update'] !!}
            </div>

            <p class="mt-4">Here are the main statistics for you.</p>
            <div class="mt-4" style="padding: 10px; background: #F2F4F8; border-radius: 5px;">
                <p><b>Location: {{$data['location']}}</b> </p>
                <p>Check in time: {{Carbon\Carbon::parse($data['checkin'])->format('H:i')}}, Checkout time:
                    {{Carbon\Carbon::parse($data['checkout'])->format('H:i')}}</p>
                <p><b>Tasks Completed: {{$data['completed_count']}}</b> </p>
                <p><b>Tasks Pending: {{$data['pending_count']}}</b> </p>
                <p><b>Tasks Overdue: {{$data['overdue_count']}}</b> </p>
                <p><b>Screenshots taken: {{$data['screenshots_count']}}</b> </p>
            </div>

            @if ($trackings->count() > 0)
            <p class="mb-4 mt-4">Here is a list of the tasks {{$user->name}} worked on today:</p>
            <table>
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Task</th>
                        <th>Tracking</th>
                        <th>Task Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trackings as $tracking)
                    <tr>
                        <td>{{$tracking->task->project->title}}</td>
                        <td>{{$tracking->task->name}}</td>
                        <td>{{Carbon\CarbonInterval::seconds($tracking->total_tracking_time)->cascade()->forHumans()}}
                        </td>
                        <td>{{$tracking->task->status}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <div class="sub-content mt-4">
                <p>If you have any questions or need assistance, feel free to reach out. We're here to help make your
                    Dayztasks experience seamless and productive!</p>
                <p style="margin-top: 45px">Best Regards,</p>
                <p style="font-weight: 600; margin-top:4px">Team DayzTasks</p>
            </div>
        </div>
        <!-- Footer Links -->
        <div class="footer mt-4">
            <p>&copy; info@dayzsolutions.com. All rights reserved.</p>
        </div>
    </div>
</body>

</html>