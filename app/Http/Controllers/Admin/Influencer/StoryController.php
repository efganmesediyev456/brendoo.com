<?php

namespace App\Http\Controllers\Admin\Influencer;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Story;
use App\Models\Influencer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class StoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:list-stories|create-stories|edit-stories|delete-stories', ['only' => ['index','show']]);
        $this->middleware('permission:create-stories', ['only' => ['create','store']]);
        $this->middleware('permission:edit-stories', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-stories', ['only' => ['destroy']]);
    }

    public function index()
    {
        $stories = null;
        if(request()->has('influencer')){
             $stories = Story::with('influencer')->where('influencer_id',request()->influencer)->paginate(10);
        }else{
            $stories = Story::with('influencer')->paginate(10);
        }
        return view('influencers.stories.index', compact('stories'));
    }

    public function create()
    {
        $influencers = Influencer::all();
        return view('influencers.stories.create', compact('influencers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required|exists:influencers,id',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'mimetypes:video/mp4,video/avi,video/mpeg|max:20480'
        ]);

        $story = new Story();
        $story->title = $request->title;
        $story->description = $request->description;
        $story->influencer_id = $request->influencer_id;

        

        


        $story->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('stories/images', 'public');
                
                Media::create([
                    'story_id' => $story->id,
                    'type' => 'image',
                    'file_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'is_primary' => $index === 0 
                ]);
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $index => $video) {
                $videoPath = $video->store('stories/videos', 'public');
                
                Media::create([
                    'story_id' => $story->id,
                    'type' => 'video',
                    'file_path' => $videoPath,
                    'file_name' => $video->getClientOriginalName(),
                    'mime_type' => $video->getMimeType(),
                    'file_size' => $video->getSize(),
                    'is_primary' => $index === 0 
                ]);
            }
        }

        $story->translateOrNew('en')->title = $request->en_title;
        $story->translateOrNew('en')->description = $request->en_description;
        $story->translateOrNew('ru')->title = $request->ru_title;
        $story->translateOrNew('ru')->description = $request->ru_description;
        
        $story->save();

        return redirect()->route('influencers.stories.index', ['influencer'=>$request->influencer_id])->with('message', 'Story uğurla əlavə edildi');
    }

    public function edit(Story $story)
    {
        $influencers = Influencer::all();
        return view('influencers.stories.edit', compact('story', 'influencers'));
    }

    public function update(Request $request, Story $story)
    {
        $request->validate([
            'influencer_id' => 'required|exists:influencers,id',
            'video' => 'mimetypes:video/mp4,video/avi,video/mpeg|max:20480'
        ]);

        $story->title = $request->title;
        $story->description = $request->description;
        $story->influencer_id = $request->influencer_id;

        

       

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('stories/images', 'public');
                
                Media::create([
                    'story_id' => $story->id,
                    'type' => 'image',
                    'file_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'is_primary' => $index === 0
                ]);
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $index => $video) {
                $videoPath = $video->store('stories/videos', 'public');
                
                Media::create([
                    'story_id' => $story->id,
                    'type' => 'video',
                    'file_path' => $videoPath,
                    'file_name' => $video->getClientOriginalName(),
                    'mime_type' => $video->getMimeType(),
                    'file_size' => $video->getSize(),
                    'is_primary' => $index === 0
                ]);
            }
        }

        

        $story->translateOrNew('en')->title = $request->en_title;
        $story->translateOrNew('en')->description = $request->en_description;
        $story->translateOrNew('ru')->title = $request->ru_title;
        $story->translateOrNew('ru')->description = $request->ru_description;
        
        $story->save();

        return redirect()->route('influencers.stories.index', ['influencer'=>$request->influencer_id])->with('message', 'Story uğurla yeniləndi');
    }

    public function destroy(Story $story)
    {
        $media = $story->media;
        $returnd_id = $story->influencer_id;
        foreach ($media as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }

        $story->delete();

        return redirect()->route('influencers.stories.index', ['influencer'=>$returnd_id])->with('message', 'Story uğurla silindi');
    }


    public function deleteMedia($mediaId)
    {
        try {
            $media = Media::findOrFail($mediaId);
            
            
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

        
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Media elementi uğurla silindi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xəta baş verdi: ' . $e->getMessage()
            ], 500);
        }
    }

}
