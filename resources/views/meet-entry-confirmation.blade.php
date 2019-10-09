<?php use App\Mail\MeetEntryConfirmation; ?>
<h1>Meet Entry Confirmation</h1>
<p>Thank you for your entry.</p>
<p>
    <strong>Date: </strong> {{$entry->created_at}}<br />
    <strong>Club: </strong> {{$entry->club->clubname}} ({{$entry->club->code}})<br />
    <strong>Entry Status: </strong> {{$entry->status->status->label}}: {{$entry->status->status->description}}<br />
    <strong>Entry Access Code: </strong>
    <a href="{{env('SITE_BASE')}}/entry-confirmation/{{$entry->code}}">{{$entry->code}}</a><br />
</p>

<p>
    <strong>Events:</strong>
</p>
<table border="1">
    <thead>
    <tr>
        <th>
            No:
        </th>
        <th>
            Event:
        </th>
        <th>
            Type:
        </th>
        <th>
            Nominated Time:
        </th>
        <th>
            Status:
        </th>
    </tr>
    </thead>
    <tbody>
        @foreach ($entry->events as $event)
            <tr>
                <td>
                    {{$event->event->prognumber}}{{$event->event->progsuffix}}
                </td>
                <td>
                    {{$event->event->eventDistance->distance}} {{$event->event->eventDiscipline->discipline}}
                </td>
                <td>
                    {{$event->event->eventType->typename}}
                </td>
                <td>
                    {{MeetEntryConfirmation::convertSeedTime($event->seedtime)}}
                </td>
                <td>
                    @if ($event->cancelled)
                        Cancelled
                    @elseif ($event->scratched)
                        Scratched
                    @else
                        Entered
                    @endif
                </td>
            </tr>
        @endforeach

    </tbody>
</table>

<p>If you have made a payment online you will receive a separate
receipt email for this payment.</p>

<p>You can view your entry online at
<a href="{{env('SITE_BASE')}}/entry-confirmation/{{$entry->code}}">
    {{env('SITE_BASE')}}/entry-confirmation/{{$entry->code}}
</a>
</p>

<p>Thank you for your entry. If you require any assistance feel free to reply
to this email or email <a href=mailto:"recorder@mastersswimmingqld.org.au">
recorder@mastersswimmingqld.org.au</a>.</p>