<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class CategoryModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.category';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    public function books(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(BookModel::class, 'category_id'); }
}
