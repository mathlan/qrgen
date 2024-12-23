<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Affiche le menu public d'un restaurant spécifique
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->with(['categories' => function($query) {
                $query->orderBy('name');
            }, 'categories.recipes' => function($query) {
                $query->orderBy('name');
            }, 'schedules'])
            ->firstOrFail();

        $menuData = [
            'restaurant' => [
                'name' => $restaurant->name,
                'description' => $restaurant->description,
                'photo' => $restaurant->photo,
                'schedules' => $restaurant->schedules->map(function($schedule) {
                    return [
                        'day' => $schedule->day,
                        'opening_time' => substr($schedule->opening_time, 0, 5),
                        'closing_time' => substr($schedule->closing_time, 0, 5)
                    ];
                })
            ],
            'categories' => $restaurant->categories->map(function($category) {
                return [
                    'name' => $category->name,
                    'recipes' => $category->recipes->map(function($recipe) {
                        return [
                            'name' => $recipe->name,
                            'description' => $recipe->description,
                            'price' => $recipe->price,
                            'photo' => $recipe->photo,
                            'ingredients' => $recipe->ingredients ?? []
                        ];
                    })
                ];
            })
        ];

        return response()->json($menuData);
    }

    /**
     * Génère le QR code pour un restaurant
     *
     * @param Restaurant $restaurant
     * @return \Illuminate\Http\Response
     */
    public function generateQr(Restaurant $restaurant)
    {
        try {
            $url = config('app.url') . "/qr/" . $restaurant->slug;

            $qrCode = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $restaurant->slug . '-qr.png"');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la génération du QR code'], 500);
        }
    }
}
