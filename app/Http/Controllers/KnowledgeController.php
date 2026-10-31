<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\KnownError;
use App\Support\Scope;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->query('q');
        $faqs = Faq::with(['category', 'organization'])
            ->when(Scope::rootId(), fn ($q) => $q->where(fn ($w) => $w->whereNull('organization_id')->orWhere('organization_id', Scope::rootId())))
            ->when($term, fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', "%$term%")->orWhere('keywords', 'like', "%$term%")))
            ->orderByDesc('views')->get();
        $knownErrors = Scope::apply(KnownError::query(), 'organization_id', false)->with(['organization', 'problem'])
            ->when($term, fn ($q) => $q->where('name', 'like', "%$term%"))->latest()->get();
        $documents = Scope::apply(Document::query(), 'organization_id', false)->with('organization')->get();
        $categories = FaqCategory::withCount('faqs')->get();

        return view('knowledge.index', compact('faqs', 'knownErrors', 'documents', 'categories', 'term'));
    }

    public function faq(Faq $faq)
    {
        $faq->increment('views');

        return view('knowledge.article', ['title' => $faq->title, 'kind' => 'FAQ · '.$faq->category->name, 'sections' => [
            'Ringkasan' => $faq->summary, 'Langkah penyelesaian' => $faq->description, 'Kata kunci' => $faq->keywords,
        ], 'meta' => number_format($faq->views).' kali dilihat']);
    }

    public function knownError(KnownError $knownError)
    {
        $ke = $knownError->load('problem', 'organization');

        return view('knowledge.article', ['title' => $ke->name, 'kind' => 'Known Error · '.$ke->domain.' · '.$ke->organization->name, 'sections' => [
            'Gejala' => $ke->symptom, 'Kode error' => $ke->error_code, 'Akar masalah' => $ke->root_cause, 'Workaround' => $ke->workaround, 'Solusi permanen' => $ke->solution ?? 'Belum tersedia — problem masih dianalisa.',
        ], 'meta' => $ke->problem ? 'Problem terkait: '.$ke->problem->ref : null, 'problem' => $ke->problem]);
    }
}
