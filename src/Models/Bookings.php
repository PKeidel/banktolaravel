<?php

namespace PKeidel\BankToLaravel\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Bookings
 *
 * @property int id
 * @property string ref_iban
 * @property Carbon bookingdate
 * @property Carbon valutadate
 * @property float amount
 * @property string creditdebit
 * @property string bookingtext
 * @property string description1
 * @property string structureddescription
 * @property string bankcode
 * @property string accountnumber
 * @property string name
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
*/
class Bookings extends Model {
    use SoftDeletes;

    protected $table    = 'bookings';
    protected $fillable = ['ref_iban','bookingdate','valutadate','amount','creditdebit','bookingtext','description1','structureddescription','bankcode','accountnumber','name'];
    protected $dates    = ['bookingdate','valutadate','created_at','updated_at','deleted_at'];
    protected $casts    = ['structureddescription' => 'array'];
}
