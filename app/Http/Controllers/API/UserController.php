<?php

namespace App\Http\Controllers\API;

use App\Helpers\CustomResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        try {
            //Digunakan untuk memvalidasi data yang digunakan untuk register 
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            //memasukan data user yang diregister ke database
            User::create([
                'name'=> $request->name,
                'username'=> $request->username,
                'email'=> $request->email,
                'phone'=> $request->phone,
                'password'=> Hash::make($request->password),
            ]);

            //mengambil semua data user yang diambil dengan email yang di request 
            $user = User::where('email', $request->email)->first();
            
            //Digunakan untuk membuat token result sesuai data user
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //Digunakan untuk custom response formatter (ketika success)
            return CustomResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User Registered');

        } catch (Exception $error) {
            //Digunakan untuk custom response (ketika error)
            return CustomResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            //digunakan untuk memvalidasi apakah email dan password kosong atau tidak
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            //Untuk mengecek di database apakah ada data email dan password di auth (Jika tidak maka keluar report unauthorized)
            if(!Auth::atempt($credentials)){
                return CustomResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            //Untuk mengambil data user dimana data email seperti email yang dimasukan
            $user = User::where('email', $request->email)->first();
            
            //Digunakan untuk mengecek password yang dihash di database apakah sama dengan password yang di inputkan (Demi tambah keamanan)
            if(!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            //Digunakan untuk membuat token setelah melakukan login
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //Memunculkan respon apabila success 
            return CustomResponseFormatter::success([
                'access_token'=> $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Auntheticate');

        } catch (Exception $error) {
            //Memunculkan respon apabila terjadi error
            return CustomResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Athentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        //Mengambil data user (Ajaib sekali ga ada function ambil data)
        //Data sudah diambil dari bagian login atau register (Dari Token) -> jadi untuk bagian pengambilan data
        //Jadi untuk ambil data user tinggal begini saja dengan memasukan authorization token setelah login atau register
        return CustomResponseFormatter::success($request->user(), 'Data profile user berhasil diambil');
    }
}
