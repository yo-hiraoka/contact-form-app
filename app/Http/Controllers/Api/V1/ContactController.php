<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContactRequest $request)
    {
        $filters = $request->validated();

        $contacts = Contact::with(['category', 'tags'])
            ->when($filters['keyword'] ?? null, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['gender'] ?? null, function ($query, $gender) {
                $query->where('gender', $gender);
            })
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($filters['date'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $contact = Contact::create([
            'category_id' => $validated['category_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'detail' => $validated['detail'],
        ]);

        $contact->tags()->attach($validated['tag_ids'] ?? []);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();

        $contact->update([
            'category_id' => $validated['category_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'detail' => $validated['detail'],
        ]);

        $contact->tags()->sync($validated['tag_ids'] ?? []);

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
