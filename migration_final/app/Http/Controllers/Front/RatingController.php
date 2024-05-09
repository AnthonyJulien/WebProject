<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Rating;


class RatingController extends Controller
{
   
    public function addRating(Request $request) {
       
        if (!Auth::check()) { 
            $message = 'Log in to rate this product';
            return redirect()->back()->with('error_message', $message);
        }

        if ($request->isMethod('post')) {
            $data = $request->all();

            $user_id = Auth::user()->id; 
            $ratingCount = Rating::where([
                'user_id'    => $user_id,
                'product_id' => $data['product_id']
            ])->count();

            if ($ratingCount > 0) {
                $message = 'You\'ve already rated this product before!';
                return redirect()->back()->with('error_message', $message);
            } else { 

                if (empty($data['rating'])) {
                    $message = 'Please click on a star to rate the product!';
                    return redirect()->back()->with('error_message', $message);
                } else {

                    $rating = new Rating();

                    $rating->user_id    = $user_id;
                    $rating->product_id = $data['product_id'];
                    $rating->review     = $data['review'];
                    $rating->rating     = $data['rating'];
                    $rating->status     = 0;

                    $rating->save();

                    $message = 'Thanks for rating the product! It\'ll be shown after being approved by an admin!';
                    return redirect()->back()->with('success_message', $message);
                }
            }
        }
    }

}