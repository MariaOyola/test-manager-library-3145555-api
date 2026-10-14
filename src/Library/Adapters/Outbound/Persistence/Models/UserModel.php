<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class UserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.user';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = ['password_hash'];
    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(LoanModel::class, 'loaned_by_user_id'); }
}
