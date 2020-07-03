<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    // protected $guarded = [];
    protected $fillables = ['user_id', 'title', 'description', 'tags', 'files'];
    public function getTagsAttribute($value)
    {
        $tags = [];
        if (!empty($value)) {
            $tags = explode(',', $value);
        }
        return $tags;
    }

    /**
     * getFilesAttribute
     *
     * @param  mixed $value
     * @return mixed $value
     */
    public function getFilesAttribute($value)
    {
        $files = [];
        if (!empty($value)) {
            $files = explode(',', $value);
        }
        foreach ($files as $key => $file) {
            $files[$key] = env('FILE_BASE_PATH') . $file;
        }
        return $files;
    }

    /**
     * client
     * Project User Relationship
     * @return void
     */
    public function client()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
