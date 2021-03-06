<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    //This is the user controller class for users



    public function signUP( Request $request){

        $this->validate($request, [
            'firstname'=> 'required',
            'lastname'=> 'required',
            'email'=> 'required|unique:users',
            'id'=> 'unique:users',
            'password'=> 'required'

        ]);

        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $id = $request['ID'];
        $email = $request['email'];
        $password = bcrypt($request['password']);
        $county = $request['county'];

        $user = new User();

        $user->firstname = $firstname;
        $user-> lastname = $lastname;
        $user-> id = $id;
        $user-> email = $email;
        $user-> password= $password;
        $user-> county = $county;

        $user->save();

        return view('success');

    }

    public function signIn( Request $request){

        if ($request->isMethod('post')){
            $credentials = array(
                'email' => $request['email'],
                'password' => $request['password']
            );

            $user = User::where('email',$request->get('email'))->first();

            if($user!=null && $user->is_admin == 1) {
                if (\Auth::attempt($credentials)) {
                    return redirect()->route('dashboard');

                }

            }

            elseif($user!=null && $user->is_official == 1) {
                if (\Auth::attempt($credentials)) {
                    return redirect()->route('dashboard-official');
                }
            }
            elseif ($user==null){
                return redirect()->back()->with('errors',['Authentication failed please try again']);
            }


            return redirect()->back()->with('errors',['Authentication failed please try again']);
        }


        return view("auth.login");
    }


    public function getDashboard() {
        $id = \App\Models\CensusId::max('id')+10010;
        return view('dashboard')->withId($id);

    }

            $password = bcrypt($request['password']);
            $county = $request['county'];
            $phoneno = $request['phoneno'];
            $ward = $request->get('ward');


            $file = $request->file('image');
            $filename = $request['firstname'].'-'.$request['ID'].'.jpg';

            if ($file)
            {
                Storage::disk('local')->put($filename, File::get($file));
            }

            $user = new User();

            $user->firstname = $firstname;
            $user-> lastname = $lastname;
            $user->is_official=1;
            $user-> id = $id;
            $user-> email = $email;
            $user-> password= $password;
            $user-> county = $county;
            $user->phone_number=$phoneno;
            $user->ward = $ward;


            $user->save();

            $request->session()->flash('alert-success','User successfully added!');

            $user = User::where('is_official',1)->count();

            return \View::make('register-official')->with(compact('user'));

            //return redirect()->route('register-official');

        }
        return view('register-official');
    }

    public function signUpEnumerator( Request $request)
    {
        $is_enumerator = 1;

        $this->validate($request, [
            'firstname'=> 'required',
            'lastname'=> 'required',
            'email'=> 'required|unique:users',
            'id'=> 'unique:users|min:6',
            'password'=> 'required'

        ]);

        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $id = $request['ID'];
        $email = $request['email'];
        $password = bcrypt($request['password']);
        $county = $request['county'];

        $phone_number = $request->get('phone_number');
        $headoffice = $request->get('headoffice');
        $reportsto = $request->get('reportsto');
        $supervisor_phone = $request->get('supervisor_phone');

        $file = $request->file('image');
        $filename = $request['firstname'].'-'.$request['ID'].'.jpg';

        if ($file)
        {
            Storage::disk('local')->put($filename, File::get($file));
        }

        $user = new User();

        $user->firstname = $firstname;
        $user-> lastname = $lastname;
        $user-> id = $id;
        $user-> email = $email;
        $user-> password= $password;
        $user-> county = $county;

        $user->is_enumerator = $is_enumerator;
        $user->phone_number = $phone_number;
        $user->headoffice = $headoffice;
        $user->reportsto = $reportsto;
        $user->supervisor_phone = $supervisor_phone;



        $user->save();

        $request->session()->flash('alert-success','Enumerator successfully added!');
        $user = User::where('is_official',1)->count();

        return \View::make('register-enumerator')->with(compact('user'));
    }

    public function getUserImage($filename){
        $file = Storage::disk('local')->get($filename);

        return new Response($file, 200);
    }

    public function viewUsers(){
        $users = User::whereIsAdmin(null)->get();
        return view('dashboard-view-users')->withUsers($users);

    }

    public function edituser(Request $request, $id)
    {
        $user = User::where('id',$id)->get()->first();

        if ($request->isMethod('put'))
        {

            $this->validate($request, [
                'firstname'=> 'required|string',
                'lastname'=> 'required|string',
                'email'=> 'required|email',
                'id'=> 'unique:users|min:6'

            ]);

            {
                Storage::disk('local')->put($filename, File::get($file));
            }



            $user->firstname = $firstname;
            $user-> lastname = $lastname;
            $user-> id = $id;
            $user-> email = $email;
            $user-> county = $county;
            $user->phone_number= $phoneno;
            $user->ward= $ward;

            if ($request['password']!=''){
                $user-> password= $password;

            }
            if ($user->is_official){
                $user->is_official=1;
            }
            else if($user->is_enumerator){
                $phone_number = $request->get('phone_number');
                $headoffice = $request->get('headoffice');
                $reportsto = $request->get('reportsto');
                $supervisor_phone = $request->get('supervisor_phone');
                $ward= $request->get('ward');

                $user->headoffice= $headoffice;
                $user->reportsto=$reportsto;
                $user->supervisor_phone=$supervisor_phone;
                $user->is_enumerator=1;
            }




            $user->update();



            $request->session()->flash('alert-success', 'successfully edited!');

            return $this->viewUsers();
            //return View::make('edit-tasklist')->with('user', $user)->with('task', $task);
        }
        return view('dashboard-edit-user')->withUser($user);
    }

    public function deleteUser(Request $request, $id){

        if($id){
            $user = User::where('id',$id)->get()->first();

            $user->delete();

            $request->session()->flash('alert-success','successfully deleted!');
            return $this->viewUsers();

        }
    }

    public function showUserProfile(){
        $user = User::find(Auth::id());
        return view('user-profile')->with('user', $user);
    }

    public function signOut(){
        Auth::logout();

        return redirect()->route('signin');
    }

}
