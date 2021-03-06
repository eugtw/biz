<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Itinerary;
use App\Presignup;
use App\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PromoController extends Controller {

	/*public function getItiExample()
	{
		return view('promo.promo-screenshot');
	}*/

	public function getPromoPage()
	{
		if(!$itiEx = Itinerary::find(env('DEMO_ITIT_ID')))
		{
			$itiEx = Itinerary::where('published', 1)->first();
		}

		return view('promo.promo-main')->with('itinerary', $itiEx);
	}

	public function postNotifyMe(Request $request)
	{
		$email = $request->email;
		$url = url(route('promo.getContactUs'));

		Presignup::firstOrCreate([
			'email' => $email
		]);

		Mail::send('emails.promo_signup', ['url' => $url], function ($m) use ($email, $url) {
			$m->to($email)->subject('Welcome to TripPlanShop.com');
		});

		return redirect('promo')->withMessage('You are now in the loop! Exciting!!');
	}

	public function postContactUs(Request $request)
	{
		$name = $request->name;
		$email = $request->email;
		$content = $request->message;

		Mail::send('emails.contactus', ['name'=>$name,'email'=>$email,'content'=>$content],function($message) use($name,$email,$content)
		{
			$message->from($email);
			$message->to(env('MAIL_ADMIN'))->subject('New Seller Request');
		});

		return redirect('promo')->withMessage('Thank you. We will respond to your email shortly.');
	}

}
