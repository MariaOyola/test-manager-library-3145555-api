<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class MemberModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.member';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(LoanModel::class, 'member_id'); }
}
