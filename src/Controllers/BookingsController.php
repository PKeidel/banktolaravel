<?php

namespace PKeidel\BankToLaravel\Controllers;

use App\Http\Controllers\Controller;
use PKeidel\BankToLaravel\Models\Bookings;

class BookingsController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view("banktolaravel::bookings.list", ['bookings' => Bookings::orderBy('bookingdate', 'DESC')->get()]);
    }

//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function create() {
//        return view("banktolaravel::bookings.edit");
//    }
//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\Response
//     */
//    public function store(Request $request) {
//        $data = $this->validate($request, [
//            'ref_iban' => '',
//            'search' => '',
//            'bookingdate' => '',
//            'valutadate' => '',
//            'amount' => '',
//            'creditdebit' => '',
//            'bookingtext' => '',
//            'description1' => '',
//            'structureddescription' => '',
//            'bankcode' => '',
//            'accountnumber' => '',
//            'name' => '',
//        ]);
//
//        Bookings::create($data);
//
//        return redirect(route("banktolaravel::bookings.index"));
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  Bookings $booking
//     * @return \Illuminate\Http\Response
//     */
//    public function show(Bookings $booking) {
//        return view("banktolaravel::bookings.view", ["bookings" => $booking]);
//    }
//
//    /**
//     * Show the form for editing the specified resource.
//     *
//     * @param  Bookings $booking
//     * @return \Illuminate\Http\Response
//     */
//    public function edit(Bookings $booking) {
//        return view("banktolaravel::bookings.edit", ["bookings" => $booking]);
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function update(Request $request, $id) {}
//
//	/**
//	 * Remove the specified resource from storage.
//	 *
//	 * @param  Bookings $booking
//	 *
//	 * @return \Illuminate\Http\Response
//	 * @throws \Exception
//	 */
//    public function destroy(Bookings $booking) {
//        $booking->delete();
//        return redirect(route("bookings.index"));
//    }
}