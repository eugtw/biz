<?php namespace App\Http\Controllers;

use App\Experience;
use App\ItiDayPhoto;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDayCreateRequest;
use Illuminate\Support\Facades\Crypt;
use App\Itinerary;
use App\ItiDay;
use Illuminate\Database\Eloquent;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ItiDayController extends Controller {

	public function __construct()
	{
		parent:: __construct();
	}

	public function destroy(ItiDay $day)
	{
		$day->delete();
	}

	public function edit(ItiDay $day)
	{
		//$exp = Experience::all()->sortBy('experience')->lists('experience','id');
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

		$itinerary = Itinerary::find($day->itinerary_id);
		return view('itineraryDay.dayEdit')
			//->withExperiences($exp)
			->withDay($day)
			->withDuration($duration)
			->with('transit_methods', $transit_methods)
			->withItinerary($itinerary);
	}

	public function update(StoreDayCreateRequest $request,ItiDay $day)
	{
		$itinerary = Itinerary::find($day->itinerary->id);

		$day->update($request->all());

		$day->itinerary()->associate($itinerary);

		//return redirect()->back();
		//return redirect()->route('itinerary.show', [$itinerary]);
	}

	public function create(Request $request)
	{
		$iti_id = Crypt::decrypt($request->input('iti_id'));
		$itinerary = Itinerary::find($iti_id);

		return view('itineraryDay.dayCreate')
			//->withExp($exp)
			->withItinerary($itinerary);

	}

	public function store(StoreDayCreateRequest $request)
	{
		//passed from hidden input
		$iti_id = Crypt::decrypt($request->input('iti_id'));
		$itinerary = Itinerary::find($iti_id);

		$day = new ItiDay([
			'day_num' => $itinerary->days()->count() + 1,
			'title' => $request->title,
			//'image'=>preg_replace('|\\\|', '/', $request->input('image')),
			//'budget' => $request->budget,
			//'day_cities' => $request->day_cities,
			//'places'=> $request->places,
			//'top_exp' => implode(',', $request->experience), //stores experience_ids from experience table
			'intro'=>$request->intro,
			//'summary' => $request->summary
		]);


		$itinerary->days()->save($day);

		//create 1st place for Day after Day created
		$day->places()->create([]);

		return redirect()->route('itinerary-day.edit', [$day]);
		//return redirect()->route('itinerary.show', [$itinerary]);
	}

	public function storeDayImages(Request $request)
	{
		$this->validate($request, [
			'image' => 'required|mimes:jpg,jpeg,png'
		]);


		$itiDay = ItiDay::find($request->day_id);
		$itinerary = Itinerary::find($itiDay->itinerary_id);

		$photo = ItiDayPhoto::makePhoto(1000, $request->file('image'), $itinerary->getRouteKey());



		//$photo = ItiDayPhoto::fromForm($request->file('image'), $request->day_id);//name 'file' defined in dropzone javascript init

		ItiDay::find($request->day_id)->addPhoto($photo);

		return $photo;

	}

	public function deleteDayImages($photo_name) //
	{


		$photoDB = ItiDayPhoto::where('name', $photo_name)->first();
		//$photo = ItiDayphoto::where('photo_path', $photo_path)->first();

		if($photoDB)
		{
			$photoDB->delete();
			if(\File::delete($photoDB->photo_path))
			{


			}

			return redirect()->back();
		}else{
			return false;
		}


	}


}


