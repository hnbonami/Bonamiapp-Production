<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bikefit;
use App\Models\BikefitImage;

class BikefitImageController extends Controller
{
    public function store(Request $request, Bikefit $bikefit)
    {
        $data = $request->validate([
            'path' => 'required|string',
            'caption' => 'nullable|string',
            'is_cover' => 'nullable|boolean',
            'position' => 'nullable|integer',
        ]);

        // if marking as cover, unset previous covers
        if (!empty($data['is_cover'])) {
            $bikefit->images()->update(['is_cover' => false]);
        }

        $img = $bikefit->images()->create([
            'path' => $data['path'],
            'caption' => $data['caption'] ?? null,
            'position' => $data['position'] ?? ($bikefit->images()->count()),
            'is_cover' => !empty($data['is_cover']) ? 1 : 0,
        ]);

        return response()->json(['ok' => true, 'image' => $img]);
    }

    public function update(Request $request, Bikefit $bikefit, BikefitImage $image)
    {
        if ($image->bikefit_id !== $bikefit->id) {
            return response()->json(['ok' => false], 403);
        }
        $data = $request->validate([
            'caption' => 'nullable|string',
            'position' => 'nullable|integer',
            'is_cover' => 'nullable|boolean',
        ]);
        if (isset($data['is_cover']) && $data['is_cover']) {
            $bikefit->images()->update(['is_cover' => false]);
            $image->is_cover = 1;
        }
        if (isset($data['caption'])) $image->caption = $data['caption'];

        // Handle position reordering: if position provided, reorder all images
        if (isset($data['position'])) {
            $newPos = max(0, (int)$data['position']);
            $images = $bikefit->images()->orderBy('position')->get()->filter(function($i) use ($image){ return $i->id !== $image->id; })->values();
            // insert the image in the desired slot
            $images->splice($newPos, 0, [$image]);
            // reassign positions
            foreach ($images as $idx => $img) {
                $img->position = $idx;
                // if this is the current image instance, update the $image object instead of saving duplicate
                if ($img->id === $image->id) {
                    $image->position = $idx;
                } else {
                    $img->save();
                }
            }
        }
        $image->save();
        return response()->json(['ok' => true, 'image' => $image]);
    }

    public function destroy(Bikefit $bikefit, BikefitImage $image)
    {
        // ensure the image belongs to the bikefit
        if ($image->bikefit_id !== $bikefit->id) {
            return response()->json(['ok' => false], 403);
        }
        $image->delete();
        return response()->json(['ok' => true]);
    }
}
