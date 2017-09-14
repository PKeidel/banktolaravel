<table class="table table-bordered table-hover">
<tr>
  <td>{{ __('id') }}</td>
  <td>{{ $bookings->id }}</td>
</tr>
<tr>
  <td>{{ __('ref_iban') }}</td>
  <td>{{ $bookings->ref_iban }}</td>
</tr>
<tr>
  <td>{{ __('search') }}</td>
  <td>{{ $bookings->search }}</td>
</tr>
<tr>
  <td>{{ __('bookingdate') }}</td>
  <td>{{ $bookings->bookingdate }}</td>
</tr>
<tr>
  <td>{{ __('valutadate') }}</td>
  <td>{{ $bookings->valutadate }}</td>
</tr>
<tr>
  <td>{{ __('amount') }}</td>
  <td>{{ $bookings->amount }}</td>
</tr>
<tr>
  <td>{{ __('creditdebit') }}</td>
  <td>{{ $bookings->creditdebit }}</td>
</tr>
<tr>
  <td>{{ __('bookingtext') }}</td>
  <td>{{ $bookings->bookingtext }}</td>
</tr>
<tr>
  <td>{{ __('description1') }}</td>
  <td>{{ $bookings->description1 }}</td>
</tr>
<tr>
  <td>{{ __('structureddescription') }}</td>
  <td>{{ json_encode($bookings->structureddescription, JSON_PRETTY_PRINT) }}</td>
</tr>
<tr>
  <td>{{ __('bankcode') }}</td>
  <td>{{ $bookings->bankcode }}</td>
</tr>
<tr>
  <td>{{ __('accountnumber') }}</td>
  <td>{{ $bookings->accountnumber }}</td>
</tr>
<tr>
  <td>{{ __('name') }}</td>
  <td>{{ $bookings->name }}</td>
</tr>
<tr>
  <td>{{ __('created_at') }}</td>
  <td>{{ $bookings->created_at }}</td>
</tr>
<tr>
  <td>{{ __('updated_at') }}</td>
  <td>{{ $bookings->updated_at }}</td>
</tr>
<tr>
  <td>{{ __('deleted_at') }}</td>
  <td>{{ $bookings->deleted_at }}</td>
</tr>
</table>
