<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function create2(Request $request)
    {
        return new UserResource($request->user());
    }

    public function create(StoreCreateUserRequest $request, User $user)
    {

        $validatedData = $request->validated();
        //dd($request->all());

        // Verifica se o campo "photo" existe
    if ($request->has('photo')) {
        // Remove o prefixo Base64 e decodifica a imagem
        $photoContent = base64_decode(preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $validatedData['photo']));

        if ($photoContent === false) {
            return response()->json(['error' => 'Erro ao decodificar a imagem.'], 422);
        }

        $request->input('name');


        // Gera um nome para a imagem
        //$photoFilename = $user->id . '_' . uniqid() . '.jpg';
        // Vai buscar o nome da imagem da base de dados
        $photoFilename = $user->photo_filename;


        /*
        na vista dos pedidos!!!!
        criar:
        se has foto
        get last user_id+1 + (string gerada) + .jpg




        atualizar:
        if (verificar se já existe foto)
        escrever por cima
    else:
        criar foto

        */



        // Guarda a imagem (como esta no config/filesystems.php) (storage/app/public/photos)
        Storage::disk('public')->put('photos/' . $photoFilename, $photoContent);

        // Atualiza o campo da base de dados
        $validatedData['photo_filename'] = $photoFilename;

        // Limpa o campo da foto para evitar um erro no update
        unset($validatedData['photo']);
    }


        // Atualiza os dados do utilizador
        $user->create($validatedData);

        return new UserResource($user);
    }

}

