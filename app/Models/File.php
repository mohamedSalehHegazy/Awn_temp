<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_name',
        'fileable_type',
        'fileable_id',
        'path',
        'notes',
        'label',
        'order',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getIsActiveAttribute($value)
    {
        return $value == 1 ? true : false;
    }

    public function getFileNameAttribute($value)
    {
        return env('APP_URL') . 'storage/app/public/' . $this->path . '/' . $value;
    }

    public function fileable()
    {
        return $this->morphTo();
    }
}
