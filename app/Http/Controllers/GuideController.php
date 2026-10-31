<?php

namespace App\Http\Controllers;

use App\Support\Guide;

class GuideController extends Controller
{
    public function index()
    {
        return view('guide.index', ['groups' => Guide::groups(), 'items' => Guide::all()]);
    }

    /** Potongan HTML satu bagian panduan, dimuat ke panel samping oleh tombol Panduan. */
    public function panel(string $key)
    {
        $item = Guide::get($key);
        abort_unless($item, 404);

        return view('guide.section', ['key' => $key, 'item' => $item, 'compact' => true]);
    }
}
