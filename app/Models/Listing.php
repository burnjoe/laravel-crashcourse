<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    // own defined attributes of Listing model
    // this fixes the required fillable property error thrown by Model::create(array)
    // you can omit this by adding Model:unguard() in boot() of AppServiceProvider.php
    // though it is not secure (coding level) compare to having fillable
    // fields should reflect to post data passed into Model::create()
    protected $fillable = ['title', 'company', 'location', 'logo', 'website', 'email', 'tags', 'description', 'user_id'];


    // own defined methods of Listing model:

    // filter out table given the query
    public function scopeFilter($query, array $filters) {
        // please do refer from ListingController

        // if $filters['tag] == null it then evaluates to false, preventing any potential errors.
        if($filters['tag'] ?? false) {          // ?? null coalescing operator    
            // same as SELECT ... WHERE tags LIKE '%tag%'
            $query->where('tags', 'like', '%' . request('tag') . '%');       // like query
        }

        // if get request is search:
        if($filters['search'] ?? false) {          // ?? null coalescing operator    
            // same as SELECT ... WHERE tags LIKE '%search%'
            // the first argument is the fieldname or the column name of the table to be queried out
            $query->where('title', 'like', '%' . request('search') . '%')
                ->orWhere('description', 'like', '%' . request('search') . '%')       // like query
                ->orWhere('tags', 'like', '%' . request('search') . '%')
                ->orWhere('location', 'like', '%' . request('search') . '%');
        }
    }

    // Relationship to User
    // this creates relationship between listing and user
    public function user() {
        // this Listing model belongs to this User model refering to 'user_id'
        return $this->belongsTo(User::class, 'user_id');
    }
}
