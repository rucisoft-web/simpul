<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function faqs() { return $this->hasMany(Faq::class); }
}
