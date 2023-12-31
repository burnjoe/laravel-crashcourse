<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // show all listings
    public function index() {
        // request()->tag and request('tag') is just the same thing
        // dd(request());

        // code from web.php
        return view('listings.index', [   
            // replace all() with latest ()            
            // 'listings' => Listing::all()

            // latest() sorts the row/data in descending order
            // tag, search are the names of get request tag is from clicked tags
            // and search is from search bar of the web app 

            // previous snippet:
            // 'listings' => Listing::latest()->filter(request(['tag', 'search']))->get()

            // replaced by:
            // paginate(numberOfItemPerPage) -> with numbers in view
            // simplePaginate(numberOfItemPerPage) -> only previous and next buttons
            // both only works with GET parameters
            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);   
    }

    // show single listing
    public function show(Listing $listing) {
        // code from web.php
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // show create form
    public function create() {
        return view('listings.create');         // redirect user to views/listings/create.blade.php
    }

    // store listing data
    public function store(Request $request) {           // dependency injection
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        // if post request has logo input,
        // append logo to formFields + file is uploaded to storage/app/public/logos
        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // sets the listing to be stored as owned by the current user logged in
        $formFields['user_id'] = auth()->id();

        // if validate() did not raise an error, create record
        // will produce fillable error the first time you run it
        Listing::create($formFields);

        // alternative of with()
        // Session::flash('message', 'Listing Created Successfully!');

        return redirect('/')->with('message', 'Listing Created Successfully!');
    }

    // show edit form
    public function edit(Listing $listing) {
        return view('listings.edit', ['listing' => $listing]);
    }

    // Edit submit to update
    public function update(Request $request, Listing $listing) {
        // Make sure logged in user is owner
        if($listing->user_id != auth()->id()) {
            // if user_id of the listing is not equal to id of currently logged in user
            // throw error 403
            abort(403, 'Unauthorized Action');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        // if post request has logo input,
        // append logo to formFields + file is uploaded to storage/app/public/logos
        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // instead of using the Listing class's methods statically,
        // we can utilize the update() method of the passed listing object
        $listing->update($formFields);

        // alternative of with()
        // Session::flash('message', 'Listing Created Successfully!');

        // instead of using redirect() to redirect back to previous page,
        // back() is used to create a redirect response to the user's previous location
        return back()->with('message', 'Listing Updated Successfully!');
    }

    // Delete listing
    public function destroy(Listing $listing) {
        // Make sure logged in user is owner
        if($listing->user_id != auth()->id()) {
            // if user_id of the listing is not equal to id of currently logged in user
            // throw error 403
            abort(403, 'Unauthorized Action');
        }

        $listing->delete();
        return redirect('/')->with('message', 'Listing Deleted Successfully');
    }
    
    // Manage listings
    public function manage() {
        // retrieves the currently authenticated user
        // then get the collection of listings owned by the authenticated user
        // this fetches the listings associated with the currently authenticated user 
        // passing them to view template 'listing.manage'
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);      
    }

}
