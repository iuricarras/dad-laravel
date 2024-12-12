<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserCreateController extends Controller
{


    public function index()
    {
        return User::get();
    }

    public function show(User $user)
    {
        return $user;
    }

    public function showMe(Request $request)
    {
        return new UserResource($request->user());
    }


    public function create(StoreCreateUserRequest $request, User $user)
    {

        $validatedData = $request->validated();

        // calcula o próximo ID
        $nextId = User::max('id') + 1;

        // caso nos dados exista uma foto
        if (isset($validatedData['photo'])) {
            $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));
            $photoFilename = $nextId . '_' . uniqid() . '.jpg';

			Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

            // altera o campo da foto com o nome gerado
            $validatedData['photo_filename'] = $photoFilename;
        }

        // aplica uma Hash na password antes de criar o user
        $validatedData['password'] = Hash::make($validatedData['password']);

        // cria o user
        $user = User::create($validatedData);

        return new UserResource($user);
    }

}

