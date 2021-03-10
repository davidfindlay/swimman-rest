Thank you for registering for the {{meetName}}.

@if (count($items) > 0)
    Your purchase from Quick Entry has been processed.
    Below is a summary of the purchase details.

    <table border="0" width="100%">
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
            <tr>
                <td>
                    {{itemNumber}}
                </td>
                <td>
                    {{itemName}}
                </td>
                <td>
                    {{unitPrice}}
                </td>
                <td>
                    {{qty}}
                </td>
                <td>
                    {{subtotal}}
                </td>
            </tr>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4">

            </td>
            <td>

            </td>
        </tr>
        </tfoot>
    </table>
@endif

Your entry details:

<table border="0" width="100%">
    <thead>
    <tr>
        <th>
            Event:
        </th>
        <th>
            Event Details:
        </th>
        <th>
            Seed Time:
        </th>
    </tr>
    </thead>
</table>

<i>Unfortunately, this email is an automated notification, which is unable to receive replies. We’re happy to help you with any questions or concerns you may have.
    Please contact us directly at <a href="recorder@mastersswimmingqld.org.au">recorder@mastersswimmingqld.org.au</a>.</i>