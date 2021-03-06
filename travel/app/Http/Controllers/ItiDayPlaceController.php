<?php namespace App\Http\Controllers;

use App\Experience;
use App\Http\Requests;
use App\Http\Requests\DayPlaces;
use App\Http\Controllers\Controller;
use App\ItiDayPhoto;
use App\ItiDay;
use App\ItiDayPlace;
use App\Itinerary;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItiDayPlaceController extends Controller {


	public function storePlaceImage(Request $request)
	{
		$this->validate($request, [
			'place_image' => 'required|mimes:jpg,jpeg,png'
		]);

		$place = ItiDayPlace::find($request->place_id);
		$itiDay = ItiDay::find($place->itiday_id);
		$itinerary = Itinerary::find($itiDay->itinerary_id);

		$photo = ItiDayPhoto::makePhoto(500, $request->file('place_image'), $itinerary->getRouteKey(), 'place_');

		if($request->place_id == 'new')
		{
			$itiDay->places()->create([
				'image_path' => $photo->photo_path,
				'image_desc' => $photo->name
			]);
		}else{
			$this->deletePlaceImage($request->place_id);
			ItiDayPlace::find($request->place_id)->update([
				'image_path' => $photo->photo_path,
				'image_desc' => $photo->name
			]);
		}
	}

	public function deletePlaceImage($place_id)
	{
		$place = ItiDayPlace::find($place_id);

		$place->image_path = '';
		$place->save();
		if(\File::delete( asset($place->image_path) ))
		{

		}

		return $place;

		//return redirect()->back();
		/*
		if(\File::delete($place->image_path))
		{


			return redirect()->back();
		}else{
			return "error";
		}
*/

	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$day = ItiDay::find($request->day_id);
		$day->places()->create([]);
		return redirect()->back();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(ItiDayPlace $place)
	{
		//return $place;

		return view('itineraryDay.partial_PlaceView')
			->withPlace($place);

	}

	public function getPlaceData(ItiDayPlace $place)
	{
		//used for place image name retrieve. ajax call after place image upload
		return $place;
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(ItiDayPlace $place)
	{
		$transit_methods = [
			'Bicycle' => 'Bicycle',
			'Car' => 'Car',
			'Public transit' => 'Public transit',
			'Walk' => 'Walk',
			'Any'
		];

		$duration = ['less than 1 hour' => 'less than 1 hour',
			'1 - 2 hours' => '1 - 2 hours',
			'2 - 4 hours' => '2 - 4 hours',
			'more than 4 hours' => 'more than 4 hours'];

		return view('itineraryDay.partial_DayEdit')
			->withPlace($place)
			->withDuration($duration)
			->with('transit_methods', $transit_methods);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, ItiDayPlace $place)
	{
		/*$v = $this->placeValidate($request);

		if($v->fails())
		{
			return redirect()->back()->withInput()->withErrors($v->errors());
		}
		*/


		$data = $request->all();



		
		if( $request->experiences != null ) {

			$exp_tags = $this->createTags($data['experiences']);
			$data['experiences'] = implode(',', $exp_tags);

		}else{
			$data['experiences'] = '';
		}
		ItiDayPlace::find($place->id)->update($data);
		
		//using ajax no need for return
		//return redirect()->back();
	}

	public function createTags($tags)
	{
		foreach($tags as $key => $tag)
		{
			if(!is_numeric($tag))
			{
				$new_StyleTag = Experience::firstOrCreate(['experience'=>$tag]);
				$tags[$key] = "$new_StyleTag->id";
			}
		}

		return $tags;
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(ItiDayPlace $place){
		$place->delete();

		//return redirect()->back();
	}

	public function placeValidate($request)
	{
		$v = Validator::make($request->all(), [
			 'place_title' => 'required',
			 'time_to_visit' => 'required',
			 'business_hours' => 'required',
			 'duration' => 'required',
			 'public_transit' => 'required',
			 'experiences' => 'required',
			 'place_intro' => 'required',
			 //'to_do' => 'required',
			 //'tips' => 'required',
		]);

		return $v;
	}

}
