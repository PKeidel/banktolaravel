<table class="table" border>
<tr>
  <td>{{ __('id') }}</td>
  <td>{{ __('ref_iban') }}</td>
  <td>{{ __('name') }}</td>
  <td>{{ __('search') }}</td>
  <td>{{ __('bookingdate') }}</td>
  <td>{{ __('valutadate') }}</td>
  <td>{{ __('amount') }}</td>
{{--  <td>{{ __('creditdebit') }}</td>--}}
  <td>{{ __('bookingtext') }}</td>
  <td>{{ __('svwz') }}</td>
{{--  <td>{{ __('structureddescription') }}</td>--}}
  <td>{{ __('bankcode') }}</td>
  <td>{{ __('accountnumber') }}</td>
  <td>{{ __('created_at') }}</td>
  <td>{{ __('updated_at') }}</td>
  <td>{{ __('deleted_at') }}</td>
</tr>
@set($sumIn, 0)
@set($sumOut, 0)
@set($month, '')
@foreach($bookings as $b)
  @if(!$loop->first && $month != $b->bookingdate->format('m/Y'))
      <tr style="color:@if($sumOut > $sumIn)red @else green @endif">
        <td colspan="2">summe des Zeitraums: {{ $month }}</td>
        <td colspan="12">In: {{ $sumIn }}<br>Out: {{ $sumOut }}</td>
      </tr>
      @set($sumIn, 0)
      @set($sumOut, 0)
  @endif

  @if($b->creditdebit == 'credit')
    @set($sumIn, $sumIn + $b->amount)
  @else
    @set($sumOut, $sumOut + $b->amount)
  @endif

  @set($month, $b->bookingdate->format('m/Y'))
  <tr>
  <td>{{ $b->id }}</td>
  <td>{{ $b->ref_iban }}</td>
  <td>{{ $b->name }}</td>
  <td><a href="{{ route('bookings.show', ['booking' => $b->id]) }}">{{ $b->search }}</a></td>
  <td>{{ $b->bookingdate->format('d.m.Y') }}</td>
  <td>{{ $b->valutadate->format('d.m.Y') }}</td>
  <td>{{ $b->amount }}€</td>
{{--  <td>{{ $b->creditdebit }}</td>--}}
  <td>{{ $b->bookingtext }}</td>
  <td>{{ isset($b->structureddescription) && !empty($b->structureddescription['SVWZ']) ? $b->structureddescription['SVWZ'] : $b->description }}</td>
{{--  <td>{{ json_encode($b->structureddescription) }}</td>--}}
  <td>{{ $b->bankcode }}</td>
  <td>{{ $b->accountnumber }}</td>
  <td>{{ $b->created_at }}</td>
  <td>{{ $b->updated_at }}</td>
  <td>{{ $b->deleted_at }}</td>
  </tr>
@endforeach
</table>
