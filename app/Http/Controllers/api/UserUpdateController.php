<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateUserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/*
class UserUpdateController extends Controller
{
    public function update(StoreUpdateUserUpdateRequest $request, User $user)
    {

        $user->update($request->validated());



        return new UserResource($user);
    }
}
*/

class UserUpdateController extends Controller
{
    public function update(StoreUpdateUserUpdateRequest $request, User $user)
    {

        $validatedData = $request->validated();

        // Processa a imagem (se fornecida)
        if ($request->has('photo')) {
            $photoBase64 = $validatedData['photo'];

            // Decodifica a imagem e salva no diretório público
            $photo = base64_decode($photoBase64);
            $photoFilename = 'profile_' . uniqid() . '.jpg';
            //Storage::put('photos/' . $photoFilename, $photo);
            Storage::disk('public')->put('photos/' . $photoFilename, $photo);

            // Atualiza o campo `photo_filename`
            $validatedData['photo_filename'] = $photoFilename;
        }

        // Remove o campo `photo` para evitar erro ao salvar
        unset($validatedData['photo']);

        // Atualiza o utilizador
        $user->update($validatedData);

        return new UserResource($user);
}

}
