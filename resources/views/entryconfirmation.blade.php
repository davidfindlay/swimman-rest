Thank you for registering for the {{$entry->meet->meetname}}.

@if (count($items) > 0)
    Your purchase from Quick Entry has been processed.
    Below is a summary of the purchase details.

    <table border="0" width="100%">
        <thead>
        <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Unit Price(ex GST)</th>
            <th>Quantity</th>
            <th>Line Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td>
                    {{$item->itemNumber}}
                </td>
                <td>
                    {{$item->itemName}}
                </td>
                <td>
                    {{$item->unitPrice}}
                </td>
                <td>
                    {{$item->qty}}
                </td>
                <td>
                    {{$item->subtotal}}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4">
                Total:
            </td>
            <td>
                {{$total}}
            </td>
        </tr>
        </tfoot>
    </table>
@endif
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