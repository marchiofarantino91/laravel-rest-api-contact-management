<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressDeleteRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ContactResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    //
    private function getAddress(int $id_contact, int $id_address): Address
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
        if (!isset($id_contact)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        "ID Address Required"
                    ]
                ]
            ], 400));
        }
        $address = Address::where('contact_id', $id_contact)->where('id', $id_address)->first();
        if (!$address) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ], 404));
        }
        return $address;
    }
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
    public function createStored(AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $contact = $this->getContact($user, $data['id_contact']);

        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();
        return (new AddressResource($address))->response()->setStatusCode(201);
    }
    public function getDetail(Request $request): AddressResource
    {
        $user = Auth::user();
        $id_contact = $request->input("id_contact");
        $id_address = $request->input("id_address");

        if (!isset($id_contact) || !isset($id_address)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        "id_contact or id_address required."
                    ]
                ]
            ], 400));
        }

        $contact = $this->getContact($user, $id_contact);
        $address = $this->getAddress($contact->id, $id_address);


        return new AddressResource($address);
    }

    public function updateStored(AddressUpdateRequest $request): AddressResource
    {
        $user = Auth::user();
        $data = $request->validated();

        $contact = $this->getContact($user, intval($data['id_contact']));
        $address = $this->getAddress($contact->id, intval($data['id_address']));

        $address->fill($data);
        $address->save();
        return new AddressResource($address);
    }
    public function deleteStored(AddressDeleteRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $contact = $this->getContact($user, intval($data['id_contact']));
        $address = $this->getAddress($contact->id, intval($data['id_address']));

        $address->delete();
        return  response()->json([
            "message" => [
                "Data Deleted"
            ]
        ], 200);
    }
    public function list(Request $request): JsonResponse
    {
        $user = Auth::user();
        $id_contact = $request->input("id_contact");

        $contact = $this->getContact($user, intval($id_contact));
        $addresses = Address::where('contact_id', $contact->id)->get();

        return (AddressResource::collection($addresses))->response()->setStatusCode(200);
    }
}
