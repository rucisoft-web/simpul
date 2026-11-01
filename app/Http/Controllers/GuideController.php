<?php

namespace App\Http\Controllers;

use App\Support\Guide;
use Illuminate\Support\Str;

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

    /** Panduan implementasi Simpul di organisasi pelanggan (sumber: resources/docs/implementasi.md). */
    public function implementation()
    {
        $html = Str::markdown(file_get_contents(resource_path('docs/implementasi.md')), ['html_input' => 'strip']);

        // Beri id pada setiap judul bagian (h2) untuk daftar isi.
        $toc = [];
        $html = preg_replace_callback('/<h2>(.*?)<\/h2>/', function ($m) use (&$toc) {
            $title = html_entity_decode(strip_tags($m[1]));
            $id = Str::slug($title);
            $toc[] = ['id' => $id, 'title' => $title];

            return '<h2 id="'.$id.'">'.$m[1].'</h2>';
        }, $html);

        return view('implementation', ['html' => $html, 'toc' => $toc]);
    }
}
