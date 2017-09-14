@if(isset($bookings))
    <form action="{{ route('bookings.update', $bookings->id) }}" method="POST">
@else
    <form action="{{ route('bookings.store') }}" method="PUT">
@endif
{!! csrf_field() !!}
<table class="table">
<input type="hidden" name="id" value="{{ isset($bookings) ? $bookings->id : '' }}">
<tr>
  <td>{{ __('search') }}</td>
  <td><input name="search" value="{{ isset($bookings) ? $bookings->search : '' }}"></td>
</tr>
<tr>
  <td>{{ __('ref_iban') }}</td>
  <td><input name="ref_iban" value="{{ isset($bookings) ? $bookings->ref_iban : '' }}"></td>
</tr>
<tr>
  <td>{{ __('bookingdate') }}</td>
  <td><input name="bookingdate" value="{{ isset($bookings) ? $bookings->bookingdate : '' }}"></td>
</tr>
<tr>
  <td>{{ __('valutadate') }}</td>
  <td><input name="valutadate" value="{{ isset($bookings) ? $bookings->valutadate : '' }}"></td>
</tr>
<tr>
  <td>{{ __('amount') }}</td>
  <td><input name="amount" value="{{ isset($bookings) ? $bookings->amount : '' }}"></td>
</tr>
<tr>
  <td>{{ __('creditdebit') }}</td>
  <td><input name="creditdebit" value="{{ isset($bookings) ? $bookings->creditdebit : '' }}"></td>
</tr>
<tr>
  <td>{{ __('bookingtext') }}</td>
  <td><input name="bookingtext" value="{{ isset($bookings) ? $bookings->bookingtext : '' }}"></td>
</tr>
<tr>
  <td>{{ __('description1') }}</td>
  <td><input name="description1" value="{{ isset($bookings) ? $bookings->description1 : '' }}"></td>
</tr>
<tr>
  <td>{{ __('structureddescription') }}</td>
  <td><input name="structureddescription" value="{{ isset($bookings) ? $bookings->structureddescription : '' }}"></td>
</tr>
<tr>
  <td>{{ __('bankcode') }}</td>
  <td><input name="bankcode" value="{{ isset($bookings) ? $bookings->bankcode : '' }}"></td>
</tr>
<tr>
  <td>{{ __('accountnumber') }}</td>
  <td><input name="accountnumber" value="{{ isset($bookings) ? $bookings->accountnumber : '' }}"></td>
</tr>
<tr>
  <td>{{ __('name') }}</td>
  <td><input name="name" value="{{ isset($bookings) ? $bookings->name : '' }}"></td>
</tr>
</table>
{!! button(__('Save'), 'submit', 'primary') !!}
</form>
