<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactDeleteRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{

    private function getContact(User $user, int $id_contact): Contact
    {
        if (!isset($id_contact)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        "ID Required"
                    ]
                ]
            ], 400));
        }
        $contact = Contact::where('user_id', $user->id)
            ->where('id', $id_contact)->first();
        if (!$contact) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ], 404));
        }
        return $contact;
    }
    public function createStored(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }
    public function deleteStored(ContactDeleteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $contact = $this->getContact($user, $data["id"]);
        $contact->delete();
        return  response()->json([
            "message" => [
                "Data Deleted"
            ]
        ], 200);
    }
    public function updateStored(ContactUpdateRequest $request): ContactResource
    {
        $data = $request->validated();
        $user = Auth::user();
        $contact = $this->getContact($user, $data["id"]);

        // $contact = Contact::where('id', $data['id'])->where('user_id', $user->id)->first();
        // if (!$contact) {
        //     throw new HttpResponseException(response([
        //         'errors' => [
        //             'message' => [
        //                 "Not Found"
        //             ]
        //         ]
        //     ], 404));
        // }
        $contact->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);
        $contact->save();
        return new ContactResource($contact);
        // return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function get(int $id): ContactResource
    {
        // Check if user is authenticated
        $user = Auth::user();

        // Find the contact belonging to the authenticated user
        $contact = $this->getContact($user, $id);

        // If contact is found, return the resource
        return new ContactResource($contact);
    }
    public function search(Request $request)
    {
        $user = Auth::user();
        $page = $request->input("page", 1);
        $perPage = $request->input("perPage", 10);

        $contacts = Contact::query()->where("user_id", $user->id);;

        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            $email = $request->input('email');
            $phone = $request->input('phone');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('first_name', 'like', '%' . $name . '%');
                    $builder->orWhere('last_name', 'like', '%' . $name . '%');
                });
            }
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });

        // Ambil data
        $contacts = $contacts->paginate($perPage, ['*'], 'page', $page);

        // Mengembalikan response
        return $contacts;
    }
}
