<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Vote extends Model
{
    protected $fillable = [
        'genre_id', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function create(array $attributes = [])
    {
        Cache::forget('current-rate');

        parent::create($attributes);
    }

    public static function getCurrentRate()
    {
        $results = [];

        if (Cache::has('current-rate')) {
            $results = Cache::get('current-rate');
        } else {
            $data = DB::select(
                DB::raw("SELECT
                          name,
                          genres.id genre_id,
                          description,
                          count(user_id),
                          count(user_id) * 100 / t1.summ AS percentage
                        FROM genres
                          LEFT JOIN votes ON (votes.genre_id = genres.id)
                          , (SELECT count(*) summ
                             FROM votes) AS t1
                        GROUP BY genre_id, name"));

            foreach ($data as $item) {
                $results[$item->genre_id] = ['name' => $item->name,
                    'description' => $item->description,
                    'percentage' => $item->percentage];
            }

            Cache::put('current-rate', $results, 60);
        }

        return $results;
    }
}
