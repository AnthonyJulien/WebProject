<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Rating;

class RatingController extends Controller
{
   
    public function ratings() {
       

        $ratings = Rating::with(['user', 'product'])->get()->toArray(); 



        return view('admin.ratings.ratings')->with(compact('ratings'));
    }

       
    public function updateRatingStatus(Request $request) {
        if ($request->ajax()) { 
            $data = $request->all(); 
            

            if ($data['status'] == 'Active') { 
                $status = 0;
            } else {
                $status = 1;
            }


            Rating::where('id', $data['rating_id'])->update(['status' => $status]); 
            

            return response()->json([ 
                'status'    => $status,
                'rating_id' => $data['rating_id']
            ]);
        }
    }

        
    public function deleteRating($id) { 
        Rating::where('id', $id)->delete();

        $message = 'Rating has been deleted successfully!';
        

        return redirect()->back()->with('success_message', $message);
    }

}