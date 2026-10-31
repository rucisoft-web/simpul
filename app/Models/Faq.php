<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $guarded = [];

    public function category() { return $this->belongsTo(FaqCategory::class, 'faq_category_id'); }
    public function organization() { return $this->belongsTo(Organization::class); }
}
