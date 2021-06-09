<p>Thank you for registering for the {{$meetname}}.</p>

<p>Here is a link to <a href="{{$viewEntry}}">view this entry in MSQ Quick Entry</a>. If you have not yet paid, you
can pay at the bottom of the page by clicking Pay Now.</p>

<table border="0">
    <tr>
        <th style="text-align: right;">Meet:</th>
        <td>{{$meetname}}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Meet Date:</th>
        <td>{{$meetDate}}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Entrant Name:</th>
        <td>{{$entrantName}}</td>
    </tr>
    @if ($clubName != '')
        <tr>
            <th style="text-align: right;">Club:</th>
            <td>{{$clubName}}</td>
        </tr>
    @endif
    @if ($mealName != '')
        <tr>
            <th style="text-align: right;">{{$mealName}}:</th>
            <td>{{$mealsOrdered}} tickets</td>
        </tr>
    @endif
</table>

@if (count($items) > 0)
    <p>
    Your purchase from Quick Entry has been processed.
    Below is a summary of the purchase details.
    </p>

    <table border="0">
        <thead>
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Unit Price(inc GST)</th>
            <th>Quantity</th>
            <th>Line Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td style="text-align: center;">
                    {{$item['itemNumber']}}
                </td>
                <td>
                    {{$item['itemName']}}
                </td>
                <td style="text-align: right;">
                    ${{number_format((float)$item['unitPrice'], 2, '.', '')}}
                </td>
                <td style="text-align: center;">
                    {{$item['qty']}}
                </td>
                <td style="text-align: right;">
                    ${{number_format((float)$item['subtotal'], 2, '.', '')}}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" style="text-align: right;">
                GST Included:
            </td>
            <td style="text-align: right;">
                ${{number_format((float)$totalgst, 2, '.', '')}}
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: right;">
                Total:
            </td>
            <td style="text-align: right;">
                ${{number_format((float)$total, 2, '.', '')}}
            </td>
        </tr>
        </tfoot>
    </table>
@endif

<p><strong>Please note:</strong> Details of any Debit/Credit Card payments received will be sent in a
    separate email from Paypal.</p>

<p>
Your entry details:
</p>
<table border="0">
    <thead>
    <tr>
        <th style="text-align: center;">
            Event:
        </th>
        <th style="text-align: left;">
            Event Details:
        </th>
        <th style="text-align: right;">
            Seed Time:
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach ($events as $eventEntry)
    <tr>
        <td style="text-align: center;">
            {{$eventEntry['prognumber']}}
        </td>
        <td>
            {{$eventEntry['details']}}
        </td>
        <td style="text-align: right;">
            {{$eventEntry['seedtime']}}
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
<p>
<i>Unfortunately, this email is an automated notification, which is unable to receive replies. We’re happy to help you with any questions or concerns you may have.
    Please contact us directly at <a href="recorder@mastersswimmingqld.org.au">recorder@mastersswimmingqld.org.au</a>.</i>
</p>